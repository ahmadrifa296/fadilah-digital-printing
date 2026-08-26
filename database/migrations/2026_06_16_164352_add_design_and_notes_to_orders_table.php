<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Menambahkan kolom file desain dan catatan tepat setelah total_price
            $table->string('design_file')->nullable()->after('total_price');
            $table->text('notes')->nullable()->after('design_file');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['design_file', 'notes']);
        });
    }
};