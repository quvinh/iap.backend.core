<?php

namespace App\DataResources\CompanyDetail;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\DataResources\CompanyDetail\CompanyDetailAriseAccountResource;
use App\DataResources\CompanyType\CompanyTypeResource;
use App\DataResources\FirstAriseAccount\FirstAriseAccountResource;
use App\Models\CompanyDetail;

class CompanyDetailResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_id',
        'company_type_id',
        'description',
        'year'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return CompanyDetail::class;
    }

    /**
     * Load data for output
     * @param CompanyDetail $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('company', $this->fields)) {
            // $this->company = BaseDataResource::generateResources($obj->company, CompanyResource::class);
            $this->withField('company');
            $this->company = new CompanyResource($obj->company);
        }

        if (in_array('type', $this->fields)) {
            $this->withField('type');
            $this->type = new CompanyTypeResource($obj->type);
        }

        if (in_array('accounts', $this->fields)) {
            $this->accounts = BaseDataResource::generateResources($obj->accounts, CompanyDetailAriseAccountResource::class, ['arise_account']);
        }

        if (in_array('tax_free_vouchers', $this->fields)) {
            $this->tax_free_vouchers = BaseDataResource::generateResources($obj->tax_free_vouchers, CompanyDetailTaxFreeVoucherResource::class);
        }
    }
}
