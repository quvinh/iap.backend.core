<?php

namespace App\DataResources\FormulaCommodity;

use App\DataResources\BaseDataResource;
use App\Models\FormulaCommodity;

class FormulaCommodityResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'formula_id',
        'note',
        'status',
        'created_by'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return FormulaCommodity::class;
    }

    /**
     * Load data for output
     * @param FormulaCommodity $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
