<?php

namespace App\Http\Controllers\Api;

use App\DataResources\ItemGroup\ItemGroupResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\ItemGroup\ItemGroupCreateRequest;
use App\Http\Requests\ItemGroup\ItemGroupImportRequest;
use App\Http\Requests\ItemGroup\ItemGroupSearchRequest;
use App\Http\Requests\ItemGroup\ItemGroupUpdateRequest;
use App\Services\IService;
use App\Services\ItemGroup\IItemGroupService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class ItemGroupController extends ApiController
{
    use DefaultRestActions;

    private IItemGroupService $itemGroupService;

    public function __construct(IItemGroupService $service)
    {
        $this->itemGroupService = $service;
    }

    /**
     * Register default routes
     * @param string|null $ItemGroup
     * @return void
     */
    public static function registerRoutes(string $ItemGroup = null): void
    {
        $root = 'item-groups';
        if ($ItemGroup == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [ItemGroupController::class, 'search']);
            Route::get($root . '/{id}', [ItemGroupController::class, 'getSingleObject']);
            Route::post($root, [ItemGroupController::class, 'create']);
            Route::put($root . '/{id}', [ItemGroupController::class, 'update']);
            Route::delete($root . '/{id}', [ItemGroupController::class, 'delete']);
            Route::delete($root . '/force/{id}', [ItemGroupController::class, 'forceDelete']);

            Route::post($root . '/import', [ItemGroupController::class, 'import']);
        }
    }

    public function getService(): IService
    {
        return $this->itemGroupService;
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
        return ItemGroupResource::class;
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
                $vRequest = ItemGroupSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = ItemGroupCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = ItemGroupUpdateRequest::createFrom($request);
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
        $result = $this->itemGroupService->delete($id, false);
        return $this->getResponseHandler()->send($result);
    }
}
