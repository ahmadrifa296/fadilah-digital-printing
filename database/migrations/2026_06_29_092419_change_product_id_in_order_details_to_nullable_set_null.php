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
        Schema::table('order_details', function (Blueprint $table) {
            // Drop foreign key lama
            $table->dropForeign(['product_id']);
            
            // Ubah kolom product_id menjadi nullable
            $table->foreignId('product_id')->nullable()->change();
            
            // Tambahkan foreign key baru dengan onDelete('set null')
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            
            // Kembalikan ke NOT NULL (pastikan tidak ada record yang null sebelum rollback ini)
            $table->foreignId('product_id')->nullable(false)->change();
            
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
        });
    }
};
