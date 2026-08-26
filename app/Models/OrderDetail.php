<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    use HasFactory;

    /**
     * Kolom yang diizinkan untuk mass assignment.
     *
     * Menggunakan $fillable (eksplisit) alih-alih $guarded = ['id']
     * sesuai Laravel 12 best practice untuk keamanan mass assignment.
     *
     * Kolom tabel: order_id, product_id, qty, subtotal, design_file, custom_text
     *
     * Catatan: Tidak ada kolom 'price' (harga satuan) di tabel order_details.
     * Nilai harga per-satuan dapat dihitung dari: subtotal / qty.
     * Jika di masa mendatang diperlukan kolom 'price' terpisah di order_details,
     * tambahkan via migration baru + tambahkan ke $fillable ini.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
        'subtotal',
        'design_file',
        'custom_text',
        'ukuran',
        'custom_length',
        'custom_width',
        'custom_area',
        'price_per_m2',
        'bahan',
        'finishing',
        'estimasi_pengerjaan',
    ];

    /**
     * Type casting untuk kolom numerik.
     */
    protected function casts(): array
    {
        return [
            'qty'      => 'integer',
            'subtotal' => 'integer',
            'custom_length' => 'float',
            'custom_width' => 'float',
            'custom_area' => 'float',
            'price_per_m2' => 'integer',
        ];
    }

    /**
     * URL file desain yang dinormalisasi untuk penayangan di View
     */
    protected function designFileUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->design_file) {
                    return null;
                }
                if (str_starts_with($this->design_file, '/storage/')) {
                    return asset($this->design_file);
                }
                return asset('storage/' . $this->design_file);
            }
        );
    }

    // =========================================================================
    // RELASI ELOQUENT
    // =========================================================================

    /**
     * Relasi: Detail barang ini masuk ke pesanan (Order) mana.
     * Tipe: Many-to-One (BelongsTo)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi: Detail barang ini merujuk ke data Produk mana.
     * Tipe: Many-to-One (BelongsTo)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}