<?php

namespace App\Repositories\User;

use App\Helpers\Common\MetaInfo;
use App\Models\User;
use App\Repositories\IRepository;

interface IUserRepository extends IRepository
{
    public function findByUsername($username): User | null;
    public function changePassword(array $form, ?MetaInfo $meta = null, string $idColumnName = 'id'): mixed;
}
