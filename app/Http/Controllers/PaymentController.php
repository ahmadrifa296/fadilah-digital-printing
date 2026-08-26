<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Midtrans\MidtransCallbackHandler;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
   /**
    * PaymentController menggunakan constructor injection via Laravel Service Container.
    * MidtransService dan MidtransCallbackHandler di-resolve otomatis oleh IoC container.
    *
    * Catatan: Logika Midtrans TIDAK ada di CheckoutController.
    * CheckoutController hanya membuat Order dan redirect ke payment.
    * PaymentController yang bertanggung jawab atas seluruh interaksi Midtrans.
    */
   public function __construct(
       private readonly MidtransService        $midtransService,
       private readonly MidtransCallbackHandler $callbackHandler
   ) {}

   // =========================================================================
   // PUBLIC METHODS
   // =========================================================================

   /**
    * Tampilkan halaman pembayaran dan generate Snap Token.
    * Route: GET /payment/{order}/pay (ditambahkan di Tahap 9)
    *
    * Authorization:
    *   - Customer hanya bisa membayar pesanan miliknya sendiri
    *   - Admin/Owner bisa melihat halaman pembayaran semua pesanan
    *
    * Snap Token:
    *   - Di-generate setiap kali halaman ini dibuka (TIDAK disimpan ke DB)
    *   - Dikirim ke View untuk digunakan oleh Snap.js di frontend
    *   - Expire otomatis dalam ~24 jam
    *
    * @param  Order  $order  Route model binding berdasarkan Order ID
    */
   public function pay(Order $order): View|RedirectResponse
   {
       // ------------------------------------------------------------------ //
       // Authorization: customer hanya bisa membayar order miliknya sendiri
       // Admin/Owner bisa akses semua order (untuk testing/monitoring)
       // ------------------------------------------------------------------ //
       $user = Auth::user();
       if ($user && $user->role === 'customer' && $order->user_id !== Auth::id()) {
           abort(403, 'Anda tidak berhak mengakses halaman pembayaran ini.');
       }

       // ------------------------------------------------------------------ //
       // Jika order sudah dibayar, redirect ke halaman sukses
       // ------------------------------------------------------------------ //
       if ($order->isPaid()) {
           return redirect()->route('payment.success', $order)
               ->with('info', 'Pesanan ini sudah lunas.');
       }

       // ------------------------------------------------------------------ //
       // Jika order sudah dibatalkan, redirect dengan pesan error
       // ------------------------------------------------------------------ //
       if ($order->order_status === 'dibatalkan') {
           return redirect()->route('dashboard')
               ->with('error', 'Pesanan ini sudah dibatalkan dan tidak dapat dibayar.');
       }

       // ------------------------------------------------------------------ //
       // Generate Snap Token setiap kali halaman dibuka
       // Token TIDAK disimpan ke database (sesuai requirement)
       // ------------------------------------------------------------------ //
       try {
           $order->loadMissing(['user', 'orderDetails.product']);

           $snapToken = $this->midtransService->createSnapToken($order);
           $clientKey = config('midtrans.client_key');
           $snapUrl   = $this->midtransService->getSnapUrl();

           return view('payment.pay', compact('order', 'snapToken', 'clientKey', 'snapUrl'));

       } catch (\Throwable $e) {
           Log::error('Payment: Failed to generate Snap Token', [
               'order_id'   => $order->id,
               'invoice'    => $order->invoice_number,
               'user_id'    => Auth::id(),
               'exception'  => $e->getMessage(),
               'file'       => $e->getFile(),
               'line'       => $e->getLine(),
           ]);

           return back()->with('error',
               'Gagal menghubungi layanan pembayaran. ' .
               'Silakan coba lagi atau hubungi admin.'
           );
       }
   }

   /**
    * Terima dan proses callback (webhook) dari server Midtrans.
    * Route: POST /payment/callback (ditambahkan di Tahap 9)
    *
    * Penting:
    *   - Route ini harus dikecualikan dari CSRF verification
    *     (tambahkan ke VerifyCsrfToken::$except di Tahap 9)
    *   - Tidak perlu auth middleware karena dipanggil oleh server Midtrans
    *   - Selalu return HTTP 200 agar Midtrans tidak retry berkali-kali,
    *     KECUALI jika ada error server (500)
    *
    * Response:
    *   - 200 OK: callback berhasil diproses
    *   - 422 Unprocessable: signature invalid atau order tidak ditemukan
    *   - 500 Server Error: terjadi error sistem
    */
   public function callback(Request $request): JsonResponse
   {
       // Midtrans mengirim data sebagai JSON atau form-data
       // Laravel's $request->all() menangani keduanya
       $payload = $request->all();

       try {
           $success = $this->callbackHandler->handle($payload);

           if ($success) {
               return response()->json([
                   'status'  => 'ok',
                   'message' => 'Callback processed successfully.',
               ], 200);
           }

           // Callback ditolak (signature invalid / order tidak ditemukan)
           // Return 200 agar Midtrans tidak retry tanpa henti
           return response()->json([
               'status'  => 'rejected',
               'message' => 'Callback could not be processed.',
           ], 200);

       } catch (\Throwable $e) {
           Log::error('Payment: Callback — Unexpected system error', [
               'payload'   => array_diff_key($payload, ['signature_key' => '']), // hide signature
               'exception' => $e->getMessage(),
               'file'      => $e->getFile(),
               'line'      => $e->getLine(),
           ]);

           return response()->json([
               'status'  => 'error',
               'message' => 'Internal server error.',
           ], 500);
       }
   }

   /**
    * Tampilkan halaman sukses setelah pembayaran berhasil.
    * Route: GET /payment/{order}/success (ditambahkan di Tahap 9)
    *
    * Halaman ini dapat diakses dari:
    *   1. Redirect otomatis dari Snap.js setelah pembayaran
    *   2. Redirect dari pay() jika order sudah dibayar
    *   3. Direct link dari halaman riwayat transaksi
    */
   public function success(Order $order, Request $request): View|RedirectResponse
   {
       // Authorization: customer hanya bisa lihat order miliknya
       $user = Auth::user();
       if ($user && $user->role === 'customer' && $order->user_id !== Auth::id()) {
           abort(403, 'Anda tidak berhak mengakses halaman ini.');
       }

       // Sinkronisasi status pembayaran real-time dari Midtrans (failover & local development helper)
       if ($order->payment_status !== 'settlement' && $order->payment_status !== 'capture' && $request->filled('midtrans_order_id')) {
           try {
               // Panggil API status Midtrans secara langsung
               $statusResponse = \Midtrans\Transaction::status($request->midtrans_order_id);
               $statusResponse = (array) $statusResponse;

               if (isset($statusResponse['transaction_status'])) {
                   $paymentStatus = $this->midtransService->mapPaymentStatus(
                       $statusResponse['transaction_status'],
                       $statusResponse['fraud_status'] ?? null
                   );

                   // Jika terdeteksi Lunas, update status database lokal saat itu juga
                   if (in_array($paymentStatus, ['settlement', 'capture'])) {
                       \Illuminate\Support\Facades\DB::transaction(function () use ($order, $statusResponse, $paymentStatus) {
                           $order->update([
                               'payment_status'          => $paymentStatus,
                               'payment_type'            => $statusResponse['payment_type'] ?? null,
                               'midtrans_transaction_id' => $statusResponse['transaction_id'] ?? null,
                               'paid_at'                 => \Illuminate\Support\Carbon::now(),
                               'order_status'            => 'diproses',
                               'status'                  => 'paid', // backward compat
                           ]);
                       });
                   }
               }
           } catch (\Throwable $e) {
               Log::warning('Payment Success: Failed to sync status with Midtrans API', [
                   'order_id' => $order->id,
                   'exception' => $e->getMessage()
               ]);
           }
       }

       $order->loadMissing(['user', 'orderDetails.product']);

       return view('payment.success', compact('order'));
    }
}
