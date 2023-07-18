<?php

namespace App\DataResources\FirstAriseAccount;

use App\DataResources\BaseDataResource;
use App\Models\FirstAriseAccount;

class FirstAriseAccountAllResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'number_account',
        'number_percent',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return FirstAriseAccount::class;
    }

    /**
     * Load data for output
     * @param FirstAriseAccount $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
