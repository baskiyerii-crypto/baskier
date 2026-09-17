<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\OohInventory;
use App\Models\OohOccupancy;
use App\Models\OohPlan;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorMember;
use App\Services\OutdoorPlanService;
use App\Services\OutdoorProofService;
use App\Services\OutdoorStaffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OutdoorVerticalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Vendor}
     */
    private function outdoorVendor(string $name = 'Outdoor Co'): array
    {
        $user = User::factory()->create(['role' => 'vendor', 'phone' => '05550001111']);
        $vendor = Vendor::factory()->create([
            'user_id' => $user->id,
            'name' => $name,
            'phone' => '02120001111',
            'is_active' => true,
            'outdoor_enabled' => true,
            'outdoor_expires_at' => now()->addMonth(),
            'registration_tracks' => ['outdoor'],
        ]);
        $user->update(['vendor_id' => $vendor->id]);
        app(OutdoorStaffService::class)->ensureOwner($vendor);

        return [$user, $vendor];
    }

    private function outdoorCategory(): Category
    {
        return Category::factory()->create([
            'name' => 'Billboard-OOH-TEST',
            'channel' => Category::CHANNEL_OUTDOOR,
            'is_active' => true,
        ]);
    }

    private function publishFace(Vendor $vendor, Category $category, string $title, float $lat = 41.0, float $lng = 29.0): OohInventory
    {
        return OohInventory::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => $title,
            'lat' => $lat,
            'lng' => $lng,
            'city' => 'İstanbul',
            'district' => 'Kadıköy',
            'list_price' => 1000,
            'price_unit' => OohInventory::UNIT_MONTH,
            'permit_no' => 'PRM-'.$vendor->id,
            'proof_radius_m' => 75,
            'status' => OohInventory::STATUS_PUBLISHED,
        ]);
    }

    public function test_catalog_lists_published_faces_only(): void
    {
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $live = $this->publishFace($vendor, $cat, 'Canlı Pano');
        OohInventory::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Taslak Pano',
            'lat' => 41,
            'lng' => 29,
            'status' => OohInventory::STATUS_DRAFT,
        ]);

        $this->get(route('outdoor.index'))
            ->assertOk()
            ->assertSee('Canlı Pano')
            ->assertDontSee('Taslak Pano');

        $this->getJson('/api/v1/ooh-inventories')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['title' => 'Canlı Pano']);

        $this->assertTrue($live->isPublished());
    }

    public function test_multi_vendor_plan_splits_quotes_and_accept_creates_orders_with_consent(): void
    {
        [$aUser, $aVendor] = $this->outdoorVendor('Ahmet Outdoor');
        [$bUser, $bVendor] = $this->outdoorVendor('Mehmet Outdoor');
        $cat = $this->outdoorCategory();
        $faceA = $this->publishFace($aVendor, $cat, 'Pano A');
        $faceB = $this->publishFace($bVendor, $cat, 'Pano B');
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '05559998877']);

        $start = now()->addDays(10)->toDateString();
        $end = now()->addDays(20)->toDateString();

        $plan = app(OutdoorPlanService::class)->submit($customer, [
            ['inventory_id' => $faceA->id, 'starts_on' => $start, 'ends_on' => $end],
            ['inventory_id' => $faceB->id, 'starts_on' => $start, 'ends_on' => $end],
        ]);

        $this->assertSame(2, $plan->vendorRequests()->count());
        $this->assertSame(2, OohOccupancy::query()->where('kind', OohOccupancy::KIND_HOLD)->count());

        $reqA = $plan->vendorRequests()->where('vendor_id', $aVendor->id)->firstOrFail();
        $reqB = $plan->vendorRequests()->where('vendor_id', $bVendor->id)->firstOrFail();

        $quoteA = app(OutdoorPlanService::class)->quote($reqA, $aUser, 1500, 'ok', true);
        $quoteB = app(OutdoorPlanService::class)->quote($reqB, $bUser, 2200, 'ok', true);

        $this->actingAs($customer)->post(route('customer.outdoor.plans.accept', [$plan, $reqA, $quoteA]))
            ->assertSessionHasErrors();

        $this->actingAs($customer)->post(route('customer.outdoor.plans.accept', [$plan, $reqA, $quoteA]), [
            'share_my_contact' => '1',
            'accept_vendor_contact' => '1',
            'accept_consent' => '1',
            'accept_consent_scrolled_at' => now()->toIso8601String(),
        ])->assertRedirect();

        $this->assertDatabaseHas('contact_shares', [
            'customer_user_id' => $customer->id,
            'vendor_id' => $aVendor->id,
            'context_type' => 'ooh_vendor_request',
            'context_id' => $reqA->id,
        ]);
        $this->assertSame(1, Order::query()->where('type', 'outdoor')->count());
        $order = Order::query()->where('type', 'outdoor')->first();
        $this->assertSame(0.0, (float) $order->commission_amount);

        app(OutdoorPlanService::class)->accept($reqB->fresh(), $quoteB->fresh(), $customer, true, true);
        $this->assertSame(2, Order::query()->where('type', 'outdoor')->count());
        $this->assertSame(2, OohOccupancy::query()->where('kind', OohOccupancy::KIND_BOOKED)->count());
        $this->assertSame(OohPlan::STATUS_ACCEPTED, $plan->fresh()->status);
    }

    public function test_overlapping_hold_is_rejected(): void
    {
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $face = $this->publishFace($vendor, $cat, 'Tek Pano');
        $c1 = User::factory()->create(['role' => 'customer']);
        $c2 = User::factory()->create(['role' => 'customer']);
        $start = now()->addMonth()->toDateString();
        $end = now()->addMonth()->addDays(5)->toDateString();
        app(OutdoorPlanService::class)->submit($c1, [
            ['inventory_id' => $face->id, 'starts_on' => $start, 'ends_on' => $end],
        ]);
        $this->expectException(\RuntimeException::class);
        app(OutdoorPlanService::class)->submit($c2, [
            ['inventory_id' => $face->id, 'starts_on' => $start, 'ends_on' => $end],
        ]);
    }

    public function test_proof_outside_radius_is_invalid_and_does_not_change_calendar(): void
    {
        Storage::fake('public');
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $face = $this->publishFace($vendor, $cat, 'GPS Pano', 41.0, 29.0);
        $occ = OohOccupancy::create([
            'ooh_inventory_id' => $face->id,
            'starts_on' => now()->addDay()->toDateString(),
            'ends_on' => now()->addDays(3)->toDateString(),
            'kind' => OohOccupancy::KIND_BOOKED,
        ]);
        $proof = app(OutdoorProofService::class)->submit(
            $occ,
            $user,
            UploadedFile::fake()->image('pop.jpg'),
            0.0,
            0.0
        );
        $this->assertFalse($proof->is_valid);
        $this->assertSame(OohOccupancy::KIND_BOOKED, $occ->fresh()->kind);
    }

    public function test_owner_can_claim_foreign_listing_and_field_staff_cannot_manage_inventory(): void
    {
        [$ownerUser, $owner] = $this->outdoorVendor('Sahip');
        [$otherUser, $other] = $this->outdoorVendor('Baska');
        $cat = $this->outdoorCategory();
        $face = $this->publishFace($other, $cat, 'Sahte İlan');

        $this->actingAs($ownerUser)->post(route('vendor.outdoor.claims.store'), [
            'ooh_inventory_id' => $face->id,
            'evidence' => 'Ruhsat bende',
            'permit_no' => 'PRM-'.$owner->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('ooh_inventory_claims', [
            'reporter_vendor_id' => $owner->id,
            'ooh_inventory_id' => $face->id,
        ]);

        $field = User::factory()->create(['role' => 'vendor', 'vendor_id' => $owner->id]);
        VendorMember::create([
            'vendor_id' => $owner->id,
            'user_id' => $field->id,
            'staff_role' => VendorMember::ROLE_FIELD,
        ]);
        $this->actingAs($field)->get(route('vendor.outdoor.inventories.create'))->assertForbidden();
    }

    public function test_outdoor_category_does_not_leak_into_tabela_rfq(): void
    {
        $this->outdoorCategory();
        $this->get(route('quote-requests.create', ['type' => 'tabela']))
            ->assertOk()
            ->assertDontSee('Billboard-OOH-TEST');
    }

    public function test_notify_upcoming_command_runs(): void
    {
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $face = $this->publishFace($vendor, $cat, 'Yarınki iş');
        OohOccupancy::create([
            'ooh_inventory_id' => $face->id,
            'starts_on' => now()->addDay()->toDateString(),
            'ends_on' => now()->addDays(3)->toDateString(),
            'kind' => OohOccupancy::KIND_BOOKED,
        ]);

        $this->artisan('ooh:notify-upcoming')->assertSuccessful();
    }

    public function test_catalog_has_district_filter_and_detail_map(): void
    {
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $face = $this->publishFace($vendor, $cat, 'Haritalı Pano');

        $this->get(route('outdoor.index'))
            ->assertOk()
            ->assertSee('Tüm ilçeler', false)
            ->assertSee('Tüm ülkeler', false);

        $this->get(route('outdoor.show', $face->slug))
            ->assertOk()
            ->assertSee('openstreetmap.org', false)
            ->assertDontSee('Taslak Pano');
    }

    public function test_duplicate_permit_gps_blocks_review_but_admin_can_still_publish(): void
    {
        [$aUser, $aVendor] = $this->outdoorVendor('A');
        [$bUser, $bVendor] = $this->outdoorVendor('B');
        $cat = $this->outdoorCategory();
        $live = $this->publishFace($aVendor, $cat, 'Orijinal', 41.125, 29.125);
        $live->permit_no = 'AYNI-1';
        $live->save();

        $draft = OohInventory::create([
            'vendor_id' => $bVendor->id,
            'category_id' => $cat->id,
            'title' => 'Kopya Pano',
            'lat' => 41.125,
            'lng' => 29.125,
            'permit_no' => 'AYNI-1',
            'status' => OohInventory::STATUS_DRAFT,
        ]);

        try {
            app(\App\Services\OutdoorInventoryService::class)->submitForReview($draft, $bUser);
            $this->fail('Çift ilan incelemesi geçmemeliydi.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Çift ilan', $e->getMessage());
        }

        app(\App\Services\OutdoorInventoryService::class)->publish($draft);
        $this->assertTrue($draft->fresh()->isPublished());
    }

    public function test_api_vendor_can_post_inventory_and_documents_show_outdoor_permit(): void
    {
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/ooh-inventories', [
            'title' => 'API Pano',
            'category_id' => $cat->id,
            'lat' => 40.99,
            'lng' => 29.11,
            'price_unit' => 'month',
            'permit_no' => 'API-9',
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'API Pano');

        $this->actingAs($user)->get(route('vendor.documents.index'))
            ->assertOk()
            ->assertSee('value="outdoor_permit"', false)
            ->assertSee('value="tax_plate"', false);
    }

    public function test_decline_and_hold_expiry_notify_planner(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        [$vendorUser, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $face = $this->publishFace($vendor, $cat, 'Hold Pano');
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '05551112233']);
        $start = now()->addDays(8)->toDateString();
        $end = now()->addDays(12)->toDateString();
        $plan = app(OutdoorPlanService::class)->submit($customer, [
            ['inventory_id' => $face->id, 'starts_on' => $start, 'ends_on' => $end],
        ]);
        $req = $plan->vendorRequests()->firstOrFail();
        app(OutdoorPlanService::class)->decline($req, $vendorUser);

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $customer,
            \App\Notifications\PlatformNotification::class,
            fn ($n) => str_contains($n->title, 'reddedildi')
        );

        $plan2 = app(OutdoorPlanService::class)->submit($customer, [
            ['inventory_id' => $face->id, 'starts_on' => $start, 'ends_on' => $end],
        ]);
        OohOccupancy::query()->where('kind', OohOccupancy::KIND_HOLD)->update(['expires_at' => now()->subMinute()]);
        $deleted = app(\App\Services\OutdoorOccupancyService::class)->expireHolds();
        $this->assertGreaterThan(0, $deleted);
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $customer,
            \App\Notifications\PlatformNotification::class,
            fn ($n) => str_contains($n->title, 'hold')
        );
    }

    public function test_catalog_filters_by_country_and_remembers_typed_places(): void
    {
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $this->publishFace($vendor, $cat, 'Istanbul Pano');
        OohInventory::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Berlin Billboard',
            'slug' => 'berlin-billboard-test',
            'lat' => 52.52,
            'lng' => 13.405,
            'country_code' => 'DE',
            'city' => 'Berlin',
            'district' => 'Mitte',
            'list_price' => 800,
            'price_unit' => OohInventory::UNIT_MONTH,
            'status' => OohInventory::STATUS_PUBLISHED,
        ]);

        $this->get(route('outdoor.index', ['ulke' => 'DE']))
            ->assertOk()
            ->assertSee('Berlin Billboard')
            ->assertDontSee('Istanbul Pano');

        $this->getJson('/api/v1/ooh-inventories?country_code=DE')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Berlin Billboard', 'country_code' => 'DE'])
            ->assertJsonMissing(['title' => 'Istanbul Pano']);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/ooh-inventories', [
            'title' => 'Hamburg Face',
            'category_id' => $cat->id,
            'lat' => 53.55,
            'lng' => 9.99,
            'price_unit' => 'month',
            'country_code' => 'DE',
            'city' => 'Hamburg',
            'district' => 'Altona',
        ])->assertCreated()
            ->assertJsonPath('data.country_code', 'DE')
            ->assertJsonPath('data.city', 'Hamburg');

        $this->assertDatabaseHas('world_places', [
            'country_code' => 'DE',
            'city' => 'Hamburg',
            'district' => 'Altona',
        ]);

        $this->getJson('/api/v1/geography/countries')
            ->assertOk()
            ->assertJsonFragment(['code' => 'DE']);

        $this->getJson('/api/v1/geography/places?country=DE')
            ->assertOk();
        $this->assertContains('Hamburg', $this->getJson('/api/v1/geography/places?country=DE')->json('data.cities'));
    }
}
