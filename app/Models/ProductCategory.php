<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    use HasFactory;

    // Tentukan koneksi database yang digunakan
    protected $connection = 'product_db';

    // Tentukan tabel yang terkait dengan model ini
    protected $table = 'property_categories';

    protected $fillable = [
        'property_id',
        'category_id',
    ];
}
