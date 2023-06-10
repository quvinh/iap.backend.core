<?php

namespace App\Repositories;

use App\Repositories\Company\CompanyRepository;
use App\Repositories\Company\ICompanyRepository;
use App\Repositories\CompanyDetail\CompanyDetailRepository;
use App\Repositories\CompanyDetail\ICompanyDetailRepository;
use App\Repositories\CompanyType\CompanyTypeRepository;
use App\Repositories\CompanyType\ICompanyTypeRepository;
use App\Repositories\Permission\IPermissionRepository;
use App\Repositories\Permission\PermissionRepository;
use App\Repositories\Role\IRoleRepository;
use App\Repositories\Role\RoleRepository;
use App\Repositories\User\IUserRepository;
use App\Repositories\User\UserRepository;

class RepositoryRegistration
{
    /**
     * Register injectable instance
     */

    public static function register(\Illuminate\Contracts\Foundation\Application $app): void
    {
        $app->singleton(IUserRepository::class, UserRepository::class);
        $app->singleton(IRoleRepository::class, RoleRepository::class);
        $app->singleton(IPermissionRepository::class, PermissionRepository::class);
        $app->singleton(ICompanyRepository::class, CompanyRepository::class);
        $app->singleton(ICompanyDetailRepository::class, CompanyDetailRepository::class);
        $app->singleton(ICompanyTypeRepository::class, CompanyTypeRepository::class);
    }
}
