<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedBackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_product_as_featured(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Öne çıkan ürün',
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'price' => 100,
            'stock' => 10,
            'is_active' => true,
            'is_featured' => 1,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product = Product::where('name', 'Öne çıkan ürün')->firstOrFail();
        $this->assertTrue((bool) $product->is_featured);
    }

    public function test_products_cannot_be_set_as_featured_by_vendor_api(): void
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);
        $user->update(['vendor_id' => $vendor->id]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/vendor/products', [
            'name' => 'Satıcı Ürünü',
            'category_id' => $category->id,
            'price' => 50,
            'stock' => 5,
            'is_featured' => true,
        ]);

        $response->assertStatus(201);
        $productId = $response->json('data.id');

        $product = Product::findOrFail($productId);
        $this->assertFalse((bool) $product->is_featured, 'Vendor cannot create featured products.');

        // Update via API
        $updateResponse = $this->actingAs($user, 'sanctum')->putJson("/api/v1/vendor/products/{$productId}", [
            'is_featured' => true,
        ]);

        $updateResponse->assertOk();
        $this->assertFalse((bool) $product->fresh()->is_featured, 'Vendor cannot update product to featured.');
    }

    public function test_admin_featured_flag_persists_on_existing_products(): void
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'is_featured' => false,
        ]);

        $product->update(['is_featured' => true]);
        $this->assertTrue((bool) $product->fresh()->is_featured);
    }
}
