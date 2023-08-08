<?php

namespace App\DataResources\Formula;

use App\DataResources\BaseDataResource;
use App\DataResources\CompanyDetail\CompanyDetailResource;
use App\DataResources\CompanyType\CompanyTypeResource;
use App\DataResources\FormulaCategoryPurchase\FormulaCategoryPurchaseResource;
use App\DataResources\FormulaCategorySold\FormulaCategorySoldResource;
use App\DataResources\FormulaCommodity\FormulaCommodityResource;
use App\DataResources\FormulaMaterial\FormulaMaterialResource;
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
        'sum_from',
        'sum_to',
        'sum_avg',
        'status',
        'note',
        'created_by',
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
            $this->category_purchases = BaseDataResource::generateResources($obj->category_purchases, FormulaCategoryPurchaseResource::class, ['category_purchase']);
        }

        if (in_array('category_solds', $this->fields)) {
            $this->category_solds = BaseDataResource::generateResources($obj->category_solds, FormulaCategorySoldResource::class, ['category_sold']);
        }

        if (in_array('commodities', $this->fields)) {
            $this->commodities = BaseDataResource::generateResources($obj->commodities, FormulaCommodityResource::class);
        }

        if (in_array('materials', $this->fields)) {
            $this->materials = BaseDataResource::generateResources($obj->materials, FormulaMaterialResource::class);
        }
    }
}
