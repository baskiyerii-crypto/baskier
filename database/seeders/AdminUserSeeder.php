<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /** Giriş: e-posta yusuf@baskiyeri.com, şifre: yusuf634152K (Laravel formu e-posta kullanır). */
    public const ADMIN_EMAIL = 'yusuf@baskiyeri.com';

    public const ADMIN_NAME = 'yusuf';

    public const ADMIN_PASSWORD = 'yusuf634152K';

    public function run(): void
    {
        $existing = User::query()
            ->where('email', self::ADMIN_EMAIL)
            ->orWhere('email', 'admin@baskiyeri.com')
            ->first();

        if ($existing) {
            $existing->fill([
                'name' => self::ADMIN_NAME,
                'email' => self::ADMIN_EMAIL,
                'password' => self::ADMIN_PASSWORD,
                'role' => 'admin',
            ])->save();

            return;
        }

        User::create([
            'name' => self::ADMIN_NAME,
            'email' => self::ADMIN_EMAIL,
            'password' => self::ADMIN_PASSWORD,
            'role' => 'admin',
        ]);
    }
}
