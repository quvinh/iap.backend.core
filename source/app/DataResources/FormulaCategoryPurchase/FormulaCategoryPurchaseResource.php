<?php

namespace App\DataResources\FormulaCategoryPurchase;

use App\DataResources\BaseDataResource;
use App\DataResources\CategoryPurchase\CategoryPurchaseResource;
use App\Models\FormulaCategoryPurchase;

class FormulaCategoryPurchaseResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'formula_id',
        'category_purchase_id',
        'value_from',
        'value_to',
        'value_avg',
        'visible_value'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return FormulaCategoryPurchase::class;
    }

    /**
     * Load data for output
     * @param FormulaCategoryPurchase $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('category_purchase', $this->fields)) {
            $this->withField('category_purchase');
            $this->category_purchase = new CategoryPurchaseResource($obj->category_purchase);
        }
    }
}
