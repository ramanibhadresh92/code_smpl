<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 
use App\Classes\SendSMS;
use App\Models\CompanyMaster;
use App\Models\AdminUser;
use App\Models\WmClientMaster;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class ClientMasterOtpVerificationMaster extends Model implements Auditable
{
    
	protected 	$table 		=	'client_master_otp_verification_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;

	/*
	Use 	: Insert OTP info for tracking user 
	Author 	: Hardyesh Gupta
	Date 	: 25 Oct,2023
	*/
	public static function AddOTPInfo($OTP = "",$MOBILE ="",$USERID = 0,$response=""){
		$add = new self();
		$add->mobile_no 		=  $MOBILE;
		$add->otp 				=  $OTP;
		$add->client_id 		=  $USERID;
		$add->otp_verify_detail =  $response;
		if($add->save()){
			return true;	
		}
		return false;
		
	}
	/*
	Use 	: send Login OTP 
	Author 	: Hardyesh Gupta
	Date 	: 25 Oct,2023
	*/
	public static function sendAuthOTP($MOBILE = 0,$USERID = 0){
		$info 		= "";
		$OTPEXITS 	= true;
		
		$OTP 		= generateNumericOTP(OTP_NUMBER_LENGTH);
		/* GENERATING OTP UNTILL ITS NOT FIND UNIQUE*/
		while($OTPEXITS){
			$COUNT = WmClientMaster::where("otp_code",$OTP)->count();
			if($COUNT == 0){
				$OTPEXITS = false;
				break;
			}	
			$OTP 		= generateNumericOTP(OTP_NUMBER_LENGTH);
		}

		// $COMPANY_NAME 	= env("COMPANY_NAME");
		// $COMPANY 		= CompanyMaster::find(Auth()->user()->company_id);
		// if($COMPANY){
		// 	$COMPANY_NAME = $COMPANY->company_name;
		// } 
		if(!empty($USERID)){
			$user = WmClientMaster::where("mobile_no",$MOBILE)->where("id",$USERID)->first();			
				$MESSAGE 	= $OTP." is your one time password to process. It is valid for 10 minutes.Do not share your OTP with anyone.";
				$MOBILE 	=  ($MOBILE == 0) ?  $user->mobile_no :  $MOBILE;
				$SENDSMS 	=  new SendSMS();
				$TIME 		=  date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +10 minutes"));
				$RESPONSE 	= $SENDSMS->AuthByOTP($MOBILE,$MESSAGE);
				// $update 	= WmClientMaster::where("id",$user->id)->update(["otp_code"=>$OTP,"otp_expiry_time"=>$TIME]);
				$update 	= WmClientMaster::where("id",$USERID)->update(["otp_code"=>$OTP,"otp_expiry_time"=>$TIME]);
				$info 		= self::AddOTPInfo($OTP,$MOBILE,$USERID,$RESPONSE);	
		}
		return $info;
	}
	

	/*
	Use 	: CHECK OTP IS VALID OR NOT
	Author 	: Axay Shah
	Date 	: 16 Aug,2019
	*/
	public static function VerifyOTP($request){

		$date 	= date("Y-m-d H:i:s");
		$MOBILE	= (isset($request->mobile_no) 	&& !empty($request->mobile_no)) 	? $request->mobile_no 	: 0;
		$OTP 	= (isset($request->otp) 	&& !empty($request->otp)) 		? $request->otp 	: 0;
		if(OTP_LOGIN_ON_CLIENT){
			if($OTP > 0){
				if(CLIENT_MASTER_OTP == $OTP){
					return true;
				}
				$Verify = WmClientMaster::where("mobile_no",$MOBILE)->where("otp_code",$OTP)->where("otp_expiry_time",">=",$date)->first();
				if(!$Verify){
					return false;
				}
				$Verify->otp_code 		 = NULL;
				$Verify->otp_expiry_time = NULL;
				if($MOBILE > 0){
					// $Verify->mobile_no 		 = $MOBILE;	
					$Verify->mobile_verify 		 = 1;	
				}
				$Verify->save();
				return true;
			}	
		}
	}

	/*
	Use 	: CHECK OTP IS VALID OR NOT
	Author 	: Hardyesh Gupta
	Date 	: 03 Nov,2023
	*/
	public static function VerifyMobileOTP($request){
		$USERID = (\Auth::check()) ? Auth()->user()->id :  0; 
		$date 	= date("Y-m-d H:i:s");
		$MOBILE	= (isset($request->mobile_no) 	&& !empty($request->mobile_no)) 	? $request->mobile_no 	: 0;
		$OTP 	= (isset($request->otp) 	&& !empty($request->otp)) 		? $request->otp 	: 0;
		if(OTP_LOGIN_ON_CLIENT){
			if($OTP > 0){
				if(CLIENT_MASTER_OTP == $OTP){
					return true;
				}
				
				$OtptableCount 			= self::where("mobile_no",$MOBILE)->where("otp",$OTP)->where("client_id",$USERID)->count();
				$TblData 				= WmClientMaster::where("id",$USERID)->where("otp_code",$OTP)->where("otp_expiry_time",">=",$date);
				$ClientMasterDataCount  = $TblData->count();
				$Verify  				= $TblData->first();
				if(!$Verify){
					return false;
				}
				$Verify->otp_code 		 = NULL;
				$Verify->otp_expiry_time = NULL;
				if($MOBILE > 0){
					$Verify->mobile_no 		 	 = $MOBILE;	
					$Verify->mobile_verify 		= 1;	
				}
				$Verify->save();
				return true;
			}	
		}
	}

	/*
	Use 	: Verify Mobile Number
	Author 	: Hardyesh Gupta
	Date 	: 25 Oct,2023
	*/
	public static function VerifyMobile($mobileNo = 0){
		$USERID 	= (\Auth::check()) ? Auth()->user()->id :  0; 
		$msg 		= trans("message.OTP_FAILED");
		$code 		= INTERNAL_SERVER_ERROR;
		$data 		= array();
		$count 		= WmClientMaster::where("mobile_no",$mobileNo)->where("id","!=",$USERID)->count();
		if($count == 0){
			$data   = self::sendAuthOTP($mobileNo,$USERID);
			$msg    = ($data) ? trans("message.OTP_SUCCESS") : trans("message.OTP_MOBILE_EXITS");
			$code   = ($data) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		}
		return response()->json(["code" =>$code,"msg" =>$msg,"data" => $data]);
	}


}
