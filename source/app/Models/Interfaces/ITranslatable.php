<?php
namespace App\Models\Interfaces;

use App\Helpers\Enums\SupportedLanguages;

interface ITranslatable{
    public function translate(string $lang): void;

    /**
     * @param string $lang
     * @return ILanguageResource
     */
    public function getLanguageResource(string $lang): mixed;
    /**
     * @return string[]
     */
    public function getTranslatableFields(): iterable;
}
