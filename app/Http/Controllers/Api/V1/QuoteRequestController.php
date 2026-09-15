<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\QuoteRequestStoreRequest;
use App\Models\Order;
use App\Models\Quote;
use App\Models\QuoteRequest;
use App\Models\Setting;
use Illuminate\Http\Request;

class QuoteRequestController extends ApiController
{
    public function index(Request $request)
    {
        $requests = QuoteRequest::where('user_id', $request->user()->id)
            ->with('category')
            ->latest()
            ->paginate(20);

        return $this->ok($requests);
    }

    public function show(Request $request, QuoteRequest $quoteRequest)
    {
        $this->authorize('view', $quoteRequest);
        $quoteRequest->load(['category', 'quotes.vendor']);

        return $this->ok($quoteRequest);
    }

    public function store(QuoteRequestStoreRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'open';
        $qr = QuoteRequest::create($validated);

        return $this->ok($qr->load('category'), null, null, 201);
    }

    public function selectQuote(Request $request, QuoteRequest $quoteRequest, Quote $quote)
    {
        $this->authorize('view', $quoteRequest);
        if ($quote->quote_request_id !== $quoteRequest->id || $quote->status !== 'pending') {
            return $this->fail('Bu teklif seçilemez.', null, 400);
        }

        $quote->update(['status' => 'selected']);
        $quoteRequest->quotes()->where('id', '!=', $quote->id)->update(['status' => 'rejected']);
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

        return $this->ok([
            'order' => $order,
            'quote_request' => $quoteRequest->fresh(),
        ]);
    }
}
