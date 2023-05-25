<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\IService;

interface IUserService extends IService
{
    public function findByEmail($email): User | null;
    public function changePassword(int $id, array $param): User | null;
}
