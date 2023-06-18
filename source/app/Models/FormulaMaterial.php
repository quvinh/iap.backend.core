<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormulaMaterial extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'formula_id',
        'value_from',
        'value_to',
        'value_ave',
        'note',
        'status'
    ];
}
