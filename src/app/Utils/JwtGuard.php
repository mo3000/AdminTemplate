<?php


namespace App\Utils;


use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;

class JwtGuard implements Guard
{
	use GuardHelpers;

	protected $inputKey = 'token';

	public function user()
	{
		if (!empty($this->user)) {
			return $this->user;
		}
		$token = $this->getTokenForRequest();
		$user = null;
		if (!empty($token)) {
			$user = $this->provider->retrieveById($token->getClaim('id'));
		}
		return $this->user = $user;
	}

	public function getTokenForRequest() : ? Token
	{
		$request = resolve(Request::class);

		if ($request->filled($this->inputKey)) {
			$token = $request->input($this->inputKey);
		}

		if (empty($token)) {
			$token = $request->bearerToken();
		}

		if (!empty($token)) {
			try {
				$jwtToken = (new Parser())->parse($token);
				if ($jwtToken->verify(
					(new Sha256()), config('auth.secretkey'))) {
					return $jwtToken;
				}
			} catch (\BadMethodCallException | \InvalidArgumentException $e) {

			}
		}

		return null;
	}

	public function validate(array $credentials = [])
	{
		return true;
	}

}