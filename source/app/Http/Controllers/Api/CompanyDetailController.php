<?php

namespace App\Http\Controllers\Api;

use App\DataResources\CompanyDetail\CompanyDetailResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\CompanyDetail\CompanyDetailCreateRequest;
use App\Http\Requests\CompanyDetail\CompanyDetailSearchRequest;
use App\Http\Requests\CompanyDetail\CompanyDetailUpdateRequest;
use App\Services\CompanyDetail\ICompanyDetailService;
use App\Services\IService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CompanyDetailController extends ApiController
{
    use DefaultRestActions;

    private ICompanyDetailService $companyDetailService;

    public function __construct(ICompanyDetailService $service)
    {
        $this->companyDetailService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'company_details';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [CompanyDetailController::class, 'search']);
            Route::get($root . '/{id}', [CompanyDetailController::class, 'getSingleObject']);
            Route::post($root, [CompanyDetailController::class, 'create']);
            Route::put($root . '/{id}', [CompanyDetailController::class, 'update']);
            Route::delete($root . '/{id}', [CompanyDetailController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->companyDetailService;
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
        return CompanyDetailResource::class;
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
                $vRequest = CompanyDetailSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = CompanyDetailCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = CompanyDetailUpdateRequest::createFrom($request);
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
