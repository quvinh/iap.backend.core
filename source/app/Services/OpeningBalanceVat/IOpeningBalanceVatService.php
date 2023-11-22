<?php

namespace App\Services\OpeningBalanceVat;

use App\Models\OpeningBalanceVat;
use App\Services\IService;

interface IOpeningBalanceVatService extends IService
{
    public function find(array $params): mixed;
}
