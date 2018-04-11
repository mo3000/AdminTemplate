<?php

namespace App\Http\Middleware;


use Illuminate\Http\Request;

class OptionResponse {
	public function handle(Request $request, \Closure $next)
	{
		if ($request->isMethod('options')) {
			return response()
				->json(['code' => 0])
				->header('Access-Control-Allow-Origin', '*')
				->header("Access-Control-Allow-Headers", "Content-Type");
		}
		return $next($request);
	}
}