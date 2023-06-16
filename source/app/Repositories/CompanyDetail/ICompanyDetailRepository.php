<?php

namespace App\Repositories\CompanyDetail;

use App\Helpers\Common\MetaInfo;
use App\Models\CompanyDetail;
use App\Repositories\IRepository;
use Illuminate\Database\Eloquent\Model;

interface ICompanyDetailRepository extends IRepository
{
    public function ariseAccount(array $param): Model;
}
