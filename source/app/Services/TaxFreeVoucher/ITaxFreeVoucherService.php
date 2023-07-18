<?php

namespace App\Services\TaxFreeVoucher;

use App\Models\TaxFreeVoucher;
use App\Services\IService;
use Illuminate\Support\Collection;

interface ITaxFreeVoucherService extends IService
{
    public function getAllTaxFreeVouchers(): Collection;
}
