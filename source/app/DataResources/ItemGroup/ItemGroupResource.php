<?php

namespace App\DataResources\ItemGroup;

use App\DataResources\BaseDataResource;
use App\DataResources\ItemCode\ItemCodeResource;
use App\Models\ItemGroup;

class ItemGroupResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_id',
        'item_group',
        'note',
        'created_by',
        'created_at',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return ItemGroup::class;
    }

    /**
     * Load data for output
     * @param ItemGroup $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('item_codes', $this->fields)) {
            $this->withField('item_codes');
            $this->item_codes = new ItemCodeResource($obj->item_codes);
        }
    }
}
