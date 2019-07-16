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
            return response()
                ->header('Access-Control-Allow-Origin', '*')
                ->header("Access-Control-Allow-Headers",
                    "x-requested-with,Content-Type,Bearer-Token");
        }


        $call = $next($request);

        $ref = new \ReflectionClass(get_class($call));
        if ($ref->hasMethod('header')) {
            return $call
                ->header('Access-Control-Allow-Origin', '*')
                ->header("Access-Control-Allow-Headers",
                    "x-requested-with,Content-Type,Bearer-Token");
        }

        return $call;
    }
}
