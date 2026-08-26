<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * Menambahkan kolom-kolom baru ke tabel 'orders' untuk mendukung:
     *   1. Nomor invoice otomatis format FDP-YYYYMMDD-XXXXXX
     *   2. Status pesanan terpisah dari status pembayaran (order_status vs payment_status)
     *   3. Integrasi Midtrans: payment_type, midtrans_transaction_id, paid_at
     *
     * PENTING: Migration ini TIDAK menghapus kolom 'status' lama agar aman
     * untuk database yang sudah berisi data. Kolom lama tetap ada hingga
     * Controller & View sudah diperbarui (Tahap 7 & 10).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            // 1. Nomor Invoice Unik
            //    Format: FDP-YYYYMMDD-XXXXXX (misal: FDP-20260628-000001)
            //    Nullable karena order lama tidak punya nomor invoice.
            $table->string('invoice_number')->nullable()->unique()->after('id');

            // 2. Status Pesanan (VARCHAR, menggantikan kolom 'status' ENUM lama)
            //    Nilai: pending | diproses | selesai | dibatalkan
            //    Dibuat nullable dulu agar aman saat migration,
            //    lalu diisi dari data kolom 'status' lama melalui DB statement di bawah.
            $table->string('order_status', 50)->nullable()->after('invoice_number');

            // 3. Status Pembayaran (Terpisah dari status pesanan)
            //    Nilai mengikuti respons Midtrans:
            //    pending | settlement | capture | expire | cancel | deny | refund
            $table->string('payment_status', 50)->default('pending')->after('order_status');

            // 4. Metode/Tipe Pembayaran dari Midtrans
            //    Sesuai field 'payment_type' pada response Midtrans API:
            //    gopay | bank_transfer | credit_card | qris | dll.
            $table->string('payment_type', 50)->nullable()->after('payment_status');

            // 5. ID Transaksi dari Midtrans
            //    Disimpan untuk keperluan rekonsiliasi & verifikasi.
            $table->string('midtrans_transaction_id')->nullable()->after('payment_type');

            // 6. Waktu Pembayaran Berhasil
            //    Diisi saat Midtrans mengirim callback dengan status settlement/capture.
            $table->timestamp('paid_at')->nullable()->after('midtrans_transaction_id');
        });

        // ------------------------------------------------------------------ //
        // Data Migration: Salin & Petakan nilai kolom 'status' ke 'order_status'
        // dan 'payment_status' yang baru.
        //
        // Mapping:
        //   'pending'   → order_status='pending',    payment_status='pending'
        //   'paid'      → order_status='diproses',   payment_status='settlement'
        //   'cancelled' → order_status='dibatalkan', payment_status='cancel'
        //
        // Menggunakan raw SQL agar efisien untuk tabel besar dan aman
        // tanpa memuat semua record ke memori PHP.
        // ------------------------------------------------------------------ //

        // Status: pending
        DB::statement("
            UPDATE orders
            SET order_status = 'pending',
                payment_status = 'pending'
            WHERE status = 'pending'
        ");

        // Status: paid → pesanan sudah dibayar, masuk ke antrian proses
        DB::statement("
            UPDATE orders
            SET order_status = 'diproses',
                payment_status = 'settlement'
            WHERE status = 'paid'
        ");

        // Status: cancelled → pesanan dibatalkan
        DB::statement("
            UPDATE orders
            SET order_status = 'dibatalkan',
                payment_status = 'cancel'
            WHERE status = 'cancelled'
        ");

        // Fallback: isi order_status 'pending' untuk row yang belum ter-mapping
        // (misalnya ada nilai tak terduga di kolom 'status' lama)
        DB::statement("
            UPDATE orders
            SET order_status = 'pending',
                payment_status = 'pending'
            WHERE order_status IS NULL
        ");

        // Setelah data dipindahkan, ubah order_status menjadi NOT NULL dengan default
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_status', 50)->default('pending')->nullable(false)->change();
        });
    }

    /**
     * Rollback migration.
     *
     * Menghapus semua kolom yang ditambahkan pada method up().
     * Kolom 'status' lama tidak tersentuh sehingga rollback aman.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Hapus index unique pada invoice_number sebelum drop kolom
            $table->dropUnique(['invoice_number']);

            $table->dropColumn([
                'invoice_number',
                'order_status',
                'payment_status',
                'payment_type',
                'midtrans_transaction_id',
                'paid_at',
            ]);
        });
    }
};
