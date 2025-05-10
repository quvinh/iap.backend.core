<?php

namespace App\DataResources\InvoiceDetail;

use App\DataResources\BaseDataResource;
use App\DataResources\Invoice\InvoiceBasicResource;
use App\Models\InvoiceDetail;
use App\DataResources\ItemCode\ItemCodeResource;

class InvoiceDetailResource extends BaseDataResource
{
    protected $item_code;
    protected $item_code_path;
    protected $invoice;

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

        if (in_array('item_code', $this->fields)) {
            $this->withField('item_code');
            $this->item_code = new ItemCodeResource($obj->item_code);
            if (!empty($this->item_code->id)) {
                $product = $this->item_code->product ? "|{$this->item_code->product}" : "";
                $this->withField('item_code_path');
                $this->item_code_path = "{$this->item_code->id}|{$this->item_code->product_code}$product";
            }
        }

        if (in_array('invoice', $this->fields)) {
            $this->withField('invoice');
            $this->invoice = new InvoiceBasicResource($obj->invoice, ['business_partner']);
        }
    }
}
