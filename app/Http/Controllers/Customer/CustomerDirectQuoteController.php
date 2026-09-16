<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DirectQuoteRequest;
use App\Models\Order;
use App\Models\Vendor;
use App\Services\ContactShareService;
use Illuminate\Http\Request;

class CustomerDirectQuoteController extends Controller
{
    public function index(Request $request)
    {
        $items = DirectQuoteRequest::query()
            ->with('vendor')
            ->where('customer_user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('customer.direct-quotes.index', compact('items'));
    }

    public function create(Request $request)
    {
        $vendorIds = Order::query()
            ->where('user_id', $request->user()->id)
            ->whereNotNull('vendor_id')
            ->distinct()
            ->pluck('vendor_id');

        $vendors = Vendor::query()->whereIn('id', $vendorIds)->where('is_active', true)->orderBy('name')->get();

        return view('customer.direct-quotes.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        DirectQuoteRequest::create([
            'customer_user_id' => $request->user()->id,
            'vendor_id' => $validated['vendor_id'],
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
            'status' => 'open',
        ]);

        return redirect()->route('customer.direct-quotes.index')->with('success', __('panel.direct_quote_sent'));
    }

    public function show(Request $request, DirectQuoteRequest $directQuote)
    {
        abort_unless($directQuote->customer_user_id === $request->user()->id, 403);
        $directQuote->load('vendor');

        return view('customer.direct-quotes.show', compact('directQuote'));
    }

    public function accept(Request $request, DirectQuoteRequest $directQuote, ContactShareService $shares)
    {
        abort_unless($directQuote->customer_user_id === $request->user()->id, 403);
        $request->validate([
            'share_my_contact' => ['accepted'],
            'accept_vendor_contact' => ['accepted'],
        ]);

        if ($directQuote->status !== 'offered' || ! $directQuote->offer_amount) {
            return back()->with('error', __('panel.direct_quote_not_ready'));
        }
        if (! $directQuote->vendor_consented) {
            // Column may be missing before migrate; treat as not consented only when present.
            if (\Illuminate\Support\Facades\Schema::hasColumn('direct_quote_requests', 'vendor_consented')) {
                return back()->with('error', __('panel.waiting_vendor_consent'));
            }
        }

        try {
            $shares->shareAfterAccept(
                $request->user(),
                $directQuote->vendor,
                'direct_quote',
                $directQuote->id,
                true,
                (bool) $directQuote->vendor_consented,
                false
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $directQuote->update(['status' => 'accepted', 'closed_at' => now()]);

        return back()->with('success', __('panel.direct_quote_accepted'));
    }
}
