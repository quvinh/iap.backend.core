<?php

namespace App\Http\Controllers\Api;

use App\DataResources\CompanyType\CompanyTypeResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\CompanyType\CompanyTypeCreateRequest;
use App\Http\Requests\CompanyType\CompanyTypeSearchRequest;
use App\Http\Requests\CompanyType\CompanyTypeUpdateRequest;
use App\Services\IService;
use App\Services\CompanyType\ICompanyTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CompanyTypeController extends ApiController
{
    use DefaultRestActions;

    private ICompanyTypeService $companyTypeService;

    public function __construct(ICompanyTypeService $service)
    {
        $this->companyTypeService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'company-types';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::get($root . '/all', [CompanyTypeController::class, 'all']);
            Route::post($root . '/search', [CompanyTypeController::class, 'search']);
            Route::get($root . '/{id}', [CompanyTypeController::class, 'getSingleObject']);
            Route::post($root, [CompanyTypeController::class, 'create']);
            Route::put($root . '/{id}', [CompanyTypeController::class, 'update']);
            Route::delete($root . '/{id}', [CompanyTypeController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->companyTypeService;
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
        return CompanyTypeResource::class;
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
                $vRequest = CompanyTypeSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = CompanyTypeCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = CompanyTypeUpdateRequest::createFrom($request);
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

    /**
     * Get all company types
     */
    public function all()
    {
        $result = $this->companyTypeService->getAllCompanyTypes();
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }
}
