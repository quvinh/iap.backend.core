<?php

namespace App\Services\Formula;

use App\Helpers\Common\MetaInfo;
use App\Models\Formula;
use App\Services\IService;

interface IFormulaService extends IService
{
    public function updateDetail(mixed $id, array $param, MetaInfo $commandMetaInfo = null): Formula;
}
