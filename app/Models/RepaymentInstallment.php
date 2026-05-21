<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepaymentInstallment extends Model
{
    protected $fillable = [
        'transaction_id', 'installment_number', 'due_date',
        'base_amount', 'penalty_amount', 'amount_paid', 'status',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
