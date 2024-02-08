<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
use App\Models\NepraImpactReport;
use App\Models\CompanyMaster;
use App\Classes\CCOF;
use JWTFactory;
use JWTAuth;
use Validator;
use File;
use Storage;
use Input;
use DB;
use Auth;
use Mail;

class CCOFReportController extends LRBaseController
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

	/*
	Use 	: To Store CCOF Data
	Date 	: 30 june 2022
	Author 	: Arun
	*/
	public function save_ccof_data_Api(Request $request)
	{
		$CCOFMaster 	= new CCOFMaster();
		$location_id 	= isset($request->selected_location)?$request->selected_location:0;
		$MONTH 			= isset($request->selected_month)?$request->selected_month:0;
		$YEAR 			= isset($request->selected_year)?$request->selected_year:0;
		if ($request->isMethod("post") && !empty($location_id) && !empty($MONTH) && !empty($YEAR)) {
			$CCOFMasterData = $CCOFMaster->where("location_id",$location_id)->where("month",$MONTH)->where("year",$YEAR)->first();
			if(!empty($CCOFMasterData->id)) {
				$CCOFMaster->saveRecord($request,$CCOFMasterData->id);
			} else {
				$CCOFMaster->saveRecord($request);
			}
			return response()->json(["code" => SUCCESS , "msg" =>'CCOF data updated successfully !!!',"data" => ""]);
		} else {
			return response()->json(["code" => VALIDATION_ERROR , "msg" =>'Location, Month and Year fields are required.',"data" => ""]);
		}
	}

	/*
	Use 	: To Get CCOF DATA for location,month and year
	Date 	: 30 june 2022
	Author 	: Arun
	*/
	public function getCcofDataApi(Request $request)
	{
		$CCOFMaster 	= new CCOFMaster();
		$location_id 	= isset($request->selected_location)?$request->selected_location:0;
		$MONTH 			= isset($request->selected_month)?$request->selected_month:0;
		$YEAR 			= isset($request->selected_year)?$request->selected_year:0;
		if ($request->isMethod("post") && !empty($location_id) && !empty($MONTH) && !empty($YEAR)) {
			$data['ccofData'] 	= $CCOFMaster->getCCOFDetails($location_id,$MONTH,$YEAR);
			$data['is_save'] 	= 1;
			$cur_month 			= date('m');
		   	if($MONTH == $cur_month){
		   		$data['is_save'] = 1;
		   	} else {
		   		if(($cur_month - 1) == $MONTH){
		   			if(date('d') < 16) {
		   				$data['is_save'] = 1;
		   			}
		   		}
		   	}
			return response()->json(["code" => SUCCESS , "msg" =>'Record Found.',"data" => $data]);
		} else {
			return response()->json(["code" => VALIDATION_ERROR , "msg" =>'Location, Month and Year fields are required.']);
		}
	}

	/*
	Use 	: To Get CCOF DATA for location,month and year
	Date 	: 30 june 2022
	Author 	: Arun
	*/
	public function GetMRFListForCCOF(Request $request){
		
		$CCOFMaster 	= new CCOFLocations();
		$location_id 	= isset($request->location_id)?$request->location_id:array();
		$data 			= CCOFLocations::GetMRFListForCCOF($location_id);
		return response()->json(["code" => SUCCESS , "msg" =>'Record Found.',"data" => $data]);
	} 

	/*
	Use 	: To Publish CCOF DATA for month and year
	Date 	: 30 june 2022
	Author 	: Arun
	*/
	public function publishImpactReport(Request $request)
	{
		$NepraImpactReport 	= new NepraImpactReport;
		$message 			= "Impact Report published failed.";
		$code 				= VALIDATION_ERROR;
		$r_month 			= isset($request->report_starttime)?date("m",strtotime($request->report_starttime)):0;
		$r_year 			= isset($request->report_starttime)?date("Y",strtotime($request->report_starttime)):0;
		$current_month 		= date("m");
		$current_year 		= date("Y");
		if ($r_year >= $NepraImpactReport->MIN_YEAR) {
			if ($r_month > $current_month && $r_year > $current_year) {
				$message = "Future report cannot be published.";
			} else if ($r_month > $NepraImpactReport->MIN_MONTH) {
				$NepraImpactReport = NepraImpactReport::where("r_month",$r_month)->where("r_year",$r_year)->first();
				if (!empty($NepraImpactReport) && $NepraImpactReport->id > 0) {
					if ($NepraImpactReport->status == STATUS_ACTIVE) {
						$message = "Impact report for selected period is already published.";
					} else {
						$message = "Impact report for selected period marked as un-published.";
					}
				} else {
					$NewReport 				= new NepraImpactReport;
					$NewReport->r_month 	= $r_month;
					$NewReport->r_year 		= $r_year;
					$NewReport->status 		= STATUS_ACTIVE;
					$NewReport->created_at 	= date("Y-m-d H:i:s");
					$NewReport->created_by 	= Auth()->user()->adminuserid;
					$NewReport->save();
					$this->SendPublishReportEmail($r_month,$r_year);
					$message 	= "Impact Report published successfully.";
					$code 		= SUCCESS;
				}
			} else {
				$message = "Select Period is not allowed to publish impact report. Please contact administrator.";
			}
		} else {
			$message = "Select Period is not allowed to publish impact report. Please contact administrator.";
		}
		return response()->json(["code"=>$code,"msg"=>$message]);
	}

	/*
	Use 	: Send Publish Report Email
	Date 	: 20 Dec 2022
	Author 	: Kalpak Prajapati
	*/
	private function SendPublishReportEmail($month,$year)
	{
		$Attachments    = array();
		$CompanyDetails = CompanyMaster::find(1);
		$ReportYear 	= date("Y",strtotime($year."-".$month."-01"));
		$ReportMonth 	= date("F",strtotime($year."-".$month."-01"));
		$Subject 		= "NEPRA Impact Report published for ".$ReportMonth."-".$ReportYear;
		$FromEmail      = array('Email'=>$CompanyDetails->company_email,'Name'=>$CompanyDetails->company_name);
		$ToEmail 		= $CompanyDetails->impact_report_email;
		$sendEmail      = Mail::send("email-template.impact_report_publish_email",array("Year"=>$ReportYear,"Month"=>$ReportMonth,"CompanyDetails"=>$CompanyDetails), function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to(explode(",",$ToEmail));
							$message->bcc(array("kalpak@nepra.co.in","ronakv@nepra.co.in"));
							$message->subject($Subject);
							if (!empty($Attachments)) {
								foreach($Attachments as $Attachment) {
									$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
								}
							}
						});
	}
}