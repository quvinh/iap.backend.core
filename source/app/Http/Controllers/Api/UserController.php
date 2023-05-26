<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Services\IService;
use App\Services\User\IUserService;
use Illuminate\Http\Request;
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
        // if ($role == UserRoles::USER) {
        //     Route::get($root.'/{id}', [CartController::class, 'getSingleObject']);
        //     Route::post($root, [CartController::class, 'create']);
        //     Route::put($root, [CartController::class, 'update']);
        //     Route::delete($root.'/{id}', [CartController::class, 'delete']);
        // }
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [UserController::class, 'search']);
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
        return CartResource::class;
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
                // $searchRequest = CartSearchRequest::createFrom($request);
                // $searchRequest->addField('user_id');
                // $inputs = array_merge($searchRequest->input(), ['user_id' => auth()->user()->getAuthIdentifier()]);
                // $searchRequest->replace($inputs);
                // $searchRequest->validate();
                // return $searchRequest;
            case 'create':
            case 'update':
                // $faqRequest = CartRequest::createFrom($request);
                // $inputs = $faqRequest->input();
                // $inputs = array_merge($inputs, [
                //         'id' => array_merge($inputs['id'], ['required']),
                //         'user_id' => auth()->user()->getAuthIdentifier()]
                // );
                // $faqRequest->addField('user_id');
                // $faqRequest->replace($inputs);
                // $faqRequest->validate();
                // return $faqRequest;
            case 'getSingleObject':
            case 'delete':
                $inputs = array_merge($request->input(), ['id' => auth()->user()->getAuthIdentifier()]);
                $request->replace($inputs);
                return $request;
            default:
                return $request;
        }
    }
}
