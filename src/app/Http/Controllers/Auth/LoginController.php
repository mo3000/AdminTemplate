<?php

namespace App\Http\Controllers\Auth;

use App\Models\Auth\Roles;
use App\Utils\JsonResponse;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class LoginController extends Controller
{
//    use ThrottlesLogins;

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
        Cache::put('login-'.$request->input($this->username()), 0);
        $user = $this->guard()->user();
        $roles = Roles::whereHas('admins', function ($query) use ($user) {
            $query->where('id', $user->id);
        })
            ->select('name')
            ->get()
            ->pluck('name')
            ->toArray();


        return new JsonResponse(0, '', [
            'token' => strval((new Builder())
                ->issuedAt(time())
                ->relatedTo($user->id)
                ->getToken((new Sha256()), (new Key(config('auth.token_secret_key'))))),
            'roles' => $roles,
            'realname' => $user->realname,
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        //
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        return new JsonResponse(-1, trans('auth.failed'));
    }

    private function hasTooManyLoginAttempts($request)
    {
        $username = $request->input($this->username());
        if (Cache::has("lock-$username")) {
            return true;
        }
        $times = Cache::increment("login-$username");
        $tooMany = $times >= 10;
        if ($tooMany) {
            Cache::add("lock-$username", 1, 300);
        }
        return $tooMany;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
                $this->username() => 'required',
                'password' => 'required',
            ]
        );

        if ($this->hasTooManyLoginAttempts($request)) {
            return new JsonResponse(-1, '登录次数过多被锁定');
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

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

    public function __construct()
    {
        $this->middleware('requirelogin')->except('login');
    }

}
