<?php

namespace App\Http\Controllers\Api;

use App\DataResources\CompanyDetail\OpeningBalanceVatResource;
use App\Exceptions\Business\ActionFailException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\ErrorCodes;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Utils\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\OpeningBalanceVat\OpeningBalanceVatCreateRequest;
use App\Http\Requests\OpeningBalanceVat\OpeningBalanceVatFindRequest;
use App\Http\Requests\OpeningBalanceVat\OpeningBalanceVatSearchRequest;
use App\Http\Requests\OpeningBalanceVat\OpeningBalanceVatUpdateRequest;
use App\Services\Company\ICompanyService;
use App\Services\IService;
use App\Services\OpeningBalanceVat\IOpeningBalanceVatService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class OpeningBalanceVatController extends ApiController
{
    use DefaultRestActions;

    private IOpeningBalanceVatService $openingBalanceVatService;

    public function __construct(IOpeningBalanceVatService $service)
    {
        $this->openingBalanceVatService = $service;
    }

    /**
     * Register default routes
     * @param string|null $openingBalanceVat
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'opening-balance-vat';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/find', [OpeningBalanceVatController::class, 'find']);
            Route::put($root . '/{id}', [OpeningBalanceVatController::class, 'update']);
        }
    }

    public function getService(): IService
    {
        return $this->openingBalanceVatService;
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
        return OpeningBalanceVatResource::class;
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
                return $request;
            case 'create':
                return $request;
            case 'update':
                $vRequest = OpeningBalanceVatUpdateRequest::createFrom($request);
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
     * @return Response
     */
    public function find(OpeningBalanceVatFindRequest $request): Response
    {
        $result = $this->openingBalanceVatService->find($request->all());
        return $this->getResponseHandler()->send($result);
    }
}
