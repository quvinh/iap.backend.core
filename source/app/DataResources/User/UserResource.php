<?php

namespace App\DataResources\User;

use App\DataResources\BaseDataResource;
use App\Models\Company;
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
     * @param User $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
        if (in_array('companies', $this->fields)) {
            $this->companies = BaseDataResource::generateResources($obj->permissions, Company::class);
        }
    }
}
