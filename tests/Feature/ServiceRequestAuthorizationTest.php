<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FreelancerJobBid;
use App\Models\FreelancerJobListing;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\PlatformVendorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_service_request_and_select_bid(): void
    {
        $this->seed(PlatformVendorSeeder::class);

        $customer = User::factory()->create(['role' => 'customer']);
        $verifiedVendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create([
            'user_id' => $verifiedVendorUser->id,
            'freelancer_enabled' => true,
            'verification_status' => 'verified',
        ]);
        $verifiedVendorUser->update(['vendor_id' => $vendor->id]);

        // Customer creates request via web
        $createResponse = $this->actingAs($customer)->post(route('service-requests.store'), [
            'category' => 'logo',
            'title' => 'Logo Tasarımı Talebi',
            'description' => 'Modern ve minimalist bir logo istiyoruz.',
            'budget_min' => 1000,
            'budget_max' => 2500,
        ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('freelancer_job_listings', [
            'user_id' => $customer->id,
            'title' => 'Logo Tasarımı Talebi',
            'status' => 'open',
        ]);

        $request = FreelancerJobListing::where('title', 'Logo Tasarımı Talebi')->firstOrFail();

        // Verified freelancer submits bid
        $bidResponse = $this->actingAs($verifiedVendorUser)->post(route('service-requests.bid', $request), [
            'amount' => 1500,
            'delivery_days' => 5,
            'proposal' => 'Portfolio referanslarımla hazırlayabilirim.',
        ]);
        $bidResponse->assertSessionHas('success');
        $this->assertDatabaseHas('freelancer_job_bids', [
            'freelancer_job_listing_id' => $request->id,
            'user_id' => $verifiedVendorUser->id,
            'amount' => 1500,
        ]);

        $bid = FreelancerJobBid::where('freelancer_job_listing_id', $request->id)->firstOrFail();

        // Customer selects bid
        $selectResponse = $this->actingAs($customer)->post(route('service-requests.select-bid', [$request, $bid]));
        $selectResponse->assertRedirect();
        $this->assertEquals('selected', $bid->fresh()->status);
        $this->assertEquals('closed', $request->fresh()->status);
    }

    public function test_freelancer_gets_403_when_trying_to_create_service_request(): void
    {
        $freelancerUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create([
            'user_id' => $freelancerUser->id,
            'freelancer_enabled' => true,
        ]);
        $freelancerUser->update(['vendor_id' => $vendor->id]);

        // Web create screen returns 403
        $this->actingAs($freelancerUser)->get(route('service-requests.create'))->assertForbidden();

        // Web store action returns 403
        $this->actingAs($freelancerUser)->post(route('service-requests.store'), [
            'category' => 'logo',
            'title' => 'İzinsiz Talep',
        ])->assertForbidden();

        // API store action returns 403
        $this->actingAs($freelancerUser, 'sanctum')->postJson('/api/v1/service-requests', [
            'category' => 'logo',
            'title' => 'İzinsiz API Talep',
        ])->assertStatus(403);
    }

    public function test_unverified_freelancer_cannot_submit_bid(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $request = FreelancerJobListing::create([
            'user_id' => $customer->id,
            'category' => 'wordpress',
            'title' => 'Wordpress Geliştirme',
            'status' => 'open',
        ]);

        // Vendor without verified status or documents
        $unverifiedVendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create([
            'user_id' => $unverifiedVendorUser->id,
            'freelancer_enabled' => true,
            'verification_status' => 'pending',
        ]);
        $unverifiedVendorUser->update(['vendor_id' => $vendor->id]);

        $response = $this->actingAs($unverifiedVendorUser)->post(route('service-requests.bid', $request), [
            'amount' => 500,
            'delivery_days' => 3,
        ]);

        $response->assertSessionHas('error', 'Yalnızca aktif ve doğrulanmış freelancerlar teklif verebilir.');
        $this->assertDatabaseCount('freelancer_job_bids', 0);

        // API attempt returns 403
        $apiResponse = $this->actingAs($unverifiedVendorUser, 'sanctum')->postJson("/api/v1/service-requests/{$request->id}/bids", [
            'amount' => 500,
        ]);

        $apiResponse->assertStatus(403);
    }

    public function test_legacy_get_is_ilanlari_urls_redirect_301_to_canonical(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $request = FreelancerJobListing::create([
            'user_id' => $customer->id,
            'category' => 'logo',
            'title' => 'Eski URL Testi',
            'status' => 'open',
        ]);

        // 1. /is-ilanlari -> 301 -> /hizmet-talepleri
        $this->get('/is-ilanlari')->assertStatus(301)->assertRedirect('/hizmet-talepleri');

        // 2. /is-ilanlari/yeni -> 301 -> /hizmet-talebi-olustur
        $this->actingAs($customer)->get('/is-ilanlari/yeni')->assertStatus(301)->assertRedirect('/hizmet-talebi-olustur');

        // 3. /is-ilanlari/{id} -> 301 -> /hizmet-talebi/{id}
        $this->get("/is-ilanlari/{$request->id}")->assertStatus(301)->assertRedirect("/hizmet-talebi/{$request->id}");

        // 4. /hesap/is-ilanlarim -> 301 -> /hesap/hizmet-taleplerim
        $this->actingAs($customer)->get('/hesap/is-ilanlarim')->assertStatus(301)->assertRedirect('/hesap/hizmet-taleplerim');
    }

    public function test_legacy_freelancer_jobs_api_returns_deprecation_headers(): void
    {
        $response = $this->getJson('/api/v1/freelancer-jobs');

        $response->assertOk();
        $response->assertHeader('Deprecation', '@1798675200');
        $response->assertHeader('Sunset', 'Wed, 31 Dec 2026 23:59:59 GMT');
        $response->assertHeader('Link', '</api/v1/service-requests>; rel="successor-version"');
    }
}
