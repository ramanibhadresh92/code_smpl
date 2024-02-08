<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
######### SETU API OPEN API ############
Route::post('/v1/gst/generate-token','SetuApiController@GenerateTokenSetuApi')->name('GenerateTokenSetuApi');
Route::post('/v1/gst/get-gst-detail','SetuApiController@VerifyGSTDetails')->name('VerifyGSTDetails');
Route::post('/v1/cin/get-cin-detail','SetuApiController@VerifyCINDetails')->name('VerifyCINDetails');
Route::post('/v1/cin/get-cin-director-detail','SetuApiController@VerifyCompanyDirectorDetailsByCIN')->name('VerifyCompanyDirectorDetailsByCIN');
######### SETU API OPEN API ############


Route::get('/message', function () {
	$data = [
    'message' => trans('message.USER_LOGIN_SUCCESS')
];
return response()->json($data, 200);
})->middleware('localization');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
/*Supervisor Login*/
Route::post('supervisor/login', 'APILoginController@supervisorLogin')->middleware('cors');
/*End Supervisor Login*/
Route::post('user/login', 'APILoginController@login')->middleware('cors');
Route::post('user/register', 'APIRegisterController@register')->middleware('cors');
Route::post('user/Forgot-password','APILoginController@forgotPassword')->middleware('cors');
Route::post('user/passencrypt', 'APIRegisterController@passencrypt')->middleware('cors');
Route::post('user/passdecrypt', 'APIRegisterController@passdecrypt')->middleware('cors');

//Route::middleware('jwt.auth')->get('users', function (Request $request) {
   // return auth()->user();
//});
Route::group(['middleware' => ['jwt.auth','cors']], function () {
    Route::get('users', function (Request $request) {
    return auth()->user();
});
Route::get('user/logout', 'APILoginController@logout');
Route::get('user/typelist', 'APILoginController@typelist');
	Route::any('state/list', function (Illuminate\Http\Request $request) {
		return App\Models\LocationMaster::getAllState();
	})->name('get-state-list');  

	Route::any('city/list', function (Illuminate\Http\Request $request) {
		return App\Models\LocationMaster::getCityByState($request->state);
	})->name('get-city-list');

	Route::any('city-state-all', function () {
		return  App\Models\LocationMaster::all()->toJson();
	})->name('city-state-all');
	Route::any('usertype/list', function (Illuminate\Http\Request $request) {
		return App\Models\GroupMaster::getUserType();
	})->name('get-state-list');  
});

/** WEB PORTAL ROUTE STARTS */
Route::group(['middleware' => ['webPortal']], function () {
	Route::post('get-impact-location-list',['uses' =>'WebPortalController@getImpactLocationList'])->name('get-impact-locations-list');
	Route::post('get-impact-period-list',['uses' =>'WebPortalController@getImpactReportPeriodFilter'])->name('get-impact-periods-list');
	Route::post('get-impact-report-details',['uses' =>'WebPortalController@getImpactReportDetails'])->name('get-impact-report-detail');
});
/** WEB PORTAL ROUTE ENDS */


/** HDFC ROUTE STARTS */
Route::group(['middleware' => ['HDFCApi']], function () {
	Route::post('get-payment-trasaction-details',['uses' =>'HDFCPaymentTransactionsController@getPaymentTransactionDetails'])->name('get-payment-trasaction-details');
});
/** HDFC ROUTE ENDS */