<?php

namespace App\Http\Controllers\Api;

use App\DataResources\FormulaCommodity\FormulaCommodityResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\FormulaCommodity\FormulaCommodityCreateRequest;
use App\Http\Requests\FormulaCommodity\FormulaCommoditySearchRequest;
use App\Http\Requests\FormulaCommodity\FormulaCommodityUpdateRequest;
use App\Services\IService;
use App\Services\FormulaCommodity\IFormulaCommodityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class FormulaCommodityController extends ApiController
{
    use DefaultRestActions;

    private IFormulaCommodityService $formulaCommodityService;

    public function __construct(IFormulaCommodityService $service)
    {
        $this->formulaCommodityService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'formula-commodities';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [FormulaCommodityController::class, 'search']);
            Route::get($root . '/{id}', [FormulaCommodityController::class, 'getSingleObject']);
            Route::post($root, [FormulaCommodityController::class, 'create']);
            Route::put($root . '/{id}', [FormulaCommodityController::class, 'update']);
            Route::delete($root . '/{id}', [FormulaCommodityController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->formulaCommodityService;
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
        return FormulaCommodityResource::class;
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
                $vRequest = FormulaCommoditySearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = FormulaCommodityCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = FormulaCommodityUpdateRequest::createFrom($request);
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
