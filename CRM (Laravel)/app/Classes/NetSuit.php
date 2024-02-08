<?php

namespace App\Classes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NetSuit {

	/** variable declaration of class variable */
	var $ACCOUNT_ID             = "";
	var $TOKEN_ID               = "";
	var $TOKEN_SECRET           = "";
	var $CONSUMER_KEY           = "";
	var $CONSUMER_SECRET 		= "";
	var $URL                    = "";
	var $OAUTH_NONCE            = "";
	var $OAUTH_TIMESTAMP        = "";
	var $OAUTH_SIGNATURE_METHOD = "";
	var $OAUTH_VERSION          = "";
	var $API_RESPONSE 			= "";

	function __construct() {
	}

	/*
	Use     : Send curl request to net suit
	Author  : Axay Shah
	Date    : 17 March 2021
	*/
	public function SendCurlNetSuitRequest($REQUEST,$SCRIPT_ID,$DEPLOY_ID=1)
	{
		$oauth_nonce 				= md5(mt_rand());
		$oauth_timestamp 			= time();
		$oauth_signature_method 	= 'HMAC-SHA256';
		$oauth_version 				= "1.0";
		$REQUEST 					= !empty($REQUEST) ? json_encode($REQUEST) : "";
		$base_string = "POST&" . urlencode(NETSUITE_URL) . "&" .
						urlencode(	"deploy=" . $DEPLOY_ID
									. "&oauth_consumer_key=" . NETSUITE_CONSUMER_KEY
									. "&oauth_nonce=" . $oauth_nonce
									. "&oauth_signature_method=" . $oauth_signature_method
									. "&oauth_timestamp=" . $oauth_timestamp
									. "&oauth_token=" . NETSUITE_TOKEN_ID
									. "&oauth_version=" . $oauth_version
									. "&realm=" . NETSUITE_ACCOUNT
									. "&script=" . $SCRIPT_ID);
		$sig_string 	= urlencode(NETSUITE_CONSUMER_SECRET) . '&' . urlencode(NETSUITE_TOKEN_SECRET);
		$signature 		= base64_encode(hash_hmac("SHA256", $base_string, $sig_string, true));
		$auth_header 	= "OAuth "
						. 'oauth_signature="' . rawurlencode($signature) . '", '
						. 'oauth_version="' . rawurlencode($oauth_version) . '", '
						. 'oauth_nonce="' . rawurlencode($oauth_nonce) . '", '
						. 'oauth_signature_method="' . rawurlencode($oauth_signature_method) . '", '
						. 'oauth_consumer_key="' . rawurlencode(NETSUITE_CONSUMER_KEY) . '", '
						. 'oauth_token="' . rawurlencode(NETSUITE_TOKEN_ID) . '", '
						. 'oauth_timestamp="' . rawurlencode($oauth_timestamp) . '", '
						. 'realm="' . rawurlencode(NETSUITE_ACCOUNT) .'"';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, NETSUITE_URL . '?&script=' . $SCRIPT_ID . '&deploy=' . $DEPLOY_ID . '&realm=' . NETSUITE_ACCOUNT);
		curl_setopt($ch, CURLOPT_POST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $REQUEST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $auth_header,'Content-Type: application/json','Content-Length: ' . strlen($REQUEST)]);
		$output = curl_exec($ch);
		curl_error($ch);
		curl_close($ch);
		return $output;
	}

	/*
	Use     : Set Header Parameter
	Author  : Axay Shah
	Date    : 18 March 2021
	*/
	public function SetHeader($SIGNATURE="")
	{
		$HEADER 	= "OAuth "
		. 'oauth_signature="' . rawurlencode($SIGNATURE) . '", '
		. 'oauth_version="' . rawurlencode($this->OAUTH_VERSION) . '", '
		. 'oauth_nonce="' . rawurlencode($this->OAUTH_NONCE) . '", '
		. 'oauth_signature_method="' . rawurlencode($this->OAUTH_SIGNATURE_METHOD) . '", '
		. 'oauth_consumer_key="' . rawurlencode($this->CONSUMER_KEY) . '", '
		. 'oauth_token="' . rawurlencode($this->TOKEN_SECRET) . '", '
		. 'oauth_timestamp="' . rawurlencode($this->OAUTH_TIMESTAMP) . '", '
		. 'realm="' . rawurlencode($this->ACCOUNT_ID) .'"';
		return $HEADER;
	}

	/*
	Use     : Get Data From Netsuit
	Author  : Kalpak Prajapati
	Date    : 28 June 2022
	*/
	public function GetDataFromNetSuit($REQUEST,$SCRIPT_ID,$DEPLOY_ID=1)
	{

		$oauth_nonce 				= md5(mt_rand());
		$oauth_timestamp 			= time();
		$oauth_signature_method 	= 'HMAC-SHA256';
		$oauth_version 				= "1.0";
		$REQUEST 					= !empty($REQUEST) ? json_encode($REQUEST) : "";
		$base_string = "POST&" . urlencode(NETSUITE_URL) . "&" .
						urlencode(	"deploy=" . $DEPLOY_ID
									. "&oauth_consumer_key=" . $this->CONSUMER_KEY
									. "&oauth_nonce=" . $oauth_nonce
									. "&oauth_signature_method=" . $oauth_signature_method
									. "&oauth_timestamp=" . $oauth_timestamp
									. "&oauth_token=" . $this->TOKEN_ID
									. "&oauth_version=" . $oauth_version
									. "&realm=" . NETSUITE_ACCOUNT
									. "&script=" . $SCRIPT_ID);
		$sig_string 	= urlencode($this->CONSUMER_SECRET) . '&' . urlencode($this->TOKEN_SECRET);
		$signature 		= base64_encode(hash_hmac("SHA256", $base_string, $sig_string, true));
		$auth_header 	= "OAuth "
						. 'oauth_signature="' . rawurlencode($signature) . '", '
						. 'oauth_version="' . rawurlencode($oauth_version) . '", '
						. 'oauth_nonce="' . rawurlencode($oauth_nonce) . '", '
						. 'oauth_signature_method="' . rawurlencode($oauth_signature_method) . '", '
						. 'oauth_consumer_key="' . rawurlencode($this->CONSUMER_KEY) . '", '
						. 'oauth_token="' . rawurlencode($this->TOKEN_ID) . '", '
						. 'oauth_timestamp="' . rawurlencode($oauth_timestamp) . '", '
						. 'realm="' . rawurlencode(NETSUITE_ACCOUNT) .'"';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, NETSUITE_URL . '?&script=' . $SCRIPT_ID . '&deploy=' . $DEPLOY_ID . '&realm=' . NETSUITE_ACCOUNT);
		curl_setopt($ch, CURLOPT_POST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $REQUEST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $auth_header,'Content-Type: application/json','Content-Length: ' . strlen($REQUEST)]);
		$output = curl_exec($ch);
		curl_error($ch);
		curl_close($ch);
		$this->API_RESPONSE = $output;
		return $output;
	}
}