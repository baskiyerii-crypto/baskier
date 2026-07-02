<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        $vendors = Vendor::all();

        if ($categories->isEmpty() || $vendors->isEmpty()) {
            return;
        }

        Product::factory()
            ->count(80)
            ->make()
            ->each(function (Product $product) use ($categories, $vendors) {
                $product->category_id = $categories->random()->id;
                $product->vendor_id = $vendors->random()->id;
                $product->save();
            });
    }
}
