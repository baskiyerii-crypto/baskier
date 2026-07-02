<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;

class BusinessTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['slug' => 'davetiye', 'name' => 'Davetiye firmaları', 'sort_order' => 10],
            ['slug' => 'matbaa', 'name' => 'Matbaa', 'sort_order' => 20],
            ['slug' => 'reklam', 'name' => 'Reklam ajansları', 'sort_order' => 30],
            ['slug' => 'tabela', 'name' => 'Tabela & görsel iletişim', 'sort_order' => 40],
            ['slug' => 'hediyelik-baski', 'name' => 'Hediyelik baskılı ürünler', 'sort_order' => 50],
            ['slug' => 'freelancer-tasarim', 'name' => 'Freelancer (grafik / web / mobil)', 'sort_order' => 60],
            ['slug' => 'kirtasiye', 'name' => 'Kırtasiye', 'sort_order' => 70],
            ['slug' => 'promosyon', 'name' => 'Promosyon ürünleri', 'sort_order' => 80],
            ['slug' => 'yazici-sarf', 'name' => 'Yazıcı & sarf malzemesi', 'sort_order' => 90],
        ];

        foreach ($types as $t) {
            BusinessType::updateOrCreate(
                ['slug' => $t['slug']],
                ['name' => $t['name'], 'sort_order' => $t['sort_order']]
            );
        }
    }
}
