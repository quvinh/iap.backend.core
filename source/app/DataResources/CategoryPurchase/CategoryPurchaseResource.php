<?php

namespace App\DataResources\CategoryPurchase;

use App\DataResources\BaseDataResource;
use App\Models\CategoryPurchase;

class CategoryPurchaseResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'tag',
        'note',
        'status',
        'created_by'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return CategoryPurchase::class;
    }

    /**
     * Load data for output
     * @param CategoryPurchase $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
