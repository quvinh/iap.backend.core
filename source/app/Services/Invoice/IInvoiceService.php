<?php

namespace App\Services\Invoice;

use App\Helpers\Common\MetaInfo;
use App\Models\Invoice;
use App\Services\IService;

interface IInvoiceService extends IService
{
    public function storeEachRowInvoice(array $param, MetaInfo $commandMetaInfo = null): Invoice;
}
