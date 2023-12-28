<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * @return BelongsTo
     */
    public function permission_group(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_id', 'id');
    }
}
