<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property float $sum_from
 * @property float $sum_to
 * @property float $sum_avg
 */
class Formula extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'note',
        'company_detail_id',
        'company_type_id',
        'sum_from',
        'sum_to',
        'sum_avg',
        'status'
    ];

    public function setFormula(float $sum_from, float $sum_to): void
    {
        $this->sum_from = $sum_from;
        $this->sum_to = $sum_to;
        $this->sum_avg = $this->getAverage();
    }

    public function getAverage(): float
    {
        try {
            $avg = ($this->sum_from + $this->sum_to) / 2;
            return round($avg, 2);
        } catch (\Exception) {
            return 0;
        }
    }

    /**
     * @return HasOne
     */
    public function company_detail(): HasOne
    {
        return $this->hasOne(CompanyDetail::class, 'id', 'company_detail_id');
    }

    /**
     * @return HasOne
     */
    public function company_type(): HasOne
    {
        return $this->hasOne(CompanyType::class, 'id', 'company_type_id');
    }

    /**
     * @return HasMany
     */
    public function category_purchases(): HasMany
    {
        return $this->hasMany(FormulaCategoryPurchase::class, 'formula_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function category_solds(): HasMany
    {
        return $this->hasMany(FormulaCategorySold::class, 'formula_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function commodities(): HasMany
    {
        return $this->hasMany(FormulaCommodity::class, 'formula_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function materials(): HasMany
    {
        return $this->hasMany(FormulaMaterial::class, 'formula_id', 'id');
    }
}
