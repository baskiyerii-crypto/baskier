<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Vendor;

class CommissionService
{
    public function rateForVendor(?Vendor $vendor): float
    {
        if ($vendor && $vendor->commission_rate_override !== null) {
            return (float) $vendor->commission_rate_override;
        }

        return Setting::commissionRate();
    }

    public function amountsForSubtotal(float $subtotal, float $ratePercent): array
    {
        $commission = round($subtotal * $ratePercent / 100, 2);
        $vendorNet = round($subtotal - $commission, 2);

        return [$commission, $vendorNet];
    }

    public function syncCommissionRow(Order $order): Commission
    {
        return Commission::updateOrCreate(
            ['order_id' => $order->id],
            [
                'vendor_id' => $order->vendor_id,
                'rate' => $order->commission_rate,
                'amount' => $order->commission_amount,
                'vendor_net' => $order->vendor_amount,
                'payout_status' => $order->payout_approved ? 'released' : 'pending',
            ]
        );
    }
}
