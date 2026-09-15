<?php

namespace App\Http\Controllers\Vendor;

use App\Domain\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BasitKargoService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class VendorOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private BasitKargoService $shipping
    ) {}

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
        $orders = $vendor->orders()->with(['user', 'quote', 'items.product'])->latest()->paginate(20);

        return view('vendor.orders.index', compact('vendor', 'orders'));
    }

    public function show(Request $request, Order $order)
    {
        $vendor = $this->getVendor($request);
        if ($order->vendor_id !== $vendor->id) {
            abort(403);
        }
        $order->load(['user', 'quote.quoteRequest', 'items.product']);
        $nextStatuses = app(\App\Services\OrderWorkflowService::class)->allowedTransitions()[$order->status] ?? [];
        $carriers = $this->shipping->carriers();

        return view('vendor.orders.show', compact('order', 'vendor', 'nextStatuses', 'carriers'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $vendor = $this->getVendor($request);
        if ($order->vendor_id !== $vendor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string'],
            'carrier_code' => ['nullable', 'string', 'max:64'],
        ]);

        $mapAliases = [
            'in_progress' => OrderStatus::IN_PRODUCTION,
            'delivered' => OrderStatus::DELIVERED,
        ];
        $to = $mapAliases[$validated['status']] ?? $validated['status'];

        try {
            $this->orderService->transition($order, $to, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($to === OrderStatus::SHIPPED || $to === OrderStatus::READY_TO_SHIP) {
            $this->shipping->createShipment($order->fresh(), $validated['carrier_code'] ?? null);
            if ($to === OrderStatus::READY_TO_SHIP) {
                try {
                    $this->orderService->transition($order->fresh(), OrderStatus::SHIPPED, $request->user());
                } catch (\InvalidArgumentException) {
                    // ignore if already shipped
                }
            }
        }

        return back()->with('success', 'Sipariş durumu güncellendi.');
    }
}
