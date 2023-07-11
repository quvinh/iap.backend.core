<?php

namespace App\Http\Controllers\Api;

use App\DataResources\BaseDataResource;
use App\DataResources\FirstAriseAccount\FirstAriseAccountAllResource;
use App\DataResources\FirstAriseAccount\FirstAriseAccountResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\FirstAriseAccount\FirstAriseAccountCreateRequest;
use App\Http\Requests\FirstAriseAccount\FirstAriseAccountSearchRequest;
use App\Http\Requests\FirstAriseAccount\FirstAriseAccountUpdateRequest;
use App\Services\IService;
use App\Services\FirstAriseAccount\IFirstAriseAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class FirstAriseAccountController extends ApiController
{
    use DefaultRestActions;

    private IFirstAriseAccountService $firstAriseAccountService;

    public function __construct(IFirstAriseAccountService $service)
    {
        $this->firstAriseAccountService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'arise_accounts';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::get($root . '/all', [CompanyController::class, 'all']);
            Route::post($root . '/search', [FirstAriseAccountController::class, 'search']);
            Route::get($root . '/{id}', [FirstAriseAccountController::class, 'getSingleObject']);
            Route::post($root, [FirstAriseAccountController::class, 'create']);
            Route::put($root . '/{id}', [FirstAriseAccountController::class, 'update']);
            Route::delete($root . '/{id}', [FirstAriseAccountController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->firstAriseAccountService;
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
        return FirstAriseAccountResource::class;
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
                $vRequest = FirstAriseAccountSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = FirstAriseAccountCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = FirstAriseAccountUpdateRequest::createFrom($request);
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
     * Get all arise accounts
     */
    public function all()
    {
        $response = $this->firstAriseAccountService->getAllAriseAccounts();
        # Convert result to output resource
        $result = BaseDataResource::generateResources($response, FirstAriseAccountAllResource::class);
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }
}
