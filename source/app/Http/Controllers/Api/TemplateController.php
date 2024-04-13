<?php

namespace App\Http\Controllers\Api;

use App\DataResources\Template\TemplateResource;
use App\Exceptions\Business\ActionFailException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\ErrorCodes;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Utils\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Template\TemplateCreateRequest;
use App\Http\Requests\Template\TemplateSearchRequest;
use App\Http\Requests\Template\TemplateUpdateRequest;
use App\Services\Company\ICompanyService;
use App\Services\IService;
use App\Services\Template\ITemplateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class TemplateController extends ApiController
{
    use DefaultRestActions;

    private ITemplateService $templateService;

    public function __construct(ITemplateService $service)
    {
        $this->templateService = $service;
    }

    /**
     * Register default routes
     * @param string|null $Template
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'templates';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [TemplateController::class, 'search']);
            Route::get($root . '/{id}', [TemplateController::class, 'getSingleObject']);
            Route::post($root, [TemplateController::class, 'create']);
            Route::put($root . '/{id}', [TemplateController::class, 'update']);
            Route::delete($root . '/{id}', [TemplateController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->templateService;
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
        return TemplateResource::class;
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
                $vRequest = TemplateSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = TemplateCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = TemplateUpdateRequest::createFrom($request);
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
