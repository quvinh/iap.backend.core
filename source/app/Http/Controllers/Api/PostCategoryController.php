<?php

namespace App\Http\Controllers\Api;

use App\DataResources\PostCategory\PostCategoryResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\PostCategory\PostCategoryCreateRequest;
use App\Http\Requests\PostCategory\PostCategorySearchRequest;
use App\Http\Requests\PostCategory\PostCategoryUpdateRequest;
use App\Services\IService;
use App\Services\PostCategory\IPostCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PostCategoryController extends ApiController
{
    use DefaultRestActions;

    private IPostCategoryService $postCategoryService;

    public function __construct(IPostCategoryService $service)
    {
        $this->postCategoryService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'post-categories';
        if (in_array($role, UserRoles::getValues())) {
            Route::post($root . '/search', [PostCategoryController::class, 'search']);
            Route::get($root . '/{id}', [PostCategoryController::class, 'getSingleObject']);
        }

        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root, [PostCategoryController::class, 'create']);
            Route::put($root . '/{id}', [PostCategoryController::class, 'update']);
            Route::delete($root . '/{id}', [PostCategoryController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->postCategoryService;
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
        return true;
    }

    public function getDataResourceClass(): string
    {
        return PostCategoryResource::class;
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
                $vRequest = PostCategorySearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = PostCategoryCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = PostCategoryUpdateRequest::createFrom($request);
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
