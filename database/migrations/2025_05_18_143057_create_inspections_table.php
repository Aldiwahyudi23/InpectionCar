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
        $table->foreignId('submitted_by')->nullable()->constrained('users');
        $table->timestamp('submitted_at')->nullable();
        $table->foreignId('user_id')->nullable()->constrained('users');
        $table->foreignId('customer_id')->nullable()->constrained('customers');
        $table->foreignId('category_id')->constrained('categories');
        $table->foreignId('car_id')->nullable()->constrained('car_details');
        $table->string('car_name')->nullable();
        $table->string('plate_number')->nullable();
        $table->string('km')->nullable();
        $table->string('color')->nullable();
        $table->string('noka')->nullable();
        $table->string('nosin')->nullable();
        $table->dateTime('inspection_date');
        
        // Perbaikan bagian status:
        $table->enum('status', [
            'draft',
            'in_progress',
            'pending',
            'pending_review',
            'approved', 
            'rejected',
            'revision',
            'completed',
            'cancelled'
        ])->default('draft'); // Hapus ->change() karena ini bukan alter table
        
        $table->json('settings')->nullable(); // Kolom untuk menyimpan konfigurasi dinamis
        $table->text('notes')->nullable();
        $table->text('file')->nullable();
        $table->string('code')->nullable();
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
