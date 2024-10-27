<?php
namespace App\Models\Interfaces;

interface ILanguageResource{
    function apply(ITranslatable &$entity, iterable $fields): void;
}
