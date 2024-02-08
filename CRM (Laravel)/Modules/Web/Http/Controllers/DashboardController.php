<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\DashboardMaster;
use App\Models\UserDashboardMapping;
use App\Models\WidgetMaster;
use App\Models\AdminUserRights;
use App\Models\GroupRightsTransaction;
use App\Models\ViewAppointmentList;
use App\Models\QuickLinksAccessLog;
use App\Models\SalesPlanPrediction;
use App\Models\MissedSaledBasedOnPrediction;
use App\Models\WmClientPurchaseOrders;
use App\Models\AdminUser;
use App\Models\WmSalesTargetMaster;
use App\Models\WmProductionReportMaster;
use App\Models\WmDepartment;
use App\Models\PortalImpactStats;
use App\Models\CCOFLocations;
use App\Models\CCOFMaster;
use App\Models\VGGS;
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
use DB;
class DashboardController extends LRBaseController
{
   /*
	Use     : Create & update Dashboard
	Author  : Axay Shah
	Date    : 29 Mar,2019
   */
	public function saveDashboard(Request $request){
	  try{
		$msg  = trans("message.RECORD_NOT_FOUND");
		$code = SUCCESS;
		$data = "";
		$data = DashboardMaster::saveDashboard($request);
		if($data){
			$msg = trans("message.RECORD_UPDATED");
		}
	}catch(\Exeption $e){
		$msg            = trans("message.SOMETHING_WENT_WRONG");
		$code           = SUCCESS;
	}
	return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
  }

  /*
	Use     : List Widget
	Author  : Axay Shah
	Date    : 29 Mar,2019
   */
  public function listWidget(Request $request)
  {
		try {
			$AdminRights   				= new AdminUserRights();
			$GroupRightsTransaction   	= new GroupRightsTransaction();
			$WidgetMaster  				= new WidgetMaster();
			$WidGet        				= $WidgetMaster->getTable();
			$msg  							= trans("message.RECORD_NOT_FOUND");
			$code 							= SUCCESS;

			$data 			= WidgetMaster::join($AdminRights->getTable()." as R","$WidGet.trn_id","=","R.trnid")
									->where("$WidGet.status","1")
									->where("R.adminuserid",Auth()->user()->adminuserid)
									->get();
			if(ROLE_WISE_TRN_FLAG) {
				$data 			= WidgetMaster::join($GroupRightsTransaction->getTable()." as R","$WidGet.trn_id","=","R.trn_id")
									->where("$WidGet.status","1")
									->where("R.group_id",Auth()->user()->user_type)
									->get();
			}
			if($data) {
				$msg = trans("message.RECORD_FOUND");
			}
		} catch(\Exeption $e) {
			$msg 	= trans("message.SOMETHING_WENT_WRONG");
			$code = SUCCESS;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : List Dashboard with widget
	Author  : Axay Shah
	Date    : 29 Mar,2019
   */
	public function listDashboard(Request $request){
	try{
		$msg  = trans("message.RECORD_NOT_FOUND");
		$code = SUCCESS;
		$data = UserDashboardMapping::listDashboard(Auth()->user()->adminuserid);
		if($data){
			$msg = trans("message.RECORD_FOUND");
		}
	}catch(\Exeption $e){
		$msg            = trans("message.SOMETHING_WENT_WRONG");
		$code           = SUCCESS;
	}
	return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}


	/*
	Use     : Get count of pending appointment assignment
	Author  : Axay Shah
	Date    : 30 Mar,2019
	*/
	public function getDashboardPendingAppointment(Request $request){
		try{
			$startTime  = date("Y-m-d",strtotime('-1 days', strtotime(date('Y-m-d H:i:s'))));
			$endTime    = date("Y-m-d",strtotime('+1 days', strtotime(date('Y-m-d H:i:s'))));
			/* SET PARAMETER */
			$params = array('params' => array(
				"para_status_id"        =>  APPOINTMENT_NOT_ASSIGNED,
				"partner_appointment"   =>  "N",
				"extra_pickup"          =>  "N",
				"appointment_from"      =>  $startTime,
				"appointment_to"        =>  $endTime
			));
			/* ADDING PARAMETER IN REQUEST PARAMETER*/
			$request->request->add($params);
			$msg    = trans("message.RECORD_NOT_FOUND");
			$code   = SUCCESS;
			$data   = ViewAppointmentList::searchAppointment($request);
			if(count($data) > 0){
				$msg = trans("message.RECORD_FOUND");
			}
		}catch(\Exeption $e){
			$msg            = trans("message.SOMETHING_WENT_WRONG");
			$code           = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get count of pending appointment assignment
	Author  : Axay Shah
	Date    : 30 Mar,2019
   */
	public function getDashboardRequestApporoval(Request $request){
	try{
		$startTime  = date("Y-m-d",strtotime('-1 days', strtotime(date('Y-m-d H:i:s'))));
		$endTime    = date("Y-m-d",strtotime('+1 days', strtotime(date('Y-m-d H:i:s'))));
		/* SET PARAMETER */
		$params = array('params' => array(
			"para_status_id"        =>  APPOINTMENT_NOT_ASSIGNED,
			"partner_appointment"   =>  "N",
			"extra_pickup"          =>  "N",
			"appointment_from"      =>  $startTime,
			"appointment_to"        =>  $endTime
		));
		/* ADDING PARAMETER IN REQUEST PARAMETER*/
		$request->request->add($params);
		$msg    = trans("message.RECORD_NOT_FOUND");
		$code   = SUCCESS;
		$data   = ViewAppointmentList::searchAppointment($request);
		if(count($data) > 0){
			$msg = trans("message.RECORD_FOUND");
		}
	}catch(\Exeption $e){
		$msg            = trans("message.SOMETHING_WENT_WRONG");
		$code           = INTERNAL_SERVER_ERROR;
	}
	return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Get count of pending appointment assignment
	Author  : Axay Shah
	Date    : 30 Mar,2019
   */
	public function getTodayAppointment(Request $request){
		try{
			$startTime  = date("Y-m-d",strtotime('-1 days', strtotime(date('Y-m-d H:i:s'))));
			$endTime    = date("Y-m-d",strtotime('+1 days', strtotime(date('Y-m-d H:i:s'))));
			/* SET PARAMETER */
			$params = array('params' => array(
				"para_status_id"        =>  APPOINTMENT_NOT_ASSIGNED,
				"partner_appointment"   =>  "N",
				"extra_pickup"          =>  "N",
				"appointment_from"      =>  $startTime,
				"appointment_to"        =>  $endTime
			));
			/* ADDING PARAMETER IN REQUEST PARAMETER*/
			$request->request->add($params);
			$msg    = trans("message.RECORD_NOT_FOUND");
			$code   = SUCCESS;
			$data   = ViewAppointmentList::searchAppointment($request);
			if(count($data) > 0){
				$msg = trans("message.RECORD_FOUND");
			}
		}catch(\Exeption $e){
			$msg            = trans("message.SOMETHING_WENT_WRONG");
			$code           = INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get count of pending appointment assignment
	Author  : Axay Shah
	Date    : 30 Mar,2019
	*/
	public function GetDetailsSummeryOfTodayAppointment(Request $request){
		$msg    = trans("message.RECORD_NOT_FOUND");
		$code   = SUCCESS;
		$data   = ViewAppointmentList::GetTodayAppointmentList($request);
		if(!empty($data)){
			$msg = trans("message.RECORD_FOUND");
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	 /*
	Use     : Get Count of BOP appointment
	Author  : Axay Shah
	Date    : 30 Sep,2019
	*/
	public function GetDetailsSummeryOfTodayBOPAppointment(Request $request){
		$msg    = trans("message.RECORD_NOT_FOUND");
		$code   = SUCCESS;
		$data   = ViewAppointmentList::GetTodayAppointmentList($request,true);
		if(!empty($data)){
			$msg = trans("message.RECORD_FOUND");
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get Quick Links Data
	Author  : Axay Shah
	Date    : 16 MAY,2022
	*/
	public function GetAccessLogByUser(Request $request)
	{
		$msg                        = trans("message.RECORD_NOT_FOUND");
		$code                       = SUCCESS;
		$adminuserid                = isset(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid : 0;
		$data                       = QuickLinksAccessLog::GetAccessLogByUser($adminuserid);
		$result1['header_name']     = "NEPRA Projects";
		$result1['result']          = PROJECT_LIST;
		$result2['header_name']     = "Quick Links";
		$result2['result']          = $data;
		$res                        = array();
		// $res[0]                  = $result1;
		$res[0] 					= $result2;
		if(!empty($data)) {
			$msg = trans("message.RECORD_FOUND");
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$res]);
	}

	/*
	Use 	: Get Dispatches Based on Predictive Analysis
	Author 	: Kalpak Prajapati
	Date 	: 23 May 2022
	*/
	public function getSalesPredictionWidget(Request $request)
	{
		$code   						= SUCCESS;
		$msg    						= trans("message.RECORD_NOT_FOUND");
		$arrResult['SALES_PREDICTION'] 	= SalesPlanPrediction::getDispatchPredictionWidget($request);
		$arrResult['WIDGET_TITLE'] 		= "SALES PREDICTION";
		$arrResult['REPORT_TITLE'] 		= "SALES PREDICTION DETAILS";
		if(!empty($arrResult['SALES_PREDICTION'])) {
			$msg = trans("message.RECORD_FOUND");
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$arrResult]);
	}

	/*
	Use 	: Get Missed Dispatches Based on Predictive Analysis
	Author 	: Kalpak Prajapati
	Date 	: 23 May 2022
	*/
	public function getMissedSalesPredictionWidget(Request $request)
	{
		$code   						= SUCCESS;
		$msg    						= trans("message.RECORD_NOT_FOUND");
		$arrResult['SALES_PREDICTION'] 	= MissedSaledBasedOnPrediction::getMissedDispatchBasedonPredictionWidget($request);
		$arrResult['WIDGET_TITLE'] 		= "MISSED SALES PLAN BASED ON PREDICTION";
		$arrResult['REPORT_TITLE'] 		= "MISSED SALES BASED ON PREDICTION";
		if(!empty($arrResult['SALES_PREDICTION'])) {
			$msg = trans("message.RECORD_FOUND");
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$arrResult]);
	}

	/*
	Use 	: Get Plant Client Material wise Dispatch Plan
	Author 	: Kalpak Prajapati
	Date    : 01 Sep 2022
	*/
	public function getPlantClientMaterialwiseDispatchPlan(Request $request)
	{
		$code   		= SUCCESS;
		$msg    		= trans("message.RECORD_FOUND");
		$arrResult 	= WmClientPurchaseOrders::getPlantClientMaterialwiseDispatchPlan($request);
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$arrResult]);
	}

	/*
	Use     : GP Anaysis Data
	Author  : Hardyesh Gupta
	Date    : 07 August,2023
	*/
	public function getGPAnalysisReportAPI(Request $request)
	{
		try
		{
			$this->report_starttime     = !empty($request->from_date )?$request->from_date." ".GLOBAL_START_TIME:"2022-03-01 ".GLOBAL_START_TIME;
			$this->report_endtime       = !empty($request->to_date)?$request->to_date." ".GLOBAL_END_TIME:date("Y-m-t",strtotime($this->report_starttime))." ".GLOBAL_END_TIME;
			$this->basestation_id       = (isset($request->basestation_id) && !empty($request->basestation_id)) ? $request->basestation_id: array();
			$this->mrf_id               = (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id: array();
			$msg  						= trans("message.RECORD_NOT_FOUND");
			$code 						= SUCCESS;
			$data 						= "";
			$arrResult              	= array();
			$arrMRF                 	= array();
			$arrBaseStations        	= array();
			$arrSales               	= array();
			$arrServices            	= array();
			$arrCNDetails           	= array();
			$arrDNDetails           	= array();
			$arrTransferSales       	= array();
			$arrInternalTransfer    	= array();
			$arrNetSales            	= array();
			$arrPurchase            	= array();
			$arrPurchaseCNDetails   	= array();
			$arrPurchaseDNDetails   	= array();
			$arrTransferPurchase    	= array();
			$arrInternalPurchase    	= array();
			$arrNetPurchase         	= array();
			$arrStockDetails        	= array();
			$arrCOGSDetails         	= array();
			$arrGPDetails           	= array();
			$arrGPPDetails          	= array();
			$arrOpeningStockDetails 	= array();
			$arrJobworkStockDetails 	= array();
			$arrClosingStockDetails 	= array();
			$arrStockInTransistDetails 	= array();
			$OpeningStockTotal 			= 0;
			$JobworkStockTotal 			= 0;
			$ClosingStockTotal 			= 0;
			$InTransistStockTotal 		= 0;

			if (!empty($this->basestation_id)) {
				$arrMRF = WmDepartment::where("status",1)->whereIn('base_location_id',$this->basestation_id)->pluck("department_name","id")->toArray();
			}

			$arrSales               = GPAnalysis::getSalesDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
			$arrServices            = GPAnalysis::getServiceDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
			$arrCNDetails           = GPAnalysis::getSalesServiceCNDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
			$arrDNDetails           = GPAnalysis::getSalesServiceDNDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
			$arrTransferSales       = GPAnalysis::getTransferDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id,true);
			$arrInternalTransfer    = GPAnalysis::getTransferDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id,false);
			$arrNetSales            = GPAnalysis::getNetSales($arrSales,$arrServices,$arrCNDetails,$arrDNDetails,$arrTransferSales,$arrInternalTransfer);
			$arrPurchase            = GPAnalysis::getPurchaseDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
			$arrPurchaseCNDetails   = GPAnalysis::getPurchaseCNDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
			$arrPurchaseDNDetails   = GPAnalysis::getPurchaseDNDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
			$arrTransferPurchase    = GPAnalysis::getPurchaseTransferDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id,true);
			$arrInternalPurchase    = GPAnalysis::getPurchaseTransferDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id,false);
			$arrNetPurchase         = GPAnalysis::getNetPurchase($arrPurchase,$arrPurchaseCNDetails,$arrPurchaseDNDetails,$arrTransferPurchase,$arrInternalPurchase);
			$arrStockDetails        = GPAnalysis::getStockValuationDetails($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
			$arrCOGSDetails         = GPAnalysis::getCOGSValue($arrNetSales,$arrNetPurchase,$arrStockDetails);
			$arrGPDetails           = GPAnalysis::getGPValue($arrNetSales,$arrCOGSDetails);
			$arrGPPDetails          = GPAnalysis::getGPPercentage($arrNetSales,$arrGPDetails);
			$arrBaseStations        = BaseLocationMaster::where("status","A")->pluck("base_location_name","id")->toArray();

			$arrTitleName 				= array('Particulars','MRF','AFR','CFM','Trading','EPR Service','EPR Advisory','EPR Tradex','Other Service','Total');
			$arrTitlekey 				= array('MRF','AFR','CFM','TRD','SERVICE','ADVISORY','TRADEX','OTHER');
			$arrSales 					= array('TITLE' => 'Sales','MRF'=>$arrSales['MRF_SALES'],'AFR'=>$arrSales['AFR_SALES'],'CFM'=>$arrSales['CFM_SALES'],'TRD'=>$arrSales['TRD_SALES'],'SERVICE'=>$arrSales['EPR_SERVICE'],'ADVISORY'=>$arrSales['EPR_ADVISORY'],'TRADEX'=>$arrSales['EPR_TRADEX'],'OTHER'=>$arrSales['OTHER_SERVICE'],'TOTAL'=>$arrSales['TOTAL']);
			$arrServices 				= array('TITLE' => 'Service','MRF'=>$arrServices['MRF_SALES'],'AFR'=>$arrServices['AFR_SALES'],'CFM'=>$arrServices['CFM_SALES'],'TRD'=>$arrServices['TRD_SALES'],'SERVICE'=>$arrServices['EPR_SERVICE'],'ADVISORY'=>$arrServices['EPR_ADVISORY'],'TRADEX'=>$arrServices['EPR_TRADEX'],'OTHER'=>$arrServices['OTHER_SERVICE'],'TOTAL'=>$arrServices['TOTAL']);
			$arrCNDetails 				= array('TITLE' => 'Credit Note','MRF'=>$arrCNDetails['MRF_CN'],'AFR'=>$arrCNDetails['AFR_CN'],'CFM'=>$arrCNDetails['CFM_CN'],'TRD'=>$arrCNDetails['TRD_CN'],'SERVICE'=>$arrCNDetails['SERVICE_CN'],'ADVISORY'=>$arrCNDetails['ADVISORY_CN'],'TRADEX'=>$arrCNDetails['TRADEX_CN'],'OTHER'=>$arrCNDetails['OTHER_CN'],'TOTAL'=>$arrCNDetails['TOTAL']);
			$arrDNDetails 				= array('TITLE' => 'Debit Note','MRF'=>$arrDNDetails['MRF_DN'],'AFR'=>$arrDNDetails['AFR_DN'],'CFM'=>$arrDNDetails['CFM_DN'],'TRD'=>$arrDNDetails['TRD_DN'],'SERVICE'=>$arrDNDetails['SERVICE_DN'],'ADVISORY'=>$arrDNDetails['ADVISORY_DN'],'TRADEX'=>$arrDNDetails['TRADEX_DN'],'OTHER'=>$arrDNDetails['OTHER_DN'],'TOTAL'=>$arrDNDetails['TOTAL']);
			$arrTransferSales 			= array('TITLE' => 'Inter Branch Sales','MRF'=>$arrTransferSales['TRANSFER_AMOUNT'],'AFR'=>0,'TRD'=>0,'SERVICE'=>0,'ADVISORY'=>0,'TRADEX'=>0,'OTHER'=>0,'TOTAL'=>$arrTransferSales['TOTAL']);
			$arrInternalTransfer 		= array('TITLE' => 'Inter Branch Transfer','MRF'=>$arrInternalTransfer['TRANSFER_AMOUNT'],'AFR'=>0,'TRD'=>0,'SERVICE'=>0,'ADVISORY'=>0,'TRADEX'=>0,'OTHER'=>0,'TOTAL'=>$arrInternalTransfer['TOTAL']);
			$arrNetSales 				= array('TITLE' => 'Net Sales','MRF'=>$arrNetSales['MRF'],'AFR'=>$arrNetSales['AFR'],'CFM'=>$arrNetSales['CFM'],'TRD'=>$arrNetSales['TRD'],'SERVICE'=>$arrNetSales['SERVICE'],'ADVISORY'=>$arrNetSales['ADVISORY'],'TRADEX'=>$arrNetSales['TRADEX'],'OTHER'=>$arrNetSales['OTHER_SERVICE'],'TOTAL'=>$arrNetSales['TOTAL']);
			$arrPurchase 				= array('TITLE' => 'Purchase','MRF'=>$arrPurchase['MRF_PURCHASE'],'AFR'=>$arrPurchase['AFR_PURCHASE'],'CFM'=>$arrPurchase['CFM_PURCHASE'],'TRD'=>$arrPurchase['TRD_PURCHASE'],'SERVICE'=>$arrPurchase['EPR_SERVICE'],'ADVISORY'=>$arrPurchase['EPR_ADVISORY'],'TRADEX'=>$arrPurchase['EPR_TRADEX'],'OTHER'=>$arrPurchase['OTHER_SERVICE'],'TOTAL'=>$arrPurchase['TOTAL']);
			$arrPurchaseCNDetails 		= array('TITLE' => 'Credit Note','MRF'=>$arrPurchaseCNDetails['MRF_CN'],'AFR'=>$arrPurchaseCNDetails['AFR_CN'],'CFM'=>$arrPurchaseCNDetails['CFM_CN'],'TRD'=>$arrPurchaseCNDetails['TRD_CN'],'SERVICE'=>$arrPurchaseCNDetails['SERVICE_CN'],'ADVISORY'=>$arrPurchaseCNDetails['ADVISORY_CN'],'TRADEX'=>$arrPurchaseCNDetails['TRADEX_CN'],'OTHER'=>$arrPurchaseCNDetails['OTHER_CN'],'TOTAL'=>$arrPurchaseCNDetails['TOTAL']);
			$arrPurchaseDNDetails 		= array('TITLE' => 'Debit Note','MRF'=>$arrPurchaseDNDetails['MRF_DN'],'AFR'=>$arrPurchaseDNDetails['AFR_DN'],'CFM'=>$arrPurchaseDNDetails['CFM_DN'],'TRD'=>$arrPurchaseDNDetails['TRD_DN'],'SERVICE'=>$arrPurchaseDNDetails['SERVICE_DN'],'ADVISORY'=>$arrPurchaseDNDetails['ADVISORY_DN'],'TRADEX'=>$arrPurchaseDNDetails['TRADEX_DN'],'OTHER'=>$arrPurchaseDNDetails['OTHER_DN'],'TOTAL'=>$arrPurchaseDNDetails['TOTAL']);
			$arrTransferPurchase 		= array('TITLE' => 'Inter Branch Purchase','MRF'=>$arrTransferPurchase['TRANSFER_AMOUNT'],'AFR'=>0,'TRD'=>0,'SERVICE'=>0,'ADVISORY'=>0,'TRADEX'=>0,'OTHER'=>0,'TOTAL'=>$arrTransferPurchase['TOTAL']);
			$arrInternalPurchase 		= array('TITLE' => 'Inter Branch Transfer','MRF'=>$arrInternalPurchase['TRANSFER_AMOUNT'],'AFR'=>0,'TRD'=>0,'SERVICE'=>0,'ADVISORY'=>0,'TRADEX'=>0,'OTHER'=>0,'TOTAL'=>$arrInternalPurchase['TOTAL']);
			$arrNetPurchase 			= array('TITLE' => 'Net Purchase','MRF'=>$arrNetPurchase['MRF'],'AFR'=>$arrNetPurchase['AFR'],'CFM'=>$arrNetPurchase['CFM'],'TRD'=>$arrNetPurchase['TRD'],'SERVICE'=>$arrNetPurchase['SERVICE'],'ADVISORY'=>$arrNetPurchase['ADVISORY'],'TRADEX'=>$arrNetPurchase['TRADEX'],'OTHER'=>$arrNetPurchase['OTHER_SERVICE'],'TOTAL'=>$arrNetPurchase['TOTAL']);
			$arrCOGSDetails 			= array('TITLE' => 'COGS','MRF'=>$arrCOGSDetails['MRF'],'AFR'=>$arrCOGSDetails['AFR'],'CFM'=>$arrCOGSDetails['CFM'],'TRD'=>$arrCOGSDetails['TRD'],'SERVICE'=>$arrCOGSDetails['SERVICE'],'ADVISORY'=>$arrCOGSDetails['ADVISORY'],'TRADEX'=>$arrCOGSDetails['TRADEX'],'OTHER'=>$arrCOGSDetails['OTHER'],'TOTAL'=>$arrCOGSDetails['TOTAL']);
			$arrGPDetails 				= array('TITLE' => 'GP','MRF'=>$arrGPDetails['MRF'],'AFR'=>$arrGPDetails['AFR'],'CFM'=>$arrGPDetails['CFM'],'TRD'=>$arrGPDetails['TRD'],'SERVICE'=>$arrGPDetails['SERVICE'],'ADVISORY'=>$arrGPDetails['ADVISORY'],'TRADEX'=>$arrGPDetails['TRADEX'],'OTHER'=>$arrGPDetails['OTHER'],'TOTAL'=>$arrGPDetails['TOTAL']);
			$arrGPPDetails 				= array('TITLE' => 'GP%','MRF'=>$arrGPPDetails['MRF'],'AFR'=>$arrGPPDetails['AFR'],'CFM'=>$arrGPPDetails['CFM'],'TRD'=>$arrGPPDetails['TRD'],'SERVICE'=>$arrGPPDetails['SERVICE'],'ADVISORY'=>$arrGPPDetails['ADVISORY'],'TRADEX'=>$arrGPPDetails['TRADEX'],'OTHER'=>$arrGPPDetails['OTHER'],'TOTAL'=>$arrGPPDetails['TOTAL']);
			$arrAFRStockDetails 		= array('OPENING_STOCK_VAL' => 0,'CLOSING_STOCK_VAL' => 0,'JOBWORK_VAL' => 0,'INTRANSIT_VALUE' => 0);
			$arrCFMStockDetails 		= array('OPENING_STOCK_VAL' => 0,'CLOSING_STOCK_VAL' => 0,'JOBWORK_VAL' => 0,'INTRANSIT_VALUE' => 0);
			$arrTRDStockDetails 		= array('OPENING_STOCK_VAL' => 0,'CLOSING_STOCK_VAL' => 0,'JOBWORK_VAL' => 0,'INTRANSIT_VALUE' => 0);
			$arrServiceStockDetails 	= array('OPENING_STOCK_VAL' => 0,'CLOSING_STOCK_VAL' => 0,'JOBWORK_VAL' => 0,'INTRANSIT_VALUE' => 0);
			$arrAdvisoryStockDetails	= array('OPENING_STOCK_VAL' => 0,'CLOSING_STOCK_VAL' => 0,'JOBWORK_VAL' => 0,'INTRANSIT_VALUE' => 0);
			$arrTradexStockDetails 		= array('OPENING_STOCK_VAL' => 0,'CLOSING_STOCK_VAL' => 0,'JOBWORK_VAL' => 0,'INTRANSIT_VALUE' => 0);
			$arrOtherStockDetails 		= array('OPENING_STOCK_VAL' => 0,'CLOSING_STOCK_VAL' => 0,'JOBWORK_VAL' => 0,'INTRANSIT_VALUE' => 0);
			$StockArray 				= array($arrStockDetails,$arrAFRStockDetails,$arrCFMStockDetails,$arrTRDStockDetails,$arrServiceStockDetails,$arrAdvisoryStockDetails,$arrTradexStockDetails,$arrOtherStockDetails);

			foreach($StockArray as $row => $innerArray) {
			  foreach($innerArray as $innerRow => $value) {
					$value = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
					if($innerRow == "OPENING_STOCK_VAL") {
						$OpeningStockTotal 	= $OpeningStockTotal + $value;
					}
					if($innerRow == "CLOSING_STOCK_VAL") {
						$ClosingStockTotal 	= $ClosingStockTotal + $value;
					}
					if($innerRow == "JOBWORK_VAL") {
						$JobworkStockTotal = $JobworkStockTotal + $value;
					}
					if($innerRow == "INTRANSIT_VALUE") {
						$InTransistStockTotal 	= $InTransistStockTotal + $value;
					}
			  }
			}
			$OpeningStockTotal 		= GPAnalysis::NumberFormat($OpeningStockTotal);
			$ClosingStockTotal 		= GPAnalysis::NumberFormat($ClosingStockTotal);
			$JobworkStockTotal 		= GPAnalysis::NumberFormat($JobworkStockTotal);
			$InTransistStockTotal 	= GPAnalysis::NumberFormat($InTransistStockTotal);

			foreach($StockArray as $row => $innerArray) {
				foreach($innerArray as $innerRow => $value) {
					if($innerRow == "OPENING_STOCK_VAL") {
						array_push($arrOpeningStockDetails,$value);
					}
					if($innerRow == "CLOSING_STOCK_VAL") {
						array_push($arrClosingStockDetails,$value);
					}
					if($innerRow == "JOBWORK_VAL") {
						array_push($arrJobworkStockDetails,$value);
					}
					if($innerRow == "INTRANSIT_VALUE") {
						array_push($arrStockInTransistDetails,$value);
					}
				}
			}
			$arrOpeningStockDetails 			= array_combine($arrTitlekey,$arrOpeningStockDetails);
			$arrJobworkStockDetails 			= array_combine($arrTitlekey,$arrJobworkStockDetails);
			$arrClosingStockDetails 			= array_combine($arrTitlekey,$arrClosingStockDetails);
			$arrStockInTransistDetails 			= array_combine($arrTitlekey,$arrStockInTransistDetails);
			$arrOpeningStockDetails 			= array('TITLE'=>'Opening Stock') 	+ $arrOpeningStockDetails 		+ array("TOTAL"=>$OpeningStockTotal);
			$arrClosingStockDetails 			= array('TITLE'=>'Closing Stock') 	+ $arrClosingStockDetails 		+ array("TOTAL"=>$ClosingStockTotal);
			$arrJobworkStockDetails 			= array('TITLE'=>'Jobwork Stock') 	+ $arrJobworkStockDetails 		+ array("TOTAL"=>$JobworkStockTotal);
			$arrStockInTransistDetails 			= array('TITLE'=>'Stock-in-Transit')+ $arrStockInTransistDetails 	+ array("TOTAL"=>$InTransistStockTotal);
			$gp_array['title_name'] 			= $arrTitleName;
			$gp_array['sales'] 					= array($arrSales,$arrServices,$arrCNDetails,$arrDNDetails,$arrTransferSales,$arrInternalTransfer);
			$gp_array['net_sales'] 				= $arrNetSales;
			$gp_array['purchase'] 				= array($arrPurchase,$arrPurchaseCNDetails,$arrPurchaseDNDetails,$arrTransferPurchase,$arrInternalPurchase);
			$gp_array['net_purchase'] 			= $arrNetPurchase;
			$gp_array['stock'] 					= array($arrOpeningStockDetails,$arrJobworkStockDetails,$arrClosingStockDetails,$arrStockInTransistDetails);
			$gp_array['cogs'] 					= $arrCOGSDetails;
			$gp_array['gp_value'] 				= $arrGPDetails;
			$gp_array['gp_percent'] 			= $arrGPPDetails;
			$arrResult['arrMRF']            	= $arrMRF;
			$arrResult['basestation_id']    	= $this->basestation_id;
			$arrResult['mrf_id']            	= $this->mrf_id;
			$arrResult['report_starttime']  	= $this->report_starttime;
			$arrResult['report_endtime']    	= $this->report_endtime;
			$arrResult['gp_analysis_data']   	= $gp_array;
			$arrResult['arrBaseStations']    	= $arrBaseStations;
			if($arrResult) {
				$msg = trans("message.RECORD_FOUND");
			}
		} catch(\Exeption $e) {
			$msg            = trans("message.SOMETHING_WENT_WRONG");
			$code           = SUCCESS;
			$arrResult 		= array();
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$arrResult]);
	}

	/**
	* @name getProjectionVsProductionVsActualReport
	* @uses Widget for Projection Vs Production Vs Actual Executions Report
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-09-09
	* @return string json
	*/
	public function getProjectionVsProductionVsActualReportV1(Request $request)
	{
		try
		{
			$this->report_starttime	= !empty($request->from_date)?$request->from_date:"2022-03-01 ".GLOBAL_START_TIME;
			$this->report_endtime 	= !empty($request->to_date)?$request->to_date:date("Y-m-t",strtotime($this->report_starttime))." ".GLOBAL_END_TIME;
			$this->basestation_id 	= (isset($request->basestation_id) && !empty($request->basestation_id)) ? $request->basestation_id: array();
			$this->mrf_id 				= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id: array();
			$this->mrf_id 				= (!is_array($this->mrf_id)?explode(",",$this->mrf_id):$this->mrf_id);
			$arrMRFID 					= array(0);
			$arrMRF 						= array();
			if (!empty($this->mrf_id)) {
				$arrMRF 	= WmDepartment::where("status",1)
								->select("department_name","id")
								->whereIn('id',$this->mrf_id)
								->where('is_virtual',0)
								->where('is_service_mrf',0)
								->get()
								->toArray();
				if (!empty($arrMRF)) {
					foreach ($arrMRF as $arrMRFRow) {
						array_push($arrMRFID, $arrMRFRow['id']);
					}
				}
			} else if (!empty($this->basestation_id)) {
				$arrMRF 	= WmDepartment::where("status",1)
								->select("department_name","id")
								->whereIn('base_location_id',$this->basestation_id)
								->where('is_virtual',0)
								->where('is_service_mrf',0)
								->get()
								->toArray();
				if (!empty($arrMRF)) {
					foreach ($arrMRF as $arrMRFRow) {
						array_push($arrMRFID, $arrMRFRow['id']);
					}
				}
			}
			$MONTH 		= date("m",strtotime($this->report_starttime));
			$YEAR 		= date("Y",strtotime($this->report_starttime));
			$starttime 	= date("Y-m-d",strtotime($this->report_starttime));
			$endtime 	= date("Y-m-d",strtotime($this->report_endtime));
			$endtime 	= (strtotime($endtime) > strtotime(date("Y-m-d"))?date("Y-m-d"):$endtime);
			$IsToday 	= (strtotime($endtime) == strtotime(date("Y-m-d")))?1:0;
			$SELECT_SQL = "SELECT DISTINCT CONCAT(wm_product_master.id,'|',wm_production_report_master.mrf_id) AS U_I_D,
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
								) END AS ACTUAL_QTY,
								getMRFSalesQuantityForProduct('$starttime','$endtime',wm_product_master.id,wm_department.id) as MRF_Sales_Qty,
								getProductCurrentStock(wm_product_master.id,'$endtime',wm_department.id,$IsToday,".PRODUCT_SALES.") as MRF_Stock
								FROM wm_product_master
								LEFT JOIN wm_processed_product_master ON wm_product_master.id = wm_processed_product_master.sales_product_id
								LEFT JOIN wm_production_report_master ON wm_production_report_master.id = wm_processed_product_master.production_id
								LEFT JOIN wm_department ON wm_department.id = wm_production_report_master.mrf_id
								WHERE wm_production_report_master.production_date BETWEEN '$this->report_starttime' AND '$this->report_endtime'
								AND wm_department.id IN (".implode(",",$arrMRFID).")
								AND wm_product_master.recyclable = 1
								GROUP BY wm_processed_product_master.id, wm_production_report_master.mrf_id
								ORDER BY MRF_NAME ASC, PRODUCT_NAME ASC";
			$SELECT_S_RES 						= DB::connection('master_database')->select($SELECT_SQL);
			$arrUniquePro 						= array();
			$arrUniquePID 						= array();
			$arrUniqueMRF 						= array();
			$DAYS_OF_THE_MONTH 				= 0;
			$arrResult 							= array();
			$TOTAL_PROJECTION_QTY 			= 0;
			$TOTAL_TILL_DATE_QTY 			= 0;
			$TOTAL_ACTUAL_QTY 				= 0;
			$TOTAL_SURPLUS_DEFICIT 			= 0;
			$TOTAL_SURPLUS_DEFICIT_PER 	= 0;
			$TOTAL_REMAINING_TARGET 		= 0;
			$TOTAL_REMAINING_TARGET_PER 	= 0;
			$TOTAL_MRF_SALES_QTY 			= 0;
			$TOTAL_MRF_STOCK 					= 0;
			if (!empty($SELECT_S_RES)) {
				foreach ($SELECT_S_RES as $SELECT_S_ROW) {
					if (!in_array($SELECT_S_ROW->PRODUCT_ID,$arrUniquePID)) {
						array_push($arrUniquePID,$SELECT_S_ROW->PRODUCT_ID);
						array_push($arrUniquePro,array("PRODUCT_ID"=>$SELECT_S_ROW->PRODUCT_ID,"PRODUCT_NAME"=>$SELECT_S_ROW->PRODUCT_NAME));
					}
					if (date("m",strtotime("now")) == date("m",strtotime($this->report_starttime)) && date("Y",strtotime("now")) == date("Y",strtotime($this->report_starttime))) {
						$STARTDATE 	= date("Y-m-d",strtotime($this->report_starttime));
						if (strtotime(date("Y-m-d",strtotime("now"))) > strtotime(date("Y-m-d",strtotime($this->report_endtime)))) {
							$ENDDATE 	= date("Y-m-d",strtotime($this->report_endtime));
						} else {
							$ENDDATE 	= date("Y-m-d",strtotime("yesterday"));
						}
					} else{
						$STARTDATE 	= date("Y-m-d",strtotime($this->report_starttime));
						$ENDDATE 	= date("Y-m-d",strtotime($this->report_endtime));
					}
					if (!in_array($SELECT_S_ROW->MRFID,$arrUniqueMRF)) {
						$PRODUCTION_DAYS_SQL = "SELECT COUNT(DISTINCT production_date) AS CNT
														FROM wm_production_report_master
														WHERE mrf_id = ".intval($SELECT_S_ROW->MRFID)."
														AND production_date BETWEEN '".$STARTDATE."' AND '".$ENDDATE."'";
						$PRODUCTION_DAYS_RES = DB::connection('master_database')->select($PRODUCTION_DAYS_SQL);
						$TOTAL_DAYS_IN_MONTH = isset($PRODUCTION_DAYS_RES[0]->CNT)?$PRODUCTION_DAYS_RES[0]->CNT:0;
						$arrUniqueMRF[$SELECT_S_ROW->MRFID] = $TOTAL_DAYS_IN_MONTH;
					} else {
						$TOTAL_DAYS_IN_MONTH = $arrUniqueMRF[$SELECT_S_ROW->MRFID];
					}
					$DAYS_OF_THE_MONTH 		= $TOTAL_DAYS_IN_MONTH;
					$TOTALDAYSINMONTH 		= date("t",strtotime($STARTDATE));
					$TILL_DATE_QTY 			= ((!empty($TOTALDAYSINMONTH) && !empty($SELECT_S_ROW->PROJECTION_QTY))?round($SELECT_S_ROW->PROJECTION_QTY/$TOTALDAYSINMONTH):0) * $TOTAL_DAYS_IN_MONTH;
					$SURPLUS_DEFICIT 			= $SELECT_S_ROW->ACTUAL_QTY - $TILL_DATE_QTY;
					$SURPLUS_DEFICIT_PER 	= _FormatNumberV2((!empty($SURPLUS_DEFICIT) && !empty($TILL_DATE_QTY))?(($SURPLUS_DEFICIT/$TILL_DATE_QTY)*100):0);
					$REMAINING_TARGET 		= $SELECT_S_ROW->ACTUAL_QTY - $SELECT_S_ROW->PROJECTION_QTY;
					$REMAINING_TARGET_PER 	= _FormatNumberV2((!empty($REMAINING_TARGET) && !empty($SELECT_S_ROW->PROJECTION_QTY))?(($REMAINING_TARGET/$SELECT_S_ROW->PROJECTION_QTY)*100):0);
					if (!empty($SURPLUS_DEFICIT_PER)) {
						$SURPLUS_DEFICIT_PER 	= ($SURPLUS_DEFICIT_PER > 0)?"<font class=\"text-success text-bold\">".$SURPLUS_DEFICIT_PER."%</font>":"<font class=\"text-danger text-bold\">".$SURPLUS_DEFICIT_PER."%</font>";
					}
					if (!empty($REMAINING_TARGET_PER)) {
						$REMAINING_TARGET_PER 	= ($REMAINING_TARGET_PER > 0)?"<font class=\"text-success text-bold\">".$REMAINING_TARGET_PER."%</font>":"<font class=\"text-danger text-bold\">".$REMAINING_TARGET_PER."%</font>";
					}
					$MRF_SALES_QTY = !empty($SELECT_S_ROW->MRF_Sales_Qty)?$SELECT_S_ROW->MRF_Sales_Qty:0;
					$MRF_STOCK 		= !empty($SELECT_S_ROW->MRF_Stock)?$SELECT_S_ROW->MRF_Stock:0;
					$arrResult[$SELECT_S_ROW->PRODUCT_ID][$SELECT_S_ROW->MRFID] = array(	"PROJECTION_QTY"=>_FormatNumberV2($SELECT_S_ROW->PROJECTION_QTY),
																												"TILL_DATE_QTY"=>_FormatNumberV2($TILL_DATE_QTY),
																												"ACTUAL_QTY"=>_FormatNumberV2($SELECT_S_ROW->ACTUAL_QTY),
																												"SURPLUS_DEFICIT"=>_FormatNumberV2($SURPLUS_DEFICIT),
																												"SURPLUS_DEFICIT_PER"=>($SURPLUS_DEFICIT_PER),
																												"REMAINING_TARGET"=>_FormatNumberV2($REMAINING_TARGET),
																												"REMAINING_TARGET_PER"=>($REMAINING_TARGET_PER),
																												"MRF_SALES_QTY"=>_FormatNumberV2($MRF_SALES_QTY),
																												"MRF_STOCK"=>_FormatNumberV2($MRF_STOCK));
					$TOTAL_PROJECTION_QTY += $SELECT_S_ROW->PROJECTION_QTY;
					$TOTAL_TILL_DATE_QTY += $TILL_DATE_QTY;
					$TOTAL_ACTUAL_QTY += $SELECT_S_ROW->ACTUAL_QTY;
					$TOTAL_MRF_SALES_QTY += $MRF_SALES_QTY;
					$TOTAL_MRF_STOCK += $MRF_STOCK;
				}

				$TOTAL_SURPLUS_DEFICIT 			= $TOTAL_ACTUAL_QTY - $TOTAL_TILL_DATE_QTY;
				$TOTAL_SURPLUS_DEFICIT_PER 	= _FormatNumberV2((!empty($TOTAL_SURPLUS_DEFICIT) && !empty($TOTAL_TILL_DATE_QTY))?(($TOTAL_SURPLUS_DEFICIT/$TOTAL_TILL_DATE_QTY)*100):0);
				$TOTAL_REMAINING_TARGET 		= $TOTAL_ACTUAL_QTY - $TOTAL_PROJECTION_QTY;
				$TOTAL_REMAINING_TARGET_PER 	= _FormatNumberV2((!empty($TOTAL_REMAINING_TARGET) && !empty($TOTAL_PROJECTION_QTY))?(($TOTAL_REMAINING_TARGET/$TOTAL_PROJECTION_QTY)*100):0);
				if (!empty($TOTAL_SURPLUS_DEFICIT_PER)) {
					$TOTAL_SURPLUS_DEFICIT_PER 	= ($TOTAL_SURPLUS_DEFICIT_PER > 0)?"<font class=\"text-success text-bold\">".$TOTAL_SURPLUS_DEFICIT_PER."%</font>":"<font class=\"text-danger text-bold\">".$TOTAL_SURPLUS_DEFICIT_PER."%</font>";
				}
				if (!empty($TOTAL_REMAINING_TARGET_PER)) {
					$TOTAL_REMAINING_TARGET_PER 	= ($TOTAL_REMAINING_TARGET_PER > 0)?"<font class=\"text-success text-bold\">".$TOTAL_REMAINING_TARGET_PER."%</font>":"<font class=\"text-danger text-bold\">".$TOTAL_REMAINING_TARGET_PER."%</font>";
				}
			}
			$code 														= SUCCESS;
			$msg 															= trans("message.RECORD_FOUND");
			$arrReturn['ReportData']['arrUniquePro'] 			= $arrUniquePro;
			$arrReturn['ReportData']['arrMRF'] 					= $arrMRF;
			$arrReturn['ReportData']['ReportRows'] 			= $arrResult;
			$arrReturn['ReportData']['DAYS_OF_THE_MONTH'] 	= (!empty($arrUniqueMRF))?max($arrUniqueMRF):$DAYS_OF_THE_MONTH;
			$arrReturn['ReportData']['Page_Title'] 			= "Production Projection Vs Actual";
			$arrReturn['ReportData']['TOTAL_ROW'] 				= array(	"TOTAL_PROJECTION_QTY"=>_FormatNumberV2($TOTAL_PROJECTION_QTY),
																						"TOTAL_TILL_DATE_QTY"=>_FormatNumberV2($TOTAL_TILL_DATE_QTY),
																						"TOTAL_ACTUAL_QTY"=>_FormatNumberV2($TOTAL_ACTUAL_QTY),
																						"TOTAL_SURPLUS_DEFICIT"=>_FormatNumberV2($TOTAL_SURPLUS_DEFICIT),
																						"TOTAL_SURPLUS_DEFICIT_PER"=>$TOTAL_SURPLUS_DEFICIT_PER,
																						"TOTAL_REMAINING_TARGET"=>_FormatNumberV2($TOTAL_REMAINING_TARGET),
																						"TOTAL_REMAINING_TARGET_PER"=>$TOTAL_REMAINING_TARGET_PER,
																						"TOTAL_MRF_SALES_QTY"=>_FormatNumberV2($TOTAL_MRF_SALES_QTY),
																						"TOTAL_MRF_STOCK"=>_FormatNumberV2($TOTAL_MRF_STOCK));
		} catch(\Exeption $e) {
			$msg 												= trans("message.SOMETHING_WENT_WRONG");
			$code 											= SUCCESS;
			$arrReturn['ReportData']['Page_Title'] = "Production Projection Vs Actual";
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$arrReturn]);
	}



	/**
	* @name getProjectionVsProductionVsActualReport
	* @uses Widget for Projection Vs Production Vs Actual Executions Report
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-09-09
	* @return string json
	*/
	public function getProjectionVsProductionVsActualReport(Request $request)
	{
		try
		{
			$this->report_starttime	= !empty($request->from_date)?$request->from_date:"2022-03-01 ".GLOBAL_START_TIME;
			$this->report_endtime 	= !empty($request->to_date)?$request->to_date:date("Y-m-t",strtotime($this->report_starttime))." ".GLOBAL_END_TIME;
			$this->basestation_id 	= (isset($request->basestation_id) && !empty($request->basestation_id)) ? $request->basestation_id: array();
			$this->mrf_id 				= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id: array();
			$this->mrf_id 				= (!is_array($this->mrf_id)?explode(",",$this->mrf_id):$this->mrf_id);
			$arrMRFID 					= array(0);
			$arrMRF 						= array();
			$arrMRFName 				= array();
			if (!empty($this->mrf_id)) {
				$arrMRF 	= WmDepartment::where("status",1)
								->select("department_name","id")
								->whereIn('id',$this->mrf_id)
								->where('is_virtual',0)
								->where('is_service_mrf',0)
								->orderBy("department_name","ASC")
								->get()
								->toArray();
				if (!empty($arrMRF)) {
					foreach ($arrMRF as $arrMRFRow) {
						array_push($arrMRFID, $arrMRFRow['id']);
						$arrMRFName[$arrMRFRow['id']] = $arrMRFRow['department_name'];
					}
				}
			} else if (!empty($this->basestation_id)) {
				$arrMRF 	= WmDepartment::where("status",1)
								->select("department_name","id")
								->whereIn('base_location_id',$this->basestation_id)
								->where('is_virtual',0)
								->where('is_service_mrf',0)
								->orderBy("department_name","ASC")
								->get()
								->toArray();
				if (!empty($arrMRF)) {
					foreach ($arrMRF as $arrMRFRow) {
						array_push($arrMRFID, $arrMRFRow['id']);
						$arrMRFName[$arrMRFRow['id']] = $arrMRFRow['department_name'];
					}
				}
			}
			$MONTH 								= date("m",strtotime($this->report_starttime));
			$YEAR 								= date("Y",strtotime($this->report_starttime));
			$starttime 							= date("Y-m-d",strtotime($this->report_starttime));
			$endtime 							= date("Y-m-d",strtotime($this->report_endtime));
			$endtime 							= (strtotime($endtime) > strtotime(date("Y-m-d"))?date("Y-m-d"):$endtime);
			$IsToday 							= (strtotime($endtime) == strtotime(date("Y-m-d")))?1:0;
			$arrUniquePro 						= array();
			$arrUniquePID 						= array();
			$arrUniqueMRF 						= array();
			$DAYS_OF_THE_MONTH 				= 0;
			$arrResult 							= array();
			$TOTAL_PROJECTION_QTY 			= 0;
			$TOTAL_TILL_DATE_QTY 			= 0;
			$TOTAL_ACTUAL_QTY 				= 0;
			$TOTAL_SURPLUS_DEFICIT 			= 0;
			$TOTAL_SURPLUS_DEFICIT_PER 	= 0;
			$TOTAL_REMAINING_TARGET 		= 0;
			$TOTAL_REMAINING_TARGET_PER 	= 0;
			$TOTAL_MRF_SALES_QTY 			= 0;
			$TOTAL_MRF_STOCK 					= 0;
			foreach ($arrMRFID as $MRFID)
			{
				$MRF_NAME 		= isset($arrMRFName[$MRFID])?$arrMRFName[$MRFID]:"-";
				$SELECT_SQL 	= "SELECT wm_product_master.id as PRODUCT_ID,
										'$MRFID' as MRFID,
										wm_product_master.title AS PRODUCT_NAME,
										'$MRF_NAME' as MRF_NAME,
										CASE WHEN 1=1 THEN
										(
											SELECT wm_projection_plan.projection_qty
											FROM wm_projection_plan
											WHERE wm_projection_plan.product_id = wm_product_master.id
											AND wm_projection_plan.month = $MONTH
											AND wm_projection_plan.year = $YEAR
											AND wm_projection_plan.mrf_id = '$MRFID'
											GROUP BY wm_projection_plan.product_id
										) END AS PROJECTION_QTY,
										CASE WHEN 1=1 THEN
										(
											SELECT SUM(wm_processed_product_master.qty)
											FROM wm_processed_product_master
											LEFT JOIN wm_production_report_master ON wm_production_report_master.id = wm_processed_product_master.production_id
											WHERE wm_processed_product_master.sales_product_id = wm_product_master.id
											AND wm_production_report_master.mrf_id = '$MRFID'
											AND wm_production_report_master.production_date BETWEEN '$this->report_starttime' AND '$this->report_endtime'
											AND wm_production_report_master.paid_collection = 0
											GROUP BY wm_processed_product_master.sales_product_id
										) END AS ACTUAL_QTY,
										getMRFSalesQuantityForProduct('$starttime','$endtime',wm_product_master.id,'$MRFID') as MRF_Sales_Qty,
										getProductCurrentStock(wm_product_master.id,'$endtime','$MRFID',$IsToday,".PRODUCT_SALES.") as MRF_Stock
										FROM wm_product_master
										WHERE wm_product_master.recyclable = 1
										AND wm_product_master.status = 1
										HAVING (MRF_Stock > 0 OR MRF_Sales_Qty > 0)
										ORDER BY PRODUCT_NAME ASC";
				$SELECT_S_RES 		= DB::connection('master_database')->select($SELECT_SQL);
				if (!empty($SELECT_S_RES)) {
					foreach ($SELECT_S_RES as $SELECT_S_ROW) {
						if (!in_array($SELECT_S_ROW->PRODUCT_ID,$arrUniquePID)) {
							array_push($arrUniquePID,$SELECT_S_ROW->PRODUCT_ID);
							array_push($arrUniquePro,array("PRODUCT_ID"=>$SELECT_S_ROW->PRODUCT_ID,"PRODUCT_NAME"=>$SELECT_S_ROW->PRODUCT_NAME));
						}
						if (date("m",strtotime("now")) == date("m",strtotime($this->report_starttime)) && date("Y",strtotime("now")) == date("Y",strtotime($this->report_starttime))) {
							$STARTDATE 	= date("Y-m-d",strtotime($this->report_starttime));
							if (strtotime(date("Y-m-d",strtotime("now"))) > strtotime(date("Y-m-d",strtotime($this->report_endtime)))) {
								$ENDDATE 	= date("Y-m-d",strtotime($this->report_endtime));
							} else {
								$ENDDATE 	= date("Y-m-d",strtotime("yesterday"));
							}
						} else{
							$STARTDATE 	= date("Y-m-d",strtotime($this->report_starttime));
							$ENDDATE 	= date("Y-m-d",strtotime($this->report_endtime));
						}
						if (!in_array($SELECT_S_ROW->MRFID,$arrUniqueMRF)) {
							$PRODUCTION_DAYS_SQL = "SELECT COUNT(DISTINCT production_date) AS CNT
															FROM wm_production_report_master
															WHERE mrf_id = ".intval($SELECT_S_ROW->MRFID)."
															AND production_date BETWEEN '".$STARTDATE."' AND '".$ENDDATE."'";
							$PRODUCTION_DAYS_RES = DB::connection('master_database')->select($PRODUCTION_DAYS_SQL);
							$TOTAL_DAYS_IN_MONTH = isset($PRODUCTION_DAYS_RES[0]->CNT)?$PRODUCTION_DAYS_RES[0]->CNT:0;
							$arrUniqueMRF[$SELECT_S_ROW->MRFID] = $TOTAL_DAYS_IN_MONTH;
						} else {
							$TOTAL_DAYS_IN_MONTH = $arrUniqueMRF[$SELECT_S_ROW->MRFID];
						}
						$DAYS_OF_THE_MONTH 		= $TOTAL_DAYS_IN_MONTH;
						$TOTALDAYSINMONTH 		= date("t",strtotime($STARTDATE));
						$TILL_DATE_QTY 			= ((!empty($TOTALDAYSINMONTH) && !empty($SELECT_S_ROW->PROJECTION_QTY))?round($SELECT_S_ROW->PROJECTION_QTY/$TOTALDAYSINMONTH):0) * $TOTAL_DAYS_IN_MONTH;
						$SURPLUS_DEFICIT 			= $SELECT_S_ROW->ACTUAL_QTY - $TILL_DATE_QTY;
						$SURPLUS_DEFICIT_PER 	= _FormatNumberV2((!empty($SURPLUS_DEFICIT) && !empty($TILL_DATE_QTY))?(($SURPLUS_DEFICIT/$TILL_DATE_QTY)*100):0);
						$REMAINING_TARGET 		= $SELECT_S_ROW->ACTUAL_QTY - $SELECT_S_ROW->PROJECTION_QTY;
						$REMAINING_TARGET_PER 	= _FormatNumberV2((!empty($REMAINING_TARGET) && !empty($SELECT_S_ROW->PROJECTION_QTY))?(($REMAINING_TARGET/$SELECT_S_ROW->PROJECTION_QTY)*100):0);
						if (!empty($SURPLUS_DEFICIT_PER)) {
							$SURPLUS_DEFICIT_PER 	= ($SURPLUS_DEFICIT_PER > 0)?"<font class=\"text-success text-bold\">".$SURPLUS_DEFICIT_PER."%</font>":"<font class=\"text-danger text-bold\">".$SURPLUS_DEFICIT_PER."%</font>";
						}
						if (!empty($REMAINING_TARGET_PER)) {
							$REMAINING_TARGET_PER 	= ($REMAINING_TARGET_PER > 0)?"<font class=\"text-success text-bold\">".$REMAINING_TARGET_PER."%</font>":"<font class=\"text-danger text-bold\">".$REMAINING_TARGET_PER."%</font>";
						}
						$MRF_SALES_QTY = !empty($SELECT_S_ROW->MRF_Sales_Qty)?$SELECT_S_ROW->MRF_Sales_Qty:0;
						$MRF_STOCK 		= !empty($SELECT_S_ROW->MRF_Stock)?$SELECT_S_ROW->MRF_Stock:0;
						$arrResult[$SELECT_S_ROW->PRODUCT_ID][$SELECT_S_ROW->MRFID] = array(	"PROJECTION_QTY"=>_FormatNumberV2($SELECT_S_ROW->PROJECTION_QTY),
																													"TILL_DATE_QTY"=>_FormatNumberV2($TILL_DATE_QTY),
																													"ACTUAL_QTY"=>_FormatNumberV2($SELECT_S_ROW->ACTUAL_QTY),
																													"SURPLUS_DEFICIT"=>_FormatNumberV2($SURPLUS_DEFICIT),
																													"SURPLUS_DEFICIT_PER"=>($SURPLUS_DEFICIT_PER),
																													"REMAINING_TARGET"=>_FormatNumberV2($REMAINING_TARGET),
																													"REMAINING_TARGET_PER"=>($REMAINING_TARGET_PER),
																													"MRF_SALES_QTY"=>_FormatNumberV2($MRF_SALES_QTY),
																													"MRF_STOCK"=>_FormatNumberV2($MRF_STOCK));
						$TOTAL_PROJECTION_QTY += $SELECT_S_ROW->PROJECTION_QTY;
						$TOTAL_TILL_DATE_QTY += $TILL_DATE_QTY;
						$TOTAL_ACTUAL_QTY += $SELECT_S_ROW->ACTUAL_QTY;
						$TOTAL_MRF_SALES_QTY += $MRF_SALES_QTY;
						$TOTAL_MRF_STOCK += $MRF_STOCK;
					}
					$TOTAL_SURPLUS_DEFICIT 			= $TOTAL_ACTUAL_QTY - $TOTAL_TILL_DATE_QTY;
					$TOTAL_SURPLUS_DEFICIT_PER 	= _FormatNumberV2((!empty($TOTAL_SURPLUS_DEFICIT) && !empty($TOTAL_TILL_DATE_QTY))?(($TOTAL_SURPLUS_DEFICIT/$TOTAL_TILL_DATE_QTY)*100):0);
					$TOTAL_REMAINING_TARGET 		= $TOTAL_ACTUAL_QTY - $TOTAL_PROJECTION_QTY;
					$TOTAL_REMAINING_TARGET_PER 	= _FormatNumberV2((!empty($TOTAL_REMAINING_TARGET) && !empty($TOTAL_PROJECTION_QTY))?(($TOTAL_REMAINING_TARGET/$TOTAL_PROJECTION_QTY)*100):0);
					if (!empty($TOTAL_SURPLUS_DEFICIT_PER)) {
						$TOTAL_SURPLUS_DEFICIT_PER 	= ($TOTAL_SURPLUS_DEFICIT_PER > 0)?"<font class=\"text-success text-bold\">".$TOTAL_SURPLUS_DEFICIT_PER."%</font>":"<font class=\"text-danger text-bold\">".$TOTAL_SURPLUS_DEFICIT_PER."%</font>";
					}
					if (!empty($TOTAL_REMAINING_TARGET_PER)) {
						$TOTAL_REMAINING_TARGET_PER 	= ($TOTAL_REMAINING_TARGET_PER > 0)?"<font class=\"text-success text-bold\">".$TOTAL_REMAINING_TARGET_PER."%</font>":"<font class=\"text-danger text-bold\">".$TOTAL_REMAINING_TARGET_PER."%</font>";
					}
				}
			}
			$code 														= SUCCESS;
			$msg 															= trans("message.RECORD_FOUND");
			$arrReturn['ReportData']['arrUniquePro'] 			= $arrUniquePro;
			$arrReturn['ReportData']['arrMRF'] 					= $arrMRF;
			$arrReturn['ReportData']['ReportRows'] 			= $arrResult;
			$arrReturn['ReportData']['DAYS_OF_THE_MONTH'] 	= (!empty($arrUniqueMRF))?max($arrUniqueMRF):$DAYS_OF_THE_MONTH;
			$arrReturn['ReportData']['Page_Title'] 			= "Production Projection Vs Actual";
			$arrReturn['ReportData']['TOTAL_ROW'] 				= array(	"TOTAL_PROJECTION_QTY"=>_FormatNumberV2($TOTAL_PROJECTION_QTY),
																						"TOTAL_TILL_DATE_QTY"=>_FormatNumberV2($TOTAL_TILL_DATE_QTY),
																						"TOTAL_ACTUAL_QTY"=>_FormatNumberV2($TOTAL_ACTUAL_QTY),
																						"TOTAL_SURPLUS_DEFICIT"=>_FormatNumberV2($TOTAL_SURPLUS_DEFICIT),
																						"TOTAL_SURPLUS_DEFICIT_PER"=>$TOTAL_SURPLUS_DEFICIT_PER,
																						"TOTAL_REMAINING_TARGET"=>_FormatNumberV2($TOTAL_REMAINING_TARGET),
																						"TOTAL_REMAINING_TARGET_PER"=>$TOTAL_REMAINING_TARGET_PER,
																						"TOTAL_MRF_SALES_QTY"=>_FormatNumberV2($TOTAL_MRF_SALES_QTY),
																						"TOTAL_MRF_STOCK"=>_FormatNumberV2($TOTAL_MRF_STOCK));
		} catch(\Exeption $e) {
			$msg 												= trans("message.SOMETHING_WENT_WRONG");
			$code 											= SUCCESS;
			$arrReturn['ReportData']['Page_Title'] = "Production Projection Vs Actual";
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$arrReturn]);
	}
}