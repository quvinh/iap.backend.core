<?php

namespace App\Services\InvoiceDetail;

use App\Helpers\Common\MetaInfo;
use App\Models\InvoiceDetail;
use App\Services\IService;

interface IInvoiceDetailService extends IService
{
    public function updateProgressByFormula(array $param, MetaInfo $commandMetaInfo = null): bool;
}
