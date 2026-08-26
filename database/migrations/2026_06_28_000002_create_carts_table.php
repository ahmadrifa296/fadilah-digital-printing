<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * Membuat tabel 'carts' untuk fitur Shopping Cart.
     *
     * Desain tabel ini menggunakan pendekatan "price snapshot" — harga produk
     * disimpan pada saat item ditambahkan ke keranjang. Tujuannya agar
     * perubahan harga produk oleh admin tidak memengaruhi nilai cart yang
     * sudah dibuat oleh pelanggan.
     *
     * Catatan: Tabel ini bersifat sementara (session cart). Saat checkout,
     * data dipindahkan ke tabel 'orders' dan 'order_details', lalu cart dikosongkan.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // Relasi ke user yang memiliki cart ini
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // Cart otomatis terhapus jika user dihapus

            // Relasi ke produk yang dimasukkan ke cart
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade'); // Cart item otomatis terhapus jika produk dihapus

            // Jumlah item yang dipesan
            $table->unsignedInteger('qty')->default(1);

            // Harga satuan saat item ditambahkan ke cart (price snapshot)
            // Menggunakan unsignedBigInteger agar sesuai dengan tipe harga produk
            $table->unsignedBigInteger('price');

            // Subtotal = qty × price, dihitung dan disimpan saat add/update
            // Disimpan agar tidak perlu kalkulasi ulang setiap kali query
            $table->unsignedBigInteger('subtotal');

            // Catatan khusus pelanggan untuk item ini
            // (misal: ukuran spanduk, warna dominan, dsb.)
            $table->text('notes')->nullable();

            // File desain yang diunggah pelanggan per item
            // (misal: file JPG/PDF untuk cetak spanduk, stempel, dll.)
            $table->string('design_file')->nullable();

            $table->timestamps();

            // Index untuk mempercepat query cart per user
            $table->index('user_id');

            // Mencegah duplikasi: satu user hanya bisa punya satu baris
            // per produk di cart. Jika sudah ada, controller akan update qty.
            $table->unique(['user_id', 'product_id']);
        });
    }

    /**
     * Rollback migration.
     *
     * Menghapus tabel 'carts' sepenuhnya. Aman karena tabel ini
     * tidak memiliki dependensi dari tabel lain.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
