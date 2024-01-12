<?php

namespace App\Helpers\Utils;

use App\Helpers\Common\Constants;

class RoundMoneyHelper
{
    /**
     * Round money: using round/floor
     * @param float $money
     * @param int $rounding
     * @return float
     */
    public static function roundMoney(float $money, int $rounding = 1): float
    {
        if ($rounding == 1) return round($money);
        else return floor($money);
    }
}
