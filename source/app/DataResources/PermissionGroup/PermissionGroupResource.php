<?php

namespace App\DataResources\PermissionGroup;

use App\DataResources\BaseDataResource;
use App\DataResources\Permission\PermissionResource;
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

        if (in_array('permission', $this->fields)) {
            $this->withField('permission');
            $this->permission = new PermissionResource($obj->permission);
        }
    }
}
