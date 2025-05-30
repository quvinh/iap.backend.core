<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryPurchase extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'tag',
        'note',
        'status',
        'method',
        'created_by',
        'updated_by'
    ];
}
