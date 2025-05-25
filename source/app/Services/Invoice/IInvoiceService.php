<?php

namespace App\Services\Invoice;

use App\Helpers\Common\MetaInfo;
use App\Models\Invoice;
use App\Services\IService;
use Illuminate\Database\Eloquent\Collection;

interface IInvoiceService extends IService
{
    function storeEachRowInvoice(array $param, MetaInfo $commandMetaInfo = null): Invoice;

    function import(array $param, MetaInfo $commandMetaInfo = null): mixed;
    function restoreRowsInvoice(mixed $id, MetaInfo $commandMetaInfo = null): mixed;
    function findPartnersByCompanyId(mixed $company_id, mixed $year): mixed;
    function info(array $params): array;
    function findNextInvoice(array $params): Invoice | Collection | null;
    function reportSold(array $params): Collection | Invoice | array | null;
    function createInvoiceTct(array $param): Invoice;
    function saveInvoiceTct(array $param): mixed;
}
