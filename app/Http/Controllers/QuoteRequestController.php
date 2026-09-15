<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteRequestController extends Controller
{
    public function create(Request $request)
    {
        $type = $request->get('type', 'physical_quote');
        $channel = $type === 'freelancer' ? 'freelancer' : 'physical_quote';

        $categories = Category::where('is_active', true)
            ->where(function ($q) use ($channel) {
                $q->where('channel', $channel)->orWhereNull('channel');
            })
            ->when($type !== 'freelancer', fn ($q) => $q->where(function ($q2) {
                $q2->where('requires_quote', true)->orWhere('channel', 'physical_quote');
            }))
            ->orderBy('name')->get();

        if ($categories->isEmpty()) {
            $categories = Category::where('is_active', true)->orderBy('name')->get();
        }
        $products = Product::select(['id', 'name', 'category_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('quote-requests.create', compact('categories', 'products', 'type'));
    }

    public function store(Request $request)
    {
        $hasItems = is_array($request->input('items')) && count($request->input('items')) > 0;

        $validated = $request->validate([
            // Legacy single-category form (kept for backward compatibility).
            'category_id' => [$hasItems ? 'nullable' : 'required', 'exists:categories,id'],
            'title' => [$hasItems ? 'nullable' : 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:11', 'regex:/^[0-9]{10,11}$/'],
            'request_type' => ['nullable', 'in:physical_quote,freelancer,tabela'],
            'show_customer_profile' => ['nullable', 'boolean'],

            // Multi-item RFQ
            'items' => [$hasItems ? 'required' : 'nullable', 'array', 'min:1'],
            'items.*.category_id' => ['required_with:items', 'exists:categories,id'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'items.*.unit' => ['nullable', 'string', 'max:32'],
            'items.*.spec' => ['nullable', 'string', 'max:5000'],
            'items.*.files' => ['nullable', 'array', 'max:10'],
            'items.*.files.*' => ['file', 'mimes:jpg,jpeg,png,pdf,zip', 'max:'.config('uploads.max_kb', 5120)],
        ]);

        $validated['request_type'] = $validated['request_type'] ?? 'physical_quote';
        $validated['show_customer_profile'] = $request->boolean('show_customer_profile');

        $items = $hasItems ? ($validated['items'] ?? []) : [];
        if ($hasItems) {
            $firstCategory = $items[0]['category_id'] ?? null;
            if (!empty($firstCategory) && empty($validated['category_id'])) {
                $validated['category_id'] = $firstCategory;
            }
            if (empty($validated['title'])) {
                $names = collect($items)
                    ->pluck('product_id')
                    ->filter()
                    ->unique()
                    ->take(3)
                    ->values();
                if ($names->isNotEmpty()) {
                    $productNames = Product::whereIn('id', $names)->pluck('name')->toArray();
                    $validated['title'] = implode(' + ', $productNames);
                } else {
                    $validated['title'] = 'Teklif talebi';
                }
            }
        }

        if (! $request->user()) {
            // Files can't be carried through session. We persist items payload only.
            $pending = $validated;
            unset($pending['items']);
            $pending['items'] = collect($items)->map(function ($it) {
                return [
                    'category_id' => $it['category_id'] ?? null,
                    'product_id' => $it['product_id'] ?? null,
                    'quantity' => $it['quantity'] ?? null,
                    'unit' => $it['unit'] ?? null,
                    'spec' => $it['spec'] ?? null,
                ];
            })->values()->all();
            session(['pending_quote_request' => $pending]);
            return redirect()->route('register')->with('info', 'Teklif talebinizi gönderebilmek için üye olun veya giriş yapın. Form bilgileriniz kaydedildi.');
        }

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'open';

        DB::transaction(function () use ($request, $validated, $items) {
            $qr = QuoteRequest::create(collect($validated)->except(['items'])->all());

            foreach (array_values($items) as $idx => $it) {
                $item = $qr->items()->create([
                    'category_id' => $it['category_id'] ?? $qr->category_id,
                    'product_id' => $it['product_id'] ?? null,
                    'quantity' => $it['quantity'] ?? null,
                    'unit' => $it['unit'] ?? null,
                    'spec' => $it['spec'] ?? null,
                    'sort_order' => $idx,
                ]);

                $files = $request->file("items.$idx.files", []);
                foreach ($files ?? [] as $file) {
                    if (!$file) continue;
                    $path = $file->store("quote-request-items/{$qr->id}", 'public');
                    $item->files()->create([
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }
        });

        return redirect()->route('quote-requests.index')->with('success', 'Teklif talebiniz alındı. Satıcılar size teklif verebilecek.');
    }

    public function index(Request $request)
    {
        $requests = QuoteRequest::where('user_id', $request->user()->id)
            ->with(['category', 'items.category', 'items.product'])
            ->latest()
            ->paginate(15);
        return view('quote-requests.index', compact('requests'));
    }

    public function show(Request $request, QuoteRequest $quoteRequest)
    {
        if ($quoteRequest->user_id !== $request->user()->id) {
            abort(403);
        }
        $quoteRequest->load(['category', 'quotes' => fn ($q) => $q->whereIn('status', ['pending', 'selected', 'rejected'])->with('vendor'), 'items.category', 'items.product', 'items.files']);
        // Müşteri yalnızca teklif vermiş satıcıları görür
        $quoteRequest->setRelation('quotes', $quoteRequest->quotes);
        return view('quote-requests.show', compact('quoteRequest'));
    }

    public function selectQuote(Request $request, QuoteRequest $quoteRequest, Quote $quote)
    {
        if ($quoteRequest->user_id !== $request->user()->id) {
            abort(403);
        }
        if ($quote->quote_request_id !== $quoteRequest->id || $quote->status !== 'pending') {
            abort(400, 'Bu teklif seçilemez.');
        }
        $quote->update(['status' => 'selected']);
        $quote->quoteRequest->quotes()->where('id', '!=', $quote->id)->update(['status' => 'rejected']);
        $quoteRequest->update(['status' => 'closed', 'closed_at' => now()]);

        $rate = Setting::commissionRate();
        $subtotal = $quote->amount;
        $commissionAmount = round($subtotal * $rate / 100, 2);
        $vendorAmount = $subtotal - $commissionAmount;
        $waitDays = Setting::commissionWaitDays();

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => $request->user()->id,
            'vendor_id' => $quote->vendor_id,
            'type' => 'quote',
            'quote_id' => $quote->id,
            'status' => 'paid',
            'subtotal' => $subtotal,
            'commission_rate' => $rate,
            'commission_amount' => $commissionAmount,
            'vendor_amount' => $vendorAmount,
            'paid_at' => now(),
            'commission_ready_at' => now()->addDays($waitDays),
            'termin_due_at' => now()->addDays(max(1, (int) ($quote->delivery_days ?? 7))),
        ]);

        $order->items()->create([
            'name' => $quoteRequest->title,
            'price' => $subtotal,
            'quantity' => 1,
        ]);

        return redirect()->route('quote-requests.show', $quoteRequest)->with('success', 'Teklif seçildi. Sipariş #' . $order->order_number . ' oluşturuldu.');
    }
}
