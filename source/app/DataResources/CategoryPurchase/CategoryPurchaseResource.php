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
        'method',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
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
