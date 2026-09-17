<?php

namespace Tests\Feature;

use App\Http\Resources\Api\V1\ProductResource;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_simple_product_single_click_ajax_add_to_cart(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'price' => 150,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('cart.add', $product), ['quantity' => 1]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Ürün sepete eklendi.',
                'cart_count' => 1,
            ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_out_of_stock_product_cannot_be_added(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'price' => 150,
            'stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('cart.add', $product), ['quantity' => 1]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Yeterli stok yok.',
            ]);
    }

    public function test_variant_product_requires_valid_variant_and_updates_cart(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'price' => 200,
            'stock' => 10,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Kırmızı / XL',
            'sku' => 'VAR-RED-XL',
            'price_adjustment' => 25.00,
            'stock' => 5,
        ]);

        // Add valid variant
        $response = $this->actingAs($user)
            ->postJson(route('cart.add', $product), [
                'variant_id' => $variant->id,
                'quantity' => 2,
            ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'cart_count' => 2,
            ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        // Exceed variant stock
        $overResponse = $this->actingAs($user)
            ->postJson(route('cart.add', $product), [
                'variant_id' => $variant->id,
                'quantity' => 10,
            ]);

        $overResponse->assertStatus(422)
            ->assertJson(['message' => 'Yeterli stok yok.']);
    }

    public function test_product_resource_exposes_consistent_cta_type(): void
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        // 1. Simple in stock
        $simple = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'stock' => 5,
            'pricing_type' => 'fixed',
        ]);
        $simpleResource = (new ProductResource($simple))->toArray(request());
        $this->assertEquals('add_to_cart', $simpleResource['cta_type']);

        // 2. Out of stock
        $out = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'stock' => 0,
            'pricing_type' => 'fixed',
        ]);
        $outResource = (new ProductResource($out))->toArray(request());
        $this->assertEquals('out_of_stock', $outResource['cta_type']);

        // 3. Quote based
        $quote = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'pricing_type' => 'quote',
        ]);
        $quoteResource = (new ProductResource($quote))->toArray(request());
        $this->assertEquals('quote', $quoteResource['cta_type']);

        // 4. Variant product
        $variantProd = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'stock' => 10,
        ]);
        ProductVariant::create([
            'product_id' => $variantProd->id,
            'name' => 'V1',
            'stock' => 5,
        ]);
        $variantResource = (new ProductResource($variantProd))->toArray(request());
        $this->assertEquals('variant', $variantResource['cta_type']);
    }
}
