<?php

namespace App\Repositories\InvoiceTask;

use App\Helpers\Common\MetaInfo;
use App\Models\InvoiceTask;
use App\Repositories\IRepository;
use Illuminate\Support\Collection;

interface IInvoiceTaskRepository extends IRepository
{
    function getMoneyOfMonths(int $company_id, int $year): array;
    function getTaskNotProcess(): Collection;
    function monthlyTask(): array;
    function monthlyInvoice(): array;
    function invoiceMediaNotCompleted(): int;
    function forceDeleteInvoiceWithTask(int $task_id, string $invoice_type): bool;
}
