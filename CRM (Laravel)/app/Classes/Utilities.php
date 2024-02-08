<?php

namespace App\Classes;

use App\Models\DigitalSignatureApiLog;

class Utilities
{
	public static function generate_uuid()
	{
		return substr(sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0C2f) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0x2Aff), mt_rand(0, 0xffD3), mt_rand(0, 0xff4B)), 10);
	}

	public static function getDocBase64($path)
	{
		$b64Doc = base64_encode(file_get_contents($path));
		return $b64Doc;
	}

	public static function apiCall($url, $data)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS,  urlencode($data));
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	/**
	* @uses Digital Signature
	* @param string $pdfPath
	* @param string $fullPath
	* @param string $signedFileName
	* @return
	* @author Kalpak Prajapati
	* @since 2019-03-12
	*/
	public static function DigitalSignature($pdfPath="",$fullPath="",$signedFileName="")
	{
		$clientID       = DIGI_SIGN_CLIENT_ID;
		$keyID          = DIGI_SIGN_KEY_ID;
		$accessKey      = DIGI_SIGN_ACCESS_KEY;
		$certified      = DIGI_SIGN_CERTIFIED;
		$appearanceId   = DIGI_SIGN_APPEARANCE_ID;
		$position       = DIGI_SIGN_POSITION;
		$locationText   = DIGI_SIGN_LOCATION_TXT;
		$reasonText     = "";
		$tick           = DIGI_SIGN_TICK;
		$border         = DIGI_SIGN_BORDER;
		$sessionId      = DIGI_SIGN_SESSION_ID;
		$id             = DIGI_SIGN_ID;
		$docType        = DIGI_SIGN_DOC_TYPE;
		$version        = DIGI_VERSION;
		$standAlone     = DIGI_SIGN_STANDALONE;
		$signVersion    = DIGI_SIGN_VERSION;
		$txn            = str_replace('-', '', self::generate_uuid());
		$timeStamp      = str_replace(' ', 'T', date('Y-m-d H:i:s')) . '+05:30';
		$accessKeyhash  = hash('sha256', $txn . $accessKey . $timeStamp);
		$pdfBase64      = self::getDocBase64($pdfPath);
		$xml            = "<?xml version=\"" . $version . "\" encoding=\"UTF-8\" standalone=\"" . $standAlone . "\"?> <SignDocReq version=\"" . $signVersion . "\" ts=\"" . $timeStamp . "\" txn=\"" . $txn . "\" clientID=\"" . $clientID . "\" keyID=\"" . $keyID . "\" accessKeyhash=\"" . $accessKeyhash . "\"\n" . "sessionId=\"" . $sessionId . "\">\n" . "<Docs>\n" . "<Doc id=\"" . $id . "\" docType=\"" . $docType . "\">\n" . "<DocData>" . $pdfBase64 . "</DocData>\n" . "<Signatures certified=\"" . $certified . "\" appearanceId=\"" . $appearanceId . "\" position=\"" . $position . "\"\n" . "locationText=\"" . $locationText . "\" reasonText=\"" . $reasonText . "\" tick=\"" . $tick . "\" border=\"" . $border . "\"\n></Signatures>\n" . "</Doc>\n" . "</Docs>\n" . "</SignDocReq>";
		$url            = DIGI_SIGN_URL;
		$responseXML    = self::apiCall($url, $xml);
		// parsing response xml
		$response       = simplexml_load_string($responseXML) or die("Error: Cannot create object");
		// XML to json
		$jsonResponse   = json_encode($response);
		$jsonObj        = json_decode($jsonResponse);
		// getting required attributes
		$status         = $jsonObj->{"@attributes"}->status;
		$errorCode      = $jsonObj->{"@attributes"}->errorCode;
		$errorMessage   = $jsonObj->{"@attributes"}->errorMessage;
		$signgedDir     = $fullPath;
		DigitalSignatureApiLog::addLog($pdfPath,$xml,$jsonResponse);
		if ($status == 1) {
			// getting signed base64 pdf
			$docSignaturesArray = $jsonObj->DocSignatures;
			// creating signed pdf folder
			if (!file_exists($signgedDir)) {
				mkdir($signgedDir, 0777, true);
			}
			// decoding and saving it as pdf
			if (file_put_contents($signgedDir."/".$signedFileName, base64_decode($docSignaturesArray->DocSignature))) {
				return true;
			} else {
				return false;
			};
		} else {
			return false;
		}
	}
}
?>