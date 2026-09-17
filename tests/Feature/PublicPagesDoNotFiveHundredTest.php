<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicPagesDoNotFiveHundredTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_get_pages_do_not_return_500(): void
    {
        $routes = [
            '/',
            '/urunler',
            '/saticilar',
            '/acik-hava',
            '/giris',
            '/kayit',
            '/blog',
            '/gizlilik',
            '/kullanim-kosullari',
            '/hakkimizda',
            '/iletisim',
            '/teklif-talebi',
            '/teklif-talebi?type=tabela',
            '/teklif-talebi?type=ozalit',
            '/teklif-talebi?type=freelancer',
            '/api/v1/ooh-inventories',
        ];

        foreach ($routes as $path) {
            $response = str_starts_with($path, '/api/')
                ? $this->getJson($path)
                : $this->get($path);
            $this->assertNotEquals(500, $response->status(), "GET {$path} returned 500");
        }
    }

    public function test_outdoor_pages_survive_missing_ooh_tables(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'ooh_proofs',
            'ooh_inventory_claims',
            'ooh_occupancies',
            'ooh_quotes',
            'ooh_vendor_requests',
            'ooh_plan_items',
            'ooh_plans',
            'ooh_inventory_images',
            'ooh_inventories',
            'vendor_members',
        ] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();

        $this->get(route('outdoor.index'))->assertOk()->assertSee('Mecra kataloğu');
        $this->getJson('/api/v1/ooh-inventories')->assertOk()->assertJsonPath('success', true);

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get(route('customer.outdoor.plans.index'))->assertOk();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.outdoor.inventories'))->assertOk();
        $this->actingAs($admin)->get(route('admin.outdoor.claims'))->assertOk();
        $this->actingAs($admin)->get(route('admin.outdoor.plans'))->assertOk();
    }
}
