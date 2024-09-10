<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    // Tentukan koneksi database yang digunakan
    protected $connection = 'product_db';

    // Tentukan tabel yang terkait dengan model ini
    protected $table = 'reviews';

    // Tentukan atribut yang dapat diisi
    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'comment',
    ];

    // Relasi dengan model Product (satu ulasan milik satu produk)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi dengan User jika ada (satu ulasan dibuat oleh satu pengguna)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
