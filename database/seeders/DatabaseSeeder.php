<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            VendorUserSeeder::class,
            OutdoorVendorUserSeeder::class,
            DemoPanelUsersSeeder::class,
            DemoCustomerSeeder::class,
            PlatformVendorSeeder::class,
            BusinessTypeSeeder::class,
            CategorySeeder::class,
            VendorSeeder::class,
            ProductSeeder::class,
            FreelancerJobListingSeeder::class,
            TurkiyeGeographySeeder::class,
            OutdoorLocationDataSeeder::class,
            WorldPlacesIso3166Seeder::class,
            OutdoorCategorySeeder::class,
        ]);
    }
}
