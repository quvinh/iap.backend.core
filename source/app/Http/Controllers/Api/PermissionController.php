<?php

namespace App\Http\Controllers\Api;

use App\DataResources\Permission\PermissionResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Permission\PermissionCreateRequest;
use App\Http\Requests\Permission\PermissionSearchRequest;
use App\Http\Requests\Permission\PermissionUpdateRequest;
use App\Services\IService;
use App\Services\Permission\IPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PermissionController extends ApiController
{
    use DefaultRestActions;

    private IPermissionService $permissionService;

    public function __construct(IPermissionService $service)
    {
        $this->permissionService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'permissions';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [PermissionController::class, 'search']);
            Route::get($root . '/{id}', [PermissionController::class, 'getSingleObject']);
            Route::post($root, [PermissionController::class, 'create']);
            Route::put($root . '/{id}', [PermissionController::class, 'update']);
            Route::delete($root . '/{id}', [PermissionController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->permissionService;
    }

    public function getRelatedFields(string $actionName): array
    {
        return [];
    }

    public function getCurrentMetaInfo(): MetaInfo
    {
        return $this->currentMetaInfo();
    }

    public function isTranslatable(): bool
    {
        return false;
    }

    public function getDataResourceClass(): string
    {
        return PermissionResource::class;
    }

    public function getDataResourceExtraFields(string $actionName): array
    {
        return [];
    }

    /**
     * @param Request $request
     * @param string $actionName
     * @return Request
     */
    public function validateRequest(Request $request, string $actionName): Request
    {
        switch ($actionName) {
            case 'search':
                $vRequest = PermissionSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = PermissionCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = PermissionUpdateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'getSingleObject':
                return $request;
            case 'delete':
                return $request;
            default:
                return $request;
        }
    }
}
