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
        Schema::table('carts', function (Blueprint $table) {
            $table->string('ukuran')->nullable()->after('notes');
            $table->string('bahan')->nullable()->after('ukuran');
            $table->string('finishing')->nullable()->after('bahan');
            $table->string('custom_text')->nullable()->after('finishing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['ukuran', 'bahan', 'finishing', 'custom_text']);
        });
    }
};
