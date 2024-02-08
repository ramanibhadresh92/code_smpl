<?php

namespace App\Http\Controllers;
use App\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use App\Models\AdminUser;
use App\Models\WmSalesTargetMaster;
use App\Models\WmProductionReportMaster;
use App\Models\WmDepartment;
use App\Models\PortalImpactStats;
use App\Models\CCOFLocations;
use App\Models\CCOFMaster;
use App\Models\VGGS;
use App\Models\SalesPlanPrediction;
use App\Models\MissedSaledBasedOnPrediction;
use App\Models\IOTEquipments;
use App\Models\IOTEquipmentReading;
use App\Models\IOTEquipmentParameters;
use App\Models\WmDispatchProduct;
use App\Models\WmProductMaster;
use App\Models\WmDispatch;
use App\Models\CCOFLocationsWebsite;
use App\Models\WmClientMaster;
use App\Models\BaseLocationMaster;
use App\Classes\CCOF;
use App\Classes\GPAnalysis;
use App\Imports\ImportSalesPaymentSheet;
use App\Models\AmbajiPadYatraCollection;
use App\Models\AmbajiPadYatraSorting;
use JWTFactory;
use JWTAuth;
use Validator;
use Response;
use File;
use Storage;
use Input;
use DB;
use Excel;
use PDF;
use App\Models\WmSalesPaymentDetails;
class ImportCollectionController extends LRBaseController
{
	public $daterange 			= "";
	public $report_starttime 	= "";
	public $report_endtime 		= "";
	public $mrf_id 				= array();
	public $basestation_id 		= array();
	public $location_id 		= array();
	public $company_id 			= array();
	public $type_of_sales 		= 1;
	public $report_type 		= 1;
	public $exclude_cat			= array("RDF");
	public $IP_ADDRESS 			= array("203.88.147.186","103.86.19.72","123.201.21.122","223.226.209.81","27.57.163.167");
	public $arrLocation 		= array("1"=>"iCreate","2"=>"Mahatma Mandir","3"=>"Exhibition Hall 2");
	public $selected_month 		= 0;
	public $selected_year 		= 0;
	public $slave_id 			= "";
	public $amp_slave_id 		= "";
	public $product_ids 		= array();
	public $arrMonths 			= array();
	public $device_code 		= "";
	public $reportdate 			= "";
	public $reportmonth 		= "";
	public $client_ids 			= array();
	public $arrSelColumns 		= array();
	public $collection_dt 		= "";
	public $hdnaction 			= "";
	public $errorMessage 		= "";
	public $PHP_AUTH_USER 		= "Nepra";
	public $PHP_AUTH_PW 		= "Nepra$2023";

	private function SetVariables($request)
	{
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="HTTP AUTHENTICATION"');
			header('HTTP/1.0 401 Unauthorized');
			exit;
		} else if (isset($_SERVER['PHP_AUTH_USER'])) {
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
			if (($_SERVER['PHP_AUTH_USER'] != $this->PHP_AUTH_USER) || ($_SERVER['PHP_AUTH_PW'] != $this->PHP_AUTH_PW)) {
				header('WWW-Authenticate: Basic realm="HTTP AUTHENTICATION"');
				header('HTTP/1.0 401 Unauthorized');
				exit;
			}
		}

		$this->report_period 	= isset($request['report_period'])?$request['report_period']:0;
		$this->report_starttime = isset($request['report_starttime'])?$request['report_starttime']:"";
		$this->report_endtime 	= isset($request['report_endtime'])?$request['report_endtime']:"";
		$this->mrf_id 			= isset($request['mrf_id'])?$request['mrf_id']:array();
		$this->basestation_id 	= isset($request['basestation_id'])?$request['basestation_id']:array();
		$this->type_of_sales 	= isset($request['type_of_sales'])?$request['type_of_sales']:"";
		$this->report_type 		= isset($request['report_type'])?$request['report_type']:"";
		$this->location_id 		= isset($request['location_id'])?$request['location_id']:array();
		$this->product_ids 		= isset($request['product_ids'])?$request['product_ids']:array();
		$this->arrMonths 		= isset($request['arrMonth'])?$request['arrMonth']:array();
		$this->slave_id 		= isset($request['slave_id'])?$request['slave_id']:"";
		$this->amp_slave_id 	= isset($request['amp_slave_id'])?$request['amp_slave_id']:"";
		$this->device_code 		= isset($request['device_code'])?$request['device_code']:"";
		$this->reportdate 		= isset($request['reportdate'])?$request['reportdate']:date("Y-m-d");
		$this->reportmonth 		= isset($request['reportmonth'])?$request['reportmonth']:date("M-Y");
		$this->daterange 		= "";
		$this->client_ids 		= isset($request['client_ids'])?$request['client_ids']:array();
		$this->arrSelColumns 	= isset($request['column_ids'])?$request['column_ids']:array();
		$this->collection_dt 	= isset($request['collection_dt'])?$request['collection_dt']:date("Y-m-d");
		$this->hdnaction 		= isset($request['hdnaction'])?$request['hdnaction']:"";
		switch($this->report_period)
		{
			case 1:
			{
				$this->report_starttime = date("Y-m-d")." 00:00:00";
				$this->report_endtime 	= date("Y-m-d")." 23:59:59";
				break;
			}
			case 2:
			{
				$this->report_starttime = date("Y-m-d", strtotime("-1 day"))." 00:00:00";
				$this->report_endtime 	= date("Y-m-d", strtotime("-1 day"))." 23:59:59";
				break;
			}
			case 3:
			{
				$this->report_starttime = $this->week_day(1)." 00:00:00";
				$this->report_endtime 	= $this->week_day(7)." 23:59:59";
				break;
			}
			case 4:
			{
				$this->report_starttime = date("Y")."-".date("m")."-01"." 00:00:00";
				$this->report_endtime	= date('Y-m-d',strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))))." 23:59:59";
				break;
			}
			default:
			{
				if ($this->report_starttime != "" && $this->report_endtime != "") {
					$this->report_starttime = date("Y-m-d",strtotime($this->report_starttime))." 00:00:00";
					$this->report_endtime 	= date("Y-m-d",strtotime($this->report_endtime))." 23:59:59";
				}
				break;
			}
		}
		if (!empty($this->reportdate) && empty($this->report_starttime)) {
			$this->report_starttime = date("Y-m-d",strtotime($this->reportdate))." 00:00:00";
			$this->report_endtime 	= date("Y-m-d",strtotime($this->reportdate))." 23:59:59";
		}
		if ($this->report_starttime != "" && $this->report_endtime != "") {
			$this->daterange = date("M d, Y",strtotime($this->report_starttime))." - ".date("M d, Y",strtotime($this->report_endtime));
		} else {
			$this->daterange = "SELECT DATE RANGE";
		}
	}

	public function importfile(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], $this->IP_ADDRESS))
		{
			if ($request->isMethod("post"))
			{
				$doc_file 			= isset($_FILES['doc_file'])?$_FILES['doc_file']:array();
				$FILESIZE			= isset($doc_file['size'])?$doc_file['size']:0;
				$FILESIZE			= ($FILESIZE > 0)?(($FILESIZE/1024)/1024):$FILESIZE;
				$allowed_file_types = array("text/csv","application/vnd.ms-excel");
				if (empty($doc_file)
					|| (!isset($doc_file['tmp_name']) || empty($doc_file['tmp_name']))
					|| (!isset($doc_file['type']) || !in_array($doc_file['type'],$allowed_file_types))
					|| (empty($FILESIZE) || $FILESIZE > 10)
				) {
					echo json_encode(['error'=>"Please upload CSV only upto 10 MB in size."]);
					die;
				} else {
					if (isset($doc_file['name']) && $doc_file['error'] == 0) {
						$filename 			= preg_replace("/[^0-9a-z\.]/i","_",$doc_file['name']);
						$destination_path	= storage_path();
						$filename 			= $destination_path."/import_collection/".$filename;
						if (!file_exists($filename)) {
							move_uploaded_file($doc_file['tmp_name'],$filename);
							echo json_encode([]);
						} else {
							echo json_encode(['error'=>"File already exists with same name."]);
						}
						die;
					} else {
						echo json_encode(['error'=>"Please upload CSV only upto 10 MB in size."]);
						die;
					}
				}
			}
			return view("importcollection.importfile");
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	public function productionreportstat(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			$daterange							= $this->daterange;
			$report_starttime					= $this->report_starttime;
			$report_endtime						= $this->report_endtime;
			$MRF_LOCATION_MASTERS				= WmDepartment::where("is_virtual",0)->where("status",1)->pluck("department_name","id")->toArray();
			$Above_5_Production_Product_Stats 	= WmProductionReportMaster::GetProductionReportDetails($request,false,5,5);
			$Above_2_Production_Product_Stats 	= WmProductionReportMaster::GetProductionReportDetails($request,false,2,5);
			$Below_2_Production_Product_Stats 	= WmProductionReportMaster::GetProductionReportDetails($request,false,0,2);
			return view("importcollection.productionreportstat",[	"Above_5_Production_Product_Stats"=>$Above_5_Production_Product_Stats,
																	"Above_2_Production_Product_Stats"=>$Above_2_Production_Product_Stats,
																	"Below_2_Production_Product_Stats"=>$Below_2_Production_Product_Stats,
																	"MRF_LOCATIONS"=>$MRF_LOCATION_MASTERS,
																	"MRF_IDS"=>$this->mrf_id,
																	"daterange"=>$daterange,
																	"report_starttime"=>$report_starttime,
																	"report_endtime"=>$report_endtime]);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	public function saveImpactData(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], $this->IP_ADDRESS))
		{
			$PortalImpactStats = new PortalImpactStats();
			if ($request->isMethod("post")) {
				$PortalImpactStats->saveImpactData($request);
				return redirect()->route('save-impact-data')->with('message','Impact data updated successfully !!!');
			} else {
				$LastRecordData = $PortalImpactStats->get()->last();
			}
			return view("importcollection.impactdata",["LastRecordData"=>json_decode($LastRecordData->stats_json)]);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	public function importpaymentdata(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], $this->IP_ADDRESS))
		{
			if ($request->isMethod("post"))
			{
				$doc_file 			= isset($_FILES['doc_file'])?$_FILES['doc_file']:array();
				$FILESIZE			= isset($doc_file['size'])?$doc_file['size']:0;
				$FILESIZE			= ($FILESIZE > 0)?(($FILESIZE/1024)/1024):$FILESIZE;
				$allowed_file_types = array("text/csv","application/vnd.ms-excel");
				if (empty($doc_file)
					|| (!isset($doc_file['tmp_name']) || empty($doc_file['tmp_name']))
					|| (!isset($doc_file['type']) || !in_array($doc_file['type'],$allowed_file_types))
					|| (empty($FILESIZE) || $FILESIZE > 10)
				) {
					echo json_encode(['error'=>"Please upload CSV only upto 10 MB in size."]);
					die;
				} else {
					if (isset($doc_file['name']) && $doc_file['error'] == 0) {
						$filename 			= preg_replace("/[^0-9a-z\.]/i","_",$doc_file['name']);
						$destination_path	= storage_path();
						$filename 			= $destination_path."/import_payment_collection/".$filename;
						if (!file_exists($filename)) {
							move_uploaded_file($doc_file['tmp_name'],$filename);
							echo json_encode([]);
						} else {
							echo json_encode(['error'=>"File already exists with same name."]);
						}
						die;
					} else {
						echo json_encode(['error'=>"Please upload CSV only upto 10 MB in size."]);
						die;
					}
				}
			}
			return view("importcollection.importpaymentfile");
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	public function analysisreport(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			// $this->analysisreportByBaseLocationV2();
			if ($this->report_type == 2) {
				return $this->analysisreportByBaseLocation();
			} else {
				return $this->analysisreportByMRF();
			}
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function analysisreportByMRF()
	{
		$arrResult 		= array();
		$arrProductCat	= array();
		$arrMRF			= array();
		$arrTimePeriod	= array();
		$C_YEAR 		= date("Y");
		$N_YEAR 		= date("Y",strtotime("+1 Year"));
		$F_YEAR_START 	= $C_YEAR."-04-01 00:00:00";
		$F_YEAR_END 	= $N_YEAR."-03-01 23:59:59";
		$WhereCond 		= "";
		if (isset($this->type_of_sales) && !empty($this->type_of_sales)) {
			switch ($this->type_of_sales) {
				case 2: {
					$WhereCond = " AND wm_dispatch.bill_from_mrf_id = wm_dispatch.master_dept_id";
					break;
				}
				case 3: {
					$WhereCond = " AND wm_dispatch.bill_from_mrf_id != wm_dispatch.master_dept_id";
					break;
				}
			}
		}
		if (!empty($this->exclude_cat))
		{
			$WhereCond .= " AND wm_product_master.product_category NOT IN ("."'".implode("','", $this->exclude_cat)."'".")";
		}
		$SELECT_SQL 	= "	SELECT CONCAT(MONTHNAME(wm_dispatch.dispatch_date),'-',YEAR(wm_dispatch.dispatch_date)) AS DispatchMonth,
							YEAR(wm_dispatch.dispatch_date) AS Dispatch_Year,
							MONTH(wm_dispatch.dispatch_date) AS Dispatch_Month,
							IF (Bill_From_MRF.department_name IS NULL, From_MRF.department_name, Bill_From_MRF.department_name) AS MRF,
							wm_product_master.product_category AS P_C, SUM(wm_dispatch_product.quantity) AS QTY,
							ROUND((SUM(wm_dispatch_product.gross_amount) / SUM(wm_dispatch_product.quantity)),2) AS AVG_PRICE
							FROM wm_dispatch_product
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
							LEFT JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
							LEFT JOIN wm_department as Bill_From_MRF ON Bill_From_MRF.id = wm_dispatch.bill_from_mrf_id
							LEFT JOIN wm_department as From_MRF ON From_MRF.id = wm_dispatch.master_dept_id
							WHERE wm_dispatch.dispatch_date BETWEEN '$F_YEAR_START' AND '$F_YEAR_END'
							AND wm_dispatch.approval_status = 1
							$WhereCond
							GROUP BY MRF,Dispatch_Year,Dispatch_Month,P_C
							ORDER BY Dispatch_Year ASC, Dispatch_Month ASC ,MRF ASC,P_C ASC";
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		foreach ($SELECT_RES as $SELECT_ROW) {
			if (!isset($arrResult[$SELECT_ROW->MRF])) {
				$arrResult[$SELECT_ROW->MRF] = array();
			}
			if (!isset($arrResult[$SELECT_ROW->MRF][$SELECT_ROW->DispatchMonth])) {
				$arrResult[$SELECT_ROW->MRF][$SELECT_ROW->DispatchMonth] = array();
			}
			if (!isset($arrResult[$SELECT_ROW->MRF][$SELECT_ROW->DispatchMonth][$SELECT_ROW->P_C])) {
				$arrResult[$SELECT_ROW->MRF][$SELECT_ROW->DispatchMonth][$SELECT_ROW->P_C] = array("QTY"=>intval($SELECT_ROW->QTY),"Avg_Price"=>$SELECT_ROW->AVG_PRICE);
			}
			if (!in_array($SELECT_ROW->P_C,$arrProductCat)) array_push($arrProductCat,$SELECT_ROW->P_C);
			if (!in_array($SELECT_ROW->MRF,$arrMRF)) array_push($arrMRF,$SELECT_ROW->MRF);
			if (!in_array($SELECT_ROW->DispatchMonth,$arrTimePeriod)) array_push($arrTimePeriod,$SELECT_ROW->DispatchMonth);
		}
		$SELECT_SQL 	= "	SELECT
							CONCAT(MONTHNAME(wm_dispatch.dispatch_date),'-',YEAR(wm_dispatch.dispatch_date)) AS DispatchMonth,
							wm_product_master.product_category AS P_C, SUM(wm_dispatch_product.quantity) AS QTY,
							ROUND((SUM(wm_dispatch_product.gross_amount) / SUM(wm_dispatch_product.quantity)),2) AS AVG_PRICE
							FROM wm_dispatch_product
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
							LEFT JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
							WHERE wm_dispatch.dispatch_date BETWEEN '$F_YEAR_START' AND '$F_YEAR_END'
							AND wm_dispatch.approval_status = 1
							$WhereCond
							GROUP BY DispatchMonth,P_C
							ORDER BY DispatchMonth,P_C";
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		foreach ($SELECT_RES as $SELECT_ROW) {
			if (!isset($arrResult['ALL'][$SELECT_ROW->DispatchMonth][$SELECT_ROW->P_C])) {
				$arrResult['ALL'][$SELECT_ROW->DispatchMonth][$SELECT_ROW->P_C] = array("QTY"=>intval($SELECT_ROW->QTY),"Avg_Price"=>$SELECT_ROW->AVG_PRICE);
			}
		}
		array_push($arrMRF,'ALL');
		return view("importcollection.analysisreport",[	"Page_Title"=>"Avg Composition vs Sales Price (By MRF)",
														"arrResult"=>$arrResult,
														"arrMRF"=>$arrMRF,
														"colspan"=>((count($arrTimePeriod)+1) * 2),
														"arrProductCat"=>$arrProductCat,
														"arrTimePeriod"=>$arrTimePeriod,
														"type_of_sales"=>$this->type_of_sales,
														"report_type"=>$this->report_type]);
	}

	private function analysisreportByBaseLocation()
	{
		$arrResult 		= array();
		$arrProductCat	= array();
		$arrMRF			= array();
		$arrTimePeriod	= array();
		$C_YEAR 		= date("Y");
		$N_YEAR 		= date("Y",strtotime("+1 Year"));
		$F_YEAR_START 	= $C_YEAR."-04-01 00:00:00";
		$F_YEAR_END 	= $N_YEAR."-03-01 23:59:59";
		$WhereCond 		= "";
		if (isset($this->type_of_sales) && !empty($this->type_of_sales)) {
			switch ($this->type_of_sales) {
				case 2: {
					$WhereCond = " AND wm_dispatch.bill_from_mrf_id = wm_dispatch.master_dept_id";
					break;
				}
				case 3: {
					$WhereCond = " AND wm_dispatch.bill_from_mrf_id != wm_dispatch.master_dept_id";
					break;
				}
			}
		}
		if (!empty($this->exclude_cat))
		{
			$WhereCond .= " AND wm_product_master.product_category NOT IN ("."'".implode("','", $this->exclude_cat)."'".")";
		}
		$SELECT_SQL 	= "	SELECT CONCAT(MONTHNAME(wm_dispatch.dispatch_date),'-',YEAR(wm_dispatch.dispatch_date)) AS DispatchMonth,
							YEAR(wm_dispatch.dispatch_date) AS Dispatch_Year,
							MONTH(wm_dispatch.dispatch_date) AS Dispatch_Month,
							IF (Bill_From_BL.base_location_name IS NULL, From_MRF_BL.base_location_name, Bill_From_BL.base_location_name) AS MRF,
							wm_product_master.product_category AS P_C, SUM(wm_dispatch_product.quantity) AS QTY,
							ROUND((SUM(wm_dispatch_product.gross_amount) / SUM(wm_dispatch_product.quantity)),2) AS AVG_PRICE
							FROM wm_dispatch_product
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
							LEFT JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
							LEFT JOIN wm_department as Bill_From_MRF ON Bill_From_MRF.id = wm_dispatch.bill_from_mrf_id
							LEFT JOIN wm_department as From_MRF ON From_MRF.id = wm_dispatch.master_dept_id
							LEFT JOIN base_location_master as Bill_From_BL ON Bill_From_MRF.base_location_id = Bill_From_BL.id
							LEFT JOIN base_location_master as From_MRF_BL ON From_MRF.base_location_id = From_MRF_BL.id
							WHERE wm_dispatch.dispatch_date BETWEEN '$F_YEAR_START' AND '$F_YEAR_END'
							AND wm_dispatch.approval_status = 1
							$WhereCond
							GROUP BY MRF,Dispatch_Year,Dispatch_Month,P_C
							ORDER BY Dispatch_Year ASC, Dispatch_Month ASC ,MRF ASC,P_C ASC";
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		foreach ($SELECT_RES as $SELECT_ROW) {
			if (!isset($arrResult[$SELECT_ROW->MRF])) {
				$arrResult[$SELECT_ROW->MRF] = array();
			}
			if (!isset($arrResult[$SELECT_ROW->MRF][$SELECT_ROW->DispatchMonth])) {
				$arrResult[$SELECT_ROW->MRF][$SELECT_ROW->DispatchMonth] = array();
			}
			if (!isset($arrResult[$SELECT_ROW->MRF][$SELECT_ROW->DispatchMonth][$SELECT_ROW->P_C])) {
				$arrResult[$SELECT_ROW->MRF][$SELECT_ROW->DispatchMonth][$SELECT_ROW->P_C] = array("QTY"=>intval($SELECT_ROW->QTY),"Avg_Price"=>$SELECT_ROW->AVG_PRICE);
			}
			if (!in_array($SELECT_ROW->P_C,$arrProductCat)) array_push($arrProductCat,$SELECT_ROW->P_C);
			if (!in_array($SELECT_ROW->MRF,$arrMRF)) array_push($arrMRF,$SELECT_ROW->MRF);
			if (!in_array($SELECT_ROW->DispatchMonth,$arrTimePeriod)) array_push($arrTimePeriod,$SELECT_ROW->DispatchMonth);
		}
		$SELECT_SQL 	= "	SELECT
							CONCAT(MONTHNAME(wm_dispatch.dispatch_date),'-',YEAR(wm_dispatch.dispatch_date)) AS DispatchMonth,
							wm_product_master.product_category AS P_C, SUM(wm_dispatch_product.quantity) AS QTY,
							ROUND((SUM(wm_dispatch_product.gross_amount) / SUM(wm_dispatch_product.quantity)),2) AS AVG_PRICE
							FROM wm_dispatch_product
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
							LEFT JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
							WHERE wm_dispatch.dispatch_date BETWEEN '$F_YEAR_START' AND '$F_YEAR_END'
							AND wm_dispatch.approval_status = 1
							$WhereCond
							GROUP BY DispatchMonth,P_C
							ORDER BY DispatchMonth,P_C";
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		foreach ($SELECT_RES as $SELECT_ROW) {
			if (!isset($arrResult['ALL'][$SELECT_ROW->DispatchMonth][$SELECT_ROW->P_C])) {
				$arrResult['ALL'][$SELECT_ROW->DispatchMonth][$SELECT_ROW->P_C] = array("QTY"=>intval($SELECT_ROW->QTY),"Avg_Price"=>$SELECT_ROW->AVG_PRICE);
			}
		}
		array_push($arrMRF,'ALL');
		return view("importcollection.analysisreport",[	"Page_Title"=>"Avg Composition vs Sales Price (By Base Location)",
														"arrResult"=>$arrResult,
														"arrMRF"=>$arrMRF,
														"colspan"=>((count($arrTimePeriod)+1) * 2),
														"arrProductCat"=>$arrProductCat,
														"arrTimePeriod"=>$arrTimePeriod,
														"type_of_sales"=>$this->type_of_sales,
														"report_type"=>$this->report_type]);
	}

	private function analysisreportByBaseLocationV2()
	{
		$arrResult 		= array();
		$arrProductCat	= array();
		$arrMRF			= array();
		$arrTimePeriod	= array();
		$C_YEAR 		= date("Y");
		$N_YEAR 		= date("Y",strtotime("+1 Year"));
		$F_YEAR_START 	= $C_YEAR."-04-01 00:00:00";
		$F_YEAR_END 	= $N_YEAR."-03-01 23:59:59";
		$WhereCond 		= "";
		if (isset($this->type_of_sales) && !empty($this->type_of_sales)) {
			switch ($this->type_of_sales) {
				case 2: {
					$WhereCond = " AND wm_dispatch.bill_from_mrf_id = wm_dispatch.master_dept_id";
					break;
				}
				case 3: {
					$WhereCond = " AND wm_dispatch.bill_from_mrf_id != wm_dispatch.master_dept_id";
					break;
				}
			}
		}
		if (!empty($this->exclude_cat))
		{
			$WhereCond .= " AND wm_product_master.product_category NOT IN ("."'".implode("','", $this->exclude_cat)."'".")";
		}
		$SELECT_SQL 	= "	SELECT CONCAT(MONTHNAME(wm_dispatch.dispatch_date),'-',YEAR(wm_dispatch.dispatch_date)) AS DispatchMonth,
							YEAR(wm_dispatch.dispatch_date) AS Dispatch_Year,
							MONTH(wm_dispatch.dispatch_date) AS Dispatch_Month,
							IF (Bill_From_BL.base_location_name IS NULL, From_MRF_BL.base_location_name, Bill_From_BL.base_location_name) AS MRF,
							wm_product_master.product_category AS P_C, SUM(wm_dispatch_product.quantity) AS QTY,
							ROUND((SUM(wm_dispatch_product.gross_amount) / SUM(wm_dispatch_product.quantity)),2) AS AVG_PRICE
							FROM wm_dispatch_product
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
							LEFT JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
							LEFT JOIN wm_department as Bill_From_MRF ON Bill_From_MRF.id = wm_dispatch.bill_from_mrf_id
							LEFT JOIN wm_department as From_MRF ON From_MRF.id = wm_dispatch.master_dept_id
							LEFT JOIN base_location_master as Bill_From_BL ON Bill_From_MRF.base_location_id = Bill_From_BL.id
							LEFT JOIN base_location_master as From_MRF_BL ON From_MRF.base_location_id = From_MRF_BL.id
							WHERE wm_dispatch.dispatch_date BETWEEN '$F_YEAR_START' AND '$F_YEAR_END'
							AND wm_dispatch.approval_status = 1
							$WhereCond
							GROUP BY MRF,Dispatch_Year,Dispatch_Month,P_C
							ORDER BY Dispatch_Year ASC, Dispatch_Month ASC ,MRF ASC,P_C ASC";
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		foreach ($SELECT_RES as $SELECT_ROW) {
			if (!isset($arrResult[$SELECT_ROW->MRF])) {
				$arrResult[$SELECT_ROW->MRF] = array();
			}
			if (!isset($arrResult[$SELECT_ROW->MRF][$SELECT_ROW->P_C])) {
				$arrResult[$SELECT_ROW->MRF][$SELECT_ROW->P_C] = array();
			}
			$arrResult[$SELECT_ROW->MRF][$SELECT_ROW->P_C][$SELECT_ROW->DispatchMonth] = array(	"Month"=>$SELECT_ROW->DispatchMonth,
																								"QTY"=>intval($SELECT_ROW->QTY),
																								"Avg_Price"=>$SELECT_ROW->AVG_PRICE);
			if (!in_array($SELECT_ROW->P_C,$arrProductCat)) array_push($arrProductCat,$SELECT_ROW->P_C);
			if (!in_array($SELECT_ROW->MRF,$arrMRF)) array_push($arrMRF,$SELECT_ROW->MRF);
			if (!in_array($SELECT_ROW->DispatchMonth,$arrTimePeriod)) array_push($arrTimePeriod,$SELECT_ROW->DispatchMonth);
		}
		$SELECT_SQL 	= "	SELECT
							CONCAT(MONTHNAME(wm_dispatch.dispatch_date),'-',YEAR(wm_dispatch.dispatch_date)) AS DispatchMonth,
							wm_product_master.product_category AS P_C, SUM(wm_dispatch_product.quantity) AS QTY,
							ROUND((SUM(wm_dispatch_product.gross_amount) / SUM(wm_dispatch_product.quantity)),2) AS AVG_PRICE
							FROM wm_dispatch_product
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
							LEFT JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
							WHERE wm_dispatch.dispatch_date BETWEEN '$F_YEAR_START' AND '$F_YEAR_END'
							AND wm_dispatch.approval_status = 1
							$WhereCond
							GROUP BY DispatchMonth,P_C
							ORDER BY DispatchMonth,P_C";
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		foreach ($SELECT_RES as $SELECT_ROW) {
			if (!isset($arrResult['ALL'][$SELECT_ROW->P_C])) {
				$arrResult['ALL'][$SELECT_ROW->P_C] = array();
			}
			$arrResult['ALL'][$SELECT_ROW->P_C][$SELECT_ROW->DispatchMonth] = array("Month"=>$SELECT_ROW->DispatchMonth,
																					"QTY"=>intval($SELECT_ROW->QTY),
																					"Avg_Price"=>$SELECT_ROW->AVG_PRICE);
		}
		array_push($arrMRF,'ALL');
		$counter = 0;
		$arrResult2 = array();
		foreach($arrResult as $Mrf_Name => $ResultRow) {
			$arrResult2[$counter]['lable'][] = $Mrf_Name;
			foreach($arrTimePeriod as $Month_Name) {
				array_push($arrResult2[$counter]['lable'],$Month_Name);
			}
			$arrResult2[$counter]['product'] = array();
			foreach($ResultRow as $Product_Name=>$Product_Sales_Data) {
				$SalesData = array();
				foreach($arrTimePeriod as $Month_Name) {
					if (isset($Product_Sales_Data[$Month_Name])) {
						array_push($SalesData,$Product_Sales_Data[$Month_Name]);
					} else {
						$TempArray 	= array("Month"=>$Month_Name,"QTY"=>"","Avg_Price"=>"");
						array_push($SalesData,$TempArray);
					}
				}
				$arrResult2[$counter]['product'][] = array('name'=> $Product_Name,'sales_data'=>$SalesData);
			}
			$counter++;
		}
		echo "<pre>";
		print_r($arrResult2);
		die;
		return view("importcollection.analysisreport",[	"Page_Title"=>"Avg Composition vs Sales Price (By Base Location)",
														"arrResult"=>$arrResult,
														"arrMRF"=>$arrMRF,
														"colspan"=>((count($arrTimePeriod)+1) * 2),
														"arrProductCat"=>$arrProductCat,
														"arrTimePeriod"=>$arrTimePeriod,
														"type_of_sales"=>$this->type_of_sales,
														"report_type"=>$this->report_type]);
	}

	public function readytodispach(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			if ($this->report_type == 2) {
				return $this->readytodispachByBaseLocation();
			} else {
				return $this->readytodispachByMRF();
			}
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function readytodispachByMRF()
	{
		$arrResult 		= array();
		$WhereCond 		= "";
		$StockDate		= date("Y-m-d");
		$arrMRF 		= array();
		$SELECT_SQL 	= "	SELECT wm_department.department_name as MRF_NAME,
							wm_product_master.title AS PRODUCT_NAME,
							getSalesProductCurrentStock(stock_ladger.product_id,'".$StockDate."',stock_ladger.mrf_id,0) AS Current_Stock,
							stock_ladger.product_id AS PRODUCT_ID,
							stock_ladger.mrf_id AS MRF_ID
							FROM stock_ladger
							INNER JOIN wm_product_master ON wm_product_master.id = stock_ladger.product_id
							INNER JOIN wm_department ON wm_department.id = stock_ladger.mrf_id
							WHERE stock_ladger.product_type = ".PRODUCT_SALES."
							AND stock_ladger.stock_date = '".$StockDate."'
							HAVING Current_Stock > 0
							ORDER BY MRF_NAME ASC, PRODUCT_NAME ASC, Current_Stock ASC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			$counter = 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$REMAINING_STOCK = 0;
				if (!isset($arrResult[$SELECT_ROW->MRF_NAME])) {
					$arrResult[$SELECT_ROW->MRF_NAME] = array();
					array_push($arrMRF,$SELECT_ROW->MRF_NAME);
					$counter = 0;
				}
				$arrResult[$SELECT_ROW->MRF_NAME][$counter]['PRODUCT_NAME'] 		= $SELECT_ROW->PRODUCT_NAME;
				$arrResult[$SELECT_ROW->MRF_NAME][$counter]['CURRENT_STOCK'] 		= $SELECT_ROW->Current_Stock;
				$arrProductSalesPlan 												= $this->getProductSalesPlan($SELECT_ROW->PRODUCT_ID,$SELECT_ROW->MRF_ID,$SELECT_ROW->Current_Stock,$StockDate);
				$arrResult[$SELECT_ROW->MRF_NAME][$counter]['SALES_PLAN']			= $arrProductSalesPlan['SALES_PLAN'];
				$arrResult[$SELECT_ROW->MRF_NAME][$counter]['REMAINING_STOCK'] 		= $arrProductSalesPlan['REMAINING_STOCK'];
				$counter++;
			}
		}
		return view("importcollection.readytodispatch",["Page_Title"=>"Ready To Dispatch (By MRF)",
														"arrResult"=>$arrResult,
														"arrMRF"=>$arrMRF,
														"report_type"=>$this->report_type]);
	}

	private function readytodispachByBaseLocation()
	{
		$arrResult 		= array();
		$WhereCond 		= "";
		$StockDate		= date("Y-m-d");
		$arrMRF 		= array();
		$SELECT_SQL 	= "	SELECT base_location_master.base_location_name as MRF_NAME,
							wm_product_master.title AS PRODUCT_NAME,
							getSalesProductCurrentStock(stock_ladger.product_id,'".$StockDate."',wm_department.base_location_id,1) AS Current_Stock,
							stock_ladger.product_id AS PRODUCT_ID,
							wm_department.base_location_id AS BASE_LOCATION_ID
							FROM stock_ladger
							INNER JOIN wm_product_master ON wm_product_master.id = stock_ladger.product_id
							INNER JOIN wm_department ON wm_department.id = stock_ladger.mrf_id
							INNER JOIN base_location_master ON base_location_master.id = wm_department.base_location_id
							WHERE stock_ladger.product_type = ".PRODUCT_SALES."
							AND stock_ladger.stock_date = '".$StockDate."'
							GROUP BY wm_department.base_location_id,stock_ladger.product_id
							HAVING Current_Stock > 0
							ORDER BY MRF_NAME ASC, PRODUCT_NAME ASC, Current_Stock ASC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			$counter = 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$REMAINING_STOCK = 0;
				if (!isset($arrResult[$SELECT_ROW->MRF_NAME])) {
					$arrResult[$SELECT_ROW->MRF_NAME] = array();
					array_push($arrMRF,$SELECT_ROW->MRF_NAME);
					$counter = 0;
				}
				$arrResult[$SELECT_ROW->MRF_NAME][$counter]['PRODUCT_NAME'] 		= $SELECT_ROW->PRODUCT_NAME;
				$arrResult[$SELECT_ROW->MRF_NAME][$counter]['CURRENT_STOCK'] 		= $SELECT_ROW->Current_Stock;
				$arrProductSalesPlan 												= $this->getProductSalesPlan($SELECT_ROW->PRODUCT_ID,$SELECT_ROW->BASE_LOCATION_ID,$SELECT_ROW->Current_Stock,$StockDate,1);
				$arrResult[$SELECT_ROW->MRF_NAME][$counter]['SALES_PLAN']			= $arrProductSalesPlan['SALES_PLAN'];
				$arrResult[$SELECT_ROW->MRF_NAME][$counter]['REMAINING_STOCK'] 		= $arrProductSalesPlan['REMAINING_STOCK'];
				$counter++;
			}
		}
		return view("importcollection.readytodispatch",["Page_Title"=>"Ready To Dispatch (By Base Location)",
														"arrResult"=>$arrResult,
														"arrMRF"=>$arrMRF,
														"report_type"=>$this->report_type]);
	}

	private function getProductSalesPlan($product_id,$mrf_id,$current_stock,$sales_date,$BaseLocation=false)
	{
		$Sales_Qty 				= 0;
		$arrProductSalesPlan	= array('SALES_PLAN'=>array(),'REMAINING_STOCK'=>$current_stock);
		if (!$BaseLocation) {
			$SELECT_SQL = "	SELECT wm_client_master.client_name AS CLIENT_NAME,
							0 AS SALES_QTY,
							wm_dispatch_plan_product.rate AS SALES_RATE
							FROM wm_dispatch_plan_product
							INNER JOIN wm_dispatch_plan ON wm_dispatch_plan_product.dispatch_plan_id = wm_dispatch_plan.id
							INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch_plan.client_master_id
							WHERE '$sales_date' BETWEEN wm_dispatch_plan.dispatch_plan_date AND wm_dispatch_plan.valid_last_date
							AND wm_dispatch_plan.approval_status IN (0,1)
							AND wm_dispatch_plan.master_dept_id = $mrf_id
							AND wm_dispatch_plan_product.sales_product_id = $product_id";
		} else {
			$SELECT_SQL = "	SELECT wm_client_master.client_name AS CLIENT_NAME,
							0 AS SALES_QTY,
							wm_dispatch_plan_product.rate AS SALES_RATE
							FROM wm_dispatch_plan_product
							INNER JOIN wm_dispatch_plan ON wm_dispatch_plan_product.dispatch_plan_id = wm_dispatch_plan.id
							INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch_plan.client_master_id
							INNER JOIN wm_department ON wm_dispatch_plan.master_dept_id = wm_department.id
							WHERE '$sales_date' BETWEEN wm_dispatch_plan.dispatch_plan_date AND wm_dispatch_plan.valid_last_date
							AND wm_dispatch_plan.approval_status IN (0,1)
							AND wm_department.base_location_id = $mrf_id
							AND wm_dispatch_plan_product.sales_product_id = $product_id";
		}
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			foreach($SELECT_RES as $SELECT_ROW) {
				$arrProductSalesPlan['SALES_PLAN'][]= array("CLIENT_NAME"=>$SELECT_ROW->CLIENT_NAME,
															"SALES_QTY"=>number_format($SELECT_ROW->SALES_QTY,2),
															"SALES_RATE"=>$SELECT_ROW->SALES_RATE);
				$Sales_Qty += $SELECT_ROW->SALES_QTY;
			}
			if (!empty($Sales_Qty)) {
				$arrProductSalesPlan['REMAINING_STOCK'] = number_format($Sales_Qty,2);
			}
		}
		return $arrProductSalesPlan;
	}

	public function getsalestarget(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS)) {
			$this->SetVariables($request);
			return $this->getMRFWiseTarget();
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function getMRFWiseTarget()
	{
		$arrResult 		= array();
		$WhereCond 		= "";
		$StartDate		= date("Y-m-d")." 00:00:00";
		$EndDate		= date("Y-m-t")." 23:59:59";
		$StartDate		= "2021-09-01 00:00:00";
		$EndDate		= "2021-09-30 23:59:59";
		$MONTH 			= date("m",strtotime($StartDate));
		$YEAR 			= date("Y",strtotime($StartDate));
		$arrMRF 		= array();
		$arrServiceTypes= array(1043001=>"EPR Services",1043002=>"Other Service");
		$BASELOCATIONID = 1;
		$ASSIGNEDBLIDS	= array(1);
		$SELECT_SQL 	= "	SELECT wm_department.id as MRF_ID,
							wm_department.department_name as MRF_NAME,
							ROUND(wm_sales_target_master.bill_from_mrf_target) as MRF_TARGET,
							ROUND(wm_sales_target_master.virtual_mrf_target) as AGR_TARGET,
							wm_department.is_service_mrf as SERVICE_MRF,
							ROUND(getAchivedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0)) AS MRF_ACHIVED,
							ROUND(getAchivedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0)) AS AGR_ACHIVED,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0,0) AS MRF_CN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0,0) AS MRF_DN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,1,0) AS AGR_CN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,1,0) AS AGR_DN
							FROM wm_sales_target_master
							INNER JOIN wm_department ON wm_department.id = wm_sales_target_master.mrf_id
							WHERE wm_sales_target_master.month = $MONTH
							AND wm_sales_target_master.year = $YEAR
							AND wm_department.base_location_id IN (".$BASELOCATIONID.")
							ORDER BY wm_department.department_name ASC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);


		$MRF_TARGET_TOTAL 	= 0;
		$AGR_TARGET_TOTAL 	= 0;
		$MRF_ACHIVED_TOTAL 	= 0;
		$AGR_ACHIVED_TOTAL 	= 0;
		$MRF_CNDN_TOTAL 	= 0;
		$AGR_CNDN_TOTAL 	= 0;
		$MRF_FINAL_TOTAL 	= 0;
		$AGR_FINAL_TOTAL 	= 0;
		$TARGET_GTOTAL 		= 0;
		$ACHIVED_GTOTAL 	= 0;
		$CNDN_GTOTAL 		= 0;
		$FINAL_GTOTAL 		= 0;
		$F_TRUE 			= true;
		if (!empty($SELECT_RES)) {
			$counter 			= 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{

				if ($SELECT_ROW->SERVICE_MRF == 1 && $F_TRUE)
				{
					$scounter							= 0;
					$arrResult[$counter]['MRF_ID'] 		= $SELECT_ROW->MRF_ID;
					$arrResult[$counter]['MRF_TARGET'] 	= $SELECT_ROW->MRF_TARGET;
					$arrResult[$counter]['AGR_TARGET'] 	= $SELECT_ROW->AGR_TARGET;
					$TARGET_GTOTAL 						+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AGR_TARGET);
					$arrResult[$counter]['childs']		= array();

					$S_ROW_MRF_ACHIVED 	= 0;
					$S_ROW_AGR_ACHIVED 	= 0;
					$S_MRF_CN 			= 0;
					$S_MRF_DN 			= 0;
					$S_AGR_CN 			= 0;
					$S_AGR_DN 			= 0;
					foreach($arrServiceTypes as $ServiceType=>$ServiceTitle)
					{
						$SELECT_S_SQL 	= "	SELECT
											getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,".$ServiceType.",0) AS MRF_ACHIVED,
											0 AS AGR_ACHIVED,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,0,".$ServiceType.",0) AS MRF_CN,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0,0,".$ServiceType.",0) AS MRF_DN,
											0 AS AGR_CN,
											0 AS AGR_DN";
						$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
						foreach($SELECT_S_RES as $SELECT_S_ROW)
						{
							$MRF_CN 	= ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
							$AGR_CN 	= ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
							$arrResult[$counter]['childs'][$scounter] = array(	"MRF_NAME"=>$ServiceTitle,
																				"MRF_ACHIVED"=>$SELECT_S_ROW->MRF_ACHIVED,
																				"AGR_ACHIVED"=>$SELECT_S_ROW->AGR_ACHIVED,
																				"MRF_CN"=>$MRF_CN,
																				"AGR_CN"=>$AGR_CN);

							$S_ROW_MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
							$S_ROW_AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
							$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
							$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
							$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
							$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;
						}
						$SELECT_S_SQL 	= "	SELECT
											getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_ACHIVED,
											0 AS AGR_ACHIVED,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",0,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_CN,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$BASELOCATIONID.",1,0,1,".$ServiceType.",".$SELECT_ROW->MRF_ID.") AS MRF_DN,
											0 AS AGR_CN,
											0 AS AGR_DN";
						$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
						foreach($SELECT_S_RES as $SELECT_S_ROW)
						{
							$MRF_ACHIVED 	= $arrResult[$counter]['childs'][$scounter]['MRF_ACHIVED'] + ($SELECT_S_ROW->MRF_ACHIVED);
							$AGR_ACHIVED 	= $arrResult[$counter]['childs'][$scounter]['AGR_ACHIVED'] + ($SELECT_S_ROW->AGR_ACHIVED);
							$MRF_CN 		= $arrResult[$counter]['childs'][$scounter]['MRF_CN'] + ($SELECT_S_ROW->MRF_CN - $SELECT_S_ROW->MRF_DN);
							$AGR_CN 		= $arrResult[$counter]['childs'][$scounter]['AGR_CN'] + ($SELECT_S_ROW->AGR_CN - $SELECT_S_ROW->AGR_DN);
							$arrResult[$counter]['childs'][$scounter] = array(	"MRF_NAME"=>$ServiceTitle,
																				"MRF_ACHIVED"=>$MRF_ACHIVED,
																				"AGR_ACHIVED"=>$AGR_ACHIVED,
																				"MRF_CN"=>$MRF_CN,
																				"AGR_CN"=>$AGR_CN);

							$S_ROW_MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
							$S_ROW_AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
							$S_MRF_CN 			+= $SELECT_S_ROW->MRF_CN;
							$S_MRF_DN 			+= $SELECT_S_ROW->MRF_DN;
							$S_AGR_CN 			+= $SELECT_S_ROW->AGR_CN;
							$S_AGR_DN 			+= $SELECT_S_ROW->AGR_DN;
							$scounter++;
						}
					}
					$S_MRF_FINAL 		= ($SELECT_ROW->MRF_TARGET - ($S_ROW_MRF_ACHIVED + $S_MRF_DN) + $S_MRF_CN);
					$S_AGR_FINAL 		= ($SELECT_ROW->AGR_TARGET - ($S_ROW_AGR_ACHIVED + $S_AGR_DN) + $S_AGR_CN);

					if ($S_MRF_FINAL >= $arrResult[$counter]['MRF_TARGET']) {
						$arrResult[$counter]['MRF_FINAL']  	= $S_MRF_FINAL;
					} else {
						$arrResult[$counter]['MRF_FINAL']  	= ($S_MRF_FINAL * -1);
					}
					if ($S_AGR_FINAL >= $arrResult[$counter]['AGR_TARGET']) {
						$arrResult[$counter]['AGR_FINAL']  	= $S_AGR_FINAL;
					} else {
						$arrResult[$counter]['AGR_FINAL']  	= ($S_AGR_FINAL * -1);
					}


					$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
					$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
					$MRF_ACHIVED_TOTAL 	+= $S_ROW_MRF_ACHIVED;
					$AGR_ACHIVED_TOTAL 	+= $S_ROW_AGR_ACHIVED;
					$MRF_CNDN_TOTAL 	+= ($S_MRF_CN - $S_MRF_DN);
					$AGR_CNDN_TOTAL 	+= ($S_AGR_CN - $S_AGR_DN);
					$MRF_FINAL_TOTAL 	+= $S_MRF_FINAL;
					$AGR_FINAL_TOTAL 	+= $S_AGR_FINAL;
					$ACHIVED_GTOTAL 	+= ($S_ROW_MRF_ACHIVED + $S_ROW_AGR_ACHIVED);
					$CNDN_GTOTAL 		+= ($S_MRF_CN + $S_AGR_CN);
					$FINAL_GTOTAL 		+= ($S_MRF_FINAL + $S_AGR_FINAL);
					$counter++;
				} else {
					$arrResult[$counter]['MRF_ID'] 			= $SELECT_ROW->MRF_ID;
					$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
					$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
					$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
					$arrResult[$counter]['MRF_ACHIVED'] 	= $SELECT_ROW->MRF_ACHIVED;
					$arrResult[$counter]['AGR_ACHIVED'] 	= $SELECT_ROW->AGR_ACHIVED;
					$arrResult[$counter]['MRF_CN'] 			= $SELECT_ROW->MRF_CN - $SELECT_ROW->MRF_DN;
					$arrResult[$counter]['AGR_CN'] 			= $SELECT_ROW->AGR_CN - $SELECT_ROW->AGR_DN;
					$MRF_FINAL 								= ($SELECT_ROW->MRF_TARGET - ($SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->MRF_DN)) + $SELECT_ROW->MRF_CN;
					$AGR_FINAL 								= ($SELECT_ROW->AGR_TARGET - ($SELECT_ROW->AGR_ACHIVED + $SELECT_ROW->AGR_DN)) + $SELECT_ROW->AGR_CN;

					if ($MRF_FINAL >= $arrResult[$counter]['MRF_TARGET']) {
						$arrResult[$counter]['MRF_FINAL']  	= $MRF_FINAL;
					} else {
						$arrResult[$counter]['MRF_FINAL']  	= ($MRF_FINAL * -1);
					}
					if ($AGR_FINAL >= $arrResult[$counter]['AGR_TARGET']) {
						$arrResult[$counter]['AGR_FINAL']  	= $AGR_FINAL;
					} else {
						$arrResult[$counter]['AGR_FINAL']  	= ($AGR_FINAL * -1);
					}

					$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
					$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
					$MRF_ACHIVED_TOTAL 	+= $SELECT_ROW->MRF_ACHIVED;
					$AGR_ACHIVED_TOTAL 	+= $SELECT_ROW->AGR_ACHIVED;
					$MRF_CNDN_TOTAL 	+= $arrResult[$counter]['MRF_CN'];
					$AGR_CNDN_TOTAL 	+= $arrResult[$counter]['AGR_CN'];
					$MRF_FINAL_TOTAL 	+= $MRF_FINAL;
					$AGR_FINAL_TOTAL 	+= $AGR_FINAL;

					$TARGET_GTOTAL 		+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AGR_TARGET);
					$ACHIVED_GTOTAL 	+= ($SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->AGR_ACHIVED);
					$CNDN_GTOTAL 		+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AGR_CN']);
					$FINAL_GTOTAL 		+= ($MRF_FINAL + $AGR_FINAL);
					$counter++;
				}
			}
		}

		if ($MRF_FINAL_TOTAL <= $MRF_TARGET_TOTAL) {
			$MRF_FINAL_TOTAL = ($MRF_FINAL_TOTAL * -1);
		}
		if ($AGR_FINAL_TOTAL <= $AGR_TARGET_TOTAL) {
			$AGR_FINAL_TOTAL = ($AGR_FINAL_TOTAL * -1);
		}
		if ($FINAL_GTOTAL <= $TARGET_GTOTAL) {
			$FINAL_GTOTAL = ($FINAL_GTOTAL * -1);
		}

		$arrFinalResult['BY_MRF'] 	= array("arrResult" 		=> $arrResult,
											"MRF_TARGET_TOTAL" 	=> $MRF_TARGET_TOTAL,
											"AGR_TARGET_TOTAL" 	=> $AGR_TARGET_TOTAL,
											"MRF_ACHIVED_TOTAL" => $MRF_ACHIVED_TOTAL,
											"AGR_ACHIVED_TOTAL" => $AGR_ACHIVED_TOTAL,
											"MRF_CNDN_TOTAL" 	=> $MRF_CNDN_TOTAL,
											"AGR_CNDN_TOTAL" 	=> $AGR_CNDN_TOTAL,
											"MRF_FINAL_TOTAL" 	=> $MRF_FINAL_TOTAL,
											"AGR_FINAL_TOTAL" 	=> $AGR_FINAL_TOTAL,
											"TARGET_GTOTAL"		=> $TARGET_GTOTAL,
											"ACHIVED_GTOTAL" 	=> $ACHIVED_GTOTAL,
											"CNDN_GTOTAL" 		=> $CNDN_GTOTAL,
											"FINAL_GTOTAL" 		=> $FINAL_GTOTAL);

		$SELECT_SQL 	= "	SELECT base_location_master.id as MRF_ID,
							base_location_master.base_location_name as MRF_NAME,
							ROUND(sum(wm_sales_target_master.bill_from_mrf_target)) as MRF_TARGET,
							ROUND(sum(wm_sales_target_master.virtual_mrf_target)) as AGR_TARGET,
							getAchivedTarget('".$StartDate."','".$EndDate."',base_location_master.id,0,1) AS MRF_ACHIVED,
							getAchivedTarget('".$StartDate."','".$EndDate."',base_location_master.id,1,1) AS AGR_ACHIVED,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,0,1) AS MRF_CN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,1,0,1) AS MRF_DN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,1,1) AS AGR_CN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,1,1,1) AS AGR_DN
							FROM base_location_master
							INNER JOIN wm_sales_target_master ON base_location_master.id = wm_sales_target_master.base_location_id
							WHERE wm_sales_target_master.month = $MONTH
							AND wm_sales_target_master.year = $YEAR
							AND base_location_master.id IN (".implode(",",$ASSIGNEDBLIDS).")
							GROUP BY base_location_master.id
							ORDER BY MRF_NAME ASC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);

		$arrResult 			= array();
		$MRF_TARGET_TOTAL 	= 0;
		$AGR_TARGET_TOTAL 	= 0;
		$MRF_ACHIVED_TOTAL 	= 0;
		$AGR_ACHIVED_TOTAL 	= 0;
		$MRF_CNDN_TOTAL 	= 0;
		$AGR_CNDN_TOTAL 	= 0;
		$MRF_FINAL_TOTAL 	= 0;
		$AGR_FINAL_TOTAL 	= 0;
		$TARGET_GTOTAL 		= 0;
		$ACHIVED_GTOTAL 	= 0;
		$CNDN_GTOTAL 		= 0;
		$FINAL_GTOTAL 		= 0;
		if (!empty($SELECT_RES)) {
			$counter 			= 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
				$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
				$MRF_ACHIVED 							= $SELECT_ROW->MRF_ACHIVED;
				$AGR_ACHIVED 							= $SELECT_ROW->AGR_ACHIVED;
				$MRF_CN 								= $SELECT_ROW->MRF_CN;
				$MRF_DN 								= $SELECT_ROW->MRF_DN;
				$AGR_CN 								= $SELECT_ROW->AGR_CN;
				$AGR_DN 								= $SELECT_ROW->AGR_DN;
				if ($F_TRUE) {
					foreach($arrServiceTypes as $ServiceType=>$ServiceTitle)
					{
						$SELECT_S_SQL 	= "	SELECT
											getServiceAchivedTarget('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,1,".$ServiceType.",0) AS MRF_ACHIVED,
											0 AS AGR_ACHIVED,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",0,0,1,".$ServiceType.",0) AS MRF_CN,
											getServiceCreditDebitNoteAmount('".$StartDate."','".$EndDate."',".$SELECT_ROW->MRF_ID.",1,0,1,".$ServiceType.",0) AS MRF_DN,
											0 AS AGR_CN,
											0 AS AGR_DN";
						$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_S_SQL);
						foreach($SELECT_S_RES as $SELECT_S_ROW)
						{
							$MRF_ACHIVED 	+= $SELECT_S_ROW->MRF_ACHIVED;
							$AGR_ACHIVED 	+= $SELECT_S_ROW->AGR_ACHIVED;
							$MRF_CN 		+= $SELECT_S_ROW->MRF_CN;
							$MRF_DN 		+= $SELECT_S_ROW->MRF_DN;
							$AGR_CN 		+= $SELECT_S_ROW->AGR_CN;
							$AGR_DN 		+= $SELECT_S_ROW->AGR_DN;
						}
					}
				}
				$arrResult[$counter]['MRF_ACHIVED'] 	= $MRF_ACHIVED;
				$arrResult[$counter]['AGR_ACHIVED'] 	= $AGR_ACHIVED;
				$arrResult[$counter]['MRF_CN'] 			= $MRF_CN - $MRF_DN;
				$arrResult[$counter]['AGR_CN'] 			= $AGR_CN - $AGR_DN;
				$MRF_FINAL 								= ($SELECT_ROW->MRF_TARGET - ($MRF_ACHIVED + $MRF_DN)) + $MRF_CN;
				$AGR_FINAL 								= ($SELECT_ROW->AGR_TARGET - ($AGR_ACHIVED + $AGR_DN)) + $AGR_CN;

				if ($MRF_FINAL >= $arrResult[$counter]['MRF_TARGET']) {
					$arrResult[$counter]['MRF_FINAL']  	= $MRF_FINAL;
				} else {
					$arrResult[$counter]['MRF_FINAL']  	= ($MRF_FINAL * -1);
				}
				if ($AGR_FINAL >= $arrResult[$counter]['AGR_TARGET']) {
					$arrResult[$counter]['AGR_FINAL']  	= $AGR_FINAL;
				} else {
					$arrResult[$counter]['AGR_FINAL']  	= ($AGR_FINAL * -1);
				}

				$MRF_TARGET_TOTAL 	+= $SELECT_ROW->MRF_TARGET;
				$AGR_TARGET_TOTAL 	+= $SELECT_ROW->AGR_TARGET;
				$MRF_ACHIVED_TOTAL 	+= $arrResult[$counter]['MRF_ACHIVED'];
				$AGR_ACHIVED_TOTAL 	+= $arrResult[$counter]['AGR_ACHIVED'];
				$MRF_CNDN_TOTAL 	+= $arrResult[$counter]['MRF_CN'];
				$AGR_CNDN_TOTAL 	+= $arrResult[$counter]['AGR_CN'];
				$MRF_FINAL_TOTAL 	+= $MRF_FINAL;
				$AGR_FINAL_TOTAL 	+= $AGR_FINAL;
				$TARGET_GTOTAL 		+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AGR_TARGET);
				$ACHIVED_GTOTAL 	+= ($arrResult[$counter]['MRF_ACHIVED'] + $arrResult[$counter]['AGR_ACHIVED']);
				$CNDN_GTOTAL 		+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AGR_CN']);
				$FINAL_GTOTAL 		+= ($MRF_FINAL + $AGR_FINAL);

				$counter++;
			}
		}

		if ($MRF_FINAL_TOTAL <= $MRF_TARGET_TOTAL) {
			$MRF_FINAL_TOTAL = ($MRF_FINAL_TOTAL * -1);
		}
		if ($AGR_FINAL_TOTAL <= $AGR_TARGET_TOTAL) {
			$AGR_FINAL_TOTAL = ($AGR_FINAL_TOTAL * -1);
		}
		if ($FINAL_GTOTAL <= $TARGET_GTOTAL) {
			$FINAL_GTOTAL = ($FINAL_GTOTAL * -1);
		}

		$arrFinalResult['BY_BASELOCATION'] 	= array("arrResult" 		=> $arrResult,
													"MRF_TARGET_TOTAL" 	=> $MRF_TARGET_TOTAL,
													"AGR_TARGET_TOTAL" 	=> $AGR_TARGET_TOTAL,
													"MRF_ACHIVED_TOTAL" => $MRF_ACHIVED_TOTAL,
													"AGR_ACHIVED_TOTAL" => $AGR_ACHIVED_TOTAL,
													"MRF_CNDN_TOTAL" 	=> $MRF_CNDN_TOTAL,
													"AGR_CNDN_TOTAL" 	=> $AGR_CNDN_TOTAL,
													"MRF_FINAL_TOTAL" 	=> $MRF_FINAL_TOTAL,
													"AGR_FINAL_TOTAL" 	=> $AGR_FINAL_TOTAL,
													"TARGET_GTOTAL"		=> $TARGET_GTOTAL,
													"ACHIVED_GTOTAL" 	=> $ACHIVED_GTOTAL,
													"CNDN_GTOTAL" 		=> $CNDN_GTOTAL,
													"FINAL_GTOTAL" 		=> $FINAL_GTOTAL);

		return view("importcollection.salestarget",	["Page_Title" => "Sales Target Vs Achived","arrFinalResult"=>$arrFinalResult]);
	}

	private function getMRFWiseTargetV2()
	{
		$arrResult 		= array();
		$WhereCond 		= "";
		$StartDate		= date("Y-m-d")." 00:00:00";
		$EndDate		= date("Y-m-t")." 23:59:59";
		$StartDate		= "2021-09-01 00:00:00";
		$EndDate		= "2021-09-30 23:59:59";
		$MONTH 			= date("m",strtotime($StartDate));
		$YEAR 			= date("Y",strtotime($StartDate));
		$arrMRF 		= array();
		$TOTAL_SERVICE  = 0;
		$TOTAL_OTHER  	= 0;
		$MONTH 				= (isset($request['month']) && !empty($request['month'])) ? $request['month'] : date("m");
		$YEAR 				= (isset($request['year']) && !empty($request['year'])) ? $request['year'] : date("Y");
		// $BASE_LOCATION_ID 	= Auth()->user()->base_location;
		// $ADMIN_USER_ID 		= Auth()->user()->adminuserid;
		$StartDate			= "$YEAR-$MONTH-01 00:00:00";
		$EndDate			= date("Y-m-t", strtotime($StartDate))." 23:59:59";
		$SELECT_SQL 	= "SELECT wm_department.department_name as MRF_NAME,
							wm_sales_target_master.bill_from_mrf_target as MRF_TARGET,
							wm_sales_target_master.virtual_mrf_target as AGR_TARGET,
							getAchivedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0) AS MRF_ACHIVED,
							getAchivedTarget('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0) AS AGR_ACHIVED,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,0,0) AS MRF_CN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,0,0) AS MRF_DN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,0,1,0) AS AGR_CN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',wm_sales_target_master.mrf_id,1,1,0) AS AGR_DN
							FROM wm_sales_target_master
							INNER JOIN wm_department ON wm_department.id = wm_sales_target_master.mrf_id
							WHERE wm_sales_target_master.month = $MONTH
							AND wm_sales_target_master.year = $YEAR
							AND wm_department.is_virtual = 0 and wm_department.status = 1 and wm_department.id != 59 and wm_department.base_location_id =  1
							ORDER BY wm_department.department_name ASC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		$DEPARTMENTDATA = array();
		if (!empty($SELECT_RES)) {
			$counter 			= 0;
			$MRF_TARGET_TOTAL 	= 0;
			$AGR_TARGET_TOTAL 	= 0;
			$MRF_ACHIVED_TOTAL 	= 0;
			$AGR_ACHIVED_TOTAL 	= 0;
			$MRF_CNDN_TOTAL 	= 0;
			$AGR_CNDN_TOTAL 	= 0;
			$MRF_FINAL_TOTAL 	= 0;
			$AGR_FINAL_TOTAL 	= 0;
			$TARGET_GTOTAL 		= 0;
			$ACHIVED_GTOTAL 	= 0;
			$CNDN_GTOTAL 		= 0;
			$FINAL_GTOTAL 		= 0;
			foreach($SELECT_RES as $KEY => $SELECT_ROW)
			{

				$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
				$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
				$arrResult[$counter]['MRF_ACHIVED'] 	= $SELECT_ROW->MRF_ACHIVED;
				$arrResult[$counter]['AGR_ACHIVED'] 	= $SELECT_ROW->AGR_ACHIVED;
				$arrResult[$counter]['MRF_CN'] 			= $SELECT_ROW->MRF_CN - $SELECT_ROW->MRF_DN;
				$arrResult[$counter]['AGR_CN'] 			= $SELECT_ROW->AGR_CN - $SELECT_ROW->AGR_DN;
				$MRF_FINAL 								= ($SELECT_ROW->MRF_TARGET - ($SELECT_ROW->MRF_ACHIVED - $SELECT_ROW->MRF_CN)) + $SELECT_ROW->MRF_DN;
				$arrResult[$counter]['MRF_FINAL'] 		= $MRF_FINAL;
				$AGR_FINAL 								= ($SELECT_ROW->AGR_TARGET - ($SELECT_ROW->AGR_ACHIVED - $SELECT_ROW->AGR_CN)) + $SELECT_ROW->AGR_DN;
				$arrResult[$counter]['AGR_FINAL'] 		= $AGR_FINAL;

				// prd($arrResult);
				$MRF_TARGET_TOTAL += $SELECT_ROW->MRF_TARGET;
				$AGR_TARGET_TOTAL += $SELECT_ROW->AGR_TARGET;
				$MRF_ACHIVED_TOTAL += $SELECT_ROW->MRF_ACHIVED;
				$AGR_ACHIVED_TOTAL += $SELECT_ROW->AGR_ACHIVED;
				$MRF_CNDN_TOTAL += $arrResult[$counter]['MRF_CN'];
				$AGR_CNDN_TOTAL += $arrResult[$counter]['AGR_CN'];
				$MRF_FINAL_TOTAL += $MRF_FINAL;
				$AGR_FINAL_TOTAL += $AGR_FINAL;

				$TARGET_GTOTAL 		+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AGR_TARGET);
				$ACHIVED_GTOTAL 	+= ($SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->AGR_ACHIVED);
				$CNDN_GTOTAL 		+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AGR_CN']);
				$FINAL_GTOTAL 		+= ($MRF_FINAL + $AGR_FINAL);

				$MRF_CN_DN 			= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AGR_CN']);

				######## ADDING RESPONSE #######
				$DEPARTMENTDATA[$KEY]['department_name'] 				= $SELECT_ROW->MRF_NAME;
				$DEPARTMENTDATA[$KEY]['id'] 							= 0;
				$DEPARTMENTDATA[$KEY]['bill_from_mrf_target'] 			= $SELECT_ROW->MRF_TARGET;
				$DEPARTMENTDATA[$KEY]['virtual_mrf_target'] 			= $SELECT_ROW->AGR_TARGET;
				$DEPARTMENTDATA[$KEY]['bill_from_mrf_achived_target'] 	= $SELECT_ROW->MRF_ACHIVED;
				$DEPARTMENTDATA[$KEY]['virtual_mrf_achived_target'] 	= $SELECT_ROW->AGR_ACHIVED;
				$DEPARTMENTDATA[$KEY]['credit_bill_from'] 				= $arrResult[$counter]['MRF_CN'];
				$DEPARTMENTDATA[$KEY]['credit_virtual'] 				= $arrResult[$counter]['AGR_CN'];
				$DEPARTMENTDATA[$KEY]['bill_from_deficiate'] 			= $MRF_FINAL;
				$DEPARTMENTDATA[$KEY]['virtual_deficiate'] 				= $AGR_FINAL;
				######## ADDING RESPONSE #######
				$counter++;
			}
		}
		########### SERVICE ###########
		$DEPT_CNT = count($DEPARTMENTDATA);
		$cnt 	  = ($DEPT_CNT > 0) ? $DEPT_CNT : 0;
		$PARA_DATA = DB::select("SELECT para_id,para_value FROM parameter where para_parent_id = 1043");
		if(!empty($PARA_DATA)){
			foreach ($PARA_DATA as  $value) {
				$MRF_LIST 		=  WmDepartment::where(array("is_virtual"=> 0,"status"=>1,"base_location_id"=>1))->pluck("id")->toArray();
				$MRF_STR 		=  (!empty($MRF_LIST)) ? implode(",",$MRF_LIST)  : "0";
				$MONTH 			= "09";
				$YEAR 			= "2021";

				$SERVICE_DATA 	=  WmSalesTargetMaster::select(
				DB::raw("FN_GET_ACHIVED_SERVICE_TARGET('".$MRF_STR."','".$MONTH."','".$YEAR."','".$value->para_id."') AS SERVICE_ACHIVED"),
				DB::raw("FN_GET_CREDIT_SERVICE_TARGET('".$MRF_STR."','".$MONTH."','".$YEAR."','".$value->para_id."') AS SERVICE_CN_DN"))->first();

				$SERVICE_ACHIVED	= (!empty($SERVICE_DATA)) ? $SERVICE_DATA->SERVICE_ACHIVED : 0;
				$SERVICE_CN 		= (!empty($SERVICE_DATA)) ? $SERVICE_DATA->SERVICE_CN_DN : 0;
				$SERVICE_DEFICIATE 	= ($SERVICE_ACHIVED - $SERVICE_CN);
				$MRF_ACHIVED_TOTAL 	+= $SERVICE_ACHIVED;
				$ACHIVED_GTOTAL 	+= $SERVICE_ACHIVED;
				$MRF_CNDN_TOTAL 	+= $SERVICE_CN;
				$CNDN_GTOTAL  		+= $SERVICE_CN;
				$DEPARTMENTDATA[$cnt]['department_name'] 				= $value->para_value;
				$DEPARTMENTDATA[$cnt]['id'] 							= 0;
				$DEPARTMENTDATA[$cnt]['bill_from_mrf_target'] 			= 0;
				$DEPARTMENTDATA[$cnt]['virtual_mrf_target'] 			= 0;
				$DEPARTMENTDATA[$cnt]['bill_from_mrf_achived_target'] 	= $SERVICE_ACHIVED;
				$DEPARTMENTDATA[$cnt]['virtual_mrf_achived_target'] 	= 0;
				$DEPARTMENTDATA[$cnt]['credit_bill_from'] 				= $SERVICE_CN;
				$DEPARTMENTDATA[$cnt]['credit_virtual'] 				= 0;
				$DEPARTMENTDATA[$cnt]['bill_from_deficiate'] 			= $SERVICE_DEFICIATE;
				$DEPARTMENTDATA[$cnt]['virtual_deficiate'] 				= 0;
				$cnt ++;
			}
		}
		########### SERVICE ###########



		$data["DEPARTMENT_WISE"] = 	array(
		"result" 							=> $DEPARTMENTDATA,
		"TOTAL_BILL_TARGET" 				=> $MRF_TARGET_TOTAL,
		"TOTAL_VIRTUAL_TARGET" 				=> $AGR_TARGET_TOTAL,
		"TOTAL_BILL_ACHIVED" 				=> $MRF_ACHIVED_TOTAL,
		"TOTAL_VIRTUAL_ACHIVED" 			=> $AGR_ACHIVED_TOTAL,
		"TOTAL_BILL_CREDIT" 				=> $MRF_CNDN_TOTAL,
		"TOTAL_VIRTUAL_CREDIT" 				=> $AGR_CNDN_TOTAL,
		"TOTAL_BILL_DEFICITE" 				=> $MRF_FINAL_TOTAL,
		"TOTAL_VIRTUAL_DEFICITE" 			=> $AGR_FINAL_TOTAL,
		"GRAND_TOTAL_TARGET"				=> $TARGET_GTOTAL,
		"GRAND_TOTAL_ACHIVED" 				=> $ACHIVED_GTOTAL,
		"GRAND_TOTAL_CREDIT" 				=> $CNDN_GTOTAL,
		"GRAND_TOTAL_SURPLUS_DEFICIAT" 		=> $FINAL_GTOTAL);
		###### DEPARTMENT WISE END CODE #############

		$SELECT_SQL 	= "	SELECT base_location_master.base_location_name as MRF_NAME,
							sum(wm_sales_target_master.bill_from_mrf_target) as MRF_TARGET,
							sum(wm_sales_target_master.virtual_mrf_target) as AGR_TARGET,
							getAchivedTarget('".$StartDate."','".$EndDate."',base_location_master.id,0,1) AS MRF_ACHIVED,
							getAchivedTarget('".$StartDate."','".$EndDate."',base_location_master.id,1,1) AS AGR_ACHIVED,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,0,1) AS MRF_CN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,1,0,1) AS MRF_DN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,0,1,1) AS AGR_CN,
							getCreditDebitNoteAmount('".$StartDate."','".$EndDate."',base_location_master.id,1,1,1) AS AGR_DN
							FROM base_location_master
							INNER JOIN wm_sales_target_master ON base_location_master.id = wm_sales_target_master.base_location_id
							WHERE wm_sales_target_master.month = $MONTH
							AND wm_sales_target_master.year = $YEAR
							GROUP BY base_location_master.id
							ORDER BY MRF_NAME ASC";
		$SELECT_RES 			= DB::connection('master_database')->select($SELECT_SQL);
		$arrResult 				= array();
		$BASE_LOCATION_WISE 	= array();
		if (!empty($SELECT_RES)) {
			$counter 			= 0;
			$MRF_TARGET_TOTAL 	= 0;
			$AGR_TARGET_TOTAL 	= 0;
			$MRF_ACHIVED_TOTAL 	= 0;
			$AGR_ACHIVED_TOTAL 	= 0;
			$MRF_CNDN_TOTAL 	= 0;
			$AGR_CNDN_TOTAL 	= 0;
			$MRF_FINAL_TOTAL 	= 0;
			$AGR_FINAL_TOTAL 	= 0;
			$TARGET_GTOTAL 		= 0;
			$ACHIVED_GTOTAL 	= 0;
			$CNDN_GTOTAL 		= 0;
			$FINAL_GTOTAL 		= 0;
			foreach($SELECT_RES as $KEY => $SELECT_ROW)
			{
				$arrResult[$counter]['MRF_NAME'] 		= $SELECT_ROW->MRF_NAME;
				$arrResult[$counter]['MRF_TARGET'] 		= $SELECT_ROW->MRF_TARGET;
				$arrResult[$counter]['AGR_TARGET'] 		= $SELECT_ROW->AGR_TARGET;
				$arrResult[$counter]['MRF_ACHIVED'] 	= $SELECT_ROW->MRF_ACHIVED;
				$arrResult[$counter]['AGR_ACHIVED'] 	= $SELECT_ROW->AGR_ACHIVED;
				$arrResult[$counter]['MRF_CN'] 			= $SELECT_ROW->MRF_CN - $SELECT_ROW->MRF_DN;
				$arrResult[$counter]['AGR_CN'] 			= $SELECT_ROW->AGR_CN - $SELECT_ROW->AGR_DN;
				$MRF_FINAL 								= ($SELECT_ROW->MRF_TARGET - ($SELECT_ROW->MRF_ACHIVED - $SELECT_ROW->MRF_CN)) + $SELECT_ROW->MRF_DN;
				$arrResult[$counter]['MRF_FINAL'] 		= $MRF_FINAL;
				$AGR_FINAL 								= ($SELECT_ROW->AGR_TARGET - ($SELECT_ROW->AGR_ACHIVED - $SELECT_ROW->AGR_CN)) + $SELECT_ROW->AGR_DN;
				$arrResult[$counter]['AGR_FINAL'] 		= $AGR_FINAL;

				$MRF_TARGET_TOTAL += $SELECT_ROW->MRF_TARGET;
				$AGR_TARGET_TOTAL += $SELECT_ROW->AGR_TARGET;
				$MRF_ACHIVED_TOTAL += $SELECT_ROW->MRF_ACHIVED;
				$AGR_ACHIVED_TOTAL += $SELECT_ROW->AGR_ACHIVED;
				$MRF_CNDN_TOTAL += $arrResult[$counter]['MRF_CN'];
				$AGR_CNDN_TOTAL += $arrResult[$counter]['AGR_CN'];
				$MRF_FINAL_TOTAL += $MRF_FINAL;
				$AGR_FINAL_TOTAL += $AGR_FINAL;

				$TARGET_GTOTAL 		+= ($SELECT_ROW->MRF_TARGET + $SELECT_ROW->AGR_TARGET);
				$ACHIVED_GTOTAL 	+= ($SELECT_ROW->MRF_ACHIVED + $SELECT_ROW->AGR_ACHIVED);
				$CNDN_GTOTAL 		+= ($arrResult[$counter]['MRF_CN'] + $arrResult[$counter]['AGR_CN']);
				$FINAL_GTOTAL 		+= ($MRF_FINAL + $AGR_FINAL);
				####### base location ######
				$BASE_LOCATION_WISE[$KEY]['department_name'] 				= $SELECT_ROW->MRF_NAME;
				$BASE_LOCATION_WISE[$KEY]['id'] 							= 0;
				$BASE_LOCATION_WISE[$KEY]['bill_from_mrf_target'] 			= $SELECT_ROW->MRF_TARGET;
				$BASE_LOCATION_WISE[$KEY]['virtual_mrf_target'] 			= $SELECT_ROW->AGR_TARGET;
				$BASE_LOCATION_WISE[$KEY]['bill_from_mrf_achived_target'] 	= $SELECT_ROW->MRF_ACHIVED;
				$BASE_LOCATION_WISE[$KEY]['virtual_mrf_achived_target'] 	= $SELECT_ROW->AGR_ACHIVED;
				$BASE_LOCATION_WISE[$KEY]['credit_bill_from'] 				= $arrResult[$counter]['MRF_CN'];
				$BASE_LOCATION_WISE[$KEY]['credit_virtual'] 				= $arrResult[$counter]['AGR_CN'];
				$BASE_LOCATION_WISE[$KEY]['bill_from_deficiate'] 			= $MRF_FINAL;
				$BASE_LOCATION_WISE[$KEY]['virtual_deficiate'] 				= $AGR_FINAL;
				####### base location ######
				$counter++;
			}
		}
		$data["BASE_LOCATION_WISE"] = 	array(
			"result" 								=> $BASE_LOCATION_WISE,
			"TOTAL_BILL_TARGET" 					=> $MRF_TARGET_TOTAL,
			"TOTAL_VIRTUAL_TARGET" 					=> $AGR_TARGET_TOTAL,
			"TOTAL_BILL_ACHIVED" 					=> $MRF_ACHIVED_TOTAL,
			"TOTAL_VIRTUAL_ACHIVED" 				=> $AGR_ACHIVED_TOTAL,
			"TOTAL_BILL_CREDIT" 					=> $MRF_CNDN_TOTAL,
			"TOTAL_VIRTUAL_CREDIT" 					=> $AGR_CNDN_TOTAL,
			"TOTAL_BILL_DEFICITE" 					=> $MRF_FINAL_TOTAL,
			"TOTAL_VIRTUAL_DEFICITE" 				=> $AGR_FINAL_TOTAL,
			"BASE_GRAND_TOTAL_TARGET"				=> $TARGET_GTOTAL,
			"BASE_GRAND_TOTAL_ACHIVED" 				=> $ACHIVED_GTOTAL,
			"BASE_GRAND_TOTAL_CREDIT" 				=> $CNDN_GTOTAL,
			"BASE_GRAND_TOTAL_SURPLUS_DEFICIAT" 	=> $FINAL_GTOTAL
		);
		return response()->json(['code'=>200,'msg'=>"",'data'=>$data]);
	}

	public function dprdetails(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			return $this->JamnagarProductionReport();
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function NumberFormat($num)
	{
		// setlocale(LC_MONETARY, 'en_IN');
		// $Number = money_format('%!i', $Number);
		// return $Number;
		$explrestunits 	= "";
		$num 			= intval($num);
		if(strlen($num)>3) {
			$lastthree = substr($num, strlen($num)-3, strlen($num));
			$restunits = substr($num, 0, strlen($num)-3); // extracts the last three digits
			$restunits = (strlen($restunits)%2 == 1)?"0".$restunits:$restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
			$expunit = str_split($restunits, 2);
			for($i=0; $i<sizeof($expunit); $i++) {
				// creates each of the 2's group and adds a comma to the end
				if($i==0) {
					$explrestunits .= (int)$expunit[$i].","; // if is first value , convert into integer
				} else {
					$explrestunits .= $expunit[$i].",";
				}
			}
			$thecash = $explrestunits.$lastthree;
		} else {
			$thecash = $num;
		}
		return $thecash; // writes the final format where $currency is the currency symbol.
	}

	private function ProductionReportDetailsByMRF()
	{
		$arrResult 				= array();
		$arrDates 				= array();
		$arrDatesRSpan 			= array();
		$arrProducts 			= array();
		$WhereCond 				= "";
		$Today 					= date("Y-m-d");
		$PRODUCTION_FROM_DATE	= date("Y-m-d",strtotime("-6 day"))." 00:00:00";
		$PRODUCTION_TO_DATE 	= date("Y-m-d",strtotime("-1 day"))." 23:59:59";
		$MRFID 					= 22;
		$MRF_NAME 				= "INDORE";
		$arrMRF 				= array();
		$PLANT_CAPACITY			= 15000;
		$SELECT_SQL 	= "	SELECT company_product_master.id AS P_ID,
							wm_production_report_master.production_date AS P_DATE,
							CONCAT(company_product_master.name,' ',PQP.parameter_name) AS PRODUCT_NAME,
							SUM(processing_qty) AS Total_Processed_Quantity,
							GROUP_CONCAT(wm_production_report_master.id) AS PR_ID,
							CASE WHEN 1=1 THEN
							(
								SELECT opening_stock
								FROM stock_ladger
								WHERE mrf_id = $MRFID
								AND stock_ladger.product_type = ".PRODUCT_PURCHASE."
								AND stock_ladger.stock_date = wm_production_report_master.production_date
								AND stock_ladger.product_id = company_product_master.id
							) END AS opening_stock,
							CASE WHEN 1=1 THEN
							(
								SELECT inward
								FROM stock_ladger
								WHERE mrf_id = $MRFID
								AND stock_ladger.product_type = ".PRODUCT_PURCHASE."
								AND stock_ladger.stock_date = wm_production_report_master.production_date
								AND stock_ladger.product_id = company_product_master.id
							) END AS TOTAL_INWARD
							FROM wm_production_report_master
							INNER JOIN company_product_master ON company_product_master.id = wm_production_report_master.product_id
							INNER JOIN company_product_quality_parameter AS PQP ON PQP.product_id = company_product_master.id
							WHERE wm_production_report_master.production_date BETWEEN '".$PRODUCTION_FROM_DATE."' AND '".$PRODUCTION_TO_DATE."'
							AND wm_production_report_master.mrf_id = $MRFID
							GROUP BY wm_production_report_master.production_date,company_product_master.id
							ORDER BY wm_production_report_master.production_date DESC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			$counter = 0;
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$INWARD 	= $SELECT_ROW->TOTAL_INWARD;
				IF ($SELECT_ROW->P_DATE == $Today) {
					$STOCK_SQL = "	SELECT SUM(quantity) AS TOTAL_INWARD
									FROM inward_ledger
									WHERE inward_date = '".$SELECT_ROW->P_DATE."'
									AND mrf_id = ".$MRFID."
									AND product_id = ".$SELECT_ROW->P_ID."
									AND product_type = ".PRODUCT_PURCHASE;
					$STOCK_RES 	= DB::connection('master_database')->select($STOCK_SQL);
					$INWARD 	= $STOCK_RES[0]['TOTAL_INWARD'];
				}
				$PR_DETAILS = $this->GetProductionReportDetails($SELECT_ROW->PR_ID,$SELECT_ROW->Total_Processed_Quantity);
				$T_R_PER 	= (($SELECT_ROW->Total_Processed_Quantity > 0 && $PR_DETAILS['T_R_QTY'] > 0)?round((($PR_DETAILS['T_R_QTY'] * 100)/$SELECT_ROW->Total_Processed_Quantity),2):0);
				$arrResult[$SELECT_ROW->P_DATE][$SELECT_ROW->P_ID]	= array(	"P_NAME"=>$SELECT_ROW->PRODUCT_NAME,
																				"P_QTY"=>$this->NumberFormat($SELECT_ROW->Total_Processed_Quantity),
																				"O_STOCK"=>$this->NumberFormat($SELECT_ROW->opening_stock),
																				"INWARD"=>$this->NumberFormat($INWARD),
																				"PR_DETAILS"=>$PR_DETAILS['PRODUCTION_DETAILS'],
																				"T_R_QTY"=>$this->NumberFormat($PR_DETAILS['T_R_QTY']),
																				"T_R_PER"=>$T_R_PER);
				if (!in_array($SELECT_ROW->P_DATE,$arrDates)) array_push($arrDates,$SELECT_ROW->P_DATE);
				if (!in_array($SELECT_ROW->P_ID,$arrProducts)) array_push($arrProducts,$SELECT_ROW->P_ID);
				if (isset($arrDatesRSpan[$SELECT_ROW->P_DATE])) {
					$arrDatesRSpan[$SELECT_ROW->P_DATE] += sizeof($PR_DETAILS['PRODUCTION_DETAILS']);
				} else {
					$arrDatesRSpan[$SELECT_ROW->P_DATE] = sizeof($PR_DETAILS['PRODUCTION_DETAILS']);
				}
			}
		}
		return view("importcollection.productiondetailreport",[	"Page_Title"=>"DPR (By MRF) - $MRF_NAME",
																"arrResult"=>$arrResult,
																"arrDates"=>$arrDates,
																"arrDatesRSpan"=>$arrDatesRSpan,
																"arrProducts"=>$arrProducts,
																"report_type"=>$this->report_type]);
	}

	private function GetProductionReportDetails($PR_ID,$P_QTY)
	{
		$arrResult 		= array('PRODUCTION_DETAILS'=>array(),"T_R_QTY"=>0);
		$SELECT_SQL 	= "	SELECT wm_product_master.id AS P_ID,wm_product_master.inert_flag,
							wm_product_master.title AS PRODUCT_NAME,
							SUM(qty) AS R_QTY
							FROM wm_processed_product_master
							INNER JOIN wm_product_master ON wm_product_master.id = wm_processed_product_master.sales_product_id
							WHERE wm_processed_product_master.production_id IN (".$PR_ID.")
							GROUP BY wm_processed_product_master.sales_product_id
							ORDER BY R_QTY DESC, PRODUCT_NAME ASC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$R_PER 								= (($P_QTY > 0 && $SELECT_ROW->R_QTY > 0)?round((($SELECT_ROW->R_QTY * 100)/$P_QTY),2):0);
				$arrResult['PRODUCTION_DETAILS'][] 	= array("P_ID"=>$SELECT_ROW->P_ID,
															"P_NAME"=>$SELECT_ROW->PRODUCT_NAME,
															"R_QTY"=>$this->NumberFormat($SELECT_ROW->R_QTY),
															"R_PER"=>$R_PER);
				$arrResult['T_R_QTY'] 				+= ($SELECT_ROW->inert_flag == false)?$SELECT_ROW->R_QTY:0;
			}
		}
		return $arrResult;
	}

	public function salesprojectionplan(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			return $this->SalesProjectPlanDetails();
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function SalesProjectPlanDetails()
	{
		$MRF_ID 							= 22;
		$MRF_NAME 							= "INDORE";
		$MONTH 								= 11;
		$YEAR 								= 2021;
		$arrResult[$MRF_ID]["NAME"] 		= "INDORE";
		$arrResult[$MRF_ID]["SALES_PLAN"] 	= $this->GetMRFProjectionPlan($MRF_ID,$MONTH,$YEAR);
		return view("importcollection.projectplanvsactual",["Page_Title"=>"SALES PROJECTION - PLAN Vs ACTUAL","arrResult"=>$arrResult]);
	}

	private function GetMRFProjectionPlan($MRF_ID,$MONTH,$YEAR)
	{
		$arrResult 								= array();
		$arrResult['PROJECTION_PLAN_TOTAL_QTY'] = 0;
		$arrResult['PROJECTION_PLAN_TOTAL_AMT'] = 0;
		$arrResult['PROJECTION_PLAN_SP'] 		= 0;
		$arrResult['ACTUAL_TOTAL_QTY'] 			= 0;
		$arrResult['ACTUAL_TOTAL_AMT'] 			= 0;
		$arrResult['ACTUAL_SP'] 				= 0;
		$arrProducts 							= $this->GetSalesProjection($MRF_ID,$MONTH,$YEAR);
		foreach($arrProducts AS $PRODUCT_ID=>$PROJECT_ROW)
		{
			$arrResult['PRODUCTS'][$PRODUCT_ID] 					= $PROJECT_ROW;
			$ACTUAL_SALES_DETAILS									= $this->GetActualProductSalesByMRF($MRF_ID,$PRODUCT_ID,$MONTH,$YEAR);

			$arrResult['PRODUCTS'][$PRODUCT_ID]['ACTUAL']			= $ACTUAL_SALES_DETAILS['ACTUAL_SALES_DETAILS'];
			$arrResult['PRODUCTS'][$PRODUCT_ID]['ACTUAL_TOTAL_QTY']	= $ACTUAL_SALES_DETAILS['ACTUAL_TOTAL_QTY'];
			$arrResult['PRODUCTS'][$PRODUCT_ID]['ACTUAL_TOTAL_AMT']	= $ACTUAL_SALES_DETAILS['ACTUAL_TOTAL_AMT'];
			$PROJECTION_PLAN_ROWS									= sizeof($arrResult['PRODUCTS'][$PRODUCT_ID]['PROJECTION_PLAN']);
			$ACTUAL_SALES_ROWS										= sizeof($arrResult['PRODUCTS'][$PRODUCT_ID]['ACTUAL']);
			$arrResult['PRODUCTS'][$PRODUCT_ID]["ROW_SPAN"]			= ($PROJECTION_PLAN_ROWS > $ACTUAL_SALES_ROWS)?$PROJECTION_PLAN_ROWS:$ACTUAL_SALES_ROWS;

			if ($PROJECTION_PLAN_ROWS > $ACTUAL_SALES_ROWS)	{
				$RemainingRows = $PROJECTION_PLAN_ROWS - $ACTUAL_SALES_ROWS;
				for($i=1;$i<=$RemainingRows;$i++) {
					$arrResult['PRODUCTS'][$PRODUCT_ID]['ACTUAL'][] = array("Name"=>"","Qty"=>"","Rate"=>"","Total_Amount"=>"","Remark"=>"");
				}
			} else if ($ACTUAL_SALES_ROWS > $PROJECTION_PLAN_ROWS)	{
				$RemainingRows = $ACTUAL_SALES_ROWS - $PROJECTION_PLAN_ROWS;
				for($i=1;$i<=$RemainingRows;$i++) {
					$arrResult['PRODUCTS'][$PRODUCT_ID]['PROJECTION_PLAN'][] = array("Name"=>"","Qty"=>"","Rate"=>"","Total_Amount"=>"","Remark"=>"");
				}
			}
			$arrResult['PROJECTION_PLAN_TOTAL_QTY']					+= $arrResult['PRODUCTS'][$PRODUCT_ID]['TOTAL_PROJECTION_QTY'];
			$arrResult['PROJECTION_PLAN_TOTAL_AMT']					+= $arrResult['PRODUCTS'][$PRODUCT_ID]['TOTAL_PROJECTION_AMT'];
			$arrResult['ACTUAL_TOTAL_QTY']							+= $ACTUAL_SALES_DETAILS['ACTUAL_TOTAL_QTY'];
			$arrResult['ACTUAL_TOTAL_AMT']							+= $ACTUAL_SALES_DETAILS['ACTUAL_TOTAL_AMT'];
		}
		$arrResult['PROJECTION_PLAN_SP'] 	= round(($arrResult['PROJECTION_PLAN_TOTAL_AMT'] / $arrResult['PROJECTION_PLAN_TOTAL_QTY']),2);
		$arrResult['ACTUAL_SP'] 			= round(($arrResult['ACTUAL_TOTAL_AMT'] / $arrResult['ACTUAL_TOTAL_QTY']),2);
		return $arrResult;
	}

	private function GetSalesProjection($MRF_ID,$MONTH,$YEAR)
	{
		$arrResult 										= array();
		$PRODUCT_ID 									= 66;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Waste Paper Board - Used Bottom (Mix Board)";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 420000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"Camerich","Qty"=>70000,"Rate"=>12.5,"Total_Amount"=>875000,"Remark"=>"Rs.2.5 logistics");
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"Anant Resources","Qty"=>120000,"Rate"=>10,"Total_Amount"=>1200000,"Remark"=>"Rs.2 logistics");
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"Kohinoor","Qty"=>150000,"Rate"=>10,"Total_Amount"=>1500000,"Remark"=>"Ex factory, quantum is less 230 mt combined");
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"Bijasan pkg","Qty"=>20000,"Rate"=>10,"Total_Amount"=>200000,"Remark"=>"Ex factory, quantum is less 230 mt combined");
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"amit marketing","Qty"=>60000,"Rate"=>10,"Total_Amount"=>600000,"Remark"=>"Ex factory, quantum is less 230 mt combined");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 420000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 4375000;

		$PRODUCT_ID 									= 68;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Waste Paper - Kraft Paper / Cardboard";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 42000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"Amit mkt","Qty"=>32000,"Rate"=>16,"Total_Amount"=>512000,"Remark"=>"Apart from this trial to A'bad planned");
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"Bijasan pkg","Qty"=>10000,"Rate"=>14,"Total_Amount"=>140000,"Remark"=>"Apart from this trial to A'bad planned");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 42000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 652000;

		$PRODUCT_ID 									= 18;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Waste Plastic - LLDPE Film (Milk Pouch Waste)";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 7000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"Lucro","Qty"=>7000,"Rate"=>28,"Total_Amount"=>196000,"Remark"=>"Ex factory");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 7000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 196000;

		$PRODUCT_ID 									= 7;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Waste Plastic - LD / HM Film (Colour & Printed)";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 112000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"Super plastic","Qty"=>60000,"Rate"=>10,"Total_Amount"=>600000,"Remark"=>"Rs.2 logistics");
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"dhoraji other","Qty"=>16000,"Rate"=>9,"Total_Amount"=>144000,"Remark"=>"Rs.4 logistics");
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"sheela / zikra","Qty"=>36000,"Rate"=>8,"Total_Amount"=>288000,"Remark"=>"Ex factory");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 112000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 1032000;

		$PRODUCT_ID 									= 379;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Waste Plastic - LD/HM (Transparent) (Flexible)";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 70000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"Vipul Plastic","Qty"=>15000,"Rate"=>20,"Total_Amount"=>300000,"Remark"=>"Rs.4 logistics");
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"others","Qty"=>55000,"Rate"=>18,"Total_Amount"=>990000,"Remark"=>"Rs.4 logistics");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 70000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 1290000;

		$PRODUCT_ID 									= 166;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Waste Plastic - PVC (Mix)";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 70000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"A'bad Transfer","Qty"=>70000,"Rate"=>16.5,"Total_Amount"=>1155000,"Remark"=>"If 1 tup transfer then 25");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 70000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 1155000;

		$PRODUCT_ID 									= 332;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Waste Metal Scrap - Iron (Tod - Fod)";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 30000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"rajasthan","Qty"=>30000,"Rate"=>20,"Total_Amount"=>600000,"Remark"=>"");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 30000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 600000;

		$PRODUCT_ID 									= 47;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Waste Metal Scrap - Used Aluminium Cans";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 1000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"A'bad Transfer","Qty"=>1000,"Rate"=>30,"Total_Amount"=>130000,"Remark"=>"");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 1000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 130000;

		$PRODUCT_ID 									= 270;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Waste Plastic - (Raffia Patti / Box Strap)";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 140000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"new vendor","Qty"=>100000,"Rate"=>7.5,"Total_Amount"=>750000,"Remark"=>"Ex factory");
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"zikra / sheela","Qty"=>40000,"Rate"=>7.5,"Total_Amount"=>300000,"Remark"=>"Ex factory");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 140000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 1050000;

		$PRODUCT_ID 									= 76;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Waste Plastic - PET (Transparent)";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 22000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"badri","Qty"=>22000,"Rate"=>55,"Total_Amount"=>1210000,"Remark"=>"");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 22000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 1210000;

		$PRODUCT_ID 									= 283;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "USED GLASS WASTE";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 6000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"sheela, zikra","Qty"=>22000,"Rate"=>4,"Total_Amount"=>24000,"Remark"=>"");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 6000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 24000;

		$PRODUCT_ID 									= 99;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Used Shoes Waste (Whole)";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 112000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"zikra","Qty"=>112000,"Rate"=>6.75,"Total_Amount"=>756000,"Remark"=>"");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 112000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 756000;

		$PRODUCT_ID 									= 130;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "PP Disposal";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 17000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"A'bad Transfer","Qty"=>17000,"Rate"=>28,"Total_Amount"=>476000,"Remark"=>"");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 17000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 476000;

		$PRODUCT_ID 									= 345;
		$arrResult[$PRODUCT_ID]['NAME'] 				= "Waste Plastic - HDPE (Mix Colour)";
		$arrResult[$PRODUCT_ID]['PRODUCTION_QTY'] 		= 11000;
		$arrResult[$PRODUCT_ID]['PROJECTION_PLAN'][]	= array("Name"=>"A'bad Transfer","Qty"=>11000,"Rate"=>36.5,"Total_Amount"=>401500,"Remark"=>"");
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_QTY']	= 11000;
		$arrResult[$PRODUCT_ID]['TOTAL_PROJECTION_AMT']	= 401500;

		return $arrResult;
	}

	private function GetActualProductSalesByMRF($MRF_ID,$PRODUCT_ID,$MONTH,$YEAR)
	{
		$StartDate	= $YEAR."-".$MONTH."-01";
		$EndDate	= date("Y-m-t",strtotime($StartDate));
		$arrResult 	= array("ACTUAL_SALES_DETAILS"=>array(),"ACTUAL_TOTAL_QTY"=>0,"ACTUAL_TOTAL_AMT"=>0);
		$SELECT_SQL = "	SELECT wm_client_master.client_name,
						sum(wm_dispatch_product.quantity) AS TOTAL_QTY,
						sum(wm_dispatch_product.net_amount) AS TOTAL_AMOUNT
						FROM wm_dispatch_product
						INNER JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
						INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						WHERE wm_dispatch.dispatch_date BETWEEN '".$StartDate."' AND '".$EndDate."'
						AND wm_dispatch.approval_status = 1
						AND wm_dispatch_product.product_id = ".$PRODUCT_ID."
						AND (wm_dispatch.bill_from_mrf_id = $MRF_ID OR wm_dispatch.master_dept_id = $MRF_ID)
						GROUP BY wm_dispatch.client_master_id";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			foreach($SELECT_RES AS $SELECT_ROW)
			{
				$arrResult['ACTUAL_SALES_DETAILS'][] 	= array("Name"=>$SELECT_ROW->client_name,
																"Qty"=>$SELECT_ROW->TOTAL_QTY,
																"Rate"=>round(($SELECT_ROW->TOTAL_AMOUNT/$SELECT_ROW->TOTAL_QTY),2),
																"Total_Amount"=>$SELECT_ROW->TOTAL_AMOUNT,
																"Remark"=>"");
				$arrResult['ACTUAL_TOTAL_QTY']			+= $SELECT_ROW->TOTAL_QTY;
				$arrResult['ACTUAL_TOTAL_AMT']			+= $SELECT_ROW->TOTAL_AMOUNT;
			}
		}
		return $arrResult;
	}

	private function JamnagarProductionReport()
	{
		$arrResult 		= array();
		$arrDates 		= array();
		$arrDatesRSpan 	= array();
		$arrProducts 	= array();
		$WhereCond 		= "";
		$MRF_NAME 		= "MRF - JAMNAGAR";
		$arrMRF 		= array();
		$SELECT_SQL 	= "	SELECT jam_production_report.pro_date,
							sum(jam_production_report.inward) as inward,
							sum(jam_production_report.aglo) as aglo,
							sum(jam_production_report.granules) as granules,
							sum(jam_production_report.total_pro_qty) as total_pro_qty
							FROM jam_production_report
							WHERE jam_production_report.pro_date BETWEEN '".$this->report_starttime."' AND '".$this->report_endtime."'
							GROUP BY jam_production_report.pro_date
							ORDER BY jam_production_report.pro_date ASC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		$Total_Inward 	= 0;
		$Total_Aglo 	= 0;
		$Total_Granules = 0;
		$Total_Prod_Qty = 0;
		if (!empty($SELECT_RES)) {
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$arrResult[] = array(	"p_date"			=> $SELECT_ROW->pro_date,
										"inward_product"	=> "Mix Dry Waste",
										"inward_qty"		=> $SELECT_ROW->inward,
										"aglo"				=> $SELECT_ROW->aglo,
										"granules"			=> $SELECT_ROW->granules,
										"total_pro_qty"		=> $SELECT_ROW->total_pro_qty);
				$Total_Inward += $SELECT_ROW->inward;
				$Total_Aglo += $SELECT_ROW->aglo;
				$Total_Granules += $SELECT_ROW->granules;
				$Total_Prod_Qty += $SELECT_ROW->total_pro_qty;
			}
		}
		return view("importcollection.jamproductionreportstat",["Page_Title"=>"DPR - $MRF_NAME",
																"arrResult"=>$arrResult,
																"arrDates"=>$arrDates,
																"Total_Inward"=>$Total_Inward,
																"Total_Aglo"=>$Total_Aglo,
																"Total_Granules"=>$Total_Granules,
																"Total_Prod_Qty"=>$Total_Prod_Qty,
																"daterange"=>$this->daterange,
																"report_starttime"=>$this->report_starttime,
																"report_endtime"=>$this->report_endtime]);
	}

	public function getvggsdetails(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			if ($request->isMethod("post")) {
				VGGS::AddCollectionDetails($request);
				return redirect()->route('vggs-2022')->with('message','VGGS 2022 data updated successfully !!!');
			}
			return $this->GetVGGS2022();
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function GetVGGS2022()
	{
		$arrResult 		= VGGS::GetCollectionDetails();
		return view("importcollection.vggs",["Page_Title"=>"Vibrant Gujarat Global Summit 2022",
											"arrResult"=>$arrResult,
											"arrLocation"=>$this->arrLocation]);
	}

	public function hsnwisesalesreport(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			return $this->GetHSNWiseSalesDetails();
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function GetHSNWiseSalesDetails()
	{
		$arrResult 			= array();
		$WhereCond 			= "";
		$SELECT_SQL 		= "	SELECT wm_department.id, wm_department.department_name as MRF_NAME
								FROM wm_department
								WHERE wm_department.is_virtual = 0 AND wm_department.status = 1
								ORDER BY wm_department.department_name ASC";
		$SELECT_RES 		= DB::connection('master_database')->select($SELECT_SQL);
		$GRAND_TOTAL_AMT 	= 0;
		$GRAND_TOTAL_CN 	= 0;
		$GRAND_TOTAL_DN 	= 0;
		$GRAND_TOTAL 		= 0;
		$arrMRF 			= array();
		if (!empty($SELECT_RES))
		{
			foreach($SELECT_RES as $SELECT_ROW)
			{
				$arrMRF[$SELECT_ROW->id] = $SELECT_ROW->MRF_NAME;
				if (!empty($this->mrf_id) && !in_array($SELECT_ROW->id,$this->mrf_id)) continue;
				$SELECT_SQL = "	SELECT wm_product_master.title, wm_product_master.hsn_code,
								CASE WHEN 1=1 THEN
								(
									SELECT SUM(wm_dispatch_product.quantity * wm_dispatch_product.price)
									FROM wm_dispatch_product
									INNER JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
									WHERE wm_dispatch.approval_status = 1
									AND wm_dispatch_product.product_id = wm_product_master.id
									AND wm_dispatch.bill_from_mrf_id = $SELECT_ROW->id
									AND wm_dispatch.dispatch_date BETWEEN '$this->report_starttime' AND '$this->report_endtime'
								) END AS SALES_AMOUNT,
								CASE WHEN 1=1 THEN
								(
									SELECT SUM(revised_gross_amount)
									FROM wm_invoices_credit_debit_notes_details
									INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
									INNER JOIN wm_dispatch_product ON wm_invoices_credit_debit_notes_details.dispatch_product_id = wm_dispatch_product.id
									INNER JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
									WHERE wm_dispatch.approval_status = 1
									AND wm_invoices_credit_debit_notes.notes_type = 0
									AND wm_dispatch_product.product_id = wm_product_master.id
									AND wm_dispatch.bill_from_mrf_id = $SELECT_ROW->id
									AND wm_invoices_credit_debit_notes.status = 3
									AND wm_invoices_credit_debit_notes.change_date BETWEEN '$this->report_starttime' AND '$this->report_endtime'
								) END AS CREDIT_AMT,
								CASE WHEN 1=1 THEN
								(
									SELECT SUM(revised_gross_amount)
									FROM wm_invoices_credit_debit_notes_details
									INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
									INNER JOIN wm_dispatch_product ON wm_invoices_credit_debit_notes_details.dispatch_product_id = wm_dispatch_product.id
									INNER JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
									WHERE wm_dispatch.approval_status = 1
									AND wm_invoices_credit_debit_notes.notes_type = 1
									AND wm_dispatch_product.product_id = wm_product_master.id
									AND wm_dispatch.bill_from_mrf_id = $SELECT_ROW->id
									AND wm_invoices_credit_debit_notes.status = 3
									AND wm_invoices_credit_debit_notes.change_date BETWEEN '$this->report_starttime' AND '$this->report_endtime'
								) END AS DEBIT_AMT
								FROM wm_product_master
								WHERE wm_product_master.status = 1
								HAVING SALES_AMOUNT IS NOT NULL OR CREDIT_AMT IS NOT NULL OR DEBIT_AMT IS NOT NULL
								ORDER BY wm_product_master.title ASC";
				$SELECTRES 	= DB::connection('master_database')->select($SELECT_SQL);
				if (!empty($SELECTRES))
				{
					foreach ($SELECTRES as $SELECTROW)
					{
						$TOTAL_ROW_AMT	= !empty($SELECTROW->SALES_AMOUNT)?$SELECTROW->SALES_AMOUNT:0;
						$TOTAL_ROW_AMT	= !empty($SELECTROW->CREDIT_AMT)?($TOTAL_ROW_AMT-$SELECTROW->CREDIT_AMT):$TOTAL_ROW_AMT;
						$TOTAL_ROW_AMT	= !empty($SELECTROW->DEBIT_AMT)?($TOTAL_ROW_AMT+$SELECTROW->DEBIT_AMT):$TOTAL_ROW_AMT;
						$arrResult[] 	= array("MRF_NAME"=>$SELECT_ROW->MRF_NAME,
												"PRODUCT_NAME"=>$SELECTROW->title,
												"HSN_CODE"=>$SELECTROW->hsn_code,
												"SALES_AMOUNT"=>_FormatNumberV2($SELECTROW->SALES_AMOUNT),
												"CREDIT_AMT"=>_FormatNumberV2($SELECTROW->CREDIT_AMT),
												"DEBIT_AMT"=>_FormatNumberV2($SELECTROW->DEBIT_AMT),
												"TOTAL"=>_FormatNumberV2($TOTAL_ROW_AMT));
						$GRAND_TOTAL_AMT += !empty($SELECTROW->SALES_AMOUNT)?$SELECTROW->SALES_AMOUNT:0;
						$GRAND_TOTAL_CN += !empty($SELECTROW->CREDIT_AMT)?$SELECTROW->CREDIT_AMT:0;
						$GRAND_TOTAL_DN += !empty($SELECTROW->DEBIT_AMT)?$SELECTROW->DEBIT_AMT:0;
						$GRAND_TOTAL += $TOTAL_ROW_AMT;
					}
				}

			}
		}
		return view("importcollection.hsnwisesalesreport",["Page_Title"=>"HSN-WISE SALES SUMMARY REPORT",
															"arrResult"=>$arrResult,
															"arrMRF"=>$arrMRF,
															"MRF_IDS"=>$this->mrf_id,
															"GRAND_TOTAL_AMT"=>$GRAND_TOTAL_AMT,
															"GRAND_TOTAL_CN"=>$GRAND_TOTAL_CN,
															"GRAND_TOTAL_DN"=>$GRAND_TOTAL_DN,
															"GRAND_TOTAL"=>$GRAND_TOTAL,
															"daterange"=>$this->daterange,
															"report_starttime"=>$this->report_starttime,
															"report_endtime"=>$this->report_endtime]);
	}

	public function calculatepricematrix(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			return $this->_CalculatePriceMatrix($request);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function _CalculatePriceMatrix($request)
	{
		$virgin_granules_rate 	= isset($request->virgin_granules_rate)?$request->virgin_granules_rate:0;
		$variance 				= isset($request->variance)?$request->variance:0;
		$g_quantity 			= isset($request->s_3_g_q)?$request->s_3_g_q:0;
		$s_3_p_m 				= isset($request->s_3_p_m)?$request->s_3_p_m:0;
		$s_3_p_c_25k_b 			= isset($request->s_3_p_c_25k_b)?$request->s_3_p_c_25k_b:0;
		$s_3_p_p_kg 			= isset($request->s_3_p_p_kg)?$request->s_3_p_p_kg:0;
		$s_3_p_l_p 				= isset($request->s_3_p_l_p)?$request->s_3_p_l_p:0;
		$s_2_p_m 				= isset($request->s_2_p_m)?$request->s_2_p_m:0;
		$s_2_p_c 				= isset($request->s_2_p_c)?$request->s_2_p_c:0;
		$s_2_w_p_l 				= isset($request->s_2_w_p_l)?$request->s_2_w_p_l:0;
		$s_2_w_c 				= isset($request->s_2_w_c)?$request->s_2_w_c:0;
		$s_1_p_l 				= isset($request->s_1_p_l)?$request->s_1_p_l:0;
		$s_1_p_c 				= isset($request->s_1_p_c)?$request->s_1_p_c:0;
		$s_1_s_c 				= isset($request->s_1_s_c)?$request->s_1_s_c:0;

		$selling_price 			= _FormatNumberV2((!empty($virgin_granules_rate) && !empty($variance)?($virgin_granules_rate - (($virgin_granules_rate * $variance)/100)):0));
		$granules_val 			= _FormatNumberV2($g_quantity * $selling_price);
		$profit_margin_value 	= _FormatNumberV2((!empty($granules_val) && !empty($s_3_p_m)?($granules_val*$s_3_p_m/100):0));
		$s_3_r 					= _FormatNumberV2((!empty($selling_price) && !empty($s_3_p_m)?$selling_price-($selling_price*$s_3_p_m/100):$selling_price));
		$s_3_v 					= _FormatNumberV2($g_quantity * $s_3_r);
		$s_3_p_c 				= _FormatNumberV2(!empty($g_quantity)?(($g_quantity/25)*$s_3_p_c_25k_b):0);
		$s_3_p_kg_p_c 			= _FormatNumberV2(!empty($s_3_p_c_25k_b)?(($s_3_p_c_25k_b/25)):0);
		$s_3_g_p_c 				= _FormatNumberV2(!empty($g_quantity) && !empty($s_3_p_p_kg)?(($g_quantity*$s_3_p_p_kg)):0);
		$s_3_g_p_l 				= _FormatNumberV2(!empty($g_quantity) && !empty($s_3_p_l_p)?($g_quantity+(($g_quantity*$s_3_p_l_p)/100)):$g_quantity);
		$s_3_g_p_l_kg 			= _FormatNumberV2(!empty($s_3_g_p_l) && !empty($s_3_p_l_p)?((($s_3_g_p_l*$s_3_p_l_p)/100)):0);
		$s_3_g_p_l_v 			= _FormatNumberV2($s_3_g_p_l_kg * $s_3_r);
		$s_3_g_p_l_p_kg 		= _FormatNumberV2((!empty($s_3_g_p_l_v) && !empty($g_quantity)?($s_3_g_p_l_v/$g_quantity):0));

		$s_2_s_r_w_g 			= _FormatNumberV2($s_3_r-($s_3_p_kg_p_c+$s_3_p_p_kg+$s_3_g_p_l_p_kg));
		$s_2_w_g_q 				= _FormatNumberV2($s_3_g_p_l);
		$s_2_w_g_v 				= _FormatNumberV2($s_3_v - ($s_3_p_c+$s_3_g_p_c));
		$s_2_w_g_p 				= _FormatNumberV2((!empty($s_2_w_g_v) && !empty($s_2_p_m)?(($s_2_w_g_v*$s_2_p_m)/100):0));
		$s_2_r_b_p 				= _FormatNumberV2((!empty($s_2_s_r_w_g) && !empty($s_2_p_m)?($s_2_s_r_w_g - (($s_2_s_r_w_g * $s_2_p_m)/100)):0));
		$s_2_v_b_p 				= _FormatNumberV2($s_2_w_g_q * $s_2_r_b_p);
		$s_2_p_v 				= _FormatNumberV2(!empty($s_2_w_g_q)?(($s_2_w_g_q/25)*$s_2_p_c):0);;
		$s_2_w_p_l_v 			= _FormatNumberV2(!empty($s_2_w_g_q) && !empty($s_2_w_p_l)?$s_2_w_g_q+(($s_2_w_g_q*$s_2_w_p_l)/100):0);
		$s_2_w_v 				= _FormatNumberV2($s_2_w_p_l_v * $s_2_w_c);
		$s_2_u_w_g_v 			= _FormatNumberV2($s_2_v_b_p - ($s_2_p_v+$s_2_w_v));
		$s_2_u_w_g_r 			= _FormatNumberV2(!empty($s_2_u_w_g_v) && !empty($s_2_w_p_l_v)?($s_2_u_w_g_v/$s_2_w_p_l_v):0);

		$s_1_u_w_g_q 			= _FormatNumberV2($s_2_w_p_l_v);
		$s_1_q_b_p_l 			= _FormatNumberV2(!empty($s_1_u_w_g_q) && !empty($s_1_p_l)?($s_1_u_w_g_q + (($s_1_u_w_g_q*$s_1_p_l)/100)):0);
		$s_1_s_g_c 				= _FormatNumberV2($s_1_q_b_p_l * $s_1_s_c);
		$s_1_i_p_v 				= _FormatNumberV2($s_2_u_w_g_v-$s_1_s_g_c);
		$s_1_i_p_r 				= _FormatNumberV2((!empty($s_1_i_p_v) && !empty($s_1_q_b_p_l)?($s_1_i_p_v/$s_1_q_b_p_l):0));


		return view("importcollection.pricematrix",["Page_Title"=>"PRICE MATRIX",
													"virgin_granules_rate"=>$virgin_granules_rate,
													"variance"=>$variance,
													"s_3_p_m"=>$s_3_p_m,
													"s_3_p_c_25k_b"=>$s_3_p_c_25k_b,
													"s_3_p_p_kg"=>$s_3_p_p_kg,
													"s_3_p_l_p"=>$s_3_p_l_p,
													"s_2_p_m"=>$s_2_p_m,
													"s_2_p_c"=>$s_2_p_c,
													"s_2_w_p_l"=>$s_2_w_p_l,
													"s_2_w_c"=>$s_2_w_c,
													"s_1_p_l"=>$s_1_p_l,
													"s_1_p_c"=>$s_1_p_c,
													"s_1_s_c"=>$s_1_s_c,

													"selling_price"=>$selling_price,
													"granules_qty"=>$g_quantity,
													"s_3_g_q"=>$g_quantity,
													"granules_val"=>$granules_val,
													"profit_margin_value"=>$profit_margin_value,
													"s_3_r"=>$s_3_r,
													"s_3_v"=>$s_3_v,
													"s_3_p_c"=>$s_3_p_c,
													"s_3_p_kg_p_c"=>$s_3_p_kg_p_c,
													"s_3_g_p_c"=>$s_3_g_p_c,
													"s_3_p_p_kg"=>$s_3_p_p_kg,
													"s_3_g_p_l"=>$s_3_g_p_l,
													"s_3_g_p_l_v"=>$s_3_g_p_l_v,
													"s_3_g_p_l_p_kg"=>$s_3_g_p_l_p_kg,

													"s_2_s_r_w_g"=>$s_2_s_r_w_g,
													"s_2_w_g_q"=>$s_2_w_g_q,
													"s_2_w_g_v"=>$s_2_w_g_v,
													"s_2_w_g_p"=>$s_2_w_g_p,
													"s_2_r_b_p"=>$s_2_r_b_p,
													"s_2_v_b_p"=>$s_2_v_b_p,
													"s_2_p_v"=>$s_2_p_v,
													"s_2_w_p_l_v"=>$s_2_w_p_l_v,
													"s_2_w_v"=>$s_2_w_v,
													"s_2_u_w_g_v"=>$s_2_u_w_g_v,
													"s_2_u_w_g_r"=>$s_2_u_w_g_r,

													"s_1_u_w_g_q"=>$s_1_u_w_g_q,
													"s_1_q_b_p_l"=>$s_1_q_b_p_l,
													"s_1_s_g_c"=>$s_1_s_g_c,
													"s_1_i_p_v"=>$s_1_i_p_v,
													"s_1_i_p_r"=>$s_1_i_p_r]);
	}

	public function ccofdetails(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			return $this->_GetCCOFDetails($request);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function _GetCCOFDetails($request)
	{
		$arrMRF 					= array();
		$arrResult 					= array();
		$this->report_starttime 	= "2022-07-01 ".GLOBAL_START_TIME;
		$this->report_endtime 		= date("Y-m-t",strtotime($this->report_starttime))." ".GLOBAL_END_TIME;
		$arrLocations 				= array();
		if (!empty($this->location_id)) {
			$arrCCOFLocationMaster = CCOFLocations::whereIn("id",$this->location_id)->where("status",1)->get();
			if (!empty($arrCCOFLocationMaster)) {
				foreach ($arrCCOFLocationMaster as $arrResult) {
					if (!empty($arrResult->baselocation_id)) array_push($this->basestation_id,$arrResult->baselocation_id);
					if (!empty($arrResult->mrf_ids)) array_push($this->mrf_id,$arrResult->mrf_ids);
					if (!empty($arrResult->nca_user_location)) {
						$TempArray = explode(",",$arrResult->nca_user_location);
						foreach ($TempArray as $nca_user_location) {
							array_push($arrLocations,$nca_user_location);
						}
					}
					if (!empty($arrResult->nca_company_master_id)) {
						$TempArray = explode(",",$arrResult->nca_company_master_id);
						foreach ($TempArray as $nca_company_master_id) {
							array_push($this->company_id,$nca_company_master_id);
						}
					}
				}
			}
		}
		$TopSuppliers				= CCOF::getTopSuppliers($this->report_starttime,$this->report_endtime,$this->basestation_id);
		$TopClients					= CCOF::getTopClients($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$InwardMaterialComposition	= CCOF::InwardMaterialComposition($this->report_starttime,$this->report_endtime,$this->basestation_id);
		$OutwardMaterialComposition	= CCOF::OutwardMaterialComposition($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalMaterialProcessed		= CCOF::TotalMaterialProcessed($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalMaterialOutwardR		= CCOF::TotalMaterialOutward($this->report_starttime,$this->report_endtime,RECYCLEBLE_TYPE,$this->mrf_id);
		$TotalMaterialOutwardNR		= CCOF::TotalMaterialOutward($this->report_starttime,$this->report_endtime,NON_RECYCLEBLE_TYPE,$this->mrf_id);
		$TotalInertMaterial			= CCOF::TotalInertMaterial($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalRDFMaterial			= CCOF::TotalRDFMaterial($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalInwardMaterialCost	= CCOF::TotalInwardMaterialCost($this->report_starttime,$this->report_endtime,$this->basestation_id);
		$TotalSalesRevenueDetails	= CCOF::TotalSalesRevenueDetails($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalServicesRevenueDetails= CCOF::TotalServicesRevenueDetails($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$GetRetentionRatio 			= CCOF::GetRetentionRatio($this->report_starttime,$this->report_endtime,$arrLocations,$this->company_id);
		$arrCCOFLocations 			= CCOFLocations::where("status",1)->pluck("location_title","id");
		$CCOF 								= new CCOF();
		$CCOF->TotalManpowerInformation 	= new \stdClass();
		$CCOF->ExpensesAndRevnue 			= new \stdClass();
		$CCOF->Compliance 					= new \stdClass();
		$CCOF->Grievance_Matrix 			= new \stdClass();
		$CCOF->Employment_Summary 			= new \stdClass();
		$CCOF->TotalManpowerInformation($this->report_starttime,$this->report_endtime,$arrLocations,$this->location_id,$this->company_id);
		$CCOF->GetEmploymentSummary($this->report_starttime,$this->report_endtime,$arrLocations,$this->company_id);
		return view("importcollection.ccofdata",[	"Page_Title"=>"CCOF REPORT",
													"arrResult"=>$arrResult,
													"arrCCOFLocations"=>$arrCCOFLocations,
													"LocationIDS"=>$this->location_id,
													"arrWTypes"=>$CCOF->arrWTypes,
													"arrGender"=>$CCOF->arrGender,
													"arrWorkers"=>$CCOF->arrWorkers,
													"arrComplianceData"=>$CCOF->arrComplianceData,
													"arrGrievanceMatrix"=>$CCOF->arrGrievanceMatrix,
													"arrEmploymentSummary"=>$CCOF->arrEmploymentSummary,
													"TopSuppliers"=>$TopSuppliers,
													"TopClients"=>$TopClients,
													"InwardMaterialComposition"=>$InwardMaterialComposition,
													"OutwardMaterialComposition"=>$OutwardMaterialComposition,
													"TotalMaterialProcessed"=>$TotalMaterialProcessed,
													"TotalMaterialOutwardR"=>$TotalMaterialOutwardR,
													"TotalMaterialOutwardNR"=>$TotalMaterialOutwardNR,
													"TotalInertMaterial"=>$TotalInertMaterial,
													"TotalRDFMaterial"=>$TotalRDFMaterial,
													"TotalInwardMaterialCost"=>$TotalInwardMaterialCost,
													"TotalSalesRevenueDetails"=>$TotalSalesRevenueDetails,
													"TotalServicesRevenueDetails"=>$TotalServicesRevenueDetails,
													"TotalManpowerInformation"=>$CCOF->TotalManpowerInformation,
													"ExpensesAndRevnue"=>$CCOF->ExpensesAndRevnue,
													"Compliance"=>$CCOF->Compliance,
													"Grievance_Matrix"=>$CCOF->Grievance_Matrix,
													"GetRetentionRatio"=>$GetRetentionRatio,
													"Employment_Summary"=>$CCOF->Employment_Summary,
													"MRF_IDS"=>$this->mrf_id,
													"daterange"=>$this->daterange,
													"report_starttime"=>$this->report_starttime,
													"report_endtime"=>$this->report_endtime]);
	}

	public function saveCCOFData(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], $this->IP_ADDRESS))
		{
			$CCOFMaster 	= new CCOFMaster();
			$location_id 	= isset($request->selected_location)?$request->selected_location:0;
			$MONTH 			= isset($request->selected_month)?$request->selected_month:0;
			$YEAR 			= isset($request->selected_year)?$request->selected_year:0;
			$HDNACTION 		= isset($request->hdnaction)?$request->hdnaction:"";
			if ($request->isMethod("post") && $HDNACTION == "save-data" && !empty($location_id) && !empty($MONTH) && !empty($YEAR)) {
				$CCOFMaster->saveRecord($request);
				return redirect()->route('save-ccof-data')->with('message','CCOF data updated successfully !!!');
			} else {
				$LastRecordData = $CCOFMaster->getCCOFDetails($location_id,$MONTH,$YEAR);
			}
			$arrCCOFLocationMaster 	= CCOFLocations::where("status",1)->get();
			$arrMonths 				= CCOFMaster::getMonths();
			$arrYears 				= CCOFMaster::getYears();
			return view("importcollection.ccofdetails",["LastRecordData"=>$LastRecordData,
														"arrFields"=>$CCOFMaster->arrFields,
														"arrCCOFLocations"=>$arrCCOFLocationMaster,
														"selected_location"=>$location_id,
														"selected_month"=>$MONTH,
														"selected_year"=>$YEAR,
														"arrMonths"=>$arrMonths,
														"arrYears"=>$arrYears]);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	public function getDispatchPredictionWidget(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			return $this->_getDispatchPredictionWidget($request);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function _getDispatchPredictionWidget($request)
	{
		$arrResult 				= SalesPlanPrediction::getDispatchPredictionWidget($request);
		$arrMissedDispatches 	= MissedSaledBasedOnPrediction::getMissedDispatchBasedonPredictionWidget($request);
		$arrMRF 				= WmDepartment::where("display_in_unload",1)->where("status",1)->pluck("department_name","id");
		return view("importcollection.salesprediction",["Page_Title"=>"SALES PREDICTION REPORT",
														"Page_Title_Missed"=>"MISSED DISPATCHES BASED ON PREDICTION PLAN",
														"arrResult"=>$arrResult,
														"arrMissedDispatches"=>$arrMissedDispatches,
														"arrMRF"=>$arrMRF,
														"MRF_IDS"=>$this->mrf_id,
														"daterange"=>$this->daterange,
														"report_starttime"=>$this->report_starttime,
														"report_endtime"=>$this->report_endtime]);
	}

	public function getIOTDashboard(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			return $this->_getIOTDashboard($request);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function _getIOTDashboard($request)
	{
		$IOTEquipmentReading 	= new IOTEquipmentReading;
		$MRF_ID 				= (isset($request->mrf_id)?$request->mrf_id:11);
		$arrDevices 			= IOTEquipments::where('status',1)->where("mrf_id",$MRF_ID)->pluck("title","slave_id");
		$arrParameter 			= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("title","code");
		$arrParameterUOM 		= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("uom","code");

		$arrRunHHourData 					= array('ChartData'=>"","TabularData"=>"");
		$arrParameterDailyAVG 				= array('ChartData'=>"","TabularData"=>"");
		$arrParameterAMPReading 			= array('ChartData'=>"","TabularData"=>"");
		$arrParameterTimeAnalysisReading 	= array('ChartData'=>"","TabularData"=>"");
		$arrPowerFactorData 				= array('ChartData'=>"","TabularData"=>"");

		$this->report_starttime	= empty($this->report_starttime)?date("now"):$this->report_starttime;
		$this->report_endtime	= empty($this->report_endtime)?date("now"):$this->report_endtime;
		$DeviceTitle 			= isset($arrDevices[$request->slave_id])?$arrDevices[$request->slave_id]:"-";
		$arrRunHHourData 		= $IOTEquipmentReading->getKGPerHourReadingV1($MRF_ID,$request->slave_id,$this->report_starttime,$this->report_endtime,$DeviceTitle,1);
		$arrKGPerkWH 			= $IOTEquipmentReading->getKGPerkWHV2($MRF_ID,$this->report_starttime,$this->report_endtime);
		$arrPowerFactorData 	= $IOTEquipmentReading->getPowerFactorChartV2($MRF_ID,$this->report_starttime,$this->report_endtime);

		$YesterdayPowerConsumption 	= $IOTEquipmentReading->getYesterdayPowerConsumption($MRF_ID);
		$YesterdayPowerFactor 		= $IOTEquipmentReading->getYesterdayPowerFactor($MRF_ID);
		if (isset($request->device_code) && !empty($request->device_code)) {
			$this->report_starttime	= empty($this->report_starttime)?date("now"):$this->report_starttime;
			$this->report_endtime	= empty($this->report_endtime)?date("now"):$this->report_endtime;
			$ParameterTitle 		= isset($arrParameter[$request->device_code])?$arrParameter[$request->device_code]:"-";
			$arrParameterDailyAVG 	= $IOTEquipmentReading->getEquipmentAvgReading($MRF_ID,$request->device_code,$this->report_starttime,$this->report_endtime,$ParameterTitle);
		}
		if (isset($request->amp_slave_id) && !empty($request->amp_slave_id)) {
			$DeviceTitle 						= isset($arrDevices[$request->amp_slave_id])?$arrDevices[$request->amp_slave_id]:"-";
			$arrParameterTimeAnalysisReading 	= $IOTEquipmentReading->getEquipmentTimeAnalysisReading($MRF_ID,$request->amp_slave_id,$this->reportdate,$DeviceTitle);
			$arrParameterAMPReading 			= $IOTEquipmentReading->getEquipmentAmpReading($MRF_ID,$request->amp_slave_id,$this->reportdate,$DeviceTitle);
		}
		return view("importcollection.iotdashboard",["Page_Title"=>"IOT DASHBOARD",
													"YesterdayPowerConsumption"=>$YesterdayPowerConsumption,
													"YesterdayPowerFactor"=>$YesterdayPowerFactor,
													"arrRunHHourData"=>$arrRunHHourData,
													"arrParameterDailyAVG"=>$arrParameterDailyAVG,
													"arrParameterTimeAnalysisReading"=>$arrParameterTimeAnalysisReading,
													"arrParameterAMPReading"=>$arrParameterAMPReading,
													"arrPowerFactorData"=>$arrPowerFactorData,
													"arrKGPerkWH"=>$arrKGPerkWH,
													"arrDevices"=>$arrDevices,
													"arrParameter"=>$arrParameter,
													"slave_id"=>$this->slave_id,
													"amp_slave_id"=>$this->amp_slave_id,
													"device_code"=>$this->device_code,
													"daterange"=>$this->daterange,
													"reportdate"=>$this->reportdate,
													"report_date"=>date("m/d/Y",strtotime($this->reportdate)),
													"report_starttime"=>$this->report_starttime,
													"report_endtime"=>$this->report_endtime]);
	}
	/*
	Author : Arun
	Use : CCOF Details
	Date : 30 June 2022
	*/
	public function ccofdetailsApi(Request $request)
	{
		$arrMRF 					= array();
		$arrResult 					= array();
		$this->report_starttime 	= !empty($request->report_starttime)?$request->report_starttime:"2022-03-01 ".GLOBAL_START_TIME;
		$this->report_endtime 		= !empty($request->report_endtime)?$request->report_endtime:date("Y-m-t",strtotime($this->report_starttime))." ".GLOBAL_END_TIME;
		$arrLocations 				= array();
		$location_id 	= isset($request->locations)?$request->locations:0;
		if (!empty($location_id)) {
			$arrCCOFLocationMaster = CCOFLocations::where("id",$location_id)->where("status",1)->get();

			if (!empty($arrCCOFLocationMaster)) {
				foreach ($arrCCOFLocationMaster as $arrResult) {
					if (!empty($arrResult->baselocation_id)) array_push($this->basestation_id,$arrResult->baselocation_id);
					if (!empty($arrResult->mrf_ids)) array_push($this->mrf_id,$arrResult->mrf_ids);
					if (!empty($arrResult->nca_user_location)) array_push($arrLocations,$arrResult->nca_user_location);
				}
			}
		}
		$TopSuppliers				= CCOF::getTopSuppliers($this->report_starttime,$this->report_endtime,$this->basestation_id);
		$TopClients					= CCOF::getTopClients($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$InwardMaterialComposition	= CCOF::InwardMaterialComposition($this->report_starttime,$this->report_endtime,$this->basestation_id);
		$OutwardMaterialComposition	= CCOF::OutwardMaterialComposition($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalMaterialProcessed		= CCOF::TotalMaterialProcessed($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalMaterialOutwardR		= CCOF::TotalMaterialOutward($this->report_starttime,$this->report_endtime,RECYCLEBLE_TYPE,$this->mrf_id);
		$TotalMaterialOutwardNR		= CCOF::TotalMaterialOutward($this->report_starttime,$this->report_endtime,NON_RECYCLEBLE_TYPE,$this->mrf_id);
		$TotalInertMaterial			= CCOF::TotalInertMaterial($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalRDFMaterial			= CCOF::TotalRDFMaterial($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalInwardMaterialCost	= CCOF::TotalInwardMaterialCost($this->report_starttime,$this->report_endtime,$this->basestation_id);
		$TotalSalesRevenueDetails	= CCOF::TotalSalesRevenueDetails($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalServicesRevenueDetails= CCOF::TotalServicesRevenueDetails($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$GetRetentionRatio 			= CCOF::GetRetentionRatio($this->report_starttime,$this->report_endtime,$arrLocations);
		$arrCCOFLocations 			= CCOFLocations::where("status",1)->pluck("location_title","id");
		$CCOF 								= new CCOF();
		$CCOF->TotalManpowerInformation 	= new \stdClass();
		$CCOF->ExpensesAndRevnue 			= new \stdClass();
		$CCOF->Compliance 					= new \stdClass();
		$CCOF->Grievance_Matrix 			= new \stdClass();
		$CCOF->Employment_Summary 			= new \stdClass();
		$CCOF->TotalManpowerInformation($this->report_starttime,$this->report_endtime,$arrLocations,$this->location_id);
		$CCOF->GetEmploymentSummary($this->report_starttime,$this->report_endtime,$arrLocations);

		$operations = [];
		$operating_financials = [];
		$employment_hr = [];

		$operations['TopSuppliers']	= $TopSuppliers;
		$operations['TopClients']	= $TopClients;
		$operations['InwardMaterialComposition']	= $InwardMaterialComposition;
		$operations['OutwardMaterialComposition']	= $OutwardMaterialComposition;
		$operations['OperationUtilities'] 	= array(
			array('text' => 'Total Material Processed (in MT)','value'=> $TotalMaterialProcessed),
			array('text' => 'Outward Material (Recyclable Material) (in MT)','value'=> $TotalMaterialOutwardR),
			array('text' => 'Residual Inert (in MT)','value'=> $TotalInertMaterial),
			array('text' => 'RDF (in MT)','value'=> $TotalRDFMaterial),
			array('text' => 'Electricity Consumed (Units)','value'=> $CCOF->ExpensesAndRevnue->Electricity_Consumed)
		);
		$operations['Impact_data_operations'] 	= array(
			array('Particulars' => 'Male Waste Pickers','Details'=> $CCOF->ExpensesAndRevnue->Male_Waste_Pickers),
			array('Particulars' => 'Female Waste Pickers','Details'=> $CCOF->ExpensesAndRevnue->Female_Waste_Pickers),
			array('Particulars' => 'Customers','Details'=> $CCOF->ExpensesAndRevnue->Customers),
			array('Particulars' => 'New customers', 'Details'=> $CCOF->ExpensesAndRevnue->New_Customers),
			array('Particulars' => 'Diesel Consumption','Details'=> $CCOF->ExpensesAndRevnue->Diesel_Consumption)
		);

		$Total_other_Revenue = number_format(($TotalSalesRevenueDetails->Total_Revenue + $TotalServicesRevenueDetails->Total_Revenue + $CCOF->ExpensesAndRevnue->Other_Revenue),2);
		$operating_financials['Revenue'] = array(
			array('name' => 'Sales from materials',
				'total_Revenue' => $TotalSalesRevenueDetails->Total_Revenue,
				'total_Tonne' => $TotalSalesRevenueDetails->Total_Tonne,
				'per_Tonne_Revenue' => $TotalSalesRevenueDetails->Per_Tonne_Revenue
			),
			array('name' => 'Long-term Service Contracts',
				'total_Revenue' => $TotalServicesRevenueDetails->Total_Revenue
			),
			array('name' => 'Other revenue',
				'total_Revenue' => $CCOF->ExpensesAndRevnue->Other_Revenue
			),
			array('name' => 'Total Revenue (INR in Mn)',
				'total_Revenue' => $Total_other_Revenue
			)
		);

		$Total_Direct_Cost = 0;
		foreach($CCOF->ExpensesAndRevnue as $CostHead=>$CostAmount) {
			if ($CostHead != "Other_Revenue" &&
				$CostHead != "Customers" &&
				$CostHead != "New_Customers" &&
				$CostHead != "Electricity_Consumed" &&
				$CostHead != "Diesel_Consumption" &&
				$CostHead != "Male_Waste_Pickers" &&
				$CostHead != "Female_Waste_Pickers") {
				$Total_Direct_Cost += $CostAmount;
			}
		}

		$operating_financials['Cost'] = array(
			'Material' =>
				array(
					array('name' => 'Inward',
						'total_Cost' => $TotalInwardMaterialCost->Total_Cost,
						'total_MT' => $TotalInwardMaterialCost->Weight_In_MT,
						'per_MT' => $TotalInwardMaterialCost->Price_Per_MT
					)
				),
			'Labour' =>
				array(
					array(
						'name' => 'Labour',
						'total_Cost' => $CCOF->ExpensesAndRevnue->Amount_Paid_To_Labour
					),
					array(
						'name' => 'Overtime Paid',
						'total_Cost' => $CCOF->ExpensesAndRevnue->Overtime_Paid
					),
					array(
						'name' => 'Benefits',
						'total_Cost' => $CCOF->ExpensesAndRevnue->Benefits_Paid
					)
				),
			'Operations' =>
				array(
					array(
						'name' => 'Utilities',
						'total_Cost' => $CCOF->ExpensesAndRevnue->Utilities
					),
					array(
						'name' => 'Maintenance & repairs',
						'total_Cost' => $CCOF->ExpensesAndRevnue->Maintenance_Repairs
					),
					array(
						'name' => 'Other Direct Exp',
						'total_Cost' => $CCOF->ExpensesAndRevnue->Other_Direct_Exp
					),
					array(
						'name' => 'Transportation',
						'total_Cost' => $CCOF->ExpensesAndRevnue->Transportation
					)
				),
			'Others' =>
				array(
					array(
						'name' => 'SG&A',
						'total_Cost' => $CCOF->ExpensesAndRevnue->SGA
					),
					array(
						'name' => 'Insurance',
						'total_Cost' => $CCOF->ExpensesAndRevnue->Insurance
					),
					array(
						'name' => 'Total Direct Cost (INR in Mn)',
						'total_Cost' => $Total_Direct_Cost
					)
				)
		);

		$workers_detail = [];
		foreach($CCOF->arrWorkers as $FieldType=>$FieldTitle){

			$workers_data = [];
			$workers_data['title'] = $FieldTitle;

			switch($FieldType){
				case 'AVG_TENURE':
				case 'TOTAL_WORKERS':
				case 'TOTAL_WORKERS_EX_NH':
				case 'TOTAL_NEW_WORKERS':
				case 'TOTAL_WORKERS_BENIFITS_PAID':
					foreach($CCOF->arrWTypes as $WTitle=>$WType){
								$FieldName 	= $WType."_".$FieldType;
								$FieldValue = (isset($CCOF->TotalManpowerInformation->$FieldName)?$CCOF->TotalManpowerInformation->$FieldName:0);
								$workers_data[$WType] = array('Male' => '0','Female' => '0','Common' => true,"Common_value" => $FieldValue);
					}
					break;
				default:
					foreach($CCOF->arrWTypes as $WTitle=>$WType){
						foreach($CCOF->arrGender as $Gender){
							$FieldName 	= $WType."_".$FieldType."_".$Gender;
							$FieldValue = (isset($CCOF->TotalManpowerInformation->$FieldName)?$CCOF->TotalManpowerInformation->$FieldName:0);
							$workers_data[$WType][$Gender] = $FieldValue;
						}
						$workers_data[$WType]['Common'] = false;
						$workers_data[$WType]['Common_value'] = '0';
					}
			}
			$workers_detail[] = $workers_data;
		}

		$employment_hr['staff_workers_detail'] = $workers_detail;

		$employment_hr['retention_rate'] = array(
			array(
				'Title' => 'staff',
				'Women' => $GetRetentionRatio->STAFF_F_RETENTION,
				'Man' => $GetRetentionRatio->STAFF_M_RETENTION
			),
			array(
				'Title' => 'Workers',
				'Women' => $GetRetentionRatio->WORKER_F_RETENTION,
				'Man' => $GetRetentionRatio->WORKER_M_RETENTION
			)
		);

		$employment_hr['statutory_compliance'] = [];
		if(!empty($CCOF->arrComplianceData)){
			foreach($CCOF->arrComplianceData as $Field=>$FieldTitle){

				$employment_hr['statutory_compliance'][] = array(
					'Particulars' => str_replace("_"," ",$FieldTitle),
					'Date_of_payment' => (isset($Compliance->$Field)?$Compliance->$Field:"-")
				);
			}
		}

		$employment_hr['employment_summary'] = [];
		if(!empty($CCOF->arrEmploymentSummary)){
			foreach($CCOF->arrEmploymentSummary as $Field=>$FieldTitle){

				$employment_hr['employment_summary'][] = array(
					'title' => str_replace("_"," ",$FieldTitle),
					'value' => (isset($CCOF->Employment_Summary->$Field)?$CCOF->Employment_Summary->$Field:0)
				);
			}
		}

		$employment_hr['grievance_matrix'] = [];
		if(!empty($CCOF->arrGrievanceMatrix)){
			foreach($CCOF->arrGrievanceMatrix as $Field=>$FieldTitle){

				$employment_hr['grievance_matrix'][] = array(
					'title' => str_replace("_"," ",$FieldTitle),
					'value' => (isset($CCOF->Grievance_Matrix->$Field)?$CCOF->Grievance_Matrix->$Field:0)
				);
			}
		}

		$result = array('operations' => $operations, 'operating_financials' => $operating_financials, 'employment' => $employment_hr);

		return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $result]);
	}

	public function getRDFDashboard(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			return $this->_getRDFDashboard($request);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function _getRDFDashboard($request)
	{
		$NoOfDays 		= 3;
		$Yesterday 		= date("Y-m-d",strtotime("-$NoOfDays Day"));
		$TODAY 			= date("Y-m-d");
		$ScheduleDate	= date("Y-m-d",strtotime("+$NoOfDays Days"));
		$SELECT_SQL 	= "	SELECT TRIM(REPLACE(REPLACE(REPLACE(wm_department.department_name,'MRF-',''),'MRF -',''),'MRF -','')) as Source,
							wm_client_master.client_name as Destination,
							shipping_address_master.shipping_address as Shipping_Address,
							wm_client_master_po_details.daily_dispatch_qty as DailyDispatchQty,
							vehicle_type_master.vehicle_type as TypeOfVehicle,
							vehicle_type_loading_capacity.min_load_allowed as Min_Capacity,
							vehicle_type_loading_capacity.max_load_allowed as Max_Capacity,
							wm_client_master_po_details.transportation_cost,
							wm_client_master_po_details.start_date,
							wm_client_master_po_details.end_date,
							wm_product_master.title as Product_Name,
							wm_client_master_po_details.wm_product_id,
							wm_client_master_po_details.wm_client_id,
							wm_client_master_po_details.mrf_id,
							IF (wm_client_master_po_details.stop_dispatch = 1, wm_client_master_po_details.stop_dispatch_reason,'') Stop_Reason
							FROM wm_client_master_po_details
							INNER JOIN wm_product_master ON wm_product_master.id = wm_client_master_po_details.wm_product_id
							INNER JOIN wm_client_master ON wm_client_master.id = wm_client_master_po_details.wm_client_id
							LEFT JOIN shipping_address_master ON shipping_address_master.id = wm_client_master_po_details.wm_client_shipping_id
							INNER JOIN wm_department ON wm_department.id = wm_client_master_po_details.mrf_id
							LEFT JOIN vehicle_type_master ON vehicle_type_master.id = wm_client_master_po_details.vehicle_type_id
							LEFT JOIN vehicle_type_loading_capacity ON vehicle_type_loading_capacity.vehicle_type_id = vehicle_type_master.id AND
							vehicle_type_loading_capacity.mrf_id = wm_department.id
							WHERE wm_client_master_po_details.status = 1
							AND '$TODAY' BETWEEN wm_client_master_po_details.start_date AND wm_client_master_po_details.end_date
							ORDER BY Source ASC, Product_Name ASC";
		$SELECTRES 	= DB::connection('master_database')->select($SELECT_SQL);
		$arrResult 	= array();
		$arrDates 	= array();
		$PrevID 	= 0;
		if (!empty($SELECTRES))
		{
			$ROWID = 0;
			foreach ($SELECTRES as $SELECTROW)
			{
				if ($PrevID != $SELECTROW->mrf_id) {
					$ROWID 										= 0;
					$arrResult[$SELECTROW->mrf_id] 				= array();
					$arrResult[$SELECTROW->mrf_id]['MRF_NAME'] 	= $SELECTROW->Source;
					$PrevID 									= $SELECTROW->mrf_id;
				}
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID]['Product_Name'] 		= $SELECTROW->Product_Name;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID]['Source'] 			= $SELECTROW->Source;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID]['Destination'] 		= $SELECTROW->Destination;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID]['Shipping_Address']	= $SELECTROW->Shipping_Address;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID]['TypeOfVehicle'] 	= $SELECTROW->TypeOfVehicle;
				$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID]['Capacity'] 			= $SELECTROW->Min_Capacity." - ".$SELECTROW->Max_Capacity;
				$DateOfTarget 												= $TODAY;
				$MRF_ID 													= $SELECTROW->mrf_id;
				$CLIENT_ID 													= $SELECTROW->wm_client_id;
				$PRODUCT_ID 												= $SELECTROW->wm_product_id;
				$DispatchOfTheDay 											= 0;
				while(strtotime($Yesterday) < strtotime($TODAY)) {
					if (strtotime($SELECTROW->start_date) <= strtotime($Yesterday)) {
						$DispatchQty 		= WmDispatchProduct::GetProductDispatchByMRF($Yesterday,$MRF_ID,$CLIENT_ID,$PRODUCT_ID);
						$DispatchOfTheDay	+= ($SELECTROW->DailyDispatchQty - $DispatchQty);
					}
					$Yesterday = date('Y-m-d', strtotime($Yesterday . ' +1 day'));
				}
				$Adjustment 	= ($DispatchOfTheDay > 0)?abs($DispatchOfTheDay/($NoOfDays+1)):0;
				while(strtotime($DateOfTarget) <= strtotime($ScheduleDate)) {
					$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID][$DateOfTarget]['Target'] = $SELECTROW->DailyDispatchQty;
					if ($DateOfTarget == $TODAY) {
						$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID][$DateOfTarget]['Adjustment'] 		= $Adjustment;
						$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID][$DateOfTarget]['Target_Qty'] 		= ($SELECTROW->DailyDispatchQty + $Adjustment);
						$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID][$DateOfTarget]['Actual_Dispatch'] 	= WmDispatchProduct::GetProductDispatchByMRF($DateOfTarget,$MRF_ID,$CLIENT_ID,$PRODUCT_ID);
						$Remaining 																				= ($SELECTROW->DailyDispatchQty + $Adjustment) - $arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID][$DateOfTarget]['Actual_Dispatch'];
						$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID][$DateOfTarget]['Remaining'] 			= ($Remaining > 0)?$Remaining:0;
					} else {
						$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID][$DateOfTarget]['Adjustment'] 		= $Adjustment;
						$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID][$DateOfTarget]['Target_Qty'] 		= ($SELECTROW->DailyDispatchQty + $Adjustment);
						$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID][$DateOfTarget]['Actual_Dispatch'] 	= 0;
						$Remaining 																				= ($SELECTROW->DailyDispatchQty + $Adjustment) - $arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID][$DateOfTarget]['Actual_Dispatch'];
						$arrResult[$SELECTROW->mrf_id]['PRODUCTS'][$ROWID][$DateOfTarget]['Remaining'] 			= ($Remaining > 0)?$Remaining:0;
					}
					array_push($arrDates,$DateOfTarget);
					$DateOfTarget = date('Y-m-d', strtotime($DateOfTarget . ' +1 day'));
				}
				$ROWID++;
			}
		}
		$arrDates = array_unique($arrDates);
		return view("importcollection.rdfdashboard",["Page_Title"=>"Widget of Dispatch Plan(In KG)","arrResult"=>$arrResult,"arrDates"=>$arrDates]);
	}

	public function salesprediction(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			return $this->_SalesPrediction($request);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function _SalesPrediction($request)
	{
		$arrResult 		= array();
		$arrMRF			= array();
		$arrMonth		= array();
		$arrMRF 		= WmDepartment::where(array("is_virtual"=> 0,"status"=>1,"display_in_unload"=>1))->pluck("department_name","id")->toArray();
		$arrProducts 	= WmProductMaster::where(array("status"=>1))->orderBy("title","ASC")->pluck("title","net_suit_code")->toArray();
		if ($request->isMethod("post"))
		{
		}
		$counter = 1;
		while($counter <= 3) {
			$MONTHNAME 					= date('M',strtotime('first day of +'.$counter.' month'));
			$MONTH 						= date('m',strtotime('first day of +'.$counter.' month'));
			$YEAR 						= date('Y',strtotime('first day of +'.$counter.' month'));
			$arrMonth["$MONTH-$YEAR"] 	= $MONTHNAME."-".$YEAR;
			$counter++;
		}
		return view("importcollection.sales-prediction",["Page_Title"=>"Sales Prediction",
														"arrResult"=>$arrResult,
														"arrMonth"=>$arrMonth,
														"arrMRF"=>$arrMRF,
														"mrf_id"=>$this->mrf_id,
														"product_ids"=>$this->product_ids,
														"arrProducts"=>$arrProducts]);
	}

	public function investorccofdetails(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			return $this->_GetInvestorCCOFDetails($request);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function _GetInvestorCCOFDetails($request)
	{
		$arrMRF 					= array();
		$arrResult 					= array();
		$this->report_starttime 	= date("Y-m-01",strtotime("01-".$this->reportmonth))." ".GLOBAL_START_TIME;
		$this->report_endtime 		= date("Y-m-t",strtotime("01-".$this->reportmonth))." ".GLOBAL_END_TIME;
		$arrLocations 				= array();
		$OriLocation 				= "";
		if (!empty($this->location_id)) {
			$OriLocation 			= $this->location_id;
			$arrCCOFLocationWebsite = CCOFLocationsWebsite::whereIn("id",$this->location_id)->where("status",1)->get();
			$arrrLocationMaster 	= "";
			if (!empty($arrCCOFLocationWebsite)) {
				foreach ($arrCCOFLocationWebsite as $arrRow) {
					$arrrLocationMaster .= $arrRow->ccof_location_master_ids.",";
				}
				if (!empty($arrrLocationMaster)) {
					$arrrLocationMaster = rtrim($arrrLocationMaster,",");
					$this->location_id 	= explode(",",$arrrLocationMaster);
				}
			}
			$arrCCOFLocationMaster = CCOFLocations::whereIn("id",$this->location_id)->where("status",1)->get();
			if (!empty($arrCCOFLocationMaster)) {
				foreach ($arrCCOFLocationMaster as $arrResult) {
					array_push($this->basestation_id,$arrResult->baselocation_id);
					array_push($this->mrf_id,$arrResult->mrf_ids);
					if (!empty($arrResult->nca_user_location)) {
						$TempArray = explode(",",$arrResult->nca_user_location);
						foreach ($TempArray as $nca_user_location) {
							array_push($arrLocations,$nca_user_location);
						}
					}
					if (!empty($arrResult->nca_company_master_id)) {
						$TempArray = explode(",",$arrResult->nca_company_master_id);
						foreach ($TempArray as $nca_company_master_id) {
							array_push($this->company_id,$nca_company_master_id);
						}
					}
				}
				if (sizeof($this->basestation_id) > 1) {
					if (($key = array_search(0,$this->basestation_id)) !== false) {
						unset($this->basestation_id[$key]);
					}
				}
				if (sizeof($this->mrf_id) > 1) {
					if (($key = array_search(0,$this->mrf_id)) !== false) {
						unset($this->mrf_id[$key]);
					}
				}
			}
		}
		$TopSuppliers						= CCOF::getTopSuppliers($this->report_starttime,$this->report_endtime,$this->basestation_id);
		$TopClients							= CCOF::getTopClients($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$InwardMaterialComposition			= CCOF::InwardMaterialComposition($this->report_starttime,$this->report_endtime,$this->basestation_id,true);
		$OutwardMaterialComposition			= CCOF::OutwardMaterialComposition($this->report_starttime,$this->report_endtime,$this->mrf_id,true);
		$TotalMaterialProcessed				= CCOF::TotalMaterialProcessed($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalMaterialOutwardR				= CCOF::TotalMaterialOutward($this->report_starttime,$this->report_endtime,RECYCLEBLE_TYPE,$this->mrf_id,true);
		$TotalMaterialOutwardNR				= CCOF::TotalMaterialOutward($this->report_starttime,$this->report_endtime,NON_RECYCLEBLE_TYPE,$this->mrf_id,true);
		$TotalInertMaterial					= CCOF::TotalInertMaterial($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalRDFMaterial					= CCOF::TotalRDFMaterial($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalInwardMaterialCost			= CCOF::TotalInwardMaterialCost($this->report_starttime,$this->report_endtime,$this->basestation_id,true);
		$TotalSalesRevenueDetails			= CCOF::TotalSalesRevenueDetails($this->report_starttime,$this->report_endtime,$this->mrf_id,true);
		$TotalServicesRevenueDetails		= CCOF::TotalServicesRevenueDetails($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$GetRetentionRatio 					= CCOF::GetRetentionRatio($this->report_starttime,$this->report_endtime,$arrLocations,$this->company_id);
		$GetCarbonMitigationAndEnergySaving = CCOF::GetCarbonMitigationAndEnergySaving($this->report_starttime,$this->report_endtime,$this->basestation_id,true);
		$arrCCOFLocations 					= CCOFLocationsWebsite::where("status",1)->pluck("title","id");
		$CCOF 								= new CCOF();
		$CCOF->TotalManpowerInformation 	= new \stdClass();
		$CCOF->ExpensesAndRevnue 			= new \stdClass();
		$CCOF->Compliance 					= new \stdClass();
		$CCOF->Grievance_Matrix 			= new \stdClass();
		$CCOF->Employment_Summary 			= new \stdClass();
		$CCOF->TotalManpowerInformation($this->report_starttime,$this->report_endtime,$arrLocations,$this->location_id,$this->company_id);
		$CCOF->GetEmploymentSummary($this->report_starttime,$this->report_endtime,$arrLocations,$this->company_id);
		return view("importcollection.investor-ccofdata",[	"Page_Title"=>"NEPRA's Monthly Impact Reporting",
															"arrResult"=>$arrResult,
															"arrCCOFLocations"=>$arrCCOFLocations,
															"LocationIDS"=>$OriLocation,
															"arrWTypes"=>$CCOF->arrWTypes,
															"arrGender"=>$CCOF->arrGender,
															"arrWorkers"=>$CCOF->arrWorkers,
															"arrComplianceData"=>$CCOF->arrComplianceData,
															"arrGrievanceMatrix"=>$CCOF->arrGrievanceMatrix,
															"arrEmploymentSummary"=>$CCOF->arrEmploymentSummary,
															"TopSuppliers"=>$TopSuppliers,
															"TopClients"=>$TopClients,
															"InwardMaterialComposition"=>$InwardMaterialComposition,
															"OutwardMaterialComposition"=>$OutwardMaterialComposition,
															"TotalMaterialProcessed"=>$TotalMaterialProcessed,
															"TotalMaterialOutwardR"=>$TotalMaterialOutwardR,
															"TotalMaterialOutwardNR"=>$TotalMaterialOutwardNR,
															"TotalInertMaterial"=>$TotalInertMaterial,
															"TotalRDFMaterial"=>$TotalRDFMaterial,
															"TotalInwardMaterialCost"=>$TotalInwardMaterialCost,
															"TotalSalesRevenueDetails"=>$TotalSalesRevenueDetails,
															"TotalServicesRevenueDetails"=>$TotalServicesRevenueDetails,
															"TotalManpowerInformation"=>$CCOF->TotalManpowerInformation,
															"ExpensesAndRevnue"=>$CCOF->ExpensesAndRevnue,
															"Compliance"=>$CCOF->Compliance,
															"Grievance_Matrix"=>$CCOF->Grievance_Matrix,
															"GetRetentionRatio"=>$GetRetentionRatio,
															"Employment_Summary"=>$CCOF->Employment_Summary,
															"GetCarbonMitigationAndEnergySaving"=>$GetCarbonMitigationAndEnergySaving,
															"MRF_IDS"=>$this->mrf_id,
															"daterange"=>$this->daterange,
															"reportmonth"=>$this->reportmonth,
															"report_starttime"=>$this->report_starttime,
															"report_endtime"=>$this->report_endtime]);
	}

	public function getRDFDashboardV2(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS))
		{
			$this->SetVariables($request);
			return $this->_getRDFDashboardV3($request);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function _getRDFDashboardV2($request)
	{
		$arrProducts 	= WmProductMaster::where(array("wm_product_master.status"=>1))
							->leftjoin("wm_client_master_po_details","wm_client_master_po_details.wm_product_id","=", "wm_product_master.id")
							->whereNotNull("wm_client_master_po_details.id")
							->groupBy("wm_product_master.id")
							->orderBy("wm_product_master.title","ASC")
							->pluck("wm_product_master.title","wm_product_master.id")
							->toArray();
		$arrMRF			= WmDepartment::where("is_virtual",0)->where("status",1)->pluck("department_name","id")->toArray();
		$arrClients 	= WmClientMaster::where("wm_client_master.status","A")
							->leftjoin("wm_client_master_po_details","wm_client_master_po_details.wm_client_id","=", "wm_client_master.id")
							->whereNotNull("wm_client_master_po_details.id")
							->groupBy("wm_client_master.id")
							->orderBy("wm_client_master.client_name","ASC")
							->pluck("wm_client_master.client_name","wm_client_master.id");
		$arrColumns 	= array("SALES_QUANTITY"=>"Sales Qty.","SALES_REVENUE"=>"Sales Rev.","SALES_RATE_PER_KG"=>"Sales/KG",
								"FREIGHT_CHARGE"=>"Freight","FREIGHT_RATE_PER_KG"=>"Freight/KG",
								"MRF_COST"=>"MRF Cost","MRF_COST_PER_KG"=>"MRF Cost/KG",
								"PROFIT_LOSS"=>"P & L","PROFIT_LOSS_PER_KG"=>"P & L/KG");
		$STARTDATE 		= date("Y-m-d",strtotime($this->report_starttime));
		$ENDDATE 		= date("Y-m-d",strtotime($this->report_endtime));

		$WHERECOND 		= "";
		if (!empty($this->mrf_id) && is_array($this->mrf_id)) {
			$WHERECOND .= "AND wm_department.id IN (".implode($this->mrf_id,",").")";
		}
		if (!empty($this->product_ids) && is_array($this->product_ids)) {
			$WHERECOND .= "AND wm_product_master.id IN (".implode($this->product_ids,",").")";
		}
		if (!empty($this->client_ids) && is_array($this->client_ids)) {
			$WHERECOND .= "AND wm_client_master.id IN (".implode($this->client_ids,",").")";
		}


		$SELECT_SQL 	= "	SELECT
							Trim(Replace(Replace(Replace(wm_department.department_name, 'MRF-', ''), 'MRF -', ''), 'MRF -', '')) AS Source,
							wm_client_master.client_name AS Destination,
							wm_product_master.id AS Product_ID,
							wm_product_master.title AS Product_Name,
							DATE_FORMAT(wm_dispatch.dispatch_date,'%Y-%m-%d') AS Dispatch_Date,
							CASE WHEN 1 = 1 THEN
							(
								SELECT CONCAT(wm_client_master_po_planning.plan_end_date,'|',wm_client_master_po_planning.para_quality_type_id)
								FROM wm_client_master_po_planning
								LEFT JOIN wm_client_master_po_details ON wm_client_master_po_details.id = wm_client_master_po_planning.wm_client_po_id
								WHERE wm_client_master_po_planning.plan_end_date >= Dispatch_Date
								ORDER BY wm_client_master_po_planning.id DESC
								LIMIT 1
							) END AS Product_Quality,
							CASE WHEN 1 = 1 THEN
							(
								SELECT CONCAT(wm_client_master_po_planning_log.plan_end_date,'|',wm_client_master_po_planning_log.para_quality_type_id)
								FROM wm_client_master_po_planning_log
								LEFT JOIN wm_client_master_po_details ON wm_client_master_po_details.id = wm_client_master_po_planning_log.wm_client_po_id
								WHERE wm_client_master_po_planning_log.plan_end_date >= Dispatch_Date
								ORDER BY wm_client_master_po_planning_log.log_id DESC
								LIMIT 1
							) END AS Product_Quality_Log,
							wm_dispatch.id AS Dispatch_ID,
							wm_dispatch.bill_from_mrf_id AS MRF_ID,
							wm_dispatch_product.quantity AS DispatchQty,
							wm_dispatch_product.price AS Dispatch_Rate,
							wm_dispatch_product.net_amount AS Net_Amount,
							transporter_details_master.rate AS Transportation_Cost,
							transporter_details_master.demurrage as Demurrage

							FROM wm_dispatch_product
							LEFT JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
							LEFT JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
							LEFT JOIN wm_client_master_po_details ON wm_client_master.id = wm_client_master_po_details.wm_client_id
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_client_master_po_details.wm_product_id
							LEFT JOIN wm_department ON wm_department.id = wm_client_master_po_details.mrf_id
							LEFT JOIN transporter_details_master ON wm_dispatch.id = transporter_details_master.dispatch_id
							WHERE wm_dispatch_product.product_id = wm_product_master.id
							AND wm_client_master_po_details.status = 1
							AND wm_dispatch.approval_status = 1
							AND wm_dispatch.dispatch_date BETWEEN '".$STARTDATE." ".GLOBAL_START_TIME."' AND '".$ENDDATE." ".GLOBAL_END_TIME."'
							$WHERECOND
							GROUP BY wm_dispatch.id
							ORDER BY Source ASC, wm_client_master.client_name ASC, wm_product_master.title ASC";
		$SELECTRES 		= DB::connection('master_database')->select($SELECT_SQL);
		$arrResult 		= array();
		$arrDates 		= array();
		$PrevID 		= 0;
		$T_S_R 			= 0;
		$T_S_Q 			= 0;
		$T_F_C 			= 0;
		$T_M_C 			= 0;
		$T_P_L 			= 0;
		if (!empty($SELECTRES))
		{
			$ROWID = 0;
			foreach ($SELECTRES as $SELECTROW)
			{
				$MATERIAL_TYPE = 105601; //default unshredded
				if (!empty($SELECTROW->Product_Quality_Log)) {
					$MATERIAL_TYPE_ROW = explode("|",$SELECTROW->Product_Quality_Log);
					if (strtotime($SELECTROW->Dispatch_Date) <= strtotime($MATERIAL_TYPE_ROW[0])) {
						$MATERIAL_TYPE = $MATERIAL_TYPE_ROW[1];
					}
				}
				if (empty($MATERIAL_TYPE) && !empty($SELECTROW->Product_Quality)) {
					$MATERIAL_TYPE_ROW = explode("|",$SELECTROW->Product_Quality);
					if (strtotime($SELECTROW->Dispatch_Date) <= strtotime($MATERIAL_TYPE_ROW[0])) {
						$MATERIAL_TYPE = $MATERIAL_TYPE_ROW[1];
					}
				}
				/** GET PROCESSING COST MONTHWISE FOR EACH MRF */
				$Cost_Per_Kg 	= 0;
				$MRF_COST_SQL 	= "	SELECT none_shredding, single_shredding, double_shredding
									FROM wm_plant_rdf_cost_monthwise
									WHERE mrf_id = ".$SELECTROW->MRF_ID."
									AND c_year = ".date("Y",strtotime($STARTDATE))."
									AND c_month = ".date("m",strtotime($STARTDATE));
				$MRF_COST_RES 	= DB::connection('master_database')->select($MRF_COST_SQL);
				if (!empty($MRF_COST_RES) && isset($MRF_COST_RES[0])) {
					$MRF_COST_ROW = $MRF_COST_RES[0];
					switch ($MATERIAL_TYPE) {
						case 105601:
							$Cost_Per_Kg = $MRF_COST_ROW->none_shredding;
							break;
						case 105602:
							$Cost_Per_Kg = $MRF_COST_ROW->single_shredding;
							break;
						case 105603:
							$Cost_Per_Kg = $MRF_COST_ROW->double_shredding;
							break;
					}
				}
				/** GET PROCESSING COST MONTHWISE FOR EACH MRF */

				$Dispatch_Rate 	= $SELECTROW->Dispatch_Rate;
				$DispatchQty 	= $SELECTROW->DispatchQty;
				$OriginalDisQty = $SELECTROW->DispatchQty;
				$SALES_AMT 		= 0;
				if (!empty($SELECTROW->Dispatch_ID)) {
					$CN_DN_SQL = "	SELECT wm_invoices_credit_debit_notes_details.change_in, wm_invoices_credit_debit_notes_details.revised_rate,
									wm_invoices_credit_debit_notes_details.new_quantity
									FROM wm_invoices_credit_debit_notes_details
									INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
									WHERE wm_invoices_credit_debit_notes.dispatch_id = ".$SELECTROW->Dispatch_ID."
									AND wm_invoices_credit_debit_notes_details.product_id = ".$SELECTROW->Product_ID."
									AND wm_invoices_credit_debit_notes.status = 2
									ORDER BY wm_invoices_credit_debit_notes.id DESC";
					$CN_DN_RES 	= DB::connection('master_database')->select($CN_DN_SQL);
					if (!empty($CN_DN_RES)) {
						$NDispatchQty 	= 0;
						$NDispatch_Rate = 0;
						foreach ($CN_DN_RES as $CN_DN_ROW) {
							if ($CN_DN_ROW->change_in == 2) { //QUANTITY
								$NDispatchQty += $CN_DN_ROW->new_quantity;
								$SALES_AMT += round(($NDispatchQty * $Dispatch_Rate),2);
							} else if ($CN_DN_ROW->change_in == 1) { //RATE
								$NDispatch_Rate = $CN_DN_ROW->revised_rate;
								$SALES_AMT += round(($DispatchQty * $NDispatch_Rate),2);
							}
						}
					} else {
						$SALES_AMT 	= round(($DispatchQty * $Dispatch_Rate),2);
					}
				} else {
					$SALES_AMT 	= round(($DispatchQty * $Dispatch_Rate),2);
				}
				$SALES_AMT 				= round(($DispatchQty * $Dispatch_Rate),2);
				$FREIGHT_CHARGE 		= round(($SELECTROW->Transportation_Cost + $SELECTROW->Demurrage),2);
				$FREIGHT_RATE_PER_KG	= !empty($FREIGHT_CHARGE)?round(($FREIGHT_CHARGE/$DispatchQty),2):0;
				$MRF_COST				= round(($OriginalDisQty * $Cost_Per_Kg),2);
				$PROFIT_LOSS 			= round(($SALES_AMT - ($FREIGHT_CHARGE + $MRF_COST)),2);
				$PROFIT_LOSS_PER_KG 	= !empty($PROFIT_LOSS)?round(($PROFIT_LOSS/$OriginalDisQty),2):0;
				$arrResult[] 			= array("MRF"=>$SELECTROW->Source,
												"CLIENT"=>$SELECTROW->Destination,
												"PRODUCT"=>$SELECTROW->Product_Name,
												"SALES_QUANTITY"=>$SELECTROW->DispatchQty,
												"SALES_REVENUE"=>$SALES_AMT,
												"SALES_RATE_PER_KG"=>$Dispatch_Rate,
												"FREIGHT_CHARGE"=>$FREIGHT_CHARGE,
												"FREIGHT_RATE_PER_KG"=>$FREIGHT_RATE_PER_KG,
												"MRF_COST"=>$MRF_COST,
												"MRF_COST_PER_KG"=>$Cost_Per_Kg,
												"PROFIT_LOSS"=>$PROFIT_LOSS,
												"PROFIT_LOSS_PER_KG"=>$PROFIT_LOSS_PER_KG);
				$T_S_R += $SALES_AMT;
				$T_S_Q += $DispatchQty;
				$T_F_C += $FREIGHT_CHARGE;
				$T_M_C += $MRF_COST;
				$T_P_L += $PROFIT_LOSS;
			}
		}
		$arrGrandTotal['T_S_R'] 	= $T_S_R;
		$arrGrandTotal['T_S_Q'] 	= $T_S_Q;
		$arrGrandTotal['T_S_A'] 	= round((!empty($T_S_Q)?($T_S_R/$T_S_Q):0),2);
		$arrGrandTotal['T_F_C'] 	= $T_F_C;
		$arrGrandTotal['T_F_A'] 	= round((!empty($T_S_Q)?($T_F_C/$T_S_Q):0),2);
		$arrGrandTotal['T_M_C'] 	= $T_M_C;
		$arrGrandTotal['T_M_A'] 	= round((!empty($T_S_Q)?($T_M_C/$T_S_Q):0),2);
		$arrGrandTotal['T_P_L'] 	= $T_P_L;
		$arrGrandTotal['T_P_L_A'] 	= round((!empty($T_S_Q)?($T_P_L/$T_S_Q):0),2);
		$daterange					= $this->daterange;
		$report_starttime			= $this->report_starttime;
		$report_endtime				= $this->report_endtime;
		return view("importcollection.rdf-dashboard-2",["Page_Title"=>"RDF/AFR P & L Report For (".$STARTDATE." To ".$ENDDATE.")",
														"arrResult"=>$arrResult,
														"arrGrandTotal"=>$arrGrandTotal,
														"arrDates"=>$arrDates,
														"daterange"=>$daterange,
														"report_starttime"=>$report_starttime,
														"report_endtime"=>$report_endtime,
														"arrColumns"=>$arrColumns,
														"arrSelColumns"=>$this->arrSelColumns,
														"arrClients"=>$arrClients,
														"arrClientsIDs"=>$this->client_ids,
														"arrProducts"=>$arrProducts,
														"arrProductsIDs"=>$this->product_ids,
														"arrMRF"=>$arrMRF,
														"arrMRFIDs"=>$this->mrf_id]);
	}

	private function _getRDFDashboardV3($request)
	{
		$arrProducts 	= WmProductMaster::where(array("wm_product_master.status"=>1))
							->leftjoin("wm_client_master_po_details","wm_client_master_po_details.wm_product_id","=", "wm_product_master.id")
							->whereNotNull("wm_client_master_po_details.id")
							->groupBy("wm_product_master.id")
							->orderBy("wm_product_master.title","ASC")
							->pluck("wm_product_master.title","wm_product_master.id")
							->toArray();
		$arrMRF			= WmDepartment::where("is_virtual",0)->where("status",1)->pluck("department_name","id")->toArray();
		$arrClients 	= WmClientMaster::where("wm_client_master.status","A")
							->leftjoin("wm_client_master_po_details","wm_client_master_po_details.wm_client_id","=", "wm_client_master.id")
							->whereNotNull("wm_client_master_po_details.id")
							->groupBy("wm_client_master.id")
							->orderBy("wm_client_master.client_name","ASC")
							->pluck("wm_client_master.client_name","wm_client_master.id");
		$arrColumns 	= array("SALES_QUANTITY"=>"Sales Qty.","SALES_REVENUE"=>"Sales Rev.","SALES_RATE_PER_KG"=>"Sales/KG",
								"FREIGHT_CHARGE"=>"Freight","FREIGHT_RATE_PER_KG"=>"Freight/KG",
								"MRF_COST"=>"MRF Cost","MRF_COST_PER_KG"=>"MRF Cost/KG",
								"PROFIT_LOSS"=>"P & L","PROFIT_LOSS_PER_KG"=>"P & L/KG");
		$STARTDATE 		= date("Y-m-d",strtotime($this->report_starttime));
		$ENDDATE 		= date("Y-m-d",strtotime($this->report_endtime));

		$WHERECOND 		= "";
		if (!empty($this->mrf_id) && is_array($this->mrf_id)) {
			$WHERECOND .= "AND wm_department.id IN (".implode($this->mrf_id,",").")";
		}
		if (!empty($this->product_ids) && is_array($this->product_ids)) {
			$WHERECOND .= "AND wm_product_master.id IN (".implode($this->product_ids,",").")";
		}
		if (!empty($this->client_ids) && is_array($this->client_ids)) {
			$WHERECOND .= "AND wm_client_master.id IN (".implode($this->client_ids,",").")";
		}


		$SELECT_SQL 	= "	SELECT
							Trim(Replace(Replace(Replace(wm_department.department_name, 'MRF-', ''), 'MRF -', ''), 'MRF -', '')) AS Source,
							wm_client_master.client_name AS Destination,
							wm_product_master.id AS Product_ID,
							wm_product_master.title AS Product_Name,
							DATE_FORMAT(wm_dispatch.dispatch_date,'%Y-%m-%d') AS Dispatch_Date,
							CASE WHEN 1 = 1 THEN
							(
								SELECT CONCAT(wm_client_master_po_planning.plan_end_date,'|',wm_client_master_po_planning.para_quality_type_id)
								FROM wm_client_master_po_planning
								LEFT JOIN wm_client_master_po_details ON wm_client_master_po_details.id = wm_client_master_po_planning.wm_client_po_id
								WHERE wm_client_master_po_planning.plan_end_date >= Dispatch_Date
								ORDER BY wm_client_master_po_planning.id DESC
								LIMIT 1
							) END AS Product_Quality,
							CASE WHEN 1 = 1 THEN
							(
								SELECT CONCAT(wm_client_master_po_planning_log.plan_end_date,'|',wm_client_master_po_planning_log.para_quality_type_id)
								FROM wm_client_master_po_planning_log
								LEFT JOIN wm_client_master_po_details ON wm_client_master_po_details.id = wm_client_master_po_planning_log.wm_client_po_id
								WHERE wm_client_master_po_planning_log.plan_end_date >= Dispatch_Date
								ORDER BY wm_client_master_po_planning_log.log_id DESC
								LIMIT 1
							) END AS Product_Quality_Log,
							wm_dispatch.id AS Dispatch_ID,
							wm_dispatch.bill_from_mrf_id AS MRF_ID,
							wm_dispatch_product.quantity AS DispatchQty,
							wm_dispatch_product.price AS Dispatch_Rate,
							wm_dispatch_product.net_amount AS Net_Amount,
							transporter_details_master.rate AS Transportation_Cost,
							transporter_details_master.demurrage as Demurrage,
							wm_client_master.id as wm_client_id,
							wm_product_master.id AS wm_product_id,
							wm_department.id as MRFID
							FROM wm_dispatch_product
							LEFT JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
							LEFT JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
							LEFT JOIN wm_client_master_po_details ON wm_client_master.id = wm_client_master_po_details.wm_client_id
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_client_master_po_details.wm_product_id
							LEFT JOIN wm_department ON wm_department.id = wm_client_master_po_details.mrf_id
							LEFT JOIN transporter_details_master ON wm_dispatch.id = transporter_details_master.dispatch_id
							WHERE wm_dispatch_product.product_id = wm_product_master.id
							AND wm_client_master_po_details.status = 1
							AND wm_dispatch.approval_status = 1
							AND wm_dispatch.dispatch_date BETWEEN '".$STARTDATE." ".GLOBAL_START_TIME."' AND '".$ENDDATE." ".GLOBAL_END_TIME."'
							$WHERECOND
							GROUP BY wm_dispatch.id
							ORDER BY Source ASC, wm_client_master.client_name ASC, wm_product_master.title ASC";
		$SELECTRES 		= DB::connection('master_database')->select($SELECT_SQL);
		$arrResult 		= array();
		$arrDates 		= array();
		$PrevID 		= 0;
		$T_S_R 			= 0;
		$T_S_Q 			= 0;
		$T_F_C 			= 0;
		$T_M_C 			= 0;
		$T_P_L 			= 0;
		if (!empty($SELECTRES))
		{
			$ROWID = 0;
			foreach ($SELECTRES as $SELECTROW)
			{
				$MATERIAL_TYPE = 105601; //default unshredded
				if (!empty($SELECTROW->Product_Quality_Log)) {
					$MATERIAL_TYPE_ROW = explode("|",$SELECTROW->Product_Quality_Log);
					if (strtotime($SELECTROW->Dispatch_Date) <= strtotime($MATERIAL_TYPE_ROW[0])) {
						$MATERIAL_TYPE = $MATERIAL_TYPE_ROW[1];
					}
				}
				if (empty($MATERIAL_TYPE) && !empty($SELECTROW->Product_Quality)) {
					$MATERIAL_TYPE_ROW = explode("|",$SELECTROW->Product_Quality);
					if (strtotime($SELECTROW->Dispatch_Date) <= strtotime($MATERIAL_TYPE_ROW[0])) {
						$MATERIAL_TYPE = $MATERIAL_TYPE_ROW[1];
					}
				}
				/** GET PROCESSING COST MONTHWISE FOR EACH MRF */
				$Cost_Per_Kg 	= 0;
				$MRF_COST_SQL 	= "	SELECT none_shredding, single_shredding, double_shredding
									FROM wm_plant_rdf_cost_monthwise
									WHERE mrf_id = ".$SELECTROW->MRF_ID."
									AND c_year = ".date("Y",strtotime($STARTDATE))."
									AND c_month = ".date("m",strtotime($STARTDATE));
				$MRF_COST_RES 	= DB::connection('master_database')->select($MRF_COST_SQL);
				if (!empty($MRF_COST_RES) && isset($MRF_COST_RES[0])) {
					$MRF_COST_ROW = $MRF_COST_RES[0];
					switch ($MATERIAL_TYPE) {
						case 105601:
							$Cost_Per_Kg = $MRF_COST_ROW->none_shredding;
							break;
						case 105602:
							$Cost_Per_Kg = $MRF_COST_ROW->single_shredding;
							break;
						case 105603:
							$Cost_Per_Kg = $MRF_COST_ROW->double_shredding;
							break;
					}
				}
				/** GET PROCESSING COST MONTHWISE FOR EACH MRF */

				$Dispatch_Rate 	= $SELECTROW->Dispatch_Rate;
				$DispatchQty 	= $SELECTROW->DispatchQty;
				$OriginalDisQty = $SELECTROW->DispatchQty;
				$SALES_AMT 		= 0;
				if (!empty($SELECTROW->Dispatch_ID)) {
					$CN_DN_SQL = "	SELECT wm_invoices_credit_debit_notes_details.change_in, wm_invoices_credit_debit_notes_details.revised_rate,
									wm_invoices_credit_debit_notes_details.new_quantity
									FROM wm_invoices_credit_debit_notes_details
									INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
									WHERE wm_invoices_credit_debit_notes.dispatch_id = ".$SELECTROW->Dispatch_ID."
									AND wm_invoices_credit_debit_notes_details.product_id = ".$SELECTROW->Product_ID."
									AND wm_invoices_credit_debit_notes.status = 2
									ORDER BY wm_invoices_credit_debit_notes.id DESC";
					$CN_DN_RES 	= DB::connection('master_database')->select($CN_DN_SQL);
					if (!empty($CN_DN_RES)) {
						$NDispatchQty 	= 0;
						$NDispatch_Rate = 0;
						foreach ($CN_DN_RES as $CN_DN_ROW) {
							if ($CN_DN_ROW->change_in == 2) { //QUANTITY
								$NDispatchQty += $CN_DN_ROW->new_quantity;
								$SALES_AMT += round(($NDispatchQty * $Dispatch_Rate),2);
							} else if ($CN_DN_ROW->change_in == 1) { //RATE
								$NDispatch_Rate = $CN_DN_ROW->revised_rate;
								$SALES_AMT += round(($DispatchQty * $NDispatch_Rate),2);
							}
						}
					} else {
						$SALES_AMT 	= round(($DispatchQty * $Dispatch_Rate),2);
					}
				} else {
					$SALES_AMT 	= round(($DispatchQty * $Dispatch_Rate),2);
				}
				$SALES_AMT 				= round(($DispatchQty * $Dispatch_Rate),2);
				$FREIGHT_CHARGE 		= round(($SELECTROW->Transportation_Cost + $SELECTROW->Demurrage),2);
				$FREIGHT_RATE_PER_KG	= !empty($FREIGHT_CHARGE)?round(($FREIGHT_CHARGE/$DispatchQty),2):0;
				$MRF_COST				= round(($OriginalDisQty * $Cost_Per_Kg),2);
				$PROFIT_LOSS 			= round(($SALES_AMT - ($FREIGHT_CHARGE + $MRF_COST)),2);
				$PROFIT_LOSS_PER_KG 	= !empty($PROFIT_LOSS)?round(($PROFIT_LOSS/$OriginalDisQty),2):0;
				$arrResult[] 			= array("MRF_ID"=>$SELECTROW->MRFID,
												"WM_CLIENT_ID"=>$SELECTROW->wm_client_id,
												"WM_PRODUCT_ID"=>$SELECTROW->wm_product_id,
												"MRF"=>$SELECTROW->Source,
												"CLIENT"=>$SELECTROW->Destination,
												"PRODUCT"=>$SELECTROW->Product_Name,
												"SALES_QUANTITY"=>$SELECTROW->DispatchQty,
												"SALES_REVENUE"=>$SALES_AMT,
												"SALES_RATE_PER_KG"=>$Dispatch_Rate,
												"FREIGHT_CHARGE"=>$FREIGHT_CHARGE,
												"FREIGHT_RATE_PER_KG"=>$FREIGHT_RATE_PER_KG,
												"MRF_COST"=>$MRF_COST,
												"MRF_COST_PER_KG"=>$Cost_Per_Kg,
												"PROFIT_LOSS"=>$PROFIT_LOSS,
												"PROFIT_LOSS_PER_KG"=>$PROFIT_LOSS_PER_KG);
				$T_S_R += $SALES_AMT;
				$T_S_Q += $DispatchQty;
				$T_F_C += $FREIGHT_CHARGE;
				$T_M_C += $MRF_COST;
				$T_P_L += $PROFIT_LOSS;
			}
		}

		$arrFinalResult 	= array();
		$PrevID 			= "";
		$COUNTER 			= 0;
		$T_SALES_QUANTITY 	= 0;
		$T_SALES_REVENUE 	= 0;
		$T_FREIGHT_CHARGE 	= 0;
		$T_MRF_COST 		= 0;
		$T_PROFIT_LOSS 		= 0;
		foreach ($arrResult as $arrResultRow) {
			if (!empty($PrevID) && $PrevID != $arrResultRow['MRF_ID']."_".$arrResultRow['WM_PRODUCT_ID']."_".$arrResultRow['WM_CLIENT_ID']) {

				/** Add TOTAL MRF/PRODUCT/CLIENT WISE */
				$arrFinalResult[$COUNTER]['SALES_QUANTITY'] 		= _FormatNumberV2($T_SALES_QUANTITY,2);
				$arrFinalResult[$COUNTER]['SALES_REVENUE'] 			= _FormatNumberV2($T_SALES_REVENUE,2);
				$arrFinalResult[$COUNTER]['SALES_RATE_PER_KG'] 		= _FormatNumberV2((!empty($T_SALES_QUANTITY)?($T_SALES_REVENUE/$T_SALES_QUANTITY):0),2);
				$arrFinalResult[$COUNTER]['FREIGHT_CHARGE'] 		= _FormatNumberV2($T_FREIGHT_CHARGE,2);
				$arrFinalResult[$COUNTER]['FREIGHT_RATE_PER_KG'] 	= _FormatNumberV2((!empty($T_SALES_QUANTITY)?($T_FREIGHT_CHARGE/$T_SALES_QUANTITY):0),2);
				$arrFinalResult[$COUNTER]['MRF_COST'] 				= _FormatNumberV2($T_MRF_COST,2);
				$arrFinalResult[$COUNTER]['MRF_COST_PER_KG'] 		= _FormatNumberV2((!empty($T_SALES_QUANTITY)?($T_MRF_COST/$T_SALES_QUANTITY):0),2);
				$arrFinalResult[$COUNTER]['PROFIT_LOSS'] 			= _FormatNumberV2($T_PROFIT_LOSS,2);
				$arrFinalResult[$COUNTER]['PROFIT_LOSS_PER_KG'] 	= _FormatNumberV2((!empty($T_SALES_QUANTITY)?($T_PROFIT_LOSS/$T_SALES_QUANTITY):0),2);
				/** Add TOTAL MRF/PRODUCT/CLIENT WISE */

				$COUNTER++;
				$PrevID 								= $arrResultRow['MRF_ID']."_".$arrResultRow['WM_PRODUCT_ID']."_".$arrResultRow['WM_CLIENT_ID'];
				$T_SALES_QUANTITY 						= 0;
				$T_SALES_REVENUE 						= 0;
				$T_FREIGHT_CHARGE 						= 0;
				$T_MRF_COST 							= 0;
				$T_PROFIT_LOSS 							= 0;
				$arrFinalResult[$COUNTER]['MRF'] 		= $arrResultRow['MRF'];
				$arrFinalResult[$COUNTER]['PRODUCT'] 	= $arrResultRow['PRODUCT'];
				$arrFinalResult[$COUNTER]['CLIENT'] 	= $arrResultRow['CLIENT'];

			} else if ($PrevID != $arrResultRow['MRF_ID']."_".$arrResultRow['WM_PRODUCT_ID']."_".$arrResultRow['WM_CLIENT_ID']) {
				$arrFinalResult[$COUNTER]['MRF'] 		= $arrResultRow['MRF'];
				$arrFinalResult[$COUNTER]['PRODUCT'] 	= $arrResultRow['PRODUCT'];
				$arrFinalResult[$COUNTER]['CLIENT'] 	= $arrResultRow['CLIENT'];
				$PrevID 								= $arrResultRow['MRF_ID']."_".$arrResultRow['WM_PRODUCT_ID']."_".$arrResultRow['WM_CLIENT_ID'];
			}
			$T_SALES_QUANTITY 						+= $arrResultRow['SALES_QUANTITY'];
			$T_SALES_REVENUE 						+= $arrResultRow['SALES_REVENUE'];
			$T_FREIGHT_CHARGE 						+= $arrResultRow['FREIGHT_CHARGE'];
			$T_MRF_COST 							+= $arrResultRow['MRF_COST'];
			$T_PROFIT_LOSS 							+= $arrResultRow['PROFIT_LOSS'];
		}

		/** Add TOTAL MRF/PRODUCT/CLIENT WISE */
		$arrFinalResult[$COUNTER]['SALES_QUANTITY'] 		= _FormatNumberV2($T_SALES_QUANTITY,2);
		$arrFinalResult[$COUNTER]['SALES_REVENUE'] 			= _FormatNumberV2($T_SALES_REVENUE,2);
		$arrFinalResult[$COUNTER]['SALES_RATE_PER_KG'] 		= _FormatNumberV2((!empty($T_SALES_QUANTITY)?($T_SALES_REVENUE/$T_SALES_QUANTITY):0),2);
		$arrFinalResult[$COUNTER]['FREIGHT_CHARGE'] 		= _FormatNumberV2($T_FREIGHT_CHARGE,2);
		$arrFinalResult[$COUNTER]['FREIGHT_RATE_PER_KG'] 	= _FormatNumberV2((!empty($T_SALES_QUANTITY)?($T_FREIGHT_CHARGE/$T_SALES_QUANTITY):0),2);
		$arrFinalResult[$COUNTER]['MRF_COST'] 				= _FormatNumberV2($T_MRF_COST,2);
		$arrFinalResult[$COUNTER]['MRF_COST_PER_KG'] 		= _FormatNumberV2((!empty($T_SALES_QUANTITY)?($T_MRF_COST/$T_SALES_QUANTITY):0),2);
		$arrFinalResult[$COUNTER]['PROFIT_LOSS'] 			= _FormatNumberV2($T_PROFIT_LOSS,2);
		$arrFinalResult[$COUNTER]['PROFIT_LOSS_PER_KG'] 	= _FormatNumberV2((!empty($T_SALES_QUANTITY)?($T_PROFIT_LOSS/$T_SALES_QUANTITY):0),2);
		/** Add TOTAL MRF/PRODUCT/CLIENT WISE */

		$arrGrandTotal['T_S_R'] 	= _FormatNumberV2($T_S_R,2);
		$arrGrandTotal['T_S_Q'] 	= _FormatNumberV2($T_S_Q,2);
		$arrGrandTotal['T_S_A'] 	= _FormatNumberV2((!empty($T_S_Q)?($T_S_R/$T_S_Q):0),2);
		$arrGrandTotal['T_F_C'] 	= _FormatNumberV2($T_F_C,2);
		$arrGrandTotal['T_F_A'] 	= _FormatNumberV2((!empty($T_S_Q)?($T_F_C/$T_S_Q):0),2);
		$arrGrandTotal['T_M_C'] 	= _FormatNumberV2($T_M_C,2);
		$arrGrandTotal['T_M_A'] 	= _FormatNumberV2((!empty($T_S_Q)?($T_M_C/$T_S_Q):0),2);
		$arrGrandTotal['T_P_L'] 	= _FormatNumberV2($T_P_L,2);
		$arrGrandTotal['T_P_L_A'] 	= _FormatNumberV2((!empty($T_S_Q)?($T_P_L/$T_S_Q):0),2);
		$daterange					= $this->daterange;
		$report_starttime			= $this->report_starttime;
		$report_endtime				= $this->report_endtime;
		return view("importcollection.rdf-dashboard-2",["Page_Title"=>"RDF/AFR P & L Summary Report For (".$STARTDATE." To ".$ENDDATE.")",
														"arrResult"=>$arrFinalResult,
														"arrGrandTotal"=>$arrGrandTotal,
														"arrDates"=>$arrDates,
														"daterange"=>$daterange,
														"report_starttime"=>$report_starttime,
														"report_endtime"=>$report_endtime,
														"arrColumns"=>$arrColumns,
														"arrSelColumns"=>$this->arrSelColumns,
														"arrClients"=>$arrClients,
														"arrClientsIDs"=>$this->client_ids,
														"arrProducts"=>$arrProducts,
														"arrProductsIDs"=>$this->product_ids,
														"arrMRF"=>$arrMRF,
														"arrMRFIDs"=>$this->mrf_id]);
	}
	public function importSalesPaymentSheet(Request $request)
	{
		if($request->hasFile('document')){
			$uploadPath = "document";
		    $image      = $request->file('document');
            $fileName   = time() . '.' . $image->getClientOriginalExtension();
            if(!is_dir(public_path($uploadPath))) {
				mkdir(public_path($uploadPath),0777,true);
			}
			$image->move(public_path($uploadPath),$fileName);
			$this->SetVariables($request);
			$FilePath 			= public_path("document/".$fileName);
			$ImportFileObject 	= new ImportSalesPaymentSheet;
			$ExcelSheet 		= Excel::import($ImportFileObject, $FilePath);
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'),'data'=>'']);
		}
			return response()->json(['code' => ERROR, 'msg' => trans('message.SOMETHING_WENT_WRONG'),'data'=>'']);
	}

	public function SalesPaymentOutStandingReport(Request $request)
	{
		$data = WmSalesPaymentDetails::ListOutStandingReport($request->all());
		return response()->json(['code' => ERROR, 'msg' => trans('message.SOMETHING_WENT_WRONG'),'data'=>$data]);
	}

	public function generateBillTDiclarationPDF(Request $request)
	{
		$DATE 				= date("d-M-Y");
		$VehicleNo 			= "GJ-18-BR-0250";
		$WasteType  		= "Waste Plastic";
		$TranspoterName 	= "JK Transportation";
		$SOURCE  			= "AHMEDABAD";
		$CLIENT_NAME 		= "JK-2 Transportation";
		$DESTINATION  		= "INDORE";
		$TYPEOFTRANS 		= "Aggregator";
		$FILENAME   		= "Declaration-Sample.pdf";
		$HeaderImage 		= public_path("assets/pdf/Header.png");
		$HeaderImageType 	= pathinfo($HeaderImage, PATHINFO_EXTENSION);
		$imgData			= file_get_contents($HeaderImage);
		$HeaderImage 		= 'data:image/' . $HeaderImageType . ';base64,' . base64_encode($imgData);

		$FooterImage 		= public_path("assets/pdf/Footer.png");
		$FooterImageType 	= pathinfo($FooterImage, PATHINFO_EXTENSION);
		$imgData			= file_get_contents($FooterImage);
		$FooterImage 		= 'data:image/' . $FooterImageType . ';base64,' . base64_encode($imgData);

		$params 			= array("Trans_Date"=>$DATE,
									"VehicleNo"=>$VehicleNo,
									"WasteType"=>$WasteType,
									"TranspoterName"=>$TranspoterName,
									"Source"=>$SOURCE,
									"Client_Name"=>$CLIENT_NAME,
									"Destination"=>$DESTINATION,
									"TYPEOFTRANS"=>$TYPEOFTRANS,
									"HeaderImage"=>$HeaderImage,
									"FooterImage"=>$FooterImage);
		$PDF 				= PDF::loadView('pdf.declaration-billt',$params);
		$PDF->setPaper("letter","A4");
		return $PDF->stream("declaration.pdf");
		die;
	}

	public function getMRFByBaseLocations(Request $request)
	{
		$arrMRF = array();
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS)) {
			$this->SetVariables($request);
			$arrMRFs 	= WmDepartment::select("base_location_master.base_location_name","wm_department.id","wm_department.department_name")
							->leftjoin("base_location_master","base_location_master.id","wm_department.base_location_id")
							->where("wm_department.status",1)
							->whereIn('wm_department.base_location_id',$this->basestation_id)
							->orderBy("base_location_master.id","ASC")
							->get();
			if (!empty($arrMRFs)) {
				foreach ($arrMRFs as $arrMRFRow) {
					if (!isset($arrMRF[$arrMRFRow->base_location_name])) {
						$arrMRF[$arrMRFRow->base_location_name] = array();
					}
					$arrMRF[$arrMRFRow->base_location_name][] = array("id"=>$arrMRFRow->id,"name"=>$arrMRFRow->department_name);
				}
			}
			return response()->json(['code' => SUCCESS, 'msg' => trans('message.RECORD_FOUND'),'data'=>$arrMRF]);
		} else {
			return response()->json(['code' => ERROR, 'msg' => trans('message.SOMETHING_WENT_WRONG'),'data'=>$arrMRF]);
		}
	}

	public function getGPAnalysisReport(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS)) {
			if (!$request->isMethod("post")) {
				$request->merge(['report_period' => 4]);
			}
			$this->SetVariables($request);
			return $this->_getGPAnalysisReport($request);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function _getGPAnalysisReport($request)
	{
		$arrResult 				= array();
		$arrMRF 				= array();
		$arrBaseStations		= array();
		$arrSales				= array();
		$arrServices			= array();
		$arrCNDetails			= array();
		$arrDNDetails			= array();
		$arrTransferSales		= array();
		$arrInternalTransfer	= array();
		$arrNetSales			= array();

		$arrPurchase 			= array();
		$arrPurchaseCNDetails 	= array();
		$arrPurchaseDNDetails 	= array();
		$arrTransferPurchase 	= array();
		$arrInternalPurchase 	= array();
		$arrNetPurchase 		= array();

		$arrStockDetails 		= array();
		$arrCOGSDetails 		= array();
		$arrGPDetails 			= array();
		$arrGPPDetails 			= array();

		if (!empty($this->basestation_id)) {
			$arrMRF = WmDepartment::where("status",1)->whereIn('base_location_id',$this->basestation_id)->pluck("department_name","id")->toArray();
		}

		$arrSales 				= GPAnalysis::getSalesDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
		$arrServices 			= GPAnalysis::getServiceDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
		$arrCNDetails 			= GPAnalysis::getSalesServiceCNDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
		$arrDNDetails 			= GPAnalysis::getSalesServiceDNDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
		$arrTransferSales 		= GPAnalysis::getTransferDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id,true);
		$arrInternalTransfer 	= GPAnalysis::getTransferDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id,false);
		$arrNetSales 			= GPAnalysis::getNetSales($arrSales,$arrServices,$arrCNDetails,$arrDNDetails,$arrTransferSales,$arrInternalTransfer);

		$arrPurchase 			= GPAnalysis::getPurchaseDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
		$arrPurchaseCNDetails 	= GPAnalysis::getPurchaseCNDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
		$arrPurchaseDNDetails 	= GPAnalysis::getPurchaseDNDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
		$arrTransferPurchase 	= GPAnalysis::getPurchaseTransferDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id,true);
		$arrInternalPurchase 	= GPAnalysis::getPurchaseTransferDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id,false);
		$arrNetPurchase 		= GPAnalysis::getNetPurchase($arrPurchase,$arrPurchaseCNDetails,$arrPurchaseDNDetails,$arrTransferPurchase,$arrInternalPurchase);
		$arrStockDetails 		= GPAnalysis::getStockValuationDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
		$arrCOGSDetails 		= GPAnalysis::getCOGSValue($arrNetSales,$arrNetPurchase,$arrStockDetails);
		$arrGPDetails 			= GPAnalysis::getGPValue($arrNetSales,$arrCOGSDetails);
		$arrGPPDetails 			= GPAnalysis::getGPPercentage($arrNetSales,$arrGPDetails);
		$arrBaseStations = BaseLocationMaster::where("status","A")->pluck("base_location_name","id")->toArray();
		return view("importcollection.gp-analysis-dashboard",[	"Page_Title"=>"GP Analytical Dashboard",
																"arrBaseStations"=>$arrBaseStations,
																"arrMRF"=>$arrMRF,
																"basestation_id"=>$this->basestation_id,
																"mrf_id"=>$this->mrf_id,
																"arrSales"=>$arrSales,
																"arrServices"=>$arrServices,
																"arrCNDetails"=>$arrCNDetails,
																"arrDNDetails"=>$arrDNDetails,
																"arrTransferSales"=>$arrTransferSales,
																"arrInternalTransfer"=>$arrInternalTransfer,
																"arrNetSales"=>$arrNetSales,
																"arrPurchase"=>$arrPurchase,
																"arrPurchaseCNDetails"=>$arrPurchaseCNDetails,
																"arrPurchaseDNDetails"=>$arrPurchaseDNDetails,
																"arrTransferPurchase"=>$arrTransferPurchase,
																"arrInternalPurchase"=>$arrInternalPurchase,
																"arrNetPurchase"=>$arrNetPurchase,
																"arrStockDetails"=>$arrStockDetails,
																"arrCOGSDetails"=>$arrCOGSDetails,
																"arrGPDetails"=>$arrGPDetails,
																"arrGPPDetails"=>$arrGPPDetails,
																"daterange"=>$this->daterange,
																"report_starttime"=>$this->report_starttime,
																"report_endtime"=>$this->report_endtime]);
	}

	public function getProductionVsProjectionVsActualDashboard(Request $request)
	{
		if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'],$this->IP_ADDRESS)) {
			if (!$request->isMethod("post")) {
				$request->merge(['report_period' => 4]);
			}
			$this->SetVariables($request);
			return $this->_getProductionVsProjectionVsActualDashboard($request);
		} else {
			header("location:https://v2.letsrecycle.co.in/");
			die;
		}
	}

	private function _getProductionVsProjectionVsActualDashboard($request)
	{

		if (!empty($this->mrf_id)) {
			$arrMRF 	= WmDepartment::where("status",1)
							->whereIn('id',$this->mrf_id)
							->where('is_virtual',0)
							->where('is_service_mrf',0)
							->pluck("department_name","id")
							->toArray();
			$arrMRFID 	= array_keys($arrMRF);
		} else if (!empty($this->basestation_id)) {
			$arrMRF 	= WmDepartment::where("status",1)
							->whereIn('base_location_id',$this->basestation_id)
							->where('is_virtual',0)
							->where('is_service_mrf',0)
							->pluck("department_name","id")
							->toArray();
			$arrMRFID 	= array_keys($arrMRF);
		} else {
			$arrMRF 	= array();
			$arrMRFID 	= array(0);
		}
		$MONTH 		= date("m",strtotime($this->report_starttime));
		$YEAR 		= date("Y",strtotime($this->report_starttime));
		$SELECT_SQL = "	SELECT DISTINCT CONCAT(wm_product_master.id,'|',wm_production_report_master.mrf_id) AS U_I_D,
						wm_product_master.id as PRODUCT_ID,
						wm_production_report_master.mrf_id as MRFID,
						wm_product_master.title AS PRODUCT_NAME,
						wm_department.department_name as MRF_NAME,
						CASE WHEN 1=1 THEN
						(
							SELECT wm_projection_plan.projection_qty
							FROM wm_projection_plan
							WHERE wm_projection_plan.product_id = wm_product_master.id
							AND wm_projection_plan.month = $MONTH
							AND wm_projection_plan.year = $YEAR
							AND wm_projection_plan.mrf_id = wm_department.id
							GROUP BY wm_projection_plan.product_id
						) END AS PROJECTION_QTY,
						CASE WHEN 1=1 THEN
						(
							SELECT SUM(wm_processed_product_master.qty)
							FROM wm_processed_product_master
							LEFT JOIN wm_production_report_master ON wm_production_report_master.id = wm_processed_product_master.production_id
							WHERE wm_processed_product_master.sales_product_id = wm_product_master.id
							AND wm_production_report_master.mrf_id = wm_department.id
							AND wm_production_report_master.production_date BETWEEN '$this->report_starttime' AND '$this->report_endtime'
							AND wm_production_report_master.paid_collection = 0
							GROUP BY wm_processed_product_master.sales_product_id
						) END AS ACTUAL_QTY
						FROM wm_processed_product_master
						LEFT JOIN wm_product_master ON wm_product_master.id = wm_processed_product_master.sales_product_id
						LEFT JOIN wm_production_report_master ON wm_production_report_master.id = wm_processed_product_master.production_id
						LEFT JOIN wm_department ON wm_department.id = wm_production_report_master.mrf_id
						WHERE wm_production_report_master.production_date BETWEEN '$this->report_starttime' AND '$this->report_endtime'
						AND wm_department.id IN (".implode(",",$arrMRFID).")
						AND wm_product_master.recyclable = 1
						GROUP BY wm_processed_product_master.id, wm_production_report_master.mrf_id
						ORDER BY MRF_NAME ASC, PRODUCT_NAME ASC";
		$SELECT_S_RES 	= DB::connection('master_database')->select($SELECT_SQL);

		$arrUniquePro 				= array();
		$arrUniquePID 				= array();
		$DAYS_OF_THE_MONTH 			= 0;
		$arrResult 					= array();
		$TOTAL_PROJECTION_QTY 		= 0;
		$TOTAL_TILL_DATE_QTY 		= 0;
		$TOTAL_ACTUAL_QTY 			= 0;
		$TOTAL_SURPLUS_DEFICIT 		= 0;
		$TOTAL_SURPLUS_DEFICIT_PER 	= 0;
		$TOTAL_REMAINING_TARGET 	= 0;
		$TOTAL_REMAINING_TARGET_PER = 0;
		if (!empty($SELECT_S_RES)) {
			foreach ($SELECT_S_RES as $SELECT_S_ROW) {
				if (!in_array($SELECT_S_ROW->PRODUCT_ID,$arrUniquePID)) {
					array_push($arrUniquePID,$SELECT_S_ROW->PRODUCT_ID);
					$arrUniquePro[$SELECT_S_ROW->PRODUCT_ID] = $SELECT_S_ROW->PRODUCT_NAME;
				}
				$TOTAL_DAYS_IN_MONTH 			= date("t");
				$DAYS_OF_THE_MONTH 				= date("d");
				if (date("m",strtotime("now")) == date("m",strtotime($this->report_starttime)) && date("Y",strtotime("now")) == date("Y",strtotime($this->report_starttime))) {
				$DAYS_OF_THE_MONTH 				= date("d",strtotime("now"));
				} else if (strtotime(date("Y-m-d")) >= strtotime($this->report_starttime)) {
					$DAYS_OF_THE_MONTH = $TOTAL_DAYS_IN_MONTH;
				} else {
					$DAYS_OF_THE_MONTH = 0;
				}
				$TILL_DATE_QTY 			= ((!empty($TOTAL_DAYS_IN_MONTH))?round($SELECT_S_ROW->PROJECTION_QTY/$TOTAL_DAYS_IN_MONTH):0) * $DAYS_OF_THE_MONTH;
				$SURPLUS_DEFICIT 		= $SELECT_S_ROW->ACTUAL_QTY - $TILL_DATE_QTY;
				$SURPLUS_DEFICIT_PER 	= _FormatNumberV2((!empty($SURPLUS_DEFICIT) && !empty($TILL_DATE_QTY))?(($SURPLUS_DEFICIT/$TILL_DATE_QTY)*100):0);
				$REMAINING_TARGET 		= $SELECT_S_ROW->ACTUAL_QTY - $SELECT_S_ROW->PROJECTION_QTY;
				$REMAINING_TARGET_PER 	= _FormatNumberV2((!empty($REMAINING_TARGET) && !empty($SELECT_S_ROW->PROJECTION_QTY))?(($REMAINING_TARGET/$SELECT_S_ROW->PROJECTION_QTY)*100):0);
				if (!empty($SURPLUS_DEFICIT_PER)) {
					$SURPLUS_DEFICIT_PER 	= ($SURPLUS_DEFICIT_PER > 0)?"<font class=\"text-success text-bold\">".$SURPLUS_DEFICIT_PER."%</font>":"<font class=\"text-danger text-bold\">".$SURPLUS_DEFICIT_PER."%</font>";
				}
				if (!empty($REMAINING_TARGET_PER)) {
					$REMAINING_TARGET_PER 	= ($REMAINING_TARGET_PER > 0)?"<font class=\"text-success text-bold\">".$REMAINING_TARGET_PER."%</font>":"<font class=\"text-danger text-bold\">".$REMAINING_TARGET_PER."%</font>";
				}
				$arrResult[$SELECT_S_ROW->PRODUCT_ID][$SELECT_S_ROW->MRFID] = array("PROJECTION_QTY"=>_FormatNumberV2($SELECT_S_ROW->PROJECTION_QTY),
																					"TILL_DATE_QTY"=>_FormatNumberV2($TILL_DATE_QTY),
																					"ACTUAL_QTY"=>_FormatNumberV2($SELECT_S_ROW->ACTUAL_QTY),
																					"SURPLUS_DEFICIT"=>_FormatNumberV2($SURPLUS_DEFICIT),
																					"SURPLUS_DEFICIT_PER"=>($SURPLUS_DEFICIT_PER),
																					"REMAINING_TARGET"=>_FormatNumberV2($REMAINING_TARGET),
																					"REMAINING_TARGET_PER"=>($REMAINING_TARGET_PER));
				$TOTAL_PROJECTION_QTY += $SELECT_S_ROW->PROJECTION_QTY;
				$TOTAL_TILL_DATE_QTY += $TILL_DATE_QTY;
				$TOTAL_ACTUAL_QTY += $SELECT_S_ROW->ACTUAL_QTY;
			}

			$TOTAL_SURPLUS_DEFICIT 		= $TOTAL_ACTUAL_QTY - $TOTAL_TILL_DATE_QTY;
			$TOTAL_SURPLUS_DEFICIT_PER 	= _FormatNumberV2((!empty($TOTAL_SURPLUS_DEFICIT) && !empty($TOTAL_TILL_DATE_QTY))?(($TOTAL_SURPLUS_DEFICIT/$TOTAL_TILL_DATE_QTY)*100):0);
			$TOTAL_REMAINING_TARGET 	= $TOTAL_ACTUAL_QTY - $TOTAL_PROJECTION_QTY;
			$TOTAL_REMAINING_TARGET_PER = _FormatNumberV2((!empty($TOTAL_REMAINING_TARGET) && !empty($TOTAL_PROJECTION_QTY))?(($TOTAL_REMAINING_TARGET/$TOTAL_PROJECTION_QTY)*100):0);
			if (!empty($TOTAL_SURPLUS_DEFICIT_PER)) {
				$TOTAL_SURPLUS_DEFICIT_PER 	= ($TOTAL_SURPLUS_DEFICIT_PER > 0)?"<font class=\"text-success text-bold\">".$TOTAL_SURPLUS_DEFICIT_PER."%</font>":"<font class=\"text-danger text-bold\">".$TOTAL_SURPLUS_DEFICIT_PER."%</font>";
			}
			if (!empty($TOTAL_REMAINING_TARGET_PER)) {
				$TOTAL_REMAINING_TARGET_PER 	= ($TOTAL_REMAINING_TARGET_PER > 0)?"<font class=\"text-success text-bold\">".$TOTAL_REMAINING_TARGET_PER."%</font>":"<font class=\"text-danger text-bold\">".$TOTAL_REMAINING_TARGET_PER."%</font>";
			}
		}
		$arrBaseStations = BaseLocationMaster::where("status","A")->pluck("base_location_name","id")->toArray();
		return view("importcollection.production-projection-actual",["Page_Title"=>"Production Projection vs Actual Dashboard",
																	"arrBaseStations"=>$arrBaseStations,
																	"arrMRF"=>$arrMRF,
																	"basestation_id"=>$this->basestation_id,
																	"mrf_id"=>$this->mrf_id,
																	"DAYS_OF_THE_MONTH"=>$DAYS_OF_THE_MONTH,
																	"arrUniquePro"=>$arrUniquePro,
																	"arrResult"=>$arrResult,
																	"TOTAL_PROJECTION_QTY"=>$TOTAL_PROJECTION_QTY,
																	"TOTAL_TILL_DATE_QTY"=>$TOTAL_TILL_DATE_QTY,
																	"TOTAL_ACTUAL_QTY"=>$TOTAL_ACTUAL_QTY,
																	"TOTAL_SURPLUS_DEFICIT"=>$TOTAL_SURPLUS_DEFICIT,
																	"TOTAL_SURPLUS_DEFICIT_PER"=>$TOTAL_SURPLUS_DEFICIT_PER,
																	"TOTAL_REMAINING_TARGET"=>$TOTAL_REMAINING_TARGET,
																	"TOTAL_REMAINING_TARGET_PER"=>$TOTAL_REMAINING_TARGET_PER,
																	"daterange"=>$this->daterange,
																	"report_starttime"=>$this->report_starttime,
																	"report_endtime"=>$this->report_endtime]);
	}

	public function saveAmbajiPadYatraDetails(Request $request)
	{
		$this->SetVariables($request);
		$AmbajiPadYatraCollection 	= new AmbajiPadYatraCollection();
		$AmbajiPadYatraSorting 		= new AmbajiPadYatraSorting();
		if ($request->isMethod("post")) {
			if ($request->has("hdnaction") && $request->get("hdnaction") == "save") {
				if ($this->validateCollectionDetails($request,$AmbajiPadYatraCollection,$AmbajiPadYatraSorting)) {
					$AmbajiPadYatraCollection->saveAmbajiPadYatraDetails($request);
					$AmbajiPadYatraSorting->saveAmbajiPadYatraDetails($request);
					return redirect()->route('save-ambaji-pad-yatra')->with('message','Ambaji Pad Yatra Collection data updated successfully !!!');
				}
			} else {
				$AmbajiPadYatraCollection 	= $AmbajiPadYatraCollection->where("collection_dt",date("Y-m-d",strtotime($request->collection_dt)))->first();
				$AmbajiPadYatraSorting 		= $AmbajiPadYatraSorting->where("sorting_dt",date("Y-m-d",strtotime($request->collection_dt)))->first();
			}
		}
		if (empty($AmbajiPadYatraCollection)) {
			$AmbajiPadYatraCollection 					= new AmbajiPadYatraCollection();
			$AmbajiPadYatraCollection->collection_dt 	= date("Y-m-d",strtotime($request->collection_dt));
		}
		if (empty($AmbajiPadYatraSorting)) {
			$AmbajiPadYatraSorting = new AmbajiPadYatraSorting();
		}
		return view("importcollection.ambaji-pad-yatra",[	"Page_Title"=>"Ambaji Pad Yatra 2023",
															"ErroMessage"=>$this->errorMessage,
															"AmbajiPadYatraCollection"=>$AmbajiPadYatraCollection,
															"AmbajiPadYatraSorting"=>$AmbajiPadYatraSorting]);
}

	private function validateCollectionDetails($request,$AmbajiPadYatraCollection,$AmbajiPadYatraSorting)
	{
		$AmbajiPadYatraCollection->collection_dt 	= date("Y-m-d",strtotime($request->collection_dt));
		$AmbajiPadYatraCollection->collection_qty 	= ($request->has("collection_qty"))?floatval($request->collection_qty):0;
		if (empty($AmbajiPadYatraCollection->collection_dt)) {
			$this->errorMessage = "Collection date is required.";
			return false;
		} else if (strtotime($AmbajiPadYatraCollection->collection_dt) > strtotime(date("Y-m-d",strtotime("now")))) {
			$this->errorMessage = "Collection date cannot be greater than current date.";
			return false;
		}
		$AmbajiPadYatraSorting->paper				= ($request->has("paper"))?floatval($request->paper):0;
		$AmbajiPadYatraSorting->plastic				= ($request->has("plastic"))?floatval($request->plastic):0;
		$AmbajiPadYatraSorting->metal				= ($request->has("metal"))?floatval($request->metal):0;
		$AmbajiPadYatraSorting->mix_waste			= ($request->has("mix_waste"))?floatval($request->mix_waste):0;
		$AmbajiPadYatraSorting->non_recyclable		= ($request->has("non_recyclable"))?floatval($request->non_recyclable):0;
		$TotalSortedQty 							= ($AmbajiPadYatraSorting->paper + $AmbajiPadYatraSorting->plastic + $AmbajiPadYatraSorting->metal + $AmbajiPadYatraSorting->mix_waste + $AmbajiPadYatraSorting->non_recyclable);
		if ($AmbajiPadYatraCollection->collection_qty >= $TotalSortedQty) {
			return true;
		} else {
			$this->errorMessage = "Collection quantity must not be less than seggregated quantity.";
			return false;
		}
	}
}