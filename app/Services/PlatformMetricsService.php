<?php
namespace App\Services;

use App\Domain\OrderStatus;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\VendorDocument;
use Illuminate\Support\Facades\DB;
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
        $now = now();
        $oneDayAgo = $now->copy()->subDay();
        $sevenDaysAgo = $now->copy()->subDays(7);

        // 1. Payment Metrics (Last 24h & 7d)
        $orders24h = Order::where('created_at', '>=', $oneDayAgo)->get();
        $total24h = $orders24h->count();
        $paid24h = $orders24h->where('payment_status', 'paid')->count();
        $rate24h = $total24h > 0 ? round(($paid24h / $total24h) * 100, 1) : 100.0;
        $revenue24h = (float) $orders24h->where('payment_status', 'paid')->sum('subtotal');

        $orders7d = Order::where('created_at', '>=', $sevenDaysAgo)->get();
        $total7d = $orders7d->count();
        $paid7d = $orders7d->where('payment_status', 'paid')->count();
        $rate7d = $total7d > 0 ? round(($paid7d / $total7d) * 100, 1) : 100.0;

        // 2. Queue & Dead-Letter Depth
        $pendingJobs = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        // 3. Stock Reservations
        $activeReservations = 0;
        $expiredReservations = 0;
        if (Schema::hasTable('stock_reservations')) {
            $activeReservations = DB::table('stock_reservations')->where('expires_at', '>', $now)->count();
            $expiredReservations = DB::table('stock_reservations')->where('expires_at', '<=', $now)->count();
        }

        // 4. Verification Queue & Trust Levels
        $pendingDocuments = VendorDocument::where('status', 'pending')->count();
        $vendorsByTrust = Vendor::query()
            ->select('trust_level', DB::raw('count(*) as count'))
            ->groupBy('trust_level')
            ->pluck('count', 'trust_level')
            ->toArray();

        // 5. Payment Table Volume
        $paymentsCount24h = 0;
        if (Schema::hasTable('payments')) {
            $paymentsCount24h = DB::table('payments')->where('created_at', '>=', $oneDayAgo)->count();
        }

        // 6. Last Backup / Restore Verification
        $lastBackupVerification = \App\Models\Setting::get('last_backup_restore_verified_at');
        $lastRpoSeconds = \App\Models\Setting::get('last_backup_rpo_seconds');
        $lastRtoMs = \App\Models\Setting::get('last_backup_rto_ms');

        return [
            'payments' => [
                'success_rate_24h' => $rate24h,
                'total_orders_24h' => $total24h,
                'paid_orders_24h'  => $paid24h,
                'revenue_24h'      => $revenue24h,
                'success_rate_7d'  => $rate7d,
                'total_orders_7d'  => $total7d,
                'paid_orders_7d'   => $paid7d,
                'payment_records_24h' => $paymentsCount24h,
            ],
            'queue' => [
                'pending_jobs' => $pendingJobs,
                'failed_jobs'  => $failedJobs,
                'status'       => $failedJobs > 0 ? 'attention' : 'healthy',
            ],
            'stock_reservations' => [
                'active'  => $activeReservations,
                'expired' => $expiredReservations,
            ],
            'verification' => [
                'pending_documents' => $pendingDocuments,
                'vendors_by_trust'  => [
                    'level_0' => $vendorsByTrust[0] ?? 0,
                    'level_1' => $vendorsByTrust[1] ?? 0,
                    'level_2' => $vendorsByTrust[2] ?? 0,
                    'level_3' => $vendorsByTrust[3] ?? 0,
                ],
            ],
            'disaster_recovery' => [
                'last_verified_at' => $lastBackupVerification,
                'rpo_seconds'      => $lastRpoSeconds ? (int) $lastRpoSeconds : null,
                'rto_ms'           => $lastRtoMs ? (int) $lastRtoMs : null,
            ],
        ];
    }
}