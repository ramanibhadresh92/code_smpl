<?php
/* 
Use     : Class to call partner appointment 
Author  : Axay Shah
Date    : 10 Dec,2018

*/
namespace App\Classes;
use App\Models\AppointmentCollection;
class PartnerAppointment {
    
    public function UpdatePartnerSiteAppointmentStatus($appointment_id, $para_status_id)
    {
		$api_function_url 			= RR_API_URL.'appointments/changestatus.json';
		$data['appointment_id']		= $appointment_id;
		$data['para_status_id']		= $para_status_id;
		$Result 					= $this->ApiCall($api_function_url,$data,RR_AUTH_USER,RR_AUTH_PASS);
		return $Result;
    }
	
	public function SendNotificationToThirdParty($appointment_id, $app_date_time,$collection_by_user)
    {
		$api_function_url 			= RR_API_URL.'appointments/sendpushmessage.json';
		$data['appointment_id']		= $appointment_id;
		$data['app_date_time']		= $app_date_time;
		$data['collection_by_user']	= $collection_by_user;
		$Result 					= $this->ApiCall($api_function_url,$data,RR_AUTH_USER,RR_AUTH_PASS);
		return $Result;
    }
	
	public function GenerateHash($data)
	{
		$content		= json_encode($data);
		$hash = hash_hmac('sha256', $content, RR_HMAC_HASH_PRIVATE_KEY);
		return $hash;
	}

    public function ApiCall($api_function_url,$data,$AUTH_USER="",$AUTH_PASS="")
    {
        $url	= $api_function_url;

		//url-ify the data for the POST
		$fields_string = '';
		if (!empty($data)) {
			foreach($data as $key=>$value) {
				$fields_string .= $key.'='.$value.'&'; 
			}
		}
		$fields_string = rtrim($fields_string,'&');

		//open connection
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$url);
		if ($AUTH_USER != "" && $AUTH_PASS != "") {
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, "$AUTH_USER:$AUTH_PASS"); 
		}
		curl_setopt($ch,CURLOPT_POST,count($data));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Hash: '.$this->GenerateHash($data)));
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10); # timeout after 10 seconds, you can increase it
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);  # Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)"); # Some server may refuse your request if you dont pass user agent
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//execute post
		$result = curl_exec($ch);
		//close connection
		curl_close($ch);
		return $result;
        
    }   
}

