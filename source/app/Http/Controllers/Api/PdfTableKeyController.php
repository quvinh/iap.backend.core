<?php

namespace App\Http\Controllers\Api;

use App\DataResources\PdfTableKey\PdfTableKeyResource;
use App\Exceptions\Business\ActionFailException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\ErrorCodes;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Utils\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\PdfTableKey\PdfTableKeyCreateRequest;
use App\Http\Requests\PdfTableKey\PdfTableKeySearchRequest;
use App\Http\Requests\PdfTableKey\PdfTableKeyUpdateRequest;
use App\Services\Company\ICompanyService;
use App\Services\IService;
use App\Services\PdfTableKey\IPdfTableKeyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class PdfTableKeyController extends ApiController
{
    use DefaultRestActions;

    private IPdfTableKeyService $pdfTableKeyService;

    public function __construct(IPdfTableKeyService $service)
    {
        $this->pdfTableKeyService = $service;
    }

    /**
     * Register default routes
     * @param string|null $pdfTableKey
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'pdf-table-key';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [PdfTableKeyController::class, 'search']);
            Route::get($root . '/{id}', [PdfTableKeyController::class, 'getSingleObject']);
            Route::post($root, [PdfTableKeyController::class, 'create']);
            Route::put($root . '/{id}', [PdfTableKeyController::class, 'update']);
            Route::delete($root . '/{id}', [PdfTableKeyController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->pdfTableKeyService;
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
        return PdfTableKeyResource::class;
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
                $vRequest = PdfTableKeySearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = PdfTableKeyCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = PdfTableKeyUpdateRequest::createFrom($request);
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
}
