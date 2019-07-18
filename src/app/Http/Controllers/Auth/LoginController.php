<?php

namespace App\Http\Controllers\Auth;

use App\Utils\JsonResponse;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
	use ThrottlesLogins;

	protected function username()
	{
		return 'username';
	}

	protected function attemptLogin(Request $request)
	{
		return $this->guard()->attempt(
			$this->credentials($request), $request->filled('remember')
		);
	}

	protected function guard()
	{
		return Auth::guard();
	}

	protected function credentials(Request $request)
	{
		return $request->only($this->username(), 'password');
	}

	protected function sendLoginResponse(Request $request)
	{
		$this->clearLoginAttempts($request);

		return $this->authenticated($request, $this->guard()->user())
			?: redirect()->intended($this->redirectPath());
	}

	protected function authenticated(Request $request, $user)
	{
		//
	}

	protected function sendFailedLoginResponse(Request $request)
	{
		return new JsonResponse(-1, trans('auth.failed'));
	}

	public function login(Request $request)
	{
		return new JsonResponse(0);
		$this->validate($request, [
				$this->username() => 'required',
				'password' => 'required',
			]
		);

		if ($this->hasTooManyLoginAttempts($request)) {
			$this->fireLockoutEvent($request);
			$this->sendLockoutResponse($request);
		}

		if ($this->attemptLogin($request)) {
			return $this->sendLoginResponse($request);
		}

		$this->incrementLoginAttempts($request);

		return $this->sendFailedLoginResponse($request);
	}

	public function logout(Request $request)
	{
		$this->guard()->logout();

		return $this->loggedOut($request);
	}

	protected function loggedOut(Request $request)
	{
		//
	}
}
