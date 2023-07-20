<?php

namespace App\Services\CategoryPurchase;

use App\Models\CategoryPurchase;
use App\Services\IService;
use Illuminate\Support\Collection;

interface ICategoryPurchaseService extends IService
{
    public function getAllCategoryPurchases(): Collection;
}
