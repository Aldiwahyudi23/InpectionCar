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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "Dokumen", "Interior", "Eksterior"
            $table->json('settings')->nullable(); // Kolom untuk menyimpan konfigurasi dinamis
            $table->integer('order'); // Urutan tampil di form
            $table->boolean('is_active')->default(true); // Aktif/non-aktif
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
