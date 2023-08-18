<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemGroup extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'item_group',
        'note',
    ];

    /**
     * @return HasMany
     */
    public function item_codes(): HasMany
    {
        return $this->hasMany(ItemCode::class, 'item_group_id', 'id');
    }
}
