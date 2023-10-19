<?php

namespace App\Http\Controllers\Api;

use App\DataResources\InvoiceMedia\InvoiceMediaResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\InvoiceMedia\InvoiceMediaCreateRequest;
use App\Http\Requests\InvoiceMedia\InvoiceMediaSearchRequest;
use App\Http\Requests\InvoiceMedia\InvoiceMediaUpdateRequest;
use App\Services\IService;
use App\Services\InvoiceMedia\IInvoiceMediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class InvoiceMediaController extends ApiController
{
    use DefaultRestActions;

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
}
