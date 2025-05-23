<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FirstAriseAccount extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'number_account',
        'number_percent',
        'method',
        'note',
        'status',
        'is_tracking',
    ];
}
