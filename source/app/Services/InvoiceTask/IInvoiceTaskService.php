<?php

namespace App\Services\InvoiceTask;

use App\Models\InvoiceTask;
use App\Services\IService;
use Illuminate\Support\Collection;

interface IInvoiceTaskService extends IService
{
    public function updateHandleFormula(array $params): mixed;
    public function getMoneyOfMonths(array $params): mixed;
    public function getTaskNotProcess(): Collection;
    public function monthlyTask(): array;
    public function forceDeleteInvoiceWithTask(array $params): mixed;
}
