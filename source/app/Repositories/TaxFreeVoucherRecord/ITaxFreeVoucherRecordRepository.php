<?php

namespace App\Repositories\TaxFreeVoucherRecord;

use App\Helpers\Common\MetaInfo;
use App\Models\TaxFreeVoucherRecord;
use App\Repositories\IRepository;

interface ITaxFreeVoucherRecordRepository extends IRepository
{
    public function find(array $params): mixed;
}
