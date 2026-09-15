<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCustomerSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'musteri@baskiyeri.com'],
            [
                'name' => 'Demo Müşteri',
                'password' => Hash::make('musteri634152K'),
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now(),
                'phone' => '5550001122',
                'phone_verified_at' => now(),
            ]
        );
    }
}
