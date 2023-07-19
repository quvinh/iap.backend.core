<?php

namespace App\Repositories\CompanyDetail;

use App\Helpers\Common\MetaInfo;
use App\Models\CompanyDetail;
use App\Repositories\IRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface ICompanyDetailRepository extends IRepository
{
    public function getSinglePropertyObject(mixed $idCom, mixed $idAcc): Builder;
    public function createAriseAccount(array $param): Model;
    public function updateAriseAccount(array $param): Model;
    public function deleteAriseAccount(mixed $idCom, array $ids): bool;

    public function getSingleVoucherPropertyObject(mixed $idCom, mixed $idTax): Builder;
    public function createTaxFreeVoucher(array $param): Model;
    public function deleteTaxFreeVoucher(mixed $idCom, array $ids): bool;
}
