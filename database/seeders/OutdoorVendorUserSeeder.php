<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use App\Services\OutdoorStaffService;
use Illuminate\Database\Seeder;

class OutdoorVendorUserSeeder extends Seeder
{
    /** Giriş: acikhava@baskiyeri.com / acikhava634152K */
    public const EMAIL = 'acikhava@baskiyeri.com';

    public const NAME = 'Açık Hava';

    public const PASSWORD = 'acikhava634152K';

    public const SHOP_NAME = 'Açık Hava Reklam';

    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => self::NAME,
                'password' => self::PASSWORD,
                'role' => 'vendor',
                'is_active' => true,
            ],
        );

        $vendor = Vendor::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => self::SHOP_NAME,
                'slug' => 'acik-hava-'.$user->id,
                'email' => self::EMAIL,
                'is_active' => true,
            ],
        );

        $vendor->forceFill([
            'name' => self::SHOP_NAME,
            'email' => self::EMAIL,
            'is_active' => true,
            'outdoor_enabled' => true,
            'outdoor_expires_at' => null,
            'registration_tracks' => ['outdoor'],
            'verification_status' => 'verified',
        ])->save();

        if ($user->vendor_id !== $vendor->id) {
            $user->update(['vendor_id' => $vendor->id]);
        }

        app(OutdoorStaffService::class)->ensureOwner($vendor);
    }
}
