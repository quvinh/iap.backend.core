<?php

namespace App\Services;

use App\Services\Auth\AuthService;
use App\Services\Auth\IAuthService;
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
    }
}
