<?php
namespace App\Classes;
include_once(__DIR__.'/einvoice/include/bootstrape.php');
// ini_set ('display_errors', 'on');
// ini_set ('log_errors', 'on');
// ini_set ('display_startup_errors', 'on');
// ini_set ('error_reporting', E_ALL);
// define('ROOT_DIR', __DIR__);
Class Einvoice
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

	public function  __construct($USERNAME="CygnetGSP27",$PASSWORD="CygnetGSP27@Pass",$GSTIN="27AACPH8447G002")
	{

		$this->domain       = "https://staging-passthroughapi.cygnetgsp.in/eivital";
        $this->username     = $USERNAME;
       	$this->password     = $PASSWORD;
        $this->GSTIN        = $GSTIN;
       $this->CLIENT_ID    = 'ztio3in1MJFiqyDhaYRAksYokz2l3Yx6UtcxtpewW3AMj6MDIhB6CmsvpMwkkvm6VnIN7SNRTaWnPB4HsAMtvizwY2MQEk1cJ/5vm0WHCBI+gOFk1TEu9XwmAUUSPpXb';
		$this->SECRET       = 'nu+YuyjXGzB6S3/g2okECwsQAYbtdAfLUZgtoYT2JgZoM5AFecc1hWv9OpEhK3hjU1TZO0x4j/B/8Aas3UATWePlTvk0WB+R1jDB75ZwqCUrdHphGDUZAIQKAvb68az0ZQVJotVGKFp9izsiq4E3K1I8riq4eGUBIWOgry1Tt4lja2FeqM7vYzXD7MJ7WldpXGTPP87qB09+Kaiy3vcWdDi3zSaAptpu2NgC1Ov/wXndUg1uq0G5qNSP23My4pbO';
        $this->API_VERSION  = 'v1.03';
        $this->TABLE        = 'eway_bill_request_response';
        $this->ACCESS_TOKEN = "";
        $this->randstring 	= "";

        // 24AACPH8447G002	CygnetGSP24	CygnetGSP24@Pass

	}

	public function generateRandomString()
	{
		return $this->randstring = generateRandomString();
	}


	public function getAuthentication(){
		$this->generateRandomString();
		$keyFilePath        = __DIR__. '/einvoice/key/EINV_PublicKey_Sandbox.pem';
	   	$this->encAppKey    = generateRsaEncryption($this->randstring, $keyFilePath); // Generate encrypted
	   	$encPassword        = generateRsaEncryption($this->password, $keyFilePath); // Encrypting password
	   	$loginHeaders       = [
	   							"Accept : application/json",
								"client_id : ".$this->CLIENT_ID,
								"client_secret : ".$this->SECRET];
		$loginPayload       = [
								"UserName" 					=> $this->username,
							    "Password" 					=> $encPassword,
							    "AppKey" 					=> $this->encAppKey,
							    "ForceRefreshAccessToken" 	=> true
							];
		$loginPayload 		= array("Data"=>$loginPayload);
		$loginUrl           = $this->domain."/".$this->API_VERSION."/auth";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $loginUrl,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => json_encode($loginPayload),
		  CURLOPT_HTTPHEADER => array(
		    "Content-Type: application/json",
		    "client_id: ".$this->CLIENT_ID,
		    "client_secret:".$this->SECRET
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
		  return $err;
		} else {
		  return $response;
		}
	}


	public function prd($string)
	{
		echo "<pre>"; print_r($string);exit;
	}


	public function GenerateEInvoice($requestData){
		$url 	= "https://staging-passthroughapi.cygnetgsp.in/eicore/v1.03/Invoice";
		$Auth 	= $this->getAuthentication();
		if(!empty($Auth)){
			$Auth = json_decode($Auth);
			if(isset($Auth->Status) && $Auth->Status == 1){
				$this->ACCESS_TOKEN = $Auth->Data->AuthToken;
				$sekCypher 			= $Auth->Data->Sek;
				$this->SEK          = generateAesEncryption($sekCypher, $this->randstring, 1);

				$Header = [
					"Accept : application/json",
					"client_id : ".$this->CLIENT_ID,
					"client_secret : ".$this->SECRET,
					"Gstin : ".$this->GSTIN,
					"user_name : ".$this->username,
					"AuthToken : ".$this->ACCESS_TOKEN,
				];


				// $requestData 	= '{"Version":"1.1","TranDtls":{"TaxSch":"GST","SupTyp":"B2B","RegRev":"Y","EcmGstin":null,"IgstOnIntra":"N"},"DocDtls":{"Typ":"INV","No":"DDOCC/00527","Dt":"12/04/2021"},"SellerDtls":{"Gstin":"27AACPH8447G002","LglNm":"NIC company pvt ltd","TrdNm":"NIC Industries","Addr1":"5th block, kuvempu layout","Addr2":"kuvempu layout","Loc":"GANDHINAGAR","Pin":400068,"Stcd":"27","Ph":"9000000000","Em":"abc@gmail.com"},"BuyerDtls":{"Gstin":"29AWGPV7107B1Z1","LglNm":"XYZ company pvt ltd","TrdNm":"XYZ Industries","Pos":"12","Addr1":"7th block, kuvempu layout","Addr2":"kuvempu layout","Loc":"GANDHINAGAR","Pin":562160,"Stcd":"29","Ph":"91111111111","Em":"xyz@yahoo.com"},"DispDtls":{"Nm":"ABC company pvt ltd","Addr1":"7th block, kuvempu layout","Addr2":"kuvempu layout","Loc":"Banagalore","Pin":562160,"Stcd":"29"},"ShipDtls":{"Gstin":"29AWGPV7107B1Z1","LglNm":"CBE company pvt ltd","TrdNm":"kuvempu layout","Addr1":"7th block, kuvempu layout","Addr2":"kuvempu layout","Loc":"Banagalore","Pin":562160,"Stcd":"29"},"ItemList":[{"SlNo":"1","PrdDesc":"Rice","IsServc":"N","HsnCd":"30049057","Barcde":"123456","Qty":100.345,"FreeQty":10,"Unit":"BAG","UnitPrice":99.545,"TotAmt":9988.84,"Discount":10,"PreTaxVal":1,"AssAmt":9978.84,"GstRt":12.0,"IgstAmt":1197.46,"CgstAmt":0,"SgstAmt":0,"CesRt":5,"CesAmt":498.94,"CesNonAdvlAmt":10,"StateCesRt":12,"StateCesAmt":1197.46,"StateCesNonAdvlAmt":5,"OthChrg":10,"TotItemVal":12897.7,"OrdLineRef":"3256","OrgCntry":"AG","PrdSlNo":"12345","BchDtls":{"Nm":"123456","ExpDt":"01/08/2020","WrDt":"01/09/2020"},"AttribDtls":[{"Nm":"Rice","Val":"10000"}]}],"ValDtls":{"AssVal":9978.84,"CgstVal":0,"SgstVal":0,"IgstVal":1197.46,"CesVal":508.94,"StCesVal":1202.46,"Discount":10,"OthChrg":20,"RndOffAmt":0.3,"TotInvVal":12908,"TotInvValFc":12897.7},"PayDtls":{"Nm":"ABCDE","AccDet":"5697389713210","Mode":"Cash","FinInsBr":"SBIN11000","PayTerm":"100","PayInstr":"Gift","CrTrn":"test","DirDr":"test","CrDay":100,"PaidAmt":10000,"PaymtDue":5000},"RefDtls":{"InvRm":"TEST","DocPerdDtls":{"InvStDt":"01/08/2020","InvEndDt":"01/09/2020"},"PrecDocDtls":[{"InvNo":"DOC/002","InvDt":"01/08/2020","OthRefNo":"123456"}],"ContrDtls":[{"RecAdvRefr":"Doc/003","RecAdvDt":"01/08/2020","TendRefr":"Abc001","ContrRefr":"Co123","ExtRefr":"Yo456","ProjRefr":"Doc-456","PORefr":"Doc-789","PORefDt":"01/08/2020"}]},"AddlDocDtls":[{"Url":"https://einv-apisandbox.nic.in","Docs":"Test Doc","Info":"Document Test"}],"ExpDtls":{"ShipBNo":"A-248","ShipBDt":"01/08/2020","Port":"INABG1","RefClm":"N","ForCur":"AED","CntCode":"AE","ExpDuty":null}}';
				// $requestData = '{"merchant_key":"10AF64BB99992F6B6187503D20444EA2","username":"NEPRAIND@1_API_NEP","password":"N$praInd@2021","user_gst_in":"05AAAAH1426Q1ZO","SellerDtls":{"Gstin":"NEPRAIND@1_API_NEP","LglNm":"Nepra Resource Management Private Limited","TrdNm":"Nepra Resource Management Private Limited","Addr1":"MRF-1, TRANCHING GROUND,INDORE BETUL-NEMAWAR ROAD DEVGARUDIYA INDORE, MP.","Addr2":null,"Pin":452020,"Stcd":"23","Ph":null,"Em":null},"BuyerDtls":{"Gstin":"23AKFPR3047K1Z2","LglNm":"SAKSHI PILASTIC","TrdNm":"SAKSHI PILASTIC","Addr1":"365\/2\/1\/2,, avantika nagar, sanwer road,","Addr2":null,"Loc":"INDORE","Pin":452001,"Stcd":"23","Ph":null,"Em":null},"DispDtls":null,"ShipDtls":null,"EwbDtls":null,"version":"1.1","TranDtls":{"TaxSch":"GST","SupTyp":"B2B","RegRev":null,"EcmGstin":null,"IgstOnIntra":"N"},"DocDtls":{"Typ":"INV","No":"3100069","Dt":"14\/04\/2021"},"ValDtls":{"AssVal":"720","CgstVal":"64.8","SgstVal":"64.8","IgstVal":"0","CesVal":"0","StCesVal":"0","Discount":"0","OthChrg":"0","RndOffAmt":"0.4","TotInvVal":"850"},"ItemList":[{"SlNo":"1","PrdDesc":"Thermocol Waste","IsServc":"N","HsnCd":"39219010","Qty":"60","Unit":"KGS","UnitPrice":"12","TotAmt":"720","Discount":"0","PreTaxVal":"0","AssAmt":"720","GstRt":"18","IgstAmt":"0","CgstAmt":"64.8","SgstAmt":"64.8","CesRt":"0","CesAmt":"0","CesNonAdvlAmt":"0","StateCesRt":"0","StateCesAmt":"0","StateCesNonAdvlAmt":"0","OthChrg":"0","TotItemVal":"849.6"}]}';

				// $encJsonPayLoad = generateAesEncryption($requestData, $this->SEK);


				// prd(json_encode($requestData));





				$encJsonPayLoad = generateAesEncryption(json_encode($requestData), $this->SEK);

				$requestPayload = ['Data' => $encJsonPayLoad];
				// prd($requestPayload);
				$curl = curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL => $url,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => json_encode($requestPayload),
				  CURLOPT_HTTPHEADER => array(
				    "Content-Type: application/json",
				    "client_id : ".$this->CLIENT_ID,
					"client_secret : ".$this->SECRET,
					"Gstin : ".$this->GSTIN,
					"user_name : ".$this->username,
					"AuthToken : ".$this->ACCESS_TOKEN,
				  ),
				));

				$response 	= curl_exec($curl);
				$err 		= curl_error($curl);
				curl_close($curl);

				if ($err) {
				  	$response = json_decode($err);
				} else {
					$result 				= json_decode($response);
					if(isset($result) && $result->Status == 1){
						$decryptedJsonPayLoad   = generateAesEncryption($result->Data, $this->SEK, 1);
						$Data 					= $decryptedJsonPayLoad;
						$Status 				= $result->Status;
						$ErrorDetails 			= $result->ErrorDetails;
						$InfoDtls 				= $result->InfoDtls;
					}else{
						$Data 					= $result->Data;
						$Status 				= $result->Status;
						$ErrorDetails 			= $result->ErrorDetails;
						$InfoDtls 				= $result->InfoDtls;
					}
					$res["Status"] 			= $Status;
					$res["ErrorDetails"] 	= $ErrorDetails;
					$res["InfoDtls"] 		= $InfoDtls;
					$res["Data"] 			= $Data;
					return $res;
				}
			}
		}
	}

	public function CancelEInvoice($requestData){
		$url 	= "https://staging-passthroughapi.cygnetgsp.in/eicore/v1.03/Invoice/Cancel";
		$Auth 	= $this->getAuthentication();
		if(!empty($Auth)){
			$Auth = json_decode($Auth);
			if(isset($Auth->Status) && $Auth->Status == 1){
				$this->ACCESS_TOKEN = $Auth->Data->AuthToken;
				$sekCypher 			= $Auth->Data->Sek;
				$this->SEK          = generateAesEncryption($sekCypher, $this->randstring, 1);

				$encJsonPayLoad = generateAesEncryption(json_encode($requestData), $this->SEK);

				$requestPayload = ['Data' => $encJsonPayLoad];

				$curl = curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL => $url,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => json_encode($requestPayload),
				  CURLOPT_HTTPHEADER => array(
				    "Content-Type: application/json",
				    "client_id : ".$this->CLIENT_ID,
					"client_secret : ".$this->SECRET,
					"Gstin : ".$this->GSTIN,
					"user_name : ".$this->username,
					"AuthToken : ".$this->ACCESS_TOKEN,
				  ),
				));

				$response 	= curl_exec($curl);
				$err 		= curl_error($curl);
				curl_close($curl);

				if ($err) {
				  	$response = json_decode($err);
				} else {
					$result 				= json_decode($response);
					if(isset($result) && $result->Status == 1){
						$decryptedJsonPayLoad   = generateAesEncryption($result->Data, $this->SEK, 1);
						$Data 					= $decryptedJsonPayLoad;
						$Status 				= $result->Status;
						$ErrorDetails 			= $result->ErrorDetails;
						$InfoDtls 				= $result->InfoDtls;
					}else{
						$Data 					= $result->Data;
						$Status 				= $result->Status;
						$ErrorDetails 			= $result->ErrorDetails;
						$InfoDtls 				= $result->InfoDtls;
					}
					$res["Status"] 			= $Status;
					$res["ErrorDetails"] 	= $ErrorDetails;
					$res["InfoDtls"] 		= $InfoDtls;
					$res["Data"] 			= $Data;
					return $res;
				}
			}
		}
	}
}