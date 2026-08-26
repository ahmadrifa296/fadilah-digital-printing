<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('cms_testimonials');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('cms_testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->integer('rating');
            $table->text('comment');
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
};
