<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCode extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'product_code',
        'product_exchange',
        'product',
        'price',
        'quantity',
        'begining_total_value',
        'unit',
        'year',
        'status',
    ];
}
