<?php

namespace App\DataResources\InvoiceMedia;

use App\DataResources\BaseDataResource;
use App\Models\InvoiceMedia;

class InvoiceMediaResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_id',
        'invoice_id',
        'year',
        'path',
        'note',
        'created_by',
        'created_at',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return InvoiceMedia::class;
    }

    /**
     * Load data for output
     * @param InvoiceMedia $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
