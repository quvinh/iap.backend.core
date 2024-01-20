<?php

namespace App\DataResources\InvoiceMedia;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\DataResources\Invoice\InvoiceBasicResource;
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
        'month',
        'path',
        'note',
        'status',
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

        if (in_array('invoice', $this->fields)) {
            $this->withField('invoice');
            $this->invoice = new InvoiceBasicResource($obj->invoice);
        }

        if (in_array('company', $this->fields)) {
            $this->withField('company');
            $this->company = new CompanyResource($obj->company);
        }
    }
}
