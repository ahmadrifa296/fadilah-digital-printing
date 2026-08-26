<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    /**
     * Kolom yang diizinkan untuk mass assignment.
     *
     * Catatan desain:
     *   - 'price'    : snapshot harga satuan saat item ditambahkan ke cart.
     *                  Nilai ini tidak berubah walaupun admin mengubah harga produk.
     *   - 'subtotal' : qty × price, dihitung dan disimpan oleh CartController
     *                  agar tidak perlu kalkulasi ulang setiap kali render view.
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'qty',
        'price',
        'subtotal',
        'notes',
        'design_file',
        'ukuran',
        'custom_length',
        'custom_width',
        'custom_area',
        'price_per_m2',
        'bahan',
        'finishing',
        'custom_text',
    ];

    /**
     * Type casting untuk kolom-kolom spesifik.
     */
    protected function casts(): array
    {
        return [
            'qty'      => 'integer',
            'price'    => 'integer',
            'subtotal' => 'integer',
            'custom_length' => 'float',
            'custom_width' => 'float',
            'custom_area' => 'float',
            'price_per_m2' => 'integer',
        ];
    }

    // =========================================================================
    // RELASI ELOQUENT
    // =========================================================================

    /**
     * Relasi: Item cart ini milik User mana.
     * Tipe: Many-to-One (BelongsTo)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Item cart ini merujuk ke Produk mana.
     * Tipe: Many-to-One (BelongsTo)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // =========================================================================
    // ACCESSOR (Computed Properties untuk View)
    // Contoh penggunaan: {{ $cartItem->formatted_price }}
    // =========================================================================

    /**
     * Harga satuan terformat: "Rp 30.000"
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp ' . number_format($this->price, 0, ',', '.')
        );
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

    /**
     * Subtotal terformat: "Rp 90.000"
     */
    protected function formattedSubtotal(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp ' . number_format($this->subtotal, 0, ',', '.')
        );
    }

    // =========================================================================
    // STATIC HELPERS (Query langsung ke database tanpa instansiasi model)
    // =========================================================================

    /**
     * Total jumlah semua item (qty) di cart milik seorang user.
     * Berguna untuk badge counter di navbar.
     *
     * Contoh: Cart::getTotalQtyForUser(Auth::id())
     */
    public static function getTotalQtyForUser(?int $userId): int
    {
        if (!$userId) {
            return 0;
        }
        return (int) self::where('user_id', $userId)->sum('qty');
    }

    /**
     * Total nilai belanja (sum of subtotal) di cart milik seorang user.
     * Berguna untuk menampilkan grand total sebelum checkout.
     *
     * Contoh: Cart::getTotalAmountForUser(Auth::id())
     */
    public static function getTotalAmountForUser(?int $userId): int
    {
        if (!$userId) {
            return 0;
        }
        return (int) self::where('user_id', $userId)->sum('subtotal');
    }

    /**
     * Jumlah baris unik produk di cart milik seorang user.
     * Berguna untuk badge keranjang di navbar (jumlah jenis produk).
     *
     * Contoh: Cart::getItemCountForUser(Auth::id())
     */
    public static function getItemCountForUser(?int $userId): int
    {
        if (!$userId) {
            return 0;
        }
        return self::where('user_id', $userId)->count();
    }
}

