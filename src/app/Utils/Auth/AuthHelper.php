<?php

namespace app\Utils\Auth;

use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class AuthHelper {
	public function verifyToken(?string $token) {
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
		throw_if(
			$jwtToken->getClaim('expire_in') < Carbon::now()->subHours(8),
			AuthenticationException::class,
			'登录已过期，请重新登录'
		);
	}
}