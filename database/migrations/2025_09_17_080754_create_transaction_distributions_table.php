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
        Schema::create('transaction_distributions', function (Blueprint $table) {
            $table->id();
             $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('region_id')->nullable()->constrained()->onDelete('set null');
            $table->string('role_type'); // inspector, coordinator, owner
            $table->decimal('amount', 12, 0);
            $table->decimal('percentage', 5, 2);
            $table->text('calculation_note')->nullable();
              $table->boolean('is_released')->default(false); // apakah fee sudah dikirim ke user
            $table->timestamp('released_at')->nullable();   // kapan fee dikirim
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete(); // siapa yang kirim
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_distributions');
    }
};
