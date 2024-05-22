<?php

namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;

class CompanyPaymentStatus extends Enum
{
    const UNPAID = 'unpaid';
    const ADVANCE_MONEY = 'advance_money';
    const PAY_OFF = 'pay_off';
}
