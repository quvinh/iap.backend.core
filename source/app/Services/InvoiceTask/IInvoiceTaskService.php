<?php

namespace App\Services\InvoiceTask;

use App\Models\InvoiceTask;
use App\Services\IService;

interface IInvoiceTaskService extends IService
{
    public function updateHandleFormula(array $params): mixed;
}
