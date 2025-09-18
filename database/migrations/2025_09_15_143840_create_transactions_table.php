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
        Schema::create('transactions', function (Blueprint $table) {
           $table->id();
            
            // Foreign key ke inspections table
            $table->foreignId('inspection_id')->constrained()->onDelete('cascade');
            
            // Informasi pembayaran
            $table->decimal('amount', 12, 0)->nullable(); // Nominal pembayaran
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'expired'])->default('pending');
            $table->enum('payment_method', ['transfer', 'cash', 'credit_card', 'debit_card', 'qris'])->nullable();
            
            // Informasi waktu pembayaran
            $table->dateTime('payment_date')->nullable();
            $table->dateTime('due_date')->nullable();
            
            // Informasi referensi
            $table->string('transaction_reference')->nullable()->unique(); // No referensi dari payment gateway
            $table->string('invoice_number')->unique(); // No invoice internal
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Informasi tambahan
            $table->text('notes')->nullable();
            $table->text('payment_proof')->nullable(); // Path untuk bukti pembayaran
            $table->json('metadata')->nullable(); // Data tambahan dalam format JSON
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index untuk performa query
            $table->index('status');
            $table->index('payment_method');
            $table->index('invoice_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
