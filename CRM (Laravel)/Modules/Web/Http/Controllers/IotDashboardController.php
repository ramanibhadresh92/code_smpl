<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

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
use App\Models\IOTReportConfigMaster;
use App\Models\IOTdeviceAlarmLimitReadingLog;
use App\Models\IOTDeviceMaintenanceParameters;
use App\Models\IOTDeviceBreakdownDetails;
use App\Models\IOTDeviceMaintenanceReasonActions;
use App\Models\IOTDeviceBreakdownDetailLog;
use App\Models\AdminUserRights;
use App\Http\Requests\BreakdownAddRequest;
use App\Http\Requests\BreakdownCloseRequest;
use App\Classes\CCOF;

use Mail;
use JWTFactory;
use JWTAuth;
use Validator;
use File;
use Storage;
use Input;
use DB;

class IotDashboardController extends LRBaseController
{
	public $daterange 			= "";
	public $report_starttime 	= "";
	public $report_endtime 		= "";
	public $mrf_id 				= array();
	public $basestation_id 		= array();
	public $location_id 		= array();
	public $type_of_sales 		= 1;
	public $report_type 		= 1;
	public $exclude_cat			= array("RDF");
	public $IP_ADDRESS 			= array("203.88.147.186","103.86.19.72","123.201.21.122","43.241.144.32","223.226.209.81");
	public $arrLocation 		= array("1"=>"iCreate","2"=>"Mahatma Mandir","3"=>"Exhibition Hall 2");
	public $selected_month 		= 0;
	public $selected_year 		= 0;
	public $slave_id 			= "";
	public $amp_slave_id 		= "";
	public $device_code 		= "";
	public $reportdate 			= "";

	private function SetVariables($request)
	{
		$this->report_period 	= isset($request['report_period'])?$request['report_period']:0;
		$this->report_starttime = isset($request['report_starttime'])?$request['report_starttime']:"";
		$this->report_endtime 	= isset($request['report_endtime'])?$request['report_endtime']:"";
		$this->mrf_id 			= isset($request['mrf_id'])?$request['mrf_id']:array();
		$this->type_of_sales 	= isset($request['type_of_sales'])?$request['type_of_sales']:"";
		$this->report_type 		= isset($request['report_type'])?$request['report_type']:"";
		$this->location_id 		= isset($request['location_id'])?$request['location_id']:array();
		$this->slave_id 		= isset($request['slave_id'])?$request['slave_id']:"";
		$this->amp_slave_id 	= isset($request['amp_slave_id'])?$request['amp_slave_id']:"";
		$this->device_code 		= isset($request['device_code'])?$request['device_code']:"";
		$this->reportdate 		= isset($request['reportdate'])?$request['reportdate']:date("Y-m-d");
		$this->daterange 		= "";
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

	public function runHourData(Request $request)
	{
		$IOTEquipmentReading 		= new IOTEquipmentReading;
		$MRF_ID 					= (isset($request->mrf_id)?$request->mrf_id:0);
		$arrDevices 				= IOTEquipments::where('status',1)->where("mrf_id",$MRF_ID)->pluck("title","slave_id");
		$arrParameter 				= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("title","code");
		$arrParameterUOM 			= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("uom","code");
		$arrRunHHourData 			= array('ChartData'=>"","TabularData"=>"");
		$request->report_starttime	= empty($request->report_starttime)?date("now"):$request->report_starttime;
		$request->report_endtime	= empty($request->report_endtime)?date("now"):$request->report_endtime;
		$DeviceTitle 				= isset($arrDevices[$request->slave_id])?$arrDevices[$request->slave_id]:"-";
		$arrRunHHourData 			= $IOTEquipmentReading->getKGPerHourReading($MRF_ID,$request->slave_id,$request->report_starttime,$request->report_endtime,$DeviceTitle);
		return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $arrRunHHourData]);
	}

	public function dailyDeviceConsuption(Request $request)
	{
		$IOTEquipmentReading 	= new IOTEquipmentReading;
		$MRF_ID 				= (isset($request->mrf_id)?$request->mrf_id:0);
		$arrDevices 			= IOTEquipments::where('status',1)->where("mrf_id",$MRF_ID)->pluck("title","slave_id");
		$arrParameter 			= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("title","code");
		$arrParameterUOM 		= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("uom","code");
		$arrRunHHourData 		= array('ChartData'=>"","TabularData"=>"");
		$arrParameterDailyAVG 	= [];
		
		if (isset($request->device_code) && !empty($request->device_code)) {
			$request->report_starttime	= empty($request->report_starttime)?date("now"):$request->report_starttime;
			$request->report_endtime	= empty($request->report_endtime)?date("now"):$request->report_endtime;
			$ParameterTitle 			= isset($arrParameter[$request->device_code])?$arrParameter[$request->device_code]:"-";
			$arrParameterDailyAVG 		= $IOTEquipmentReading->getEquipmentAvgReading($MRF_ID,$request->device_code,$request->report_starttime,$request->report_endtime,$ParameterTitle);
			
		}
		$msg = !empty($arrParameterDailyAVG)?trans('message.RECORD_FOUND'):'No Record Found';
		return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $arrParameterDailyAVG]);
	}

	public function ampTimeAnalysisReading(Request $request)
	{
		$IOTEquipmentReading 				= new IOTEquipmentReading;
		$MRF_ID 							= (isset($request->mrf_id)?$request->mrf_id:0);
		$ReportType 						= (isset($request->rtype)?$request->rtype:"");
		$arrDevices 						= IOTEquipments::where('status',1)->where("mrf_id",$MRF_ID)->pluck("title","slave_id");
		$arrParameter 						= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("title","code");
		$arrParameterUOM 					= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("uom","code");
		$arrRunHHourData 					= array('ChartData'=>"","TabularData"=>"");
		$arrParameterDailyAVG 				= [];
		$arrParameterTimeAnalysisReading 	= array();
		$arrParameterAMPReading 			= array();
		if (isset($request->amp_slave_id) && !empty($request->amp_slave_id))
		{
			$DeviceTitle = isset($arrDevices[$request->amp_slave_id])?$arrDevices[$request->amp_slave_id]:"-";
			if ($ReportType == "timegrpah") {
				$arrParameterTimeAnalysisReading = $IOTEquipmentReading->getEquipmentTimeAnalysisReading($MRF_ID,$request->amp_slave_id,$request->reportdate,$DeviceTitle);
			} else if ($ReportType == "ampgraph") {
				$ReportStartTime 		= isset($request->report_starttime)?$request->report_starttime:"";
				$ReportEndTime 			= isset($request->report_endtime)?$request->report_endtime:"";
				$arrParameterAMPReading = $IOTEquipmentReading->getEquipmentAmpReading($MRF_ID,$request->amp_slave_id,$request->reportdate,$DeviceTitle,$ReportStartTime,$ReportEndTime);
			} else {
				$arrParameterTimeAnalysisReading 	= $IOTEquipmentReading->getEquipmentTimeAnalysisReading($MRF_ID,$request->amp_slave_id,$request->reportdate,$DeviceTitle);
				$arrParameterAMPReading 			= $IOTEquipmentReading->getEquipmentAmpReading($MRF_ID,$request->amp_slave_id,$request->reportdate,$DeviceTitle);
			}
		}
		return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => array('TimeAnalysisReading' => $arrParameterTimeAnalysisReading, 'AMPReading' => $arrParameterAMPReading)]);
	}

	public function kGPerkWH(Request $request)
	{
		$IOTEquipmentReading 	= new IOTEquipmentReading;
		$MRF_ID 				= (isset($request->mrf_id)?$request->mrf_id:0);
		$arrDevices 			= IOTEquipments::where('status',1)->where("mrf_id",$MRF_ID)->pluck("title","slave_id");
		$arrParameter 			= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("title","code");
		$arrParameterUOM 		= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("uom","code");
		$report_starttime		= empty($request->report_starttime)?date("now"):$request->report_starttime;
		$report_endtime			= empty($request->report_endtime)?date("now"):$request->report_endtime;
		$arrKGPerkWH 			= $IOTEquipmentReading->getKGPerkWH($MRF_ID,$report_starttime,$report_endtime);
		$msg 					= !empty($arrKGPerkWH)?trans('message.RECORD_FOUND'):'No Record Found';
		return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $arrKGPerkWH]);
	}

	public function powerQualityAnalysis(Request $request)
	{
		$IOTEquipmentReading 	= new IOTEquipmentReading;
		$MRF_ID 				= (isset($request->mrf_id)?$request->mrf_id:Auth()->user()->mrf_user_id);
		$report_starttime		= empty($request->report_starttime)?date("now"):$request->report_starttime;
		$report_endtime			= empty($request->report_endtime)?date("now"):$request->report_endtime;
		$arrDevices 			= IOTEquipments::where('status',1)->where("mrf_id",$MRF_ID)->pluck("title","slave_id");
		$arrParameter 			= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("title","code");
		$arrParameterUOM 		= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("uom","code");
		$arrPowerFactorData 	= array('ChartData'=>"","TabularData"=>"");
		$arrPowerFactorData 	= $IOTEquipmentReading->getPowerFactorChart($MRF_ID,$report_starttime,$report_endtime);
		$msg 					= !empty($arrPowerFactorData)?trans('message.RECORD_FOUND'):'No Record Found';
		return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $arrPowerFactorData]);
	}

	public function getDeviceCode()
	{
		$IOTEquipmentReading 	= new IOTEquipmentReading;
		$arrParameter 			= IOTEquipmentParameters::select('title','code')->where('status',1)->where("show_in_filter",1)->get()->toArray();
		$msg 					= !empty($arrParameter)?trans('message.RECORD_FOUND'):trans('message.RECORD_NOT_FOUND');
		return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => !empty($arrParameter)?$arrParameter:array()]);
	}

	public function save_ccof_data_Api(Request $request)
	{
		$CCOFMaster 	= new CCOFMaster();
		$location_id 	= isset($request->selected_location)?$request->selected_location:0;
		$MONTH 			= isset($request->selected_month)?$request->selected_month:0;
		$YEAR 			= isset($request->selected_year)?$request->selected_year:0;
		$id 			= isset($request->id)?$request->id:0;
		if ($request->isMethod("post") && !empty($location_id) && !empty($MONTH) && !empty($YEAR)) {
			$CCOFMaster->saveRecord($request,$id);	
			return response()->json(["code" => SUCCESS , "msg" =>'CCOF data updated successfully !!!']);
		} else {
			return response()->json(["code" => VALIDATION_ERROR , "msg" =>'Location, Month and Year fields are required.']);
		}
	}

	public static function getDeviceListByBaseLocation() {
		if(Auth()->user()->adminuserid) {
			$AdminInfo = AdminUser::select('base_location','company_id')->where('adminuserid',Auth()->user()->adminuserid)->first()->toArray();			
			$userID 			= Auth()->user()->adminuserid;
			$CurrentBaseID 		= $AdminInfo['base_location'];
			$Department 	= WmDepartment::select("id",DB::raw("UPPER(TRIM(REPLACE(REPLACE(REPLACE(department_name,'MRF-',''),'MRF -',''),'MRF -',''))) as department_name"))
			->where("iot_enabled",1)
			->where("status",1)
			->where("company_id",$AdminInfo['company_id'])
			->whereNotNull("code")
			->orderBy("department_name","ASC")->first();
			$msg = trans('message.RECORD_NOT_FOUND');
			$arrDevices = array();
			if(!empty($Department)) {
				$result = $Department->toArray();	
				if(array_key_exists('id', $result)) {
					$MRF_ID = (array_key_exists('id', $result)?$result['id']:Auth()->user()->mrf_user_id);
				}		
				
				///////
				//$arrDevices = IOTEquipments::select("title","slave_id")->where('status',1)->where("mrf_id",$MRF_ID)->orderby("title","ASC")->get()->toArray();
				
				$arrDevices = IOTDeviceMaintenanceParameters::select("title","id AS slave_id")->where("mrf_id",$MRF_ID)->orderby("title","ASC")->get()->toArray();

				if(!empty($arrDevices)) {
					$msg = trans('message.RECORD_FOUND');
					$data = array('MRF_ID' => $MRF_ID,'MRF' => $result['department_name'], 'devices' => $arrDevices);
					return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
				}
			}
			return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => array()]);
		}

		$msg 	= trans('message.SOMETHING_WENT_WRONG');
		$code 	= INTERNAL_SERVER_ERROR;
		return response()->json(array("code" => $code,"msg"=>$msg));
	}

	public function getDeviceList(Request $request)
	{
		$IOTEquipmentReading 	= new IOTEquipmentReading;
		$MRF_ID 				= (isset($request->mrf_id)?$request->mrf_id:Auth()->user()->mrf_user_id);
		$arrDevices 			= IOTEquipments::select("title","slave_id")->where('status',1)->where("mrf_id",$MRF_ID)->orderby("title","ASC")->get()->toArray();
		$msg 					= !empty($arrDevices)?trans('message.RECORD_FOUND'):trans('message.RECORD_NOT_FOUND');
		return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => !empty($arrDevices)?$arrDevices:array()]);
	}
	
	public function GetMRFListForCCOF(Request $request)
	{
		$CCOFMaster 	= new CCOFMaster();
		$location_id 	= isset($request->selected_location)?$request->selected_location:0;
		$MONTH 			= isset($request->selected_month)?$request->selected_month:0;
		$YEAR 			= isset($request->selected_year)?$request->selected_year:0;
		$id 			= isset($request->id)?$request->id:0;
		if ($request->isMethod("post") && !empty($location_id) && !empty($MONTH) && !empty($YEAR)) {
			$CCOFMaster->saveRecord($request,$id);	
			return response()->json(["code" => SUCCESS , "msg" =>'CCOF data updated successfully !!!']);
		} else {
			return response()->json(["code" => VALIDATION_ERROR , "msg" =>'Location, Month and Year fields are required.']);
		}
	}

	public function iotDashboardData(Request $request)
	{
		$IOTEquipmentReading 	= new IOTEquipmentReading;
		$arrParameterDailyAVG 	= $IOTEquipmentReading->GetIotDashboardData($request);
		$msg = !empty($arrParameterDailyAVG)?trans('message.RECORD_FOUND'):'No Record Found';
		return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $arrParameterDailyAVG]);
	}

	public function iotDashboardGraphData(Request $request)
	{
		$IOTEquipmentReading 	= new IOTEquipmentReading;
		$MRF_ID_DATA 			= (isset($request->mrf_id) && !empty($request->mrf_id))? explode(",",$request->mrf_id):array();
		$ReportType 			= (isset($request->rtype)?$request->rtype:"");
		$IOT_DETAILS_DATA 		= array();
		$IOT_DATA 				= array();
		$ReportStartTime 		= isset($request->report_starttime)?$request->report_starttime:"";
		$ReportEndTime 			= isset($request->report_endtime)?$request->report_endtime:"";
		if(empty($MRF_ID_DATA)) {
			$base_location_id 	= GetUserAssignedBaseLocation(Auth()->user()->adminuserid);
			$MRF_DATA 			= WmDepartment::select("id as mrf_id","department_name")
									->whereIn("base_location_id",$base_location_id)
									->where('status',1)
									->where("is_service_mrf",0)
									->where("is_virtual",0)
									->get()
									->toArray();
		} else {
			$MRF_DATA 			= WmDepartment::select("id as mrf_id","department_name")
									->whereIn("id",$MRF_ID_DATA)
									->where('status',1)
									->where("is_service_mrf",0)
									->where("is_virtual",0)
									->get()
									->toArray();
		}
		if(!empty($MRF_DATA))
		{
			foreach($MRF_DATA AS $key => $value)
			{
				$DEPARMENT_NAME 					= $value['department_name'];
				$MRF_ID 							= $value['mrf_id'];
				$MRF_DATA[$key]['department_name'] 	= $value['department_name'];
				$arrDevices 						= IOTEquipments::where('status',1)->where("mrf_id",$MRF_ID)->pluck("title","slave_id");
				// $arrParameter 						= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("title","code");
				// $arrParameterUOM 					= IOTEquipmentParameters::where('status',1)->where("show_in_filter",1)->pluck("uom","code");
				$arrParameterDailyAVG 				= [];
				$arrParameterTimeAnalysisReading 	= array();
				$arrParameterAMPReading 			= array();
				$arrParameterAMPReadingAfr			= array();
				$IOT_DATA 							= IOTReportConfigMaster::select("iot_report_config_master.*","wm_department.department_name","wm_department.id as mrf_id")
														->leftjoin("wm_department","wm_department.id","=","iot_report_config_master.mrf_id")
														->where("iot_report_config_master.mrf_id",$MRF_ID)
														->where("iot_report_config_master.status","1")
														->where("iot_report_config_master.tabular","0")
														->first();
														
				if(!empty($IOT_DATA))
				{
					$IOT_DETAILS_DATA = \DB::table("iot_report_config_details")->where("config_id",$IOT_DATA->id)->get()->toArray();

					if(!empty($IOT_DETAILS_DATA))
					{
						foreach($IOT_DETAILS_DATA AS $iot_key => $iot_value){
							$IOT_MRF_SLAVE_ID 		= 0;
							$IOT_AFR_SLAVE_ID 		= 0;
							$IOT_DEVICE_CODE 		= $iot_value->device_code;
							############# MRF DATA #############
							if ($iot_value->afr == 0)
							{
								$IOT_MRF_DEVICE_CODE 	= $iot_value->device_code;
								$IOT_MRF_SLAVE_ID 		= $iot_value->slave_id;
								$DeviceTitle 			= isset($arrDevices[$IOT_MRF_SLAVE_ID])?$arrDevices[$IOT_MRF_SLAVE_ID]:"-";
								if ($ReportType == "timegrpah") {
									$arrParameterTimeAnalysisReading = $IOTEquipmentReading->getEquipmentTimeAnalysisReading($MRF_ID,$IOT_MRF_SLAVE_ID,$request->reportdate,$DeviceTitle);
								} else if ($ReportType == "ampgraph") {
									$ReportStartTime 		= isset($request->report_starttime)?$request->report_starttime:"";
									$ReportEndTime 			= isset($request->report_endtime)?$request->report_endtime:"";
									$arrParameterAMPReading = $IOTEquipmentReading->getEquipmentAmpReading($MRF_ID,$IOT_MRF_SLAVE_ID,$request->reportdate,$DeviceTitle,$ReportStartTime,$ReportEndTime,true);
									
								} else {
									$arrParameterTimeAnalysisReading 	= $IOTEquipmentReading->getEquipmentTimeAnalysisReading($MRF_ID,$IOT_MRF_SLAVE_ID,$request->reportdate,$DeviceTitle);
									$arrParameterAMPReading 			= $IOTEquipmentReading->getEquipmentAmpReading($MRF_ID,$IOT_MRF_SLAVE_ID,$request->reportdate,$DeviceTitle);
								}
								$MRF_DATA[$key]['MRF']['reading_time_date'] 	= isset($arrParameterAMPReading['ChartData']) ? $arrParameterAMPReading['ChartData']['reading_time_date'] : array();
								$MRF_DATA[$key]['MRF']['amp_use_data'] 			= isset($arrParameterAMPReading['ChartData']) ? $arrParameterAMPReading['ChartData']['amp_use_data'] : array();
								$MRF_DATA[$key]['MRF']['name'] 					= $DEPARMENT_NAME;
								$MRF_DATA[$key]['MRF']['DeviceTitle'] 			= $DeviceTitle;
								$MRF_DATA[$key]['MRF']['IOT_MRF_SLAVE_ID'] 		= $IOT_MRF_SLAVE_ID;
								$MRF_DATA[$key]['MRF']['IOT_MRF_DEVICE_CODE'] 	= $IOT_MRF_DEVICE_CODE;
							}
							############# AFR DATA #############
							if ($iot_value->afr > 0)
							{
								$IOT_AFR_SLAVE_ID 		= $iot_value->slave_id;
								$IOT_AFR_DEVICE_CODE 	= $iot_value->device_code;
								$DeviceTitle 			= isset($arrDevices[$IOT_AFR_SLAVE_ID])?$arrDevices[$IOT_AFR_SLAVE_ID]:"-";
								if ($ReportType == "timegrpah") {
									$arrParameterTimeAnalysisReading = $IOTEquipmentReading->getEquipmentTimeAnalysisReading($MRF_ID,$IOT_AFR_SLAVE_ID,$request->reportdate,$DeviceTitle);
								} else if ($ReportType == "ampgraph") {
									$ReportStartTime 			= isset($request->report_starttime)?$request->report_starttime:"";
									$ReportEndTime 				= isset($request->report_endtime)?$request->report_endtime:"";
									$arrParameterAMPReadingAfr 	= $IOTEquipmentReading->getEquipmentAmpReading($MRF_ID,$IOT_AFR_SLAVE_ID,$request->reportdate,$DeviceTitle,$ReportStartTime,$ReportEndTime,true);
								} else {
									$arrParameterTimeAnalysisReading 	= $IOTEquipmentReading->getEquipmentTimeAnalysisReading($MRF_ID,$IOT_AFR_SLAVE_ID,$request->reportdate,$DeviceTitle);
									$arrParameterAMPReadingAfr 			= $IOTEquipmentReading->getEquipmentAmpReading($MRF_ID,$IOT_AFR_SLAVE_ID,$request->reportdate,$DeviceTitle);
								}
								$MRF_DATA[$key]['AFR']['reading_time_date'] 	= isset($arrParameterAMPReadingAfr['ChartData'])?$arrParameterAMPReadingAfr['ChartData']['reading_time_date']:array();
								$MRF_DATA[$key]['AFR']['amp_use_data'] 			= isset($arrParameterAMPReadingAfr['ChartData'])?$arrParameterAMPReadingAfr['ChartData']['amp_use_data']:array();
								$MRF_DATA[$key]['AFR']['name'] 					= $DEPARMENT_NAME."-AFR";
								$MRF_DATA[$key]['AFR']['DeviceTitle'] 			= $DeviceTitle;
								$MRF_DATA[$key]['AFR']['IOT_AFR_SLAVE_ID'] 		= $IOT_AFR_SLAVE_ID;
								$MRF_DATA[$key]['AFR']['IOT_AFR_DEVICE_CODE'] 	= $IOT_AFR_DEVICE_CODE;
							}
						}
					}
				} else {
					$MRF_DATA[$key]['MRF']['reading_time_date'] = array();
					$MRF_DATA[$key]['MRF']['amp_use_data'] 		= array();
					$MRF_DATA[$key]['MRF']['name'] 				= $DEPARMENT_NAME;

					$MRF_DATA[$key]['AFR']['reading_time_date'] = array();
					$MRF_DATA[$key]['AFR']['amp_use_data'] 		= array();
					$MRF_DATA[$key]['AFR']['name'] 				= $DEPARMENT_NAME;
				}

				########### TOTALIZER ###############
				$IOT_MRF_DEVICE_CODE 	= "400001";
				$IOT_MRF_SLAVE_ID 		= "73";
				$DeviceTitle 			= isset($arrDevices[$IOT_MRF_SLAVE_ID])?$arrDevices[$IOT_MRF_SLAVE_ID]:"-";
				$arrParameterAMPReading = $IOTEquipmentReading->getEquipmentAmpReading($MRF_ID,$IOT_MRF_SLAVE_ID,$request->reportdate,$DeviceTitle,$ReportStartTime,$ReportEndTime,true);
				$MRF_DATA[$key]['TOTALIZER']['reading_time_date'] 	= isset($arrParameterAMPReading['ChartData']) ? $arrParameterAMPReading['ChartData']['reading_time_date'] : array();
							$MRF_DATA[$key]['TOTALIZER']['amp_use_data'] 			= isset($arrParameterAMPReading['ChartData']) ? $arrParameterAMPReading['ChartData']['amp_use_data'] : array();
				
				$MRF_DATA[$key]['TOTALIZER']['name'] 					= $DEPARMENT_NAME;
				$MRF_DATA[$key]['TOTALIZER']['DeviceTitle'] 			= $DeviceTitle;
				$MRF_DATA[$key]['TOTALIZER']['IOT_MRF_SLAVE_ID'] 		= $IOT_MRF_SLAVE_ID;
				$MRF_DATA[$key]['TOTALIZER']['IOT_MRF_DEVICE_CODE'] 	= $IOT_MRF_DEVICE_CODE;

				########### TOTALIZER ###############
				########### Belt speed ###############
				$IOT_MRF_DEVICE_CODE 	= "400003";
				$DeviceTitle 			= isset($arrDevices[$IOT_MRF_SLAVE_ID])?$arrDevices[$IOT_MRF_SLAVE_ID]:"-";
				$arrParameterAMPReading = $IOTEquipmentReading->getEquipmentAmpReading($MRF_ID,$IOT_MRF_SLAVE_ID,$request->reportdate,$DeviceTitle,$ReportStartTime,$ReportEndTime,true);
				$MRF_DATA[$key]['BELT_SPEED']['reading_time_date'] 	= isset($arrParameterAMPReading['ChartData']) ? $arrParameterAMPReading['ChartData']['reading_time_date'] : array();
							$MRF_DATA[$key]['BELT_SPEED']['amp_use_data'] 			= isset($arrParameterAMPReading['ChartData']) ? $arrParameterAMPReading['ChartData']['amp_use_data'] : array();
				
				$MRF_DATA[$key]['BELT_SPEED']['name'] 					= $DEPARMENT_NAME;
				$MRF_DATA[$key]['BELT_SPEED']['DeviceTitle'] 			= $DeviceTitle;
				$MRF_DATA[$key]['BELT_SPEED']['IOT_MRF_SLAVE_ID'] 		= $IOT_MRF_SLAVE_ID;
				$MRF_DATA[$key]['BELT_SPEED']['IOT_MRF_DEVICE_CODE'] 	= $IOT_MRF_DEVICE_CODE;
				########### Belt speed ###############
				########### Belt speed ###############
				$IOT_MRF_DEVICE_CODE 	= "400005";
				$DeviceTitle 			= isset($arrDevices[$IOT_MRF_SLAVE_ID])?$arrDevices[$IOT_MRF_SLAVE_ID]:"-";
				$arrParameterAMPReading = $IOTEquipmentReading->getEquipmentAmpReading($MRF_ID,$IOT_MRF_SLAVE_ID,$request->reportdate,$DeviceTitle,$ReportStartTime,$ReportEndTime,true);
				$MRF_DATA[$key]['FEED_RATE_TRACKING']['reading_time_date'] 	= isset($arrParameterAMPReading['ChartData']) ? $arrParameterAMPReading['ChartData']['reading_time_date'] : array();
							$MRF_DATA[$key]['FEED_RATE_TRACKING']['amp_use_data'] 			= isset($arrParameterAMPReading['ChartData']) ? $arrParameterAMPReading['ChartData']['amp_use_data'] : array();
				
				$MRF_DATA[$key]['FEED_RATE_TRACKING']['name'] 					= $DEPARMENT_NAME;
				$MRF_DATA[$key]['FEED_RATE_TRACKING']['DeviceTitle'] 			= $DeviceTitle;
				$MRF_DATA[$key]['FEED_RATE_TRACKING']['IOT_MRF_SLAVE_ID'] 		= $IOT_MRF_SLAVE_ID;
				$MRF_DATA[$key]['FEED_RATE_TRACKING']['IOT_MRF_DEVICE_CODE'] 	= $IOT_MRF_DEVICE_CODE;
				########### Belt speed ###############

			}
		}
		$GET_IOT_DATA = IOTEquipmentReading::GetIotDashboardData($request->all());
		return response()->json(["code" => SUCCESS,
								"msg" =>trans('message.RECORD_FOUND'),
								"data" => array('TimeAnalysisReading' => $arrParameterTimeAnalysisReading,
												'AMPReading' => $MRF_DATA,
												"TabularData" => $GET_IOT_DATA)]);
	}


	public function GetTotalizerData(Request $request){
		try{
			$SLAVE_ID 				= (isset($request->slave_id) && !empty($request->slave_id)) ? $request->slave_id : 73;
			$DEVICE_CODE 			= 400001;
			$IOTEquipmentReading 	= new IOTEquipmentReading;
			$MRF_ID_DATA 			= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id :array();
			$ReportType 			= (isset($request->rtype)?$request->rtype:"");
			$IOT_DETAILS_DATA 		= array();
			$data 					= array();

			$ReportStartTime 		= isset($request->report_starttime)?$request->report_starttime:date("Y-m-d");
			$ReportEndTime 			= isset($request->report_endtime)?$request->report_endtime:date("Y-m-d");
			
			$data  					= IOTEquipmentReading::GetTotalizerData($MRF_ID_DATA,$SLAVE_ID,$DEVICE_CODE,"","",$ReportStartTime,$ReportEndTime);
			$msg 					= !empty($data)?trans('message.RECORD_FOUND'):'No Record Found';
			return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
		}catch(\Exception $e){
			prd($e->getMessage()." ".$e->getLine()." ".$e->getFile());
		}
		
	}

	public function feedRateofTotalizerData(Request $request){
		try{
			$SLAVE_ID 				= (isset($request->slave_id) && !empty($request->slave_id)) ? $request->slave_id : 73;
			$DEVICE_CODE 			= 400001;
			$IOTEquipmentReading 	= new IOTEquipmentReading;
			$MRF_ID_DATA 			= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id :"";
			$report_starttime 		= (isset($request->report_starttime) && !empty($request->report_starttime)) ? $request->report_starttime :"";
			$report_endtime 		= (isset($request->report_endtime) && !empty($request->report_endtime)) ? $request->report_endtime :"";
			$ReportType 			= (isset($request->rtype)?$request->rtype:"");
			$IOT_DETAILS_DATA 		= array();
			$data 					= array();
			$ReportDate 			= isset($request->report_date)?$request->report_date:date("Y-m-d");
			$msg 					= !empty($data)?trans('message.RECORD_FOUND'):'No Record Found';
			$data  					= IOTEquipmentReading::feedRateofTotalizerDataV1($MRF_ID_DATA,$SLAVE_ID,$DEVICE_CODE,$report_starttime,$report_endtime);
			
			
			return response()->json(["code" => SUCCESS , "msg" =>$msg,"data" => $data]);
		}catch(\Exception $e){
			prd($e->getMessage()." ".$e->getLine()." ".$e->getFile());
		}
		
	}

	public function PlantOprationChartData(Request $request){
		try{
			$data  = IOTEquipmentReading::PlantOprationChartData($request->all());
			return response()->json(["code" => SUCCESS , "msg" =>"","data" => $data]);
		}catch(\Exception $e){
			prd($e->getMessage()." ".$e->getLine()." ".$e->getFile());
		}
		
	}

	public function PlantOprationChartDraw(Request $request){
		try{
			$data  = IOTEquipmentReading::GetChartDataIdWise($request);
			return response()->json(["code" => SUCCESS , "msg" =>"","data" => $data]);
		}catch(\Exception $e){
			prd($e->getMessage()." ".$e->getLine()." ".$e->getFile());
		}
		
	}
	/**
	Use 	: For Display Widget IOT Alarm
	Author 	: Axay Shah
	Date 	: 03 Octomber 2023
	*/
	public function IOTDashboardAlarmWidget(Request $request)
	{
		$data = IOTdeviceAlarmLimitReadingLog::GetAlarmWidgetReport($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data" => $data));
	}
	/**
	Use 	: For List Alarm details by slave id and mrf id and device code
	Author 	: Axay Shah
	Date 	: 03 Octomber 2023
	*/
	public function IOTAlarmDataDetails(Request $request)
	{
		$data = IOTdeviceAlarmLimitReadingLog::GetAlarmDetailsForPerticularDevice($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data" => $data));
	}
	/**
	Use 	: Graph For IOT Alarm Data Details
	Author 	: Axay Shah
	Date 	: 03 Octomber 2023
	*/
	public function IOTAlarmGraph(Request $request)
	{
		$data = IOTdeviceAlarmLimitReadingLog::GetAlarmGraph($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data" => $data));
	}

	/**
	Use 	: For List of Breakdown Devices By MRF
	Author 	: Kalpak Prajapati
	Date 	: 29 November 2023
	*/
	public function getBreakdownDevices(Request $request)
	{
		$data = IOTDeviceMaintenanceParameters::getBreakdownDevices($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data" => $data));
	}

	/**
	Use 	: save Breakdown Details
	Author 	: Kalpak Prajapati
	Date 	: 29 November 2023
	*/
	public function saveBreakdownDetails(Request $request)
	{
		$data 	= IOTDeviceBreakdownDetails::saveBreakdownDetails($request);
		if(trim($data) != '') {
			$request = new Request();
			$request->id = $data;
			return $this->doOpenedProcess($request);
		} else {
			$msg 	= trans('message.SOMETHING_WENT_WRONG');
			$code 	= INTERNAL_SERVER_ERROR;
			return response()->json(array("code" => $code,"msg"=>$msg,"data" => $data));
		}
	}

	/**
	Use 	: close Breakdown Details
	Author 	: Kalpak Prajapati
	Date 	: 29 November 2023
	*/
	public function closeBreakdownDetails(BreakdownCloseRequest $request)
	{
		$data 	= IOTDeviceBreakdownDetails::saveBreakdownDetails($request,true);
		$msg 	= ($data > 0)?trans('message.RECORD_UPDATED'):trans('message.SOMETHING_WENT_WRONG');
		$code 	= ($data > 0 )?SUCCESS:INTERNAL_SERVER_ERROR;
		return response()->json(array("code" => $code,"msg"=>$msg,"data" => $data));
	}

	/*
	Use     : Get Breakdown Details List
	Author 	: Kalpak Prajapati
	Date 	: 29 November 2023
	*/
	public function getBreakdownDetailsList(Request $request)
	{ 
		$data = IOTDeviceBreakdownDetails::getRecordsList($request);
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND"):trans("message.RECORD_NOT_FOUND");
		return response()->json(["data" => $data,"msg" =>$msg,'code' => STATUS_CODE_SUCCESS]);
	}

	/**
	Use 	: get Breakdown Details
	Author 	: Kalpak Prajapati
	Date 	: 29 November 2023
	*/
	public function getBreakdownDetails(Request $request)
	{
		$data 	= IOTDeviceBreakdownDetails::getRecordById($request);
		$msg 	= (!empty($data))?trans('message.RECORD_FOUND'):trans('message.SOMETHING_WENT_WRONG');
		$code 	= (!empty($data))?SUCCESS:INTERNAL_SERVER_ERROR;
		return response()->json(array("code" => $code,"msg"=>$msg,"data" => $data));
	}

	/**
	Use 	: get Breakdown Device Reasons
	Author 	: Kalpak Prajapati
	Date 	: 01 December 2023
	*/
	public function getBreakdownDeviceReasons(Request $request)
	{
		$data 	= IOTDeviceMaintenanceReasonActions::getBreakdownReasons($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data" => $data));
	}

	/**
	Use 	: get Breakdown Device Reason Action
	Author 	: Kalpak Prajapati
	Date 	: 01 December 2023
	*/
	public function getBreakdownDeviceReasonActions(Request $request)
	{
		$data 	= IOTDeviceMaintenanceReasonActions::getBreakdownReasonActions($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data" => $data));
	}

	public function doOpenedProcess(Request $request)
	{			
		$lastinsertid = IOTDeviceBreakdownDetailLog::doOpenedProcess($request);
		if($lastinsertid) {
			$this->SendAndroidPush($request->id,$lastinsertid);
			$this->doMobileNotify($request->id,$lastinsertid);
			return response()->json(array("code" => SUCCESS,"msg"=>"Breakdown raised successfully."));
		}
	}

	public function doStartedProcess(Request $request)
	{	

		$validator 	= Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
        	return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->errors(),'data'=>'']);
		} else {
			$createdAt = IOTDeviceBreakdownDetails::getCreatedAt($request->id);
			$validator 	= Validator::make($request->all(), [
	            'start_at' => 'required|date_format:Y-m-d H:i:s|after_or_equal:' .$createdAt
	        ]);

            if ($validator->fails()) {
	        	return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->errors(),'data'=>'']);
			}
		}

		$lastinsertid = IOTDeviceBreakdownDetailLog::doStartedProcess($request);

		if($lastinsertid) {
			$this->SendAndroidPush($request->id,$lastinsertid);
			$this->doMobileNotify($request->id,$lastinsertid);
			return response()->json(array("code" => SUCCESS,"msg"=>"Breakdown started successfully."));
		}
	}

	public function doCompletedProcess(Request $request)
	{
		$validator 	= Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
        	return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->errors(),'data'=>'']);
		} else {
			$createdAt = IOTDeviceBreakdownDetailLog::getLstCreatedAtForComplete($request->id);
			$validator 	= Validator::make($request->all(), [
	            'end_at' => 'required|date_format:Y-m-d H:i:s|after_or_equal:' . $createdAt,
	            'group_breakdown_reason_id' => 'required|integer',
	            'breakdown_reason_id' => Rule::requiredIf(function () use ($request) {
	            	if($request->has('group_breakdown_reason_id') && $request->group_breakdown_reason_id != 99999) {
	            		return true;
	            	}

	            	return false;
			    }),
	            'breakdown_reason_remark' => Rule::requiredIf(function () use ($request) {
	            	if($request->has('group_breakdown_reason_id') && $request->group_breakdown_reason_id == 99999) {
	            		return true;
	            	}

	            	return false;
			    }),
			    'corrective_action_remark' => Rule::requiredIf(function () use ($request) {
			        if($request->has('breakdown_reason_id') && $request->breakdown_reason_id == 99999) {
	            		return true;
	            	}

	            	return false;
			    })
	        ]);

            if ($validator->fails()) {
	        	return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->errors(),'data'=>'']);
			}
		}

		$lastinsertid = IOTDeviceBreakdownDetailLog::doCompletedProcess($request);

		if($lastinsertid) {
			$this->SendAndroidPush($request->id,$lastinsertid);
			$this->doMobileNotify($request->id,$lastinsertid);
			return response()->json(array("code" => SUCCESS,"msg"=>"Breakdown completed successfully."));
		}
	}

	public function beforeDoCompletedProcess(Request $request)
	{
		$validator 	= Validator::make($request->all(), [
            'id' => 'required|integer'            
        ]);
		
        if ($validator->fails()) {
        	return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->errors(),'data'=>'']);
		} else {
			$data = IOTDeviceBreakdownDetailLog::beforeDoCompletedProcess($request);
			return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data" => $data));
		}
	}

	public function doClosedProcess(Request $request)
	{
		$validator 	= Validator::make($request->all(), [
            'id' => 'required|integer',
            'rating' => 'required|numeric|min:0|max:5'
        ]);

        if ($validator->fails()) {
        	return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->errors(),'data'=>'']);
		} else {
			$lastinsertid = IOTDeviceBreakdownDetailLog::doClosedProcess($request);
			if($lastinsertid) {
				$this->SendAndroidPush($request->id,$lastinsertid);
				$this->doMobileNotify($request->id,$lastinsertid);
				$this->doEmailNotify($request->id,$lastinsertid);
				return response()->json(array("code" => SUCCESS,"msg"=>"Breakdown closed successfully."));
			}
		}
	}

	public function doReopenedProcess(Request $request)
	{
		$validator 	= Validator::make($request->all(), [
            'id' => 'required|integer',
            'remarks' => 'required'
        ]);

        if ($validator->fails()) {
        	return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->errors(),'data'=>'']);
		} else {
			$lastinsertid = IOTDeviceBreakdownDetailLog::doReopenedProcess($request);
			if($lastinsertid) {
				$this->SendAndroidPush($request->id,$lastinsertid);
				$this->doMobileNotify($request->id,$lastinsertid);
				return response()->json(array("code" => SUCCESS,"msg"=>"Breakdown reopened successfully."));
			}
		}
	}

	public function getLogs(Request $request)
	{	
		$validator 	= Validator::make($request->all(), [
            'id' => 'sometimes|integer',
            'created_by' => 'sometimes|integer',
            'updated_by' => 'sometimes|integer'
        ]);

		if ($validator->fails()) {
			return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$validator->errors(),'data'=>'']);
		} else {
			$data 	= IOTDeviceBreakdownDetailLog::getLogs($request);
			if(!$data->isEmpty()) {
				return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data" => $data));
			} else {
				return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_NOT_FOUND"),"data" => $data));
			}
		}
	}

	public function currentUserRightsForBreakdown(Request $request){
		if(Auth()->user()->adminuserid) {
			$adminUserid  = Auth()->user()->adminuserid;
			$users = AdminUserRights::where('adminuserid',$adminUserid)->pluck('trnid')->toArray();
			if(!empty($users)) {
				return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_FOUND'),"data"=>$users]);
			} else {
				return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_NOT_FOUND'),"data"=> []]);
			}
		}
		
		return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>trans('message.SOMETHING_WENT_WRONG'),"data"=>""]);
	}    

	public function SendAndroidPush($breakdown_id,$lastinsertid) 
	{
		$user_tokens = IOTDeviceBreakdownDetailLog::retrieveUserTokens($breakdown_id);		
		if(!empty($user_tokens)) {
			$SMS_TEXT 			= IOTDeviceBreakdownDetails::retrieveSMSText($breakdown_id,$lastinsertid);
			$MESSAGE			= urlencode($SMS_TEXT);
			$server_key 	= "AAAAxtr1tSw:APA91bFT4gcYA-BRPsOOZSleM9AShj6SbxS4qHpU5ftORvDmkcvgpxGMjgvH0VdDRA8OSc9PQlml5y9gkDHCfZPKCIiztKuAWMUBf1PdUKPT12wy64MVh6STCMpJJTBIdBO0wXavsGb7"; // Firebase key
			$ndata 			= array("title"=>"Breakdown Maintenance Module","body"=>$SMS_TEXT,"type" => "dispatch");
			$url 			= 'https://fcm.googleapis.com/fcm/send';
			$fields 		= array();
			$fields['data'] = $ndata;
			$fields['registration_ids'] 	= $user_tokens;
			$headers 		= array('Content-Type:application/json','Authorization:key='.$server_key);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
			$result = curl_exec($ch);
			if ($result === FALSE) {
			    return ('FCM Send Error: ' . curl_error($ch));
			}
			curl_close($ch);
		}
	}

	public function doMobileNotify($breakdown_id,$lastinsertid)
	{	
		if($lastinsertid) {
			// send SMS notification 
			if(!SMS_ON) {
				$mobile_nos = IOTDeviceBreakdownDetailLog::retrieveMobile($lastinsertid);
				$MOBILENOS	= "";
				$COMMA		= "";
				foreach ($mobile_nos as $mobile) {
					$mobile			= trim($mobile);
					$countrycode 	= substr($mobile,0,2);
					if ($countrycode != MOBILE_COUNTRY_CODE && strlen($mobile) == MOBILE_DIGIT_LENGHT) {
						$MOBILENOS .= $COMMA.MOBILE_COUNTRY_CODE.$mobile;
						$COMMA		= ",";
					}
				}

				if ($MOBILENOS != "") 
				{
					$MOBILE	 			= $MOBILENOS;
					$SMS_TEXT 			= IOTDeviceBreakdownDetails::retrieveSMSText($breakdown_id,$lastinsertid);
					$MESSAGE			= urlencode($SMS_TEXT);
					$FIND_ARRAY			= array("[SMS_USER]","[SMS_PASS]","[MESSAGE]","[MOBILE]");
					$MOBILE	 			= $MOBILENOS;
					$REPL_ARRAY			= array(SMS_USER,SMS_PASS,$MESSAGE,$MOBILE);
					$SMS_GATEWAY_URL 	= str_replace($FIND_ARRAY,$REPL_ARRAY,SMS_GATWAY_URL);
					$ch 				= curl_init($SMS_GATEWAY_URL);
					curl_setopt($ch, CURLOPT_HEADER,0);  			// DO NOT RETURN HTTP HEADERS
					curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  	// RETURN THE CONTENTS
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT  ,0);
					$SMS_CONTENT 		= curl_exec($ch);
					IOTDeviceBreakdownDetailLog::saveSMSResponse($SMS_CONTENT,$lastinsertid);
				}
			}
			return true;
		}

		return false;
	}

	public function doEmailNotify($breakdown_id,$lastinsertid)
	{	
		if($lastinsertid) {
			// send EMAIL notification 
			$result = IOTDeviceBreakdownDetails::retrieveEMAILData($breakdown_id,$lastinsertid);
			if(!empty($result)) {
				$Attachments    = array();
				$Subject = 'Breakdown maintenance event breakdown no. '.$result['breakdown_no'].' is closed.';
				$FromEmail = array('Email' => 'info@letsrecycle.in', 'Name' => 'NEPRA (Breakdown Info)');
				$ToEmail = IOTDeviceBreakdownDetailLog::retrieveTos($lastinsertid);
				$ToEmail = array_values(array_filter($ToEmail));
				
				$ToEmail = array('bhadresh.ramani@nepra.co.in'); ///////				
				
				if(!empty($ToEmail)) {
					foreach($ToEmail as $ToEmail_s) {

						$sendEmail = Mail::send('email-template.breakdownemail',['result'=>$result], function ($message) use ($result,$ToEmail_s,$FromEmail,$Subject) {
							//$message->setBody($Email_text, 'text/html');
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to($ToEmail_s);
							$message->subject($Subject);
						});
					}
					IOTDeviceBreakdownDetailLog::saveEMAILResponse(json_encode($result, true),$lastinsertid);
				}
			}
			return true;
		}

		return false;
	}

	public function GetNotificationEmailHtml($id)
	{
		if($id) {
			$predefined = array('id' => '','reference_id' => '','label' => '','rating' => '','remarks' => '','start_at' => '','end_at' => '','created_by' => '');
		    $data = IOTDeviceBreakdownDetailLog::find($id)->toArray();
		    if (!empty($data)) {
		    	foreach($data AS $dataKey => $dataValue) {
		    		$predefined[$dataKey] = $dataValue;
		    	}
				$HTML_CONTENT = file_get_contents(PATH_ABSOLUTE_HTTP."images/email-template/iot_breakdown_service_process_info.blade.html");
				$SEARCH_ARRAY	= array("[id]","[reference_id]","[label]","[rating]","[remarks]","[start_at]","[end_at]","[created_by]");
				$REPLACE_ARRAY	= array($predefined['id'],$predefined['reference_id'],$predefined['label'],$predefined['rating'],$predefined['remarks'],$predefined['start_at'],$predefined['end_at'],$predefined['created_by']);
				return str_replace($SEARCH_ARRAY,$REPLACE_ARRAY,$HTML_CONTENT);
		    }
		}
	}
}