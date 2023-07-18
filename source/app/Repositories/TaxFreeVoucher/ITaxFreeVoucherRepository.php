<?php

namespace App\Repositories\TaxFreeVoucher;

use App\Helpers\Common\MetaInfo;
use App\Models\TaxFreeVoucher;
use App\Repositories\IRepository;
use Illuminate\Support\Collection;

interface ITaxFreeVoucherRepository extends IRepository
{
    public function getAllTaxFreeVouchers(): Collection;
}
