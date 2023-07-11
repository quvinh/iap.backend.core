<?php

namespace App\Repositories\FirstAriseAccount;

use App\Helpers\Common\MetaInfo;
use App\Models\FirstAriseAccount;
use App\Repositories\IRepository;
use Illuminate\Support\Collection;

interface IFirstAriseAccountRepository extends IRepository
{
    public function getAllAriseAccounts(): Collection;
}
