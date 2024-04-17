<?php

namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;

class InvoiceProperties extends Enum
{
    const UNKNOWN = 0;
    const HOA_DON_MOI = 1;
    const HOA_DON_THAY_THE = 2;
    const HOA_DON_DIEU_CHINH = 3;
    const HOA_DON_DA_BI_THAY_THE = 4;
    const HOA_DON_DA_BI_DIEU_CHINH = 5;
    const HOA_DON_DA_BI_HUY = 6;
}
