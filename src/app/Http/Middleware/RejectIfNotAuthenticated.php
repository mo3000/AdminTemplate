<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class RejectIfNotAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @throws AuthenticationException
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (request()->getPathInfo() != '/admin/login' && ! Auth::guard($guard)->check()) {
            throw new AuthenticationException('用户未登录');
        }

        return $next($request);
    }
}
