<?php

namespace App\Http\Middleware;

use App\Exceptions\Business\AuthorizationIsInvalid;
use App\Helpers\Enums\ErrorCodes;
use App\Helpers\Utils\RequestHelper;
use App\Services\Auth\IAuthService;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AppChannel
{
    private IAuthService $authService;
    public function __construct(IAuthService $authService){
        $this->authService = $authService;
    }

    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');
        if (is_null($header)) throw new AuthorizationIsInvalid(ErrorCodes::ERR_AUTHORIZATION_HEADER_NOT_FOUND);
        $parts = explode(' ', $header);
        if (count($parts) != 2) throw new AuthorizationIsInvalid(ErrorCodes::ERR_INVALID_AUTHORIZATION);
        switch (strtoupper($parts[0])) { // Authorization type
            case 'IAP': // Custom authorization type
                if ($this->isValidHandshakeCode($request, $parts[1])) return $next($request);
                break;
            case 'BEARER':
                if ($this->isValidToken($request, $parts[1])) return $next($request);
                break;
            default:
                throw new AuthorizationIsInvalid(ErrorCodes::ERR_INVALID_AUTHORIZATION);
        }
        throw new AuthorizationIsInvalid(ErrorCodes::ERR_INVALID_AUTHORIZATION);
    }

    /**
     * Validate handshake token
     * @param string $code
     * @return false|void
     */
    private function isValidHandshakeCode(Request $request, string $code)
    {
        $claims = $this->authService->handshake($code, RequestHelper::getMetaInfo(null));
        if (is_null($claims)) return false;
        return true;
    }

    /**
     * Validate authorization token
     * @param string $int
     * @return bool
     */
    private function isValidToken(Request $request, string $int)
    {
        try{
            $claims = RequestHelper::getClaims();
            if (is_null($claims)) return false; // expired token
            $meta = RequestHelper::getMetaInfo();
            $hook = RequestHelper::findHanshakeHook($claims->getByClaimName('cnidh')->getValue());
            if (is_null($hook)) return false; // the hook value is dismissed
            $identifier = $meta->identifier;
            $currentConnectionHash = Hash::make($hook['value'].$identifier);
            $handshake = $this->authService->handshake($currentConnectionHash, $meta);
            if (is_null($handshake)) return false; // the handshake information is invalid
            $claimSign = $claims->getByClaimName('sign')->getValue();
            $curSign = $handshake->getJWTCustomClaims()['sign'];
            if ($claimSign !== $curSign) return false; // the handshake information is invalid
            return true;
        } catch (Exception $e){
            return false;
        }
    }
}
