<?php

namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;

class InvoiceTypes extends Enum
{
    const SOLD = 'sold';
    const PURCHASE = 'purchase';
}
