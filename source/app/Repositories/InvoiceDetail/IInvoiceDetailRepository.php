<?php

namespace App\Repositories\InvoiceDetail;

use App\Helpers\Common\MetaInfo;
use App\Models\InvoiceDetail;
use App\Repositories\IRepository;

interface IInvoiceDetailRepository extends IRepository
{
    public function updateProgressByFormula(array $form, MetaInfo $meta = null): bool;
}
