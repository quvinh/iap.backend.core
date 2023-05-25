<?php
namespace App\Helpers\Utils;

class StatisticHelpers {

    /**
     * Find the values with the most frequency
     * @param $array
     * @return array
     */
    public static function findMostAppears($array): array
    {
        if (!is_array($array) || $array == null) $array = [];
        $list = array_count_values($array);
        if (array_key_exists(0, $list)) unset($list[0]);
        if ($list == null || count($list) == 0) return [];
        arsort($list);
        $max = max(array_values($list));
        $ret = [];
        foreach ($list as $key => $value){
            if ($value == $max) $ret[] = $key;
        }
        return $ret;
    }
}
?>
