<?php

namespace App\Http\Controllers\Vendor;

use App\Domain\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\OrderWorkflowService;
use App\Support\UiLabels;
use Illuminate\Http\Request;

class VendorOrderController extends Controller
{
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
        $query = $vendor->orders()->with(['user', 'items.product', 'latestShipment'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
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
            'designApprovals' => fn($q) => $q->latest('id'),
            'shippingAddress',
            'billingAddress',
            'latestShipment',
        ]);

        $allowedTransitions = $workflow->allowedTransitions()[$order->status] ?? [];

        return view('vendor.orders.show', compact('order', 'vendor', 'allowedTransitions'));
    }

    public function updateStatus(Request $request, Order $order, OrderWorkflowService $workflow)
    {
        $vendor = $this->getVendor($request);
        if ((int) $order->vendor_id !== (int) $vendor->id) {
            abort(403, 'Bu sipariş mağazanıza ait değil.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string'],
            'carrier' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        $targetStatus = $validated['status'];

        if (! $workflow->canTransition($order, $targetStatus)) {
            return back()->with('error', "Geçersiz sipariş durumu geçişi: {$order->status} → {$targetStatus}");
        }

        // Kargo çıkışı yapılıyorsa kargo firması ve takip no zorunlu
        if ($targetStatus === OrderStatus::SHIPPED) {
            $request->validate([
                'carrier' => ['required', 'string', 'max:100'],
                'tracking_number' => ['required', 'string', 'max:100'],
            ]);

            Shipment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'carrier' => $validated['carrier'],
                    'tracking_number' => $validated['tracking_number'],
                    'shipped_at' => now(),
                ]
            );
        }

        $workflow->transition($order, $targetStatus, $request->user());

        return back()->with('success', 'Sipariş durumu güncellendi: ' . UiLabels::orderStatus($targetStatus));
    }
}
