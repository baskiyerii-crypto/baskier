<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorUserSeeder extends Seeder
{
    /** Giriş: zeki@baskiyeri.com / zeki634152K */
    public const VENDOR_EMAIL = 'zeki@baskiyeri.com';

    public const VENDOR_NAME = 'Zeki';

    public const VENDOR_PASSWORD = 'zeki634152K';

    public const SHOP_NAME = 'Zeki Mağaza';

    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => self::VENDOR_EMAIL],
            [
                'name' => self::VENDOR_NAME,
                'password' => self::VENDOR_PASSWORD,
                'role' => 'vendor',
            ],
        );

        $vendor = Vendor::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => self::SHOP_NAME,
                'slug' => 'zeki-magaza-'.$user->id,
                'email' => self::VENDOR_EMAIL,
                'is_active' => true,
            ],
        );

        if ($user->vendor_id !== $vendor->id) {
            $user->update(['vendor_id' => $vendor->id]);
        }
    }
}
