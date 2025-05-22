<?php

namespace App\Services\ItemCode;

use App\Helpers\Common\MetaInfo;
use App\Models\ItemCode;
use App\Services\IService;
use Illuminate\Database\Eloquent\Collection;

interface IItemCodeService extends IService
{
    function import(array $param, MetaInfo $commandMetaInfo = null): array;
    function getAll(array $param): Collection;
    function autoFill(array $param): mixed;
    function saveAutoFill(array $param): mixed;
}
