<?php

namespace App\Http\Controllers\Api;

use App\DataResources\User\UserResource;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserSearchRequest;
use App\Http\Requests\User\UserSingleRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Services\IService;
use App\Services\User\IUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class UserController extends ApiController
{
    use DefaultRestActions;

    private IUserService $userService;

    public function __construct(IUserService $service)
    {
        $this->userService = $service;
    }

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'users';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [UserController::class, 'search']);
            Route::get($root . '/{id}', [UserController::class, 'getSingleObject']);
            Route::post($root, [UserController::class, 'create']);
            Route::put($root . '/{id}', [UserController::class, 'update']);
            Route::delete($root . '/{id}', [UserController::class, 'delete']);
        }
    }

    public function getService(): IService
    {
        return $this->userService;
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
        return UserResource::class;
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
                $vRequest = UserSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = UserCreateRequest::createFrom($request);
                // $inputs = $vRequest->input();
                // $inputs = array_merge($inputs, [
                //     'password' => Hash::make(md5('password')) // default password
                // ]);
                // $vRequest->addField('password');
                // $vRequest->replace($inputs);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = UserUpdateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'getSingleObject':
                return $request;
            case 'delete':
                // $inputs = array_merge($request->input(), ['id' => auth()->user()->getAuthIdentifier()]);
                // $request->replace($inputs);
                // return $request;
                return $request;
            default:
                return $request;
        }
    }
}
