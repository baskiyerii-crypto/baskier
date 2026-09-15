<?php

namespace App\Http\Controllers\Vendor;

use App\Domain\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\BasitKargoService;
use App\Services\OrderService;
use App\Services\OrderWorkflowService;
use App\Support\UiLabels;
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
            abort(403, 'Satıcı hesabı bulunamadı.');
        }

        return $vendor;
    }

    public function index(Request $request)
    {
        $vendor = $this->getVendor($request);
        $query = $vendor->orders()->with(['user', 'quote', 'items.product', 'latestShipment'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('vendor.orders.index', compact('vendor', 'orders'));
    }

    public function show(Request $request, Order $order, OrderWorkflowService $workflow)
    {
        $vendor = $this->getVendor($request);
        if ((int) $order->vendor_id !== (int) $vendor->id) {
            abort(403, 'Bu sipariş mağazanıza ait değil.');
        }

        $order->load([
            'user',
            'quote.quoteRequest',
            'items.product',
            'items.variant',
            'designApprovals' => fn ($q) => $q->latest('id'),
            'shippingAddress',
            'billingAddress',
            'latestShipment',
        ]);

        $allowedTransitions = $workflow->allowedTransitions()[$order->status] ?? [];
        $nextStatuses = $allowedTransitions;
        $carriers = $this->shipping->carriers();

        return view('vendor.orders.show', compact('order', 'vendor', 'allowedTransitions', 'nextStatuses', 'carriers'));
    }

    public function updateStatus(Request $request, Order $order, OrderWorkflowService $workflow)
    {
        $vendor = $this->getVendor($request);
        if ((int) $order->vendor_id !== (int) $vendor->id) {
            abort(403, 'Bu sipariş mağazanıza ait değil.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string'],
            'carrier_code' => ['nullable', 'string', 'max:64'],
            'carrier' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        $mapAliases = [
            'in_progress' => OrderStatus::IN_PRODUCTION,
            'delivered' => OrderStatus::DELIVERED,
        ];
        $targetStatus = $mapAliases[$validated['status']] ?? $validated['status'];

        if (! $workflow->canTransition($order, $targetStatus)) {
            return back()->with('error', "Geçersiz sipariş durumu geçişi: {$order->status} → {$targetStatus}");
        }

        if ($targetStatus === OrderStatus::SHIPPED) {
            $carrier = $validated['carrier'] ?? $validated['carrier_code'] ?? null;
            $tracking = $validated['tracking_number'] ?? null;

            if (! $carrier || ! $tracking) {
                $shipment = $this->shipping->createShipment($order->fresh(), $validated['carrier_code'] ?? $carrier);
                $carrier = $carrier ?: ($shipment->carrier_code ?? 'basitkargo');
                $tracking = $tracking ?: ($shipment->tracking_number ?? null);
            }

            if ($carrier && $tracking) {
                Shipment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'carrier' => $carrier,
                        'tracking_number' => $tracking,
                        'shipped_at' => now(),
                    ]
                );
            }
        }

        if ($targetStatus === OrderStatus::READY_TO_SHIP) {
            $this->shipping->createShipment($order->fresh(), $validated['carrier_code'] ?? null);
        }

        try {
            $this->orderService->transition($order, $targetStatus, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $label = class_exists(UiLabels::class)
            ? UiLabels::orderStatus($targetStatus)
            : str_replace('_', ' ', $targetStatus);

        return back()->with('success', 'Sipariş durumu güncellendi: '.$label);
    }
}
