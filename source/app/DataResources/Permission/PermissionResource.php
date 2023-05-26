<?php

namespace App\DataResources\Permision;

use App\DataResources\BaseDataResource;
use App\Models\Faq;
use App\Models\Permision;

class PermisionResource extends BaseDataResource
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
        return Permision::class;
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
