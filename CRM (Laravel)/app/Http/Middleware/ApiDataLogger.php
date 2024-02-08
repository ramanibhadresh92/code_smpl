<?php

namespace App\Http\Middleware;

use Closure;
use http\Env\Request;
use http\Env\Response;
use App\Models\QuickLinksAccessLog;
class ApiDataLogger
{




	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	private $startTime;

	// public function handle($request, Closure $next)
 //    {
 //        $this->startTime = microtime(true);
 //        $data = $request->all();
 //        $response = $next($request);





 //        if ( env('API_DATALOGGER') == true && $request->method() != "OPTIONS") {
 //            $name = \Route::currentRouteName();

 //            // if($response->status() != 404) {
 //                // $name = \Request::route()->getName();
 //                // if(array_has(NO_LOG_API,\Request::route()->getName())){
 //                if (in_array($name,NO_LOG_API)) {
 //                    // \Log::info("********************".$name."****************************".$request->method());
 //                }else{
 //                    $endTime = microtime(true);
 //                    (\Auth::check()) ? $adminuserId = Auth()->user()->adminuserid : $adminuserId = 0;
 //                    \DB::table('api_data_logger')->insert(
 //                        [
 //                            "time" => $endTime,
 //                            "duration" => number_format($endTime - $this->startTime, 3),
 //                            "ip" => $request->ip(),
 //                            "url" => $request->fullUrl(),
 //                            "method" => $request->method(),
 //                            "input" => print_r($data, true),
 //                            "output" => print_r($response->content(), true),
 //                            "adminuserid" => $adminuserId,
 //                            // "route" => $name,
 //                            "created_at" => date("Y-m-d H:i:s")
 //                        ]);
 //                }
 //            // }
 //        }
 //        return $response;
 //    }

    public function handle($request, Closure $next)
    {
        $this->startTime    = microtime(true);
        $data               = $request->all();
        $response           = $next($request);
        if ( env('API_DATALOGGER') == true) {
            $RouteName = @\Request::route()->getName();
            if(isset($RouteName) && !empty($RouteName) && !in_array($RouteName, NO_LOG_API)) {
                if($response->status() != 404) {
                    $endTime = microtime(true);
                    (\Auth::check()) ? $adminuserId = Auth()->user()->adminuserid : $adminuserId = 0;
                    // \DB::table('api_data_logger')->insert(
                    // [
                    //      "time" => $endTime,
                    //      "duration" => number_format($endTime - $this->startTime, 3),
                    //      "ip" => $request->ip(),
                    //      "url" => $request->fullUrl(),
                    //      "method" => $request->method(),
                    //      "input" => print_r($data, true),
                    //      "output" => print_r($response->content(), true),
                    //      "adminuserid" => $adminuserId,
                    //      "created_at" => date("Y-m-d H:i:s")
                    // ]);
                }
            }
        }
        ########## QUICK ACCESS LOG LOGIC - 16 MAY 2022 ##########
        $ACCESS_KEY         = (isset($data['accesskey']) && !empty($data['accesskey'])) ? $data['accesskey'] : 0;
        $ADMINUSERID        = (\Auth::check()) ? Auth()->user()->adminuserid :  0;
        $MENU_URL           = (isset($data['menu_url']) && !empty($data['menu_url'])) ? $data['menu_url'] : '';
        if($ACCESS_KEY > 0){
            QuickLinksAccessLog::updateOrCreate(
            [
                "trn_id"       => $ACCESS_KEY,
                "user_id"      => $ADMINUSERID,
                "created_at"   => date("Y-m-d H:i")
            ],
            [
                "trn_id"       => $ACCESS_KEY,
                "user_id"      => $ADMINUSERID,
                "created_at"   => date("Y-m-d H:i"),
                "menu_url"     => $MENU_URL
            ]);
        }
        ########## QUICK ACCESS LOG LOGIC - 16 MAY 2022 ##########
        return $response;
    }
}
