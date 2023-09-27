<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\IService;

interface IUserService extends IService
{
    public function findByUsername($username): User | null;
    public function changePassword(int $id, array $param): User | null;
    public function forgotPassword(string $email): mixed;//User | null
}
