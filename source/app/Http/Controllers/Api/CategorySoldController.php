<?php

namespace App\Http\Controllers\Api;

use App\DataResources\CategorySold\CategorySoldResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\CategorySold\CategorySoldCreateRequest;
use App\Http\Requests\CategorySold\CategorySoldSearchRequest;
use App\Http\Requests\CategorySold\CategorySoldUpdateRequest;
use App\Services\IService;
use App\Services\CategorySold\ICategorySoldService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CategorySoldController extends ApiController
{
    use DefaultRestActions;

    private ICategorySoldService $categorySoldService;

    public function __construct(ICategorySoldService $service)
    {
        $this->categorySoldService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'category_solds';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [CategorySoldController::class, 'search']);
            Route::get($root . '/{id}', [CategorySoldController::class, 'getSingleObject']);
            Route::post($root, [CategorySoldController::class, 'create']);
            Route::put($root . '/{id}', [CategorySoldController::class, 'update']);
            Route::delete($root . '/{id}', [CategorySoldController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->categorySoldService;
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
        return CategorySoldResource::class;
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
                $vRequest = CategorySoldSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = CategorySoldCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = CategorySoldUpdateRequest::createFrom($request);
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