<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property float $value_from
 * @property float $value_to
 * @property float $value_avg
 */
class CompanyDetailAriseAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_detail_id',
        'arise_account_id',
        'value_from',
        'value_to',
        'value_avg',
        // 'visible_value'
    ];

    public $timestamps = false;

    public function setCompanyDetailAriseAccount(float $value_from, float $value_to): void
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
    public function arise_account(): HasOne
    {
        return $this->hasOne(FirstAriseAccount::class, 'id', 'arise_account_id');
    }
}
