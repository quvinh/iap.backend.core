<?php

namespace App\DataResources\CompanyDetail;

use App\DataResources\BaseDataResource;
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
    }
}
