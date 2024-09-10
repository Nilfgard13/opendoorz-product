<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(CategorySeeder::class);
        // Product::factory()->count(5)->create();
        // Image::factory()->count(5)->create();
        // ProductCategory::factory()->count(5)->create();
        // $this->call(ProductCategorySeeder::class);
    }
}
