<?php

namespace App\Repositories\Permission;

use App\Helpers\Common\MetaInfo;
use App\Models\Permission;
use App\Repositories\IRepository;

interface IPermissionRepository extends IRepository
{
    public function findBySlug($slug): Permission | null;
}
