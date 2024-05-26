<?php

namespace App\DataResources\InvoiceTask;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\DataResources\Invoice\InvoiceResource;
use App\Models\InvoiceTask;

class InvoiceTaskResource extends BaseDataResource
{
    protected $company;
    protected $invoices;

    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_id',
        'month_of_year',
        'task_import',
        'task_progress',
        'note',
        'opening_balance_value',
        'total_money_sold',
        'total_money_purchase',
        'meta',
        'created_at'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return InvoiceTask::class;
    }

    /**
     * Load data for output
     * @param InvoiceTask $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('company', $this->fields)) {
            $this->withField('company');
            $this->company = new CompanyResource($obj->company, ['years']);
        }

        if (in_array('invoices', $this->fields)) {
            $this->invoices = BaseDataResource::generateResources($obj->invoices()->get(), InvoiceResource::class, ['invoice_details']);
        }
    }
}
