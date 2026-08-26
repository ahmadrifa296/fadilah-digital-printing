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
        // 1. Create user_addresses table
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('label'); // e.g. Rumah, Kantor
            $table->string('receiver_name');
            $table->string('phone');
            $table->string('province');
            $table->string('city');
            $table->string('district');
            $table->string('subdistrict');
            $table->string('postal_code');
            $table->string('rt');
            $table->string('rw');
            $table->string('no_rumah');
            $table->string('patokan')->nullable();
            $table->text('full_address');
            $table->string('notes')->nullable(); // Catatan Kurir
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 2. Add address snapshot columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->string('receiver_name')->nullable()->after('notes');
            $table->string('phone')->nullable()->after('receiver_name');
            $table->string('province')->nullable()->after('phone');
            $table->string('city')->nullable()->after('province');
            $table->string('district')->nullable()->after('city');
            $table->string('subdistrict')->nullable()->after('district');
            $table->string('postal_code')->nullable()->after('subdistrict');
            $table->string('rt')->nullable()->after('postal_code');
            $table->string('rw')->nullable()->after('rt');
            $table->string('no_rumah')->nullable()->after('rw');
            $table->string('patokan')->nullable()->after('no_rumah');
            $table->text('full_address')->nullable()->after('patokan');
            $table->string('address_label')->nullable()->after('full_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'receiver_name',
                'phone',
                'province',
                'city',
                'district',
                'subdistrict',
                'postal_code',
                'rt',
                'rw',
                'no_rumah',
                'patokan',
                'full_address',
                'address_label'
            ]);
        });

        Schema::dropIfExists('user_addresses');
    }
};
