<?php

namespace App\DataResources\CompanyDetail;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\DataResources\CompanyType\CompanyTypeResource;
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
            $this->withField('product');
            $this->company = new CompanyResource($obj->company);
        }

        if (in_array('type', $this->fields)) {
            $this->withField('type');
            $this->type = new CompanyTypeResource($obj->type);
        }
    }
}
