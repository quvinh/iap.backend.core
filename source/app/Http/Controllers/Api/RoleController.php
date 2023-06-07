<?php

namespace App\Http\Controllers\Api;

use App\DataResources\Role\RoleResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Role\RoleCreateRequest;
use App\Http\Requests\Role\RoleSearchRequest;
use App\Http\Requests\Role\RoleUpdateRequest;
use App\Services\IService;
use App\Services\Role\IRoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RoleController extends ApiController
{
    use DefaultRestActions;

    private IRoleService $roleService;

    public function __construct(IRoleService $service)
    {
        $this->roleService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'roles';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [RoleController::class, 'search']);
            Route::get($root . '/{id}', [RoleController::class, 'getSingleObject']);
            Route::post($root, [RoleController::class, 'create']);
            Route::put($root . '/{id}', [RoleController::class, 'update']);
            Route::delete($root . '/{id}', [RoleController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->roleService;
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
        return RoleResource::class;
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
                $vRequest = RoleSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = RoleCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = RoleUpdateRequest::createFrom($request);
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
