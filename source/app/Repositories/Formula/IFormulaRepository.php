<?php

namespace App\Repositories\Formula;

use App\Helpers\Common\MetaInfo;
use App\Models\Formula;
use App\Repositories\IRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface IFormulaRepository extends IRepository
{
    public function getSingleCategorySoldObject(mixed $idFor, mixed $idCat): Builder;
    public function createCategorySold(array $param): Model;
    public function deleteCategorySold(mixed $idCom, array $ids): bool;

    public function getSingleCategoryPurchaseObject(mixed $idFor, mixed $idCat): Builder;
    public function createCategoryPurchase(array $param): Model;
    public function updateCategoryPurchase(array $param): Model;
    public function deleteCategoryPurchase(mixed $idCom, array $ids): bool;
}
