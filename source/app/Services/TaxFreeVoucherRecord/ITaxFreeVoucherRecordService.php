<?php

namespace App\Services\TaxFreeVoucherRecord;

use App\Models\TaxFreeVoucherRecord;
use App\Services\IService;

interface ITaxFreeVoucherRecordService extends IService
{
    public function find(array $params): mixed;
}
