<?php

namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;

class InvoiceNumberForms extends Enum
{
    const VALUE_ADDED = 1; # hoa don GTGT
    const SALES = 2; # hoa don ban hang
    const THE_SALE_OF_PUBLIC_PROPERTY = 3; # hoa don ban tai san cong
    const NATIONAL_RESERVE_SALES = 4; # hoa don ban hang du tru quoc gia
    const E_TICKETS = 5; # cac loai hoa don: tem ve dien tu
    const WAREHOUSE_RELEASE = 6; # phieu xuat kho kiem van chuyen noi bo
}
