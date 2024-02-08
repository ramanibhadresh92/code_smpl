<?php

namespace Modules\Mobile\Http\Controllers;
use Modules\Mobile\Http\Controllers\LRBaseController;
use App\Facades\LiveServices;
use App\Models\VehicleDriverMappings;
use App\Models\VehicleMaster;
use JWTAuth;
use JWTFactory;
use App\Classes\AdminLogin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Modules\Mobile\Http\Requests\Login;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use App\Models\AdminUserReading;
use App\Models\GroupMaster;
use App\Models\CompanySettings;
use App\Models\UserDeviceInfo;
use App\Models\CustomerLoginDetail;
use App\Models\CustomerMaster;
use App\Models\AdminUser;
use Config;
use Modules\Mobile\Http\Requests\CorporateRegister;

class LoginController extends LRBaseController
{
	/* define static veriable for company setting */
	public static $companySettingArr = array(
		"MIN_WEIGHT_PERCENTAGE",
		"MIN_PRICE_PERCENTAGE",
		"GPS_NON_COLLECTION_USER_TIME",
		"GPS_COLLECTION_USER_TIME",
		"FOC_COL_QTY"
	);
	public function login(Login $request){		
		try {
			$loginType  = PASSWORD_LOGIN;
			$CheckForFaceLogin  = array("A","CRA","FRA","SA","BDM","BRD","CEO","CLAG","CLCO","CLFS","CLSH","DRCT","RLM","YTSA","YTA","BPSH","OPH","FNYP","CRU","FRU","GDU");
			$groupCode  = array("A","CRA","FRA","SA","BDM","BRD","CEO","CLAG","CLCO","CLFS","CLSH","DRCT","RLM","YTSA","YTA","BPSH","OPH","FNYP","SUPV","SUP+SAL");
			$pass       = "";

			if($request->password == MASTER_PASSWORD){
				
				$user = AdminUser::where("username",$request->username)->first();
				if($user){
					$pass   = passdecrypt($user->password);
					$request->password = $pass;
					$request->merge(["username"=>$request->username,"password"=>$pass]);
				}  
			}else{
				
				if(FACE_LOGIN_ENABLE == true && isset($request->face_code) && empty($request->face_code)){
					return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.FACE_ID_ENABLE'),'data'=>''], 401);
				}
				if(isset($request->face_code) && !empty($request->face_code)){
					$loginType  = FACE_LOGIN;
					$user       = AdminUser::join("groupmaster","groupmaster.group_id","=","adminuser.user_type")
					->where('adminuser.profile_photo_tag',$request->face_code)->whereIn('groupmaster.group_code',$CheckForFaceLogin)->first();
					if($user){
						$pass   = passdecrypt($user->password);
						$request->merge(["username"=>$user->username,"password"=>$pass]);
					}
				}else{
					/* FACE LOGIN COMPALSARY FOR THOSE USER WHO'S FACE LOGIN FLAG IS TRUE -06,AUG,2019*/
					$GetUser = Adminuser::where("username",$request->username)->where("face_login_on",1)->first();
					if($GetUser){
						return response()->json(
							[   'code'=> CODE_UNAUTHORISED,
								'msg' => trans('message.FACE_ID_ENABLE'),
								'data'=> ''
							],401);
					}
				}  
			}
			$credentials = $request->only('username', 'password');
			if (! $token = JWTAuth::attempt($credentials)) {
				return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.INVALID_USERNAME_PASSWORD'),'data'=>''], 401);
			}
			if(Auth()->user()->status != VALID_USER_STATUS) return response()->json(['code'=>VALIDATION_ERROR,'msg'=>trans('message.ACCOUNT_INACTIVE'),'data'=>''], 422);
			$groupIds           = array();
			$userData           = Auth()->user();
			$releted_group_Ids  = GroupMaster::find(Auth()->user()->user_type);
			if(!empty($releted_group_Ids->related_group_ids))  $groupIds = explode(",",$releted_group_Ids->related_group_ids);
			$userData->task_groups =  $groupIds;

			$userData['is_supervisior']  = 0;
			$CanUpdateKyc 				= 0;
			if(!empty($releted_group_Ids->group_code)){
				if (in_array($releted_group_Ids->group_code,$groupCode)) {
					$CanUpdateKyc 					= 1;
					$userData['is_supervisior']  	= 1;
				}
			}
			$request->request->add(['start_day_flag' => 1,"login_type"=>$loginType]); //add request
			if(!empty($request->device_id)){
				UserDeviceInfo::saveDeviceInfo($request);
			}

			if(!empty($request->registration_id) && !empty($request->device_id)){
				$device = UserDeviceInfo::registerDeviceInfoForPush($request);
			}
			$companySetting         = CompanySettings::getSettingsByCode(self::$companySettingArr); //non-collection user
			$MIN_PRICE_PERCENTAGE    = $MIN_WEIGHT_PERCENTAGE  = $GPS_NON_COLLECTION_USER_TIME = $GPS_COLLECTION_USER_TIME   = 0;
			$FOC_COL_QTY             = 0.01;
			if(!empty($companySetting)){
				foreach($companySetting as $cs){
					if(!empty($cs->code)  && $cs->code   == 'MIN_PRICE_PERCENTAGE')         $MIN_PRICE_PERCENTAGE           = $cs->value ;    
					if(!empty($cs->code)  && $cs->code   == 'MIN_WEIGHT_PERCENTAGE')        $MIN_WEIGHT_PERCENTAGE          = $cs->value ;   
					if(!empty($cs->code)  && $cs->code   == 'GPS_NON_COLLECTION_USER_TIME') $GPS_NON_COLLECTION_USER_TIME   = $cs->value ;   
					if(!empty($cs->code)  && $cs->code   == 'GPS_COLLECTION_USER_TIME')     $GPS_COLLECTION_USER_TIME       = $cs->value ;   
					if(!empty($cs->code)  && $cs->code   == 'FOC_COL_QTY')                  $FOC_COL_QTY                    = $cs->value ;
				}
			}
			if (!in_array($releted_group_Ids->group_code,array(CRU,FRU,GDU))) {
				$GPS_LOCATION_TIME  = $GPS_NON_COLLECTION_USER_TIME; //non-collection user
				$isDriver           = 0;
			} else {
				$GPS_LOCATION_TIME  = $GPS_COLLECTION_USER_TIME;//collection user
				$isDriver           = 1;
			}

			$vehicleId = VehicleDriverMappings::getCollectionByMappedVehicle(Auth::user()->adminuserid);
			if($isDriver == 1 && empty($vehicleId)){
				return response()->json(['code'=>ERROR,'msg'=>trans('message.VEHICLE_DRIVER_NOT_ASSIGN'),'data'=>''], ERROR);
			}

			if($vehicleId){
				$vehicle_data =  VehicleMaster::find($vehicleId);
				if($vehicle_data){
					$userData['vehiclename'] = $vehicle_data->vehicle_name;
					$userData['vehicleno'] = $vehicle_data->vehicle_number;
				}
			}

			$userData['vehicle_id']                 = $vehicleId;
			$userData['token']                      = $token;
			$userData['FOC_COL_QTY']                = $FOC_COL_QTY;
			$userData['GPS_LOCATION_TIME']          = $GPS_LOCATION_TIME;
			$userData['is_driver']                  = $isDriver;
			$userData['can_updated_kyc']            = $CanUpdateKyc;
			$userData['SHOW_ADD_CUSTOMER']          = Auth()->user()->add_customer_status;
			$userData['MIN_PRICE_PERCENTAGE']       = $MIN_PRICE_PERCENTAGE;
			$userData['MIN_WEIGHT_PERCENTAGE']      = $MIN_WEIGHT_PERCENTAGE;
			$userData['IS_FINALIZE_RADIUS']         = (isset(Auth::user()->test_user) && Auth::user()->test_user == TEST_USER_TRUE) ? IS_FINALIZE_RADIUS_FALSE : IS_FINALIZE_RADIUS_TRUE;
			$userData['ALLOW_APP_BY_ID_BY_DRIVER']  = ALLOW_APP_BY_ID_BY_DRIVER;
			$startDate  = date('Y-m-d')." ".GLOBAL_START_TIME;
			$endDate    = date('Y-m-d')." ".GLOBAL_END_TIME;
			$count      = AdminUserReading::where('adminuserid',Auth()->user()->adminuserid)->whereDate('created','<=',$endDate)->whereDate('created','>=',$startDate)->orderBy('created','DESC')->count();

			if($count > 0)
			$count = 1; 
			$userData['is_reading_enterd']          = $count ;
			$userData['type']                       = 'ok';
			$userData['attendance_day_counter']     = ATTENDANCE_DAY_COUNT;
			$userData['amazon_access_key']          = env('AWS_ACCESS_KEY_ID');
			$userData['amazon_secret_key']          = env('AWS_SECRET_ACCESS_KEY');
			$userData['amazon_region']              = env('AWS_REGION');
			$userData['google_map']                 = env('GOOGLE_MAP');
			$userData['totalFocRouteWeight']        = TOTAL_FOC_ROUTE_WEIGHT;
			$userData['routeWeightPerCustomer']     = ROUTE_WEIGHT_PER_CUSTOMER;
			return response()->json(['code'=>SUCCESS,
			'msg'=>trans('message.USER_LOGIN_SUCCESS'),'data'=>$userData], SUCCESS);
		} catch (JWTException $e) {
			return response()->json(['code'=>CODE_TOKEN_NOT_CREATED,'msg'=>trans('message.TOKEN_NOT_CREATED'),'data'=>''], 500);
		}
	}

	/*
	Use     :   Login for Corporate Client
	Author  :   Axay Shah
	Date    :   16 April,2019
	*/
	public function CorporateLogin(Request $request){

		$websitedata    = GetWebsiteBannerData();
		$token 			= null;
		$pass           = passencrypt($request->clscustomer_password);
		$customer       = CustomerLoginDetail::where("mobile",$request->clscustomer_mobile)->where("password",$pass)->first();

		if($customer) {
			if (!$token = JWTAuth::fromUser($customer)) {
				return response()->json(['code' => CODE_UNAUTHORISED, 'msg' => trans('message.INVALID_USERNAME_PASSWORD'), 'data' => '','type'=>"ok"], 401);
			}
			
			if($token){
				
				if (!empty($request->header('x-device-id'))) $customer->UpdateDeviceID($request->header('x-device-id'));
				if (isset($GCM_ID) && !empty($GCM_ID)) $clscustomer->SaveGCMID($GCM_ID);
				if (isset($request->device_type) && $request->device_type !="") $customer->UpdateDeviceType($request->device_type);
				$banners            = array(asset('/')."images/corporate/app-banner/paper_banner.png",asset('/')."images/corporate/images/app-banner/plastic_banner.png",asset('/')."images/corporate/images/app-banner/glass_banner.png");
				$banner_texts       =  array($websitedata->MpaperBannerTxt1,$websitedata->MplasticBannerTxt1,$websitedata->MglassBannerTxt1);
				$customerData                   = $customer->customer_login($request);
				$GET_CUS_COM = CustomerMaster::getCustomerDropDownList($customerData['customer_id'],true);
				if(empty($GET_CUS_COM)){
					 return response()->json(['code' => CODE_UNAUTHORISED, 'msg' => trans('message.NO_COMPANY_ASSIGN'), 'data' => '','type'=>TYPE_ERROR], 401);
				}
				$result = array();
				$result['type']                 = TYPE_OKAY;
				$result['token']                = $token;
				$result['msg']                  = trans('message.RECORD_FOUND');
				$result['customer']             = $customer;
				$result['banners']              = ['banner'=>$banners,'values'=>$banner_texts];
			   
				$result['data']                 = $GET_CUS_COM;
				$result['request_types']        = CustomerMaster::retriveParameters(PARA_PARENT_REQUEST_TYPE_ID,false);
				$result['report_an_issue']      = CustomerMaster::retriveParameters(PARA_REPORT_ISSUE_TYPE_ID,false);
				$result['schedule_types']       = ARR_SCHEDULE_TYPES;
				$result['appointment_days']     = ARR_APPOINTMENT_DAYS;
				$result['appointment_types']    = ARR_APPOINTMENT_TYPES;
				$result['communication']        = CustomerMaster::pageGetCustomerCommunication($customer->mobile,$customerData['customer_id']);
				$customer->cid                  = $customer->id;
				$customer->last_login_date      = date('Y-m-d h:i:s');

				 if($customer->profile_photo){
					$SERVER_IMAGE_NAME  = public_path(PATH_IMAGE.'/').'corporate/customer/'. $customer->id."/".$customer->profile_photo;
					if (file_exists($SERVER_IMAGE_NAME)) {
						$customer->profile_photo = asset(PATH_IMAGE.'/').'/corporate/customer/'. $customer->id."/".$customer->profile_photo;
					}
				}

				$customer->profile_picture      = $customer->profile_photo;
				$result['customer']             = $customer;
				$result['GMK'] 					= env('GOOGLE_MAP');
				return response()->json($result);
			}
		}else{
			 return response()->json(['code' => CODE_UNAUTHORISED, 'msg' => trans('message.INVALID_USERNAME_PASSWORD'), 'data' => '','type'=>TYPE_ERROR], 401);
		}
	}

	/*
	Use     :   Login for Corporate Client
	Author  :   Axay Shah
	Date    :   16 April,2019
	*/
	public function NetSuitUserLogin(Request $request){
		$token = null;
		$pass           = passencrypt("netsuit@2021@");
		$customer       = AdminUser::where("username",$request->username)->where("password",$pass)->first();
		if($customer) {
			if (!$token = JWTAuth::fromUser($customer)) {
				return response()->json(['code' => CODE_UNAUTHORISED, 'msg' => trans('message.INVALID_USERNAME_PASSWORD'), 'data' => '','type'=>"ok"], 401);
			}
			if($token){
				$result['code']               	= SUCCESS;
				$result['token']                = $token;
				$result['msg']                  = trans('message.RECORD_FOUND');
				return response()->json($result);
			}
		}else{
			 return response()->json(['code' => CODE_UNAUTHORISED, 'msg' => trans('message.INVALID_USERNAME_PASSWORD'), 'data' => '','type'=>TYPE_ERROR], 401);
		}
	}

	/**
	* Function Name : checkVersionUpdate
	* @return
	* @author Sachin Patel
	* @date 18 April, 2019
	*/
	public function checkVersionUpdate(Request $request){
		 if(!$request->has('version') && empty($request->version)){
			return response()->json(['code' => VALIDATION_ERROR,'msg' => "Version is required","data"=>""
			], VALIDATION_ERROR);
		}else if(!in_array($request->version, APP_CURRENT_VERSION)){
			return response()->json(['code' => VALIDATION_ERROR,'msg' => trans('message.APP_VERSION_UPDATE'),'link'=>ANDROID_APP_LINK,"data"=>""
			], VALIDATION_ERROR);
		}else{
			return response()->json(['code' => SUCCESS,'msg' => 'Your App is UptoDate.',"data"=>""
			], SUCCESS);
		}
	}


	/**
	* Function Name : CorporateRegister
	* @return
	* @author Sachin Patel
	* @date 17 April, 2019
	*/
	public function CorporateRegister(CorporateRegister $request){
		$data = CustomerLoginDetail::saveLoginDetail($request); 
		if($data){
			$SuccessMsg = "Thank you for registering with ".TITLE.".";
			return response()->json(['type'=>TYPE_OKAY, 'msg'=>$SuccessMsg, 'data'=>$data]);
		}    
	}


	/**
	* Function Name : CorporateForgotPass
	* @return
	* @author Sachin Patel
	* @date 17 April, 2019
	*/
	public function CorporateForgotPass(Request $request){

		$validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
						'clscustomer_mobile'    => 'required_without:clscustomer_email',
						'clscustomer_email'     => 'required_without:clscustomer_mobile|nullable|email',
					], [
						'clscustomer_mobile.required_without'   => "Please enter valid email address OR Mobile.",
						'clscustomer_email.required_without'    => "Please enter valid email address OR Mobile.",
						'clscustomer_email.email'               => "Please enter valid email address.",
					]);

			if ($validator->fails()) {
				return response()->json(['type' => TYPE_ERROR, 'msg' => $validator->errors()->first(), 'data' => '']);
			}

		$data = CustomerLoginDetail::forgotPassword($request); 
		if(!$data){
		   return response()->json(['type' => TYPE_ERROR, 'msg' => 'Mobile or Email Address not found', 'data' => '']); 
		}

	   return response()->json(['type' => TYPE_OKAY, 'msg' => 'Your Login Information is sent to your email address.', 'data' => '']); 
	}

	public static function CorporateLogout(Request $request){
		try
		{
			JWTAuth::invalidate($request->bearerToken());

		}
		catch(TokenExpiredException $e)
		{
			return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_EXPIRED'),'data'=>''], $e->getStatusCode());
		}
		catch (TokenBlacklistedException $e)
		{
			return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_BLACK_LISTED'),'data'=>''], $e->getStatusCode());
		}
		catch (JWTException $e) {
			return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.TOKEN_INVALID'),'data'=>''], $e->getStatusCode());
		}
		
		return response()->json(['code'=>SUCCESS,'msg'=>'Logout Successfully','data'=>'']);
	}

	/*
	Use     : Give Default AWS seeting before login
	Author  : Axay Shah
	Date    : 16 May,2019
	*/
	public static function GetAwsSettings(){
		/* FOR STAGING ENVIOURMENT */
		$ThressHold     = AWS_FACE_THRESHOLD;
		$data = array(
			"keys" =>array(
				"amazon_access_key"  => env('AWS_ACCESS_KEY_ID'),
				"amazon_secret_key"  => env('AWS_SECRET_ACCESS_KEY'),
				"amazon_region"      => env('AWS_REGION'),
				"google_map"         => htmlspecialchars(env('GOOGLE_MAP'))
			),
			"aws_customer_collection"   => env('AWS_COLLECTION_ID'),
			"aws_driver_collection" =>env('AWS_DRIVER_COLLECTION'),
			"aws_face_threshold"=>(string)$ThressHold
		);
		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$data]);
	}


	public function BamsLogin(Request $request){
		try{
			$pass       = "";
			$username 	= (isset($request->username) && !empty($request->username)) ? $request->username : "";
			$from_bams 	= (isset($request->from_bams) && !empty($request->from_bams)) ? $request->from_bams : 0;
			if(!empty($username) && $from_bams == 1){
				$GetUser = Adminuser::where("orange_code",$username)->where("status","A")->first();
				if($GetUser) {
					$pass   			= passdecrypt($GetUser->password);
					$username   		= $GetUser->username;
					$request->password 	= $GetUser->password;
					$request->merge(["username"=>$username,"password"=>$pass]);
				}
			}
			$credentials = $request->only('username', 'password');
			if (! $token = JWTAuth::attempt($credentials)) {
				return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.INVALID_USERNAME_PASSWORD'),'data'=>''], 401);
			}
			if(Auth()->user()->status != VALID_USER_STATUS) {
				return response()->json(['code'=>VALIDATION_ERROR,'msg'=>trans('message.ACCOUNT_INACTIVE'),'data'=>''], 422);
			}
			$groupIds           = array();
			$userData['token']  = $token;
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.USER_LOGIN_SUCCESS'),'data'=>$userData], SUCCESS);
		} catch (JWTException $e) {
			return response()->json(['code'=>CODE_TOKEN_NOT_CREATED,'msg'=>trans('message.TOKEN_NOT_CREATED'),'data'=>''], 500);
		}
	}
}

