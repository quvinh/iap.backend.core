<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'template_id',
        'parent_id',
        'name',
        'date',
        'date_started',
        'date_ended',
        'number_of_months',
        'status',
        'meta',
    ];
}
