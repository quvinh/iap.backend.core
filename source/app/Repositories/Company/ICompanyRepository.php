<?php

namespace App\Repositories\Company;

use App\Helpers\Common\MetaInfo;
use App\Models\Company;
use App\Repositories\IRepository;
use Illuminate\Support\Collection;

interface ICompanyRepository extends IRepository
{
    public function getAllCompanies(): Collection;
    public function inventory(mixed $company_id, string $start, string $end): array;
}
