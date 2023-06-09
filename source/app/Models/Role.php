<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'name'
    ];

    public function permissions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Permission::class,
            PermissionGroup::class,
            'role_id',
            'id',
            'id',
            'permission_id'
        );
    }

    public function getNamePermissions()
    {
        $permissions = array_map(function ($item) {
            $permission = Permission::find($item['permission_id']);
            if ($permission) {
                return $permission->name;
            }
        }, $this->permissionGroup->toArray());
        return implode(', ', $permissions);
    }

    public function getIdOfPermissions()
    {
        $permissions = array_map(function ($item) {
            $permission = Permission::find($item['permission_id']);
            if ($permission) {
                return $permission->id;
            }
        }, $this->permissionGroup->toArray());
        return $permissions;
    }
}
