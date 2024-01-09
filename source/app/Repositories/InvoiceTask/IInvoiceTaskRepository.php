<?php

namespace App\Repositories\InvoiceTask;

use App\Helpers\Common\MetaInfo;
use App\Models\InvoiceTask;
use App\Repositories\IRepository;
use Illuminate\Support\Collection;

interface IInvoiceTaskRepository extends IRepository
{
    public function getMoneyOfMonths(int $company_id, int $year): array;
    public function getTaskNotProcess(): Collection;
    public function monthlyTask(): array;
    public function forceDeleteInvoiceWithTask(int $task_id, string $invoice_type): bool;
}
