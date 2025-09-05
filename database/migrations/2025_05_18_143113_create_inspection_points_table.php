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
       Schema::create('inspection_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained('components');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->integer('order');
            $table->boolean('is_active')->default(true);
            $table->string('file_path')->nullable(); // lokasi file gambar
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_points');
    }
};
