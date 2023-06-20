<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxFreeVoucher extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'number_account',
        'note',
        'status'
    ];
}
