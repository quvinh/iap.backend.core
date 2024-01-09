<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
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
            Route::post($root . '/cache/clear', [CommandController::class, 'cacheClear']);
            Route::post($root . '/user/fresh', [CommandController::class, 'userFresh']);
            Route::post($root . '/user/companies', [CommandController::class, 'userCompanies']);
        }
    }

    public function migrate()
    {
        Log::info('migrate');
        Artisan::call('migrate');
        return ApiResponse::v1()->send(['info' => 'Migrate executed successfully', 'output' => Artisan::output()]);
    }

    public function migrateFresh()
    {
        Log::info('migrate:fresh');
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
        return ApiResponse::v1()->send(['info' => 'Migrate:fresh executed successfully', 'output' => Artisan::output()]);
    }

    public function backup()
    {
        Log::info('backup:run');
        Artisan::call('backup:run');
        return ApiResponse::v1()->send(['info' => 'Backup executed', 'output' => Artisan::output()]);
    }

    public function cacheClear()
    {
        Log::info('cache:clear');
        Artisan::call('cache:clear');
        return ApiResponse::v1()->send(['info' => 'Cache:clear executed successfully', 'output' => Artisan::output()]);
    }

    public function userFresh()
    {
        Log::info('user:fresh');
        Artisan::call('user:fresh');
        return ApiResponse::v1()->send(['info' => 'User:fresh executed successfully', 'output' => Artisan::output()]);
    }

    public function userCompanies()
    {
        Log::info('user:companies');
        Artisan::call('user:companies');
        return ApiResponse::v1()->send(['info' => 'User:companies executed', 'output' => Artisan::output()]);
    }
}
