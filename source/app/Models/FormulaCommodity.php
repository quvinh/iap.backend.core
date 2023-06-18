<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormulaCommodity extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'formula_id',
        'note',
        'status'
    ];
}
