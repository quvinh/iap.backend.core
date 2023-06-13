<?php

namespace App\Services;

use App\Services\Auth\AuthService;
use App\Services\Auth\IAuthService;
use App\Services\Company\CompanyService;
use App\Services\Company\ICompanyService;
use App\Services\CompanyDetail\CompanyDetailService;
use App\Services\CompanyDetail\ICompanyDetailService;
use App\Services\CompanyType\CompanyTypeService;
use App\Services\CompanyType\ICompanyTypeService;
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
        $app->singleton(ICompanyService::class, CompanyService::class);
        $app->singleton(ICompanyDetailService::class, CompanyDetailService::class);
        $app->singleton(ICompanyTypeService::class, CompanyTypeService::class);
    }
}
