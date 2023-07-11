<?php

namespace App\Services\FirstAriseAccount;

use App\Models\FirstAriseAccount;
use App\Services\IService;
use Illuminate\Support\Collection;

interface IFirstAriseAccountService extends IService
{
    public function getAllAriseAccounts(): Collection;
}
