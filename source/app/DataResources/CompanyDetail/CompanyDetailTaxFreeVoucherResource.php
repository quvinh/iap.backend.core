<?php

namespace App\DataResources\CompanyDetail;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\DataResources\TaxFreeVoucher\TaxFreeVoucherResource;
use App\Models\CompanyDetailTaxFreeVoucher;

class CompanyDetailTaxFreeVoucherResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_detail_id',
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

        if (in_array('tax_free_voucher', $this->fields)) {
            $this->withField('tax_free_voucher');
            $this->tax_free_voucher = new TaxFreeVoucherResource($obj->tax_free_voucher);
        }
    }
}
