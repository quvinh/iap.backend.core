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
    // public function permissions(): HasManyThrough
    // {
    //     return $this->hasManyThrough(
    //         Permission::class,
    //         PermissionGroup::class,
    //         'role_id',
    //         'id',
    //         'id',
    //         'permission_id'
    //     );
    // }

    /**
     * @return HasMany
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(PermissionGroup::class, 'role_id', 'id');
    }

    /**
     * Get slug of permissions
     * @return string
     */
    public function getSlugPermissions()
    {
        $permissions = array_map(function ($item) {
            $permission = Permission::find($item['permission_id']);
            if ($permission) {
                return $permission->slug;
            }
        }, $this->permissions->toArray());
        // return implode(', ', $permissions);
        return $permissions;
    }
}
