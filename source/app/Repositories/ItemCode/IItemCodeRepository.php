<?php

namespace App\Repositories\ItemCode;

use App\Helpers\Common\MetaInfo;
use App\Models\ItemCode;
use App\Repositories\IRepository;
use Illuminate\Database\Eloquent\Builder;

interface IItemCodeRepository extends IRepository
{
    function findByGroup($group): Builder | null;
    function getAll(array $params): Builder | null;
}
