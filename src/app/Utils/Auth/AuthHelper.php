<?php

namespace App\Utils\Auth;

use App\Admin;
use App\User;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;

class AuthHelper {
	public function parse(?string $token) : Token
	{
		if (empty($token)) {
			throw new AuthenticationException('用户未登录');
		}
		try {
			$jwtToken = (new Parser())->parse($token);
			$verify = $jwtToken->verify((new Sha256()), env('APP_SECURE_TOKEN'));
		} catch (\BadMethodCallException | \InvalidArgumentException $e) {
			$verify = false;
		}
		throw_if(!$verify, AuthenticationException::class, '登录错误');
		return $jwtToken;
	}

	public function signAdmin(Admin $admin) : Token
	{
		$signer = new Sha256();
		$authcode = uniqid();
		$admin->authcode = $authcode;
		$admin->save();
		$token = (new Builder())
			->set('userid', $admin->id)
			->set('authcode', $authcode)
			->set('expire_in', Carbon::now()->addHours(8))
			->sign($signer, env('APP_SECURE_TOKEN'))
			->getToken();
		return $token;
	}

	public function signUserWechatWeb(User $user) : Token
	{
		$signer = new Sha256();
		$user->save();
		$token = (new Builder())
			->set('userid', $user->id)
			->sign($signer, env('APP_SECURE_TOKEN'))
			->getToken();
		return $token;
	}
}