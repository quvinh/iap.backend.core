<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Common\MetaInfo as CommonMetaInfo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assignable_id',
        'assignable_type',
    ];

    /**
     * @return MorphTo
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Meta info
     */
    public function setMetaInfo(CommonMetaInfo $meta = null, bool $isCreate = true): void
    {
        if (is_null($meta))
            $meta = new CommonMetaInfo('');
    }
}
