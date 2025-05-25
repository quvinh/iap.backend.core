<?php

namespace App\Http\Controllers\Api;

use App\DataResources\BaseDataResource;
use App\DataResources\Invoice\InvoiceBasicResource;
use App\DataResources\Invoice\InvoiceResource;
use App\Exports\InvoiceDetailsExport;
use App\Exports\InvoicesExport;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\InvoiceTypes;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Utils\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Invoice\InvoiceCreateEachRowRequest;
use App\Http\Requests\Invoice\InvoiceCreateRequest;
use App\Http\Requests\Invoice\InvoiceFindNextRequest;
use App\Http\Requests\Invoice\InvoiceImportRequest;
use App\Http\Requests\Invoice\InvoiceSearchPartnerRequest;
use App\Http\Requests\Invoice\InvoiceSearchRequest;
use App\Http\Requests\Invoice\InvoiceTctCreateRequest;
use App\Http\Requests\Invoice\InvoiceUpdateRequest;
use App\Imports\ImportedGoodsImport;
use App\Jobs\ImportedGoodsExcelJob;
use App\Models\JobHistory;
use App\Services\IService;
use App\Services\Invoice\IInvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

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
            Route::post($root . '/search', [InvoiceController::class, 'search'])->middleware('can:search,App\Models\Invoice');
            Route::post($root . '/search-export', [InvoiceController::class, 'searchForExport'])->middleware('can:search,App\Models\Invoice');
            Route::get($root . '/{id}', [InvoiceController::class, 'getSingleObject'])->middleware('can:search,App\Models\Invoice');
            Route::post($root, [InvoiceController::class, 'create'])->middleware('can:create,App\Models\Invoice');
            // Route::post($root . '/each', [InvoiceController::class, 'createEachRow']);
            Route::put($root . '/{id}', [InvoiceController::class, 'update'])->middleware('can:update,App\Models\Invoice');
            Route::delete($root . '/{id}', [InvoiceController::class, 'delete'])->middleware('can:delete,App\Models\Invoice');

            Route::post($root . '/import', [InvoiceController::class, 'import'])->middleware('can:create,App\Models\Invoice');
            Route::post($root . '/import-pdf', [InvoiceController::class, 'importPDF'])->middleware('can:create,App\Models\Invoice');
            Route::post($root . '/import-imported-goods', [InvoiceController::class, 'importImportedGoods'])->middleware('can:create,App\Models\Invoice');
            Route::post($root . '/restore-rows/{id}', [InvoiceController::class, 'restoreRows']);
            Route::post($root . '/partners', [InvoiceController::class, 'partners']);
            Route::post($root . '/info', [InvoiceController::class, 'info']);
            Route::post($root . '/next', [InvoiceController::class, 'next']);

            Route::post($root . '/invoices-export', [InvoiceController::class, 'invoicesExport']);
            Route::post($root . '/invoice-details-export', [InvoiceController::class, 'invoiceDetailsExport']);
            Route::post($root . '/tct', [InvoiceController::class, 'createInvoiceTct'])->middleware('can:search,App\Models\Invoice');
            Route::post($root . '/save-tct', [InvoiceController::class, 'saveInvoiceTct'])->middleware('can:search,App\Models\Invoice');
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

    public function getDataBasicResourceClass(): string
    {
        return InvoiceBasicResource::class;
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
        }
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send(true);
    }

    public function partners(InvoiceSearchPartnerRequest $request): Response
    {
        $result = $this->invoiceService->findPartnersByCompanyId($request->company_id, $request->year);
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }

    /**
     * Search list of inovices
     * @param Request $request
     * @return Response
     * @throws InvalidPaginationInfoException
     */
    public function searchForExport(InvoiceSearchRequest $request): Response
    {
        # 1. get payload
        $payload = $request->input();
        $meta = $this->getCurrentMetaInfo();
        $requireToTranslate = false;
        $withs = $payload['withs'] ?? [];
        # 2. get pagination if any
        $paging = $request->getPaginationInfo();

        # 3. Call business processes
        $result = $this->getService()->search($payload, paging: $paging, withs: $withs);

        # 4. Translate if required
        if ($requireToTranslate) {
            foreach ($result as $item) $item->translate($meta->lang);
        }

        # 5. Convert result to output resource
        $resourceClass = $this->getDataBasicResourceClass();
        $result = BaseDataResource::generateResources($result, $resourceClass, $withs);

        # 6. Send response using the predefined format
        $response = $this->getResponseHandler();
        if (!is_null($paging)) $response = $response->withTotalPages($paging->lastPage, $paging->total);
        return $response->send($result);
    }

    /**
     * Info list inovices
     */
    public function info(Request $request): Response
    {
        $result = $this->invoiceService->info($request->all());
        # Send response using the predefined format
        $response = $this->getResponseHandler();
        return $response->send($result);
    }

    /**
     * Find next invoice
     */
    public function next(InvoiceFindNextRequest $request): Response
    {
        $result = $this->invoiceService->findNextInvoice($request->all());
        # Send response using the predefined format
        $response = $this->getResponseHandler();
        return $response->send($result);
    }

    public function searchBusiness(InvoiceSearchRequest $request): array
    {
        # Get payload
        $payload = $request->input();
        $withs = $payload['withs'] ?? [];
        # Get pagination if any
        $paging = $request->getPaginationInfo();
        # Call business processes
        $result = $this->getService()->search($payload, paging: $paging, withs: $withs);
        # Convert result to output resource
        $resourceClass = $this->getDataBasicResourceClass();
        $result = BaseDataResource::generateResources($result, $resourceClass, $withs);
        return $result;
    }

    public function invoicesExport(InvoiceSearchRequest $request)
    {
        # Search invoices
        $record = $this->searchBusiness($request);
        # Send response using the predefined format
        $response = $this->getResponseHandler();

        # Set file path
        $timestamp = date('YmdHi');
        $file = "DanhSachHoaDon_$timestamp.xlsx";
        $filePath = "invoices/$file";

        $result = Excel::store(new InvoicesExport($record), $filePath, StorageHelper::EXCEL_DISK_NAME);
        if (empty($result)) $response->fail(['status' => $result]);
        # Generate file base64
        $fileContent = Storage::disk(StorageHelper::EXCEL_DISK_NAME)->get($filePath);
        $fileType = File::mimeType(storage_path("app/export/$filePath"));
        $base64 = base64_encode($fileContent);
        $fileBase64Uri = "data:$fileType;base64,$base64";

        # Delete if needed
        Storage::disk(StorageHelper::EXCEL_DISK_NAME)->delete($filePath);

        # Return
        return $response->send([
            'file' => $file,
            'type' => $fileType,
            'data' => $fileBase64Uri,
        ]);
    }

    public function invoiceDetailsExport(InvoiceSearchRequest $request)
    {
        # Search invoices
        $record = $this->searchBusiness($request);
        # Send response using the predefined format
        $response = $this->getResponseHandler();

        # Set file path
        $timestamp = date('YmdHi');
        $file = "DanhSachChiTietHoaDon_$timestamp.xlsx";
        $filePath = "invoice-details/$file";

        $result = Excel::store(new InvoiceDetailsExport($record), $filePath, StorageHelper::EXCEL_DISK_NAME);
        if (empty($result)) $response->fail(['status' => $result]);
        # Generate file base64
        $fileContent = Storage::disk(StorageHelper::EXCEL_DISK_NAME)->get($filePath);
        $fileType = File::mimeType(storage_path("app/export/$filePath"));
        $base64 = base64_encode($fileContent);
        $fileBase64Uri = "data:$fileType;base64,$base64";

        # Delete if needed
        Storage::disk(StorageHelper::EXCEL_DISK_NAME)->delete($filePath);

        # Return
        return $response->send([
            'file' => $file,
            'type' => $fileType,
            'data' => $fileBase64Uri,
        ]);
    }

    public function createInvoiceTct(InvoiceTctCreateRequest $request)
    {
        $result = $this->invoiceService->createInvoiceTct($request->all());
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }

    public function saveInvoiceTct(Request $request)
    {
        $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'type' => ['required', 'string', 'max:10', Rule::in(InvoiceTypes::getValues())],
            'records' => ['required', 'array'],
        ]);

        $result = $this->invoiceService->saveInvoiceTct($request->all());
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }

    public function importImportedGoods(Request $request)
    {
        $request->validate([
            'file' => ['required', 'mimes:xlsx'],
            'company_id' => ['required', 'exists:companies,id'],
            'year' => ['required'],
            'invoice_type' => ['required', Rule::in(['sold', 'purchase'])],
        ]);

        # Send response using the predefined format
        $response = ApiResponse::v1();

        $date = Carbon::now()->format('Ymd');
        $storage = Storage::disk(StorageHelper::TMP_DISK_NAME);
        $folder = "excel/queue/$date";

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $storage->put($folder, $file);

        DB::beginTransaction();
        try {
            $user_id = auth()->user()->getAuthIdentifier();
            $jobHistory = JobHistory::create([
                'company_id' => $request->company_id,
                'job_id' => null,
                'file_name' => $fileName,
                'note' => "Chờ xử lý",
                'path' => null,
                'status' => JobHistory::STATUS_PENDING,
            ]);
            ImportedGoodsExcelJob::dispatch(
                $this->invoiceService,
                $filePath,
                $request->input(),
                $user_id,
                $jobHistory->id,
                $this->getCurrentMetaInfo()
            );

            DB::commit();
            return $response->send(true);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return $response->fail($ex->getMessage());
        }
    }
}
