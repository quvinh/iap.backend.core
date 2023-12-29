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
        'type',
        'date',
        'invoice_symbol',
        'invoice_number_form',
        'invoice_number',
        'partner_name',
        'partner_tax_code',
        'partner_address',
        'currency',
        'currency_price',
        'payment_method',
        'verification_code_status',
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

        if (in_array('invoice_details', $this->fields)) {
            $this->invoice_details = BaseDataResource::generateResources($obj->invoice_details, InvoiceDetailResource::class, ['item_code']);
        }

        if (in_array('company', $this->fields)) {
            $this->company = BaseDataResource::generateResources($obj->company()->get(), CompanyResource::class);
        }
    }
}
