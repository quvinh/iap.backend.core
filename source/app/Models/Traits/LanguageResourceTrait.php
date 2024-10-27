<?php
namespace App\Models\Traits;

trait LanguageResourceTrait{

    /**
     * Replace translatable fields value with corresponding language resource
     * @param $entity
     * @param iterable $fields
     * @return void
     */
    function apply(&$entity, iterable $fields): void
    {
        foreach($fields as $f){
            if (array_key_exists($f, $this->attributes) && array_key_exists($f, $entity->attributes)){
                $entity->$f = $this->$f;
            }
        }
    }
}
