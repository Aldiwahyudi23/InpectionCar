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
        Schema::create('menu_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_menu_id')->constrained('app_menus');
            $table->foreignId('inspection_point_id')->constrained('inspection_points');
            $table->string('input_type')->default('text');
            $table->json('settings')->nullable(); // Kolom untuk menyimpan konfigurasi dinamis
            $table->integer('order');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_points');
    }
};
