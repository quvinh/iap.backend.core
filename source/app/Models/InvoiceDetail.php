<?php

namespace App\Models;

use App\Helpers\Utils\RoundMoneyHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $invoice_id
 * @property int $item_code_id
 * @property string $product
 * @property string $product_exchange
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
        'formula_path_id',
        'formula_commodity_id',
        'formula_material_id',
        'item_code_id',
        'formula_group_name',
        'product',
        'product_exchange',
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
        'icp_price',
        'isf_price',
        'import_tax',
        'special_consumption_tax',
        'customs_code',
    ];

    public function setInvoiceDetail(float $quantity, float $price, int $vat, bool $rounding = true): void
    {
        $this->quantity = $quantity;
        $this->price = $price;
        $this->total_money = RoundMoneyHelper::roundMoney($quantity * $price, $rounding ? 1 : 0);
        $this->vat = $vat;
        $this->vat_money = $this->getVatMoney();
    }

    public function getVatMoney(): float
    {
        try {
            $vat = $this->vat;
            if ($this->vat < 0) $vat = 0; // Exception vat=-1; vat=-2
            $vat_money = $this->quantity * $this->price * $vat * 0.01;
            return round($vat_money, 2);
        } catch (\Exception) {
            return 0;
        }
    }

    /**
     * @return HasOne
     */
    public function item_code(): HasOne
    {
        return $this->hasOne(ItemCode::class, 'id', 'item_code_id');
    }

    /**
     * @return HasOne
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }
}
