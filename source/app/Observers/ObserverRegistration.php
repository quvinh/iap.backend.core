<?php

namespace App\Observers;

use App\Models\User;

class ObserverRegistration
{
    public static function register(\Illuminate\contracts\Foundation\Application $app)
    {
        // User::observer(UserObserver::class);
    }
}
