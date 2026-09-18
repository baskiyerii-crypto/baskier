<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OutdoorCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Billboard / Standart Pano',
            'Raket / CLP Pano',
            'Megaboard',
            'Dijital Ekran (LED / Totem)',
            'Kuleboard',
            'Köprü / Üstgeçit Reklamı',
            'Bina Cephe / Duvar Giydirme',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                [
                    'name' => $name,
                    'channel' => Category::CHANNEL_OUTDOOR,
                ],
                [
                    'slug' => Str::slug($name),
                    'is_active' => true,
                    'parent_id' => null,
                ]
            );
        }
    }
}