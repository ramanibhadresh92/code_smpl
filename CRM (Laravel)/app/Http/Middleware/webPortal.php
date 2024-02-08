<?php

namespace App\Http\Middleware;

use Closure;

class webPortal
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
		if ($request->header('Authorization') != WEBPORTL_CRED) {
			return response()->json(["code" => ERROR , "msg" =>"Not Authorized","data" => array("Auth"=>$request->header('Authorization'),"WEBPORTL_CRED"=>WEBPORTL_CRED)]);
		}
		return $next($request);
	}
}
