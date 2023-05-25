<?php

namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;

class SupportedLanguages extends Enum
{
    const VIETNAMESE = 'vi';
    const ENGLISH = 'en';
    const CHINESE = 'cn';

    const DEFAULT_LOCALE = 'vi';
}
