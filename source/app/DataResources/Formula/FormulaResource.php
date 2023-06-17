<?php

namespace App\DataResources\Formula;

use App\DataResources\BaseDataResource;
use App\DataResources\CompanyDetail\CompanyDetailResource;
use App\Models\Formula;

class FormulaResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'company_detail_id',
        'company_type_id',
        'status',
        'note'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return Formula::class;
    }

    /**
     * Load data for output
     * @param Formula $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('company_detail', $this->fields)) {
            $this->company_detail = BaseDataResource::generateResources($obj->company_detail()->get(), CompanyDetailResource::class, ['company']);
        }
    }
}
