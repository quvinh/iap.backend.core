<?php

namespace App\DataResources\InvoiceTask;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\Models\InvoiceTask;

class InvoiceTaskResource extends BaseDataResource
{
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
            $this->company = new CompanyResource($obj->company);
        }
    }
}
