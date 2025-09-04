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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');                           // Region name
            $table->string('code')->unique();                 // Unique region code
            $table->text('address')->nullable();              // Address
            $table->string('city')->nullable();               // City
            $table->string('province')->nullable();           // Province
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Relasi ke users
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
