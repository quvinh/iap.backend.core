<?php

namespace App\Helpers\Utils;

use App\Exceptions\Request\InvalidDatetimeInputException;
use Carbon\Carbon;

/**
 * @property string $date
 * @property int $addDays
 */
class DateHelper
{
    /**
     * parse date
     *
     * @param string|null $date
     * @param int $addDays
     * @return string
     *@throws InvalidDatetimeInputException
     */
    public static function parse(string $date = null, int $addDays = 0): string
    {
        try {
            $result = new Carbon($date);
            $result->addDays($addDays);
            return $result->format('Y-m-d');
        } catch (\Exception $ex) {
            throw new InvalidDatetimeInputException("ERR_TIMESTAMP");
        }
    }

    /**
     * parse date
     *
     * @param string|null $date
     * @param int $addDays
     * @return string
     *@throws InvalidDatetimeInputException
     */
    public static function parseWithTime(string $date = null, int $addDays = 0): string
    {
        try {
            $result = new Carbon($date);
            $result->addDays($addDays);
            return $result->format('Y-m-d H:i:s');
        } catch (\Exception $ex) {
            throw new InvalidDatetimeInputException("ERR_TIMESTAMP");
        }
    }

    /**
     * parse date
     *
     * @param string|null $date
     * @param int $addDays
     * @return string
     *@throws InvalidDatetimeInputException
     */
    public static function parseMd(string $date = null, int $addDays = 0): string
    {
        try {
            $result = new Carbon($date);
            $result->addDays($addDays);
            return $result->format('m-d');
        } catch (\Exception $ex) {
            throw new InvalidDatetimeInputException("ERR_TIMESTAMP");
        }
    }
}
