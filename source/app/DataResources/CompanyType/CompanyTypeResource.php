<?php

namespace App\DataResources\CompanyType;

use App\DataResources\BaseDataResource;
use App\Models\CompanyType;

class CompanyTypeResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'note',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return CompanyType::class;
    }

    /**
     * Load data for output
     * @param CompanyType $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
