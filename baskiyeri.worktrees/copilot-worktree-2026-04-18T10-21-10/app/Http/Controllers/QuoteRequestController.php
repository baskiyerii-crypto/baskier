<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Quote;
use App\Models\QuoteRequest;
use App\Models\Setting;
use Illuminate\Http\Request;

class QuoteRequestController extends Controller
{
    public function create()
    {
        $categories = Category::where('is_active', true)->where('requires_quote', true)->orderBy('name')->get();
        if ($categories->isEmpty()) {
            $categories = Category::where('is_active', true)->orderBy('name')->get();
        }
        return view('quote-requests.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ]);

        if (! $request->user()) {
            session(['pending_quote_request' => $validated]);
            return redirect()->route('register')->with('info', 'Teklif talebinizi gönderebilmek için üye olun veya giriş yapın. Form bilgileriniz kaydedildi.');
        }

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'open';
        QuoteRequest::create($validated);
        return redirect()->route('quote-requests.index')->with('success', 'Teklif talebiniz alındı. Satıcılar size teklif verebilecek.');
    }

    public function index(Request $request)
    {
        $requests = QuoteRequest::where('user_id', $request->user()->id)->with('category')->latest()->paginate(15);
        return view('quote-requests.index', compact('requests'));
    }

    public function show(Request $request, QuoteRequest $quoteRequest)
    {
        if ($quoteRequest->user_id !== $request->user()->id) {
            abort(403);
        }
        $quoteRequest->load(['category', 'quotes.vendor']);
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
        ]);

        $order->items()->create([
            'name' => $quoteRequest->title,
            'price' => $subtotal,
            'quantity' => 1,
        ]);

        return redirect()->route('quote-requests.show', $quoteRequest)->with('success', 'Teklif seçildi. Sipariş #' . $order->order_number . ' oluşturuldu.');
    }
}
