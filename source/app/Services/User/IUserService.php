<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\IService;
use Illuminate\Support\Collection;

interface IUserService extends IService
{
    public function findByUsername($username): User | null;
    public function findByCompanies($user_id): mixed;
    public function changePassword(int $id, array $param): User | null;
    public function forgotPassword(string $email): mixed;//User | null
    public function resetPassword(array $param): User | null;
    public function getAllUsers(): Collection;
}
