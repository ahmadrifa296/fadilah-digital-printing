<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saving(function ($product) {
            $product->is_custom_size = in_array($product->calculation_type, ['custom_size', 'quantity_custom_size']);

            // Bidirectional price / base_price sync
            if ($product->base_price !== null && $product->base_price !== '') {
                $product->price = $product->base_price;
            } elseif ($product->price !== null && $product->price !== '') {
                $product->base_price = $product->price;
            } else {
                $product->base_price = 0;
                $product->price = 0;
            }

            // Bidirectional price_per_square_meter / price_per_m2 sync
            if ($product->price_per_square_meter !== null && $product->price_per_square_meter !== '') {
                $product->price_per_m2 = $product->price_per_square_meter;
            } elseif ($product->price_per_m2 !== null && $product->price_per_m2 !== '') {
                $product->price_per_square_meter = $product->price_per_m2;
            } else {
                $product->price_per_square_meter = 0;
                $product->price_per_m2 = 0;
            }

            // Bidirectional minimum_order / min_purchase sync
            if ($product->minimum_order !== null && $product->minimum_order !== '') {
                $product->min_purchase = $product->minimum_order;
            } elseif ($product->min_purchase !== null && $product->min_purchase !== '') {
                $product->minimum_order = $product->min_purchase;
            } else {
                $product->minimum_order = 1;
                $product->min_purchase = 1;
            }
        });
    }

    /**
     * Kolom yang diizinkan untuk mass assignment.
     *
     * Menggunakan $fillable (eksplisit) alih-alih $guarded = ['id']
     * sesuai Laravel 12 best practice untuk keamanan mass assignment.
     *
     * Kolom tabel: category_id, product_name, price, stock, image, description
     */
    protected $fillable = [
        'category_id',
        'product_type',
        'calculation_type',
        'is_custom_size',
        'product_name',
        'price',
        'base_price',
        'price_per_m2',
        'price_per_square_meter',
        'stock',
        'min_purchase',
        'minimum_order',
        'maximum_order',
        'image',
        'description',
        'weight',
        'length',
        'width',
        'height',
        'min_width',
        'max_width',
        'min_length',
        'max_length',
        'sku',
        'discount_percent',
        'discount_flat',
        'meta_title',
        'meta_description',
        'requires_design_file',
    ];

    /**
     * Type casting untuk kolom numerik.
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'base_price' => 'integer',
            'price_per_m2' => 'integer',
            'price_per_square_meter' => 'integer',
            'stock' => 'integer',
            'min_purchase' => 'integer',
            'minimum_order' => 'integer',
            'maximum_order' => 'integer',
            'discount_percent' => 'integer',
            'discount_flat' => 'integer',
            'requires_design_file' => 'boolean',
            'is_custom_size' => 'boolean',
            'min_width' => 'float',
            'max_width' => 'float',
            'min_length' => 'float',
            'max_length' => 'float',
        ];
    }

    /**
     * Accessor untuk harga final setelah diskon.
     */
    public function getFinalPriceAttribute(): int
    {
        $price = $this->price;
        if ($this->discount_percent > 0) {
            $price -= ($price * ($this->discount_percent / 100));
        }
        if ($this->discount_flat > 0) {
            $price -= $this->discount_flat;
        }
        return max(0, (int)$price);
    }

    /**
     * Accessor untuk harga m2 final setelah diskon.
     */
    public function getFinalPricePerM2Attribute(): int
    {
        $price = $this->price_per_m2;
        if ($this->discount_percent > 0) {
            $price -= ($price * ($this->discount_percent / 100));
        }
        if ($this->discount_flat > 0) {
            $price -= $this->discount_flat;
        }
        return max(0, (int)$price);
    }

    /**
     * Accessor untuk rata-rata rating.
     */
    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->where('is_visible', true)->avg('rating') ?? 0, 1);
    }

    /**
     * Accessor untuk total review.
     */
    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->where('is_visible', true)->count();
    }

    /**
     * Accessor untuk total unit terjual (hanya menghitung order dengan status selesai).
     */
    public function getSalesCountAttribute(): int
    {
        return (int) $this->orderDetails()
            ->whereHas('order', function ($query) {
                $query->where('order_status', 'selesai');
            })
            ->sum('qty');
    }

    // =========================================================================
    // RELASI ELOQUENT
    // =========================================================================

    /**
     * Relasi: Produk ini masuk ke Kategori mana.
     * Tipe: Many-to-One (BelongsTo)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi: Satu Produk bisa muncul di banyak detail pesanan.
     * Tipe: One-to-Many (HasMany)
     */
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Relasi: Satu Produk bisa ada di banyak baris cart (dari berbagai user).
     * Tipe: One-to-Many (HasMany)
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order', 'asc');
    }

    /**
     * Relasi: Variasi produk (bahan, ukuran, finishing).
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Relasi: Ulasan pelanggan.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relasi: Mutasi/riwayat perubahan stok.
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class);
    }



    /**
     * Cek apakah produk ini jenis kustom cetak.
     */
    public function isCustom(): bool
    {
        return ($this->product_type ?? 'custom') === 'custom';
    }
}