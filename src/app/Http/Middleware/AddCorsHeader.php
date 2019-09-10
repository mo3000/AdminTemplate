<?php

namespace App\Http\Middleware;

use Closure;

class AddCorsHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('options')) {
            return response()->json()
                ->header('Access-Control-Allow-Origin', '*')
                ->header("Access-Control-Allow-Headers",
                    "x-requested-with,Content-Type,Authorization");
        }
        $response = $next($request);
        $refclass = new \ReflectionClass($response);
        if ($refclass->hasMethod('header')) {
            $response->header('Access-Control-Allow-Origin', '*')
                ->header("Access-Control-Allow-Headers",
                    "x-requested-with,Content-Type,Authorization");
        }
        return $response;
    }
}
