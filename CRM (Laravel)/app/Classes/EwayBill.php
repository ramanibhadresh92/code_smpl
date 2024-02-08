<?php
namespace App\Classes;
include_once(__DIR__.'/ewaybill/include/bootstrape.php');
Class EwayBill
{
	var $action;
	var $username;
	var $password;
	var $app_key;
	var $domain;
	var $GSTIN;
	var $CLIENT_ID;
	var $SECRET;
	var $API_VERSION;
	var $randstring;
	var $encAppKey;
	var $ACCESS_TOKEN;
	var $SEK;
	var $TABLE;

	public function  __construct()
	{
		$this->domain       = "https://staging-passthroughapi.cygnetgsp.in";
		$this->username     = "05AAAAH1426Q1ZO";
		// $this->password     = "abc123@@";
		$this->password     = "abc123@@";
		$this->GSTIN        = "05AAAAH1426Q1ZO";
		$this->CLIENT_ID    = 'ztio3in1MJFiqyDhaYRAksYokz2l3Yx6UtcxtpewW3AMj6MDIhB6CmsvpMwkkvm6VnIN7SNRTaWnPB4HsAMtvizwY2MQEk1cJ/5vm0WHCBI+gOFk1TEu9XwmAUUSPpXb';
		$this->SECRET       = 'nu+YuyjXGzB6S3/g2okECwsQAYbtdAfLUZgtoYT2JgZoM5AFecc1hWv9OpEhK3hjU1TZO0x4j/B/8Aas3UATWePlTvk0WB+R1jDB75ZwqCUrdHphGDUZAIQKAvb68az0ZQVJotVGKFp9izsiq4E3K1I8riq4eGUBIWOgry1Tt4lja2FeqM7vYzXD7MJ7WldpXGTPP87qB09+Kaiy3vcWdDi3zSaAptpu2NgC1Ov/wXndUg1uq0G5qNSP23My4pbO';
		$this->API_VERSION  = 'v1.03';
		$this->TABLE        = 'eway_bill_request_response';
		$this->ACCESS_TOKEN = "";
	}

	public function generateRandomString()
	{
		return $this->randstring = generateRandomString();
	}

	public function getAuthentication()
	{
		// to gst in : 05AAAAH2043K1Z1
		$this->generateRandomString();
		$keyFilePath        = __DIR__. '/ewaybill/key/EWB_PublicKey_Sandbox.pem';
		$this->encAppKey    = generateRsaEncryption($this->randstring, $keyFilePath); // Generate encrypted
		$encPassword        = generateRsaEncryption($this->password, $keyFilePath); // Encrypting password
		$loginHeaders       = [ "Accept : application/json",
								"client-id : ".$this->CLIENT_ID,
								"client-secret : ".$this->SECRET,
								"Gstin : ".$this->GSTIN];
		$loginPayload       = [ 'action' => 'ACCESSTOKEN',
								'username' => $this->username,
								'password' => $encPassword,
								'app_key' => $this->encAppKey];
		$this->encAppKey    = $this->randstring;
		$loginUrl           = $this->domain . "/ewaybillapi/".$this->API_VERSION."/authenticate";

		$loginResponse      = connect($loginUrl, 'POST', $loginPayload, $loginHeaders);
		return $loginResponse;
	}

	public function errorCode($errorCode)
	{
		$error          = array();
		$ErrorArray     = explode(",", $errorCode);
	   	$fileErrorArray = json_decode(file_get_contents(__DIR__."/ewaybill/errorCode.txt"),true);
		foreach($ErrorArray as $key =>$code) {
			$errorCode = json_decode(base64_decode($code));
			$codeValue = $errorCode->errorCodes;
			$codeValue = trim($codeValue,',');
			foreach ($fileErrorArray as $fileKey => $fileError) {
				if($fileError['errorCode'] == $codeValue) {
					$error[$key]['errorDesc'] = $fileError['errorDesc'];
				}
			}
		}
		return $error;
	}

	public function prd($string)
	{
		echo "<pre>"; print_r($string);exit;
	}

	/*
	Develop By Axay Shah
	*/
	public function Authenticate()
	{
		$keyFilePath  = __DIR__. '/ewaybill/key/EWB_PublicKey_Sandbox.pem';
		$this->generateRandomString();
		$this->encAppKey    = generateRsaEncryption($this->randstring, $keyFilePath); // Generate
		$encPassword        = generateRsaEncryption($this->password, $keyFilePath); // Encrypting password
		$loginHeaders       = [ "Accept : application/json",
								"client-id : ".$this->CLIENT_ID,
								"client-secret : ".$this->SECRET,
								"Gstin : ".$this->GSTIN];
		$loginPayload       = [ 'action' => 'ACCESSTOKEN',
								'username' => $this->username,
								'password' => $encPassword,
								'app_key' => $this->encAppKey];
		$this->encAppKey    = $this->randstring;
		$loginUrl           = $this->domain . "/ewaybillapi/".$this->API_VERSION."/authenticate";
		$loginResponse      = connect($loginUrl, 'POST', $loginPayload, $loginHeaders);
		return $loginResponse;
	}

	/*
	Use     : Axay Shah
	Date    : 03 June 2020
	*/
	public function ewayBillApi($requestData)
	{
		$loginResponse  = $this->getAuthentication();
		if (isset($loginResponse['response']) && is_json($loginResponse['response'])) {
			$responseData       = json_decode($loginResponse['response'],1);
			$this->ACCESS_TOKEN = isset($responseData['authtoken']) ? $responseData['authtoken'] : null;
			$sekCypher          = isset($responseData['sek']) ? $responseData['sek'] : null;
			$this->SEK          = generateAesEncryption($sekCypher, $this->randstring, 1);
		} else {
			return $loginResponse;
		}
		$encJsonPayLoad = generateAesEncryption(json_encode($requestData), $this->SEK);
		if($this->ACCESS_TOKEN){
			if(isset($this->ACCESS_TOKEN)) {
				$header = [
					"Content-type: application/json",
					"Accept : application/json",
					"client-id : ".$this->CLIENT_ID,
					"client-secret : ".$this->SECRET,
					"Gstin : ".$this->GSTIN,
					"authtoken : ".$this->ACCESS_TOKEN
				];

				$requestPayload = ['action' => 'GENEWAYBILL','data' => $encJsonPayLoad];
				$url            = $this->domain."/ewaybillapi/".$this->API_VERSION."/ewayApi";
				$response       = connect($url, 'POST', $requestPayload, $header);
				return $response;
			}
		}
	}
}