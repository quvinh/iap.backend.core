<?php

namespace App\Services\Permission;

use App\Models\Permission;
use App\Services\IService;

interface IPermissionService extends IService
{
    public function findBySlug($slug): Permission | null;
}
