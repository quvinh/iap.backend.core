<?php

namespace App\Repositories\User;

use App\Helpers\Common\MetaInfo;
use App\Models\User;
use App\Repositories\IRepository;

interface IUserRepository extends IRepository
{
    public function findByUsername($username): User | null;
    public function findByEmail($email): User | null;
    public function findByCompanies($user_id): mixed;
    public function changePassword(array $form, ?MetaInfo $meta = null, string $idColumnName = 'id'): User | null;
}
