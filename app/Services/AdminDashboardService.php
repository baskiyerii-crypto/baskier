<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Setting;
use App\Models\Vendor;
use App\Models\VendorBalanceTransaction;
use Illuminate\Support\Facades\Schema;

class AdminDashboardService
{
    public function metrics(): array
    {
        $completedLike = Order::query()->whereIn('status', ['paid', 'confirmed', 'design_review', 'in_production', 'ready_to_ship', 'shipped', 'delivered', 'completed']);

        $revenue = (float) (clone $completedLike)->sum('subtotal');
        $commission = (float) (clone $completedLike)->sum('commission_amount');
        $refunds = (float) Order::query()->whereIn('status', ['cancelled', 'disputed'])->sum('subtotal');
        $expenses = Setting::platformExpenses();
        $margin = $commission - $refunds - $expenses;

        $shipped = 0;
        $notShipped = 0;
        if (Schema::hasColumn('orders', 'shipped_at')) {
            $shipped = Order::query()->whereNotNull('shipped_at')->count();
            $notShipped = Order::query()
                ->whereNull('shipped_at')
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->count();
        }

        $lateTermin = 0;
        if (Schema::hasColumn('orders', 'termin_due_at')) {
            $lateTermin = Order::query()
                ->whereNotNull('termin_due_at')
                ->where(function ($q) {
                    if (Schema::hasColumn('orders', 'shipped_at')) {
                        $q->where(function ($q2) {
                            $q2->whereNotNull('shipped_at')->whereColumn('shipped_at', '>', 'termin_due_at');
                        })->orWhere(function ($q2) {
                            $q2->whereNull('shipped_at')
                                ->where('termin_due_at', '<', now())
                                ->whereNotIn('status', ['cancelled', 'shipped', 'delivered', 'completed']);
                        });
                    } else {
                        $q->where('termin_due_at', '<', now())
                            ->whereNotIn('status', ['cancelled', 'shipped', 'delivered', 'completed']);
                    }
                })
                ->count();
        }

        $upcomingCommissions = 0.0;
        if (Schema::hasColumn('orders', 'commission_ready_at')) {
            $upcomingCommissions = (float) Order::query()
                ->whereNotNull('commission_ready_at')
                ->where('commission_ready_at', '<=', now()->addDays(7))
                ->where('commission_ready_at', '>=', now())
                ->sum('commission_amount');
        }

        $earlyPayouts = 0;
        if (Schema::hasTable('payout_requests')) {
            $earlyPayouts = PayoutRequest::query()->where('status', 'pending')->count();
        }

        $subscriptionFees = 0;
        if (Schema::hasTable('vendor_balance_transactions')) {
            $subscriptionFees = abs((float) VendorBalanceTransaction::query()
                ->where('type', 'subscription_fee')
                ->sum('amount'));
        }

        $riskSafe = 0;
        $riskMedium = 0;
        $riskRisky = 0;
        if (Schema::hasColumn('vendors', 'risk_band')) {
            $riskSafe = Vendor::where('risk_band', 'safe')->count();
            $riskMedium = Vendor::where('risk_band', 'medium')->count();
            $riskRisky = Vendor::where('risk_band', 'risky')->count();
        }

        return [
            'vendors' => Vendor::count(),
            'active_vendors' => Vendor::where('is_active', true)->count(),
            'orders' => Order::count(),
            'revenue' => $revenue,
            'commission' => $commission,
            'upcoming_commissions' => $upcomingCommissions,
            'early_payout_requests' => $earlyPayouts,
            'cancellations_refunds' => $refunds,
            'shipped' => $shipped,
            'not_shipped' => $notShipped,
            'late_termin' => $lateTermin,
            'subscription_income' => $subscriptionFees,
            'platform_expenses' => $expenses,
            'margin' => $margin,
            'risk_safe' => $riskSafe,
            'risk_medium' => $riskMedium,
            'risk_risky' => $riskRisky,
        ];
    }

    public function revenueTrend(int $days = 30): array
    {
        $rows = Order::query()
            ->selectRaw('DATE(created_at) as d, SUM(subtotal) as total')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('total', 'd')
            ->toArray();

        $labels = [];
        $values = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $labels[] = $day;
            $values[] = (float) ($rows[$day] ?? 0);
        }

        return compact('labels', 'values');
    }
}
