<?php

namespace App\DataResources\CategorySold;

use App\DataResources\BaseDataResource;
use App\Models\CategorySold;

class CategorySoldResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'note',
        'status',
        'created_by'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return CategorySold::class;
    }

    /**
     * Load data for output
     * @param CategorySold $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}