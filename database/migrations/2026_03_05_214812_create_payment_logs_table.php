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
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('installment_id')->nullable()->constrained('repayment_installments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // Jisne action kiya
            $table->enum('payment_type', ['DISBURSEMENT_TO_FARMER', 'REPAYMENT_FROM_VENDOR']);
            $table->decimal('amount', 15, 2);

            // Payment Gateway Details
            $table->string('gateway_reference')->nullable(); // Transaction ID from M-Pesa/Orange
            $table->string('gateway_name')->nullable(); // e.g., M-PESA, ORANGE_MONEY

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
