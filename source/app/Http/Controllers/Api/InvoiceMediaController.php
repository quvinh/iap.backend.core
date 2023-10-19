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
use App\Services\IService;
use App\Services\InvoiceMedia\IInvoiceMediaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class InvoiceMediaController extends ApiController
{
    use DefaultRestActions;

    private const DEFAULT_FOLDER_UPLOAD_FILE = 'upload/pdf';
    private IInvoiceMediaService $invoiceMediaService;

    public function __construct(IInvoiceMediaService $service)
    {
        $this->invoiceMediaService = $service;
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

    public function importPDF(InvoiceMediaCreateRequest $request): mixed
    {

        # Send response using the predefined format
        $response = ApiResponse::v1();

        if ($request->hasFile('file')) {
            // $id = preg_replace('/-/', '', uuid_create());
            // $id = $id.'.'.$request->file('file')->extension();
            // $path = Carbon::now()->format('Ymd');
            // if ($file = $request->file('file')->storePubliclyAs($path, $id, StorageHelper::CLOUD_DISK_NAME)) {
            //     $url = Storage::disk(StorageHelper::CLOUD_DISK_NAME)->url($file);
            //     $url = preg_replace('/\/'.$path.'\//', '/', $url);
            //     return $response->send($id);
            // }
            // throw new ActionFailException(code: ErrorCodes::ERROR_CANNOT_UPLOAD_FILE);
            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('file')->getClientOriginalExtension();
            $fileNameToStore = uuid_create() . '.' . $extension;

            $storage = Storage::disk(StorageHelper::CLOUD_DISK_NAME);
            $checkDirectory = $storage->exists(self::DEFAULT_FOLDER_UPLOAD_FILE);
            if (!$checkDirectory) {
                $storage->makeDirectory(self::DEFAULT_FOLDER_UPLOAD_FILE);
            }
            $result = $storage->put(self::DEFAULT_FOLDER_UPLOAD_FILE . '/' . $fileNameToStore, $request->file('file'), 'public');
        }
        
        return $response->send($result);
    }
}
