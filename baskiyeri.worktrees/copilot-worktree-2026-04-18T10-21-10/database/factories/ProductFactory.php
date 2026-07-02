<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'sku' => strtoupper($this->faker->bothify('PRD-#####')),
            'main_image' => null,
            'price' => $this->faker->randomFloat(2, 10, 500),
            'stock' => $this->faker->numberBetween(0, 500),
            'is_featured' => $this->faker->boolean(40),
            'is_active' => true,
            'attributes' => null,
            'short_description' => $this->faker->sentence(8),
            'description' => $this->faker->paragraph(),
        ];
    }
}
