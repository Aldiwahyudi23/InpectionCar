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
        Schema::create('image_cars', function (Blueprint $table) {
            $table->id();
             // Relasi ke tabel car_details
            $table->foreignId('car_id')->constrained('car_details');
            $table->string('name')->nullable(); // nama foto (misal: depan, samping, belakang)
            $table->string('file_path'); // lokasi file gambar
            $table->text('note')->nullable(); // catatan tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_cars');
    }
};
