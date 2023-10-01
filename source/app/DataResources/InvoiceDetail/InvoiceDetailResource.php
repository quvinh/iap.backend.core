<?php

namespace App\DataResources\InvoiceDetail;

use App\DataResources\BaseDataResource;
use App\Models\InvoiceDetail;

class InvoiceDetailResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'invoice_id',
        'formula_id',
        'formula_path_id',
        'formula_commodity_id',
        'formula_material_id',
        'formula_group_name',
        'item_code_id',
        'product',
        'product_exchange',
        'unit',
        'quantity',
        'price',
        'vat',
        'vat_money',
        'total_money',
        'warehouse',
        // 'main_entity',
        // 'visible',
        'note',
        'created_by',
        'created_at',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return InvoiceDetail::class;
    }

    /**
     * Load data for output
     * @param InvoiceDetail $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
