<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

class CommandController extends Controller
{
    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'command';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/migrate', [CommandController::class, 'migrate']);
            Route::post($root . '/migrate/fresh', [CommandController::class, 'migrateFresh']);
            Route::post($root . '/backup', [CommandController::class, 'backup']);
            Route::post($root . '/config/clear', [CommandController::class, 'configClear']);
        }
    }

    public function migrate()
    {
        Artisan::call('migrate');
        return ApiResponse::v1()->send(['info' => 'Migrate executed successfully', 'output' => Artisan::output()]);
    }

    public function migrateFresh()
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
        return ApiResponse::v1()->send(['info' => 'Migrate:fresh executed successfully', 'output' => Artisan::output()]);
    }

    public function backup()
    {
        Artisan::call('backup:run');
        return ApiResponse::v1()->send(['info' => 'Backup executed', 'output' => Artisan::output()]);
    }

    public function configClear()
    {
        Artisan::call('config:clear');
        return ApiResponse::v1()->send(['info' => 'Config:clear executed successfully', 'output' => Artisan::output()]);
    }
}
