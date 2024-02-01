<?php

namespace App\Http\Controllers\Api;

use App\DataResources\BaseDataResource;
use App\DataResources\Invoice\InvoiceBasicResource;
use App\DataResources\InvoiceTask\InvoiceTaskResource;
use App\Exports\ReportSoldExport;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Utils\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\InvoiceTask\InvoiceTaskCreateRequest;
use App\Http\Requests\InvoiceTask\InvoiceTaskHandleFormulaRequest;
use App\Http\Requests\InvoiceTask\InvoiceTaskSearchRequest;
use App\Http\Requests\InvoiceTask\InvoiceTaskUpdateRequest;
use App\Http\Requests\InvoiceTask\InvoiceTaskGetMoneyRequest;
use App\Http\Requests\InvoiceTask\InvoiceTaskWithTypeRequest;
use App\Http\Requests\InvoiceTask\ReportSoldExportRequest;
use App\Services\Invoice\IInvoiceService;
use App\Services\IService;
use App\Services\InvoiceTask\IInvoiceTaskService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceTaskController extends ApiController
{
    use DefaultRestActions;

    private IInvoiceTaskService $invoiceTaskService;
    private IInvoiceService $invoiceService;

    public function __construct(IInvoiceTaskService $service, IInvoiceService $invoiceService)
    {
        $this->invoiceTaskService = $service;
        $this->invoiceService = $invoiceService;
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
            Route::post($root . '/money-of-months', [InvoiceTaskController::class, 'getMoneyOfMonths']);
            Route::post($root . '/delete-with-type', [InvoiceTaskController::class, 'forceDeleteInvoiceWithTask']);

            Route::post($root . '/report-sold', [InvoiceTaskController::class, 'reportSold']);
            Route::post($root . '/report-sold-export', [InvoiceTaskController::class, 'reportSoldExport']);
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

    public function getMoneyOfMonths(InvoiceTaskGetMoneyRequest $request): Response
    {
        $result = $this->invoiceTaskService->getMoneyOfMonths($request->all());
        return $this->getResponseHandler()->send($result);
    }

    /**
     * Force delelte invoices with task (not delete invoice_task)
     */
    public function forceDeleteInvoiceWithTask(InvoiceTaskWithTypeRequest $request): Response
    {
        $result = $this->invoiceTaskService->forceDeleteInvoiceWithTask($request->all());
        return $this->getResponseHandler()->send($result);
    }

    /**
     * Report-sold
     */
    public function reportSold(ReportSoldExportRequest $request)
    {
        # Send response using the predefined format
        $response = $this->getResponseHandler();
        
        $record = $this->invoiceService->reportSold($request->all());
        if (empty($record)) return $response->fail('record is empty');

        # Set file path
        $timestamp = date('YmdHi');
        $file = "ChungTuBanHang_$timestamp.xlsx";
        $filePath = "report/$file";

        $result = Excel::store(new ReportSoldExport($record), $filePath, StorageHelper::EXCEL_DISK_NAME);
        if (empty($result)) return $response->fail(['status' => $result]);

        # Return
        return $response->send([
            'file' => $file,
            'path' => $filePath,
            'count' => count($record),
            'record' => BaseDataResource::generateResources($record, InvoiceBasicResource::class, ['invoice_details', 'company']),
        ]);
    }

    /**
     * Report-sold export
     */
    public function reportSoldExport(Request $request)
    {
        # Send response using the predefined format
        $response = $this->getResponseHandler();

        if (empty($request->path) || empty($request->file)) return $response->fail('file-path invalid');
        $filePath = $request->path;
        $disk = Storage::disk(StorageHelper::EXCEL_DISK_NAME);
        if (!$disk->exists($filePath)) return $response->fail('file not found');

        # Generate file base64
        $fileContent = $disk->get($filePath);
        $fileType = File::mimeType(storage_path("app/export/$filePath"));
        $base64 = base64_encode($fileContent);
        $fileBase64Uri = "data:$fileType;base64,$base64";

        # Return
        return $response->send([
            'file' => $request->file,
            'type' => $fileType,
            'data' => $fileBase64Uri,
        ]);
    }
}
