<?php

namespace App\Http\Middleware;

use App\Admin;
use App\Service\User\CurrentAdmin;
use App\Utils\Auth\AuthHelper;
use Carbon\Carbon;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AdminAuth {
	private $noAuthRoutes = [
		'api/auth/user/login',
//		'api/auth/user/logout',
		'api/auth/menus'
	];

	public function nextCall($request, $next)
	{
		return $next($request)
			->header('Access-Control-Allow-Origin', '*')
			->header("Access-Control-Allow-Headers", "Content-Type");
	}

	public function handle(Request $request, Closure $next)
	{
		if (!in_array($request->path(), $this->noAuthRoutes)
		    && substr($request->path(), 0, strlen('api/internal/') != 'api/internal/')) {
			if (env('USER_FAKE')) {
				$adminService = new CurrentAdmin(1);
				Admin::setCurrentAdmin($adminService);
				return $this->nextCall($request, $next);
			} else {
				$auth = new AuthHelper();
				$jwtToken = $auth->parse($request->input('token'));
				throw_if(
					$jwtToken->getClaim('expire_in') < Carbon::now()->subHours(8),
					AuthenticationException::class,
					'登录已过期，请重新登录'
				);
				$adminService = new CurrentAdmin($jwtToken->getClaim('userid'));
				throw_if(
					$jwtToken->getClaim('authcode') != $adminService->getAuthcode(),
					AuthenticationException::class,
					'登录已失效, 请重新登录'
				);
				if (!$adminService->getAdmin()->hasRole('superadmin')) {
					throw_if(
						!$adminService->getAdmin()->hasPermission(
							substr(Route::currentRouteAction(), strlen('App\Http\Controllers\\'))),
						AuthorizationException::class
					);
				}
				Admin::setCurrentAdmin($adminService);
			}
		}
		return $this->nextCall($request, $next);
	}
}