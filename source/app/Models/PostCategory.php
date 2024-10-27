<?php

namespace App\Models;

use App\Models\Interfaces\ITranslatable;
use App\Models\Traits\TranslatableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostCategory extends BaseModel implements ITranslatable
{
    use HasFactory, SoftDeletes, TranslatableTrait;

    protected $fillable = [
        'slug',
        'name',
        'note',
    ];

    /**
     * @return iterable of translatable fields
     */
    public function getTranslatableFields(): iterable
    {
        return ['name', 'note'];
    }

    /**
     * @param string $lang
     * @return mixed language resource or null
     */
    public function getLanguageResource(string $lang): mixed
    {
        return $this->locale($lang);
    }

    /**
     * Get the langs
     *
     * @return HasMany
     */
    public function postCategoryLangs(): HasMany
    {
        return $this->hasMany(PostCategoryLang::class);
    }

    /**
     * Get the locale record of a language
     * @param string $lang
     * @return PostLang|null
     */
    public function locale(string $lang): PostCategoryLang| null
    {
        return $this->postCategoryLangs()->where('lang', '=', $lang)->first();
    }

    /**
     * @return HasMany
     */
    public function groups(): HasMany
    {
        return $this->hasMany(PostGroup::class, 'post_category_id', 'id');
    }
}
