<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Common\MetaInfo as CommonMetaInfo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FormulaCategorySold extends Model
{
    use HasFactory;

    protected $fillable = [
        'formula_id',
        'category_sold_id',
        'value_from',
        'value_to',
        'value_avg',
        'visible_value',
    ];

    public $timestamps = false;

    public function setFormulaCategorySold(float $value_from, float $value_to): void
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

    /**
     * @return HasOne
     */
    public function category_sold(): HasOne
    {
        return $this->hasOne(CategorySold::class, 'id', 'category_sold_id');
    }

    /**
     * Meta info
     */
    public function setMetaInfo(CommonMetaInfo $meta = null, bool $isCreate = true): void
    {
        if (is_null($meta))
            $meta = new CommonMetaInfo('');
    }
}
