<?php

namespace App\Repositories\InvoiceTask;

use App\Helpers\Common\MetaInfo;
use App\Models\InvoiceTask;
use App\Repositories\IRepository;

interface IInvoiceTaskRepository extends IRepository
{
    function getMoneyOfMonths(int $company_id, int $year): array;
}
