<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Common\MetaInfo as CommonMetaInfo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CompanyDetailTaxFreeVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_detail_id',
        'tax_free_voucher_id'
    ];

    public $timestamps = false;

    /**
     * Meta info
     */
    public function setMetaInfo(CommonMetaInfo $meta = null, bool $isCreate = true): void
    {
        if (is_null($meta))
            $meta = new CommonMetaInfo('');
    }

    /**
     * @return HasOne
     */
    public function tax_free_voucher(): HasOne
    {
        return $this->hasOne(TaxFreeVoucher::class, 'id', 'tax_free_voucher_id');
    }
}
