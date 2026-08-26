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
        // 1. Add weight and dimensions to products table
        Schema::table('products', function (Blueprint $table) {
            $table->integer('weight')->default(500)->after('description'); // weight in grams
            $table->integer('length')->default(10)->after('weight'); // in cm
            $table->integer('width')->default(10)->after('length'); // in cm
            $table->integer('height')->default(10)->after('width'); // in cm
        });

        // 2. Add Biteship shipping columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('shipping_cost')->default(0)->after('address_label');
            $table->string('shipping_courier')->nullable()->after('shipping_cost');
            $table->string('shipping_service')->nullable()->after('shipping_courier');
            $table->string('shipping_estimation')->nullable()->after('shipping_service');
            $table->string('shipping_status')->nullable()->after('shipping_estimation');
            $table->string('tracking_number')->nullable()->after('shipping_status');
            $table->string('tracking_url')->nullable()->after('tracking_number');
            $table->text('shipping_snapshot')->nullable()->after('tracking_url');
            $table->string('biteship_order_id')->nullable()->after('shipping_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight', 'length', 'width', 'height']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_cost',
                'shipping_courier',
                'shipping_service',
                'shipping_estimation',
                'shipping_status',
                'tracking_number',
                'tracking_url',
                'shipping_snapshot',
                'biteship_order_id'
            ]);
        });
    }
};
