<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function bearer(User $user): array
    {
        $token = $user->createToken('test', ['*'])->plainTextToken;

        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];
    }

    public function test_public_home_returns_standard_envelope(): void
    {
        $this->getJson('/api/v1/home')
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data']);
    }

    public function test_orders_require_authentication(): void
    {
        $this->getJson('/api/v1/orders')->assertStatus(401);
    }

    public function test_customer_can_create_and_list_support_tickets(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->withHeaders($this->bearer($user))
            ->postJson('/api/v1/support-tickets', [
                'subject' => 'Test konusu',
                'body' => 'Açıklama metni.',
            ])
            ->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->withHeaders($this->bearer($user))
            ->getJson('/api/v1/support-tickets')
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_vendor_can_list_payout_requests(): void
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);
        $user->forceFill(['vendor_id' => $vendor->id])->save();

        $this->withHeaders($this->bearer($user))
            ->getJson('/api/v1/vendor/payout-requests')
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_admin_support_routes_forbidden_for_customer(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->withHeaders($this->bearer($user))
            ->getJson('/api/v1/admin/support-tickets')
            ->assertStatus(403);
    }
}
