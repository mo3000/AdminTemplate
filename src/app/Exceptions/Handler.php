<?php

namespace App\Exceptions;

use App\Utils\JsonResponse;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    private function response(JsonResponse $response)
    {
        return response()
            ->json($response->toArray())
            ->header('Access-Control-Allow-Origin', '*')
            ->header("Access-Control-Allow-Headers",
                "x-requested-with,Content-Type,Bearer-Token");
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
	    if ($exception instanceof AuthenticationException) {
		    return $this->response(new JsonResponse(-2, $exception->getMessage()));
	    } else if ($exception instanceof AuthorizationException) {
		    return $this->response(new JsonResponse(-3, '无权限查看或操作此功能'));
	    } else if ($exception instanceof ValidationException) {
    		return $this->response(new JsonResponse(-4, '', $exception->errors()));
	    } else {
	        dd($exception);
	        return $this->response(new JsonResponse(-1, $exception->getMessage()));
        }
        return parent::render($request, $exception);
    }
}
