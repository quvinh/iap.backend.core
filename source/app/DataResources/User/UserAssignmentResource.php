<?php

namespace App\DataResources\User;

use App\DataResources\BaseDataResource;
use App\Models\UserAssignment;

class UserAssignmentResource extends BaseDataResource
{
    protected $user;
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'user_id',
        'assignable_id',
        'assignable_type',
        'created_at',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return UserAssignment::class;
    }

    /**
     * Load data for output
     * @param UserAssignment $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('user', $this->fields)) {
            $this->withField('user');
            $this->user = new UserResource($obj->user);
        }
    }
}
