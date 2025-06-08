<?php

namespace App\Repositories\Invoice;

use App\Helpers\Common\MetaInfo;
use App\Models\Invoice;
use App\Repositories\IRepository;
use Illuminate\Database\Eloquent\Collection;

interface IInvoiceRepository extends IRepository
{
    function deleteInvoiceDetails(mixed $idInvoice, array $ids): bool;
    function findPartnersByCompanyId(mixed $company_id, mixed $year): mixed;
    function info(array $params): array;
    function findNextInvoice(array $params): Invoice | Collection | null;
    function reportSold(array $params): Collection | Invoice | array | null;
    function createInvoiceTct(array $param): Invoice;
    function createInvoicesTct(array $param): mixed;
    function saveInvoiceTct(array $param): mixed;
    function checkInvoiceExist(array $param): mixed;
}
