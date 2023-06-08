<?php

namespace App\Services;

use App\Services\Auth\AuthService;
use App\Services\Auth\IAuthService;
use App\Services\Permission\IPermissionService;
use App\Services\Permission\PermissionService;
use App\Services\Role\IRoleService;
use App\Services\Role\RoleService;
use App\Services\User\IUserService;
use App\Services\User\UserService;

class ServiceRegistration
{
    /**
     * Register injectable instance
     */
    public static function register(\Illuminate\Contracts\Foundation\Application $app): void
    {
        $app->singleton(IAuthService::class, AuthService::class);

        $app->singleton(IUserService::class, UserService::class);
        $app->singleton(IRoleService::class, RoleService::class);
        $app->singleton(IPermissionService::class, PermissionService::class);
    }
}
