<?php
namespace App\Helpers\Utils;

class MathHelper{

    /**
     * Try to sum the given numerical-like strings or array into a value, which is not greater than the given limit
     * For example:
     *  - $arr = [1, 2, 3, 4], $limit = 11: the result is 10
     *  - $arr = [1, 2, 3, 4], $limit = 9: the result is 1
     *  - $arr = "125", $limit = 7: the result is 7 because the final value is always greater than the limit
     * @param $arr : array of numbers or a string-format number
     * @param int $limit
     * @return int
     */
    public static function sumIntoSingleNumberWithLimit($arr, int $limit = 9): int{
        if (!is_array($arr)) {
            if (intval($arr) <= $limit) return intval($arr);
            // otherwise
            if (strlen($arr) == 1) return $limit;
            $arr = str_split($arr);
        }
        $sum = array_sum($arr);
        if ($sum > $limit) $sum = MathHelper::sumIntoSingleNumberWithLimit($sum);
        return $sum;
    }

    /**
     * Try to sum the given numerical-like string or array into a value if its value is not a member the given masters
     * @param mixed $arr
     * @param array $masters
     * @return int
     */
    public static function sumIntoSingleNumberOrMaster(mixed $arr, array $masters = [11,22,33]){
        $val = (is_array($arr))? array_sum($arr) : intval($arr);

        if (in_array($val, $masters) || $val <= 9)
            return $val;
        return MathHelper::sumIntoSingleNumberOrMaster(str_split($val), $masters);
    }
}
