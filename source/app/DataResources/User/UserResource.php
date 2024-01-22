<?php

namespace App\DataResources\User;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\DataResources\Role\RoleResource;
use App\Models\User;

class UserResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'role_id',
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

        if (in_array('role', $this->fields)) {
            $this->withField('role');
            $this->role = new RoleResource($obj->role);
            // $this->role = BaseDataResource::generateResources($obj->role()->get(), RoleResource::class, ['permissions']);
        }

        if (in_array('companies', $this->fields)) {
            $this->companies = BaseDataResource::generateResources($obj->companies, CompanyResource::class);
        }
    }
}
