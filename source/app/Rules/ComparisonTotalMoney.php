<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ComparisonTotalMoney implements Rule
{
    private $attribute;
    private float $quantity;
    private float $price;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(float $quantity, float $price)
    {
        $this->quantity = $quantity;
        $this->price = $price;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return floatval($value) === $this->quantity * $this->price;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Comparison between total money and quantity * price is not equal.';
    }
}
