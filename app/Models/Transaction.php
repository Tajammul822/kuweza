<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $table = 'transactions';

    protected $fillable = [
        'transaction_code',
        'vendor_id',
        'farmer_id',
        'rule_id',
        'total_amount',
        'currency',
        'status',
        'admin_notes'
    ];

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_id');
    }

    
    public function farmerProfile()
    {
        return $this->belongsTo(FarmProfile::class, 'farmer_id');
    }

    public function rule()
    {
        return $this->belongsTo(LoanPaymentRule::class, 'rule_id');
    }

    public function installments()
    {
        return $this->hasMany(RepaymentInstallment::class);
    }

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class);
    }
}
