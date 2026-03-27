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
        Schema::create('loan_payment_rules', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_amount', 15, 2);
            $table->decimal('max_amount', 15, 2);
            $table->integer('duration_days');
            $table->enum('installment_type', ['SINGLE', 'WEEKLY', 'MONTHLY'])->default('SINGLE');
            $table->enum('penalty_type', ['FIXED', 'PERCENTAGE'])->default('FIXED');
            $table->decimal('penalty_value', 15, 2);
            $table->integer('grace_period_days')->default(0);       
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payment_rules');
    }
};
