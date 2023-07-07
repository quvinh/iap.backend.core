<?php

namespace App\DataResources\Company;

use App\DataResources\BaseDataResource;
use App\DataResources\CompanyDetail\CompanyDetailResource;
use App\DataResources\CompanyType\CompanyTypeResource;
use App\Models\Company;

class CompanyAllResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'tax_code',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return Company::class;
    }

    /**
     * Load data for output
     * @param Company $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
