<?php

namespace App\DataResources;

use App\Exceptions\Request\InvalidSortInfoException;
use Throwable;

class SortInfo
{
    public string $type;
    public string $column;

    public function __construct()
    {
        $this->type = 'ASC';
        $this->column = 'id';
    }

    /**
     * parse sort
     *
     * @param mixed $param
     * @return SortInfo
     * @throws Throwable
     */
    public static function parse(mixed $param): SortInfo
    {
        try {
            $sort = new SortInfo();
            $info = $param;
            if (in_array('type', array_keys($info))) {
                $type = $info['type'];
                $sort->type = $type ?? $sort->type;
            }
            if (in_array('column', array_keys($info))) {
                $column = $info['column'];
                $sort->column = $column ?? $sort->column;
            }
            return $sort;
        } catch (\Exception $ex) {
            throw new InvalidSortInfoException('ERR_SORT');
        }
    }
}
