<?php

namespace App\DataResources\PermissionGroup;

use App\DataResources\BaseDataResource;
use App\Models\PermissionGroup;

class PermissionGroupResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'role_id',
        'permission_id'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return PermissionGroup::class;
    }

    /**
     * Load data for output
     * @param PermissionGroup $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
