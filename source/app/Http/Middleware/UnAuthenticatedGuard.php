<?php

namespace App\Http\Middleware;

use App\Exceptions\Business\ActionFailException;
use App\Helpers\AuthorizationSubject;
use App\Helpers\Enums\ErrorCodes;
use Closure;
use App\Models\User;

class UnAuthenticatedGuard extends Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
            $user = auth()->user();
            $blocked = false;
            if ($user instanceof User) {
                $blocked = true;
            } elseif ($user instanceof AuthorizationSubject && !$user->isAnonymous()) {
                $blocked = true;
            }

            if ($blocked) throw new ActionFailException(ErrorCodes::ERR_INVALID_URL);
        }catch (\Exception $ex) {
            // TODO: Do nothing
        }
        return $next($request);
    }
}
