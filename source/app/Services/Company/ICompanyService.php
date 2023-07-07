<?php

namespace App\Services\Company;

use App\Models\Company;
use App\Services\IService;
use Illuminate\Support\Collection;

interface ICompanyService extends IService
{
    public function getAllCompanies(): Collection;
}
