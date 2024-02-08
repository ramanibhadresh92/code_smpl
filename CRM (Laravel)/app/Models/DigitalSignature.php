<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Facades\LiveServices;
use Illuminate\Support\Facades\Http;
class DigitalSignature extends Model
{
	/*
	USE : Digital signature 

	*/
	public static function DigitalSignature($pdfPath,$signgedDir){
		$clientID       = DIGI_SIGN_CLIENT_ID;
		$keyID          = DIGI_SIGN_KEY_ID;
		$accessKey      = DIGI_SIGN_ACCESS_KEY;
		$certified      = DIGI_SIGN_CERTIFIED;
		$appearanceId   = DIGI_SIGN_APPEARANCE_ID;
		$position       = DIGI_SIGN_POSITION;
		$locationText   = DIGI_SIGN_LOCATION_TXT;
		$tick           = DIGI_SIGN_TICK;
		$border         = DIGI_SIGN_BORDER;
		$sessionId      = DIGI_SIGN_SESSION_ID;
		$id             = DIGI_SIGN_ID;
		$docType        = DIGI_SIGN_DOC_TYPE;
		$version        = DIGI_SIGN_VERSION;
		$standAlone     = DIGI_SIGN_STANDALONE;
		$signVersion    = DIGI_SIGN_VERSION;
		$reasonText     = "";
		// $pdfPath        = public_path("/").'/signed/Test.pdf';
		$utilities 		= new Utilities();
		$txn 			= str_replace('-', '', $utilities->generate_uuid());
		date_default_timezone_set('Asia/Calcutta');
		$timeStamp 		= str_replace(' ', 'T', date('Y-m-d H:i:s')) . '+05:30';
		$accessKeyhash 	= hash('sha256', $txn . $accessKey . $timeStamp);
		$pdfBase64 		= $utilities->getDocBase64($pdfPath);
		$xml = "<?xml version=\"" . $version . "\" encoding=\"UTF-8\" standalone=\"" . $standAlone . "\"?> <SignDocReq version=\"" . $signVersion . "\" ts=\"" . $timeStamp . "\" txn=\"" . $txn . "\" clientID=\"" . $clientID . "\" keyID=\"" . $keyID . "\" accessKeyhash=\"" . $accessKeyhash . "\"\n" . "sessionId=\"" . $sessionId . "\">\n" . "<Docs>\n" . "<Doc id=\"" . $id . "\" docType=\"" . $docType . "\">\n" . "<DocData>" . $pdfBase64 . "</DocData>\n" . "<Signatures certified=\"" . $certified . "\" appearanceId=\"" . $appearanceId . "\" position=\"" . $position . "\"\n" . "locationText=\"" . $locationText . "\" reasonText=\"" . $reasonText . "\" tick=\"" . $tick . "\" border=\"" . $border . "\"\n></Signatures>\n" . "</Doc>\n" . "</Docs>\n" . "</SignDocReq>";
		$url 			= DIGI_SIGN_URL;
		$responseXML 	= Utilities::apiCall($url, $xml);
		// parsing response xml
		$response 		= simplexml_load_string($responseXML) or die("Error: Cannot create object");
		// XML to json
		$jsonResponse 	= json_encode($response);
		$jsonObj 		= json_decode($jsonResponse);
		// getting required attributes
		$status 		= $jsonObj->{"@attributes"}->status;
		$errorCode 		= $jsonObj->{"@attributes"}->errorCode;
		$errorMessage 	= $jsonObj->{"@attributes"}->errorMessage;
		
		if ($status == 1) {
		    // getting signed base64 pdf
		    $docSignaturesArray = $jsonObj->DocSignatures;
			// creating signed pdf folder
		    if (!file_exists($signgedDir)) {
		        mkdir($signgedDir, 0777, true);
		    }
		    // decoding and saving it as pdf
		    if (file_put_contents($signgedDir."/".$txn.'.pdf', base64_decode($docSignaturesArray->DocSignature))) {
		        $array['status'] 		= SUCCESS;
		        $array['errorMessage'] 	= "";
				$array['ErrorCode'] 	= "";
				$array['data'] 			= $txn.'.pdf';
		    } else {
		        $array['status'] 		= ERROR;
		        $array['errorMessage'] 	= "";
				$array['ErrorCode'] 	= "";
				$array['data'] 			= "";
		    };
		} else {
			$array['status'] 		= ERROR;
			$array['errorMessage'] 	= $errorMessage;
			$array['ErrorCode'] 	= $errorCode;
			$array['data'] 			= "";
		}
	}
}
