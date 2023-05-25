<?php

namespace App\Repositories;

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
    }
}
