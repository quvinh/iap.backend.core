<?php

namespace App\Http\Middleware;

use App\Exceptions\Business\NoPermissionException;
use App\Models\User;
use Closure;

class AuthenticateAdminGuard extends Authenticate
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
        $this->authenticate($request, $guards);
        $user = auth()->user();
        if (! $user instanceof User) throw new NoPermissionException();
        return $next($request);
    }
}
