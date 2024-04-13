<?php

namespace App\DataResources\Template;

use App\DataResources\BaseDataResource;
use App\Models\Template;

class TemplateResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'template',
        'created_at',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return Template::class;
    }

    /**
     * Load data for output
     * @param Template $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
