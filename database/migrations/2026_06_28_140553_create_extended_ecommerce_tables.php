<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 2. Tabel CMS Banners / Sliders
        Schema::create('cms_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image');
            $table->string('link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Tabel CMS Testimonials
        Schema::create('cms_testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->integer('rating')->default(5);
            $table->text('comment');
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Tabel CMS Feedbacks (Kontak)
        Schema::create('cms_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });



        // 6. Tabel Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('activity');
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        // 7. Tabel Stock Logs (Mutasi Stok)
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('type', ['in', 'out']);
            $table->integer('quantity');
            $table->string('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 8. Tambahan Kolom ke Tabel Products
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('price');
            $table->integer('discount_percent')->default(0)->after('sku');
            $table->integer('discount_flat')->default(0)->after('discount_percent');
            $table->string('meta_title')->nullable()->after('description');
            $table->text('meta_description')->nullable()->after('meta_title');
        });

        // 9. Tabel Product Images (Multi Gambar)
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('image_path');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // 10. Tabel Product Variants (Pilihan Ukuran, Bahan, Finishing)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('variant_type'); // 'ukuran', 'bahan', 'finishing'
            $table->string('variant_name'); // 'A3', 'Art Paper', 'Laminating Glos'
            $table->integer('price_modifier')->default(0); // harga tambahan
            $table->integer('stock')->default(0);
            $table->timestamps();
        });

        // 11. Tambahan Kolom ke Tabel Orders & Order Details
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_status')->default('pending')->after('order_status'); // pending -> antrian -> diproduksi -> siap_diambil -> dikirim -> selesai -> dibatalkan
            $table->string('tracking_resi')->nullable()->after('tracking_status');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->string('ukuran')->nullable()->after('qty');
            $table->string('bahan')->nullable()->after('ukuran');
            $table->string('finishing')->nullable()->after('bahan');
            $table->string('estimasi_pengerjaan')->nullable()->after('finishing');
        });

        // 12. Tabel Reviews (Rating & Ulasan)
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating'); // 1 sampai 5
            $table->text('comment');
            $table->string('photo')->nullable(); // Foto hasil cetak opsional
            $table->text('reply')->nullable(); // Balasan dari admin
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['ukuran', 'bahan', 'finishing', 'estimasi_pengerjaan']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_status', 'tracking_resi']);
        });

        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sku', 'discount_percent', 'discount_flat', 'meta_title', 'meta_description']);
        });

        Schema::dropIfExists('stock_logs');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('cms_feedbacks');
        Schema::dropIfExists('cms_testimonials');
        Schema::dropIfExists('cms_banners');
        Schema::dropIfExists('settings');
    }
};
