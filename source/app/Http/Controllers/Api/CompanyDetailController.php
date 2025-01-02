<?php

namespace App\Http\Controllers\Api;

use App\DataResources\CompanyDetail\CompanyDetailResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\CompanyDetail\AriseAccountCreateRequest;
use App\Http\Requests\CompanyDetail\AriseAccountUpdateRequest;
use App\Http\Requests\CompanyDetail\CompanyDetailCreateRequest;
use App\Http\Requests\CompanyDetail\CompanyDetailPropertyUpdateRequest;
use App\Http\Requests\CompanyDetail\CompanyDetailSearchRequest;
use App\Http\Requests\CompanyDetail\CompanyDetailUpdateRequest;
use App\Services\CompanyDetail\ICompanyDetailService;
use App\Services\IService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $root = 'company-details';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [CompanyDetailController::class, 'search']);
            Route::get($root . '/{id}', [CompanyDetailController::class, 'getSingleObject']);
            Route::post($root, [CompanyDetailController::class, 'create']);            
            Route::put($root . '/{id}', [CompanyDetailController::class, 'update']);
            Route::delete($root . '/{id}', [CompanyDetailController::class, 'delete']);

            Route::post($root . '/arise-account', [CompanyDetailController::class, 'createAriseAccount']);
            Route::put($root . '/arise-account/{id}', [CompanyDetailController::class, 'updateAriseAccount']);
            Route::delete($root . '/arise-account/{id}', [CompanyDetailController::class, 'deleteAriseAccount']);
            
            Route::put($root . '/properties/{id}', [CompanyDetailController::class, 'updateProperties']);
            Route::post($root . '/clone', [CompanyDetailController::class, 'clone']);
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

    /**
     * Create
     */
    public function createAriseAccount(AriseAccountCreateRequest $request): Response
    {
        $result = $this->companyDetailService->createAriseAccount($request->all());
        return $this->getResponseHandler()->send($result);
    }

    /**
     * Update
     */
    public function updateAriseAccount(AriseAccountUpdateRequest $request, $id): Response
    {
        $result = $this->companyDetailService->updateAriseAccount($id, $request->all());
        return $this->getResponseHandler()->send($result);
    }

    /**
     * Delete
     */
    // public function deleteAriseAccount($id): Response
    // {
    //     $result = $this->companyDetailService->deleteAriseAccount($id);
    //     return $this->getResponseHandler()->send($result);
    // }

    /**
     * Update properties
     */
    public function updateProperties(mixed $id, CompanyDetailPropertyUpdateRequest $request): Response
    {
        $result = $this->companyDetailService->updateProperties($id, $request->all());
        return $this->getResponseHandler()->send($result);
    }

    /**
     * Clone company detail
     */
    public function clone(Request $request): Response
    {
        $request->validate([
            'company_detail_id' => ['required', 'exists:company_details,id'],
            'new_year' => ['required', 'numeric'],
        ]);

        $result = $this->companyDetailService->clone($request->input());
        return $this->getResponseHandler()->send($result);
    }
}
