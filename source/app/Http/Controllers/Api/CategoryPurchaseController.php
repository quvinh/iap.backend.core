<?php

namespace App\Http\Controllers\Api;

use App\DataResources\CategoryPurchase\CategoryPurchaseResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\CategoryPurchase\CategoryPurchaseCreateRequest;
use App\Http\Requests\CategoryPurchase\CategoryPurchaseSearchRequest;
use App\Http\Requests\CategoryPurchase\CategoryPurchaseUpdateRequest;
use App\Services\IService;
use App\Services\CategoryPurchase\ICategoryPurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CategoryPurchaseController extends ApiController
{
    use DefaultRestActions;

    private ICategoryPurchaseService $categoryPurchaseService;

    public function __construct(ICategoryPurchaseService $service)
    {
        $this->categoryPurchaseService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'category_purchases';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [CategoryPurchaseController::class, 'search']);
            Route::get($root . '/{id}', [CategoryPurchaseController::class, 'getSingleObject']);
            Route::post($root, [CategoryPurchaseController::class, 'create']);
            Route::put($root . '/{id}', [CategoryPurchaseController::class, 'update']);
            Route::delete($root . '/{id}', [CategoryPurchaseController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->categoryPurchaseService;
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
        return CategoryPurchaseResource::class;
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
                $vRequest = CategoryPurchaseSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = CategoryPurchaseCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = CategoryPurchaseUpdateRequest::createFrom($request);
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
