<?php

namespace App\Services\Midtrans;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    /**
     * Inisialisasi Midtrans SDK dengan konfigurasi dari config/midtrans.php.
     *
     * Dipanggil setiap kali class di-instantiate agar konfigurasi selalu fresh
     * dari environment (penting untuk testing dan multi-environment deployment).
     */
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized', true);
        Config::$is3ds        = config('midtrans.is_3ds', true);

        // Matikan verifikasi SSL cURL khusus di mode Sandbox/Lokal untuk mencegah error cacert.pem pada Windows/XAMPP
        if (!Config::$isProduction) {
            Config::$curlOptions = [
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [], // Sediakan key ini untuk mencegah Undefined array key 10023 di SDK Midtrans
            ];
        }
    }

    // =========================================================================
    // PUBLIC METHODS
    // =========================================================================

    /**
     * Generate Snap Token dari Midtrans API.
     *
     * Token ini digunakan di frontend untuk membuka popup Snap.
     * Token bersifat sementara (expire ~24 jam) dan TIDAK disimpan ke database.
     * Method ini harus dipanggil setiap kali halaman payment dibuka.
     *
     * @param  Order  $order  Order yang akan dibayar (harus sudah load user + orderDetails.product)
     * @return string         Snap Token dari Midtrans
     *
     * @throws \Exception     Jika API call gagal (server key salah, timeout, dll.)
     */
    public function createSnapToken(Order $order): string
    {
        $payload = $this->createTransactionPayload($order);
        return Snap::getSnapToken($payload);
    }

    /**
     * Buat payload untuk dikirim ke Midtrans API.
     *
     * Payload terdiri dari:
     *   - transaction_details: order_id dan gross_amount
     *   - customer_details: data pelanggan
     *   - item_details: daftar produk yang dipesan
     *
     * Catatan: order_id yang dikirim ke Midtrans adalah invoice_number
     * (bukan id Order), sehingga mudah dilacak pada callback.
     *
     * @param  Order  $order
     * @return array
     */
    public function createTransactionPayload(Order $order): array
    {
        // Pastikan relasi yang dibutuhkan sudah ter-load
        $order->loadMissing(['user', 'orderDetails.product']);

        return [
            'transaction_details' => [
                'order_id'     => $order->invoice_number . '-' . time(),
                'gross_amount' => (int) $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email'      => $order->user->email,
                'phone'      => $order->user->phone_number ?? '',
            ],
            'item_details' => $this->buildItemDetails($order),
        ];
    }

    /**
     * Verifikasi signature key dari callback Midtrans.
     *
     * Algoritma: SHA512(order_id + status_code + gross_amount + server_key)
     *
     * Menggunakan hash_equals() untuk constant-time comparison guna mencegah
     * timing attack. Ini adalah praktik keamanan standar untuk verifikasi HMAC.
     *
     * @param  string  $orderId       order_id dari payload callback (= invoice_number)
     * @param  string  $statusCode    status_code dari payload (contoh: "200")
     * @param  string  $grossAmount   gross_amount dari payload (contoh: "30000.00")
     * @param  string  $signatureKey  signature_key dari payload Midtrans
     * @return bool                   true jika signature valid
     */
    public function verifySignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $signatureKey
    ): bool {
        $serverKey = config('midtrans.server_key');
        $expected  = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        // hash_equals: constant-time comparison, aman dari timing attack
        return hash_equals($expected, strtolower($signatureKey));
    }

    /**
     * Map transaction_status dan fraud_status dari Midtrans ke payment_status internal.
     *
     * Referensi status Midtrans:
     * https://docs.midtrans.com/reference/get-status
     *
     * Aturan mapping:
     *   - capture + accept  → 'capture'    (kartu kredit, lunas)
     *   - capture + challenge → 'pending'  (dalam review antifraud)
     *   - settlement        → 'settlement' (transfer bank/e-wallet, lunas)
     *   - pending           → 'pending'    (menunggu pembayaran)
     *   - deny              → 'deny'       (ditolak bank/antifraud)
     *   - expire            → 'expire'     (melewati batas waktu)
     *   - cancel            → 'cancel'     (dibatalkan)
     *   - refund            → 'refund'     (dikembalikan)
     *
     * @param  string       $transactionStatus  Nilai transaction_status dari Midtrans
     * @param  string|null  $fraudStatus        Nilai fraud_status (hanya untuk kartu kredit)
     * @return string                            payment_status yang akan disimpan ke DB
     */
    public function mapPaymentStatus(string $transactionStatus, ?string $fraudStatus = null): string
    {
        return match (true) {
            // Kartu kredit: berhasil dan lolos antifraud
            $transactionStatus === 'capture' && $fraudStatus === 'accept'     => 'capture',
            // Kartu kredit: dalam review manual antifraud
            $transactionStatus === 'capture' && $fraudStatus === 'challenge'  => 'pending',
            // Transfer bank / e-wallet: settlement setelah dana diterima
            $transactionStatus === 'settlement'                               => 'settlement',
            // Menunggu pembayaran dari user
            $transactionStatus === 'pending'                                  => 'pending',
            // Ditolak oleh bank atau sistem antifraud
            $transactionStatus === 'deny'                                     => 'deny',
            // Melewati batas waktu pembayaran
            $transactionStatus === 'expire'                                   => 'expire',
            // Dibatalkan oleh merchant atau user
            $transactionStatus === 'cancel'                                   => 'cancel',
            // Dana dikembalikan ke customer
            $transactionStatus === 'refund'                                   => 'refund',
            // Fallback: status tidak dikenal, anggap pending
            default                                                           => 'pending',
        };
    }

    /**
     * Dapatkan URL Snap.js yang sesuai dengan environment (Sandbox/Production).
     *
     * @return string URL Snap.js
     */
    public function getSnapUrl(): string
    {
        return config('midtrans.snap_url');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Bangun array item_details untuk payload Midtrans.
     *
     * Aturan Midtrans: sum(price × quantity) harus sama persis dengan gross_amount.
     * Untuk menghindari masalah pembulatan (rounding), selisih pembulatan
     * ditambahkan sebagai item "Penyesuaian Harga".
     *
     * Catatan: order_details tidak memiliki kolom 'price' (hanya 'subtotal' dan 'qty'),
     * sehingga unit price dihitung dari subtotal / qty.
     *
     * @param  Order  $order
     * @return array
     */
    private function buildItemDetails(Order $order): array
    {
        $items      = [];
        $totalItems = 0;

        foreach ($order->orderDetails as $detail) {
            $qty = max((int) $detail->qty, 1);

            // Hitung harga satuan dari subtotal ÷ qty (round untuk menghindari desimal)
            $unitPrice = (int) round($detail->subtotal / $qty);

            // Pastikan subtotal per item konsisten
            $itemSubtotal = $unitPrice * $qty;
            $totalItems  += $itemSubtotal;

            $items[] = [
                'id'       => (string) $detail->product_id,
                'price'    => $unitPrice,
                'quantity' => $qty,
                // Midtrans membatasi panjang nama item maksimal 50 karakter
                'name'     => mb_substr($detail->product->product_name ?? 'Produk', 0, 50),
            ];
        }

        // Tambahkan ongkos kirim jika ada
        if ($order->shipping_cost > 0) {
            $items[] = [
                'id'       => 'shipping-fee',
                'price'    => (int) $order->shipping_cost,
                'quantity' => 1,
                'name'     => 'Ongkos Kirim (' . ($order->shipping_courier ?? 'Kurir') . ')',
            ];
            $totalItems += (int) $order->shipping_cost;
        }

        // Tambahkan item penyesuaian jika ada selisih pembulatan
        $roundingDiff = (int) $order->total_price - $totalItems;
        if ($roundingDiff !== 0) {
            $items[] = [
                'id'       => 'rounding-adjustment',
                'price'    => $roundingDiff,
                'quantity' => 1,
                'name'     => 'Penyesuaian Harga',
            ];
        }

        return $items;
    }
}
