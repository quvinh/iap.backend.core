<?php

namespace App\Http\Controllers\Api;

use App\DataResources\BusinessPartner\BusinessPartnerResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\BusinessPartner\BusinessPartnerCreateRequest;
use App\Http\Requests\BusinessPartner\BusinessPartnerImportRequest;
use App\Http\Requests\BusinessPartner\BusinessPartnerInsertRequest;
use App\Http\Requests\BusinessPartner\BusinessPartnerSearchRequest;
use App\Http\Requests\BusinessPartner\BusinessPartnerUpdateRequest;
use App\Services\IService;
use App\Services\BusinessPartner\IBusinessPartnerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class BusinessPartnerController extends ApiController
{
    use DefaultRestActions;

    private IBusinessPartnerService $businessPartnerService;

    public function __construct(IBusinessPartnerService $service)
    {
        $this->businessPartnerService = $service;
    }

    /**
     * Register default routes
     * @param string|null $businessPartner
     * @return void
     */
    public static function registerRoutes(string $role): void
    {
        $root = 'business-partners';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [BusinessPartnerController::class, 'search']);
            Route::get($root . '/{id}', [BusinessPartnerController::class, 'getSingleObject']);
            Route::post($root, [BusinessPartnerController::class, 'create']);
            Route::put($root . '/{id}', [BusinessPartnerController::class, 'update']);
            Route::delete($root . '/{id}', [BusinessPartnerController::class, 'delete']);
            Route::delete($root . '/force/{id}', [BusinessPartnerController::class, 'forceDelete']);
        }
    }

    public function getService(): IService
    {
        return $this->businessPartnerService;
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
        return BusinessPartnerResource::class;
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
                $vRequest = BusinessPartnerSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = BusinessPartnerCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = BusinessPartnerUpdateRequest::createFrom($request);
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
     * Force delete
     * @return Response
     */
    public function forceDelete(int $id): Response
    {
        $result = $this->businessPartnerService->delete($id, false);
        return $this->getResponseHandler()->send($result);
    }

    /**
     * Insert into group
     */
    public function insert(BusinessPartnerInsertRequest $request)
    {
        $result = $this->businessPartnerService->insert($request->all());
        return $this->getResponseHandler()->send($result);
    }
}
