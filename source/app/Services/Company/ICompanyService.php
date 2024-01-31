<?php

namespace App\Services\Company;

use App\Models\Company;
use App\Services\IService;
use Illuminate\Support\Collection;

interface ICompanyService extends IService
{
    public function getAllCompanies(): Collection;
    public function inventory(mixed $company_id, string $start, string $end): array;
}
