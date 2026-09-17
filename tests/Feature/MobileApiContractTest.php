<?php
namespace Tests\Feature;

use App\Domain\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\PlatformVendorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiContractTest extends TestCase
{
    use RefreshDatabase;

    // /api/v1/home -- response: { success, data: { featured_products, ... } }

    public function test_home_endpoint_returns_discovery_products_key(): void
    {
        $response = $this->getJson('/api/v1/home');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'featured_products',
                'digital_products',
                'discovery_products',
                'categories',
            ],
        ]);
    }

    public function test_home_discovery_products_mirrors_featured_products(): void
    {
        $response = $this->getJson('/api/v1/home');
        $response->assertOk();
        $body = $response->json('data');
        $this->assertSame(
            count($body['featured_products']),
            count($body['discovery_products']),
            'discovery_products should have same count as featured_products'
        );
    }

    // /api/v1/vendors/{slug}

    public function test_vendor_detail_includes_trust_fields(): void
    {
        $user   = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create([
            'user_id'             => $user->id,
            'verification_status' => 'verified',
        ]);

        $response = $this->getJson('/api/v1/vendors/' . $vendor->slug);
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'trust_level',
                'trust_badge_label',
                'trust_level_label',
                'verification_status',
                'is_suspended',
            ],
        ]);
    }

    // /api/v1/service-requests

    public function test_freelancer_cannot_create_service_request(): void
    {
        $this->seed(PlatformVendorSeeder::class);

        $freelancerUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create([
            'user_id'             => $freelancerUser->id,
            'freelancer_enabled'  => true,
            'verification_status' => 'verified',
        ]);
        $freelancerUser->update(['vendor_id' => $vendor->id]);

        $response = $this->actingAs($freelancerUser)->postJson('/api/v1/service-requests', [
            'category'    => 'logo',
            'title'       => 'Freelancer talep testi',
            'description' => 'Bu istek reddedilmeli.',
            'budget_min'  => 500,
            'budget_max'  => 1000,
        ]);

        $response->assertForbidden();
    }

    public function test_customer_can_create_service_request(): void
    {
        $this->seed(PlatformVendorSeeder::class);
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->postJson('/api/v1/service-requests', [
            'category'    => 'logo',
            'title'       => 'Musteri talep testi',
            'description' => 'Gecerli bir talep.',
            'budget_min'  => 500,
            'budget_max'  => 1000,
        ]);

        $response->assertCreated();
        // store() direkt model donduruyor, data wrapper yok
        $response->assertJsonPath('status', 'open');
    }

    // Bid selection -> pending_payment

    public function test_selecting_bid_creates_order_with_pending_payment_status(): void
    {
        $this->seed(PlatformVendorSeeder::class);

        $customer       = User::factory()->create(['role' => 'customer']);
        $freelancerUser = User::factory()->create(['role' => 'vendor']);
        $vendor         = Vendor::factory()->create([
            'user_id'             => $freelancerUser->id,
            'freelancer_enabled'  => true,
            'verification_status' => 'verified',
        ]);
        $freelancerUser->update(['vendor_id' => $vendor->id]);

        $createResp = $this->actingAs($customer)->postJson('/api/v1/service-requests', [
            'category'    => 'logo',
            'title'       => 'Odeme durumu testi',
            'description' => 'Test aciklamasi',
            'budget_min'  => 500,
            'budget_max'  => 1500,
        ]);
        $createResp->assertCreated();
        $requestId = $createResp->json('id');

        $bidResp = $this->actingAs($freelancerUser)->postJson(
            '/api/v1/service-requests/' . $requestId . '/bids',
            ['amount' => 1000, 'proposal' => 'Freelancer teklifim.']
        );
        $bidResp->assertCreated();
        $bidId = $bidResp->json('id');

        $selectResp = $this->actingAs($customer)->postJson(
            '/api/v1/service-requests/' . $requestId . '/bids/' . $bidId . '/select'
        );
        $selectResp->assertOk();

        $order = Order::where('contractor_user_id', $freelancerUser->id)->latest()->first();
        $this->assertNotNull($order, 'Siparis olusturulmus olmali');
        $this->assertSame(OrderStatus::PENDING_PAYMENT, $order->status);
    }
}