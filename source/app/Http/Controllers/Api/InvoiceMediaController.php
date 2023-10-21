<?php

namespace App\Http\Controllers\Api;

use App\DataResources\InvoiceMedia\InvoiceMediaResource;
use App\Exceptions\Business\ActionFailException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\ErrorCodes;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Utils\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\InvoiceMedia\InvoiceMediaCreateRequest;
use App\Http\Requests\InvoiceMedia\InvoiceMediaSearchRequest;
use App\Http\Requests\InvoiceMedia\InvoiceMediaUpdateRequest;
use App\Services\Company\ICompanyService;
use App\Services\IService;
use App\Services\InvoiceMedia\IInvoiceMediaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class InvoiceMediaController extends ApiController
{
    use DefaultRestActions;

    private const DEFAULT_FOLDER_UPLOAD_FILE = 'upload/pdf';
    private IInvoiceMediaService $invoiceMediaService;
    private ICompanyService $companyService;

    public function __construct(IInvoiceMediaService $service, ICompanyService $companyService)
    {
        $this->invoiceMediaService = $service;
        $this->companyService = $companyService;
    }

    /**
     * Register default routes
     * @param string|null $invoiceMedia
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'invoice-media';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [InvoiceMediaController::class, 'search']);
            Route::get($root . '/{id}', [InvoiceMediaController::class, 'getSingleObject']);
            Route::post($root, [InvoiceMediaController::class, 'create']);
            Route::put($root . '/{id}', [InvoiceMediaController::class, 'update']);
            Route::delete($root . '/{id}', [InvoiceMediaController::class, 'delete']);

            Route::post($root . '/import-pdf', [InvoiceMediaController::class, 'importPDF']);
            Route::get($root . '/file/{slug}', [InvoiceMediaController::class, 'getFile'])->where('slug', '.*');
        }
    }

    public function getService(): IService
    {
        return $this->invoiceMediaService;
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
        return InvoiceMediaResource::class;
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
                $vRequest = InvoiceMediaSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = InvoiceMediaCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = InvoiceMediaUpdateRequest::createFrom($request);
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
     * Import files
     */
    public function importPDF(InvoiceMediaCreateRequest $request): mixed
    {
        $root = self::DEFAULT_FOLDER_UPLOAD_FILE;
        $company_id = $request->company_id;
        $year = $request->year;
        # Send response using the predefined format
        $response = ApiResponse::v1();

        if ($request->hasFile('file')) {
            # Check company
            $com = $this->companyService->getSingleObject($company_id);
            if (empty($com)) throw new ActionFailException(code: ErrorCodes::ERR_RECORD_NOT_FOUND);
            $company_taxcode = $com->tax_code;

            # Upload
            $storage = Storage::disk(StorageHelper::TMP_DISK_NAME);
            $checkDirectory = $storage->exists($root);
            if (!$checkDirectory) {
                $storage->makeDirectory($root);
            }
            $result = $storage->put("$root/$year/$company_taxcode", $request->file('file'), 'public');
            if (!empty($result)) {
                $params = [
                    'company_id' => $com->id,
                    'year' => $year,
                    'path' => $result,
                ];

                $record = $this->invoiceMediaService->create($params, $this->getCurrentMetaInfo());
                return $response->send($record);
            }
            throw new ActionFailException(code: ErrorCodes::ERROR_CANNOT_UPLOAD_FILE);
        }
        throw new ActionFailException(code: ErrorCodes::ERROR_CANNOT_UPLOAD_FILE);
    }

    /**
     * Get the system file
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function getFile(string $slug): HttpResponse
    {
        $disk = Storage::disk(StorageHelper::TMP_DISK_NAME);
        
        if (!$disk->exists($slug)) {
            $filePath = null;
        } else {
            $filePath = $disk->path($slug);
        }

        $filePath = $filePath ?? resource_path() . '/images/default/default-thumbnail.jpg';

        $file = File::get($filePath);
        $type = File::mimeType($filePath);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }
}
