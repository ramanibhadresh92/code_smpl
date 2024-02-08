<?php
namespace App\Classes\ewaybill;
include_once(__DIR__.'/ewaybill/include/bootstrape.php');
ini_set ('display_errors', 'on');
ini_set ('log_errors', 'on');
ini_set ('display_startup_errors', 'on');
ini_set ('error_reporting', E_ALL);
define('ROOT_DIR', __DIR__);
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
        $this->password     = "Admin$123";
        $this->GSTIN        = "05AAAAH1426Q1ZO";
        $this->CLIENT_ID    = 'ztio3in1MJFiqyDhaYRAksYokz2l3Yx6UtcxtpewW3AMj6MDIhB6CmsvpMwkkvm6VnIN7SNRTaWnPB4HsAMtvizwY2MQEk1cJ/5vm0WHCBI+gOFk1TEu9XwmAUUSPpXb';
        $this->SECRET       = 'nu+YuyjXGzB6S3/g2okECwsQAYbtdAfLUZgtoYT2JgZoM5AFecc1hWv9OpEhK3hjU1TZO0x4j/B/8Aas3UATWePlTvk0WB+R1jDB75ZwqCUrdHphGDUZAIQKAvb68az0ZQVJotVGKFp9izsiq4E3K1I8riq4eGUBIWOgry1Tt4lja2FeqM7vYzXD7MJ7WldpXGTPP87qB09+Kaiy3vcWdDi3zSaAptpu2NgC1Ov/wXndUg1uq0G5qNSP23My4pbO';
        $this->API_VERSION  = 'v1.03';
        $this->TABLE        = 'eway_bill_request_response';
    }

    public function generateRandomString()
    {
        $this->randstring = generateRandomString();
    }

    public function getAuthentication() 
    {
        $this->generateRandomString();
        $keyFilePath        = __DIR__. '/ewaybill/key/EWB_PublicKey_Sandbox.pem';
        $this->encAppKey    = generateRsaEncryption($this->randstring, $keyFilePath); // Generate encrypted app key
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

    public function ewayBillApi($requestData,$Auth,$Sek)
    {
       
        $Auth   = "sSuFF5G4VF9mUvvRRXz0v8VF7";
        $Sek    = "w6xEIFs2CO2BYZO20SCecGRmzkxsa3lDnyLm8wxZFintzgdTaQogGS1IHXkOFSu3";
        $this->ACCESS_TOKEN = $Auth;
        $this->SEK   = $Sek;

        // $getAuthentication = $this->getAuthentication();
        //  if (isset($getAuthentication['status']) && $getAuthentication['status'] == 1) 
        //  {
        //     $data = json_decode($getAuthentication['response']);
        //     if (isset($data) && $data->status == 1) {
        //         $this->ACCESS_TOKEN = $data->authtoken;
        //         $this->SEK          = $data->sek;
        //     } else {
        //         return $getAuthentication;
        //     }
        // } else {
        //     return $getAuthentication;
        // }

        if($this->ACCESS_TOKEN)
        {
            if(isset($this->ACCESS_TOKEN)) {
                $headers        = [ "Accept : application/json",
                                    "client-id : ".$this->CLIENT_ID,
                                    "client-secret : ".$this->SECRET,
                                    "gstin : ".$this->GSTIN,
                                    "authtoken : ".$this->ACCESS_TOKEN];
                $decsek         = generateAesEncryption($this->SEK,$this->encAppKey,1);
                $requestSave    = $requestData;
                $requestData    = generateAesEncryption(json_encode($requestData),$decsek,0);
                $requestPayload = ['action' => 'GENEWAYBILL','data' => $requestData];
                $url            = $this->domain . "/ewaybillapi/".$this->API_VERSION."/EwayApi";
                $response       = connect($url, 'POST', $requestPayload, $headers);

                dd($response);
                return $response;
            }
        }
    }

    public function errorCode($errorCode) 
    {
        $error          = array(); 
        $ErrorArray     = explode(",", $errorCode->errorCodes);
        $fileErrorArray = json_decode(file_get_contents(ROOT_DIR."/errorCode.txt"),true);
        foreach($ErrorArray as $key =>$code){
            foreach ($fileErrorArray as $fileKey => $fileError) {
                if($fileError['errorCode'] == $code) {
                    $error[$key] = $fileError['errorDesc'];
                }
            }
        }
        return $error;
    }

    public function prd($string)
    {
        echo "<pre>"; print_r($string);exit;
    }
}