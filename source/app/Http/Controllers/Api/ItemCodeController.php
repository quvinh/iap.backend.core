<?php

namespace App\Http\Controllers\Api;

use App\DataResources\ItemCode\ItemCodeResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\ItemCode\ItemCodeCreateRequest;
use App\Http\Requests\ItemCode\ItemCodeImportRequest;
use App\Http\Requests\ItemCode\ItemCodeSearchRequest;
use App\Http\Requests\ItemCode\ItemCodeUpdateRequest;
use App\Services\IService;
use App\Services\ItemCode\IItemCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

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
     * @param string|null $ItemCode
     * @return void
     */
    public static function registerRoutes(string $ItemCode = null): void
    {
        $root = 'item-codes';
        if ($ItemCode == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [ItemCodeController::class, 'search']);
            Route::get($root . '/{id}', [ItemCodeController::class, 'getSingleObject']);
            Route::post($root, [ItemCodeController::class, 'create']);
            Route::put($root . '/{id}', [ItemCodeController::class, 'update']);
            Route::delete($root . '/{id}', [ItemCodeController::class, 'delete']);
            Route::delete($root . '/force/{id}', [ItemCodeController::class, 'forceDelete']);

            Route::post($root . '/import', [ItemCodeController::class, 'import']);
        }
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
}
