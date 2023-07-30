<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategorySold extends BaseModel
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'tag',
        'note',
        'status',
        'created_by',
        'updated_by'
    ];
}
