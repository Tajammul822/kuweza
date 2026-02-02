<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmProfile extends Model
{
    protected $table = 'farm_profiles';

    protected $fillable = [
        'user_id',
        'farm_name',
        'qr_code_string'
    ];
}
