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
        $order->load(['vendor', 'items.product', 'quote', 'contractor']);
        $existingReview = $order->vendor->reviews()
            ->where('user_id', $request->user()->id)
            ->where('order_id', $order->id)
            ->first();

        return view('customer.orders.show', compact('order', 'existingReview'));
    }
}
