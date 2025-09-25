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
        Schema::create('car_details', function (Blueprint $table) {
            $table->id();
             $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->foreignId('car_model_id')->constrained('car_models')->onDelete('cascade');
            $table->foreignId('car_type_id')->constrained('car_types')->onDelete('cascade');
            $table->year('year');
            $table->integer('cc')->nullable();
            $table->enum('transmission', ['AT', 'MT', 'CVT', 'e-CVT', 'DCT', 'AMT', 'IVT','SSG','AGS','DHT']);
            $table->string('fuel_type');
            $table->string('production_period');
            $table->string('engine_code')->nullable();
            $table->string('segment')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Menambahkan kolom deleted_at untuk soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_details');
    }
};
