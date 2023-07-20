<?php

namespace App\Repositories\CategorySold;

use App\Helpers\Common\MetaInfo;
use App\Models\CategorySold;
use App\Repositories\IRepository;
use Illuminate\Support\Collection;

interface ICategorySoldRepository extends IRepository
{
    public function getAllCategorySolds(): Collection;
}
