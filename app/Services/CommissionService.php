<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Vendor;

class CommissionService
{
    public const PRODUCT_TYPES = ['product'];

    public const ZERO_COMMISSION_TYPES = ['quote', 'freelancer', 'tabela', 'tabela_meeting', 'ozalit', 'outdoor'];

    public function rateForVendor(?Vendor $vendor = null): float
    {
        return Setting::commissionRate();
    }

    public function rateForOrderType(?string $type): float
    {
        if (in_array($type, self::PRODUCT_TYPES, true)) {
            return Setting::commissionRate();
        }

        return 0.0;
    }

    public function rateForOrder(Order $order): float
    {
        return $this->rateForOrderType($order->type);
    }

    /**
     * @return array{0: float, 1: float, 2: float} [rate, commission, vendorNet]
     */
    public function calculate(float $subtotal, ?string $orderType = 'product'): array
    {
        $rate = $this->rateForOrderType($orderType);
        [$commission, $vendorNet] = $this->amountsForSubtotal($subtotal, $rate);

        return [$rate, $commission, $vendorNet];
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
