<?php

namespace App\Models;

use App\Models\Interfaces\ITranslatable;
use App\Models\Traits\TranslatableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends BaseModel implements ITranslatable
{
    use HasFactory, SoftDeletes, TranslatableTrait;

    protected $fillable = [
        'slug',
        'name',
        'photo',
        'published_date',
        'description',
        'long_description',
        'status',
    ];

    /**
     * @return iterable of translatable fields
     */
    public function getTranslatableFields(): iterable
    {
        return ['name', 'description', 'long_description'];
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
    public function postLangs(): HasMany
    {
        return $this->hasMany(PostLang::class);
    }

    /**
     * Get the locale record of a language
     * @param string $lang
     * @return PostLang|null
     */
    public function locale(string $lang): PostLang| null
    {
        return $this->postLangs()->where('lang', '=', $lang)->first();
    }

    /**
     * @return HasMany
     */
    public function groups(): HasMany
    {
        return $this->hasMany(PostGroup::class, 'post_id', 'id');
    }
}
