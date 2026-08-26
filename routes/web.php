<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\ShippingController;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// ==================================================================== //
// HALAMAN DEPAN — Publik, tanpa login                                   //
// ==================================================================== //
Route::get('/', function () {
    $products = Product::with(['category', 'reviews', 'variants'])->latest()->get();
    $banners = \App\Models\CmsBanner::where('is_active', true)->get();
    $testimonials = collect();
    $about = \App\Models\Setting::getVal('web_about', 'Fadilah Digital Printing adalah penyedia jasa percetakan digital profesional.');
    $faq = json_decode(\App\Models\Setting::getVal('web_faq', '[]'), true);
    
    $seoTitle = \App\Models\Setting::getVal('seo_title', 'Fadilah Digital Printing - Jasa Cetak Spanduk, Brosur & Plakat');
    $seoDescription = \App\Models\Setting::getVal('seo_description', 'Pesan spanduk, x-banner, stempel otomatis secara online praktis dan cepat.');
    $seoKeywords = \App\Models\Setting::getVal('seo_keywords', 'percetakan digital, cetak spanduk online, stempel otomatis');
    
    return view('welcome', compact(
        'products', 'banners', 'testimonials', 'about', 'faq', 
        'seoTitle', 'seoDescription', 'seoKeywords'
    ));
});

Route::get('/bantuan', function () {
    return redirect('/#faq');
})->name('help');


// ==================================================================== //
// MIDTRANS CALLBACK — POST only, tanpa auth, tanpa CSRF                 //
// CSRF dikecualikan di bootstrap/app.php via validateCsrfTokens(except) //
// ==================================================================== //
Route::post('/payment/callback', [PaymentController::class, 'callback'])
    ->name('payment.callback');

Route::post('/feedbacks', [CmsController::class, 'storeFeedback'])
    ->name('feedbacks.store');

// ==================================================================== //
// GOOGLE OAUTH — Publik, tanpa auth                                     //
// ==================================================================== //
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');



// ==================================================================== //
// KELOMPOK ROUTE WAJIB LOGIN                                             //
// ==================================================================== //
Route::middleware('auth')->group(function () {

    // ---------------------------------------------------------------- //
    // DASHBOARD — Admin/Owner & Customer (via DashboardController)       //
    // ---------------------------------------------------------------- //
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    // ---------------------------------------------------------------- //
    // PROFIL — Bawaan Laravel Breeze                                     //
    // ---------------------------------------------------------------- //
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ---------------------------------------------------------------- //
    // ALAMAT PENGIRIMAN — Manajemen Alamat Customer                   //
    // ---------------------------------------------------------------- //
    Route::resource('addresses', AddressController::class);
    Route::post('addresses/{address}/default', [AddressController::class, 'makeDefault'])->name('addresses.default');
    
    // API Wilayah Indonesia Proxy
    Route::get('/api/address/provinces', [AddressController::class, 'getProvinces'])->name('api.address.provinces');
    Route::get('/api/address/regencies/{province_id}', [AddressController::class, 'getRegencies'])->name('api.address.regencies');
    Route::get('/api/address/districts/{regency_id}', [AddressController::class, 'getDistricts'])->name('api.address.districts');
    Route::get('/api/address/villages/{district_id}', [AddressController::class, 'getVillages'])->name('api.address.villages');

    // ---------------------------------------------------------------- //
    // KERANJANG BELANJA — Hanya untuk user yang login                    //
    // Authorization: CartController menggunakan where('user_id', Auth::id())
    //                dan manual check pada update/destroy/clear           //
    // ---------------------------------------------------------------- //
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    // ---------------------------------------------------------------- //
    // CHECKOUT — Konversi cart menjadi Order                             //
    // FIX [M1]: POST /checkout sekarang menuju CheckoutController@store  //
    //           (bukan OrderController@store seperti sebelumnya)          //
    // CheckoutRequest::authorize() memastikan hanya user login            //
    // ---------------------------------------------------------------- //
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/checkout/direct', [CheckoutController::class, 'directCheckout'])->name('checkout.direct');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/rates', [CheckoutController::class, 'getRates'])->name('checkout.rates');
    Route::post('/buy-now/{product}', [CheckoutController::class, 'buyNow'])->name('buy_now');

    // ---------------------------------------------------------------- //
    // PAYMENT — Halaman bayar dan konfirmasi sukses                      //
    // Authorization: PaymentController mengecek user->role + order->user_id //
    // Snap Token di-generate fresh, tidak disimpan ke DB                //
    // ---------------------------------------------------------------- //
    Route::get('/payment/{order}/pay', [PaymentController::class, 'pay'])->name('payment.pay');
    Route::get('/payment/{order}/success', [PaymentController::class, 'success'])->name('payment.success');

    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // ---------------------------------------------------------------- //
    // NOTIFIKASI CENTER                                                //
    // ---------------------------------------------------------------- //
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/navbar-list', [NotificationController::class, 'navbarList'])->name('notifications.navbar_list');
    Route::get('/notifikasi/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread_count');
    Route::get('/notifikasi/{id}/click', [NotificationController::class, 'click'])->name('notifications.click');
    Route::post('/notifikasi/read-all', [NotificationController::class, 'readAll'])->name('notifications.read_all');

    // ---------------------------------------------------------------- //
    // CHAT BANTUAN CRM (REBUILT TUNGGAL SHOPEE-STYLE)                  //
    // ---------------------------------------------------------------- //
    // Rute Global Badge Notifikasi
    Route::get('/chat/unread-badge', [ChatRoomController::class, 'unreadBadge'])->name('chat.unread_badge');

    // Rute Customer
    Route::get('/chat', [ChatRoomController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [ChatRoomController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/poll', [ChatRoomController::class, 'pollCustomer'])->name('chat.poll');

    // Rute Admin CS
    Route::get('/admin/chat', [ChatRoomController::class, 'adminIndex'])->name('admin.chat');
    Route::get('/admin/chat-rooms-poll', [ChatRoomController::class, 'adminPollRooms'])->name('chat.rooms.poll');
    Route::get('/admin/chat/room/{room}', [ChatRoomController::class, 'adminShow'])->name('admin.show');
    Route::post('/reply', [ChatRoomController::class, 'adminReply'])->name('admin.reply');
    Route::get('/admin/chat/{room}/poll', [ChatRoomController::class, 'pollAdmin'])->name('admin.poll');
    Route::post('/admin/chat/{room}/toggle-ai', [ChatRoomController::class, 'toggleAiMode'])->name('admin.chat.toggle_ai');

    // Rute Owner (Read-only monitoring & stats)
    Route::get('/owner/chat', [ChatRoomController::class, 'ownerIndex'])->name('owner.chat');

    // ---------------------------------------------------------------- //
    // CETAK NOTA / INVOICE — Bisa diakses pelanggan & admin             //
    // ---------------------------------------------------------------- //
    Route::get('/pesanan/{pesanan}/cetak', [OrderController::class, 'cetak'])->name('pesanan.cetak');
    Route::post('/pesanan/{pesanan}/terima', [OrderController::class, 'terima'])->name('pesanan.terima');
    Route::delete('/pesanan/{pesanan}', [OrderController::class, 'destroy'])->name('pesanan.destroy');
    Route::get('/pesanan/shipments/{shipment}/label', [\App\Http\Controllers\Admin\ShippingController::class, 'downloadLabelCustomer'])->name('pesanan.shipments.label');
    Route::get('/pesanan/{order}/track', [OrderController::class, 'trackCustomer'])->name('pesanan.track');
    Route::get('/pesanan/{order}/claim', [\App\Http\Controllers\OrderClaimController::class, 'create'])->name('pesanan.claim.create');
    Route::post('/pesanan/{order}/claim', [\App\Http\Controllers\OrderClaimController::class, 'store'])->name('pesanan.claim.store');

    // ---------------------------------------------------------------- //
    // ZONA KHUSUS MANAGEMENT — Hanya admin & owner                      //
    // ---------------------------------------------------------------- //
    Route::middleware('role:admin,owner')->group(function () {

        // CRUD Data Master
        Route::resource('kategori', CategoryController::class);
        Route::delete('/produk/gallery/{image}', [ProductController::class, 'destroyImage'])->name('produk.gallery.destroy');
        Route::resource('produk', ProductController::class)->except(['show']);

        Route::post('/pesanan/{pesanan}/trigger-print', [OrderController::class, 'triggerPrint'])->name('pesanan.trigger_print');
        Route::resource('pesanan', OrderController::class)->except(['store', 'create', 'destroy']);

        // Manajemen Stok
        Route::get('/manajemen-stok', [StockController::class, 'index'])->name('stok.index');
        Route::put('/manajemen-stok/{id}', [StockController::class, 'update'])->name('stok.update');

        // Laporan Penjualan
        Route::get('/laporan-penjualan', [OrderController::class, 'laporan'])->name('laporan.index');
        Route::get('/laporan-penjualan/export', [OrderController::class, 'laporanExport'])->name('laporan.export');
        Route::get('/laporan-penjualan/pdf', [OrderController::class, 'laporanPdf'])->name('laporan.pdf');

        // Riwayat Transaksi (dengan filter payment_status)
        Route::get('/riwayat-transaksi', [OrderController::class, 'riwayat'])->name('transaksi.riwayat');

        // =============================================================
        // KELOLA CMS, SETTINGS, & REVIEWS (ADMIN)
        // =============================================================
        // Settings Website
        Route::get('/settings', [CmsController::class, 'indexSettings'])->name('settings.index');
        Route::put('/settings', [CmsController::class, 'updateSettings'])->name('settings.update');
        Route::delete('/settings/logo', [CmsController::class, 'deleteLogo'])->name('settings.delete_logo');
        Route::post('/settings/backup', [CmsController::class, 'downloadBackup'])->name('settings.backup');
        Route::get('/activity-logs', [CmsController::class, 'indexLogs'])->name('logs.index');

        // Banner Slider
        Route::get('/banners', [CmsController::class, 'indexBanners'])->name('banners.index');
        Route::post('/banners', [CmsController::class, 'storeBanner'])->name('banners.store');
        Route::delete('/banners/{banner}', [CmsController::class, 'destroyBanner'])->name('banners.destroy');



        // Feedback / Kontak
        Route::get('/feedbacks', [CmsController::class, 'indexFeedbacks'])->name('feedbacks.index');
        Route::post('/feedbacks/{feedback}/read', [CmsController::class, 'readFeedback'])->name('feedbacks.read');
        Route::delete('/feedbacks/{feedback}', [CmsController::class, 'destroyFeedback'])->name('feedbacks.destroy');

        // Review Moderation
        Route::get('/reviews/admin', [ReviewController::class, 'indexAdmin'])->name('reviews.admin');
        Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');
        Route::post('/reviews/{review}/toggle-visibility', [ReviewController::class, 'toggleVisibility'])->name('reviews.toggle_visibility');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        // Manajemen Notifikasi Admin
        Route::get('/admin/notifications', [NotificationController::class, 'adminIndex'])->name('admin.notifications.index');
        Route::post('/admin/notifications/send', [NotificationController::class, 'adminSend'])->name('admin.notifications.send');
        Route::delete('/admin/notifications/{id}', [NotificationController::class, 'adminDestroy'])->name('admin.notifications.destroy');

        // Biteship Shipping Administration
        Route::get('/admin/shipping', [ShippingController::class, 'index'])->name('admin.shipping.index');
        Route::post('/admin/shipping/{order}/resi', [ShippingController::class, 'updateResi'])->name('admin.shipping.update_resi');
        Route::get('/admin/shipping/{order}/track', [ShippingController::class, 'track'])->name('admin.shipping.track');
        Route::post('/admin/shipping/{order}/refresh', [ShippingController::class, 'refreshStatus'])->name('admin.shipping.refresh');
        Route::get('/admin/shipping-settings', [ShippingController::class, 'settingsIndex'])->name('admin.shipping_settings.index');
        Route::post('/admin/shipping-settings', [ShippingController::class, 'settingsUpdate'])->name('admin.shipping_settings.update');
        Route::post('/admin/shipping-settings/test', [ShippingController::class, 'testConnection'])->name('admin.shipping_settings.test');

        // New Biteship Shipment Integration & Simulation routes
        Route::post('/admin/shipping/shipments/{order}', [ShippingController::class, 'createShipment'])->name('admin.shipping.shipments.create');
        Route::post('/admin/shipping/shipments/{shipment}/pickup', [ShippingController::class, 'simulatePickup'])->name('admin.shipping.shipments.pickup');
        Route::post('/admin/shipping/shipments/{shipment}/tracking', [ShippingController::class, 'updateTracking'])->name('admin.shipping.shipments.tracking');
        Route::get('/admin/shipping/shipments/{shipment}/label', [ShippingController::class, 'downloadLabel'])->name('admin.shipping.shipments.label');
        Route::post('/admin/shipping/shipments/{shipment}/kirim', [ShippingController::class, 'kirimPaket'])->name('admin.shipping.shipments.kirim');
        Route::post('/admin/shipping/shipments/{shipment}/selesaikan', [ShippingController::class, 'selesaikanPaket'])->name('admin.shipping.shipments.selesaikan');

        // Kelola Komplain / Garansi (Admin)
        Route::get('/admin/claims', [\App\Http\Controllers\OrderClaimController::class, 'adminIndex'])->name('admin.claims.index');
        Route::get('/admin/claims/{claim}', [\App\Http\Controllers\OrderClaimController::class, 'adminShow'])->name('admin.claims.show');
        Route::post('/admin/claims/{claim}/status', [\App\Http\Controllers\OrderClaimController::class, 'adminUpdateStatus'])->name('admin.claims.status');
    });
});

Route::get('/test-email', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('SMTP Test Body', function ($m) {
            $m->to(config('mail.from.address', 'email@gmail.com'))
              ->subject('SMTP test-email');
        });
        return 'SMTP SUCCESS';
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('[SMTP-Mail] test-email failed.', [
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'host'      => config('mail.mailers.smtp.host'),
            'port'      => config('mail.mailers.smtp.port'),
            'trace'     => $e->getTraceAsString(),
        ]);
        return 'Gagal mengirim email. Error: ' . $e->getMessage() . ' di file ' . $e->getFile() . ' baris ' . $e->getLine() . "\n\n" . $e->getTraceAsString();
    }
});

Route::get('/test-biteship', function () {
    $apiKey = config('biteship.api_key') ?? config('services.biteship.api_key') ?? '';
    $baseUrl = config('biteship.base_url') ?? config('services.biteship.base_url') ?? 'https://api.biteship.com';
    $originId = config('biteship.origin_id') ?? config('services.biteship.origin_id') ?? '';

    $maskedKey = empty($apiKey) ? 'NOT_SET' : substr($apiKey, 0, 6) . '...' . substr($apiKey, -6);

    $status = 'Unconnected';
    $httpStatus = null;
    $responseBody = null;

    if (!empty($apiKey)) {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $apiKey,
                'Accept' => 'application/json'
            ])->get("{$baseUrl}/v1/couriers");

            $httpStatus = $response->status();
            $responseBody = $response->json();

            if ($response->successful()) {
                $status = '✅ Connected to Biteship';
            } else {
                $status = '❌ Connection failed';
            }
        } catch (\Exception $e) {
            $status = '❌ Exception occurred';
            $responseBody = $e->getMessage();
        }
    } else {
        $status = '❌ API Key not set';
    }

    return response()->json([
        'status' => $status,
        'config' => [
            'api_key_masked' => $maskedKey,
            'base_url' => $baseUrl,
            'origin_id' => $originId,
        ],
        'http_status' => $httpStatus,
        'response' => $responseBody
    ]);
});

Route::get('/produk/{produk}', [ProductController::class, 'show'])
    ->name('produk.show');

require __DIR__ . '/auth.php';