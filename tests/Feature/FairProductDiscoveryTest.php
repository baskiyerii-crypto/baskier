<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Services\FairProductDiscoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FairProductDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_vendor_cannot_monopolize_discovery_in_first_round(): void
    {
        $category = Category::factory()->create();

        $vendor1 = Vendor::factory()->create(['is_active' => true]);
        $vendor2 = Vendor::factory()->create(['is_active' => true]);
        $vendor3 = Vendor::factory()->create(['is_active' => true]);

        // Vendor 1 has 5 products
        for ($i = 1; $i <= 5; $i++) {
            Product::factory()->create([
                'vendor_id' => $vendor1->id,
                'category_id' => $category->id,
                'stock' => 10,
                'is_active' => true,
            ]);
        }

        // Vendor 2 has 2 products
        for ($i = 1; $i <= 2; $i++) {
            Product::factory()->create([
                'vendor_id' => $vendor2->id,
                'category_id' => $category->id,
                'stock' => 10,
                'is_active' => true,
            ]);
        }

        // Vendor 3 has 1 product
        Product::factory()->create([
            'vendor_id' => $vendor3->id,
            'category_id' => $category->id,
            'stock' => 10,
            'is_active' => true,
        ]);

        $service = new FairProductDiscoveryService();
        $discovery = $service->getDiscoveryProducts(limit: 3, seedDate: '2026-09-17');

        $this->assertCount(3, $discovery);

        // In the first round of 3 items across 3 vendors, each vendor must appear exactly once
        $vendorIds = $discovery->pluck('vendor_id')->all();
        $this->assertContains($vendor1->id, $vendorIds);
        $this->assertContains($vendor2->id, $vendorIds);
        $this->assertContains($vendor3->id, $vendorIds);
        $this->assertCount(3, array_unique($vendorIds), 'Vendor cannot have 2 products in the first round when other vendors have products available.');
    }

    public function test_discovery_order_is_deterministic_per_day_and_rotates_on_new_day(): void
    {
        $category = Category::factory()->create();

        $vendors = [];
        for ($v = 1; $v <= 5; $v++) {
            $vendor = Vendor::factory()->create(['is_active' => true]);
            $vendors[] = $vendor;
            Product::factory()->create([
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'stock' => 10,
                'is_active' => true,
            ]);
        }

        $service = new FairProductDiscoveryService();

        // Same date returns identical order
        $run1 = $service->getDiscoveryProducts(limit: 5, seedDate: '2026-09-17')->pluck('id')->all();
        $run2 = $service->getDiscoveryProducts(limit: 5, seedDate: '2026-09-17')->pluck('id')->all();
        $this->assertEquals($run1, $run2, 'Same date must return strictly identical product ordering.');

        // Different date changes the vendor hash seed and rotation
        $runDifferentDay = $service->getDiscoveryProducts(limit: 5, seedDate: '2026-09-18')->pluck('id')->all();
        $this->assertNotEquals($run1, $runDifferentDay, 'New day must rotate the fair order.');
    }

    public function test_inactive_out_of_stock_or_unapproved_products_are_excluded(): void
    {
        $category = Category::factory()->create();
        $activeVendor = Vendor::factory()->create(['is_active' => true]);
        $inactiveVendor = Vendor::factory()->create(['is_active' => false]);

        // Active, in-stock product
        $valid = Product::factory()->create([
            'vendor_id' => $activeVendor->id,
            'category_id' => $category->id,
            'stock' => 5,
            'is_active' => true,
        ]);

        // Out of stock product
        $outOfStock = Product::factory()->create([
            'vendor_id' => $activeVendor->id,
            'category_id' => $category->id,
            'stock' => 0,
            'is_active' => true,
        ]);

        // Inactive product
        $inactive = Product::factory()->create([
            'vendor_id' => $activeVendor->id,
            'category_id' => $category->id,
            'stock' => 10,
            'is_active' => false,
        ]);

        // Product on inactive vendor
        $inactiveVendorProd = Product::factory()->create([
            'vendor_id' => $inactiveVendor->id,
            'category_id' => $category->id,
            'stock' => 10,
            'is_active' => true,
        ]);

        $service = new FairProductDiscoveryService();
        $discovery = $service->getDiscoveryProducts(limit: 10);

        $ids = $discovery->pluck('id')->all();
        $this->assertContains($valid->id, $ids);
        $this->assertNotContains($outOfStock->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
        $this->assertNotContains($inactiveVendorProd->id, $ids);
    }
}
