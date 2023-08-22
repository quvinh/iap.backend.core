<?php

namespace App\DataResources\ItemGroup;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\DataResources\ItemCode\ItemCodeResource;
use App\Models\ItemCode;
use App\Models\ItemGroup;

class ItemGroupResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_id',
        'name',
        'code',
        'year',
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
            $this->item_codes = BaseDataResource::generateResources($obj->item_codes->toArray(), ItemCode::class);
        }

        if (in_array('company', $this->fields)) {
            $this->withField('company');
            $this->company = new CompanyResource($obj->company);
        }
    }
}
