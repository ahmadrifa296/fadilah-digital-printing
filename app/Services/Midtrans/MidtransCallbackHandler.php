<?php

namespace App\Services\Midtrans;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransCallbackHandler
{
   /**
    * Field yang wajib ada di setiap callback Midtrans.
    * Jika salah satu tidak ada, callback dianggap invalid.
    */
   private const REQUIRED_FIELDS = [
       'order_id',
       'status_code',
       'gross_amount',
       'signature_key',
       'transaction_status',
   ];

   /**
    * payment_status yang dianggap "pembayaran berhasil".
    * Ketika status ini diterima, order_status diubah ke 'diproses'.
    */
   private const PAID_STATUSES = ['settlement', 'capture'];

   /**
    * payment_status yang dianggap "pembayaran gagal/batal".
    * Ketika status ini diterima, order_status diubah ke 'dibatalkan'
    * dan stok produk dikembalikan.
    */
   private const FAILED_STATUSES = ['deny', 'cancel', 'expire'];

   public function __construct(
       private readonly MidtransService $midtransService
   ) {}

   // =========================================================================
   // PUBLIC METHODS
   // =========================================================================

   /**
    * Proses callback dari Midtrans secara lengkap.
    *
    * Alur:
    *   1. Log payload yang masuk
    *   2. Validasi field yang wajib ada
    *   3. Verifikasi signature key
    *   4. Temukan Order berdasarkan invoice_number
    *   5. Map transaction_status ke payment_status internal
    *   6. Update Order di dalam DB::transaction()
    *   7. Log hasil update
    *
    * @param  array  $payload  Data callback dari Midtrans (request body)
    * @return bool             true jika callback berhasil diproses
    */
   public function handle(array $payload): bool
   {
       // ------------------------------------------------------------------ //
       // 1. Log payload masuk (untuk audit dan debugging)
       //    Tidak log signature_key untuk keamanan
       // ------------------------------------------------------------------ //
       Log::info('Midtrans: Callback received', [
           'order_id'           => $payload['order_id'] ?? null,
           'transaction_status' => $payload['transaction_status'] ?? null,
           'payment_type'       => $payload['payment_type'] ?? null,
           'status_code'        => $payload['status_code'] ?? null,
           'fraud_status'       => $payload['fraud_status'] ?? null,
       ]);

       // ------------------------------------------------------------------ //
       // 2. Validasi field yang wajib ada
       // ------------------------------------------------------------------ //
       if (!$this->hasRequiredFields($payload)) {
           Log::warning('Midtrans: Callback rejected — missing required fields', [
               'received_keys' => array_keys($payload),
               'required_keys' => self::REQUIRED_FIELDS,
           ]);
           return false;
       }

       // ------------------------------------------------------------------ //
       // 3. Verifikasi signature key
       //    Mencegah callback palsu dari pihak ketiga
       // ------------------------------------------------------------------ //
       if (!$this->midtransService->verifySignature(
           $payload['order_id'],
           $payload['status_code'],
           $payload['gross_amount'],
           $payload['signature_key']
       )) {
           Log::warning('Midtrans: Callback rejected — invalid signature', [
               'order_id' => $payload['order_id'],
           ]);
           return false;
       }

       // ------------------------------------------------------------------ //
       // 4. Temukan Order berdasarkan invoice_number
       // Ekstrak invoice_number asli (misal: FDP-YYYYMMDD-XXXXXX) dengan membuang suffix timestamp
       $orderIdParts = explode('-', $payload['order_id']);
       $invoiceNumber = implode('-', array_slice($orderIdParts, 0, 3));
       
       $order = Order::where('invoice_number', $invoiceNumber)->first();

       if (!$order) {
           Log::error('Midtrans: Callback — Order not found', [
               'invoice_number' => $invoiceNumber,
               'raw_order_id'   => $payload['order_id'],
           ]);
           return false;
       }

       // ------------------------------------------------------------------ //
       // 5. Map transaction_status ke payment_status internal
       // ------------------------------------------------------------------ //
       $paymentStatus = $this->midtransService->mapPaymentStatus(
           $payload['transaction_status'],
           $payload['fraud_status'] ?? null
       );

       // ------------------------------------------------------------------ //
       // 6. Update Order di dalam DB::transaction()
       //    Memastikan semua update atomik (berhasil semua atau tidak ada)
       // ------------------------------------------------------------------ //
       try {
           DB::transaction(function () use ($order, $payload, $paymentStatus) {
               $this->updateOrder($order, $payload, $paymentStatus);
           });
       } catch (\Throwable $e) {
           Log::error('Midtrans: Callback — Failed to update order', [
               'order_id'      => $order->id,
               'invoice'       => $order->invoice_number,
               'payment_status'=> $paymentStatus,
               'exception'     => $e->getMessage(),
               'file'          => $e->getFile(),
               'line'          => $e->getLine(),
           ]);
           return false;
       }

       // ------------------------------------------------------------------ //
       // 7. Log hasil update
       // ------------------------------------------------------------------ //
       Log::info('Midtrans: Callback — Order updated successfully', [
           'order_id'        => $order->id,
           'invoice_number'  => $order->invoice_number,
           'payment_status'  => $paymentStatus,
           'order_status'    => $order->order_status,
           'payment_type'    => $payload['payment_type'] ?? null,
       ]);

       return true;
   }

   // =========================================================================
   // PRIVATE HELPERS
   // =========================================================================

   /**
    * Cek apakah semua field yang diperlukan ada di payload.
    *
    * @param  array  $payload
    * @return bool
    */
   private function hasRequiredFields(array $payload): bool
   {
       foreach (self::REQUIRED_FIELDS as $field) {
           if (empty($payload[$field])) {
               return false;
           }
       }
       return true;
   }

   /**
    * Update data Order berdasarkan callback Midtrans.
    *
    * Update yang dilakukan:
    *   - payment_status          : status pembayaran dari Midtrans
    *   - payment_type            : metode pembayaran (gopay, bank_transfer, dll.)
    *   - midtrans_transaction_id : ID transaksi unik dari Midtrans
    *   - paid_at                 : waktu pembayaran (jika status paid)
    *   - order_status            : diperbarui sesuai status pembayaran
    *   - status (lama)           : diperbarui untuk backward compat
    *
    * Jika pembayaran gagal/batal, stok produk dikembalikan (restore stock).
    *
    * @param  Order   $order
    * @param  array   $payload
    * @param  string  $paymentStatus
    * @param  string  $paymentStatus
    */
   private function updateOrder(Order $order, array $payload, string $paymentStatus): void
   {
       $updateData = [
           'payment_status'          => $paymentStatus,
           'payment_type'            => $payload['payment_type'] ?? null,
           'midtrans_transaction_id' => $payload['transaction_id'] ?? null,
       ];

       $oldPayment = $order->payment_status;

       if (in_array($paymentStatus, self::PAID_STATUSES)) {
           // ---------------------------------------------------------------- //
           // Pembayaran BERHASIL
           // → Catat waktu pembayaran
           // → Ubah order_status ke 'diproses' (admin perlu memproses pesanan)
           // ---------------------------------------------------------------- //
           $updateData['paid_at']      = Carbon::now();
           $updateData['order_status'] = 'diproses';
           $updateData['status']       = 'paid'; // backward compat ENUM lama

       } elseif (in_array($paymentStatus, self::FAILED_STATUSES)) {
           // ---------------------------------------------------------------- //
           // Pembayaran GAGAL / BATAL
           // → Ubah order_status ke 'dibatalkan'
           // → Kembalikan stok produk (restore stock)
           // ---------------------------------------------------------------- //
           $updateData['order_status'] = 'dibatalkan';
           $updateData['status']       = 'cancelled'; // backward compat ENUM lama

           $this->restoreStock($order);
       }

       $order->update($updateData);

       // Kirim Notifikasi jika status pembayaran berubah
       if ($order->payment_status !== $oldPayment) {
           $customer = $order->user;
           if ($customer) {
               if (in_array($paymentStatus, self::PAID_STATUSES)) {
                   $customer->notify(new \App\Notifications\AppNotification(
                       'Pembayaran Berhasil',
                       "Pembayaran untuk pesanan #{$order->invoice_number} berhasil diterima. Pesanan Anda kini sedang diproses.",
                       'credit-card',
                       'green',
                       route('dashboard') . '?tab=pesanan',
                       'pembayaran'
                   ));

                   $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
                   foreach ($admins as $admin) {
                       $admin->notify(new \App\Notifications\AppNotification(
                           'Pembayaran Pesanan Diterima',
                           "Pembayaran pesanan #{$order->invoice_number} sebesar Rp " . number_format($order->total_price, 0, ',', '.') . " berhasil diterima.",
                           'credit-card',
                           'green',
                           route('pesanan.show', $order->id),
                           'pembayaran'
                       ));
                   }
               } elseif (in_array($paymentStatus, self::FAILED_STATUSES)) {
                   $customer->notify(new \App\Notifications\AppNotification(
                       'Pembayaran Gagal/Kedaluwarsa',
                       "Transaksi untuk pesanan #{$order->invoice_number} gagal, dibatalkan, atau waktu pembayaran kedaluwarsa.",
                       'x-circle',
                       'red',
                       route('dashboard') . '?tab=pesanan',
                       'pembayaran'
                   ));

                   $admins = \App\Models\User::whereIn('role', ['admin', 'owner'])->get();
                   foreach ($admins as $admin) {
                       $admin->notify(new \App\Notifications\AppNotification(
                           'Transaksi Pesanan Gagal/Expired',
                           "Pembayaran pesanan #{$order->invoice_number} gagal atau kedaluwarsa.",
                           'x-circle',
                           'red',
                           route('pesanan.show', $order->id),
                           'pembayaran'
                       ));
                   }
               }
           }
       }
   }

   /**
    * Kembalikan stok produk ketika order dibatalkan/expired/denied.
    *
    * Menggunakan loadMissing agar tidak double-query jika relasi sudah ter-load.
    * Error pada restore stock di-log tapi tidak melempar exception agar
    * update order_status tetap berhasil.
    *
    * @param  Order  $order
    */
   private function restoreStock(Order $order): void
   {
       try {
           $order->loadMissing(['orderDetails.product']);

           foreach ($order->orderDetails as $detail) {
               if ($detail->product) {
                   $detail->product->increment('stock', $detail->qty);

                   // Catat mutasi stok masuk akibat pembatalan order
                   \App\Models\StockLog::create([
                       'product_id' => $detail->product_id,
                       'type' => 'in',
                       'quantity' => $detail->qty,
                       'description' => "Pengembalian stok otomatis akibat pembatalan/kedaluwarsa pesanan #{$order->invoice_number}",
                       'user_id' => $order->user_id,
                   ]);
               }
           }

           Log::info('Midtrans: Stock restored for cancelled order', [
               'order_id'       => $order->id,
               'invoice_number' => $order->invoice_number,
           ]);

       } catch (\Throwable $e) {
           // Log error tapi jangan lempar exception —
           // update order_status harus tetap berhasil
           Log::error('Midtrans: Failed to restore stock', [
               'order_id'  => $order->id,
               'exception' => $e->getMessage(),
           ]);
       }
   }
}