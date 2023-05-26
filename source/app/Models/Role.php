<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'name'
    ];

    public function permissionGroup()
    {
        return $this->hasMany(PermissionGroup::class, 'role_id', 'id');
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
