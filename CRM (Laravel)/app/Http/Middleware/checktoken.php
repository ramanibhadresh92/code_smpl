<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use App\Models\AdminTransaction;
use App\Models\AdminUserRights;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Illuminate\Support\Facades\Route;
use App\Models\UserTokenInfo;
use Carbon\Carbon;
use Log;
class checktoken
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
		$token = $request->bearerToken();
		/* EXPIRE USER TOKEN IF USER IDEL FOR MORE THEN 3 HOURS - 20 AUG 2019*/
		$Info = UserTokenInfo::where("adminuserid",Auth()->user()->adminuserid)->first();
		if($Info) {
			$date 	= $Info->updated_at;
			$date 	= Carbon::parse($date);
			$now 	= Carbon::now()->toDateTimeString();
			$Diff 	= $date->diffInSeconds($now);

			if($Diff > TOKEN_EXPIRE_SECONDS) {
				JWTAuth::manager()->invalidate(new \Tymon\JWTAuth\Token($token), $forceForever = false);
				$Info->delete();
				return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_EXPIRED'),'data'=>'']);
			}
			$user 				= UserTokenInfo::firstOrNew(array('adminuserid' => Auth()->user()->adminuserid));
			$user->token 		= $token;
			$user->updated_at   = date("Y-m-d H:i:s");
			$user->ip 			= $request->ip();
			$user->save();
		}else{
			$user 			= UserTokenInfo::firstOrNew(array('adminuserid' => Auth()->user()->adminuserid));
			$user->token 	= $token;
			$user->ip 		= $request->ip();
			$user->save();
		}
		return $next($request);
	}
}
