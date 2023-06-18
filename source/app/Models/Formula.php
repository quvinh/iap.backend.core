<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Formula extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'note',
        'company_detail_id',
        'company_type_id',
        'status'
    ];

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
}
