<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDetailTaxFreeVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_detail_id',
        'tax_free_voucher_id'
    ];

    /**
     * Meta info
     */
    public function setMetaInfo(CommonMetaInfo $meta = null, bool $isCreate = true): void
    {
        if (is_null($meta))
            $meta = new CommonMetaInfo('');
    }
}
