<?php

namespace App\Repositories\CompanyType;

use App\Helpers\Common\MetaInfo;
use App\Models\CompanyType;
use App\Repositories\IRepository;
use Illuminate\Support\Collection;

interface ICompanyTypeRepository extends IRepository
{
    public function getAllCompanyTypes(): Collection;
}
