<?php

namespace App\Services\ItemGroup;

use App\Models\ItemGroup;
use App\Services\IService;

interface IItemGroupService extends IService
{
    public function insert(array $param): mixed;
}
