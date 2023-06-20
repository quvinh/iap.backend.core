<?php

namespace App\Http\Controllers\Api;

use App\DataResources\TaxFreeVoucher\TaxFreeVoucherResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\TaxFreeVoucher\TaxFreeVoucherCreateRequest;
use App\Http\Requests\TaxFreeVoucher\TaxFreeVoucherSearchRequest;
use App\Http\Requests\TaxFreeVoucher\TaxFreeVoucherUpdateRequest;
use App\Services\IService;
use App\Services\TaxFreeVoucher\ITaxFreeVoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class TaxFreeVoucherController extends ApiController
{
    use DefaultRestActions;

    private ITaxFreeVoucherService $taxFreeVoucherService;

    public function __construct(ITaxFreeVoucherService $service)
    {
        $this->taxFreeVoucherService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'tax_free_vouchers';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [TaxFreeVoucherController::class, 'search']);
            Route::get($root . '/{id}', [TaxFreeVoucherController::class, 'getSingleObject']);
            Route::post($root, [TaxFreeVoucherController::class, 'create']);
            Route::put($root . '/{id}', [TaxFreeVoucherController::class, 'update']);
            Route::delete($root . '/{id}', [TaxFreeVoucherController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->taxFreeVoucherService;
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
        return TaxFreeVoucherResource::class;
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
                $vRequest = TaxFreeVoucherSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = TaxFreeVoucherCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = TaxFreeVoucherUpdateRequest::createFrom($request);
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
