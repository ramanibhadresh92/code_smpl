<?php
namespace App\Classes;
use Illuminate\Http\Request;
use App\Models\SetuApiLogMaster;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Facades\LiveServices;
use DB;
Class SetuAPI {

	public $url        = "";
	public $client_id  = "";
	public $api_key    = "";
	function __construct() {
		$this->url 			= "https://apisetu.gov.in";
		$this->client_id 	= "in.letsrecycle";
		$this->api_key 		= "9kxFBH4Ux1oRjtX3dXFKiP5yE5i5mIVh";
	}

	private function SaveAPILog($document_no, $request,$response)
	{
		$datetime = date("Y-m-d H:i:s");
		DB::table("setu_api_log_master")->insert(array("document_no"=>$document_no,"request"=>$request,"response"=>$response,"created_at"=>$datetime,"updated_at"=>$datetime));
	}

	/**
	 * Use      : This trait use for check GST number is valid or not
	 * Author   : Kalpak Prajapati
	 * Date     : 19 Oct,2023
	 */
	private function GetTaxPayerGSTInfo($GST_NO)
	{
		if(!empty($GST_NO))
		{
			$curl 			= curl_init();
			curl_setopt_array($curl, [	CURLOPT_URL => $this->url."/gstn/v2/taxpayers/".$GST_NO,
										CURLOPT_RETURNTRANSFER => true,
										CURLOPT_ENCODING => "",
										CURLOPT_MAXREDIRS => 10,
										CURLOPT_TIMEOUT => 300,
										CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
										CURLOPT_CUSTOMREQUEST => "GET",
										CURLOPT_HTTPHEADER => [
										'X-APISETU-CLIENTID: '.$this->client_id,
										'X-APISETU-APIKEY: '.$this->api_key]]);
			$response 	= curl_exec($curl);
			$err 		= curl_error($curl);
			curl_close($curl);
			$this->SaveAPILog($GST_NO,$this->url."/gstn/v2/taxpayers/".$GST_NO,$response);
			if ($err) {
				return json_decode($err);
			} else {
			  return json_decode($response);
			}
		} else {
			return false;
		}
	}

	/**
	 * Use      : This trait use for check CIN number is valid or not
	 * Author   : Kalpak Prajapati
	 * Date     : 19 Oct,2023
	 */
	private function GetCompanyInformationByCIN($CINNO)
	{
		if(!empty($CINNO))
		{
			$curl 			= curl_init();
			curl_setopt_array($curl, [	CURLOPT_URL => $this->url."/mca/v1/companies/".$CINNO,
										CURLOPT_RETURNTRANSFER => true,
										CURLOPT_ENCODING => "",
										CURLOPT_MAXREDIRS => 10,
										CURLOPT_TIMEOUT => 300,
										CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
										CURLOPT_CUSTOMREQUEST => "GET",
										CURLOPT_HTTPHEADER => [
										'X-APISETU-CLIENTID: '.$this->client_id,
										'X-APISETU-APIKEY: '.$this->api_key]]);
			$response 	= curl_exec($curl);
			$err 		= curl_error($curl);
			curl_close($curl);
			$this->SaveAPILog($CINNO,$this->url."/mca/v1/companies/".$CINNO,$response);
			if ($err) {
				return json_decode($err);
			} else {
			  return json_decode($response);
			}
		} else {
			return false;
		}
	}

	/**
	 * Use      : This trait use for get company Director details
	 * Author   : Kalpak Prajapati
	 * Date     : 19 Oct,2023
	 */
	private function GetCompanyDirectorDetailsByCIN($CINNO)
	{
		if(!empty($CINNO))
		{
			$curl 			= curl_init();
			curl_setopt_array($curl, [	CURLOPT_URL => $this->url."/mca-directors/v1/companies/".$CINNO,
										CURLOPT_RETURNTRANSFER => true,
										CURLOPT_ENCODING => "",
										CURLOPT_MAXREDIRS => 10,
										CURLOPT_TIMEOUT => 300,
										CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
										CURLOPT_CUSTOMREQUEST => "GET",
										CURLOPT_HTTPHEADER => [
										'X-APISETU-CLIENTID: '.$this->client_id,
										'X-APISETU-APIKEY: '.$this->api_key]]);
			$response 	= curl_exec($curl);
			$err 		= curl_error($curl);
			curl_close($curl);
			$this->SaveAPILog($CINNO,$this->url."/mca-directors/v1/companies/".$CINNO,$response);
			if ($err) {
				return json_decode($err);
			} else {
			  return json_decode($response);
			}
		} else {
			return false;
		}
	}


	/**
	 * Use      : generate token for client id and secret id
	 * Author   : Kalpak Prajapati
	 * Date     : 06 Nov,2023
	 */
	public function GenerateTokenSetuApi($CLIENT_ID,$SECRET_KEY){
		$token 		= "";
		$msg 		= "";
		$statusCode = "";
		$response 	= array(); 
		if(!empty($CLIENT_ID) && $SECRET_KEY)
		{
			$GET_CLIENT = DB::table("setu_api_client_master")->where("client_id",$CLIENT_ID)->where("client_secret",$SECRET_KEY)->first();
			IF($GET_CLIENT)
			{
				$token 			= Str::random(30);
				$token_expire 	= date('Y-m-d H:i:s', strtotime('+2 minutes'));
				$created_at 	= date('Y-m-d H:i:s');
				$project_id 	= $GET_CLIENT->id;
				DB::table("setu_api_client_token_log")->insert(array("project_id"=>$project_id,"token"=>$token,"token_expire"=>$token_expire,"created_at"=>$created_at));
				$token 		= $token;
				$statusCode = SUCCESS;
				$msg 		= "Token Generated Successfully.";
			} else {
				$token 		= "";
				$statusCode = ERROR;
				$msg 		= "Invalid client id and secret id";
			}
		} else {
			$token 		= "";
			$statusCode = VALIDATION_ERROR;
			$msg 		= "Client ID and secret key required.";
		}
		$response['message'] 		= $msg;
		$response['status_code'] 	= $statusCode;
		$response['token'] 			= $token;
		return $response;
	}

	/**
	 * Use      : Verify API Token used to et details
	 * Author   : Kalpak Prajapati
	 * Date     : 03 Dec,2023
	 */
	private function VerifyAPIToken($request)
	{
		$Authorization 	= $request->header('Authorization');
		$token 			= trim(str_replace('Bearer','',$Authorization));
		$check_token 	= DB::table("setu_api_client_token_log")->where("token",$token)->where("token_expire",">=",date("Y-m-d H:i:s"))->where("token_hit",0)->first();
		if($check_token) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Use      : Verify GST Details
	 * Author   : Kalpak Prajapati
	 * Date     : 03 Dec,2023
	 */
	public function VerifyGSTDetails($request)
	{
		$msg 			= "";
		$statusCode 	= "200";
		$result 		= array();
		$gst_no 		= (isset($request->gst_no) && !empty($request->gst_no)) ? $request->gst_no : "";
		if ($this->VerifyAPIToken($request)) {
			if (!empty($gst_no)) {
				$result = $this->GetTaxPayerGSTInfo($gst_no);
				if(isset($result->error) && !empty($result->error)) {
					$statusCode = ERROR;
					$msg 		= "Error while fetching company information, please try again after sometime.";
				}
			} else {
				$statusCode = ERROR;
				$msg 		= "Missing parameters to get company GST information.";
			}
		} else {
			$result 	= "";
			$statusCode = ERROR;
			$msg 		= "Token expired.";
		}
		$response['message'] 		= $msg;
		$response['status_code'] 	= $statusCode;
		$response['data'] 			= $result;
		return $response;
	}

	/**
	 * Use      : Verify CIN Details
	 * Author   : Kalpak Prajapati
	 * Date     : 03 Dec,2023
	 */
	public function VerifyCINDetails($request)
	{
		$msg 			= "";
		$statusCode 	= "200";
		$result 		= array();
		$CIN 			= (isset($request->cin) && !empty($request->cin))?$request->cin:"";
		if ($this->VerifyAPIToken($request)) {
			if (!empty($CIN)) {
				$result = $this->GetCompanyInformationByCIN($CIN);
				if(isset($result->error) && !empty($result->error)) {
					$statusCode = ERROR;
					$msg 		= "Error while fetching company information, please try again after sometime.";
				}
			} else {
				$statusCode = ERROR;
				$msg 		= "Missing parameters to get company GST information.";
			}
		} else {
			$result 	= "";
			$statusCode = ERROR;
			$msg 		= "Token expired.";
		}
		$response['message'] 		= $msg;
		$response['status_code'] 	= $statusCode;
		$response['data'] 			= $result;
		return $response;
	}

	/**
	 * Use      : Verify CIN Director Details By CIN
	 * Author   : Kalpak Prajapati
	 * Date     : 03 Dec,2023
	 */
	public function VerifyCompanyDirectorDetailsByCIN($request)
	{
		$msg 			= "";
		$statusCode 	= "200";
		$result 		= array();
		$CIN 			= (isset($request->cin) && !empty($request->cin))?$request->cin:"";
		if ($this->VerifyAPIToken($request)) {
			if (!empty($CIN)) {
				$result = $this->GetCompanyDirectorDetailsByCIN($CIN);
				if(isset($result->error) && !empty($result->error)) {
					$statusCode = ERROR;
					$msg 		= "Error while fetching company information, please try again after sometime.";
				}
			} else {
				$statusCode = ERROR;
				$msg 		= "Missing parameters to get company GST information.";
			}
		} else {
			$result 	= "";
			$statusCode = ERROR;
			$msg 		= "Token expired.";
		}
		$response['message'] 		= $msg;
		$response['status_code'] 	= $statusCode;
		$response['data'] 			= $result;
		return $response;
	}
}