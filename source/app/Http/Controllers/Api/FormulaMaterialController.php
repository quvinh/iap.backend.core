<?php

namespace App\Http\Controllers\Api;

use App\DataResources\FormulaMaterial\FormulaMaterialResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\FormulaMaterial\FormulaMaterialCreateRequest;
use App\Http\Requests\FormulaMaterial\FormulaMaterialSearchRequest;
use App\Http\Requests\FormulaMaterial\FormulaMaterialUpdateRequest;
use App\Services\IService;
use App\Services\FormulaMaterial\IFormulaMaterialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class FormulaMaterialController extends ApiController
{
    use DefaultRestActions;

    private IFormulaMaterialService $formulaMaterialService;

    public function __construct(IFormulaMaterialService $service)
    {
        $this->formulaMaterialService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'formula_materials';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [FormulaMaterialController::class, 'search']);
            Route::get($root . '/{id}', [FormulaMaterialController::class, 'getSingleObject']);
            Route::post($root, [FormulaMaterialController::class, 'create']);
            Route::put($root . '/{id}', [FormulaMaterialController::class, 'update']);
            Route::delete($root . '/{id}', [FormulaMaterialController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->formulaMaterialService;
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
        return FormulaMaterialResource::class;
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
                $vRequest = FormulaMaterialSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = FormulaMaterialCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = FormulaMaterialUpdateRequest::createFrom($request);
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
