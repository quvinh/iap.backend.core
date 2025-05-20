<?php

namespace App\Http\Controllers\Api;

use App\DataResources\ItemCode\ItemCodeResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
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
}
