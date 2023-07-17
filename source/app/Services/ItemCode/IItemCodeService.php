<?php

namespace App\Services\ItemCode;

use App\Helpers\Common\MetaInfo;
use App\Models\ItemCode;
use App\Services\IService;

interface IItemCodeService extends IService
{
    public function import(array $param, MetaInfo $commandMetaInfo = null): array;
}
