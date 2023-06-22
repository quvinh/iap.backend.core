<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceDetail extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'formula_id',
        'formula_commodity_id',
        'formula_material_id',
        'item_code_id',
        'product',
        'unit',
        'quantity',
        'price',
        'vat',
        'vat_money',
        'total_money',
        'warehouse',
        'main_entity',
        'visible',
        'note',
    ];
}
