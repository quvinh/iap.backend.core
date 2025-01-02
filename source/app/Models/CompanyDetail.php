<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Helpers\Common\MetaInfo as CommonMetaInfo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'company_type_id',
        'description',
        'year'
    ];

    public $timestamps = false;

    /**
     * @return HasOne
     */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    /**
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(CompanyType::class, 'id', 'company_type_id');
    }

    /**
     * @return HasMany
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(CompanyDetailAriseAccount::class, 'company_detail_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function tax_free_vouchers(): HasMany
    {
        return $this->hasMany(CompanyDetailTaxFreeVoucher::class, 'company_detail_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function formulars(): HasMany
    {
        return $this->hasMany(Formula::class, 'company_detail_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function openingBalanceVats(): HasMany
    {
        return $this->hasMany(OpeningBalanceVat::class, 'company_detail_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function taxFreeVoucherRecords(): HasMany
    {
        return $this->hasMany(TaxFreeVoucherRecord::class, 'company_detail_id', 'id');
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
