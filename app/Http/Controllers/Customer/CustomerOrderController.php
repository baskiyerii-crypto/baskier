<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['vendor', 'items'])
            ->latest()
            ->paginate(15);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
        $order->load([
            'vendor',
            'items.product',
            'quote.quoteRequest',
            'contractor',
            'designApprovals' => fn ($q) => $q->latest('id'),
            'latestShipment',
            'shippingAddress',
        ]);
        $existingReview = $order->vendor
            ? $order->vendor->reviews()
                ->where('user_id', $request->user()->id)
                ->where('order_id', $order->id)
                ->first()
            : null;

        $reviewProductChoices = $order->items
            ->filter(fn ($line) => $line->product_id !== null)
            ->unique('product_id')
            ->values();

        return view('customer.orders.show', compact('order', 'existingReview', 'reviewProductChoices'));
    }
}
