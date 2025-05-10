<?php

namespace App\Http\Controllers\Api;

use App\DataResources\InvoiceDetail\InvoiceDetailResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Invoice\InvoiceUpdateProgressFormulaRequest;
use App\Http\Requests\InvoiceDetail\InvoiceDetailCreateRequest;
use App\Http\Requests\InvoiceDetail\InvoiceDetailSearchRequest;
use App\Http\Requests\InvoiceDetail\InvoiceDetailUpdateRequest;
use App\Services\IService;
use App\Services\InvoiceDetail\IInvoiceDetailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class InvoiceDetailController extends ApiController
{
    use DefaultRestActions;

    private IInvoiceDetailService $invoiceDetailService;

    public function __construct(IInvoiceDetailService $service)
    {
        $this->invoiceDetailService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'invoice-details';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [InvoiceDetailController::class, 'search']);
            Route::get($root . '/{id}', [InvoiceDetailController::class, 'getSingleObject']);
            Route::post($root, [InvoiceDetailController::class, 'create']);
            Route::put($root . '/{id}', [InvoiceDetailController::class, 'update']);
            Route::put($root, [InvoiceDetailController::class, 'updateProgressByFormula']);
            Route::delete($root . '/{id}', [InvoiceDetailController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->invoiceDetailService;
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
        return InvoiceDetailResource::class;
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
                $vRequest = InvoiceDetailSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                // $vRequest = InvoiceDetailCreateRequest::createFrom($request);
                // $vRequest->validate();
                // return $vRequest;
            case 'update':
                // $vRequest = InvoiceDetailUpdateRequest::createFrom($request);
                // $vRequest->validate();
                // return $vRequest;
            case 'getSingleObject':
                return $request;
            case 'delete':
                return $request;
            default:
                return $request;
        }
    }

    public function updateProgressByFormula(InvoiceUpdateProgressFormulaRequest $request)
    {
        $result = $this->invoiceDetailService->updateProgressByFormula($request->all());
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }
}
