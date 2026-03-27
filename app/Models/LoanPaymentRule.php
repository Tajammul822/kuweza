<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPaymentRule extends Model
{
    protected $table = 'loan_payment_rules';

    protected $fillable = [
        'min_amount',
        'max_amount',
        'duration_days',
        'installment_type',
        'penalty_type',
        'penalty_value',
        'grace_period_days',
    ];
}
