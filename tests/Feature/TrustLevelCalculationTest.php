<?php

namespace Tests\Feature;

use App\Domain\OrderStatus;
use App\Domain\TrustLevel;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Services\TrustBadgeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustLevelCalculationTest extends TestCase
{
    use RefreshDatabase;

    private TrustBadgeService $trustBadgeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trustBadgeService = app(TrustBadgeService::class);
    }

    private function createVendor(array $userAttrs = [], array $vendorAttrs = []): array
    {
        $user = User::factory()->create(array_merge([
            'role' => 'vendor',
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ], $userAttrs));

        $vendor = Vendor::factory()->create(array_merge([
            'user_id' => $user->id,
            'registration_tracks' => ['physical_products'],
            'is_active' => true,
            'trust_level' => 0,
        ], $vendorAttrs));

        $user->forceFill(['vendor_id' => $vendor->id])->save();

        return [$user, $vendor];
    }

    public function test_vendor_without_verified_email_or_phone_is_level_0(): void
    {
        // Unverified email and phone
        [$user, $vendor] = $this->createVendor();
        $this->assertEquals(TrustLevel::LEVEL_0, $this->trustBadgeService->calculateTrustLevel($vendor));

        // Only email verified
        $user->update(['email_verified_at' => now()]);
        $this->assertEquals(TrustLevel::LEVEL_0, $this->trustBadgeService->calculateTrustLevel($vendor));

        // Only phone verified
        $user->update(['email_verified_at' => null, 'phone_verified_at' => now()]);
        $this->assertEquals(TrustLevel::LEVEL_0, $this->trustBadgeService->calculateTrustLevel($vendor));
    }

    public function test_vendor_with_verified_email_and_phone_reaches_level_1(): void
    {
        [$user, $vendor] = $this->createVendor([
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $level = $this->trustBadgeService->recalculateAndSave($vendor);
        $this->assertEquals(TrustLevel::LEVEL_1, $level);
        $this->assertEquals(TrustLevel::LEVEL_1, $vendor->fresh()->trust_level);
    }

    public function test_physical_vendor_with_approved_tax_plate_reaches_level_2(): void
    {
        [$user, $vendor] = $this->createVendor([
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ], [
            'registration_tracks' => ['physical_products'],
        ]);

        // Add approved tax plate
        VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'path' => 'doc.pdf',
            'status' => 'approved',
            'expires_at' => now()->addYear(),
        ]);

        $level = $this->trustBadgeService->recalculateAndSave($vendor);
        $this->assertEquals(TrustLevel::LEVEL_2, $level);
    }

    public function test_freelancer_vendor_with_tax_plate_does_NOT_reach_level_2(): void
    {
        [$user, $vendor] = $this->createVendor([
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ], [
            'registration_tracks' => ['freelancer'],
        ]);

        // Add approved tax plate (CRITICAL: this does NOT satisfy freelancer qualification)
        VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'path' => 'tax.pdf',
            'status' => 'approved',
            'expires_at' => now()->addYear(),
        ]);

        $level = $this->trustBadgeService->calculateTrustLevel($vendor);
        $this->assertEquals(TrustLevel::LEVEL_1, $level, 'Tax plate must not count as freelancer qualification.');
    }

    public function test_freelancer_vendor_with_approved_diploma_reaches_level_2(): void
    {
        [$user, $vendor] = $this->createVendor([
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ], [
            'registration_tracks' => ['freelancer'],
        ]);

        // Add approved diploma
        VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'diploma',
            'path' => 'diploma.pdf',
            'status' => 'approved',
            'expires_at' => null, // permanent
        ]);

        $level = $this->trustBadgeService->recalculateAndSave($vendor);
        $this->assertEquals(TrustLevel::LEVEL_2, $level);
    }

    public function test_expired_document_drops_vendor_from_level_2_to_level_1(): void
    {
        [$user, $vendor] = $this->createVendor([
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ], [
            'registration_tracks' => ['physical_products'],
        ]);

        // Document that expired yesterday
        VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'path' => 'tax.pdf',
            'status' => 'approved',
            'expires_at' => Carbon::yesterday(),
        ]);

        $level = $this->trustBadgeService->calculateTrustLevel($vendor);
        $this->assertEquals(TrustLevel::LEVEL_1, $level, 'Expired document must not grant Level 2.');
    }

    public function test_level_3_criteria_requirements(): void
    {
        [$user, $vendor] = $this->createVendor([
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ], [
            'registration_tracks' => ['physical_products'],
            'rating_average' => 4.9,
            'reviews_count' => 10,
        ]);

        // Level 2 document
        VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'path' => 'tax.pdf',
            'status' => 'approved',
            'expires_at' => now()->addMonths(6),
        ]);

        // Only 4 completed orders (need 5)
        for ($i = 1; $i <= 4; $i++) {
            Order::create([
                'order_number' => 'ORD-TEST-'.$i,
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'status' => OrderStatus::DELIVERED,
                'subtotal' => 100,
                'commission_rate' => 10,
                'commission_amount' => 10,
                'vendor_amount' => 90,
            ]);
        }

        $this->assertEquals(TrustLevel::LEVEL_2, $this->trustBadgeService->calculateTrustLevel($vendor));

        // Add 5th completed order -> satisfies order count!
        Order::create([
            'order_number' => 'ORD-TEST-5',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::COMPLETED,
            'subtotal' => 100,
            'commission_rate' => 10,
            'commission_amount' => 10,
            'vendor_amount' => 90,
        ]);

        $this->assertEquals(TrustLevel::LEVEL_3, $this->trustBadgeService->calculateTrustLevel($vendor));

        // Rating drops below 4.5 -> drops back to Level 2
        $vendor->update(['rating_average' => 4.2]);
        $this->assertEquals(TrustLevel::LEVEL_2, $this->trustBadgeService->calculateTrustLevel($vendor));

        // Restore rating to 4.8
        $vendor->update(['rating_average' => 4.8]);
        $this->assertEquals(TrustLevel::LEVEL_3, $this->trustBadgeService->calculateTrustLevel($vendor));

        // High dispute/cancellation rate in past 12m (> 2%)
        // 5 successful + 1 cancelled = 1/6 = 16.6% dispute rate > 2%
        Order::create([
            'order_number' => 'ORD-CANCELLED-1',
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::CANCELLED,
            'subtotal' => 100,
            'commission_rate' => 10,
            'commission_amount' => 10,
            'vendor_amount' => 90,
        ]);

        $this->assertEquals(TrustLevel::LEVEL_2, $this->trustBadgeService->calculateTrustLevel($vendor));
    }

    public function test_sanctions_and_suspension_drop_vendor_to_level_0(): void
    {
        [$user, $vendor] = $this->createVendor([
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ], [
            'registration_tracks' => ['physical_products'],
            'trust_level' => TrustLevel::LEVEL_2,
        ]);

        VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'path' => 'tax.pdf',
            'status' => 'approved',
        ]);

        $this->assertEquals(TrustLevel::LEVEL_2, $this->trustBadgeService->calculateTrustLevel($vendor));

        // Admin suspends vendor
        $vendor->update([
            'is_suspended' => true,
            'suspension_reason' => 'Şüpheli hareketler',
        ]);

        $this->assertEquals(TrustLevel::LEVEL_0, $this->trustBadgeService->calculateTrustLevel($vendor));

        // Admin unsuspends
        $vendor->update([
            'is_suspended' => false,
            'suspension_reason' => null,
        ]);

        $this->assertEquals(TrustLevel::LEVEL_2, $this->trustBadgeService->calculateTrustLevel($vendor));
    }

    public function test_review_creation_triggers_trust_level_recalculation(): void
    {
        [$user, $vendor] = $this->createVendor([
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ], [
            'registration_tracks' => ['physical_products'],
            'rating_average' => 4.0, // starts below 4.5
        ]);

        VendorDocument::create([
            'vendor_id' => $vendor->id,
            'document_type' => 'tax_plate',
            'path' => 'tax.pdf',
            'status' => 'approved',
        ]);

        // 5 delivered orders
        for ($i = 1; $i <= 5; $i++) {
            $order = Order::create([
                'order_number' => 'ORD-REV-'.$i,
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'status' => OrderStatus::DELIVERED,
                'subtotal' => 100,
                'commission_rate' => 10,
                'commission_amount' => 10,
                'vendor_amount' => 90,
            ]);
        }

        $this->assertEquals(TrustLevel::LEVEL_2, $this->trustBadgeService->recalculateAndSave($vendor));

        // Add 5-star reviews
        Review::create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Harika kaliteli baskı!',
        ]);

        $vendor->refresh();
        $this->assertEquals(5.0, (float) $vendor->rating_average);
        $this->assertEquals(TrustLevel::LEVEL_3, $vendor->trust_level);
    }
}
