<?php

namespace app\Http\Middleware;

use App\Admin;
use App\Service\User\CurrentUser;
use Closure;
use Illuminate\Http\Request;

class AdminAuth {
	private $noAuthRoutes = ['api/login'];

	public function nextCall($request, $next) {
		return $next($request)
			->header('Access-Control-Allow-Origin', '*')
			->header("Access-Control-Allow-Headers","Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	}

	public function handle(Request $request, Closure $next) {
		if (in_array($request->path(), $this->noAuthRoutes)
		    || substr($request->path(), 0, strlen('api/sync/') == 'api/sync/')) {
			return $this->nextCall($request, $next);
		} else {
			if (env('USER_FAKE')) {
				$adminService = new CurrentUser(1);
				Admin::setCurrentUser($adminService);
				return $this->nextCall($request, $next);
			} else {

			}
		}
	}
}