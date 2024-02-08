<?php
namespace Modules\Mobile\Http\Controllers;
use Modules\Mobile\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use App\Models\WmClientMaster;
use App\Models\StateMaster;
use App\Models\LocationMaster;
use App\Models\TransporterDetailsMaster;
use App\Facades\LiveServices;
use App\Models\AdminUserReading;
use App\Models\GroupMaster;
use App\Models\CompanySettings;
use App\Models\UserDeviceInfo;
use App\Models\CustomerLoginDetail;
use App\Models\WmInvoices;
use App\Models\CustomerMaster;
use App\Models\AdminUser;
use App\Models\WmPaymentReceive;
use App\Models\GSTStateCodes;
use App\Models\AdminUserOtpInfo;
use App\Models\ClientMasterOtpVerificationMaster;
use Modules\Mobile\Http\Requests\ClientAddRequest;
use Modules\Mobile\Http\Requests\ClientKYCUpdate;
use JWTAuth;
use JWTFactory;
use Validator;
use PDF;
use Excel;
use DB;

class ClientController extends LRBaseController
{
	/*
	Use     : Register Client
	Author  : Hardyesh Gupta
	Date 	: 20 october,2023
	*/
	public function ClientRegister(ClientAddRequest $request){
		$city_id 		= (isset($request->city_id)) ? $request->city_id : 0;
		$state_id   	= (isset($request->state_id)) ? $request->state_id : 0;
		$city_name 		= (isset($request->city_name)) ? $request->city_name : "";
		$state_name 	= (isset($request->state_name)) ? $request->state_name : "";
		if(!empty($state_id)){
			$StateMaster 		= StateMaster::where("state_id",$state_id)->first();
			if(!empty($StateMaster)){
				$GstStateData 	= GSTStateCodes::where("id",$StateMaster->gst_state_code_id)->first();	
				$gst_state_code = $GstStateData->display_state_code;	
			}
			$request->request->add(array("gst_state_code" => $gst_state_code));
		}
		if(empty($state_id)){
			$state_id 			= StateMaster::insertGetId(['country_id'=>1,'state_name' =>ucwords($state_name),'status' => 'A','gst_state_code_id'=>0]);
			$request->state_id 	= $state_id;
		}
		if(empty($city_id))
		{
			$ref_city_id 		= DB::table('city_master')->insertGetId(['state_id'=>$state_id,'country_id'=> 1,'city_name' =>ucwords($city_name),'status' => 'A']);		
			$city_id 			= LocationMaster::insertGetId(['city' =>ucwords($city_name),'state'=> $state_name,'state_id'=>$state_id,'color_code' => '','ref_city_id'=>$ref_city_id,'status' => 'A']);
			$request->city_id 	= $city_id;
		}
		
		$data 	= WmClientMaster::AddClient($request->all(),$request);
		$msg 	= "";
		if(!empty($data)){
			
			$msg = "Thank you for registering with ".CLIENT_TITLE.".";	
		}
		$msg 	= (!empty($data)) ? $msg: trans("message.SOMETHING_WENT_WRONG");
		$code 	= (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Register Client
	Author  : Hardyesh Gupta
	Date 	: 20 october,2023
	*/
	public function ClientUpdate_ORG(Request $request){
		try {
    		$profile_pic 	= (isset($request->profile_pic)) ? $request->profile_pic : 0;
			$mobile_no   	= (isset($request->mobile_no)) ? $request->mobile_no : "";
			$data 			= WmClientMaster::UpdateClientMobile($request->all(),$request);
        	$msg 			= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
			$code 			= (!empty($data)) ?  SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}
	}

	/*
	Use     : Update Profile Pic 
	Author  : Hardyesh Gupta
	Date 	: 20 october,2023
	*/
	public function ClientProfilePicUpdate(Request $request){
		try {
			$data 			= WmClientMaster::UpdateClientProfilePic($request);
        	$msg 			= ($data == true) ? trans("message.PROFILE_PIC_UPDATE") : trans("message.RECORD_UPDATE_ERROR");
			$code 			= ($data == true) ?  SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}
	}



	/*
	Use     : List State
	Author  : Hardyesh Gupta
	Date 	: 20 october,2023
	*/
	public function ListState(Request $request){		
		$data 	= StateMaster::getAllStateData($request);
		return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);	
	}

	/*
	Use     : List City
	Author  : Hardyesh Gupta
	Date 	: 20 october,2023
	*/
	public function ListCity(Request $request){
		$state_id   = (isset($request->state_id)) ? $request->state_id : 0;
		$data 		= LocationMaster::GetCityListStateWise($request,false);
		return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
	}
	
	/*
	Use     : Login 
	Author  : Hardyesh Gupta
	Date 	: 23 october,2023
	*/
	public function Clientlogin(Request $request){
		try {
			$loginType  = MOBILE_LOGIN;
			$pass       = "";
			$mobile_no 	= (isset($request->mobile_no) && !empty($request->mobile_no)) ? $request->mobile_no : "";
			$validator 	= Validator::make($request->all(), [
            		'mobile_no' => 'required|string|max:10',
	        ]);
	        if ($validator->fails()) {
	            return response()->json(['code'=>SUCCESS,'msg'=>$validator->errors(),'data'=>'']);
	        }
			if(!empty($request->mobile_no)){
				$user = WmClientMaster::where("mobile_no",$request->mobile_no)->first();
				if($user){
					if($user->status != VALID_USER_STATUS){
						return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.CLIENT_ACCOUNT_INACTIVE'),'data'=>''], 401);	
					}else{
						if(OTP_LOGIN_ON_CLIENT){
							$data   = ClientMasterOtpVerificationMaster::sendAuthOTP($user->mobile_no,$user->id);	
							if($data){
								return response()->json(['code'=>SUCCESS,'msg'=>trans('message.CLIENT_USER_OTP_SEND_SUCCESS'),'data'=>$data], SUCCESS);
							}
						}
					}
				}else{
					return response()->json(['code'=>CODE_UNAUTHORISED,'msg'=>trans('message.CLIENT_USER_NOT_FOUND'),'data'=>''], 401);		
				}
			}
		} catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}
	}

	/*
    Use     : Verify OTP
    Author  : Hardyesh Gupta
    Date    : 25 Oct,2023
    */
    public function VerifyOTP(Request $request){
    	try {
    		$validator = Validator::make($request->all(), [
            		'mobile_no' => 'required|string|max:10',
            		'otp' 		=> 'required',
	        ]);
	        if ($validator->fails()) {
	            return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->errors(),'data'=>$validator->errors()]);
	        }
    		$data   = ClientMasterOtpVerificationMaster::VerifyOTP($request);
	        if($data == true){
	        	$mobile_no 	= (isset($request->mobile_no) && !empty($request->mobile_no)) ? $request->mobile_no : "";
	        	$token 		= null;
				$client     = WmClientMaster::where("mobile_no",$mobile_no)->first();
				if($client) {
					if (!$token = JWTAuth::fromUser($client)) {
						return response()->json(['code' => CODE_UNAUTHORISED, 'msg' => trans('message.INVALID_USERNAME_PASSWORD'), 'data' => '','type'=>"ok"], 401);
					}
					if($token){
						$result['code']               	= SUCCESS;
						$result['token']                = $token;
						$result['msg']                  = trans('message.USER_LOGIN_SUCCESS');
						return response()->json(["code" =>SUCCESS,"msg" =>$result['msg'],"data" => $result['token']]);	
					}
				}else{
					 return response()->json(['code' => CODE_UNAUTHORISED, 'msg' => trans('message.RECORD_NOT_FOUND'), 'data' => '','type'=>TYPE_ERROR], 401);
				}	
	        }else{
	        	$msg    = trans("message.OTP_VERIFICATION_FAILED");
	        	return response()->json(["code" =>CODE_UNAUTHORISED,"msg" =>$msg,"data" => $data]);	
	        }
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}
    }

    /*
	Use     : Update Client Mobile
	Author  : Hardyesh Gupta
	Date 	: 20 october,2023
	*/
	// public function ClientMobileUpdate(Request $request){
	// 	try {
	// 		$userid 		= (\Auth::check()) ? Auth()->user()->id :  0; 
	// 		$mobile_no   	= (isset($request->mobile_no)) ? $request->mobile_no : "";
	// 		$data   		= ClientMasterOtpVerificationMaster::sendAuthOTP($mobile_no,$userid);	
    //     	$msg 			= (!empty($data)) ? trans("message.CLIENT_USER_OTP_SEND_SUCCESS") : trans("message.CLIENT_OTP_FAILED");
	// 		$code 			= (!empty($data)) ?  SUCCESS : ERROR;
	// 		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    // 	}catch(\Exception $e){
	// 		return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
	// 	}
	// }

    /*
    Use     : Verify Mobile No
    Author  : Hardyesh Gupta
    Date    : 25 Oct,2023
    */
    public function VerifyMobile(Request $request){
    	try {
    		$mobileNo  = (isset($request->mobile_no) && !empty($request->mobile_no)) ?  $request->mobile_no : 0; 
	    	$validator = Validator::make($request->all(), [
	            		'mobile_no' => 'required|string|max:10',
		        ]);
		        if ($validator->fails()) {
		            return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->errors(),'data'=>'']);
		        }
	        
	        $data      = ClientMasterOtpVerificationMaster::VerifyMobile($mobileNo);
	        return $data;
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}
	    	
    }

    /*
	Use     : Update Client Mobile
	Author  : Hardyesh Gupta
	Date 	: 20 october,2023
	*/
	public function ClientMobileUpdate(Request $request){
		try {
			$validator = Validator::make($request->all(), [
            		'mobile_no' => 'required|string|min:10|max:10',
            		'otp' 		=> 'required',
	        ]);
	        if ($validator->fails()) {
	            return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->errors(),'data'=>$validator->errors()]);
	        }
    		$data   = ClientMasterOtpVerificationMaster::VerifyMobileOTP($request);
    		$msg 	= ($data == true) ? trans("message.MOBILE_NO_SUCCESSFULLY_UPDATED") : trans("message.RECORD_UPDATE_ERROR");
			$code 	= ($data == true) ?  SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}
	}

	/*
	Use     : Resend Auth OTP for adminuser
	Author  : Hardyesh Gupta
	Date    : 03 Nov,2023
	*/
	public function ResendAuthOTP(Request $request)
	{
		$MOBILE 	= (isset($request->mobile_no) && !empty($request->mobile_no)) ?  $request->mobile_no : "";
		if((\Auth::check())){
			$USERID 	= (\Auth::check()) ? Auth()->user()->id :  0; 	
		}else{
			$user     = WmClientMaster::where("mobile_no",$MOBILE)->first();
			$USERID   =  $user->id;
			$MOBILE   =  $user->mobile_no;
		}
		$data   	= ClientMasterOtpVerificationMaster::sendAuthOTP($MOBILE,$USERID);
		$msg    	= ($data) ? trans("message.OTP_SUCCESS") : trans("message.OTP_FAILED");
		$code   	= ($data) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(["code" =>$code,"msg" =>$msg,"data" => $data]);
	}

    /*
    Use     : Client Master API
    Author  : Hardyesh Gupta
    Date    : 25 Oct,2023
    */
    public function ClientMasterAPI(Request $request){
    	try {
    		$client_id 	= (\Auth::check()) ? Auth()->user()->id :  0; 
        	$data     	= WmClientMaster::GetClientById($client_id);
        	$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
			$code 		= (!empty($data)) ?  SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}
    }

    /*
    Use     : Client Master KYC Update
    Author  : Hardyesh Gupta
    Date    : 25 Oct,2023
    */
    public function UpdateClientKYC(ClientKYCUpdate $request){
    	try {
        	$data   = WmClientMaster::ClientKYCUpdate($request);
        	$msg 	= (($data == true)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_UPDATE");
			$code 	= (!empty($data)) ?  SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}
    }
    
    /*
    Use     : Logout API
    Author  : Hardyesh Gupta
    Date    : 25 Oct,2023
    */
    public static function ClientLogout(Request $request){
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
    Use     : Client Check Version
    Author  : Hardyesh Gupta
    Date    : 25 Oct,2023
    */
    public static function ClientCheckVersion(Request $request){
    	try {
    		$data 		= implode(",",CLIENT_APP_VERSION);
        	$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");

			return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}	
    }
	
	/*
	Use     : List Invoice Data
	Author  : Hardyesh Gupta
	Date    : 31 Oct,2023
	*/
	public function SearchInvoice_org(Request $request){
		try {
			$data       = WmInvoices::SearchInvoiceMobile($request);
    		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
    		$code 		= (!empty($data)) ? SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}	
	}

	/*
	Use     : Invoice Detail GetInvoiceById
	Author  : Hardyesh Gupta
	Date    : 31 Oct,2023
	*/
	public function GetInvoiceById(Request $request){
		try {
			$id         = (isset($request->invoice_id) && !empty($request->invoice_id)) ? $request->invoice_id : 0;
			$from_CNDN  = (isset($request->from_CNDN) && !empty($request->from_CNDN)) ? $request->from_CNDN : 0;
			$data       = WmInvoices::GetById($id,$from_CNDN);
    		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
    		$code 		= (!empty($data)) ? SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}	
	}

	
	/*
	Use     : List Payment History
	Author  : Hardyesh Gupta
	Date    : 31 Oct,2023
	*/
	public function PaymentHistoryList(Request $request){
		try {
			$invoice_no = (isset($request->invoice_no) && !empty($request->invoice_no)) ? $request->invoice_no : 0;
			$invoice_id = (isset($request->invoice_id) && !empty($request->invoice_id)) ? $request->invoice_id : 0;
			$data       = WmPaymentReceive::PaymentHistoryList($invoice_no,$invoice_id);
			$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
			$code 		= (!empty($data)) ? SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
		}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}	
	}

	/*
	Use     : List Payment History Client wise
	Author  : Hardyesh Gupta
	Date    : 02 Oct,2023
	*/
	public function ClientPaymentHistoryList(Request $request){
		try {
			$client_id 	= (\Auth::check()) ? Auth()->user()->id :  0; 
			$data       = WmPaymentReceive::ClientPaymentHistoryList($request,$client_id);
			$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
			$code 		= (!empty($data)) ? SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
		}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}	
	}

	/*
	Use     : Payment History GetByID
	Author  : Hardyesh Gupta
	Date    : 03 Oct,2023
	*/
	public function ClientPaymentHistoryGetById(Request $request){
		try {
			$client_id 	= (\Auth::check()) ? Auth()->user()->id :  0; 
			$id 		= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
			$data       = WmPaymentReceive::ClientPaymentHistoryGetById($id);
			$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
			$code 		= (!empty($data)) ? SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
		}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}	
	}

	/*
	Use     : Add/Insert Payment Receive Detail
	Author  : Hardyesh Gupta
	Date    : 06 Nov,2023
	*/
	public function AddPaymentReceive(Request $request){
		try {
			$data = false;
			$invoice_no 			= (isset($request->invoice_no) && !empty($request->invoice_no)) ? $request->invoice_no : 0;
			$payment_status 		= (isset($request->payment_status) && !empty($request->payment_status)) ? $request->payment_status : 0;
			$payment_amount 		= (isset($request->payment_amount) && !empty($request->payment_amount)) ? $request->payment_amount : 0;
			$payment_transaction_id = (isset($request->payment_transaction_id) && !empty($request->payment_transaction_id)) ? $request->payment_transaction_id : 0;
			$payment_transaction_detail = (isset($request->payment_transaction_detail) && !empty($request->payment_transaction_detail)) ? $request->payment_transaction_detail : array();
			$response   			= $payment_transaction_detail;	
			if(isset($response)){
				$StatusCode = $payment_status;
				$CreatedBy  = (\Auth::check()) ? Auth()->user()->id :  0; 
				if($StatusCode == 1){
					$InsertID = DB::table('client_payment_receive_response_log')->insertGetId(['transaction_id'=>$payment_transaction_id,'payment_status'=>$payment_status,'response'=>$response,'created_by' => $CreatedBy,'created_at'=>date('Y-m-d H:i:s')]);
					$requestData['invoice_no'] 			=  $invoice_no;
					$requestData['collect_by'] 			=  (\Auth::check()) ? Auth()->user()->id :  0; ;
					$requestData['remarks'] 			= "" ;
					$requestData['received_amount'] 	= $payment_amount ;
					$requestData['payment_type'] 		= 1030004 ;
					$requestData['payment_date'] 		= date('Y-m-d H:i:s') ;
					$request->request->add($requestData);
					// $data       = WmPaymentReceive::AddPaymentReceive($request->all());
				}
			}
    		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
    		$code 		= (!empty($data)) ? SUCCESS : ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}	
	}

	/*
	Use     : List Invoice Data
	Author  : Hardyesh Gupta
	Date    : 31 Oct,2023
	*/
	public function SearchInvoice(Request $request){
		try {
				$client_id 		= (\Auth::check()) ? Auth()->user()->id :  0; 
				$requestArray = array();
				$requestArray['endDate'] 			= (isset($request->endDate) && !empty($request->endDate)) ? $request->endDate : "";
				$requestArray['startDate'] 			= (isset($request->startDate) && !empty($request->startDate)) ? $request->startDate : "";
				$requestArray['invoice_no'] 		= (isset($request->invoice_no) && !empty($request->invoice_no)) ? $request->invoice_no : ""; 
				$requestArray['collect_payment_status'] = (isset($request->collect_payment_status) && !empty($request->collect_payment_status)) ? $request->collect_payment_status : ""; 
				$requestArray['client_name'] 		= (isset($request->client_name) && !empty($request->client_name)) ? $request->client_name : ""; 
				$requestArray['invoice_status'] 	= (isset($request->invoice_status) && !empty($request->invoice_status)) ? $request->invoice_status : 0; 
				$requestArray['city_id'] 			= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0; 
				// $requestArray['nespl'] 				= (isset($request->nespl) && !empty($request->nespl)) ? $request->nespl : 0; 
				// $requestArray['invoice_status'] 	= (isset($request->invoice_status) && !empty($request->invoice_status)) ? $request->invoice_status : 0; 
				$request->request->add(array("params"=>$requestArray));
				$data       = WmInvoices::SearchInvoice($request,true,$client_id);
	    		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
			return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    	}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>""]);   
		}	
	}


}