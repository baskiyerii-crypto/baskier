<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EarningsCreditService
{
    public function creditReadyOrders(?int $limit = 200): int
    {
        if (! Schema::hasColumn('orders', 'balance_credited_at')) {
            return 0;
        }

        $credited = 0;
        Order::query()
            ->whereNotNull('commission_ready_at')
            ->where('commission_ready_at', '<=', now())
            ->whereNull('balance_credited_at')
            ->whereNotIn('status', [OrderStatus::CANCELLED, OrderStatus::DISPUTED, OrderStatus::PENDING, OrderStatus::PENDING_PAYMENT])
            ->where('vendor_amount', '>', 0)
            ->orderBy('id')
            ->limit($limit ?? 200)
            ->get()
            ->each(function (Order $order) use (&$credited) {
                if ($this->creditOrder($order)) {
                    $credited++;
                }
            });

        return $credited;
    }

    public function creditOrder(Order $order): bool
    {
        if (! Schema::hasColumn('orders', 'balance_credited_at')) {
            return false;
        }

        return (bool) DB::transaction(function () use ($order) {
            /** @var Order|null $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();
            if (! $locked || $locked->balance_credited_at) {
                return false;
            }
            if ((float) $locked->vendor_amount <= 0) {
                $locked->update(['balance_credited_at' => now()]);

                return false;
            }

            $vendor = Vendor::query()->whereKey($locked->vendor_id)->lockForUpdate()->first();
            if (! $vendor) {
                return false;
            }

            $amount = (float) $locked->vendor_amount;
            $vendor->increment('balance', $amount);
            $vendor->refresh();
            $vendor->balanceTransactions()->create([
                'amount' => $amount,
                'type' => 'hakedis',
                'reference_type' => 'order',
                'reference_id' => $locked->id,
                'description' => 'Sipariş hakedişi #'.$locked->order_number,
                'balance_after' => $vendor->balance,
            ]);

            $locked->update([
                'balance_credited_at' => now(),
                'payout_approved' => true,
                'payout_at' => $locked->payout_at ?? now(),
            ]);

            return true;
        });
    }
}
