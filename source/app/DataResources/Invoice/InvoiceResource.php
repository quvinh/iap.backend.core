<?php

namespace App\DataResources\Invoice;

use App\DataResources\BaseDataResource;
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
        'invoie_symbol',
        'invoice_number_form',
        'invoice_number',
        'property',
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
        'json',
        'status',
        'created_by',
        'created_at',
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
            $this->invoice_details = BaseDataResource::generateResources($obj->invoice_details, InvoiceDetailResource::class);
        }
    }
}