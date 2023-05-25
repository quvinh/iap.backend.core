<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'permisison_id'
    ];

    public $timestamps = false;

    public function permissions()
    {
        return $this->hasOne(Permission::class, 'id', 'permission_id');
    }
}
