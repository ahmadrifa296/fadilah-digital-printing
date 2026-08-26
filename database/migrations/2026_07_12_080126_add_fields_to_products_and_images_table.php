<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_custom_size')->default(false)->after('product_type');
            $table->decimal('price_per_m2', 15, 2)->default(0)->after('price');
            $table->decimal('min_width', 8, 2)->default(0.1)->after('width');
            $table->decimal('max_width', 8, 2)->default(100.0)->after('min_width');
            $table->decimal('min_length', 8, 2)->default(0.1)->after('length');
            $table->decimal('max_length', 8, 2)->default(100.0)->after('min_length');
            $table->integer('min_purchase')->default(1)->after('stock');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('is_primary');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->decimal('custom_length', 8, 2)->nullable()->after('ukuran');
            $table->decimal('custom_width', 8, 2)->nullable()->after('custom_length');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('custom_length', 8, 2)->nullable()->after('ukuran');
            $table->decimal('custom_width', 8, 2)->nullable()->after('custom_length');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_custom_size',
                'price_per_m2',
                'min_width',
                'max_width',
                'min_length',
                'max_length',
                'min_purchase'
            ]);
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['custom_length', 'custom_width']);
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['custom_length', 'custom_width']);
        });
    }
};
