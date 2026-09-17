<?php
namespace App\Services;

use App\Models\Order;
use App\Models\Vendor;
use App\Models\VendorDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PlatformMetricsService
{
    /**
     * Gather comprehensive operational, payment, and queue metrics for the platform.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        try {
            return $this->buildMetrics();
        } catch (\Throwable $e) {
            Log::error('platform_metrics_failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile().':'.$e->getLine(),
            ]);

            return $this->emptyMetrics();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMetrics(): array
    {
        $now = now();
        $oneDayAgo = $now->copy()->subDay();
        $sevenDaysAgo = $now->copy()->subDays(7);

        $hasPaymentStatus = Schema::hasTable('orders') && Schema::hasColumn('orders', 'payment_status');
        $orders24h = Schema::hasTable('orders')
            ? Order::where('created_at', '>=', $oneDayAgo)->get()
            : collect();
        $total24h = $orders24h->count();
        $paid24h = $hasPaymentStatus ? $orders24h->where('payment_status', 'paid')->count() : 0;
        $rate24h = $total24h > 0 ? round(($paid24h / $total24h) * 100, 1) : 100.0;
        $revenue24h = $hasPaymentStatus
            ? (float) $orders24h->where('payment_status', 'paid')->sum('subtotal')
            : 0.0;

        $orders7d = Schema::hasTable('orders')
            ? Order::where('created_at', '>=', $sevenDaysAgo)->get()
            : collect();
        $total7d = $orders7d->count();
        $paid7d = $hasPaymentStatus ? $orders7d->where('payment_status', 'paid')->count() : 0;
        $rate7d = $total7d > 0 ? round(($paid7d / $total7d) * 100, 1) : 100.0;

        $pendingJobs = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        $activeReservations = 0;
        $expiredReservations = 0;
        if (Schema::hasTable('stock_reservations')) {
            $activeReservations = DB::table('stock_reservations')->where('expires_at', '>', $now)->count();
            $expiredReservations = DB::table('stock_reservations')->where('expires_at', '<=', $now)->count();
        }

        $pendingDocuments = 0;
        if (Schema::hasTable('vendor_documents')) {
            $pendingDocuments = VendorDocument::where('status', 'pending')->count();
        }

        $vendorsByTrust = [];
        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'trust_level')) {
            $vendorsByTrust = Vendor::query()
                ->select('trust_level', DB::raw('count(*) as count'))
                ->groupBy('trust_level')
                ->pluck('count', 'trust_level')
                ->toArray();
        }

        $paymentsCount24h = 0;
        if (Schema::hasTable('payments')) {
            $paymentsCount24h = DB::table('payments')->where('created_at', '>=', $oneDayAgo)->count();
        }

        $lastBackupVerification = \App\Models\Setting::get('last_backup_restore_verified_at');
        $lastRpoSeconds = \App\Models\Setting::get('last_backup_rpo_seconds');
        $lastRtoMs = \App\Models\Setting::get('last_backup_rto_ms');

        return [
            'payments' => [
                'success_rate_24h' => $rate24h,
                'total_orders_24h' => $total24h,
                'paid_orders_24h' => $paid24h,
                'revenue_24h' => $revenue24h,
                'success_rate_7d' => $rate7d,
                'total_orders_7d' => $total7d,
                'paid_orders_7d' => $paid7d,
                'payment_records_24h' => $paymentsCount24h,
            ],
            'queue' => [
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
                'status' => $failedJobs > 0 ? 'attention' : 'healthy',
            ],
            'stock_reservations' => [
                'active' => $activeReservations,
                'expired' => $expiredReservations,
            ],
            'verification' => [
                'pending_documents' => $pendingDocuments,
                'vendors_by_trust' => [
                    'level_0' => $vendorsByTrust[0] ?? 0,
                    'level_1' => $vendorsByTrust[1] ?? 0,
                    'level_2' => $vendorsByTrust[2] ?? 0,
                    'level_3' => $vendorsByTrust[3] ?? 0,
                ],
            ],
            'disaster_recovery' => [
                'last_verified_at' => $lastBackupVerification,
                'rpo_seconds' => $lastRpoSeconds ? (int) $lastRpoSeconds : null,
                'rto_ms' => $lastRtoMs ? (int) $lastRtoMs : null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyMetrics(): array
    {
        return [
            'payments' => [
                'success_rate_24h' => 100.0,
                'total_orders_24h' => 0,
                'paid_orders_24h' => 0,
                'revenue_24h' => 0.0,
                'success_rate_7d' => 100.0,
                'total_orders_7d' => 0,
                'paid_orders_7d' => 0,
                'payment_records_24h' => 0,
            ],
            'queue' => [
                'pending_jobs' => 0,
                'failed_jobs' => 0,
                'status' => 'healthy',
            ],
            'stock_reservations' => [
                'active' => 0,
                'expired' => 0,
            ],
            'verification' => [
                'pending_documents' => 0,
                'vendors_by_trust' => [
                    'level_0' => 0,
                    'level_1' => 0,
                    'level_2' => 0,
                    'level_3' => 0,
                ],
            ],
            'disaster_recovery' => [
                'last_verified_at' => null,
                'rpo_seconds' => null,
                'rto_ms' => null,
            ],
        ];
    }
}
