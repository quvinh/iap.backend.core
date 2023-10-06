<?php

namespace App\Services\Invoice;

use App\Helpers\Common\MetaInfo;
use App\Models\Invoice;
use App\Services\IService;

interface IInvoiceService extends IService
{
    public function storeEachRowInvoice(array $param, MetaInfo $commandMetaInfo = null): Invoice;

    public function import(array $param, MetaInfo $commandMetaInfo = null): array;
    public function restoreRowsInvoice(mixed $id, MetaInfo $commandMetaInfo = null): mixed;
}
