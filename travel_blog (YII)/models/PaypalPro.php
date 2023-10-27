<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class PaypalPro extends \yii\base\Model
{
    public $apiUsername = 'adel.merchant_api1.123.com';
    public $apiPassword = 'LLPGFA8QRYKVMSE7';
    public $apiSignature = 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-AhtMV6zTyLhMFdrqZv9f93i.7HfZ';
    public $apiEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
    public $subject = '';
    public $authToken = '';
    public $authSignature = '';
    public $authTimestamp = '';
    public $useProxy = FALSE;
    public $proxyHost = '127.0.0.1';
    public $proxyPort = 808;
    public $paypalURL = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
    public $version = '65.1';
    public $ackSuccess = 'SUCCESS';
    public $ackSuccessWarning = 'SUCCESSWITHWARNING';
    
    public function __construct($config = array()){ 
        ob_start();
        if (count($config) > 0){
            foreach ($config as $key => $val){
                if (isset($key) && $key == 'live' && $val == 1){
                    $this->paypalURL = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
                }else if (isset($this->$key)){
                    $this->$key = $val;
                }
            }
        }
    }
    public function nvpHeader(){
	 $apiUsername = 'adel.merchant_api1.123.com';
     $apiPassword = 'LLPGFA8QRYKVMSE7';
     $apiSignature = 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-AhtMV6zTyLhMFdrqZv9f93i.7HfZ';
     $apiEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
     $subject = '';
     $authToken = '';
     $authSignature = '';
     $authTimestamp = '';
     $useProxy = FALSE;
     $proxyHost = '127.0.0.1';
     $proxyPort = 808;
     $paypalURL = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
     $version = '65.1';
     $ackSuccess = 'SUCCESS';
     $ackSuccessWarning = 'SUCCESSWITHWARNING';
        $nvpHeaderStr = "";
    
        if((!empty($apiUsername)) && (!empty($apiPassword)) && (!empty($apiSignature)) && (!empty($subject))) {
            $authMode = "THIRDPARTY";
        }else if((!empty($apiUsername)) && (!empty($apiPassword)) && (!empty($apiSignature))) {
            $authMode = "3TOKEN";
        }elseif (!empty($authToken) && !empty($authSignature) && !empty($authTimestamp)) {
            $authMode = "PERMISSION";
        }elseif(!empty($subject)) {
            $authMode = "FIRSTPARTY";
        switch($authMode) {
		
            case "3TOKEN" : 
			
                $nvpHeaderStr = "&PWD=".urlencode($apiPassword)."&USER=".urlencode($apiUsername)."&SIGNATURE=".urlencode($apiSignature);
                break;
            case "FIRSTPARTY" :
                $nvpHeaderStr = "&SUBJECT=".urlencode($subject);
                break;
            case "THIRDPARTY" :
                $nvpHeaderStr = "&PWD=".urlencode($apiPassword)."&USER=".urlencode($apiUsername)."&SIGNATURE=".urlencode($apiSignature)."&SUBJECT=".urlencode($subject);
                break;		
            case "PERMISSION" :
                $nvpHeaderStr = PaypalPro::formAutorization($authToken,$authSignature,$authTimestamp);
                break;
        }
		
        return $nvpHeaderStr;
    }
    
    public function hashCall($methodName,$nvpStr){
		
	$apiUsername = 'adel.merchant_api1.123.com';
    $apiPassword = 'LLPGFA8QRYKVMSE7';
    $apiSignature = 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-AhtMV6zTyLhMFdrqZv9f93i.7HfZ';
    $apiEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
    $subject = '';
    $authToken = '';
    $authSignature = '';
    $authTimestamp = '';
    $useProxy = FALSE;
    $proxyHost = '127.0.0.1';
    $proxyPort = 808;
    $paypalURL = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
    $version = '65.1';
    $ackSuccess = 'SUCCESS';
    $ackSuccessWarning = 'SUCCESSWITHWARNING';
		
        // form header string
        $nvpheader = PaypalPro::nvpHeader();

        //setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$apiEndpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
    
        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        
        //in case of permission APIs send headers as HTTPheders
        if(!empty($authToken) && !empty($authSignature) && !empty($authTimestamp))
         {
            $headers_array[] = "X-PP-AUTHORIZATION: ".$nvpheader;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);
            curl_setopt($ch, CURLOPT_HEADER, false);
        }
        else 
        {
            $nvpStr = $nvpheader.$nvpStr;
        }
        if($useProxy)
            curl_setopt ($ch, CURLOPT_PROXY, $proxyHost.":".$proxyPort); 
    
        //check if version is included in $nvpStr else include the version.
        if(strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
            $nvpStr = "&VERSION=" . urlencode($version) . $nvpStr;	
        }
        
        $nvpreq="METHOD=".urlencode($methodName).$nvpStr;
        //setting the nvpreq as POST FIELD to curl
        curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);
    
        //getting response from server
        $response = curl_exec($ch);
        
        //convrting NVPResponse to an Associative Array
        $nvpResArray = PaypalPro::deformatNVP($response);
        $nvpReqArray = PaypalPro::deformatNVP($nvpreq);
        $_SESSION['nvpReqArray']=$nvpReqArray;
    
        if (curl_errno($ch)) {
            die("CURL send a error during perform operation: ".curl_error($ch));
        } else {
            //closing the curl
            curl_close($ch);
        }
    
        return $nvpResArray;
    }
    
    public function deformatNVP($nvpstr){
        $intial=0;
        $nvpArray = array();
    
        while(strlen($nvpstr)){
            //postion of Key
            $keypos = strpos($nvpstr,'=');
            //position of value
            $valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
    
            /*getting the Key and Value values and storing in a Associative Array*/
            $keyval = substr($nvpstr,$intial,$keypos);
            $valval = substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
            //decoding the respose
            $nvpArray[urldecode($keyval)] =urldecode( $valval);
            $nvpstr = substr($nvpstr,$valuepos+1,strlen($nvpstr));
         }
        return $nvpArray;
    }
    
    public function formAutorization($auth_token,$auth_signature,$auth_timestamp){
        $authString="token=".$auth_token.",signature=".$auth_signature.",timestamp=".$auth_timestamp ;
        return $authString;
    }
    
    public function paypalCall($params){
        $recurringStr = (array_key_exists("recurring",$params) && $params['recurring'] == 'Y')?'&RECURRING=Y':'';
        $nvpstr = "&PAYMENTACTION=".$params['paymentAction']."&AMT=".$params['amount']."&CREDITCARDTYPE=".$params['creditCardType']."&ACCT=".$params['creditCardNumber']."&EXPDATE=".$params['expMonth'].$params['expYear']."&CVV2=".$params['cvv']."&FIRSTNAME=".$params['firstName']."&LASTNAME=".$params['lastName']."&CITY=".$params['city']."&ZIP=".$params['zip']."&COUNTRYCODE=".$params['countryCode']."&CURRENCYCODE=".$params['currencyCode'].$recurringStr;
    
        $resArray = PaypalPro::hashCall("DoDirectPayment",$nvpstr);
        return $resArray;
    }
}
?>