<?php

namespace App\DataResources\CompanyDocument;

use App\DataResources\BaseDataResource;
use App\Models\CompanyDocument;

class CompanyDocumentResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_id',
        'name',
        'year',
        'file',
        'created_at',
        'created_by',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return CompanyDocument::class;
    }

    /**
     * Load data for output
     * @param CompanyDocument $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
