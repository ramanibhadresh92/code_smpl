<?php

namespace App\Http\Middleware;
use Validator;
use JWTFactory;
use JWTAuth;
use App\Models\AdminTransaction;
use App\Models\AdminUserRights;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Illuminate\Support\Facades\Route;
use Closure;
use Log;
class CheckAuthorized
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
		try
		{
			JWTAuth::authenticate($request->bearerToken());
			$strPath        = ($request->route()->uri);
			$strPath        = str_replace(array(WEB_PREFIX),array(""),$strPath);
			$checkedData    = AdminTransaction::getTrnidFromPageurl($strPath);
			$userData       = auth()->user();
			$permission     = AdminUserRights::getTrnPermission($userData->adminuserid);
			if(auth()->user()->user_type != SUPERADMIN_TYPE) {
				if(!empty($checkedData) && !in_array($checkedData,$permission))
				{
					return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.UNAUTHORIZED_ACCESS_PAGE'),'data'=>'']);
				}
			}
		}
		catch(TokenExpiredException $e) {
			return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_EXPIRED'),'data'=>''], $e->getStatusCode());
		}
		catch (TokenBlacklistedException $e) {
			return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_BLACK_LISTED'),'data'=>''], $e->getStatusCode());
		}
		catch (JWTException $e) {
			return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_INVALID'),'data'=>$e], $e->getStatusCode());
		}
		return $next($request);
	}
}