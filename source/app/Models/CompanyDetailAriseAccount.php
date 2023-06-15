<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDetailAriseAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_detail_id',
        'arise_account_id',
        'value_from',
        'value_to',
        'value_avg',
        'visible_value'
    ];

    public $timestamps = false;
}
