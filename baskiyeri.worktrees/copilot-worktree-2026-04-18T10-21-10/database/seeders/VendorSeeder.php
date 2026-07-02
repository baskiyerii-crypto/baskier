<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Vendor::factory()->count(8)->create();

        $types = BusinessType::all();
        if ($types->isNotEmpty()) {
            foreach (Vendor::all() as $vendor) {
                $vendor->businessTypes()->sync(
                    $types->random(min(3, $types->count()))->pluck('id')->all()
                );
            }
        }
    }
}
