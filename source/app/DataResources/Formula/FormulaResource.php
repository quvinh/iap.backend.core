<?php

namespace App\DataResources\Formula;

use App\DataResources\BaseDataResource;
use App\DataResources\CompanyDetail\CompanyDetailResource;
use App\DataResources\CompanyType\CompanyTypeResource;
use App\DataResources\FormulaCategoryPurchase\FormulaCategoryPurchaseResource;
use App\DataResources\FormulaCategorySold\FormulaCategorySoldResource;
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
            $this->company_detail = BaseDataResource::generateResources($obj->company_detail()->get(), CompanyDetailResource::class, ['company', 'type']);
        }

        if (in_array('company_type', $this->fields)) {
            $this->withField('company_type');
            $this->company_type = new CompanyTypeResource($obj->company_type);
        }

        if (in_array('category_purchases', $this->fields)) {
            $this->withField('category_purchases');
            $this->category_purchases = BaseDataResource::generateResources($obj->category_purchases()->get(), FormulaCategoryPurchaseResource::class, ['category_purchase']);
        }

        if (in_array('category_solds', $this->fields)) {
            $this->withField('category_solds');
            $this->category_solds = BaseDataResource::generateResources($obj->category_solds()->get(), FormulaCategorySoldResource::class, ['category_sold']);
        }
    }
}
