<?php

namespace XBlock\Kernel\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{

    protected $dontReport = [
        //
    ];


    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    public function render($request, Exception $exception)
    {
        $client_type = $request->header('client-type', 'other');
        if (in_array($client_type, ['web', 'app', 'watch']) || !env('APP_DEBUG')) {
            $Info = [
                'errorInfo' => $exception->getMessage(),
                'errorPath' => $exception->getFile(),
                'errorLine' => $exception->getLine(),
            ];
            switch ($exception) {
                case ($exception instanceof MethodNotAllowedHttpException):
                    return response()->json(message('ERROR_METHOD')->set($Info));
                case ($exception instanceof NotFoundHttpException):
                    return response()->json(message('ERROR_NO_RESOURCES'));
                case ($exception instanceof NeedApproval):
                    return response()->json(modal('SUCCESS')->info($exception->getMessage()));
                case($exception instanceof AuthenticationException):
                    return response()->json(message('NO_USER'), 200);
                case($exception instanceof NoAuthException):
                    return response()->json(message('ERROR_AUTH'));
                case($exception instanceof QueryException):
                    return response()->json(message('SQL_ERROR')->set($Info));
                case($exception instanceof UuidException):
                    return response()->json(message('ABSENCE_PARAM'));
//                    return codeConfig('ABSENCE_PARAM');
                case($exception instanceof ValidationException):
                    $error = $exception->errors();
                    $error = reset($error);
                    return response()->json(message('ERROR_PARAM')->info(reset($error)));
                default:
                    return response()->json(message('ERROR_INNER')->info($exception->getMessage()));
            }
        } else return parent::render($request, $exception);
    }


    public function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json(message('NO_USER'), 200);
    }
}
