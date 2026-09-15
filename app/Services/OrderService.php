<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBalanceTransaction;
use App\Models\VendorPayoutRequest;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderWorkflowService $workflow
    ) {}

    public function setTerminDueAt(Order $order): void
    {
        $days = 0;
        $order->loadMissing(['items.product.category']);
        foreach ($order->items as $item) {
            $termin = (int) ($item->product?->category?->termin_days ?? $item->product?->category?->delivery_days ?? 0);
            $days = max($days, $termin);
        }
        if ($days <= 0) {
            $days = 7;
        }

        $order->update([
            'termin_due_at' => now()->addDays($days),
        ]);
    }

    public function transition(Order $order, string $to, User $actor): void
    {
        $this->workflow->transition($order, $to, $actor);

        if ($to === OrderStatus::CONFIRMED || $to === 'paid') {
            $this->setTerminDueAt($order->fresh());
        }

        if ($to === OrderStatus::SHIPPED) {
            $order->update(['shipped_at' => now()]);
        }
    }

    public function isTerminLate(Order $order): bool
    {
        if (! $order->termin_due_at) {
            return false;
        }
        if ($order->shipped_at) {
            return $order->shipped_at->gt($order->termin_due_at);
        }

        return now()->gt($order->termin_due_at)
            && ! in_array($order->status, [OrderStatus::SHIPPED, OrderStatus::DELIVERED, OrderStatus::COMPLETED, OrderStatus::CANCELLED], true);
    }

    public function isOnTimeShip(Order $order): bool
    {
        return $order->shipped_at
            && $order->termin_due_at
            && $order->shipped_at->lte($order->termin_due_at);
    }
}
