<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Order extends Model
{
   use HasFactory;

   /**
    * Kolom yang diizinkan untuk mass assignment.
    *
    * Catatan transisi:
    *   - 'status' (ENUM lama) dipertahankan di $fillable selama masa transisi
    *     agar Controller & View lama tidak error sebelum diperbarui di Tahap 7-10.
    *   - 'order_status' adalah standar baru (VARCHAR), menggantikan 'status'.
    *   - Setelah Tahap 10 selesai, 'status' akan dihapus dari $fillable dan kolom
    *     ENUM lama akan di-drop via migration terpisah.
    */
   protected $fillable = [
       'user_id',
       'invoice_number',
       'order_status',
       'payment_status',
       'payment_type',
       'midtrans_transaction_id',
       'paid_at',
       'total_price',
       'design_file',
       'notes',
       'tracking_status',
       'tracking_resi',
       // Dipertahankan sementara untuk backward compatibility (Tahap 7-10 belum selesai)
       'status',
       // Alamat Pengiriman Snapshot
       'receiver_name',
       'phone',
       'province',
       'city',
       'district',
       'subdistrict',
       'postal_code',
       'rt',
       'rw',
       'no_rumah',
       'patokan',
       'full_address',
       'address_label',
       'shipping_cost',
       'shipping_courier',
       'shipping_service',
       'shipping_estimation',
       'shipping_status',
       'tracking_number',
       'tracking_url',
       'shipping_snapshot',
       'biteship_order_id',
   ];

   /**
    * Virtual attributes appended to array/JSON serialization.
    */
   protected $appends = [
       'shipping_method',
   ];

   /**
    * Type casting untuk kolom-kolom spesifik.
    * Menggunakan method casts() sesuai Laravel 12 best practice.
    */
   protected function casts(): array
   {
       return [
           'paid_at'     => 'datetime',
           'total_price' => 'integer',
           'order_status' => \App\Enums\OrderStatus::class,
       ];
   }

   /**
    * Virtual attribute for shipping_method.
    */
   protected function shippingMethod(): Attribute
   {
       return Attribute::make(
           get: fn () => $this->shipping_courier === 'pickup' ? 'pickup' : 'delivery'
       );
   }

   // =========================================================================
   // BUSINESS LOGIC
   // =========================================================================

   /**
    * Generate nomor invoice unik dengan format: FDP-YYYYMMDD-XXXXXX
    *
    * Contoh: FDP-20260628-000001
    *
    * Race Condition Safety:
    *   lockForUpdate() mengunci baris yang sedang dibaca secara eksklusif
    *   selama transaksi berlangsung. Transaksi lain yang mencoba membaca
    *   baris yang sama akan menunggu hingga transaksi ini commit/rollback.
    *
    * PENTING: Method ini HARUS dipanggil di dalam DB::transaction() agar
    * lockForUpdate() berfungsi. Jika dipanggil di luar transaksi, lock
    * tidak aktif dan potensi race condition tetap ada.
    *
    * Lapisan keamanan kedua: Kolom invoice_number memiliki constraint
    * UNIQUE di database. Jika dua transaksi serentak lolos dari lock,
    * hanya satu INSERT yang akan berhasil — satu lagi akan throw exception
    * yang ditangkap oleh blok catch di controller.
    *
    * @return string Format FDP-YYYYMMDD-XXXXXX
    */
   public static function generateInvoiceNumber(): string
   {
       $date   = Carbon::now()->format('Ymd');
       $prefix = "FDP-{$date}-";

       // lockForUpdate() mencegah race condition di dalam DB::transaction()
       $lastInvoice = self::where('invoice_number', 'LIKE', "{$prefix}%")
                          ->lockForUpdate()
                          ->orderBy('invoice_number', 'desc')
                          ->value('invoice_number');

       $newSequence = $lastInvoice
           ? (int) substr($lastInvoice, -6) + 1
           : 1;

       return $prefix . str_pad($newSequence, 6, '0', STR_PAD_LEFT);
   }

   /**
    * Cek apakah pesanan sudah dibayar lunas.
    * Berlaku untuk status 'settlement' (transfer) dan 'capture' (kartu kredit).
    */
   public function isPaid(): bool
   {
       return in_array($this->payment_status, ['settlement', 'capture']);
   }

   /**
    * Cek apakah pesanan masih menunggu pembayaran.
    */
   public function isPending(): bool
   {
       return $this->payment_status === 'pending';
   }

   // =========================================================================
   // ACCESSOR (Computed Properties untuk View)
   // Menggunakan sintaks Attribute::make() sesuai Laravel 9+ / 12 best practice.
   // Contoh penggunaan di Blade: {{ $order->formatted_total_price }}
   // =========================================================================

   /**
    * Total harga terformat: "Rp 150.000"
    */
   protected function formattedTotalPrice(): Attribute
   {
       return Attribute::make(
           get: fn () => 'Rp ' . number_format($this->total_price, 0, ',', '.')
       );
   }

   /**
    * Label order_status yang ramah pengguna.
    * Contoh: 'diproses' → 'Sedang Diproses'
    */
   protected function orderStatusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->order_status?->label() ?? '-'
        );
    }

   /**
    * Label payment_status yang ramah pengguna.
    * Contoh: 'settlement' → 'Lunas'
    */
   protected function paymentStatusLabel(): Attribute
   {
       return Attribute::make(
           get: fn () => match ($this->payment_status) {
               'pending'              => 'Belum Dibayar',
               'settlement', 'capture' => 'Lunas',
               'expire'               => 'Kedaluwarsa',
               'cancel'               => 'Dibatalkan',
               'deny'                 => 'Ditolak',
               'refund'               => 'Dikembalikan',
               default                => ucfirst($this->payment_status ?? '-'),
           }
       );
   }

   /**
    * Label tracking_status yang ramah pengguna.
    */
   protected function trackingStatusLabel(): Attribute
   {
       return Attribute::make(
           get: fn () => match ($this->tracking_status) {
               'pending'     => 'Menunggu Pembayaran/Konfirmasi',
               'antrian'     => 'Dalam Antrian Cetak',
               'diproduksi'  => 'Sedang Dicetak',
               'siap_diambil' => 'Siap Diambil / Dikirim',
               'dikirim'     => 'Sedang Dikirim',
               'selesai'     => 'Selesai',
               'dibatalkan'  => 'Dibatalkan',
               default       => ucfirst($this->tracking_status ?? '-'),
           }
       );
   }

   /**
    * CSS class badge Tailwind untuk order_status.
    * Contoh: {{ $order->order_status_badge_class }}
    */
   protected function orderStatusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->order_status?->badgeClass() ?? 'bg-gray-100 text-gray-800'
        );
    }

   /**
    * CSS class badge Tailwind untuk payment_status.
    */
   protected function paymentStatusBadgeClass(): Attribute
   {
       return Attribute::make(
           get: fn () => match ($this->payment_status) {
               'settlement', 'capture' => 'bg-green-100 text-green-800',
               'pending'               => 'bg-yellow-100 text-yellow-800',
               'expire'                => 'bg-orange-100 text-orange-800',
               'cancel', 'deny'        => 'bg-red-100 text-red-800',
               'refund'                => 'bg-purple-100 text-purple-800',
               default                 => 'bg-gray-100 text-gray-800',
           }
       );
   }

   /**
    * CSS class badge Tailwind untuk tracking_status.
    */
   protected function trackingStatusBadgeClass(): Attribute
   {
       return Attribute::make(
           get: fn () => match ($this->tracking_status) {
               'pending'     => 'bg-yellow-100 text-yellow-800',
               'antrian'     => 'bg-purple-100 text-purple-800',
               'diproduksi'  => 'bg-blue-100 text-blue-800',
               'siap_diambil' => 'bg-indigo-100 text-indigo-800',
               'dikirim'     => 'bg-cyan-100 text-cyan-800',
               'selesai'     => 'bg-green-100 text-green-800',
               'dibatalkan'  => 'bg-red-100 text-red-800',
               default       => 'bg-gray-100 text-gray-800',
           }
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

   // =========================================================================
   // RELASI ELOQUENT
   // =========================================================================

   /**
    * Relasi: Pesanan ini milik User (pelanggan) mana.
    * Tipe: Many-to-One (BelongsTo)
    */
   public function user(): BelongsTo
   {
       return $this->belongsTo(User::class);
   }

   /**
    * Relasi: Satu pesanan memiliki banyak baris detail produk.
    * Tipe: One-to-Many (HasMany)
    */
   public function orderDetails(): HasMany
   {
       return $this->hasMany(OrderDetail::class);
   }

   /**
    * Relasi: Ulasan ulasan dari order ini.
    */
   public function reviews(): HasMany
   {
       return $this->hasMany(Review::class);
   }

   /**
    * Relasi ke data pengiriman (Shipment).
    */
   public function shipment(): \Illuminate\Database\Eloquent\Relations\HasOne
   {
       return $this->hasOne(Shipment::class);
   }

    /**
     * Relasi ke data klaim komplain / garansi (OrderClaim).
     */
    public function claim(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OrderClaim::class);
    }

   /**
    * Otomatis selesaikan pesanan yang berstatus 'Delivered' selama lebih dari 3 hari.
    */
   public static function autoCompleteDeliveredOrders()
    {
        $oldDeliveredTrackings = \App\Models\ShipmentTracking::where('status', 'Delivered')
            ->where('created_at', '<=', \Illuminate\Support\Carbon::now()->subDays(3))
            ->with('shipment.order')
            ->get();

        $workflowService = app(\App\Services\OrderWorkflowService::class);

        foreach ($oldDeliveredTrackings as $tracking) {
            $shipment = $tracking->shipment;
            if ($shipment && $shipment->order && $shipment->order->order_status !== \App\Enums\OrderStatus::SELESAI) {
                $workflowService->complete($shipment->order, 'Sistem');
            }
        }
    }
}