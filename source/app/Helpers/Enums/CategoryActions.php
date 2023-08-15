<?php

namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;

class CategoryActions extends Enum
{
    const PLUS = 'plus'; # plus total money
    const IGNORE = 'ignore'; # ignore total money and vat money (khuyen mai,...)
    const SUB = 'sub'; # subtract total money and vat money (ck/tm,...)
}
