<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data kategori contoh yang akan diisi ke dalam tabel
        $categories = [
            ['property_id' => '1', 'category_id' => '1'],
            ['property_id' => '2', 'category_id' => '2'],
            ['property_id' => '3', 'category_id' => '3'],
            ['property_id' => '4', 'category_id' => '3'],
        ];

        // Insert data ke dalam tabel categories
        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
}
