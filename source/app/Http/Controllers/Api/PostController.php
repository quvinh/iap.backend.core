<?php

namespace App\Http\Controllers\Api;

use App\DataResources\Post\PostResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\Post\PostCreateRequest;
use App\Http\Requests\Post\PostSearchRequest;
use App\Http\Requests\Post\PostUpdateRequest;
use App\Services\IService;
use App\Services\Post\IPostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PostController extends ApiController
{
    use DefaultRestActions;

    private IPostService $postService;

    public function __construct(IPostService $service)
    {
        $this->postService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'posts';
        if (in_array($role, UserRoles::getValues())) {
            Route::post($root . '/search', [PostController::class, 'search']);
            Route::get($root . '/{id}', [PostController::class, 'getSingleObject']);
        }

        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root, [PostController::class, 'create']);
            Route::put($root . '/{id}', [PostController::class, 'update']);
            Route::delete($root . '/{id}', [PostController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->postService;
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
        return PostResource::class;
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
                $vRequest = PostSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = PostCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = PostUpdateRequest::createFrom($request);
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
