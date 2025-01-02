<?php

namespace App\Services\CompanyDetail;

use App\Helpers\Common\MetaInfo;
use App\Models\CompanyDetail;
use App\Services\IService;
use Illuminate\Database\Eloquent\Model;

interface ICompanyDetailService extends IService
{
    public function createAriseAccount(array $param): Model;
    public function updateAriseAccount(mixed $id, array $param): Model;
    // public function deleteAriseAccount(mixed $id): bool;
    public function updateProperties(mixed $id, array $param, MetaInfo $commandMetaInfo = null): CompanyDetail;
    public function clone(array $param): mixed;
}
