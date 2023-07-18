<?php

namespace App\Services\CompanyType;

use App\Models\CompanyType;
use App\Services\IService;
use Illuminate\Support\Collection;

interface ICompanyTypeService extends IService
{
    public function getAllCompanyTypes(): Collection;
}
