<?php

namespace App\Http\Controllers\Api;

use App\DataResources\BaseDataResource;
use App\DataResources\Company\CompanyAllResource;
use App\DataResources\Company\CompanyResource;
use App\Exports\DataAnnouncementExport;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Utils\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Company\CompanyCreateRequest;
use App\Http\Requests\Company\CompanySearchRequest;
use App\Http\Requests\Company\CompanyUpdateRequest;
use App\Services\IService;
use App\Services\Company\ICompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
    public function dataAnnouncementExport(Request $request)
    {
        # Send response using the predefined format
        $response = $this->getResponseHandler();

        # Set file path
        $timestamp = date('YmdHi');
        $file = "ThongBaoSoLieu_$timestamp.xlsx";
        $filePath = "data-announcement/$file";

        $result = Excel::store(new DataAnnouncementExport(), $filePath, StorageHelper::EXCEL_DISK_NAME);
        if (empty($result)) $response->fail(['status' => $result]);

        # Return
        return $response->send([
            'file' => $file,
            // 'type' => $fileType,
            // 'data' => $fileBase64Uri,
        ]);
    }
}
