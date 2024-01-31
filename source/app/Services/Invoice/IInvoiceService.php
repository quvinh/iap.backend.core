<?php

namespace App\Services\Invoice;

use App\Helpers\Common\MetaInfo;
use App\Models\Invoice;
use App\Services\IService;
use Illuminate\Database\Eloquent\Collection;

interface IInvoiceService extends IService
{
    public function storeEachRowInvoice(array $param, MetaInfo $commandMetaInfo = null): Invoice;

    public function import(array $param, MetaInfo $commandMetaInfo = null): mixed;
    public function restoreRowsInvoice(mixed $id, MetaInfo $commandMetaInfo = null): mixed;
    public function findPartnersByCompanyId(mixed $company_id, mixed $year): mixed;
    public function info(array $params): array;
    public function findNextInvoice(array $params): Invoice | Collection | null;
    public function reportSold(array $params): array | null;
}
