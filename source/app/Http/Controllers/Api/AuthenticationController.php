<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Exceptions\Business\AuthorizationIsInvalid;
use App\Exceptions\DB\RecordIsNotFoundException;
use App\Helpers\Enums\ErrorCodes;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Utils\RequestHelper;
use App\Http\Controllers\Traits\ResponseHandlerTrait;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Requests\User\UserChangePasswordRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\Role;
use App\Services\Auth\IAuthService;
use App\Services\User\IUserService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class AuthenticationController extends ApiController
{
    use ResponseHandlerTrait;
    private IAuthService $authService;
    private IUserService $userService;

    public function __construct(IAuthService $authService, IUserService $userService)
    {
        $this->userService = $userService;
        $this->authService = $authService;
    }

    /**
     * Register default routes
     * @param UserRoles|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'auth';
        if ($role == UserRoles::ANONYMOUS) {
            Route::get($root . '/handshake', [AuthenticationController::class, 'handshake']);
            Route::post($root . '/login', [AuthenticationController::class, 'login']);
            Route::post($root . '/forgot-password', [AuthenticationController::class, 'forgotPassword']);
        } else {
            Route::get($root . '/profile', [AuthenticationController::class, 'profile']);
            Route::put($root . '/profile', [AuthenticationController::class, 'updateProfile']);
            Route::put($root . '/change-password', [AuthenticationController::class, 'changePassword']);
            Route::get($root . '/logout', [AuthenticationController::class, 'logout']);
            Route::post($root . '/refresh', [AuthenticationController::class, 'refresh'])->withoutMiddleware(['auth.channel']);
        }
    }

    /**
     * Handshake before any communication.
     * This action send a welcome message with an alias name like, 'Hello, <alias name>'
     * @param Request $request
     * @return Response send an 'alias name' to client
     * @throws AuthorizationIsInvalid
     */
    public function handshake(Request $request): Response
    {
        # 1. load payload
        $header = $request->header('Authorization');
        $parts = explode(' ', $header);
        if (count($parts) != 2 && $parts[0] != 'IAP') throw new AuthorizationIsInvalid(ErrorCodes::ERR_INVALID_AUTHORIZATION);
        # 2. call main process
        $sub = $this->authService->handshake($parts[1], $this->currentMetaInfo());
        if ($token = auth()->claims($sub->getJWTCustomClaims())->attempt([])) {
            # 3. return result
            return $this->createNewToken($token);
        }
        throw new AuthorizationIsInvalid(ErrorCodes::ERR_INVALID_AUTHORIZATION);
    }

    /**
     * Return the user information corresponding with the given token
     */
    public function profile(Request $request): Response
    {
        $sub = auth()->user();
        if (!isset($sub) || !$sub->getAuthIdentifier()) {
            throw new AuthorizationIsInvalid(ErrorCodes::ERR_INVALID_AUTHORIZATION);
        }
        $companyIds = $this->userService->findByCompanies($sub->getAuthIdentifier());
        $sub->companies = array_map(function ($item) {
            return $item['company_id'];
        }, $companyIds);
        # 3. return result
        $response = ApiResponse::v1();
        return $response->send($sub);
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request): Response
    {
        $id = auth()->user()->getAuthIdentifier();
        $request->merge(['id' => $id]);
        $vRequest = UserUpdateRequest::createFrom($request);
        $vRequest->validate();
        $this->userService->update($id, $vRequest->all());
        # Return result
        $response = ApiResponse::v1();
        return $response->send(['status' => true]);
    }

    /**
     * Change password for profile
     */
    public function changePassword(UserChangePasswordRequest $request): Response
    {
        $id = auth()->user()->getAuthIdentifier();
        $this->userService->changePassword($id, $request->all());
        # Return result
        $response = ApiResponse::v1();
        return $response->send(['status' => true]);
    }

    /**
     * Forgot password
     */
    public function forgotPassword(ForgotPasswordRequest $request): Response
    {
        # Handshake
        $claims = RequestHelper::getClaims();
        $meta = RequestHelper::getMetaInfo();
        $hook = RequestHelper::findHanshakeHook($claims->getByClaimName('cnidh')->getValue());
        if (is_null($hook)) return response('Handshake failed', 404); //throw new AuthorizationIsInvalid();
        $identifier = $meta->identifier;
        $currentConnectionHash = Hash::make($hook['value'] . $identifier);
        $handshake = $this->authService->handshake($currentConnectionHash, $meta);

        if (is_null($handshake)) return response('Handshake failed', 404); //throw new AuthorizationIsInvalid();

        # Forgot password
        $this->userService->forgotPassword($request->email);
        # Return result
        $response = ApiResponse::v1();
        return $response->send(['status' => true]);
    }

    /**
     * @param LoginRequest $request
     * @return Response
     * @throws AuthorizationIsInvalid
     */
    public function login(LoginRequest $request): Response
    {
        # 1. get payload
        $request->validate();
        $payload = $request->input();
        $claims = RequestHelper::getClaims();
        $meta = RequestHelper::getMetaInfo();
        $hook = RequestHelper::findHanshakeHook($claims->getByClaimName('cnidh')->getValue());
        if (is_null($hook)) return response('Login failed', 404); //throw new AuthorizationIsInvalid();
        $identifier = $meta->identifier;
        $currentConnectionHash = Hash::make($hook['value'] . $identifier);
        $handshake = $this->authService->handshake($currentConnectionHash, $meta);

        if (is_null($handshake)) return response('Login failed', 404); //throw new AuthorizationIsInvalid();
        # 2. login
        if (!$token = auth()->claims($handshake->getJWTCustomClaims())->attempt($payload)) {
            return response('Login failed', 404);
            // throw new AuthorizationIsInvalid(ErrorCodes::ERR_INVALID_CREDENTIALS);
        }

        return $this->createNewToken($token);
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return Response
     */
    protected function createNewToken(string $token): Response
    {
        $ret = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ];

        $getAuthIdentifier = auth()->user()->getAuthIdentifier();
        if ($getAuthIdentifier) {
            $user = auth()->user();
            $role = Role::find(auth()->user()->role_id ?? null);
            $companyIds = $this->userService->findByCompanies($getAuthIdentifier);
            $ret = array_merge($ret, [
                'name' => $user->name,
                'username' => $user->username,
                'role' => $role->name,
                'permissions' => $role->getIdOfPermissions(),
                'companies' => array_map(function ($item) {
                    return $item['company_id'];
                }, $companyIds),
            ]);
        }
        # 3. return result
        $response = ApiResponse::v1();
        return $response->send($ret);
    }

    /**
     * Refresh a token.
     *
     * @return Response
     */
    public function refresh(): Response
    {
        $user = auth()->user();
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Log the user out (invalidate the token)
     * @return mixed
     */
    public function logout()
    {
        auth()->logout();
        # return result
        $response = ApiResponse::v1();
        return $response->send('User successfully signed out');
    }

    /**
     * @throws AuthorizationIsInvalid
     * @throws RecordIsNotFoundException
     */
    // public function checkCredentials(Request $request)
    // {
    //     $payload = $request->input();
    //     # 2. Signup
    //     $identifier_type = $payload['identifier_type'] ?? 'email';
    //     $password = $payload['password'];
    //     if ($identifier_type == 'email') {
    //         $user = $this->userService->findByEmail($payload['email']);
    //     } else {
    //         $user = $this->userService->findByPhone($payload['phone']);
    //     }
    //     if (is_null($user)) throw new \Exception('Invalid credentials');
    //     $oldPassword = $user->old_password;
    //     try {
    //         $requestedPassword = base64_encode(hash("ripemd160", $password . $user->old_salt));
    //         if ($oldPassword !== $requestedPassword) throw new \Exception('Invalid credentials');
    //         $user->password = Hash::make(md5($password));
    //         $user->old_password = null;
    //         $user->old_salt = null;
    //         $user->save();
    //         $this->getResponseHandler()->send(true);
    //     } catch (\Exception $ex) {
    //         throw new AuthorizationIsInvalid(previous: $ex);
    //     }
    // }
}
