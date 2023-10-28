<?php

namespace App\Http\Controllers\Api;

use App\DataResources\Invoice\InvoiceResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Invoice\InvoiceCreateEachRowRequest;
use App\Http\Requests\Invoice\InvoiceCreateRequest;
use App\Http\Requests\Invoice\InvoiceImportRequest;
use App\Http\Requests\Invoice\InvoiceSearchPartnerRequest;
use App\Http\Requests\Invoice\InvoiceSearchRequest;
use App\Http\Requests\Invoice\InvoiceUpdateRequest;
use App\Services\IService;
use App\Services\Invoice\IInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class InvoiceController extends ApiController
{
    use DefaultRestActions;

    private IInvoiceService $invoiceService;

    public function __construct(IInvoiceService $service)
    {
        $this->invoiceService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'invoices';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [InvoiceController::class, 'search']);
            Route::get($root . '/{id}', [InvoiceController::class, 'getSingleObject']);
            Route::post($root, [InvoiceController::class, 'create']);
            // Route::post($root . '/each', [InvoiceController::class, 'createEachRow']);
            Route::put($root . '/{id}', [InvoiceController::class, 'update']);
            Route::delete($root . '/{id}', [InvoiceController::class, 'delete']);

            Route::post($root . '/import', [InvoiceController::class, 'import']);
            Route::post($root . '/import-pdf', [InvoiceController::class, 'importPDF']);
            Route::post($root . '/restore-rows/{id}', [InvoiceController::class, 'restoreRows']);
            Route::post($root . '/partners', [InvoiceController::class, 'partners']);
        }
    }

    public function getService(): IService
    {
        return $this->invoiceService;
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
        return InvoiceResource::class;
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
                $vRequest = InvoiceSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                // $vRequest = InvoiceCreateRequest::createFrom($request);
                // $vRequest->validate();
                // return $vRequest;
            case 'update':
                $vRequest = InvoiceUpdateRequest::createFrom($request);
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

    public function createEachRow(InvoiceCreateEachRowRequest $request): Response
    {
        $result = $this->invoiceService->storeEachRowInvoice($request->all());
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }

    public function import(InvoiceImportRequest $request): Response
    {
        $result = $this->invoiceService->import($request->all(), $this->getCurrentMetaInfo());
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }

    public function restoreRows(mixed $id): Response
    {
        $result = $this->invoiceService->restoreRowsInvoice($id, $this->getCurrentMetaInfo());
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }

    public function importPDF(Request $request): Response
    {
        if ($request->hasFile('file')) {
            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('file')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            dd($fileNameToStore, $request->company_id);
        }
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send();
    }

    public function partners(InvoiceSearchPartnerRequest $request): Response
    {
        $result = $this->invoiceService->findPartnersByCompanyId($request->company_id, $request->year);
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }
}
