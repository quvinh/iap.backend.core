<?php

namespace App\DataResources\Permission;

use App\DataResources\BaseDataResource;
use App\Models\Faq;
use App\Models\Permission;

class PermissionResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return Permission::class;
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
