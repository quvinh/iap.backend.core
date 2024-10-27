<?php

namespace App\Models;

use App\Models\Interfaces\ILanguageResource;
use App\Models\Traits\LanguageResourceTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostCategoryLang extends BaseModel implements ILanguageResource
{
    use HasFactory, SoftDeletes, LanguageResourceTrait;

    protected $fillable = [
        'post_category_id',
        'lang',
        'name',
        'note',
    ];

    /**
     * Get the post
     *
     * @return BelongsTo
     */
    public function postCategory(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class);
    }
}
