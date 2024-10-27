<?php

namespace App\DataResources\Post;

use App\DataResources\BaseDataResource;
use App\DataResources\PostGroup\PostGroupResource;
use App\Models\Post;

class PostResource extends BaseDataResource
{
    protected $post_categories;
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'slug',
        'name',
        'photo',
        'published_date',
        'description',
        'long_description',
        'status',
        'created_by',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return Post::class;
    }

    /**
     * Load data for output
     * @param Post $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('post_categories', $this->fields)) {
            $this->post_categories = BaseDataResource::generateResources($obj->groups, PostGroupResource::class, ['post_categories']);
        }
    }
}
