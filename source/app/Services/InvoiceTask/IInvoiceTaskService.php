<?php

namespace App\Services\InvoiceTask;

use App\Models\InvoiceTask;
use App\Services\IService;
use Illuminate\Support\Collection;

interface IInvoiceTaskService extends IService
{
    function updateHandleFormula(array $params): mixed;
    function getMoneyOfMonths(array $params): mixed;
    function getTaskNotProcess(): Collection;
    function monthlyTask(): array;
    function monthlyInvoice(): array;
    function invoiceMediaNotCompleted(): int;
    function forceDeleteInvoiceWithTask(array $params): mixed;
}
