<?php

namespace App\Http\Controllers\Api;

use App\DataResources\FormulaCategorySold\FormulaCategorySoldResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Formula\FormulaCategorySoldCreateRequest;
use App\Http\Requests\Formula\FormulaCategorySoldSearchRequest;
use App\Http\Requests\Formula\FormulaCategorySoldUpdateRequest;
use App\Services\IService;
use App\Services\FormulaCategorySold\IFormulaCategorySoldService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class FormulaCategorySoldController extends ApiController
{
    use DefaultRestActions;

    private IFormulaCategorySoldService $formulaCategorySoldService;

    public function __construct(IFormulaCategorySoldService $service)
    {
        $this->formulaCategorySoldService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'formula-category-solds';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [FormulaCategorySoldController::class, 'search']);
            Route::get($root . '/{id}', [FormulaCategorySoldController::class, 'getSingleObject']);
            Route::post($root, [FormulaCategorySoldController::class, 'create']);
            Route::put($root . '/{id}', [FormulaCategorySoldController::class, 'update']);
            Route::delete($root . '/{id}', [FormulaCategorySoldController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->formulaCategorySoldService;
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
        return FormulaCategorySoldResource::class;
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
                $vRequest = FormulaCategorySoldSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = FormulaCategorySoldCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = FormulaCategorySoldUpdateRequest::createFrom($request);
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
