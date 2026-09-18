<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\OohInventory;
use App\Models\OohKgmTraffic;
use App\Models\OohOccupancy;
use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use App\Models\OohPlan;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorMember;
use App\Models\Setting;
use App\Services\OutdoorPlanService;
use App\Services\OutdoorProofService;
use App\Services\OutdoorReverseGeocodeService;
use App\Services\OutdoorStaffService;
use Database\Seeders\WorldPlacesIso3166Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
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
        $user = User::factory()->create(['role' => 'vendor']);
        $phone = '90555'.str_pad((string) $user->id, 7, '0', STR_PAD_LEFT);
        $user->update(['phone' => $phone]);
        $vendor = Vendor::factory()->create([
            'user_id' => $user->id,
            'name' => $name,
            'phone' => $phone,
            'is_active' => true,
            'outdoor_enabled' => true,
            'outdoor_expires_at' => now()->addMonth(),
            'registration_tracks' => ['outdoor'],
            'outdoor_role' => Vendor::OUTDOOR_ROLE_OWNER,
            'owner_kind' => Vendor::OWNER_KIND_COMPANY,
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

        $this->actingAs($ownerUser)->post(route('outdoor-panel.claims.store'), [
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
        $this->actingAs($field)->get(route('outdoor-panel.inventories.create'))->assertForbidden();
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

        $this->actingAs($user)->get(route('outdoor-panel.documents.index'))
            ->assertOk()
            ->assertSee('Doğrulamaya başla')
            ->assertSee('value="outdoor_permit"', false)
            ->assertSee('value="tax_plate"', false)
            ->assertDontSee('Ürünlerim', false);
        $this->actingAs($user)->get(route('vendor.documents.index'))
            ->assertRedirect(route('outdoor-panel.documents.index'));
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

        $this->getJson('/api/v1/geography/places?country=DE')->assertOk();
        $cityNames = collect($this->getJson('/api/v1/geography/places?country=DE')->json('data.cities'))
            ->pluck('name');
        $this->assertTrue($cityNames->contains('Hamburg'));
    }

    /**
     * @return array{0: User, 1: Vendor}
     */
    private function outdoorAgency(string $name = 'Ajans Co'): array
    {
        [$user, $vendor] = $this->outdoorVendor($name);
        $vendor->forceFill([
            'outdoor_role' => Vendor::OUTDOOR_ROLE_AGENCY,
            'owner_kind' => null,
        ])->save();

        return [$user, $vendor->fresh()];
    }

    /**
     * @return array<string, mixed>
     */
    private function registerVendorPayload(string $email, array $extra = []): array
    {
        $now = now()->toIso8601String();

        return array_merge([
            'name' => 'Açık Hava Test',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'vendor',
            'registration_tracks' => ['outdoor'],
            'accept_terms' => '1',
            'accept_privacy' => '1',
            'accept_terms_scrolled_at' => $now,
            'accept_privacy_scrolled_at' => $now,
            'accept_vendor_agreement' => '1',
            'accept_vendor_agreement_scrolled_at' => $now,
        ], $extra);
    }

    public function test_agency_register_without_permit_and_municipality_uses_authority_file(): void
    {
        Storage::fake('private');

        $this->post(route('register'), $this->registerVendorPayload('ajans-reg@test.com', [
            'outdoor_role' => 'agency',
            'company_name' => 'Ajans A.S.',
            'tax_office' => 'Kadıköy',
            'tax_number' => '1111111111',
            'tax_plate' => UploadedFile::fake()->image('vergi.jpg'),
        ]))->assertRedirect(route('outdoor-panel.dashboard'));

        $agency = Vendor::query()->where('email', 'ajans-reg@test.com')->firstOrFail();
        $this->assertSame(Vendor::OUTDOOR_ROLE_AGENCY, $agency->outdoor_role);
        $this->assertDatabaseMissing('vendor_documents', [
            'vendor_id' => $agency->id,
            'document_type' => 'outdoor_permit',
        ]);

        $this->post(route('logout'));

        $this->post(route('register'), $this->registerVendorPayload('belediye-reg@test.com', [
            'outdoor_role' => 'owner',
            'owner_kind' => 'municipality',
            'company_name' => 'Kadıköy Belediyesi',
            'tax_number' => '2222222222',
            'municipality_authority' => UploadedFile::fake()->image('yetki.jpg'),
        ]))->assertRedirect(route('outdoor-panel.dashboard'));

        $muni = Vendor::query()->where('email', 'belediye-reg@test.com')->firstOrFail();
        $this->assertSame(Vendor::OWNER_KIND_MUNICIPALITY, $muni->owner_kind);
        $this->assertDatabaseMissing('vendor_documents', [
            'vendor_id' => $muni->id,
            'document_type' => 'tax_plate',
        ]);
        $this->assertDatabaseHas('vendor_documents', [
            'vendor_id' => $muni->id,
            'document_type' => 'municipality_authority',
        ]);

        $this->post(route('logout'));

        $this->post(route('register'), $this->registerVendorPayload('sirket-reg@test.com', [
            'outdoor_role' => 'owner',
            'owner_kind' => 'company',
            'company_name' => 'Mecra Ltd',
            'tax_office' => 'Beşiktaş',
            'tax_number' => '3333333333',
            'tax_plate' => UploadedFile::fake()->image('levha.jpg'),
        ]))->assertRedirect(route('outdoor-panel.dashboard'));

        $company = Vendor::query()->where('email', 'sirket-reg@test.com')->firstOrFail();
        $this->assertSame(Vendor::OUTDOOR_ROLE_OWNER, $company->outdoor_role);
        $this->assertSame(Vendor::OWNER_KIND_COMPANY, $company->owner_kind);
        $this->assertDatabaseMissing('vendor_documents', [
            'vendor_id' => $company->id,
            'document_type' => 'outdoor_permit',
        ]);
    }

    public function test_nn_representation_and_plan_fans_out_to_owner_and_agency(): void
    {
        [$ownerAUser, $ownerA] = $this->outdoorVendor('Sahip A');
        [$ownerBUser, $ownerB] = $this->outdoorVendor('Sahip B');
        [$agencyUser, $agency] = $this->outdoorAgency('Ajans 1');
        [$agency2User, $agency2] = $this->outdoorAgency('Ajans 2');
        $reps = app(\App\Services\OutdoorRepresentationService::class);

        $bindA = $reps->invite($ownerA, $agencyUser->email);
        $reps->accept($bindA, $agency);
        $bindB = $reps->invite($agency, $ownerBUser->email);
        $reps->accept($bindB, $ownerB);
        $bindA2 = $reps->invite($ownerA, $agency2User->email);
        $reps->accept($bindA2, $agency2);

        $this->assertCount(2, $reps->representedOwnerIds($agency));
        $this->assertSame(2, \App\Models\OohRepresentation::query()
            ->where('owner_vendor_id', $ownerA->id)
            ->where('status', \App\Models\OohRepresentation::STATUS_ACTIVE)
            ->count());

        $cat = $this->outdoorCategory();
        $face = $this->publishFace($ownerA, $cat, 'Ajanslı Pano');
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '05550009988']);
        $start = now()->addDays(14)->toDateString();
        $end = now()->addDays(20)->toDateString();
        $plan = app(OutdoorPlanService::class)->submit($customer, [
            ['inventory_id' => $face->id, 'starts_on' => $start, 'ends_on' => $end],
        ]);

        $this->assertSame(3, $plan->vendorRequests()->count());
        $this->assertTrue($plan->vendorRequests()->where('vendor_id', $ownerA->id)->exists());
        $this->assertTrue($plan->vendorRequests()->where('vendor_id', $agency->id)->exists());
        $this->assertTrue($plan->vendorRequests()->where('vendor_id', $agency2->id)->exists());

        $agencyReq = $plan->vendorRequests()->where('vendor_id', $agency->id)->firstOrFail();
        $quote = app(OutdoorPlanService::class)->quote($agencyReq, $agencyUser, 1800, 'ajans teklif', true);
        app(OutdoorPlanService::class)->accept($agencyReq->fresh(), $quote->fresh(), $customer, true, true);

        $this->assertSame(1, OohOccupancy::query()->where('kind', OohOccupancy::KIND_BOOKED)->where('ooh_inventory_id', $face->id)->count());
        $this->assertFalse($plan->vendorRequests()->whereIn('status', ['pending', 'quoted'])->exists());
        $this->assertSame(Order::query()->where('type', 'outdoor')->value('vendor_id'), $agency->id);
    }

    public function test_exclusive_agency_is_the_only_seller_on_submit(): void
    {
        [$ownerUser, $owner] = $this->outdoorVendor('Münhasır Sahip');
        [$agencyUser, $agency] = $this->outdoorAgency('Münhasır Ajans');
        [$otherUser, $other] = $this->outdoorAgency('İkinci Ajans');
        $reps = app(\App\Services\OutdoorRepresentationService::class);
        $bind = $reps->invite($owner, $agencyUser->email, true);
        $reps->accept($bind, $agency);

        $this->expectException(\RuntimeException::class);
        $reps->invite($owner, $otherUser->email, true);
    }

    public function test_exclusive_plan_does_not_fan_out_to_owner_or_second_agency(): void
    {
        [$ownerUser, $owner] = $this->outdoorVendor('Münhasır Sahip 2');
        [$agencyUser, $agency] = $this->outdoorAgency('Münhasır Ajans 2');
        [$otherUser, $other] = $this->outdoorAgency('Dışarıdaki Ajans');
        $reps = app(\App\Services\OutdoorRepresentationService::class);
        $bind = $reps->invite($owner, $agencyUser->email, true);
        $reps->accept($bind, $agency);
        $otherBind = $reps->invite($owner, $otherUser->email, false);
        $reps->accept($otherBind, $other);

        $cat = $this->outdoorCategory();
        $face = $this->publishFace($owner, $cat, 'Münhasır Pano');
        $customer = User::factory()->create(['role' => 'customer']);
        $plan = app(OutdoorPlanService::class)->submit($customer, [
            ['inventory_id' => $face->id, 'starts_on' => now()->addDays(5)->toDateString(), 'ends_on' => now()->addDays(8)->toDateString()],
        ]);

        $this->assertSame(1, $plan->vendorRequests()->count());
        $this->assertSame($agency->id, (int) $plan->vendorRequests()->first()->vendor_id);
        $this->assertFalse($plan->vendorRequests()->where('vendor_id', $owner->id)->exists());
        $this->assertFalse($plan->vendorRequests()->where('vendor_id', $other->id)->exists());
    }

    public function test_outdoor_only_login_uses_outdoor_panel_and_blocks_product_create(): void
    {
        [$user, $vendor] = $this->outdoorVendor();

        $this->actingAs($user)->get(route('outdoor-panel.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('vendor.dashboard'))
            ->assertRedirect(route('outdoor-panel.dashboard'));
        $this->actingAs($user)->get(route('vendor.products.create'))
            ->assertRedirect(route('outdoor-panel.dashboard'));
        $this->actingAs($user)->get(route('vendor.documents.index'))
            ->assertRedirect(route('outdoor-panel.documents.index'));
        $this->actingAs($user)->get(route('outdoor-panel.documents.index'))
            ->assertOk()
            ->assertDontSee('Ürünlerim', false);
        $this->get('/satici-panel/outdoor')->assertRedirect('/acik-hava-panel/envanter');
        $this->assertSame(301, $this->get('/satici-panel/outdoor')->getStatusCode());

        $this->actingAs($user)->get(route('outdoor-panel.inventories.create'))->assertOk();
        [$agencyUser] = $this->outdoorAgency();
        $this->actingAs($agencyUser)->get(route('outdoor-panel.inventories.create'))->assertForbidden();
        $this->actingAs($agencyUser)->get(route('outdoor-panel.pool'))->assertOk();
    }

    public function test_personal_inventory_grant_sees_only_own_faces_and_revokes_to_field(): void
    {
        Storage::fake('public');
        [$ownerUser, $owner] = $this->outdoorVendor('Sahip Firma');
        $cat = $this->outdoorCategory();
        $ownerFace = $this->publishFace($owner, $cat, 'Sahip Panosu');

        $field = User::factory()->create(['role' => 'vendor', 'vendor_id' => $owner->id]);
        VendorMember::create([
            'vendor_id' => $owner->id,
            'user_id' => $field->id,
            'staff_role' => VendorMember::ROLE_FIELD,
        ]);

        $this->actingAs($field)->get(route('outdoor-panel.inventories.create'))->assertForbidden();

        $staff = app(OutdoorStaffService::class);
        $grant = $staff->grantInventory(
            $owner,
            $ownerUser,
            null,
            $field->id,
            now()->subHour(),
            now()->addDays(10)
        );

        $this->actingAs($field)->get(route('outdoor-panel.inventories.index'))
            ->assertOk()
            ->assertDontSee('Sahip Panosu');

        $this->actingAs($field)->post(route('outdoor-panel.inventories.store'), [
            'title' => 'Saha Ekstra',
            'category_id' => $cat->id,
            'lat' => 38.4,
            'lng' => 27.1,
            'price_unit' => 'month',
            'permit_no' => 'SAHA-1',
        ])->assertRedirect();

        $extra = OohInventory::query()->where('title', 'Saha Ekstra')->first();
        $this->assertNotNull($extra);
        $this->assertSame($field->id, (int) $extra->created_by_user_id);
        $this->assertSame($owner->id, (int) $extra->vendor_id);

        $this->actingAs($field)->get(route('outdoor-panel.inventories.index'))
            ->assertOk()
            ->assertSee('Saha Ekstra')
            ->assertDontSee('Sahip Panosu');

        $this->actingAs($field)->get(route('outdoor-panel.inventories.edit', $ownerFace))->assertForbidden();
        $this->actingAs($field)->put(route('outdoor-panel.inventories.update', $ownerFace), [
            'title' => 'Hack',
            'category_id' => $cat->id,
            'lat' => 38.4,
            'lng' => 27.1,
            'price_unit' => 'month',
        ])->assertForbidden();

        $this->actingAs($field)->put(route('outdoor-panel.inventories.update', $extra), [
            'title' => 'Saha Ekstra 2',
            'category_id' => $cat->id,
            'lat' => 38.4,
            'lng' => 27.1,
            'price_unit' => 'month',
        ])->assertRedirect();
        $this->assertSame('Saha Ekstra 2', $extra->fresh()->title);

        $this->actingAs($field)->delete(route('outdoor-panel.inventories.destroy', $extra))->assertRedirect();
        $this->assertSoftDeleted('ooh_inventories', ['id' => $extra->id]);

        $staff->revokeGrant($owner, $ownerUser, $grant);
        $this->actingAs($field)->get(route('outdoor-panel.inventories.create'))->assertForbidden();

        $occ = OohOccupancy::create([
            'ooh_inventory_id' => $ownerFace->id,
            'starts_on' => now()->addDay()->toDateString(),
            'ends_on' => now()->addDays(3)->toDateString(),
            'kind' => OohOccupancy::KIND_BOOKED,
            'assigned_user_id' => $field->id,
        ]);
        $proof = app(\App\Services\OutdoorProofService::class)->submit(
            $occ,
            $field,
            UploadedFile::fake()->image('asim.jpg'),
            41.0,
            29.0
        );
        $this->assertNotNull($proof);
    }

    public function test_crew_grant_allows_member_not_other_crew(): void
    {
        [$ownerUser, $owner] = $this->outdoorVendor('Crew Co');
        $cat = $this->outdoorCategory();
        $staff = app(OutdoorStaffService::class);
        $adana = $staff->createCrew($owner, $ownerUser, 'Adana');
        $ankara = $staff->createCrew($owner, $ownerUser, 'Ankara');

        $adanaUser = User::factory()->create(['role' => 'vendor', 'vendor_id' => $owner->id]);
        $ankaraUser = User::factory()->create(['role' => 'vendor', 'vendor_id' => $owner->id]);
        VendorMember::create([
            'vendor_id' => $owner->id,
            'user_id' => $adanaUser->id,
            'staff_role' => VendorMember::ROLE_FIELD,
            'crew_id' => $adana->id,
        ]);
        VendorMember::create([
            'vendor_id' => $owner->id,
            'user_id' => $ankaraUser->id,
            'staff_role' => VendorMember::ROLE_FIELD,
            'crew_id' => $ankara->id,
        ]);

        $staff->grantInventory($owner, $ownerUser, $adana->id, null, now()->subHour(), now()->addWeek());

        $this->actingAs($adanaUser)->post(route('outdoor-panel.inventories.store'), [
            'title' => 'Adana Pano',
            'category_id' => $cat->id,
            'lat' => 37.0,
            'lng' => 35.3,
            'price_unit' => 'month',
        ])->assertRedirect();
        $this->actingAs($ankaraUser)->get(route('outdoor-panel.inventories.create'))->assertForbidden();

        $staff->grantInventory($owner, $ownerUser, $ankara->id, null, now()->subWeek(), now()->subHour());
        $this->actingAs($ankaraUser)->get(route('outdoor-panel.inventories.create'))->assertForbidden();

        $this->actingAs($ownerUser)->get(route('outdoor-panel.staff'))
            ->assertOk()
            ->assertSee('Adana')
            ->assertSee('Pano ekleme yetkisi');
    }

    private function seedLocationFacts(): void
    {
        TurkiyeIl::query()->updateOrCreate(
            ['id' => 34],
            ['name' => 'İstanbul', 'population' => 15655924, 'population_year' => 2023]
        );
        TurkiyeIlce::query()->updateOrCreate(
            ['id' => 1103],
            [
                'province_id' => 34,
                'name' => 'Kadıköy',
                'postal_code' => '34710',
                'population' => 467919,
                'population_year' => 2023,
            ]
        );
        OohKgmTraffic::query()->updateOrCreate(
            ['road_ref' => 'D100', 'lat' => 41.0, 'lng' => 29.0],
            ['name' => 'D100 test', 'aadt' => 85421, 'year' => 2023]
        );
    }

    public function test_catalog_shows_tuik_population_and_kgm_when_matched(): void
    {
        $this->seedLocationFacts();
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $matched = $this->publishFace($vendor, $cat, 'D100 Pano', 41.0, 29.0);
        $unmatched = $this->publishFace($vendor, $cat, 'Uzak Pano', 38.4, 27.1);

        $this->get(route('outdoor.show', $matched->slug))
            ->assertOk()
            ->assertSee('15.655.924', false)
            ->assertSee('85.421', false)
            ->assertSee('KGM', false)
            ->assertDontSee('maps.googleapis.com', false)
            ->assertDontSee('streetview', false);

        $this->get(route('outdoor.show', $unmatched->slug))
            ->assertOk()
            ->assertSee('Bu noktada resmi araç sayımı yok', false);
    }

    public function test_proof_requires_matching_qr_token(): void
    {
        Storage::fake('public');
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $face = $this->publishFace($vendor, $cat, 'QR Pano', 41.0, 29.0);
        $this->assertNotEmpty($face->qr_token);
        $occ = OohOccupancy::create([
            'ooh_inventory_id' => $face->id,
            'starts_on' => now()->addDay()->toDateString(),
            'ends_on' => now()->addDays(3)->toDateString(),
            'kind' => OohOccupancy::KIND_BOOKED,
        ]);

        $bad = app(OutdoorProofService::class)->submit(
            $occ,
            $user,
            UploadedFile::fake()->image('bad.jpg'),
            41.0,
            29.0,
            'WRONGTOK'
        );
        $this->assertFalse($bad->is_valid);

        $ok = app(OutdoorProofService::class)->submit(
            $occ,
            $user,
            UploadedFile::fake()->image('ok.jpg'),
            41.0,
            29.0,
            $face->qr_token
        );
        $this->assertTrue($ok->is_valid);

        $this->get(route('outdoor.verify', $face->qr_token))
            ->assertOk()
            ->assertSee('QR Pano', false)
            ->assertDontSee($vendor->phone ?? 'iletişim-yok', false);
    }

    public function test_legacy_phone_login_urls_redirect_to_giris(): void
    {
        $this->get('/acik-hava-giris')->assertRedirect(route('login'));
        $this->get('/saha-giris')->assertRedirect(route('login'));
        $this->post('/acik-hava-giris', ['phone' => '05551112233', 'password' => 'password'])
            ->assertRedirect(route('login'));
        $this->post('/saha-giris', ['phone' => '05551112233', 'password' => 'password'])
            ->assertRedirect(route('login'));
    }

    public function test_print_vendor_logs_in_via_giris_email(): void
    {
        $user = User::factory()->create([
            'role' => 'vendor',
            'email' => 'print-vendor@example.com',
            'phone' => '905554443322',
        ]);
        $vendor = Vendor::factory()->create([
            'user_id' => $user->id,
            'registration_tracks' => ['physical_products'],
            'outdoor_role' => null,
            'outdoor_enabled' => false,
        ]);
        $user->update(['vendor_id' => $vendor->id]);

        $this->post('/giris', ['email' => 'print-vendor@example.com', 'password' => 'password'])
            ->assertRedirect(route('vendor.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_owner_email_login_reaches_outdoor_dashboard(): void
    {
        [$ownerUser] = $this->outdoorVendor();
        $ownerUser->update(['email' => 'outdoor-owner@example.com']);

        $this->post('/giris', ['email' => 'outdoor-owner@example.com', 'password' => 'password'])
            ->assertRedirect(route('outdoor-panel.dashboard'));
        $this->assertAuthenticatedAs($ownerUser);
    }

    public function test_field_email_login_reaches_jobs(): void
    {
        [$ownerUser, $owner] = $this->outdoorVendor();
        $member = app(OutdoorStaffService::class)->invite(
            $owner,
            $ownerUser,
            '05559998877',
            'Ali Saha',
            VendorMember::ROLE_FIELD,
            'password'
        );
        $member->load('user');

        $this->post('/giris', ['email' => $member->user->email, 'password' => 'password'])
            ->assertRedirect(route('outdoor-panel.jobs'));
        $this->assertAuthenticatedAs($member->user);
    }

    public function test_field_with_real_email_also_reaches_jobs(): void
    {
        [, $owner] = $this->outdoorVendor();
        $field = User::factory()->create([
            'role' => 'vendor',
            'vendor_id' => $owner->id,
            'email' => 'saha-mail@example.com',
            'phone' => '905558887766',
        ]);
        VendorMember::create([
            'vendor_id' => $owner->id,
            'user_id' => $field->id,
            'staff_role' => VendorMember::ROLE_FIELD,
        ]);

        $this->post('/giris', ['email' => 'saha-mail@example.com', 'password' => 'password'])
            ->assertRedirect(route('outdoor-panel.jobs'));
        $this->assertAuthenticatedAs($field);
    }

    public function test_outdoor_quote_fee_respects_switch_threshold_and_once_per_request(): void
    {
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $face = $this->publishFace($vendor, $cat, 'Ücret Pano');
        $customer = User::factory()->create(['role' => 'customer']);
        $start = now()->addDays(10)->toDateString();
        $end = now()->addDays(20)->toDateString();
        $plans = app(OutdoorPlanService::class);
        $plan = $plans->submit($customer, [
            ['inventory_id' => $face->id, 'starts_on' => $start, 'ends_on' => $end],
        ]);
        $req = $plan->vendorRequests()->firstOrFail();

        Setting::set('outdoor_quoting_enabled', '0');
        try {
            $plans->quote($req, $user, 2000, 'kapali', true);
            $this->fail('Kapalı teklif istisna fırlatmalı.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('kapalı', $e->getMessage());
        }

        Setting::set('outdoor_quoting_enabled', '1');
        Setting::set('outdoor_quote_fee_threshold', '1000');
        Setting::set('outdoor_quote_fee', '50');
        $vendor->update(['balance' => 200]);

        $plans->quote($req, $user, 500, 'alt', true);
        $this->assertEquals(200.0, (float) $vendor->fresh()->balance);

        $plans->quote($req, $user, 2000, 'ust', true);
        $this->assertEquals(150.0, (float) $vendor->fresh()->balance);

        $plans->quote($req, $user, 2500, 'tekrar', true);
        $this->assertEquals(150.0, (float) $vendor->fresh()->balance);

        $face2 = $this->publishFace($vendor, $cat, 'İkinci Pano', 41.1, 29.1);
        $plan2 = $plans->submit($customer, [
            ['inventory_id' => $face2->id, 'starts_on' => $start, 'ends_on' => $end],
        ]);
        $req2 = $plan2->vendorRequests()->firstOrFail();
        $vendor->update(['balance' => 10]);
        try {
            $plans->quote($req2, $user, 2000, 'yetersiz', true);
            $this->fail('Yetersiz bakiye istisna fırlatmalı.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('yetersiz', $e->getMessage());
        }
        $this->assertEquals(10.0, (float) $vendor->fresh()->balance);
        $this->assertSame(0, $req2->quotes()->count());
    }

    public function test_vendor_planner_accepts_counterparty_quote_and_exports_excel_with_osm(): void
    {
        [$aUser, $aVendor] = $this->outdoorVendor('Sahip A');
        [$bUser, $bVendor] = $this->outdoorVendor('Sahip B');
        $cat = $this->outdoorCategory();
        $faceA = $this->publishFace($aVendor, $cat, 'Karşı Pano', 41.0123, 29.0456);
        $start = now()->addDays(12)->toDateString();
        $end = now()->addDays(22)->toDateString();

        $this->actingAs($bUser)->get(route('outdoor-panel.dashboard'))
            ->assertOk()
            ->assertSee('Pano seç')
            ->assertSee('Planlarım');

        $plan = app(OutdoorPlanService::class)->submit(
            $bUser,
            [['inventory_id' => $faceA->id, 'starts_on' => $start, 'ends_on' => $end]],
            OohPlan::PLANNER_VENDOR,
            $bVendor,
            'B2B plan'
        );
        $req = $plan->vendorRequests()->where('vendor_id', $aVendor->id)->firstOrFail();
        $quote = app(OutdoorPlanService::class)->quote($req, $aUser, 1800, 'b2b', true);

        $this->actingAs($bUser)->get(route('outdoor-panel.plans.show', $plan))
            ->assertOk()
            ->assertSee('1.800,00')
            ->assertSee('Bu satıcıyı onayla');

        $this->actingAs($bUser)->post(route('outdoor-panel.plans.accept', [$plan, $req, $quote]), [
            'share_my_contact' => '1',
            'accept_vendor_contact' => '1',
            'accept_consent' => '1',
            'accept_consent_scrolled_at' => now()->toIso8601String(),
        ])->assertRedirect(route('outdoor-panel.plans.show', $plan));

        $order = Order::query()->where('type', 'outdoor')->first();
        $this->assertNotNull($order);
        $this->assertSame($bUser->id, (int) $order->user_id);
        $this->assertSame($aVendor->id, (int) $order->vendor_id);
        $this->assertSame(0.0, (float) $order->commission_amount);

        $plansXls = $this->actingAs($bUser)->get(route('outdoor-panel.plans.export'));
        $plansXls->assertOk();
        $plansText = $this->xlsxPlainText($plansXls);
        $this->assertStringContainsString('openstreetmap.org', $plansText);
        $this->assertStringContainsString('mlat=', $plansText);
        $this->assertStringNotContainsString((string) $aVendor->phone, $plansText);
        $this->assertStringNotContainsString((string) $bVendor->phone, $plansText);

        $reqXls = $this->actingAs($aUser)->get(route('outdoor-panel.requests.export', ['status' => 'accepted']));
        $reqXls->assertOk();
        $reqText = $this->xlsxPlainText($reqXls);
        $this->assertStringContainsString('openstreetmap.org', $reqText);

        $field = User::factory()->create(['role' => 'vendor', 'vendor_id' => $bVendor->id]);
        VendorMember::create([
            'vendor_id' => $bVendor->id,
            'user_id' => $field->id,
            'staff_role' => VendorMember::ROLE_FIELD,
        ]);
        $this->actingAs($field)->get(route('outdoor-panel.plans.export'))->assertForbidden();
        $this->actingAs($field)->get(route('outdoor-panel.dashboard'))
            ->assertOk()
            ->assertDontSee('Pano seç');
    }

    public function test_catalog_defaults_to_turkey_and_all_clears_filter(): void
    {
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $tr = $this->publishFace($vendor, $cat, 'TR Varsayilan Pano');
        OohInventory::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Berlin Face',
            'lat' => 52.5,
            'lng' => 13.4,
            'country_code' => 'DE',
            'city' => 'Berlin',
            'list_price' => 800,
            'price_unit' => OohInventory::UNIT_MONTH,
            'status' => OohInventory::STATUS_PUBLISHED,
        ]);

        $this->get(route('outdoor.index'))
            ->assertOk()
            ->assertSee('TR Varsayilan Pano')
            ->assertDontSee('Berlin Face')
            ->assertSee('value="TR"', false);

        $this->get(route('outdoor.index', ['ulke' => 'all']))
            ->assertOk()
            ->assertSee('TR Varsayilan Pano')
            ->assertSee('Berlin Face');

        $this->assertTrue($tr->isPublished());
    }

    public function test_reverse_geocode_fills_turkey_il_ilce(): void
    {
        $this->seedLocationFacts();
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                'address' => [
                    'country_code' => 'tr',
                    'province' => 'İstanbul',
                    'town' => 'Kadıköy',
                ],
            ], 200),
        ]);

        $geo = app(OutdoorReverseGeocodeService::class)->lookup(41.0, 29.0);
        $this->assertSame('TR', $geo['country_code']);
        $this->assertSame('İstanbul', $geo['city']);
        $this->assertSame('Kadıköy', $geo['district']);
        $this->assertSame(34, $geo['turkiye_il_id']);
        $this->assertSame(1103, $geo['turkiye_ilce_id']);

        $this->getJson('/api/v1/geography/reverse?lat=41&lng=29')
            ->assertOk()
            ->assertJsonPath('data.country_code', 'TR')
            ->assertJsonPath('data.city', 'İstanbul');
    }

    public function test_world_places_iso_seed_exposes_foreign_states(): void
    {
        $this->seed(WorldPlacesIso3166Seeder::class);
        $this->assertDatabaseHas('world_places', [
            'country_code' => 'DE',
            'city' => 'Bayern',
            'district' => '',
        ]);
        $this->getJson('/api/v1/geography/places?country=DE')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Bayern']);
    }

    public function test_inventory_bulk_excel_import_export_and_zip_images(): void
    {
        Storage::fake('public');
        [$user, $vendor] = $this->outdoorVendor();
        $cat = $this->outdoorCategory();
        $existing = $this->publishFace($vendor, $cat, 'Eski Pano', 41.01, 29.01);

        $this->actingAs($user)->get(route('outdoor-panel.inventories.export'))->assertOk();

        $rows = [
            [
                null,
                'Yeni Toplu Pano',
                $cat->id,
                'aciklama',
                'TR',
                'İstanbul',
                'Kadıköy',
                'Adr',
                41.02,
                29.02,
                'PRM-NEW',
                1200,
                'month',
                3,
                2,
                'N',
                1,
                'pano.jpg',
                null,
            ],
            [
                $existing->id,
                'Eski Pano Guncel',
                $cat->id,
                null,
                'TR',
                'İstanbul',
                'Kadıköy',
                null,
                41.01,
                29.01,
                'PRM-'.$vendor->id,
                1500,
                'month',
                null,
                null,
                null,
                0,
                null,
                'published',
            ],
        ];
        $tmpXlsx = 'tmp/ooh-bulk-test-'.$vendor->id.'.xlsx';
        \Illuminate\Support\Facades\Storage::disk('local')->makeDirectory('tmp');
        \Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\OutdoorInventoryExport($rows), $tmpXlsx, 'local');
        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($tmpXlsx);
        $this->assertFileExists($fullPath);
        $xlsx = new UploadedFile($fullPath, 'envanter.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $img = UploadedFile::fake()->image('pano.jpg', 40, 40);
        $zipPath = tempnam(sys_get_temp_dir(), 'oohzip').'.zip';
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($zipPath, \ZipArchive::CREATE) === true);
        $zip->addFile($img->getRealPath(), 'pano.jpg');
        $zip->close();
        $zipUpload = new UploadedFile($zipPath, 'images.zip', 'application/zip', null, true);

        Http::fake();

        $this->actingAs($user)->post(route('outdoor-panel.inventories.import'), [
            'file' => $xlsx,
            'images_zip' => $zipUpload,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('ooh_inventories', [
            'vendor_id' => $vendor->id,
            'title' => 'Yeni Toplu Pano',
            'status' => OohInventory::STATUS_DRAFT,
        ]);
        $this->assertDatabaseHas('ooh_inventories', [
            'id' => $existing->id,
            'title' => 'Eski Pano Guncel',
            'list_price' => 1500,
        ]);
        $new = OohInventory::query()->where('title', 'Yeni Toplu Pano')->first();
        $this->assertNotNull($new);
        $this->assertGreaterThan(0, $new->images()->count());

        $field = User::factory()->create(['role' => 'vendor', 'vendor_id' => $vendor->id]);
        VendorMember::create([
            'vendor_id' => $vendor->id,
            'user_id' => $field->id,
            'staff_role' => VendorMember::ROLE_FIELD,
        ]);
        $this->actingAs($field)->get(route('outdoor-panel.inventories.export'))->assertForbidden();

        @unlink($zipPath);
        @unlink($fullPath);
    }

    private function xlsxPlainText(\Illuminate\Testing\TestResponse $response): string
    {
        $binary = method_exists($response, 'streamedContent') ? $response->streamedContent() : $response->getContent();
        $tmp = tempnam(sys_get_temp_dir(), 'oohxls');
        file_put_contents($tmp, $binary);
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($tmp) === true);
        $chunks = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (is_string($name) && str_ends_with($name, '.xml')) {
                $chunks[] = (string) $zip->getFromIndex($i);
            }
        }
        $zip->close();
        @unlink($tmp);

        return implode("\n", $chunks);
    }
}
