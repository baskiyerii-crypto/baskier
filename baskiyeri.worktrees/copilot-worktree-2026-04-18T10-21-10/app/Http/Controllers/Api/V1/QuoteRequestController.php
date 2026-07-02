<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Quote;
use App\Models\QuoteRequest;
use App\Models\Setting;
use Illuminate\Http\Request;

class QuoteRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = QuoteRequest::where('user_id', $request->user()->id)
            ->with('category')
            ->latest()
            ->paginate(20);

        return response()->json($requests);
    }

    public function show(Request $request, QuoteRequest $quoteRequest)
    {
        if ($quoteRequest->user_id !== $request->user()->id) {
            abort(403);
        }
        $quoteRequest->load(['category', 'quotes.vendor']);

        return response()->json($quoteRequest);
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

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'open';
        $qr = QuoteRequest::create($validated);

        return response()->json($qr->load('category'), 201);
    }

    public function selectQuote(Request $request, QuoteRequest $quoteRequest, Quote $quote)
    {
        if ($quoteRequest->user_id !== $request->user()->id) {
            abort(403);
        }
        if ($quote->quote_request_id !== $quoteRequest->id || $quote->status !== 'pending') {
            return response()->json(['message' => 'Bu teklif seçilemez.'], 400);
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

        return response()->json(['order' => $order, 'quote_request' => $quoteRequest->fresh()]);
    }
}
