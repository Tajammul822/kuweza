<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = [
        'transaction_id', 'installment_id', 'user_id', 
        'payment_type', 'amount', 'gateway_reference', 'gateway_name'
    ];
}
