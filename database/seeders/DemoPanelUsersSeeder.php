<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Yerel / test ortamı: müşteri, admin ve satıcı paneli girişleri.
 */
class DemoPanelUsersSeeder extends Seeder
{
    public const CUSTOMER_EMAIL = 'userzeki@zeki.com';

    public const ADMIN_EMAIL = 'adminzeki@zeki.com';

    public const VENDOR_EMAIL = 'saticizeki@zeki.com';

    public const PASSWORD = 'zeki1234';

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => self::CUSTOMER_EMAIL],
            [
                'name' => 'User Zeki',
                'password' => Hash::make(self::PASSWORD),
                'role' => 'customer',
                'vendor_id' => null,
            ],
        );

        User::updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Admin Zeki',
                'password' => Hash::make(self::PASSWORD),
                'role' => 'admin',
                'vendor_id' => null,
            ],
        );

        $vendorUser = User::updateOrCreate(
            ['email' => self::VENDOR_EMAIL],
            [
                'name' => 'Satıcı Zeki',
                'password' => Hash::make(self::PASSWORD),
                'role' => 'vendor',
            ],
        );

        $vendor = Vendor::query()->firstOrCreate(
            ['user_id' => $vendorUser->id],
            [
                'name' => 'Satıcı Zeki Mağaza',
                'slug' => 'satici-zeki-'.$vendorUser->id,
                'email' => self::VENDOR_EMAIL,
                'is_active' => true,
            ],
        );

        $vendorUser->update(['vendor_id' => $vendor->id]);

        $types = BusinessType::query()->get();
        if ($types->isNotEmpty()) {
            $vendor->businessTypes()->sync(
                $types->random(min(3, $types->count()))->pluck('id')->all()
            );
        }
    }
}
