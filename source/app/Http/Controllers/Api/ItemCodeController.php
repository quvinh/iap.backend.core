<?php

namespace App\Http\Controllers\Api;

use App\DataResources\ItemCode\ItemCodeResource;
use App\Exports\DTechBusinessPartnerExport;
use App\Exports\DTechInvoicePurchaseExport;
use App\Exports\DTechInvoiceSoldExport;
use App\Exports\DTechItemCodeExport;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Responses\HttpStatuses;
use App\Helpers\Utils\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\ItemCode\ItemCodeCreateRequest;
use App\Http\Requests\ItemCode\ItemCodeImportRequest;
use App\Http\Requests\ItemCode\ItemCodeSearchRequest;
use App\Http\Requests\ItemCode\ItemCodeUpdateRequest;
use App\Imports\ImportedGoodsCodeImport;
use App\Jobs\ItemCodeExcelJob;
use App\Models\ItemCode;
use App\Models\JobHistory;
use App\Services\IService;
use App\Services\ItemCode\IItemCodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ItemCodeController extends ApiController
{
    use DefaultRestActions;

    private IItemCodeService $itemCodeService;

    public function __construct(IItemCodeService $service)
    {
        $this->itemCodeService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'item-codes';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [ItemCodeController::class, 'search']);
            Route::get($root . '/{id}', [ItemCodeController::class, 'getSingleObject']);
            Route::post($root, [ItemCodeController::class, 'create']);
            Route::put($root . '/{id}', [ItemCodeController::class, 'update']);
            Route::delete($root . '/{id}', [ItemCodeController::class, 'delete']);
            Route::delete($root . '/force/{id}', [ItemCodeController::class, 'forceDelete']);

            // Route::post($root . '/import', [ItemCodeController::class, 'import']);
            Route::post($root . '/import', [ItemCodeController::class, 'importItemCode']);
            Route::post($root . '/import-imported-goods-code', [ItemCodeController::class, 'importImportedGoodsCode']);

            Route::post($root . '/auto-fill', [ItemCodeController::class, 'autoFill']);
            Route::post($root . '/save-auto-fill', [ItemCodeController::class, 'saveAutoFill']);
            Route::post($root . '/data-export', [ItemCodeController::class, 'dataExport']);
        }
        Route::get($root, [ItemCodeController::class, 'getAll']);
    }

    public function getService(): IService
    {
        return $this->itemCodeService;
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
        return ItemCodeResource::class;
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
                $vRequest = ItemCodeSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = ItemCodeCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = ItemCodeUpdateRequest::createFrom($request);
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
        $result = $this->itemCodeService->delete($id, false);
        return $this->getResponseHandler()->send($result);
    }

    /**
     * Handle import excel
     */
    public function import(ItemCodeImportRequest $request)
    {
        $result = $this->itemCodeService->import($request->all(), $this->getCurrentMetaInfo());
        return $this->getResponseHandler()->send($result);
    }

    public function getAll(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'year' => 'required',
        ]);

        $result = $this->itemCodeService->getAll($request->input());
        return $this->getResponseHandler()->send($result);
    }

    public function importImportedGoodsCode(Request $request)
    {
        $request->validate([
            'file' => ['required', 'mimes:xlsx'],
            'company_id' => ['required', 'exists:companies,id'],
            'year' => ['required'],
        ]);

        # Send response using the predefined format
        $response = ApiResponse::v1();

        DB::beginTransaction();
        try {
            Excel::import(new ImportedGoodsCodeImport($request->company_id, $request->year), $request->file('file'));
            DB::commit();
            return $response->send(true);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return $response->fail($ex->getMessage());
        }
    }

    public function importItemCode(Request $request)
    {
        $request->validate([
            'file' => ['required', 'mimes:xlsx'],
            'company_id' => ['required', 'exists:companies,id'],
            'year' => ['required'],
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
            ItemCodeExcelJob::dispatch(
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

    public function autoFill(Request $request)
    {
        # Send response using the predefined format
        $response = ApiResponse::v1();

        $result = $this->itemCodeService->autoFill($request->input());

        return $response->send($result);
    }

    public function saveAutoFill(Request $request)
    {
        # Send response using the predefined format
        $response = ApiResponse::v1();

        $result = $this->itemCodeService->saveAutoFill($request->input());

        return $response->send($result);
    }

    /**
     * Export excel
     */
    public function dataExport(Request $request)
    {
        $request->validate([
            'key' => ['string', 'required'],
        ]);

        # Send response using the predefined format
        $response = $this->getResponseHandler();

        $timestamp = date('YmdHi');

        switch ($request->key) {
            case 'dtech-item-code':
                $file = "ExportMaHangHoa_DTech_$timestamp.xlsx";
                $filePath = "dtech/$file";
                $result = Excel::store(new DTechItemCodeExport($request->input()), $filePath, StorageHelper::EXCEL_DISK_NAME);
                break;
            case 'dtech-business-partner':
                $file = "ExportKhachHang_DTech_$timestamp.xlsx";
                $filePath = "dtech/$file";
                $result = Excel::store(new DTechBusinessPartnerExport($request->input()), $filePath, StorageHelper::EXCEL_DISK_NAME);
                break;
            case 'dtech-purchase':
                $file = "ExportMuaVao_DTech_$timestamp.xlsx";
                $filePath = "dtech/$file";
                $result = Excel::store(new DTechInvoicePurchaseExport($request->input()), $filePath, StorageHelper::EXCEL_DISK_NAME);
                break;
            case 'dtech-sold':
                $file = "ExportBanRa_DTech_$timestamp.xlsx";
                $filePath = "dtech/$file";
                $result = Excel::store(new DTechInvoiceSoldExport($request->input()), $filePath, StorageHelper::EXCEL_DISK_NAME);
                break;
            default:
                return $response->withStatusCode(HttpStatuses::HTTP_BAD_REQUEST)->fail(['status' => 'Key invalid!']);
        }

        if (empty($result)) return $response->fail(['status' => $result]);

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
}
