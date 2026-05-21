<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = [
        'transaction_id', 'installment_id', 'user_id',
        'payment_type', 'amount', 'gateway_reference', 'gateway_name',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function installment()
    {
        return $this->belongsTo(RepaymentInstallment::class, 'installment_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
