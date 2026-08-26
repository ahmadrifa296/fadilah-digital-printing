<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * Kolom yang diizinkan untuk mass assignment.
     *
     * Menggunakan $fillable (eksplisit) alih-alih $guarded = ['id']
     * sesuai Laravel 12 best practice untuk keamanan mass assignment.
     *
     * Perhatian: Nama kolom di tabel adalah 'category_name' (bukan 'name').
     * Pastikan semua operasi CategoryController menggunakan key 'category_name'.
     */
    protected $fillable = [
        'category_name',
    ];

    // =========================================================================
    // RELASI ELOQUENT
    // =========================================================================

    /**
     * Relasi: Satu Kategori memiliki banyak Produk.
     * Tipe: One-to-Many (HasMany)
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}