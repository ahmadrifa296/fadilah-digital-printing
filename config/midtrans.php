<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Midtrans Payment Gateway
|--------------------------------------------------------------------------
|
| Konfigurasi ini dibaca dari file .env sehingga credential tidak perlu
| di-hardcode dan aman untuk di-commit ke version control.
|
| Untuk Sandbox: MIDTRANS_IS_PRODUCTION=false
| Untuk Production: MIDTRANS_IS_PRODUCTION=true
|
| Server Key dan Client Key dapat ditemukan di:
| https://dashboard.sandbox.midtrans.com (Sandbox)
| https://dashboard.midtrans.com (Production)
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Server Key
    |----------------------------------------------------------------------
    | Digunakan untuk komunikasi server-to-server (generate token, callback).
    | JANGAN pernah expose ke client/frontend.
    */
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    /*
    |----------------------------------------------------------------------
    | Client Key
    |----------------------------------------------------------------------
    | Digunakan di frontend JavaScript untuk inisialisasi Snap.
    | Aman untuk ditampilkan di browser.
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    /*
    |----------------------------------------------------------------------
    | Mode Produksi
    |----------------------------------------------------------------------
    | false = Sandbox (untuk development & testing)
    | true  = Production (untuk live transaction)
    */
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |----------------------------------------------------------------------
    | Merchant ID
    |----------------------------------------------------------------------
    | Diperlukan untuk beberapa fitur Midtrans seperti recurring payment.
    */
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),

    /*
    |----------------------------------------------------------------------
    | Snap JS URL
    |----------------------------------------------------------------------
    | URL script Snap.js yang berbeda antara Sandbox dan Production.
    | Dihitung otomatis berdasarkan nilai is_production.
    */
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    /*
    |----------------------------------------------------------------------
    | Sanitasi Input
    |----------------------------------------------------------------------
    | Midtrans akan membersihkan karakter khusus di payload untuk mencegah XSS.
    */
    'is_sanitized' => true,

    /*
    |----------------------------------------------------------------------
    | 3D Secure
    |----------------------------------------------------------------------
    | Wajib aktif untuk transaksi kartu kredit.
    */
    'is_3ds' => true,

];
