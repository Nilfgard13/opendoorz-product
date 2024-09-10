<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 50000, 500000),
            'location' => $this->faker->address,
            'type' => $this->faker->randomElement(['house', 'apartment', 'condo', 'villa']),
            'status' => $this->faker->randomElement(['available', 'sold']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
