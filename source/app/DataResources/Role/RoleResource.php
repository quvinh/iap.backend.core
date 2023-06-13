<?php

namespace App\DataResources\Role;

use App\DataResources\BaseDataResource;
use App\DataResources\Permission\PermissionResource;
use App\Models\Role;

class RoleResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return Role::class;
    }

    /**
     * Load data for output
     * @param Role $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
        if (in_array('permissions', $this->fields)) {
            $this->permissions = BaseDataResource::generateResources($obj->permissions, PermissionResource::class);
        }
    }
}
