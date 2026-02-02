<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'user_id',
        'farm_id',
        'title',
        'featured_image',
        'description',
        'unit_price',
        'currency',
        'stock_quantity',
        'is_available'
    ];
}
