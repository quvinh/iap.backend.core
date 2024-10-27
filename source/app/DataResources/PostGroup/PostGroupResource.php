<?php

namespace App\DataResources\PostGroup;

use App\DataResources\BaseDataResource;
use App\DataResources\Category\CategoryResource;
use App\DataResources\Post\PostResource;
use App\DataResources\PostCategory\PostCategoryResource;
use App\DataResources\Product\ProductResource;
use App\Models\PostGroup;

class PostGroupResource extends BaseDataResource
{
    protected $posts;
    protected $post_categories;

    /**
     * @var array|string[]
     */
    protected array $fields = [
        'post_id',
        'post_category_id',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return PostGroup::class;
    }

    /**
     * Load data for output
     * @param PostGroup $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('posts', $this->fields)) {
            $this->posts = BaseDataResource::generateResources($obj->posts, PostResource::class);
        }

        if (in_array('post_categories', $this->fields)) {
            $this->post_categories = BaseDataResource::generateResources($obj->post_categories, PostCategoryResource::class);
        }
    }
}
