<?php

namespace App\DataResources\Invoice;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyResource;
use App\DataResources\InvoiceDetail\InvoiceDetailResource;
use App\Models\Invoice;

class InvoiceResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_id',
        'invoice_task_id',
        'type',
        'date',
        'invoice_symbol',
        'invoice_number_form',
        'invoice_number',
        'property',
        'invoice_status',
        'processing_status',
        'note',
        'partner_name',
        'partner_tax_code',
        'partner_address',
        'currency',
        'currency_price',
        'sum_money_no_vat',
        'sum_money_vat',
        'sum_money_discount',
        'sum_money',
        'rounding',
        'payment_method',
        'verification_code',
        'verification_code_status',
        'json',
        'status',
        'locked',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
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

        if (in_array('invoice_details', $this->fields)) {
            $this->invoice_details = BaseDataResource::generateResources($obj->invoice_details, InvoiceDetailResource::class, ['item_code']);
        }

        if (in_array('company', $this->fields)) {
            $this->company = BaseDataResource::generateResources($obj->company()->get(), CompanyResource::class);
        }
    }
}
