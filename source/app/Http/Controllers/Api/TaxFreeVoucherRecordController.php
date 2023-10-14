<?php

namespace App\Http\Controllers\Api;

use App\DataResources\TaxFreeVoucherRecord\TaxFreeVoucherRecordResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\TaxFreeVoucherRecord\TaxFreeVoucherRecordCreateRequest;
use App\Http\Requests\TaxFreeVoucherRecord\TaxFreeVoucherRecordSearchRequest;
use App\Http\Requests\TaxFreeVoucherRecord\TaxFreeVoucherRecordUpdateRequest;
use App\Services\IService;
use App\Services\TaxFreeVoucherRecord\ITaxFreeVoucherRecordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class TaxFreeVoucherRecordController extends ApiController
{
    use DefaultRestActions;

    private ITaxFreeVoucherRecordService $taxFreeVoucherRecordService;

    public function __construct(ITaxFreeVoucherRecordService $service)
    {
        $this->taxFreeVoucherRecordService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'tax-free-voucher-records';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [TaxFreeVoucherRecordController::class, 'search']);
            Route::get($root . '/{id}', [TaxFreeVoucherRecordController::class, 'getSingleObject']);
            Route::post($root, [TaxFreeVoucherRecordController::class, 'create']);
            Route::put($root . '/{id}', [TaxFreeVoucherRecordController::class, 'update']);
            Route::delete($root . '/{id}', [TaxFreeVoucherRecordController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->taxFreeVoucherRecordService;
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
        return TaxFreeVoucherRecordResource::class;
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
                $vRequest = TaxFreeVoucherRecordSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = TaxFreeVoucherRecordCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = TaxFreeVoucherRecordUpdateRequest::createFrom($request);
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
