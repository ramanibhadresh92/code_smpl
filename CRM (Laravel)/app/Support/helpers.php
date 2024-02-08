<?php
//namespace App\Models;
use App\Models\AdminLog;
use App\Models\LogMaster;
use App\Models\BaseLocationCityMapping;
use App\Models\UserBaseLocationMapping;
use App\Models\GSTStateCodes;
use App\Models\CompanyUserActionLog;
use App\Models\AdminUser;
use App\Models\GroupRightsTransaction;
// use Auth;
/*
Use 	: Function Use to Get List of city belonged to login base location id
Author 	: Axay Shah
Date 	: 29 April,2019.....
*/
function GetBaseLocationCity($baseId = 0){
	if($baseId == 0){
		return BaseLocationCityMapping::where("base_location_id",Auth()->user()->base_location)->where("company_id",Auth()->user()->company_id)->pluck('city_id')->toArray();
	}else{
		return BaseLocationCityMapping::where("base_location_id",$baseId)->pluck('city_id')->toArray();
	}
}


function GetAllBaseLocationCity($baseId = 0) {
	if($baseId == 0) {
		$BaseLocationIDs = GetUserAssignedBaseLocation();
		return BaseLocationCityMapping::whereIn("base_location_id",$BaseLocationIDs)->where("company_id",Auth()->user()->company_id)->pluck('city_id')->toArray();
	} else {
		return BaseLocationCityMapping::where("base_location_id",$baseId)->pluck('city_id')->toArray();
	}
}

function GetUserAssignedBaseLocation($userid = 0){
	return UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck('base_location_id')->toArray();
}

function DBVarConv($var)
{
	if(is_array($var) || $var=='')
		return $var;
	else
		return addslashes(html_entity_decode(ReplaceSpecialChars($var)));
}

/** function to replace special characters
* @param $text string
* @param $replace_char string
* @return $return_text string
* @author Kalpak Prajapati
*/
function ReplaceSpecialChars($text)
{
	$return_text=$text;
	$str_find = array("’", "–", "�?;", "“", "� ��A", "•", "…", "�", "�", "�", "�", "©", "®",  "é", "î","","�");
	$str_repl = array("'", "-", "\"", "\"", "*", "*", "...", "'", "'", "\"", "\"", "&copy;", "&reg;", "&copy;", "&reg;","*",".");
	$return_text = str_replace($str_find, $str_repl, $return_text);
	$return_text = preg_replace('@([��])+@',"'", $return_text);
	$return_text = preg_replace('@([��])+@'," ", $return_text);
	$return_text = preg_replace('@([��])+@',"e", $return_text);
	$return_text = preg_replace('@([��])+@',"E", $return_text);
	$return_text = preg_replace('@([����])+@',"a", $return_text);
	$return_text = preg_replace('@([�����])+@',"A", $return_text);
	$return_text = preg_replace('@([���])+@',"O", $return_text);
	$return_text = preg_replace('@([���])+@',"o", $return_text);
	$return_text = preg_replace('@([��])+@',"U", $return_text);
	$return_text = preg_replace('@([�])+@',"Y", $return_text);
	$return_text = preg_replace('@([�])+@',"y", $return_text);
	$return_text = preg_replace('@([��])+@',"i", $return_text);
	$return_text = preg_replace('@([��])+@',"I", $return_text);
	$return_text = preg_replace('@([�])+@',"c", $return_text);
	$return_text = preg_replace('@([�])+@'," ", $return_text);
	$return_text = preg_replace('@([�])+@'," ", $return_text);
	$return_text = preg_replace('@([�])+@'," ", $return_text);
	$return_text = preg_replace('@([�])+@',"*", $return_text);
	$return_text = preg_replace('@([�])+@'," ", $return_text);
	$return_text = preg_replace('@([�])+@'," ", $return_text);
	$return_text = preg_replace('@([�])+@'," ", $return_text);
	$return_text = preg_replace('@([�])+@'," ", $return_text);
	$return_text = preg_replace('@([“])+@'," ", $return_text);
	$return_text = preg_replace('@([�])+@',"b", $return_text);
	$return_text = preg_replace('/^(\$\'_*%&#\/,.a-zA-Z0-9 !\@\?�����������������������������������������\n\r\f-;\:\"\[\])$/',"",$return_text);
	return $return_text;
}
function passencrypt($str,$lngth=16)
{
	$str=substr($str,0,$lngth);
	$str = str_pad($str,$lngth," ");
	$retstr="";
	for($i=0;$i<$lngth;$i++)
	{
		$sch=substr($str,$i,1);
		$iasc=ord($sch) + 2*$i + 30;
		if($iasc>255) $iasc=$iasc-255;
		$sch=chr($iasc);
		$retstr=$retstr.$sch;
	}
	$retstr=implode("*",unpack('C*',$retstr));

	return DBVarConv($retstr);

}
function passdecrypt($pass)
{
	$retstr="";
	$string = '';
	$data = explode('*',$pass);

	for ($i=0;$i<count($data);$i++)
	{
		if ($data[$i] != '')
		$string = $string.pack('C*',$data[$i]);
	}
	$str = $string;
	$lngth=strlen($str);
	for($i=0;$i<$lngth;$i++)
	{
		$sch=substr($str,$i,1);
		$iasc=ord($sch) - 2*$i - 30;
		if($iasc<=0) $iasc=255+$iasc;
		$sch=chr($iasc);
		$retstr=$retstr.$sch;
	}
	return trim($retstr);
}
function getipaddress()
{
	$ipaddress = "";

	if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "") $ipaddress  = $_SERVER['REMOTE_ADDR'];
	else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != "") $ipaddress  = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if (getenv('HTTP_X_FORWARDED_FOR') && trim(getenv('HTTP_X_FORWARDED_FOR')) != "") $ipaddress  = getenv('HTTP_X_FORWARD_FOR');

	return $ipaddress;
}
function InsertAdminLog($userid, $actionid, $actionvalue="", $remark="")
{
	$x_ip 		= '';
	$remote_ip 	= getipaddress();
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $x_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	if($x_ip!='') $remote_ip = $remote_ip.' : '.$x_ip;
	AdminLog::create(['adminuserid'=> $userid,'actionid'=>$actionid,'actionvalue'=>$actionvalue,'remark'=>$remark,'ip'=>$remote_ip]);
}
function ValidateInputString($String){
	if($String != ''){
		if (preg_match("/^[A-Z0-9._-]/i",$String)) {
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}
function log_action($action_id,$action_value='',$action_value_table='',$system=false,$remark="")
{
	LogMaster::addLog($action_id,$action_value,$action_value_table,$system,$remark);
}

function insert_log($id, $action)
{
		$ip 	= $_SERVER['REMOTE_ADDR'];
		$date 	= date('Y-m-d g:i:s');
		$url 	= basename($_SERVER["REQUEST_URI"]);
		$a 		= parse_url($url);

		\App\Models\WmLogs::insert([
			"user_id" => Auth()->user()->adminuserid,
			"perform_time" => $date,
			"parameter" => $id,
			"ip" => $ip,
			"path" => "test",
			"action_type" => $action
		]);
}


function _FormatNumber($Amount=0){
	$newAmount = $Amount;
	if(!empty($Amount) && $Amount > 0) {
		$newAmount = str_replace(",","",$Amount);
	}
	return trim($newAmount);
}

function _FormatNumberV2($Amount=0,$decimal=2,$CurrencyFormat=false)
{
	$newAmount = $Amount;
	if($Amount != 0) {
		if (!$CurrencyFormat) {
			$newAmount = number_format((float)$Amount,$decimal,'.','');
		} else {
			// $newAmount = number_format((float)$Amount,$decimal);
			$newAmount = _NumberFormat($Amount);
			if ($decimal <= 0) {
				$newAmount = str_replace(".00","",$newAmount);
			}
		}
	}
	return trim($newAmount);
}

function moneyFormatIndia($num)
{  
	$explrestunits = "" ;  
	if(strlen($num)>3){  
		$lastthree = substr($num, strlen($num)-3, strlen($num));  
		$restunits = substr($num, 0, strlen($num)-3); // extracts the last three digits  
		$restunits = (strlen($restunits)%2 == 1)?"0".$restunits:$restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.  
		$expunit = str_split($restunits, 2);  
		for($i=0; $i < sizeof($expunit);  $i++){  
			// creates each of the 2's group and adds a comma to the end  
			if($i==0)  
			{  
				$explrestunits .= (int)$expunit[$i].","; // if is first value , convert into integer  
			}else{  
				$explrestunits .= $expunit[$i].",";  
			}  
		}  
		$thecash = $explrestunits.$lastthree;  
	} else {  
		$thecash = $num;  
	}  
	return str_replace(",.",".",$thecash); // writes the final format where $currency is the currency symbol.  
}

function _FormatedDate($Date="",$ShowTime=true,$Format="M j, Y",$ReturnEmptyDate=true){
	if(!empty($Date) && $Date != '0000-00-00 00:00:00') {
		if ($ShowTime) {
			$Date = date("$Format, g:i a",strtotime($Date));
		} else {
			$Date = date("$Format",strtotime($Date));
		}
	} else {
		if (!$ReturnEmptyDate) return "-";
	}
	return $Date;
}

function _FormatedTime($DateTime="",$ShowDate=false){
	if(!empty($DateTime) && $DateTime != '0000-00-00 00:00:00') {
		if (!$ShowDate) {
			$DateTime = date("g:i a",strtotime($DateTime));
		} else {
			$DateTime = date("M j, Y, g:i a",strtotime($DateTime));
		}
	}
	return $DateTime;
}

//Sending Push Notification Android
function send_push_notification($registatoin_ids, $message)
{
	// Set POST variables
	$url 		= "https://android.googleapis.com/gcm/send";
	$fields 	= array("registration_ids" => array($registatoin_ids),"data" => array("price"=>$message));
	$headers 	= array('Authorization: key=AIzaSyDH36YQFTkd-moztzXAticZNbq9bmF0u54','Content-Type: application/json');
	// Open connection
	$ch 		= curl_init();
	// Set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// Disabling SSL Certificate support temporarly
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	// Execute post
	$result = curl_exec($ch);
	if ($result === FALSE) {
		//die('Curl failed: ' . curl_error($ch));
		return false;
	}
	// Close connection
	curl_close($ch);
	//echo $result;
	$response = json_decode($result,true);
	if (isset($response['results'][0]['error'])) {
		return false;
	} else {
		return true;
	}
}

function prd($string){
	echo "<pre>"; print_r($string);exit;
}

function HTMLVarConv($var)
{
	return stripslashes(htmlspecialchars(html_entity_decode($var)));
}
/**
 * @name getRandomNumber
 * @uses getting random number key
 * @param int $limit
 * @return $randnum
 * @author Kalpak Prajapati
 * @since 2008-08-20
 */
function getRandomNumber($limit=10)
{
	$randnum = strtolower(substr(md5(uniqid(mt_rand())),0,$limit));
	return $randnum;
}


/**
 * Wrapper Function for rand
 *
 * Revision required
 *
 * @todo If not required remove this function.
 * @param int $minval
 * @param int $maxval
 * @return int
 */
function GenerateRandormNumber($minval,$maxval)
{
	return rand($minval,$maxval);
}

/*
Use 	: Aws face reconization
Author 	: Axay shah
Date 	: 31 Jan,2019
*/

function generateCodeId($id) {
	$num = $id;
	$len = strlen($num);
	for($i=$len; $i< 4; ++$i) {
		$num = '0'.$num;
	}
	return $num;
}


/**
 * Function Name : isValidMobile
 * @param $mobile
 * @return boolean
 * @author Sachin Patel
 */
function isValidMobile($mobile)
{
	$valid = preg_match("^(?:(?:\+|0{0,2})91(\s*[\ -]\s*)?|[0]?)?[789]\d{9}|(\d[ -]?){10}\d$", $mobile);
	if ($valid) {
		return true;
	} else {
		return false;
	}
}

/**
 * Function Name : timeago
 * @param $time_elapsed
 * @return
 * @author Axay Shah
 */
function timeAgo($time_elapsed=0){
	$seconds    	= $time_elapsed;
	$minutes    	= round($time_elapsed / 60 );
	$hours      	= round($time_elapsed / 3600);
	$days       	= round($time_elapsed / 86400 );
	$weeks      	= round($time_elapsed / 604800);
	$months     	= round($time_elapsed / 2600640 );
	$years      	= round($time_elapsed / 31207680 );
	// Seconds
	if($seconds <= 60){
		return "now";
	}
	//Minutes
	else if($minutes <=60){
		if($minutes==1){
			return "1 min";
		}
		else{
			return "$minutes min";
		}
	}
	//Hours
	else if($hours <=24){
		if($hours==1){
			return "1 Hr";
		}else{
			return "$hours Hrs";
		}
	}
	//Days
	else if($days <= 7){
		if($days==1){
			return "yesterday";
		}else{
			return "$days days";
		}
	}
	//Weeks
	else if($weeks <= 4.3){
		if($weeks==1){
			return "a week";
		}else{
			return "$weeks weeks";
		}
	}
	//Months
	else if($months <=12){
		if($months==1){
			return "a month";
		}else{
			return "$months months";
		}
	}
	//Years
	else{
		if($years==1){
			return "one year";
		}else{
			return "$years years";
		}
	}
}

/**
 * Function Name : sortByLocationDate
 * @param $p1,$p2
 * @return
 * @author Axay Shah
 */
function SortByLocationDate($p1,$p2) {
	if (empty($p1->created_dt)) return 1;
	if ($p1->nolocation == 1) return 1;
	if (strtotime($p1->created_dt) == strtotime($p1->created_dt)) return 0;
	return (strtotime($p1->created_dt) < strtotime($p1->created_dt))?1:0;
}

/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/*::                                                                         :*/
/*::  This routine calculates the distance between two points (given the     :*/
/*::  latitude/longitude of those points). It is being used to calculate     :*/
/*::  the distance between two location    									 :*/
/*::                                                                         :*/
/*::  Definitions:                                                           :*/
/*::    South latitudes are negative, east longitudes are positive           :*/
/*::                                                                         :*/
/*::  Passed to function:                                                    :*/
/*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
/*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
/*::    unit = the unit you desire for results                               :*/
/*::           where: 'M' is statute miles (default)                         :*/
/*::                  'K' is kilometers                                      :*/
/*::                  'N' is nautical miles                                  :*/
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
function distance($lat1, $lon1, $lat2, $lon2, $unit='K') {

	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	if ($unit == "K") {
		return ($miles * 1.609344);
	} else if ($unit == "M") {
		return (($miles * 1.609344) * 1000);
	} else if ($unit == "N") {
		return ($miles * 0.8684);
	} else {
		return $miles;
	}
}

/**
* Function Name : mime_content_type
* @param $filename
* @return $mime_type
* @since 2019-03-27
* @author Kalpak Prajapati
*/
if(!function_exists('mime_content_type')) {

	function mime_content_type($filename) {

		$mime_types = array(

			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);

		$ext = strtolower(array_pop(explode('.',$filename)));
		if (array_key_exists($ext, $mime_types)) {
			return $mime_types[$ext];
		}
		elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}
		else {
			return 'application/octet-stream';
		}
	}
}

function GetWebsiteBannerData()
{
	$data = array();
	if(file_exists(public_path()."/webdatajson.php")) {
		$myfile = fopen(public_path()."/webdatajson.php", "r") or die("Unable to open file!");
		$fileContent = fread($myfile,filesize(public_path()."/webdatajson.php"));
		fclose($myfile);
		if(!empty($fileContent)) {
			$data = json_decode($fileContent);
		}
	}
	return $data;
}


function encode($id, $key="")
{
	$len			= 10;
	$md5_key		= (!empty($key)?md5($key):md5('!7l@S*3h7_s54P-e543lp'));
	$len_jobid		= 16;
	$sub_md5key1	= substr($md5_key, 0, $len);
	$sub_md5key2	= substr($md5_key, $len);
	return $sub_md5key1.$id.$sub_md5key2;
}

function decode($encodeid,$vauletype='integer')
{
	$strRet = "";
	$len = 10;
	$sub_md5key1 = substr($encodeid, 0, $len);
	$sub_md5key2 = substr($encodeid, -1*(32-$len));
	$strRet = str_replace(array($sub_md5key1, $sub_md5key2), '', $encodeid);
	if($vauletype=='integer')
		$strRet = (int) $strRet;
	else
		$strRet = $strRet;

	return $strRet;
}

function generateNumericOTP($n = 6) {

	// Take a generator string which consist of
	// all numeric digits
	$generator = "1357902468";

	// Iterate for n-times and pick a single character
	// from generator and append it to $result

	// Login for generating a random character from generator
	//     ---generate a random number
	//     ---take modulus of same with length of generator (say i)
	//     ---append the character at place (i) from generator to result

	$result = "";

	for ($i = 1; $i <= $n; $i++) {
		$result .= substr($generator, (rand()%(strlen($generator))), 1);
	}

	// Return result
	return $result;
}


function CurrencyInWord($num = 0)
{
	$ones 	= array(0 =>"ZERO",
					1 => "ONE",
					2 => "TWO",
					3 => "THREE",
					4 => "FOUR",
					5 => "FIVE",
					6 => "SIX",
					7 => "SEVEN",
					8 => "EIGHT",
					9 => "NINE",
					10 => "TEN",
					11 => "ELEVEN",
					12 => "TWELVE",
					13 => "THIRTEEN",
					14 => "FOURTEEN",
					15 => "FIFTEEN",
					16 => "SIXTEEN",
					17 => "SEVENTEEN",
					18 => "EIGHTEEN",
					19 => "NINETEEN",
					"014" => "FOURTEEN");
	$tens 	= array(0 => "ZERO",
					1 => "TEN",
					2 => "TWENTY",
					3 => "THIRTY",
					4 => "FORTY",
					5 => "FIFTY",
					6 => "SIXTY",
					7 => "SEVENTY",
					8 => "EIGHTY",
					9 => "NINETY");
	$hundreds 	= array("HUNDRED",
						"THOUSAND",
						"MILLION",
						"BILLION",
						"TRILLION",
						"QUARDRILLION"); /*limit t quadrillion */
	$num = number_format($num,2,".",",");
	$num_arr = explode(".",$num);
	$wholenum = $num_arr[0];
	$decnum = $num_arr[1];
	$whole_arr = array_reverse(explode(",",$wholenum));
	krsort($whole_arr,1);
	$rettxt = "";
	foreach($whole_arr as $key => $i) {
		while(substr($i,0,1)=="0")
			$i=substr($i,1,5);
			if($i < 20) {
				/* echo "getting:".$i; */
				$rettxt .= $ones[$i];
			} elseif($i < 100) {
				if(substr($i,0,1)!="0")  $rettxt .= $tens[substr($i,0,1)];
				if(substr($i,1,1)!="0") $rettxt .= " ".$ones[substr($i,1,1)];
			} else {
				if(substr($i,0,1)!="0") $rettxt .= $ones[substr($i,0,1)]." ".$hundreds[0];
				if(substr($i,1,1)!="0")$rettxt .= " ".$tens[substr($i,1,1)];
				if(substr($i,2,1)!="0")$rettxt .= " ".$ones[substr($i,2,1)];
			}
			if($key > 0) {
				$rettxt .= " ".$hundreds[$key]." ";
			}
	}
	if($decnum > 0) {
		$rettxt .= " and ";
		if($decnum < 20) {
			$rettxt .= $ones[$decnum];
		} else if($decnum < 100) {
			$rettxt .= $tens[substr($decnum,0,1)];
			$rettxt .= " ".$ones[substr($decnum,1,1)];
		}
	}
	return $rettxt;
}

function numberTowords($num)
{
	$ones 	= array(0 =>"ZERO",
					1 => "ONE",
					2 => "TWO",
					3 => "THREE",
					4 => "FOUR",
					5 => "FIVE",
					6 => "SIX",
					7 => "SEVEN",
					8 => "EIGHT",
					9 => "NINE",
					10 => "TEN",
					11 => "ELEVEN",
					12 => "TWELVE",
					13 => "THIRTEEN",
					14 => "FOURTEEN",
					15 => "FIFTEEN",
					16 => "SIXTEEN",
					17 => "SEVENTEEN",
					18 => "EIGHTEEN",
					19 => "NINETEEN",
					"014" => "FOURTEEN");
	$tens 	= array(0 => "ZERO",
					1 => "TEN",
					2 => "TWENTY",
					3 => "THIRTY",
					4 => "FORTY",
					5 => "FIFTY",
					6 => "SIXTY",
					7 => "SEVENTY",
					8 => "EIGHTY",
					9 => "NINETY");
	$hundreds 	= array("HUNDRED",
						"THOUSAND",
						"MILLION",
						"BILLION",
						"TRILLION",
						"QUARDRILLION"
						); /*limit t quadrillion */
	$num 		= number_format($num,2,".",",");
	$num_arr 	= explode(".",$num);
	$wholenum 	= $num_arr[0];
	$decnum 	= $num_arr[1];
	$whole_arr 	= array_reverse(explode(",",$wholenum));
	krsort($whole_arr,1);
	$rettxt = "";
	foreach($whole_arr as $key => $i) {
		while(substr($i,0,1)=="0")
		$i=substr($i,1,5);
		if($i < 20) {
			$rettxt .= $ones[$i];
		} else if($i < 100) {
			if(substr($i,0,1)!="0")  $rettxt .= $tens[substr($i,0,1)];
			if(substr($i,1,1)!="0") $rettxt .= " ".$ones[substr($i,1,1)];
		} else {
			if(substr($i,0,1)!="0") $rettxt .= $ones[substr($i,0,1)]." ".$hundreds[0];
			if(substr($i,1,1)!="0")$rettxt .= " ".$tens[substr($i,1,1)];
			if(substr($i,2,1)!="0")$rettxt .= " ".$ones[substr($i,2,1)];
		}
		if($key > 0){
			$rettxt .= " ".$hundreds[$key]." ";
		}
	}
	if($decnum > 0) {
		$rettxt .= " and ";
		if($decnum < 20) {
			$decnum = ltrim($decnum, '0');
			$rettxt .= $ones[$decnum];
		} else if($decnum < 100) {
			$rettxt .= $tens[substr($decnum,0,1)];
			$rettxt .= " ".$ones[substr($decnum,1,1)] . " paisa";
		}
	}
	$Inwords = $rettxt." Only";
	return ucwords(strtolower($Inwords));
}

/* Function to use genereate leading zero*/
function LeadingZero($value,$size = 5){
	return str_pad($value, $size, '0', STR_PAD_LEFT);
}

function GetDiffInHoursMinite($STARTDATETIME,$ENDDATETIME){
	$SECONDS_PER_HOUR = 60*60;
	$startdatetime = strtotime($STARTDATETIME);
	// calculate the end timestamp
	$enddatetime = strtotime($ENDDATETIME);
	// calulate the difference in seconds
	$difference = $enddatetime - $startdatetime;
	// hours is the whole number of the division between seconds and SECONDS_PER_HOUR
	$hoursDiff = $difference / $SECONDS_PER_HOUR;
	// and the minutes is the remainder
	$minutesDiffRemainder = $difference % $SECONDS_PER_HOUR;
	// output the result
	$minutesDiffRemainder = (strlen($minutesDiffRemainder) == 1) ? "0".$minutesDiffRemainder : $minutesDiffRemainder;
	return $hoursDiff.":".$minutesDiffRemainder.":00";
}


/*
Use 	: Check For GST validation
Author 	:  Axay Shah
Date 	: 27 Nov,2020
*/
Function CheckValidGST($GST_STATE_CODE_ID=0,$GST_NO=""){
	$STATE_CODE  	= GSTStateCodes::where("id",$GST_STATE_CODE_ID)->value("display_state_code");
	$STATE_CODE 	= (strlen($STATE_CODE) == 1) ? "0".$STATE_CODE :  $STATE_CODE;
	$FIRST_TW0_NO 	= mb_substr($GST_NO, 0, 2);
	if($FIRST_TW0_NO != $STATE_CODE){
		return false;
	}
	return true;
}
/*
Use 	: change vehicle format
Author 	: Axay Shah
Date 	: 03 Feb,2021
*/
// function vehicleFormat($vehicleNo=""){
// 	$new_str = str_replace(' ', '', $vehicleNo);
// 	$Four 		= substr($new_str, -4);
// 	$First 		= substr($new_str,0,2);
// 	$Second 	= substr($new_str,2,2);
// 	$Third 		= substr($new_str,4,2);
// 	$Lenght 	= strlen($new_str);
// 	if($Lenght > 10){
// 		$Third 	= substr($new_str,4,1);
// 		$Third .= "0".$Third;
// 	}
// 	return  $First." ".$Second." ".$Third." ".$Four;
// }

/*
Use 	:  GST GROSS AND NET AMOUNT CALCULATION
Author 	:  Axay Shah
Date 	:  05 March 2021
*/
function GetGSTCalculation($QTY=0,$RATE=0,$SGST=0,$CGST=0,$IGST=0,$SAME_STATE=true){
	$GST_ARR 			= array();
	$SUM_GST_PERCENT    = 0;
	$CGST_AMT           = 0;
	$SGST_AMT           = 0;
	$IGST_AMT           = 0;
	$TOTAL_GST_AMT      = 0;
	if($SAME_STATE) {
		$CGST_AMT  = ($CGST > 0 && $QTY > 0 && $RATE > 0) ? (($QTY * $RATE) / 100) * $CGST:0;
		$SGST_AMT  = ($SGST > 0 && $QTY > 0 && $RATE > 0) ? (($QTY * $RATE) / 100) * $SGST:0;
	}else{
		$IGST_AMT  = ($IGST > 0 && $QTY > 0 && $RATE > 0) ? (($QTY * $RATE) / 100) * $IGST:0;
	}
	$TOTAL_GST_AMT    			= ($SAME_STATE) ? $CGST_AMT + $SGST_AMT : $IGST_AMT;
	$SUM_GST_PERCENT  			= ($SAME_STATE) ? $CGST + $SGST : $IGST;
	$TOTAL_GR_AMT   			= ($RATE > 0 && $QTY > 0) ? _FormatNumberV2(($RATE * $QTY)) : 0;
	$TOTAL_NET_AMT  			= ($RATE > 0 && $QTY > 0) ? _FormatNumberV2(($RATE * $QTY) + $TOTAL_GST_AMT) : 0;

	$GST_ARR['CGST_RATE']       = _FormatNumberV2($CGST);
	$GST_ARR['SGST_RATE']       = _FormatNumberV2($SGST);
	$GST_ARR['IGST_RATE']       = _FormatNumberV2($IGST);
	$GST_ARR['TOTAL_GR_AMT']    = _FormatNumberV2($TOTAL_GR_AMT);
	$GST_ARR['TOTAL_NET_AMT']   = _FormatNumberV2($TOTAL_NET_AMT);
	$GST_ARR['CGST_AMT']        = _FormatNumberV2($CGST_AMT);
	$GST_ARR['SGST_AMT']       	= _FormatNumberV2($SGST_AMT);
	$GST_ARR['IGST_AMT']        = _FormatNumberV2($IGST_AMT);
	$GST_ARR['TOTAL_GST_AMT']   = _FormatNumberV2($TOTAL_GST_AMT);
	$GST_ARR['SUM_GST_PERCENT'] = _FormatNumberV2($SUM_GST_PERCENT);
	return $GST_ARR;
}

/*
Use 	: Generate QR Code
Author 	: Axay Shah
Date 	: 31 March 2021
*/
function GetQRCode($string="",$invoiceID=""){
	include(app_path()."/Classes/phpqrcode/qrlib.php");
	// $data = QRcode::png($string) ;
	$base_path = public_path()."/phpqrcode/";

	if(!is_dir($base_path)) {
		mkdir($base_path,0777,true);
	}
	$path  	= $base_path.$invoiceID.".png";
	$data 	= QRcode::png ($string , $path, "L", 10, 10) ;
	$path 	= "phpqrcode/".$invoiceID.".png";
	return $path;
}

	/*
Use : Word Wrap Address
Author :  Axay Shah
Date : 26 April,2021
*/
function WrodWrapString($string="",$length=100){
	$string 	= preg_replace( "/\r|\n/", "", $string );
	$newLine 	= wordwrap(trim($string), $length);
	$new 		= explode(PHP_EOL, $newLine);
	return $new;

}
/*
Use : List Year Dropdown
Author :  Hasmukhi Patel
Date : 29 July,2021
*/
function YearList($year=""){
	$year 	= (!empty($year) ? $year : date('Y'));
	$start  = date('Y', strtotime($year. ' -5 years'));
	$end    = date('Y', strtotime($year. ' +5 years'));
	$data   = array();
	for($i=$start;$i<=$end;$i++){
		$year_data 			= $i;
		$data[]["year"] 	= (string) $year_data;
	}
	return $data;
}
/*
Use 	: Convert data in array
Author 	: Axay Shah
Date 	: 23 Auguest,2021
*/
function ConvertInArray($data = ""){
	$array = array();
	if(!empty($data)){
		if(!is_array($data)){
			$array = explode(",",$data);
		}else{
			$array = $data;
		}
	}
	return $array;
}

function _NumberFormat($number)
{
	$fmt = new NumberFormatter($locale = 'en_IN', NumberFormatter::CURRENCY);
	$FormattedNumber = $fmt->format($number);
	return trim(preg_replace('/[^0-9-.,]+/','',$FormattedNumber));
}
/*
Use : Get Time in human readble format
Author : Axay Shah
Date : 26 November,2021
*/
function GetTimeAgo($time)
{
    $time_difference = time() - $time;
	if( $time_difference < 1 ) { return 'less than 1 second ago'; }
    $condition = array( 
		12 * 30 * 24 * 60 * 60  =>  'year',
        30 * 24 * 60 * 60       =>  'month',
        24 * 60 * 60            =>  'day',
        60 * 60                 =>  'hour',
        60                      =>  'minute',
        1                       =>  'second'
    );
	foreach( $condition as $secs => $str )
    {
        $d = $time_difference / $secs;
	    if( $d >= 1 )
        {
            $t = round( $d );
            return $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
        }
    }
}

/*
Use 	: Store file from url to specific location
Author 	: Axay Shah
Date 	: 27 December 2021
*/
function StoreFileInSpecificPath($url,$save_file_loc,$file_name="")
{
	$ch 			= curl_init($url);
	$file_name 		= basename($url);
	$save_file_loc 	= $save_file_loc.$file_name;
	$fp 			= fopen($save_file_loc, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	return $file_name;
}

function downloadFile($urlFile)
{
    $file_name  =   basename($urlFile);
    //save the file by using base name
    $fn         =   file_put_contents($file_name,file_get_contents($urlFile));
    header("Expires: 0");
    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Content-type: application/file");
    header('Content-length: '.filesize($file_name));
    header('Content-disposition: attachment; filename="'.basename($file_name).'"');
    readfile($file_name);
}

/*
Use 	: Generate QR Code
Author 	: Kalpak Prajapati
Date 	: 08 Feb 2022
*/
function GetQRCodeV2($QRData="",$File_Path="",$ImgType="L",$SizeX=10,$SizeY=10)
{
	include(app_path()."/Classes/phpqrcode/qrlib.php");
	QRcode::png ($QRData,$File_Path,$ImgType,$SizeX,$SizeY);
}

function getIndianCurrency(float $number)
{
	$decimal = round($number - ($no = floor($number)), 2) * 100;
	$hundred = null;
	$digits_length = strlen($no);
	$i = 0;
	$str = array();
	$words = array(0 => '', 1 => 'one', 2 => 'two',
		3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
		7 => 'seven', 8 => 'eight', 9 => 'nine',
		10 => 'ten', 11 => 'eleven', 12 => 'twelve',
		13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
		16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
		19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
		40 => 'forty', 50 => 'fifty', 60 => 'sixty',
		70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
	$digits = array('', 'hundred','thousand','lakh', 'crore');
	while( $i < $digits_length ) {
		$divider = ($i == 2) ? 10 : 100;
		$number = floor($no % $divider);
		$no = floor($no / $divider);
		$i += $divider == 10 ? 1 : 2;
		if ($number) {
			$plural = (($counter = count($str)) && $number > 9) ? '' : null;
			$hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
			$str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
		} else $str[] = null;
	}
	$Rupees = implode('', array_reverse($str));
	$andVar = (!empty($Rupees)) ? " And " : " ";
	$paise 	= ($decimal > 0) ? $andVar. ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
	$final 	= ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
	return ucwords(strtolower($final))." Only";
}

/*
Use 	: Convert PDF version in 1.4
Author 	: Axay Shah
Date 	: 24 May 2022
*/
function ConvertPDFVersion($newFile,$currentFile,$pdfVersion="1.4"){
	$gsCmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=$pdfVersion -dNOPAUSE -dBATCH -sOutputFile=$newFile $currentFile";
	exec($gsCmd);
}

/*
Use 	: random_color
Date 	: 19 July 2022
Author 	: Kalpak Prajapati
*/
function random_color()
{
	$PART_1 = str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
	$PART_2 = str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
	$PART_3 = str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
	return $PART_1 . $PART_2 . $PART_3;
}

/*
Use 	: Log Entry in LR All Modules -  
Date 	: 11 Jan 2023
Author 	: Hardyesh Gupta
*/
// function LR_Modules_Log_CompanyUserActionLog($request="",$recordid=0)
// {		
// 	$useragent 			= (isset($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : ''; 			
// 	if(!empty($request))
// 	{		
// 		$routename 	 	= @request()->route()->getname();
// 		if(empty($routename)){
// 			$routename 	= (isset($_SERVER["REQUEST_URI"])) ? (basename($_SERVER["REQUEST_URI"])) : "";
// 		}
// 		$ipaddress 		= (!empty(request()->ip())) ? request()->ip() : '';
// 		if(isset($request->device_type)){
// 			$requestfrom		= $request->device_type;
// 		}else{
// 			$requestfrom 		= (stripos($useragent, "mobile")) ? 1 : 0;	
// 		}	
// 		$routeprefix			= (!empty(request()->route()->getPrefix())) ? request()->route()->getPrefix() : '';
// 		$routepathinfo			= pathinfo($routeprefix);
// 		$routegroup_prefixname 	= $routepathinfo['basename'];
// 	}else{						
// 		$routename 				= (isset($_SERVER["REQUEST_URI"])) ? (basename($_SERVER["REQUEST_URI"])) : '';	
// 		$ipaddress 				= (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
// 		$requestfrom 			= (stripos($useragent, "mobile")) ? 1 : 0;		
// 		$routepathinfo 	 		= (isset($_SERVER["REQUEST_URI"])) ? pathinfo($_SERVER["REQUEST_URI"]) : '';	
// 		$routegroup_dirpath		= $routepathinfo['dirname'];
// 		$routepathinfo2 	 	= pathinfo($routegroup_dirpath);
// 		$routegroup_prefixname	= $routepathinfo2['basename'];	
// 	}	
// 	$createdby_userid  			= (\Auth::check()) ? Auth()->user()->adminuserid :  0; 
// 	$adminuser_data 			= AdminUser::where('adminuserid',$createdby_userid)->first();
// 	if(!empty($adminuser_data)){
// 		$createdby_username 	= $adminuser_data->firstname. " ".$adminuser_data->lastname;		
// 	}	
// 	$user_action_master_data 	= \DB::table('company_user_action_master')->where('route_name', '=', $routename)->where('status',1)->first();
// 	if(!empty($user_action_master_data)){ 						
// 		$route_message	= $user_action_master_data->remark;
// 		$recordid       = (!empty($recordid)) ?  $recordid: 0; 
// 		if($routegroup_prefixname == ROUTEGROUP_USER){
// 			$route_resultdata 	= AdminUser::where('adminuserid',$recordid)->first();					
// 			$route_message      = str_replace("[UNAME]", $route_resultdata->username,$route_message );
// 		}
// 		$route_message	= str_replace("[USERNAME]", $createdby_username, $route_message);            
// 		$route_actionid	= $user_action_master_data->action_id;             						
// 		CompanyUserActionLog::insert(
// 		[
// 			"message"       => $route_message,
// 			"action_id"     => $route_actionid,
// 			"ip"            => $ipaddress,
// 			"user_agent"    => $useragent,
// 			"request_from"  => $requestfrom, 
// 			"record_id"     => $recordid, 
// 			"created_at"    => date("Y-m-d H:i:s"),
// 			"created_by"    => $createdby_userid    
// 		]);
// 	}
// }

function LR_Modules_Log_CompanyUserActionLog($request="",$recordid=0)
{		
	$useragent 			= (isset($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : ''; 			
	if(!empty($request))
	{		
		$routename 	 	= @request()->route()->getname();
		if(empty($routename)){
			$routename 	= (isset($_SERVER["REQUEST_URI"])) ? (basename($_SERVER["REQUEST_URI"])) : "";
		}
		$ipaddress 		= (!empty(request()->ip())) ? request()->ip() : '';
		if(isset($request->device_type)){
			$requestfrom		= $request->device_type;
		}else{
			$requestfrom 		= (stripos($useragent, "mobile")) ? 1 : 0;	
		}	
		$routeprefix			= (!empty(request()->route()->getPrefix())) ? request()->route()->getPrefix() : '';
		$routepathinfo			= pathinfo($routeprefix);
		$routegroup_prefixname 	= $routepathinfo['basename'];
	}else{						
		$routename 				= (isset($_SERVER["REQUEST_URI"])) ? (basename($_SERVER["REQUEST_URI"])) : '';	
		$ipaddress 				= (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
		$requestfrom 			= (stripos($useragent, "mobile")) ? 1 : 0;		
		$routepathinfo 	 		= (isset($_SERVER["REQUEST_URI"])) ? pathinfo($_SERVER["REQUEST_URI"]) : '';	
		$routegroup_dirpath		= $routepathinfo['dirname'];
		$routepathinfo2 	 	= pathinfo($routegroup_dirpath);
		$routegroup_prefixname	= $routepathinfo2['basename'];	
	}	
	$createdby_userid  			= (\Auth::check()) ? Auth()->user()->adminuserid :  0; 
	$adminuser_data 			= AdminUser::where('adminuserid',$createdby_userid)->first();
	if(!empty($adminuser_data)){
		$createdby_username 	= $adminuser_data->firstname. " ".$adminuser_data->lastname;		
	}	
	$user_action_master_data 	= \DB::table('company_user_action_master')->where('route_name', '=', $routename)->where('status',1)->first();
	if(!empty($user_action_master_data)){ 						
		$route_message	= $user_action_master_data->remark;
		$recordid       = (!empty($recordid)) ?  $recordid: 0; 
		if($routegroup_prefixname == ROUTEGROUP_USER){
			if(is_array($recordid)){
				$route_resultdata 	= AdminUser::whereIn('adminuserid',$recordid)->first();		
			}else{
				$route_resultdata 	= AdminUser::where('adminuserid',$recordid)->first();		
			}
			$route_message      = str_replace("[UNAME]", $route_resultdata->username,$route_message );
		}
		$route_message	= str_replace("[USERNAME]", $createdby_username, $route_message);            
		$route_actionid	= $user_action_master_data->action_id;    
		$recordIDS 		= array();          	
		if(!is_array($recordid)){
			$recordIDS = explode(",",$recordid);
		}else{
			$recordIDS = $recordid;
		}	
		foreach($recordIDS as $value){
			CompanyUserActionLog::insert(
			[
				"message"       => $route_message,
				"action_id"     => $route_actionid,
				"ip"            => $ipaddress,
				"user_agent"    => $useragent,
				"request_from"  => $requestfrom, 
				"record_id"     => $value, 
				"created_at"    => date("Y-m-d H:i:s"),
				"created_by"    => $createdby_userid    
			]);
		}				
	}
}
/*
Use 	: Check For GST validation with PAN Number
Author 	:  Hardyesh Gupta
Date 	: 28 Feb,2023
*/
function CheckValidGSTwithPAN($PAN_NUMBER="",$GST_NO=""){
	$GST_PAN_VALUE 		= mb_substr($GST_NO, 2, 10);	
	if($GST_PAN_VALUE 	!= $PAN_NUMBER){
		return false;
	}	
	return true;
}
/*
Use 	: Check Role has rights
Author 	: Axay Shah
Date 	: 31 May 2023
*/
function checkRoleHasRights($group_id,$trn_id){
	$count = GroupRightsTransaction::where("group_id",$group_id)->where("trn_id",$trn_id)->count();
	return $count;
}