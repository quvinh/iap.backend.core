<?php

namespace App\Services\CompanyDetail;

use App\Models\CompanyDetail;
use App\Services\IService;
use Illuminate\Database\Eloquent\Model;

interface ICompanyDetailService extends IService
{
    public function createAriseAccount(array $param): Model;
    public function updateAriseAccount(mixed $id, array $param): Model;
    public function deleteAriseAccount(mixed $id): bool;
}
