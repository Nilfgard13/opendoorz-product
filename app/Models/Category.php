<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Tentukan koneksi database yang digunakan (sama seperti Product)
    protected $connection = 'product_db';

    // Tentukan tabel yang terkait dengan model ini
    protected $table = 'categories';

    // Tentukan atribut yang dapat diisi
    protected $fillable = [
        'name',
        'description',
    ];

    // Relasi dengan model Product (satu kategori memiliki banyak produk)
    public function products()
    {
        return $this->hasMany(Product::class, 'property_categories', 'category_id');
    }
}
