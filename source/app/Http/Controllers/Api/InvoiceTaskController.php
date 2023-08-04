<?php

namespace App\Http\Controllers\Api;

use App\DataResources\InvoiceTask\InvoiceTaskResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\InvoiceTask\InvoiceTaskCreateRequest;
use App\Http\Requests\InvoiceTask\InvoiceTaskHandleFormulaRequest;
use App\Http\Requests\InvoiceTask\InvoiceTaskSearchRequest;
use App\Http\Requests\InvoiceTask\InvoiceTaskUpdateRequest;
use App\Services\IService;
use App\Services\InvoiceTask\IInvoiceTaskService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class InvoiceTaskController extends ApiController
{
    use DefaultRestActions;

    private IInvoiceTaskService $invoiceTaskService;

    public function __construct(IInvoiceTaskService $service)
    {
        $this->invoiceTaskService = $service;
    }

    /**
     * Register default routes
     * @param string|null $invoiceTask
     * @return void
     */
    public static function registerRoutes(string $invoiceTask = null): void
    {
        $root = 'invoice-tasks';
        if ($invoiceTask == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [InvoiceTaskController::class, 'search']);
            Route::get($root . '/{id}', [InvoiceTaskController::class, 'getSingleObject']);
            Route::post($root, [InvoiceTaskController::class, 'create']);
            Route::put($root . '/{id}', [InvoiceTaskController::class, 'update']);
            Route::delete($root . '/{id}', [InvoiceTaskController::class, 'delete']);
            Route::delete($root . '/force/{id}', [InvoiceTaskController::class, 'forceDelete']);

            Route::put($root . '/formula/update', [InvoiceTaskController::class, 'updateHandleFormula']);
        }
    }

    public function getService(): IService
    {
        return $this->invoiceTaskService;
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
        return InvoiceTaskResource::class;
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
                $vRequest = InvoiceTaskSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = InvoiceTaskCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = InvoiceTaskUpdateRequest::createFrom($request);
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
        $result = $this->invoiceTaskService->delete($id, false);
        return $this->getResponseHandler()->send($result);
    }

    public function updateHandleFormula(InvoiceTaskHandleFormulaRequest $request): Response
    {
        $result = $this->invoiceTaskService->updateHandleFormula($request->all());
        return $this->getResponseHandler()->send($result);
    }
}
