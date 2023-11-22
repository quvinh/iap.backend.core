<?php

namespace App\Repositories\OpeningBalanceVat;

use App\Helpers\Common\MetaInfo;
use App\Models\OpeningBalanceVat;
use App\Repositories\IRepository;

interface IOpeningBalanceVatRepository extends IRepository
{
    public function find(array $params): mixed;
}
