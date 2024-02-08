<?php

namespace App\Classes;
use App\Models\AppointmentCollection;
use App\Models\CustomerContactDetails;
use App\Models\CompanyMaster;
use App\Models\CustomerMaster;
use App\Models\AdminUser;
class SendSMS {
 
    public static function SendCollectionSMS($collectionRaw,$donation,$charityname) {
		if(SMS_ON){
			$MOBILENOS	= "";
			$COMMA		= "";
			if(isset($collectionRaw->mobile_no) && !empty($collectionRaw->mobile_no)){
				$mobile_nos	= explode(",",$collectionRaw->mobile_no);
				foreach ($mobile_nos as $mobile) {
					$mobile			= trim($mobile);
					$countrycode 	= substr($mobile,0,2);
					if ($countrycode != MOBILE_COUNTRY_CODE && strlen($mobile) == MOBILE_DIGIT_LENGHT) {
						$MOBILENOS .= $COMMA.MOBILE_COUNTRY_CODE.$mobile;
						$COMMA		= ",";
					}
				}
				if ($MOBILENOS != "") 
				{
					$MOBILE	 			= $MOBILENOS;
					$collection = AppointmentCollection::retrieveCollection($collectionRaw->collection_id);
					if($collection){
						$Amount			= $collection->given_amount;
						if ($collection->vat == "Y" ||$collection->vat == "y") {
							$VAT_AMT  	= (($Amount * $collection->vat_val) / 100);
							$Amount		= $Amount + $VAT_AMT;
						}
					}
					/* Added on 26-06-2013 for donation */
					$company_name   = Auth()->user()->company_name;
					(!empty(Auth()->user()->office_phone)) ? $feedBack = Auth()->user()->office_phone : $feedBack = '-';
					if( $donation == 0 ) {
						$MESSAGE1   = str_replace("[COMPANY_NAME]",$company_name,SMS_APPOINTMENT_DONE);
						$MESSAGE1   = str_replace("[FEED_BACK]",$feedBack,$MESSAGE1);
						$MESSAGE    = urlencode(str_replace("[AMOUNT]",_FormatNumberV2($Amount),$MESSAGE1));
					}
					if( $donation == 1) {
						$MESSAGE1   = str_replace("[AMOUNT]",_FormatNumberV2($Amount),SMS_DONATION_CONFIRMATION);
						$MESSAGE    =  urlencode(str_replace("[CHARITYNAME]",$charityname,$MESSAGE1));
					}
					$FIND_ARRAY			= array("[SMS_USER]","[SMS_PASS]","[MESSAGE]","[MOBILE]","[DAY]","[MONTH]","[YEAR]","[HOUR]","[MIN]");
					$REPL_ARRAY			= array(SMS_USER,SMS_PASS,$MESSAGE,$MOBILE,date("d"),date("m"),date("Y"),date("H"),date("i"));
					$SMS_GATEWAY_URL 	= str_replace($FIND_ARRAY,$REPL_ARRAY,SMS_GATWAY_URL);
					$ch 				= curl_init($SMS_GATEWAY_URL);
					// curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
					curl_setopt($ch, CURLOPT_HEADER,0);  			// DO NOT RETURN HTTP HEADERS
					curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  	// RETURN THE CONTENTS
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT  ,0);
					$SMS_CONTENT 		= curl_exec($ch);
					AppointmentCollection::SaveSMSResponse($collectionRaw->appointment_id,$SMS_CONTENT,$SMS_GATEWAY_URL);
				}else{
					$SMS_CONTENT        = "ISSUE WITH MOBILE NUMBER (".$collectionRaw->mobile_no.") WITH COUNTRY CODE.";
					AppointmentCollection::SaveSMSResponse($collectionRaw->appointment_id,$SMS_CONTENT,"");
				}
			}
        }
    }
 
    public static function SendAppointmentSMS($request){
		if(SMS_ON){
			$AdminUser 		= AdminUser::find($request->collection_by);
		
			/** CHANGES RELATED TO SETTING APPOINTMENT SMS CONFIRMATION BASED ON NEW CONTACT DETAILS */
			$ContactDetails = CustomerContactDetails::getNotificationInformation($request->customer_id);
			$Customer 		= CustomerMaster::find($request->customer_id);
			$CompanyDetail 	= CompanyMaster::find($Customer->company_id);
			/** CHANGES RELATED TO SETTING APPOINTMENT SMS CONFIRMATION BASED ON NEW CONTACT DETAILS */
			
			if(!empty($ContactDetails['SMS_CONTACT']))
			{
				$SMS_MOBILENOS = rtrim($ContactDetails['SMS_CONTACT'],",");
				$mobile_nos	= explode(",",$SMS_MOBILENOS);
				$MOBILENOS	= "";
				$COMMA		= "";
				foreach ($mobile_nos as $mobile) {
					$mobile			= trim($mobile);
					$countrycode 	= substr($mobile,0,2);
					if ($countrycode != 91 && strlen($mobile) == 10) {
						#$MOBILENOS .= $COMMA."91".$mobile;
						$MOBILENOS .= $COMMA.$mobile;
						$COMMA		= ",";
					}
				}
				
				if ($MOBILENOS != "") {
					$MOBILE	 			= $MOBILENOS;
					$COLLECTION_BY		= (isset($AdminUser->firstname) && !empty($AdminUser->firstname)) ? $AdminUser->firstname : "";
					$COLLECTION_TIME	= date("H:i",strtotime($request->app_date_time));
					$COMPANY_NAME   	= (isset($CompanyDetail->company_name) && !empty($CompanyDetail->company_name)) ? $CompanyDetail->company_name : "";
					$MESSAGE			= urlencode(str_replace(array("[COMPANY_NAME]","[NAME]","[TIME]"),array($COMPANY_NAME,$COLLECTION_BY,$COLLECTION_TIME),SMS_APPOINTMENT_CONFIRMATION));
					$FIND_ARRAY			= array("[SMS_USER]","[SMS_PASS]","[MESSAGE]","[MOBILE]","[DAY]","[MONTH]","[YEAR]","[HOUR]","[MIN]");
					$REPL_ARRAY			= array(SMS_USER,SMS_PASS,$MESSAGE,$MOBILE,date("d"),date("m"),date("Y"),date("H"),date("i"));
					$SMS_GATEWAY_URL 	= str_replace($FIND_ARRAY,$REPL_ARRAY,SMS_GATWAY_URL);
					$ch 				= curl_init($SMS_GATEWAY_URL);
					// curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
					curl_setopt($ch, CURLOPT_HEADER,0);  			// DO NOT RETURN HTTP HEADERS
					curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  	// RETURN THE CONTENTS
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT  ,0);
					$SMS_CONTENT 		= curl_exec($ch);
					AppointmentCollection::SaveSMSResponse($request->appointment_id,$SMS_CONTENT,$SMS_GATEWAY_URL);
				} else {
					$SMS_CONTENT = "ISSUE WITH MOBILE NUMBER (".$SMS_MOBILENOS.") WITH COUNTRY CODE.";
					AppointmentCollection::SaveSMSResponse($request->appointment_id,$SMS_CONTENT,"");
				}
			}
		}
    }

    public static function sendCustomerOTP($MOBILE,$OTP_CODE){
		if(SMS_ON){
			$MESSAGE			= urlencode("Dear Let's Reycle Customer, Your verification code is : ".$OTP_CODE.".");
			$FIND_ARRAY			= array("[SMS_USER]","[SMS_PASS]","[MESSAGE]","[MOBILE]");
			$REPL_ARRAY			= array(SMS_USER,SMS_PASS,$MESSAGE,$MOBILE);
			$SMS_GATEWAY_URL 	= str_replace($FIND_ARRAY,$REPL_ARRAY,SMS_GATWAY_URL);
			$ch 				= curl_init($SMS_GATEWAY_URL);
			// curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
			curl_setopt($ch, CURLOPT_HEADER,0);  			// DO NOT RETURN HTTP HEADERS
			curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  	// RETURN THE CONTENTS
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT  ,0);
			$SMS_CONTENT 		= curl_exec($ch);
			return ['SMS_CONTENT'=>$SMS_CONTENT,'SMS_GATEWAY_URL'=>$SMS_GATEWAY_URL];

		// $clsappointment		= new clsappointment();
		//  $clsappointment->SaveSMSResponse($this->customer_id,$SMS_CONTENT,$SMS_GATEWAY_URL);
		}
	}


	/**
	* Created By Sachin for Corporate App Forgot Passowrd
	*/
	public static function sendMessage($mobile_no,$message){
		if(!empty($mobile_no) && !empty($message))
		{
			$mobile_no		= trim($mobile_no);
			$countrycode 	= substr($mobile_no,0,2);
			
			if ($countrycode != 91 && strlen($mobile_no) == 10) {
				// $mobile_no = "91".$mobile_no;
			} else {
				return false;
			}
			$SMS_CONTENT 	 = '';
			$SMS_GATEWAY_URL = '';
			
				$FIND_ARRAY			= array("[SMS_USER]","[SMS_PASS]","[MESSAGE]","[MOBILE]");
				$REPL_ARRAY			= array(SMS_USER,SMS_PASS,urlencode(HTMLVarConv($message)),$mobile_no);
				$SMS_GATEWAY_URL 	= str_replace($FIND_ARRAY,$REPL_ARRAY,SMS_GATWAY_URL);

				$header=array(
							  /*'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
							  'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,;*/'q=0.8',
							  'Accept-Language: en-us,en;q=0.5',
							  'Accept-Encoding: gzip,deflate',
							  'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
							  'Keep-Alive: 115',
							  'Connection: keep-alive',
							);
				
				$ch 				= curl_init($SMS_GATEWAY_URL);
				curl_setopt($ch,CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12');
				// curl_setopt($ch,CURLOPT_FOLLOWLOCATION ,false);
				curl_setopt($ch,CURLOPT_HEADER,false);  			// DO NOT RETURN HTTP HEADERS
				curl_setopt($ch,CURLOPT_RETURNTRANSFER  ,true);  	// RETURN THE CONTENTS
				curl_setopt($ch,CURLOPT_CONNECTTIMEOUT  ,false);
				curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
				curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
				//curl_setopt($ch,CURLOPT_COOKIEFILE,'cookies.txt');
				//curl_setopt($ch,CURLOPT_COOKIEJAR,'cookies.txt');
				//curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
				$SMS_CONTENT 		= curl_exec($ch);
			
				AppointmentCollection::SaveSMSResponse(0,$SMS_CONTENT,$SMS_GATEWAY_URL);
		}
	}

	/*
	Use 	: send OTP to user for login
	Autho 	: Axay Shah
	Date 	: 16 Aug,2019
	*/
	public static function AuthByOTP($MOBILE,$MESSAGE){
		$SMS_CONTENT = "";
			if (strlen($MOBILE) == 10) {
					// $MOBILE = "91".$MOBILE;
			}
		// if(OTP_LOGIN_ON){
			$MESSAGE			= urlencode($MESSAGE);
			$FIND_ARRAY			= array("[SMS_USER]","[SMS_PASS]","[MESSAGE]","[MOBILE]");
			$REPL_ARRAY			= array(SMS_USER,SMS_PASS,$MESSAGE,$MOBILE);
			$SMS_GATEWAY_URL 	= str_replace($FIND_ARRAY,$REPL_ARRAY,SMS_GATWAY_URL);
			$ch 				= curl_init($SMS_GATEWAY_URL);
			
			// curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
			curl_setopt($ch, CURLOPT_HEADER,0);  			// DO NOT RETURN HTTP HEADERS
			curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  	// RETURN THE CONTENTS
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT  ,0);
			$info 				= curl_getinfo($ch);
			$SMS_RESPONSE 		= curl_exec($ch);
			$SMS_CONTENT 		= json_encode(array("URL"=>$SMS_GATEWAY_URL,"RESPONSE"=>$SMS_RESPONSE));
		// }
		return $SMS_CONTENT;
	}
}

