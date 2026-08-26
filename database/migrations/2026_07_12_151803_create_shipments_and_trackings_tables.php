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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('shipment_id')->nullable(); // Biteship shipment id
            $table->string('courier');
            $table->string('service');
            $table->string('tracking_number')->nullable();
            $table->string('status');
            $table->string('estimated_days')->nullable();
            $table->text('label_url')->nullable();
            $table->string('pickup_status')->default('waiting_pickup');
            $table->longText('raw_response')->nullable();
            $table->timestamps();
        });

        Schema::create('shipment_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('status');
            $table->text('description');
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_trackings');
        Schema::dropIfExists('shipments');
    }
};
