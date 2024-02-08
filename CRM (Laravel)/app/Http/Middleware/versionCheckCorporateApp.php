<?php

namespace App\Http\Middleware;

use Closure;

class versionCheckCorporateApp
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
        if(!$request->has('version') && empty($request->version)){
            return response()->json(['code' => VALIDATION_ERROR,'msg' => "version is required","data"=>""
            ], VALIDATION_ERROR);
        }else if(!in_array($request->version, APP_CURRENT_VERSION_CORPORATE_APP)){
            return response()->json(['code' => VALIDATION_ERROR,'msg' => trans('message.APP_VERSION_UPDATE'),'link'=>ANDROID_APP_LINK,"data"=>""
            ], VALIDATION_ERROR);
        }
        return $next($request);
    }
}
