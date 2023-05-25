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

    /**
     * Get all permissions
     */
    public function getAllPermissions()
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
}
