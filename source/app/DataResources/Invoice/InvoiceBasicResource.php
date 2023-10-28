<?php

namespace App\DataResources\Invoice;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\DataResources\InvoiceDetail\InvoiceDetailResource;
use App\Models\Invoice;

class InvoiceBasicResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_id',
        'invoice_task_id',
        'type',
        'status',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return Invoice::class;
    }

    /**
     * Load data for output
     * @param Invoice $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
