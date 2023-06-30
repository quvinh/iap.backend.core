<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $invoice_id
 * @property int $item_code_id
 * @property string $product
 * @property string $unit
 * @property float $quantity
 * @property float $price
 * @property int $vat
 * @property float $vat_money
 * @property float $total_money
 */
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

    public function setInvoiceDetail(float $total_money, int $vat): void
    {
        $this->total_money = $total_money;
        $this->vat = $vat;
        $this->vat_money = $this->getVatMoney();
    }

    public function getVatMoney(): float
    {
        try {
            $vat = $this->vat;
            if ($this->vat < 0) $vat = 0; // Exception vat=-1; vat=-2
            $vat_money = $this->total_money * ($vat / 100);
            return round($vat_money, 2);
        } catch (\Exception) {
            return 0;
        }
    }
}
