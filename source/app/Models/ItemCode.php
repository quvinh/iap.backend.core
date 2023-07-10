<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property float $price
 * @property float $quantity
 * @property float $opening_balance_value
 */
class ItemCode extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'product_code',
        'product_exchange',
        'product',
        'price',
        'quantity',
        'opening_balance_value',
        'unit',
        'year',
        'status',
    ];

    public function setItemCode(float $quantity, float $opening_balance_value): void
    {
        if ($quantity <= 0) throw new \Exception('Invalid quantity');
        $this->price = $this->getPrice();
        $this->quantity = $quantity;
        $this->opening_balance_value = $opening_balance_value;
    }

    public function getPrice(): float
    {
        try {
            $getPrice = $this->opening_balance_value / $this->quantity;
            return round($getPrice, 3);
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
}
