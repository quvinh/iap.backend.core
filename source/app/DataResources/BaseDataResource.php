<?php
namespace App\DataResources;

use App\Exceptions\Business\InvalidModelInstanceException;
use App\Helpers\Enums\ErrorCodes;
use App\Models\BaseModel;
use Carbon\Carbon;
use stdClass;

abstract class BaseDataResource implements IDataResource{
    public abstract function modelClass(): string;
    /**
     * @var array| string[]
     */
    protected array $fields = [];

    /**
     * @param mixed $obj
     * @param array $extraFields
     * @throws InvalidModelInstanceException
     */
    public function __construct(mixed $obj = null, array $extraFields = []){
        if (is_null($obj)) return;

        # 1. check model class
        $obj_class = get_class($obj);
        $model_class = $this->modelClass();
        if ($model_class != $obj_class)
            throw new InvalidModelInstanceException( ErrorCodes::ERR_INVALID_SETTING,"Expected type $model_class but found $obj_class");

        # 2. copy attributes from model to resource for output
        $this->withFields($extraFields);
        $this->load($obj);
    }

    /**
     * Dynamic load attributes
     * @param BaseModel $obj
     * @return void
     */
    public abstract function load(mixed $obj): void;
    /**
     * @param mixed $obj
     * @param string[] $props
     * @return void
     */
    protected function copy(mixed $obj, array $props=[], bool $allowNull = false): void{
        if (!isset($obj)) return;
        if ($obj instanceof stdClass){
            $obj = (array) $obj;
        }

        if (is_array($obj)){
            $attributes = array_keys($obj);
        }
        else {
            $attributes = array_keys($obj->getAttributes());
        }

        $props = (count($props) == 0)? $this->fields : $props;
        foreach ($props as $key){
            if (in_array($key, $attributes)){
                if (is_null($obj[$key]) && $allowNull)
                    $this->$key = null;
                else
                    $this->$key = $obj[$key];
            } else {
                if ($allowNull) $this->$key = null;
            }
        }
    }

    /**
     * Dynamical register extra field
     * @param string $fieldName
     * @return void
     */
    public function withField(string $fieldName): void
    {
        if (!in_array($fieldName, $this->fields))
            $this->fields[] = $fieldName;
    }

    /**
     * Dynamical register extra fields
     * @param array<string> $fields
     * @return void
     */
    public function withFields(array $fields): void
    {
        foreach ($fields as $field)
            $this->withField($field);
    }

    /**
     * @return array
     */
    public function toArray(bool $allowNull = false): array
    {
        $result = [];
        foreach ($this->fields as $key)
        {
            if (property_exists($this, $key)){
                if (! is_null($this->$key)) {
                    if ($this->$key instanceof Carbon)
                        $result[$key] = $this->$key;
                    elseif (is_array($this->$key) || is_object($this->$key))
                        $result[$key] = BaseDataResource::objectToArray($this->$key);
                    else
                        $result[$key] = $this->$key;
                    continue;
                }
            }
            if ($allowNull) $result[$key] = null;
        }
        return $result;
    }

    /**
     * Convert list of model into resources
     * @param mixed $items
     * @param string $class_name
     * @param array<string> $extraFields
     * @return array
     */
    public static function generateResources(mixed $items, string $class_name, array $extraFields = []): array
    {
        $ret = [];
        foreach ($items as $item) {
            $obj = new $class_name($item, $extraFields);
            $ret[] = $obj;
        }
        return $ret;
    }

    /**
     * Convert resource items into array
     * @param mixed $o
     * @return mixed
     */
    public static function objectToArray(mixed $o): mixed{
        if (!is_array($o) && !($o instanceof BaseDataResource)) return $o;
        if ($o instanceof BaseDataResource) return $o->toArray();
        $a = array();
        foreach ($o as $k => $v) {
            $ret = null;
            if ($v instanceof BaseDataResource)
                $ret = $v->toArray();
            else $ret = is_array($v) ? BaseDataResource::objectToArray($v) : $v;

            $a[$k] = $ret;
        }
        return $a;
    }
}
