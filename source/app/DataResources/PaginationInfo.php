<?php

namespace App\DataResources;

use App\Exceptions\Request\InvalidPaginationInfoException;
use App\Models\BaseModel;
use PHPUnit\Exception;

/**
 * @property int $page
 * @property int $perPage
 * @property int $lastPage
 * @property int $total
 */
class PaginationInfo extends BaseDataResource
{
    public int $page;
    public int $perPage;
    public int $lastPage;
    public int $total;

    public function __construct()
    {
        $this->page = 1;
        $this->perPage = env('PAGINATION_DEFAULT_PER_PAGE', 25);
    }

    /**
     * Convert this object to JSON
     * @return array
     */
    public function toArray(bool $allowNull = false): array{
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'last_page' => $this->lastPage,
            'total' => $this->total
        ];
    }

    /**
     * parse paginate
     *
     * @param array<mixed> $param
     * @return ?PaginationInfo
     * @throws InvalidPaginationInfoException
     */
    public static function parse(array $param): ?PaginationInfo
    {
        try {
            $ret = new PaginationInfo();
            $info = $param; #$param['pagination'];
            if (in_array('page', array_keys($info))) {
                $c = $info['page'];
                $ret->page = $c > 0 ? $c : $ret->page;
            }
            if (in_array('per_page', array_keys($info))) {
                $c = $info['per_page'];
                $ret->perPage = $c > 0 ? $c : $ret->perPage;
            }
            return $ret;
        }catch (Exception $ex){
            throw new InvalidPaginationInfoException('ERR_PAGINATION');
        }
    }

    /**
     * @throws \Exception
     */
    public function modelClass(): string
    {
        throw new \Exception('No model class');
    }

    /**
     * Load data from request params
     * @param mixed $obj
     * @return void
     * @throws InvalidPaginationInfoException
     */
    public function load(mixed $obj): void
    {
        $data = PaginationInfo::parse($obj);
        $this->page = $data->page;
        $this->perPage = $data->perPage;
    }
}
