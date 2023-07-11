<?php

namespace App\Http\Controllers\Api;

use App\DataResources\FormulaCategoryPurchase\FormulaCategoryPurchaseResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Formula\FormulaCategoryPurchaseCreateRequest;
use App\Http\Requests\Formula\FormulaCategoryPurchaseSearchRequest;
use App\Http\Requests\Formula\FormulaCategoryPurchaseUpdateRequest;
use App\Services\IService;
use App\Services\FormulaCategoryPurchase\IFormulaCategoryPurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class FormulaCategoryPurchaseController extends ApiController
{
    use DefaultRestActions;

    private IFormulaCategoryPurchaseService $formulaCategoryPurchaseService;

    public function __construct(IFormulaCategoryPurchaseService $service)
    {
        $this->formulaCategoryPurchaseService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'formula-category-purchases';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [FormulaCategoryPurchaseController::class, 'search']);
            Route::get($root . '/{id}', [FormulaCategoryPurchaseController::class, 'getSingleObject']);
            Route::post($root, [FormulaCategoryPurchaseController::class, 'create']);
            Route::put($root . '/{id}', [FormulaCategoryPurchaseController::class, 'update']);
            Route::delete($root . '/{id}', [FormulaCategoryPurchaseController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->formulaCategoryPurchaseService;
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
        return FormulaCategoryPurchaseResource::class;
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
                $vRequest = FormulaCategoryPurchaseSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = FormulaCategoryPurchaseCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = FormulaCategoryPurchaseUpdateRequest::createFrom($request);
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
