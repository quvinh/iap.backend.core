<?php

namespace App\DataResources\FormulaMaterial;

use App\DataResources\BaseDataResource;
use App\Models\FormulaMaterial;

class FormulaMaterialResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'formula_id',
        'value_from',
        'value_to',
        'value_avg',
        'note',
        'status',
        'created_by'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return FormulaMaterial::class;
    }

    /**
     * Load data for output
     * @param FormulaMaterial $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
