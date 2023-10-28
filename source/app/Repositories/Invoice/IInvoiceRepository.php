<?php

namespace App\Repositories\Invoice;

use App\Helpers\Common\MetaInfo;
use App\Models\Invoice;
use App\Repositories\IRepository;

interface IInvoiceRepository extends IRepository
{
    public function deleteInvoiceDetails(mixed $idInvoice, array $ids): bool;
    public function findPartnersByCompanyId(mixed $company_id, mixed $year): mixed;
}
