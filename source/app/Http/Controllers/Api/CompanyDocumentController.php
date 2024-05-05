<?php

namespace App\Http\Controllers\Api;

use App\DataResources\CompanyDocument\CompanyDocumentResource;
use App\Exceptions\Business\ActionFailException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\ErrorCodes;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Utils\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\CompanyDocument\CompanyDocumentCreateRequest;
use App\Http\Requests\CompanyDocument\CompanyDocumentImportRequest;
use App\Http\Requests\CompanyDocument\CompanyDocumentReadRequest;
use App\Http\Requests\CompanyDocument\CompanyDocumentSearchRequest;
use App\Http\Requests\CompanyDocument\CompanyDocumentUpdateRequest;
use App\Services\Company\ICompanyService;
use App\Services\IService;
use App\Services\CompanyDocument\ICompanyDocumentService;
use App\Services\PdfTableKey\IPdfTableKeyService;
use Carbon\Carbon;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyDocumentController extends ApiController
{
    use DefaultRestActions;

    private const DEFAULT_FOLDER_UPLOAD_FILE = 'upload/documents';
    private ICompanyDocumentService $companyDocumentService;
    private ICompanyService $companyService;

    public function __construct(ICompanyDocumentService $service, ICompanyService $companyService)
    {
        $this->companyDocumentService = $service;
        $this->companyService = $companyService;
    }

    /**
     * Register default routes
     * @param string|null $companyDocument
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'company-documents';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [CompanyDocumentController::class, 'search']);
            Route::get($root . '/{id}', [CompanyDocumentController::class, 'getSingleObject']);
            Route::post($root, [CompanyDocumentController::class, 'create']);
            Route::put($root . '/{id}', [CompanyDocumentController::class, 'update']);
            Route::delete($root . '/{id}', [CompanyDocumentController::class, 'delete']);

            Route::post($root . '/import', [CompanyDocumentController::class, 'import']);
            Route::get($root . '/file/{slug}', [CompanyDocumentController::class, 'getFile'])->where('slug', '.*');
        }
    }

    public function getService(): IService
    {
        return $this->companyDocumentService;
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
        return CompanyDocumentResource::class;
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
                $vRequest = CompanyDocumentSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = CompanyDocumentCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = CompanyDocumentUpdateRequest::createFrom($request);
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
    public function import(CompanyDocumentImportRequest $request): mixed
    {
        $root = self::DEFAULT_FOLDER_UPLOAD_FILE;
        $company_id = $request->company_id;
        $year = $request->year;
        $is_contract = $request->is_contract ?? 1;
        # Send response using the predefined format
        $response = ApiResponse::v1();

        if ($request->hasFile('file')) {
            # Check company
            $com = $this->companyService->getSingleObject($company_id);
            if (empty($com)) throw new ActionFailException(code: ErrorCodes::ERR_RECORD_NOT_FOUND);
            $company_taxcode = $com->tax_code;
            $date = date('Ymd_His');

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
                    'name' => "Doc_{$year}_{$date}",
                    'year' => $year,
                    'file' => $result,
                    'is_contract' => $is_contract,
                ];

                $record = $this->companyDocumentService->create($params, $this->getCurrentMetaInfo());
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

        // $response = Response::make($file, 200);
        // $response->header("Content-Type", $type);

        # TODO: convert data:base64
        $fileBase64Data = base64_encode($file);
        $fileBase64Uri = "data:$type;base64,$fileBase64Data";
        return $this->getResponseHandler()->send([
            'type' => $type,
            'uri' => $fileBase64Uri,
            'slug' => $slug,
        ]);
    }
}
