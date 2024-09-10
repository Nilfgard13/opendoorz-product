<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Product::factory(), // Mengaitkan gambar dengan produk yang dibuat oleh factory
            'image_path' => $this->faker->imageUrl(), // Menghasilkan URL gambar dummy
        ];
    }
}
