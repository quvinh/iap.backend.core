<?php

namespace App\Http\Controllers\Api;

use App\DataResources\Company\CompanyResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Company\CompanyCreateRequest;
use App\Http\Requests\Company\CompanySearchRequest;
use App\Http\Requests\Company\CompanyUpdateRequest;
use App\Services\IService;
use App\Services\Company\ICompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CompanyController extends ApiController
{
    use DefaultRestActions;

    private ICompanyService $companyService;

    public function __construct(ICompanyService $service)
    {
        $this->companyService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'companies';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [CompanyController::class, 'search']);
            Route::get($root . '/{id}', [CompanyController::class, 'getSingleObject']);
            Route::post($root, [CompanyController::class, 'create']);
            Route::put($root . '/{id}', [CompanyController::class, 'update']);
            Route::delete($root . '/{id}', [CompanyController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->companyService;
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
        return CompanyResource::class;
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
                $vRequest = CompanySearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = CompanyCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = CompanyUpdateRequest::createFrom($request);
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
