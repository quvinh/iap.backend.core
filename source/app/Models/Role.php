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
        'slug',
        'name',
    ];

    /**
     * @return HasManyThrougn
     */
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

    /**
     * @return HasMany
     */
    public function permissionGroup(): HasMany
    {
        return $this->hasMany(PermissionGroup::class, 'role_id', 'id');
    }

    /**
     * Get name of permissions
     * @return string
     */
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

    /**
     * Get id of permissions
     * @return array
     */
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
