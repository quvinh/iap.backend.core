<?php

namespace App\DataResources\Permission;

use App\DataResources\BaseDataResource;
use App\DataResources\PermissionGroup\PermissionGroupResource;
use App\Models\Permission;

class PermissionResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'slug',
        'name',
        'created_by',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return Permission::class;
    }

    /**
     * Load data for output
     * @param Permission $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('permission_groups', $this->fields)) {
            $this->permission_groups = BaseDataResource::generateResources($obj->permission_groups, PermissionGroupResource::class);
        }
    }
}
