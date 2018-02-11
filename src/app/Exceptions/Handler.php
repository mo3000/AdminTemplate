<?php

namespace App\Exceptions;

use App\Utils\Format\JsonResponse;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
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
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
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
    		return $this->jsonResponse(new JsonResponse(-2, $exception->getMessage()));
	    } else if ($exception instanceof AuthorizationException) {
    		return $this->jsonResponse(new JsonResponse(-3, '无权限查看或操作此功能'));
	    } else if ($exception instanceof ValidationException) {
		    return $this->jsonResponse(new JsonResponse(-4, '', $exception->errors()));
	    }
        return parent::render($request, $exception);
    }

    public function jsonResponse(\JsonSerializable $object)
    {
    	return response()
		    ->json($object)
		    ->header('Access-Control-Allow-Origin', '*')
		    ->header("Access-Control-Allow-Methods" , "GET,POST,PUT,DELETE,OPTIONS")
		    ->header("Access-Control-Allow-Headers","Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    }
}
