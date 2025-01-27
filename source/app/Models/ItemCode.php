<?php

namespace App\Models;

use App\Helpers\Utils\RoundMoneyHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property float $price
 * @property float $quantity
 * @property float $opening_balance_value
 * @property string $created_by
 * @property string $update_by
 */
class ItemCode extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'company_id',
        'item_group_id',
        'product_code',
        'product_exchange',
        'product',
        'price',
        'quantity',
        'opening_balance_value',
        'unit',
        'year',
        'status',
        'is_imported_goods',
    ];

    public function setItemCode(float $quantity, float $price): void
    {
        if ($quantity < 0) throw new \Exception('Invalid quantity');
        $this->quantity = $quantity;
        $this->price = $price;
        $this->opening_balance_value = $this->getTotal();
    }

    public function getTotal(): float
    {
        try {
            // $getPrice = $this->opening_balance_value / $this->quantity;
            $total = $this->quantity * $this->price;
            return RoundMoneyHelper::roundMoney($total);
        } catch (\Exception) {
            return 0;
        }
    }

    /**
     * @return HasOne
     */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    /**
     * @return HasOne
     */
    public function item_group(): HasOne
    {
        return $this->hasOne(ItemGroup::class, 'id', 'item_group_id');
    }
}
