<?php

namespace App\Repositories\Invoice;

use App\Helpers\Common\MetaInfo;
use App\Models\Invoice;
use App\Repositories\IRepository;
use Illuminate\Database\Eloquent\Collection;

interface IInvoiceRepository extends IRepository
{
    public function deleteInvoiceDetails(mixed $idInvoice, array $ids): bool;
    public function findPartnersByCompanyId(mixed $company_id, mixed $year): mixed;
    public function info(array $params): array;
    public function findNextInvoice(array $params): Invoice | Collection | null;
    public function reportSold(array $params): Collection | Invoice | array | null;
}
