<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

/**
 * Freelancer işleri için komisyon ve sipariş kaydı; ödemeler contractor kullanıcıya işlenir.
 */
class PlatformVendorSeeder extends Seeder
{
    public const SLUG = 'platform-freelancer';

    public function run(): void
    {
        $admin = User::where('role', 'admin')->orderBy('id')->first();
        Vendor::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'user_id' => $admin?->id,
                'name' => 'BaskıYeri Freelancer',
                'email' => 'freelancer@baskiyeri.com',
                'is_active' => true,
                'description' => 'Platform üzerinden yürütülen freelancer proje siparişleri.',
            ]
        );
    }

    public static function vendorId(): int
    {
        return (int) Vendor::where('slug', self::SLUG)->value('id');
    }
}
