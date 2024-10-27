<?php
namespace App\Models\Traits;

use App\Helpers\Enums\SupportedLanguages;

trait TranslatableTrait{

    /**
     * Convert translatable field to correct language if the value is available
     * @param string $lang
     * @return void
     */
    public function translate(string $lang): void
    {
        if ($lang == SupportedLanguages::DEFAULT_LOCALE) return;
        $languageResource = $this->getLanguageResource($lang);
        $languageResource?->apply($this, $this->getTranslatableFields());
    }
}
