<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Models\Vendor;
use App\Models\VendorBalanceTransaction;
use App\Models\VendorPayoutRequest;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function metrics(): array
    {
        $orders = Order::query();
        $completedLike = Order::query()->whereIn('status', ['paid', 'confirmed', 'design_review', 'in_production', 'ready_to_ship', 'shipped', 'delivered', 'completed']);

        $revenue = (float) (clone $completedLike)->sum('subtotal');
        $commission = (float) (clone $completedLike)->sum('commission_amount');
        $refunds = (float) Order::query()->whereIn('status', ['cancelled', 'disputed'])->sum('subtotal');
        $expenses = Setting::platformExpenses();
        $margin = $commission - $refunds - $expenses;

        $shipped = Order::query()->whereNotNull('shipped_at')->count();
        $notShipped = Order::query()
            ->whereNull('shipped_at')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();

        $lateTermin = Order::query()
            ->whereNotNull('termin_due_at')
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('shipped_at')->whereColumn('shipped_at', '>', 'termin_due_at');
                })->orWhere(function ($q2) {
                    $q2->whereNull('shipped_at')
                        ->where('termin_due_at', '<', now())
                        ->whereNotIn('status', ['cancelled', 'shipped', 'delivered', 'completed']);
                });
            })
            ->count();

        $upcomingCommissions = Order::query()
            ->whereNotNull('commission_ready_at')
            ->where('commission_ready_at', '<=', now()->addDays(7))
            ->where('commission_ready_at', '>=', now())
            ->sum('commission_amount');

        $earlyPayouts = 0;
        if (class_exists(VendorPayoutRequest::class) && DB::getSchemaBuilder()->hasTable('vendor_payout_requests')) {
            $earlyPayouts = VendorPayoutRequest::query()->where('status', 'pending')->count();
        }

        $subscriptionFees = 0;
        if (DB::getSchemaBuilder()->hasTable('vendor_balance_transactions')) {
            $subscriptionFees = abs((float) VendorBalanceTransaction::query()
                ->where('type', 'subscription_fee')
                ->sum('amount'));
        }

        return [
            'vendors' => Vendor::count(),
            'active_vendors' => Vendor::where('is_active', true)->count(),
            'orders' => Order::count(),
            'revenue' => $revenue,
            'commission' => $commission,
            'upcoming_commissions' => (float) $upcomingCommissions,
            'early_payout_requests' => $earlyPayouts,
            'cancellations_refunds' => $refunds,
            'shipped' => $shipped,
            'not_shipped' => $notShipped,
            'late_termin' => $lateTermin,
            'subscription_income' => $subscriptionFees,
            'platform_expenses' => $expenses,
            'margin' => $margin,
            'risk_safe' => Vendor::where('risk_band', 'safe')->count(),
            'risk_medium' => Vendor::where('risk_band', 'medium')->count(),
            'risk_risky' => Vendor::where('risk_band', 'risky')->count(),
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
