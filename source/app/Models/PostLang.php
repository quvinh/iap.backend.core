<?php

namespace App\Models;

use App\Models\Interfaces\ILanguageResource;
use App\Models\Traits\LanguageResourceTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostLang extends BaseModel implements ILanguageResource
{
    use HasFactory, SoftDeletes, LanguageResourceTrait;

    protected $fillable = [
        'post_id',
        'lang',
        'name',
        'description',
        'long_description',
    ];

    /**
     * Get the post
     *
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
