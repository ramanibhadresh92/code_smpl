<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Classes\SendSMS;
use App\Models\CompanyMaster;
use App\Models\AdminUser;
class AdminUserOtpInfo extends Model implements Auditable
{
    
	protected 	$table 		=	'adminuser_otp_info';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;

	/*
	Use 	: Insert OTP info for tracking user 
	Author 	: Axay Shah
	Date 	: 16 Aug,2019
	*/
	public static function AddOTPInfo($OTP = "",$USERID = 0,$MOBILE ="",$response=""){
		$add = new self();
		$add->mobile_no 	=  $MOBILE;
		$add->otp 			=  $OTP;
		$add->adminuserid 	=  $USERID;
		$add->response 		=  $response;
		$add->created_by 	=  Auth()->user()->adminuserid;
		if($add->save()){
			return true;	
		}
		return false;
		
	}
	/*
	Use 	: send Login OTP 
	Author 	: Axay Shah
	Date 	: 16 Aug,2019
	*/
	public static function sendAuthOTP($MOBILE = 0){
		$OTPEXITS 	= true;
		$OTP 		= generateNumericOTP(OTP_NUMBER_LENGTH);
		/* GENERATING OTP UNTILL ITS NOT FIND UNIQUE*/
		while($OTPEXITS){
			$COUNT = AdminUser::where("otp_code",$OTP)->count();
			if($COUNT == 0){
				$OTPEXITS = false;
				break;
			}	
			$OTP 		= generateNumericOTP(OTP_NUMBER_LENGTH);
		}
		$COMPANY_NAME 	= env("COMPANY_NAME");
		$COMPANY 		= CompanyMaster::find(Auth()->user()->company_id);
		if($COMPANY){
			$COMPANY_NAME = $COMPANY->company_name;
		} 
		

		$MESSAGE 	= $OTP." is your one time password to process on ".$COMPANY_NAME.".It is valid for 10 minutes.Do not share your OTP with anyone.";
		$MOBILE 	=  ($MOBILE == 0) ? Auth()->user()->mobile :  $MOBILE;
		$SENDSMS 	=  new SendSMS();
		$TIME 		=  date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +10 minutes"));
		$RESPONSE 	= $SENDSMS->AuthByOTP($MOBILE,$MESSAGE);
		$update 	= AdminUser::where("adminuserid",Auth()->user()->adminuserid)->update(["otp_code"=>$OTP,"otp_expiry_time"=>$TIME]);
		$info 		= self::AddOTPInfo($OTP,Auth()->user()->adminuserid,$MOBILE,$RESPONSE);
		return $info;
	}

	/*
	Use 	: CHECK OTP IS VALID OR NOT
	Author 	: Axay Shah
	Date 	: 16 Aug,2019
	*/
	public static function VerifyOTP($request){

		$date 	= date("Y-m-d H:i:s");
		$MOBILE	= (isset($request->mobile) 	&& !empty($request->mobile)) 	? $request->mobile 	: 0;
		$OTP 	= (isset($request->otp) 	&& !empty($request->otp)) 		? $request->otp 	: 0;
		if(OTP_LOGIN_ON){
			if($OTP > 0){
				if(MASTER_OTP == $OTP){
					return true;
				}
				$Verify = AdminUser::where("adminuserid",Auth()->user()->adminuserid)->where("otp_code",$OTP)->where("otp_expiry_time",">=",$date)->first();
				if(!$Verify){
					return false;
				}
				$Verify->otp_code 		 = NULL;
				$Verify->otp_expiry_time = NULL;
				if($MOBILE > 0){
					$Verify->mobile 		 	 = $MOBILE;	
					$Verify->mobile_verify 		 = 1;	
				}
				$Verify->save();
				return true;
			}	
		}
	}
}
