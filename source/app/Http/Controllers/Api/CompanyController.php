<?php

namespace App\Http\Controllers\Api;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyAllResource;
use App\DataResources\Company\CompanyResource;
use App\Exports\DataAnnouncementExport;
use App\Exports\InventoryExport;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Utils\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Company\CompanyCreateRequest;
use App\Http\Requests\Company\CompanySearchRequest;
use App\Http\Requests\Company\CompanyUpdateRequest;
use App\Http\Requests\Company\DataAnouncementExportRequest;
use App\Http\Requests\Company\InventoryExportRequest;
use App\Services\IService;
use App\Services\Company\ICompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CompanyController extends ApiController
{
    use DefaultRestActions;

    private ICompanyService $companyService;

    public function __construct(ICompanyService $service)
    {
        $this->companyService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'companies';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::get($root . '/all', [CompanyController::class, 'all']);
            Route::post($root . '/search', [CompanyController::class, 'search']);
            Route::get($root . '/{id}', [CompanyController::class, 'getSingleObject']);
            Route::post($root, [CompanyController::class, 'create']);
            Route::put($root . '/{id}', [CompanyController::class, 'update']);
            Route::delete($root . '/{id}', [CompanyController::class, 'delete']);

            # Export excel
            Route::post($root . '/data-announcement-export', [CompanyController::class, 'dataAnnouncementExport']);
            Route::post($root . '/inventory-export', [CompanyController::class, 'inventoryExport']);
        }
    }

    public function getService(): IService
    {
        return $this->companyService;
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
        return CompanyResource::class;
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
                $vRequest = CompanySearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = CompanyCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = CompanyUpdateRequest::createFrom($request);
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
     * Get all companies
     */
    public function all()
    {
        $response = $this->companyService->getAllCompanies();
        # Convert result to output resource
        $result = BaseDataResource::generateResources($response, CompanyAllResource::class);
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }

    /**
     * Export excel
     */
    public function dataAnnouncementExport(DataAnouncementExportRequest $request)
    {
        # Send response using the predefined format
        $response = $this->getResponseHandler();

        $payload = $request->input();
        $company = $this->companyService->getSingleObject($request->company_id);
        if (empty($company)) $response->fail(['status' => false, 'message' => 'Company not found']);

        # Set file path
        $timestamp = date('YmdHi');
        $file = "ThongBaoSoLieu_$timestamp.xlsx";
        $filePath = "data-announcement/$file";

        $result = Excel::store(new DataAnnouncementExport($company, $payload), $filePath, StorageHelper::EXCEL_DISK_NAME);
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

    /**
     * Export inventory excel
     */
    public function inventoryExport(InventoryExportRequest $request)
    {
        # Send response using the predefined format
        $response = $this->getResponseHandler();

        # Set file path
        $timestamp = date('YmdHi');
        $file = "NhapXuatTon_$timestamp.xlsx";
        $filePath = "inventory/$file";

        $result = Excel::store(new InventoryExport(
            $this->companyService, 
            $request->company_id, 
            $request->start, 
            $request->end
        ), $filePath, StorageHelper::EXCEL_DISK_NAME);
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
}
