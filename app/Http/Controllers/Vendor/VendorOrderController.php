<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class VendorOrderController extends Controller
{
    private function getVendor(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403);
        }
        return $vendor;
    }

    public function index(Request $request)
    {
        $vendor = $this->getVendor($request);
        $orders = $vendor->orders()->with(['user', 'quote'])->latest()->paginate(20);
        return view('vendor.orders.index', compact('vendor', 'orders'));
    }

    public function show(Request $request, Order $order)
    {
        $vendor = $this->getVendor($request);
        if ($order->vendor_id !== $vendor->id) {
            abort(403);
        }
        $order->load(['user', 'quote.quoteRequest', 'items']);
        return view('vendor.orders.show', compact('order', 'vendor'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $vendor = $this->getVendor($request);
        if ($order->vendor_id !== $vendor->id) {
            abort(403);
        }
        $validated = $request->validate(['status' => ['required', 'in:in_progress,delivered']]);
        $order->update($validated);
        if (($validated['status'] ?? '') === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }
        return back()->with('success', 'Siparis durumu guncellendi.');
    }
}
