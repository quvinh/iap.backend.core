<?php

namespace App\DataResources\CompanyDetail;

use App\DataResources\BaseDataResource;
use App\DataResources\FirstAriseAccount\FirstAriseAccountResource;
use App\Models\CompanyDetailAriseAccount;

class CompanyDetailAriseAccountResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_detail_id',
        'arise_account_id',
        'value_from',
        'value_to',
        'value_avg',
        // 'visible_value'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return CompanyDetailAriseAccount::class;
    }

    /**
     * Load data for output
     * @param CompanyDetailAriseAccount $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('arise_account', $this->fields)) {
            $this->arise_account = new FirstAriseAccountResource($obj->arise_account);
        }
    }
}
