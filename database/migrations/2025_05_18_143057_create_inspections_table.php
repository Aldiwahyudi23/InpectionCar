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
    $table->foreignId('user_id')->constrained('users');
    $table->foreignId('category_id')->constrained('categories');
    $table->foreignId('car_id')->nullable()->constrained('car_details');
    $table->string('plate_number')->nullable();
    $table->string('car_name')->nullable();
    $table->string('color')->nullable();
    $table->string('noka')->nullable();
    $table->string('nosin')->nullable();
    $table->dateTime('inspection_date');
    
    // Perbaikan bagian status:
    $table->enum('status', [
        'draft',
        'in_progress',
        'pending_review',
        'approved', 
        'rejected',
        'revision_required',
        'completed',
        'cancelled'
    ])->default('draft'); // Hapus ->change() karena ini bukan alter table
    
    $table->json('settings')->nullable(); // Kolom untuk menyimpan konfigurasi dinamis
    $table->text('notes')->nullable();
    $table->text('file')->nullable();
    $table->timestamps();
    $table->softDeletes();
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
