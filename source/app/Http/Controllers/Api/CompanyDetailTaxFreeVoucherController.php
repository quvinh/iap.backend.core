<?php

namespace App\Http\Controllers\Api;

use App\DataResources\CompanyDetailTaxFreeVoucher\CompanyDetailTaxFreeVoucherResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserCompanyDetailTaxFreeVouchers;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\CompanyDetailTaxFreeVoucher\CompanyDetailTaxFreeVoucherCreateRequest;
use App\Http\Requests\CompanyDetailTaxFreeVoucher\CompanyDetailTaxFreeVoucherSearchRequest;
use App\Http\Requests\CompanyDetailTaxFreeVoucher\CompanyDetailTaxFreeVoucherUpdateRequest;
use App\Services\IService;
use App\Services\CompanyDetailTaxFreeVoucher\ICompanyDetailTaxFreeVoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CompanyDetailTaxFreeVoucherController extends ApiController
{
    use DefaultRestActions;

    private ICompanyDetailTaxFreeVoucherService $companyDetailTaxFreeVoucherService;

    public function __construct(ICompanyDetailTaxFreeVoucherService $service)
    {
        $this->companyDetailTaxFreeVoucherService = $service;
    }

    /**
     * Register default routes
     * @param string|null $CompanyDetailTaxFreeVoucher
     * @return void
     */
    public static function registerRoutes(string $CompanyDetailTaxFreeVoucher = null): void
    {
        $root = 'company_detail_tax_free_vouchers';
        if ($CompanyDetailTaxFreeVoucher == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [CompanyDetailTaxFreeVoucherController::class, 'search']);
            Route::get($root . '/{id}', [CompanyDetailTaxFreeVoucherController::class, 'getSingleObject']);
            Route::post($root, [CompanyDetailTaxFreeVoucherController::class, 'create']);
            Route::put($root . '/{id}', [CompanyDetailTaxFreeVoucherController::class, 'update']);
            Route::delete($root . '/{id}', [CompanyDetailTaxFreeVoucherController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->companyDetailTaxFreeVoucherService;
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
        return CompanyDetailTaxFreeVoucherResource::class;
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
                $vRequest = CompanyDetailTaxFreeVoucherSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = CompanyDetailTaxFreeVoucherCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = CompanyDetailTaxFreeVoucherUpdateRequest::createFrom($request);
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
