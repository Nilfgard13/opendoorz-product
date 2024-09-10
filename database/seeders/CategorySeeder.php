<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data kategori contoh yang akan diisi ke dalam tabel
        $categories = [
            ['name' => 'Model 1', 'description' => 'Electronic devices and gadgets'],
            ['name' => 'Model 2', 'description' => 'Furniture and home decor'],
            ['name' => 'Model 3', 'description' => 'Clothing and apparel'],
            ['name' => 'Model 4', 'description' => 'Books and literature'],
            ['name' => 'Model 5', 'description' => 'Sports equipment and apparel'],
        ];

        // Insert data ke dalam tabel categories
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
