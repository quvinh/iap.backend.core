<?php

namespace App\Repositories\CategoryPurchase;

use App\Helpers\Common\MetaInfo;
use App\Models\CategoryPurchase;
use App\Repositories\IRepository;
use Illuminate\Support\Collection;

interface ICategoryPurchaseRepository extends IRepository
{
    public function getAllCategoryPurchases(): Collection;
}
