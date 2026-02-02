<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    protected $table = 'users_meta';

    protected $fillable = [
        'user_id',
        'id_number',
        'id_image',
        'street',
        'village',
        'region',
        'bank_name',
        'account_number'
    ];
}
