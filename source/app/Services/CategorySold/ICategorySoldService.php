<?php

namespace App\Services\CategorySold;

use App\Models\CategorySold;
use App\Services\IService;
use Illuminate\Support\Collection;

interface ICategorySoldService extends IService
{
    public function getAllCategorySolds(): Collection;
}
