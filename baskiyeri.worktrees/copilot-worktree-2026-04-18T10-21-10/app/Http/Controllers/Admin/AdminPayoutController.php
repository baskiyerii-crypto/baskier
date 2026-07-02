<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminPayoutController extends Controller
{
    public function index()
    {
        $ready = Order::where('payout_approved', false)
            ->whereNotNull('commission_ready_at')
            ->where('commission_ready_at', '<=', now())
            ->with(['vendor', 'user'])
            ->orderBy('commission_ready_at')
            ->get();
        $grouped = $ready->groupBy('vendor_id')->map(function ($orders) {
            $first = $orders->first();
            return [
                'vendor' => $first->vendor,
                'orders' => $orders,
                'total_vendor_amount' => $orders->sum('vendor_amount'),
                'total_commission' => $orders->sum('commission_amount'),
            ];
        });
        $recentlyApproved = Order::where('payout_approved', true)->with('vendor')->latest('payout_at')->limit(10)->get();
        return view('admin.payouts.index', compact('grouped', 'recentlyApproved'));
    }

    public function approve(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array'],
            'order_ids.*' => ['exists:orders,id'],
        ]);
        Order::whereIn('id', $validated['order_ids'])
            ->where('payout_approved', false)
            ->whereNotNull('commission_ready_at')
            ->where('commission_ready_at', '<=', now())
            ->update(['payout_approved' => true, 'payout_at' => now()]);
        return back()->with('success', count($validated['order_ids']) . ' siparis hakedis onaylandi.');
    }
}
