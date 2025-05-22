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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
             $table->foreignId('user_id')->constrained('users'); // FK ke tabel users
            $table->foreignId('car_id')->nullable()->constrained('car_details'); // FK ke tabel cars, nullable jika tidak selalu ada
            $table->dateTime('inspection_date');
            $table->string('status'); // Contoh: selesai, draft, dll.  Pertimbangkan untuk membuat enum jika status terbatas.
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Untuk soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
