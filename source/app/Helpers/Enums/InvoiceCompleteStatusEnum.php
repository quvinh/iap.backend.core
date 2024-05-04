<?php

namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;

/**
 * Invoice status from General Department of Taxation(Tong cuc thue)
 */
class InvoiceCompleteStatusEnum extends Enum
{
    const NGUYEN_BAN = 0;
    const DA_XU_LY = 1;
    const HOAN_THANH = 2;
}
