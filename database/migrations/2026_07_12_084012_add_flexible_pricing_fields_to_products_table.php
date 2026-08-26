<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('calculation_type')->default('fixed')->after('product_type');
            $table->decimal('base_price', 15, 2)->default(0)->after('price');
            $table->decimal('price_per_square_meter', 15, 2)->default(0)->after('price_per_m2');
            $table->integer('minimum_order')->default(1)->after('min_purchase');
            $table->integer('maximum_order')->default(99999)->after('minimum_order');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->decimal('custom_area', 8, 2)->nullable()->after('custom_width');
            $table->decimal('price_per_m2', 15, 2)->nullable()->after('custom_area');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('custom_area', 8, 2)->nullable()->after('custom_width');
            $table->decimal('price_per_m2', 15, 2)->nullable()->after('custom_area');
        });

        // Migrate existing product data
        \Illuminate\Support\Facades\DB::table('products')->get()->each(function ($p) {
            $calculationType = $p->is_custom_size ? 'custom_size' : 'fixed';
            \Illuminate\Support\Facades\DB::table('products')->where('id', $p->id)->update([
                'calculation_type' => $calculationType,
                'base_price' => $p->price ?: 0,
                'price_per_square_meter' => $p->price_per_m2 ?: 0,
                'minimum_order' => $p->is_custom_size ? ($p->min_purchase ?: 1) : 1,
                'maximum_order' => 99999,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'calculation_type',
                'base_price',
                'price_per_square_meter',
                'minimum_order',
                'maximum_order'
            ]);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['custom_area', 'price_per_m2']);
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['custom_area', 'price_per_m2']);
        });
    }
};
