<?php

namespace App\Http\Middleware;
use Closure;
use JWTFactory;
use JWTAuth;
class AssignGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if($guard != null){
            auth()->shouldUse($guard);
        }
        
        

        try {

            $token = $request->bearerToken();
            $iat = JWTAuth::setToken($token)->getPayload()->get('iat');
            //echo date('Y-m-d H:i',$iat) .'!='. date('Y-m-d H:i');exit;
            if(date('Y-m-d',$iat) != date('Y-m-d')){
                    JWTAuth::invalidate($request->bearerToken());
            }else{
                
            }

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

       return $next($request);
    }
}