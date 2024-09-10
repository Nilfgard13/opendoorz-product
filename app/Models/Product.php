<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    // Tentukan koneksi database yang digunakan
    protected $connection = 'product_db';

    // Tentukan tabel yang terkait dengan model ini
    protected $table = 'properties';

    // Tentukan atribut yang dapat diisi
    protected $fillable = [
        'title',
        'description',
        'price',
        'location',
        'type',
        'status',
    ];

    // Tentukan hubungan dengan model Category (misalnya, jika ada)
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'property_categories');
    }

    // Contoh relasi jika produk memiliki banyak gambar
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    // Contoh relasi jika produk memiliki banyak ulasan
    // public function reviews()
    // {
    //     return $this->hasMany(Review::class);
    // }
}
