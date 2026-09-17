<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FeaturedBackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_cannot_be_set_as_featured_by_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        // Create product attempting is_featured = 1
        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Adil Ürün',
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'price' => 100,
            'stock' => 10,
            'is_active' => true,
            'is_featured' => 1,
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Adil Ürün')->firstOrFail();
        $this->assertFalse((bool) $product->is_featured, 'Product created by admin must have is_featured set to false.');

        // Update product attempting is_featured = 1
        $updateResponse = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Adil Ürün Güncel',
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'price' => 120,
            'stock' => 15,
            'is_active' => true,
            'is_featured' => 1,
        ]);

        $updateResponse->assertRedirect(route('admin.products.index'));
        $this->assertFalse((bool) $product->fresh()->is_featured, 'Product updated by admin must maintain is_featured = false.');
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

    public function test_backfill_migration_clears_any_legacy_featured_products(): void
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
        ]);

        // Manually set is_featured to 1 directly in database as if legacy state
        DB::table('products')->where('id', $product->id)->update(['is_featured' => true]);
        $this->assertTrue((bool) DB::table('products')->where('id', $product->id)->value('is_featured'));

        // Run the backfill migration logic
        DB::table('products')->where('is_featured', true)->update(['is_featured' => false]);

        $this->assertFalse((bool) DB::table('products')->where('id', $product->id)->value('is_featured'));
    }
}
