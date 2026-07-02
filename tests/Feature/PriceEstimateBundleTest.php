<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceEstimateBundleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_bundle_endpoint_returns_line_totals(): void
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'price' => 100,
            'price_min' => 90,
            'price_max' => 110,
            'catalog_type' => 'standard',
            'pricing_type' => 'fixed',
        ]);

        $res = $this->postJson('/api/v1/price-estimate/bundle', [
            'lines' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);
        $res->assertOk()->assertJson(['success' => true]);
        $data = $res->json('data');
        $this->assertIsArray($data['lines'] ?? null);
        $this->assertSame(2, $data['lines'][0]['quantity'] ?? null);
        $this->assertGreaterThan(0, (float) ($data['totals']['estimated_min'] ?? 0));
    }

    public function test_products_filter_by_parent_category_id_includes_child_products(): void
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);
        $parent = Category::factory()->create(['parent_id' => null]);
        $child = Category::factory()->create(['parent_id' => $parent->id]);
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $child->id,
            'name' => 'Branda baskı örnek',
        ]);

        $this->getJson('/api/v1/products?category_id='.$parent->id)
            ->assertOk()
            ->assertJsonFragment(['id' => $product->id]);
    }
}
