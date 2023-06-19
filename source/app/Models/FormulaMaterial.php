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
        'value_avg',
        'note',
        'status'
    ];

    public function setFormulaMaterial(float $value_from, float $value_to): void
    {
        $this->value_from = $value_from;
        $this->value_to = $value_to;
        $this->value_avg = $this->getAverage();
    }

    public function getAverage(): float
    {
        try {
            $avg = ($this->value_from + $this->value_to) / 2;
            return round($avg, 2);
        } catch (\Exception) {
            return 0;
        }
    }
}
