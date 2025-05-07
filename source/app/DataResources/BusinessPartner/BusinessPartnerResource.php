<?php

namespace App\DataResources\BusinessPartner;

use App\DataResources\BaseDataResource;
use App\Models\BusinessPartner;

class BusinessPartnerResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_id',
        'name',
        'tax_code',
        'email',
        'phone',
        'address',
        'logo',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return BusinessPartner::class;
    }

    /**
     * Load data for output
     * @param BusinessPartner $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
