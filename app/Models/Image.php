<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    // Tentukan koneksi database yang digunakan
    protected $connection = 'product_db';

    // Tentukan tabel yang terkait dengan model ini
    protected $table = 'property_images';

    // Tentukan atribut yang dapat diisi
    protected $fillable = [
        'property_id',
        'image_path',
    ];

    // Relasi dengan model Product (satu gambar milik satu produk)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
