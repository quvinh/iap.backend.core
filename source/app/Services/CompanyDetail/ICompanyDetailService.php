<?php

namespace App\Services\CompanyDetail;

use App\Models\CompanyDetail;
use App\Services\IService;
use Illuminate\Database\Eloquent\Model;

interface ICompanyDetailService extends IService
{
    public function ariseAccount(array $param): Model;
}
