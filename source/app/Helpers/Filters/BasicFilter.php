<?php
namespace App\Helpers\Filters;

class BasicFilter
{

    public mixed $conditions;

    public mixed $orders;

    public mixed $limit;

    public mixed $skip;

    public mixed $detail;

    public function __construct(){
        $this->conditions = [];
        $this->skip = 0;
        $this->limit = config('app.setting.pagination.per_page');
        $this->orders = [];
        $this->detail = [];
    }

    public function addConditions(string $field, string $operand, mixed $value): BasicFilter
    {
        $this->conditions[$field] = [$field, $operand, $value];
        return $this;
    }

    /**
     * @param mixed $value
     *  $value : {
     *  conditions: [[field, operator, value]] or [field: value],
     *  orders: [field: sort_type],
     *  skip: int
     *  limit: int
     *  detail: true | [relation_name]
     * }
     * @param mixed|null $attrs
     * @param mixed $withes
     * @return BasicFilter
     */
    public static function parse(mixed $value, mixed $attrs = null, mixed $withes=[]): BasicFilter
    {
        $data = $value;
        if (is_string($data))
            $data = json_decode($data);
        if (is_object($data))
            $data = (array)$data;
        $ret = new BasicFilter();
        if (is_array($data)) {
            if (array_key_exists('skip', $data)) {
                $val = intval("0" . $data['skip']);
                $ret->skip = $val >= 0 ? $val : null;
            }
            if (array_key_exists('limit', $data)) {
                $val = intval("0" . $data['limit']);
                $ret->limit = $val > 0 ? $val : null;
            }

            $ret->orders = BasicFilter::parseOrders($data, $attrs);
            $ret->conditions = BasicFilter::parseConditions($data, $attrs);
            $ret->detail = BasicFilter::parseWithes($data, $withes);
        }
        return $ret;
    }

    /**
     * @param mixed $data
     * @param mixed $attrs
     * @return array<array<string>>
     */
    private static function parseOrders(mixed $data, mixed $attrs = []): array{
        $ret = [];
        if (array_key_exists('orders', $data)) {
            $val = $data['orders'];
            if (is_array($val))
                foreach ($val as $key => $value) {
                    if (($attrs && in_array($key, $attrs)) || $attrs === null || count($attrs) === 0) {
                        $kind = ($value === 'asc' || $value === 'desc') ? $value : 'asc';
                        $kind = ($value === 'asc') ? 'ASC' : 'DESC';
                        $ret[] = [$key, $kind];
                    }
                }
        }
        return $ret;
    }

    /**
     * @param mixed $data
     * @param mixed $attrs
     * @return array<array<string>>
     */
    private static function parseConditions(mixed $data, mixed $attrs=[]): array{
        $ret = [];
        if (array_key_exists('conditions', $data)) {
            $val = $data['conditions'];
            if (is_array($val)) {
                foreach ($val as $key => $value) {
                    if (($attrs && in_array($key, $attrs)) || $attrs === null || count($attrs) === 0) {
                        if (is_array($value) && count($value) == 2) {
                            $ret[] = [
                                $value[0],
                                $value[1],
                                $value[2]
                            ];
                        } else {
                            $ret[] = [
                                $key,
                                "=",
                                $value
                            ];
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * @param mixed $data
     * @param mixed $withes
     * @return array<string>
     */
    private static function parseWithes(mixed $data, mixed $withes=[]): array{
        $ret = [];
        if (array_key_exists('detail', $data)) {
            $detail = ($data['detail'] === true) ? ['*'] : $data['detail'];
            $detail = is_array($detail)? $detail: [];
            if (is_array($detail)){
                foreach($detail as $key){
                    if (($withes && in_array($key, $withes)) || $withes === null || count($withes) === 0 || $key == "*") {
                        $ret[] = $key;
                    }
                }
            }
        }
        return $ret;
    }
}
