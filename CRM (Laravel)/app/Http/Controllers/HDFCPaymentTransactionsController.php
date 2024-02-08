<?php

namespace App\Http\Controllers;

use App\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use App\Models\HdfcApiLog;
use Response;
use DB;
class HDFCPaymentTransactionsController extends LRBaseController
{
	 /**
	 *
	 * The status of $starttime is universe
	 *
	 * Potential value is datetime
	 *
	 * @public datetime
	 *
	 */
	public $starttime    = '';
	/**
	 *
	 * The status of $endtime is universe
	 *
	 * Potential value is datetime
	 *
	 * @public datetime
	 *
	 */
	public $endtime      = '';

	public $report_starttime 		= "";
	public $report_endtime 			= "";
	public $errorCodeSuccess 		= "0";
	public $errorMessageSuccess 	= "Success";
	public $errorCodeReject 		= "1";
	public $errorMessageReject 		= "Technical Reject";
	public $errorCodeDuplicate 		= "0";
	public $errorMessageDuplicate 	= "Duplicate";
	public $errorCodeFailed 		= "1";
	public $errorMessageUnauthentic = "You're not authorize to access this process.";
	public $IP_ADDRESS 				= array("203.88.147.186","103.86.19.72","123.201.21.122","43.241.144.32","223.226.209.81","175.100.161.31",
											"175.100.161.48","175.100.161.41","175.100.161.32","175.100.161.42","175.100.161.43","175.100.161.44",
											"175.100.161.45","103.120.107.41","103.120.107.48","103.120.107.31","103.120.107.42","103.120.107.43");

	/**
	* Function Name : SetVariables
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-09-27
	*/
	private function SetVariables($Request)
	{
		$this->report_starttime = (isset($Request->report_starttime) && !empty($Request->input('report_starttime')))?$Request->input('report_starttime'):"";
		$this->report_endtime 	= (isset($Request->report_endtime) && !empty($Request->input('report_endtime')))?$Request->input('report_endtime'):"";
		$RequestParams 			= $Request->all();
		if (!empty($RequestParams)) {
			foreach ($RequestParams as $RequestParam) {
				$json_array = json_decode($RequestParam);
				if (isset($json_array->location_id)) {
					$this->location_id = $json_array->location_id;
				}
				if (isset($json_array->report_starttime)) {
					$this->report_starttime = $json_array->report_starttime;
				}
				if (isset($json_array->report_endtime)) {
					$this->report_endtime = $json_array->report_endtime;
				}
			}
		}
		if (!is_array($this->location_id)) {
			if ($this->location_id != "_") {
				$this->location_id = explode("_",$this->location_id);
			} else {
				$this->location_id = array();
			}
		}
	}

	/**
	* Function Name : getPaymentTransactionDetails
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2023-10-16
	*/
	public function getPaymentTransactionDetails(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$domainReferenceNo 	= HdfcApiLog::SaveAPIRequestLog($request);
			$errorMessage 		= $this->errorMessageSuccess;
			$errorCode 			= $this->errorCodeSuccess;
			$returnResponse 	= array("GenericCorporateAlertResponse"=>array("domainReferenceNo"=>$domainReferenceNo,"errorMessage"=>$errorMessage,"errorCode"=>$errorCode));
			return response()->json($returnResponse);
		} else {
			$errorMessage 		= $this->errorMessageUnauthentic;
			$errorCode 			= $this->errorCodeFailed;
			$returnResponse 	= array("GenericCorporateAlertResponse"=>array("domainReferenceNo"=>0,"errorMessage"=>$errorMessage,"errorCode"=>$errorCode));
			return response()->json($returnResponse);
		}
	}
}