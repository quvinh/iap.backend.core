<?php

namespace App\DataResources\PostCategory;

use App\DataResources\BaseDataResource;
use App\DataResources\Category\CategoryResource;
use App\DataResources\PostGroup\PostGroupResource;
use App\DataResources\Product\ProductResource;
use App\Models\PostCategory;

class PostCategoryResource extends BaseDataResource
{
    protected $posts;

    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'slug',
        'name',
        'note',
        'created_at',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return PostCategory::class;
    }

    /**
     * Load data for output
     * @param PostCategory $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('posts', $this->fields)) {
            $this->posts = BaseDataResource::generateResources($obj->groups, PostGroupResource::class, ['posts']);
        }
    }
}
