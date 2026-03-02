<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    protected $table = 'vendor_profiles';

    protected $fillable = [
        'user_id',
        'shop_name',
        'market_location',
        'payment_provider'
    ];
}
