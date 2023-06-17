<?php

namespace App\Http\Controllers\Api;

use App\DataResources\Formula\FormulaResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Formula\FormulaCreateRequest;
use App\Http\Requests\Formula\FormulaSearchRequest;
use App\Http\Requests\Formula\FormulaUpdateRequest;
use App\Services\IService;
use App\Services\Formula\IFormulaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class FormulaController extends ApiController
{
    use DefaultRestActions;

    private IFormulaService $formulaService;

    public function __construct(IFormulaService $service)
    {
        $this->formulaService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'formulas';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [FormulaController::class, 'search']);
            Route::get($root . '/{id}', [FormulaController::class, 'getSingleObject']);
            Route::post($root, [FormulaController::class, 'create']);
            Route::put($root . '/{id}', [FormulaController::class, 'update']);
            Route::delete($root . '/{id}', [FormulaController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->formulaService;
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
        return FormulaResource::class;
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
                $vRequest = FormulaSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = FormulaCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = FormulaUpdateRequest::createFrom($request);
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
