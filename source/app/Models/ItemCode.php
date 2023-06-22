<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property float $price
 * @property float $quantity
 * @property float $begining_total_value
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
        'begining_total_value',
        'unit',
        'year',
        'status',
    ];

    public function setItemCode(float $quantity, float $begining_total_value): void
    {
        if ($quantity <= 0) throw new \Exception('Invalid quantity');
        $this->price = $this->getPrice();
        $this->quantity = $quantity;
        $this->begining_total_value = $begining_total_value;
    }

    public function getPrice(): float
    {
        try {
            $getPrice = $this->begining_total_value / $this->quantity;
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
