<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentCategories = [
            'Çakmaklar',
            'Kalemler',
            'Ajandalar',
            'Kupa Bardaklar',
            'Anahtarlıklar',
        ];

        foreach ($parentCategories as $name) {
            Category::factory()->create([
                'name' => $name,
                'slug' => \Str::slug($name),
                'parent_id' => null,
                'is_active' => true,
            ]);
        }
    }
}
