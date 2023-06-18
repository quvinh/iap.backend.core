<?php

namespace App\DataResources\FormulaCategorySold;

use App\DataResources\BaseDataResource;
use App\DataResources\CategorySold\CategorySoldResource;
use App\Models\FormulaCategorySold;

class FormulaCategorySoldResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'formula_id',
        'category_sold_id',
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
        return FormulaCategorySold::class;
    }

    /**
     * Load data for output
     * @param FormulaCategorySold $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('category_sold', $this->fields)) {
            $this->withField('category_sold');
            $this->category_sold = new CategorySoldResource($obj->category_sold);
        }
    }
}
