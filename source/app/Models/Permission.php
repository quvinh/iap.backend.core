<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'slug',
        'name',
    ];

    /**
     * @return HasMany
     */
    public function permission_groups(): HasMany
    {
        return $this->hasMany(PermissionGroup::class, 'permission_id', 'id');
    }
}
