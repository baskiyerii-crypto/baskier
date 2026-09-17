<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Domain\TrustLevel;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TrustBadgeService
{
    /**
     * Calculate the deterministic trust level for a given vendor.
     *
     * Rules:
     * - Level 0: Unverified contact or active sanctions.
     * - Level 1: Contact verified (both email_verified_at and phone_verified_at).
     * - Level 2: Level 1 + approved non-expired documents appropriate for the vendor track:
     *            Physical track: tax_plate or company_registration.
     *            Freelancer track: diploma, certificate, or portfolio_accreditation.
     *            CRITICAL: tax_plate does NOT count as a freelancer professional credential.
     * - Level 3: Level 2 + at least 5 completed orders (delivered/completed) +
     *            rating_average >= 4.5 + past 12m cancellation/dispute rate <= 2% +
     *            no active sanctions.
     */
    public function calculateTrustLevel(Vendor $vendor): int
    {
        // 0. Active Sanctions Check
        if ($vendor->is_suspended || $vendor->contract_suspended_at !== null) {
            return TrustLevel::LEVEL_0;
        }

        if (($vendor->verification_status ?? null) === 'rejected') {
            return TrustLevel::LEVEL_0;
        }

        // 1. Level 1 Verification: Email + Phone
        $user = $vendor->user;
        if (! $user || ! $user->email_verified_at || ! $user->phone_verified_at) {
            return TrustLevel::LEVEL_0;
        }

        // 2. Level 2 Verification: Track-Appropriate Approved Documents
        $today = Carbon::today();

        $hasPhysical = $vendor->hasPhysicalTrack();
        $hasFreelancer = $vendor->hasFreelancerTrack();

        // Physical track check: tax_plate or company_registration
        if ($hasPhysical) {
            $hasPhysicalDoc = $vendor->documents()
                ->whereIn('document_type', ['tax_plate', 'company_registration'])
                ->where('status', 'approved')
                ->where(function ($query) use ($today) {
                    $query->whereNull('expires_at')
                        ->orWhereDate('expires_at', '>=', $today);
                })
                ->exists();

            if (! $hasPhysicalDoc) {
                return TrustLevel::LEVEL_1;
            }
        }

        // Freelancer track check: diploma, certificate, or portfolio_accreditation
        // NOTE: tax_plate explicitly does NOT satisfy freelancer qualification
        if ($hasFreelancer) {
            $hasFreelancerDoc = $vendor->documents()
                ->whereIn('document_type', ['diploma', 'certificate', 'portfolio_accreditation'])
                ->where('status', 'approved')
                ->where(function ($query) use ($today) {
                    $query->whereNull('expires_at')
                        ->orWhereDate('expires_at', '>=', $today);
                })
                ->exists();

            if (! $hasFreelancerDoc) {
                return TrustLevel::LEVEL_1;
            }
        }

        // 3. Level 3 Verification: Performance, Rating & Dispute Metrics
        // Minimum 5 completed orders
        $completedOrdersCount = $vendor->orders()
            ->whereIn('status', [OrderStatus::DELIVERED, OrderStatus::COMPLETED])
            ->count();

        if ($completedOrdersCount < 5) {
            return TrustLevel::LEVEL_2;
        }

        // Rating average >= 4.5
        $rating = (float) ($vendor->rating_average ?? 0);
        if ($rating < 4.5) {
            return TrustLevel::LEVEL_2;
        }

        // Past 12 months cancellation / dispute rate <= 2%
        $twelveMonthsAgo = Carbon::now()->subMonths(12);
        $totalOrdersLast12m = $vendor->orders()
            ->where('created_at', '>=', $twelveMonthsAgo)
            ->count();

        if ($totalOrdersLast12m > 0) {
            $problematicOrdersLast12m = $vendor->orders()
                ->where('created_at', '>=', $twelveMonthsAgo)
                ->whereIn('status', [OrderStatus::CANCELLED, OrderStatus::DISPUTED])
                ->count();

            $disputeRate = $problematicOrdersLast12m / $totalOrdersLast12m;
            if ($disputeRate > 0.02) {
                return TrustLevel::LEVEL_2;
            }
        }

        return TrustLevel::LEVEL_3;
    }

    /**
     * Recalculate trust level and persist to vendor.
     */
    public function recalculateAndSave(Vendor $vendor): int
    {
        $oldLevel = (int) $vendor->trust_level;
        $newLevel = $this->calculateTrustLevel($vendor);

        $updates = ['trust_level' => $newLevel];

        if ($newLevel >= TrustLevel::LEVEL_2 && $vendor->verification_status !== 'verified') {
            $updates['verification_status'] = 'verified';
        } elseif ($newLevel < TrustLevel::LEVEL_2 && $vendor->verification_status === 'verified') {
            $updates['verification_status'] = 'pending';
        }

        $vendor->updateQuietly($updates);

        if ($oldLevel !== $newLevel) {
            Log::info("Vendor #{$vendor->id} trust level transitioned from {$oldLevel} to {$newLevel}.", [
                'vendor_id' => $vendor->id,
                'old_level' => $oldLevel,
                'new_level' => $newLevel,
            ]);
        }

        return $newLevel;
    }

    /**
     * Get detailed checklist breakdown for display in UI.
     *
     * @return array<string, mixed>
     */
    public function getCriteriaBreakdown(Vendor $vendor): array
    {
        $today = Carbon::today();
        $user = $vendor->user;

        $emailVerified = (bool) ($user?->email_verified_at !== null);
        $phoneVerified = (bool) ($user?->phone_verified_at !== null);
        $level1Passed = $emailVerified && $phoneVerified && ! $vendor->is_suspended;

        $hasPhysical = $vendor->hasPhysicalTrack();
        $hasFreelancer = $vendor->hasFreelancerTrack();

        $physicalDocApproved = $hasPhysical
            ? $vendor->documents()
                ->whereIn('document_type', ['tax_plate', 'company_registration'])
                ->where('status', 'approved')
                ->where(function ($q) use ($today) {
                    $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', $today);
                })->exists()
            : null;

        $freelancerDocApproved = $hasFreelancer
            ? $vendor->documents()
                ->whereIn('document_type', ['diploma', 'certificate', 'portfolio_accreditation'])
                ->where('status', 'approved')
                ->where(function ($q) use ($today) {
                    $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', $today);
                })->exists()
            : null;

        $level2Passed = $level1Passed
            && ($physicalDocApproved === null || $physicalDocApproved === true)
            && ($freelancerDocApproved === null || $freelancerDocApproved === true);

        $completedOrdersCount = $vendor->orders()
            ->whereIn('status', [OrderStatus::DELIVERED, OrderStatus::COMPLETED])
            ->count();

        $rating = (float) ($vendor->rating_average ?? 0);

        $twelveMonthsAgo = Carbon::now()->subMonths(12);
        $totalOrdersLast12m = $vendor->orders()->where('created_at', '>=', $twelveMonthsAgo)->count();
        $problematicOrders = $totalOrdersLast12m > 0
            ? $vendor->orders()->where('created_at', '>=', $twelveMonthsAgo)->whereIn('status', [OrderStatus::CANCELLED, OrderStatus::DISPUTED])->count()
            : 0;
        $disputeRate = $totalOrdersLast12m > 0 ? ($problematicOrders / $totalOrdersLast12m) : 0.0;

        $level3Passed = $level2Passed
            && $completedOrdersCount >= 5
            && $rating >= 4.5
            && $disputeRate <= 0.02
            && ! $vendor->is_suspended
            && $vendor->contract_suspended_at === null;

        return [
            'current_level' => (int) $vendor->trust_level,
            'is_suspended' => (bool) $vendor->is_suspended,
            'suspension_reason' => $vendor->suspension_reason,
            'level_1' => [
                'passed' => $level1Passed,
                'email_verified' => $emailVerified,
                'phone_verified' => $phoneVerified,
            ],
            'level_2' => [
                'passed' => $level2Passed,
                'has_physical' => $hasPhysical,
                'physical_doc_approved' => $physicalDocApproved,
                'has_freelancer' => $hasFreelancer,
                'freelancer_doc_approved' => $freelancerDocApproved,
            ],
            'level_3' => [
                'passed' => $level3Passed,
                'completed_orders_count' => $completedOrdersCount,
                'rating_average' => $rating,
                'total_orders_last_12m' => $totalOrdersLast12m,
                'problematic_orders' => $problematicOrders,
                'dispute_rate_percent' => round($disputeRate * 100, 2),
                'no_sanctions' => ! $vendor->is_suspended && $vendor->contract_suspended_at === null,
            ],
        ];
    }
}
