<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Mail;
use Throwable;

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
    public function report(Throwable $exception)
    {
        if ($exception instanceof \Exception) {
            // emails.exception is the template of your email
            // it will have access to the $error that we are passing below

            if ($exception instanceof
                  \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                 return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_EXPIRED'),'data'=>'','type'=> TYPE_ERROR],CODE_UNAUTHORISED);
            } else if ($exception instanceof
                          \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
               return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_INVALID'),'data'=>'','type'=> TYPE_ERROR],401);
            } else if ($exception instanceof
                     \Tymon\JWTAuth\Exceptions\TokenBlacklistedException) {
                return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_BLACK_LISTED'),'data'=>'','type'=> TYPE_ERROR]);
            }else if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException){
              return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_INVALID'),'data'=>'','type'=> TYPE_ERROR],CODE_UNAUTHORISED);
            }else{
                if($exception->getCode() != 0){
                    Mail::send('errorreport', ['e' => $exception], function ($m) {
                     $m->to(ERROR_LOG_EMAILS)
                     // ->cc(env('SEND_ERROR_REPORT_CC'))
                     ->subject('LR - ERROR FROM PRODUCTION!!');
                    });
                }
                
            }
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {

        // $code = $exception->getStatusCode();
        // if($code == 500){
        //     return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>trans('message.SOMETHING_WENT_WRONG'),"data" =>"axay"],500);
        // }
        if ($exception) {

            // return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>trans('message.SOMETHING_WENT_WRONG'),"data" =>""],500);
        }


        if ($exception instanceof
                  \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                 return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_EXPIRED'),'data'=>'','type'=> TYPE_ERROR],CODE_UNAUTHORISED);
            } else if ($exception instanceof
                          \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
               return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_EXPIRED'),'data'=>'','type'=> TYPE_ERROR],401);
            } else if ($exception instanceof
                     \Tymon\JWTAuth\Exceptions\TokenBlacklistedException) {
                return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_BLACK_LISTED'),'data'=>'','type'=> TYPE_ERROR]);
           }else if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException){
              return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_INVALID'),'data'=>'','type'=> TYPE_ERROR],CODE_UNAUTHORISED);
           }


        return parent::render($request, $exception);
    }
}
