<?php

namespace App\DataResources\CompanyDetail;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\DataResources\CompanyDetail\CompanyDetailTaxFreeVoucherResource;
use App\DataResources\CompanyType\CompanyTypeResource;
use App\DataResources\FirstAriseAccount\FirstAriseAccountResource;
use App\Models\CompanyDetail;
use App\Models\CompanyDetailTaxFreeVoucher;

class CompanyDetailResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'tax_free_voucher_id',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return CompanyDetailTaxFreeVoucher::class;
    }

    /**
     * Load data for output
     * @param CompanyDetailTaxFreeVoucher $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
