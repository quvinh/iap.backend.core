<?php

namespace App\DataResources\ItemCode;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\DataResources\ItemGroup\ItemGroupResource;
use App\Models\ItemCode;

class ItemCodeResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_id',
        'item_group_id',
        'product_code',
        'product_exchange',
        'product',
        'price',
        'quantity',
        'opening_balance_value',
        'unit',
        'year',
        'status',
        'created_by',
        'created_at',
        'deleted_at',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return ItemCode::class;
    }

    /**
     * Load data for output
     * @param ItemCode $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('company', $this->fields)) {
            $this->withField('company');
            $this->company = new CompanyResource($obj->company);
        }

        if (in_array('item_group', $this->fields)) {
            $this->withField('item_group');
            $this->item_group = new ItemGroupResource($obj->item_group);
        }
    }
}
