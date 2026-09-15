<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Support\Facades\Schema;

class VendorRiskService
{
    public function recalculate(Vendor $vendor): Vendor
    {
        if (! Schema::hasColumn('vendors', 'risk_band') || ! Schema::hasColumn('vendors', 'risk_score')) {
            return $vendor;
        }

        $orders = collect();
        if (Schema::hasColumn('orders', 'termin_due_at')) {
            $orders = Order::query()
                ->where('vendor_id', $vendor->id)
                ->whereNotNull('termin_due_at')
                ->get();
        }

        $terminScore = 70.0;
        if ($orders->isNotEmpty()) {
            $onTime = $orders->filter(function (Order $order) {
                if ($order->shipped_at) {
                    return $order->shipped_at->lte($order->termin_due_at);
                }

                return $order->termin_due_at->isFuture();
            })->count();
            $terminScore = ($onTime / max(1, $orders->count())) * 100;
        }

        $rating = (float) ($vendor->rating_average ?? 0);
        $satisfactionScore = $rating > 0 ? ($rating / 5) * 100 : 70.0;

        $score = round(($terminScore * 0.6) + ($satisfactionScore * 0.4), 2);
        $band = match (true) {
            $score >= 75 => 'safe',
            $score >= 50 => 'medium',
            default => 'risky',
        };

        $vendor->update([
            'risk_score' => $score,
            'risk_band' => $band,
        ]);

        return $vendor->fresh();
    }

    public function recalculateAll(): int
    {
        $count = 0;
        Vendor::query()->chunkById(50, function ($vendors) use (&$count) {
            foreach ($vendors as $vendor) {
                $this->recalculate($vendor);
                $count++;
            }
        });

        return $count;
    }

    public function label(?string $band): string
    {
        return match ($band) {
            'safe' => 'Risksiz',
            'medium' => 'Orta risk',
            'risky' => 'Riskli',
            default => 'Hesaplanmadı',
        };
    }
}
