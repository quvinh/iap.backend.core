<?php

namespace App\DataResources\Company;

use App\DataResources\BaseDataResource;
use App\Models\Company;

class CompanyResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'tax_code',
        'tax_password',
        'email',
        'phone',
        'address',
        'logo',
        'manager_name',
        'manager_role',
        'manager_phone',
        'manager_email',
        'status'
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
