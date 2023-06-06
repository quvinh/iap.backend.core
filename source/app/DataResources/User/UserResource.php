<?php

namespace App\DataResources\User;

use App\DataResources\BaseDataResource;
use App\Models\Faq;
use App\Models\User;

class UserResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'username',
        'email',
        'name',
        'photo',
        'address',
        'birthday',
        'phone',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return User::class;
    }

    /**
     * Load data for output
     * @param Faq $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
