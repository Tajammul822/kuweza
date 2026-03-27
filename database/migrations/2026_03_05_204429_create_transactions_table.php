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
            $table->string('transaction_code')->unique();
            $table->foreignId('vendor_id')->constrained('vendor_profiles')->onDelete('cascade');
            $table->foreignId('farmer_id')->constrained('farm_profiles')->onDelete('cascade');
            $table->foreignId('rule_id')->nullable()->constrained('loan_payment_rules');
            $table->decimal('total_amount', 15, 2);
            $table->enum('currency', ['CDF', 'USD'])->default('USD');
            $table->enum('status', ['PENDING', 'APPROVED', 'REPAID', 'DEFAULTED', 'REJECTED'])->default('PENDING');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
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
