<?php

namespace App\Repositories\ItemCode;

use App\Helpers\Common\MetaInfo;
use App\Models\ItemCode;
use App\Repositories\IRepository;
use Illuminate\Database\Eloquent\Builder;

interface IItemCodeRepository extends IRepository
{
    public function findByGroup($group): Builder | null;
}
