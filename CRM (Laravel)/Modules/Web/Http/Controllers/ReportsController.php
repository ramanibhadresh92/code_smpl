<?php
namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

/************************************************************
* File Name : ReportsController.php						    *
* purpose	: generalise class for all reports 				*
* @package  : Module/Web/Http/							    *
* @author 	: Kalpak Prajapati								*
* @since 	: 12-03-2019									*
************************************************************/

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Appoinment;
use App\Models\AppointmentCollectionDetail;
use App\Models\FocAppointmentStatus;
use App\Models\WmBatchMaster;
use App\Models\AdminGeoCode;
use App\Models\LogMaster;
use App\Models\LogAction;
use App\Models\WmDispatch;
use App\Models\OutWardLadger;
use App\Models\WmTransferMaster;
use App\Models\CustomerDailyCollectionReport;
use App\Models\CompanyBankAccountMaster;
use App\Models\AccountReport;
use App\Models\CCOFLocations;
use App\Models\CCOFMaster;
use App\Models\CustomerMaster;
use App\Models\WmProductMaster;
use App\Models\WmClientMaster;
use App\Models\WmSalesPaymentDetails;
use App\Imports\ImportSalesPaymentSheet;
use App\Models\VendorLedgerBalanceMaster;
use App\Classes\CCOF;
use DB;
use PDF;
use Excel;
class ReportsController extends LRBaseController
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

    /**
     *
     * The status of $DEFAULT_SLOT_ID is universe
     *
     * Potential value is integer
     *
     * @public DEFAULT_SLOT_ID
     *
     */
    public $DEFAULT_SLOT_ID      = 6;

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
	public $IP_ADDRESS 			= array("203.88.147.186","103.86.19.72","123.201.21.122","43.241.144.32","223.226.209.81");
	public $arrLocation 		= array("1"=>"iCreate","2"=>"Mahatma Mandir","3"=>"Exhibition Hall 2");
	public $selected_month 		= 0;
	public $selected_year 		= 0;
	public $slave_id 			= "";
	public $amp_slave_id 		= "";
	public $device_code 		= "";
	public $reportdate 			= "";
	public $client_ids 			= array();
	public $product_ids 		= array();
	public $summary_view 		= 0;

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND')]);
    }

    /**
	* Function Name : setVars
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
    public function setVars($Request)
    {
        $this->report_period    = (isset($Request->report_period) && !empty($Request->input('report_period')))? $Request->input('report_period') : '';
        $this->starttime        = (isset($Request->starttime) && !empty($Request->input('starttime')))? $Request->input('starttime') : '';
        $this->endtime          = (isset($Request->endtime) && !empty($Request->input('endtime')))? $Request->input('endtime') : '';
    }

    /**
	* Function Name : week_day
	* @param $dayofweek
	* @param $day
	* @param $month
	* @param $year
	* @return
	* @author Kalpak Prajapati
	*/
	public function week_day($dayofweek=1,$day="",$month="",$year="")
	{
		$day 	    = ($day == "")?date("d"):$day;
		$month 	    = ($month == "")?date("m"):$month;
		$year 	    = ($year == "")?date("Y"):$year;
		$loop_start = $day-(date('N', mktime(0, 0, 0, $month, $day, $year))-1);
		$loop_end   = $day+(7-(date('N', mktime(0, 0, 0, $month, $day, $year))));
		for($i = $loop_start; $i<=$loop_end; $i++)
		{
			$day_of_the_week    = date('N', mktime(0, 0, 0, $month, $i, $year));
			$loop_date          = date('d', mktime(0, 0, 0, $month, $i, $year));
			if($day_of_the_week == $dayofweek) {
				return date("Y-m-d",mktime(0, 0, 0, $month, $i, $year));
			}
		}
	}

    /**
	* Function Name : SetDefaultReportTime
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	public function SetDefaultReportTime($Request)
	{
		$this->setVars($Request);
		switch($this->report_period)
		{
			case 1:
			{
				$this->starttime    = date("Y-m-d")." 00:00:00";
				$this->endtime 	    = date("Y-m-d")." 23:59:59";
				break;
			}
			case 2:
			{
				$this->starttime    = date("Y-m-d", strtotime("-1 day"))." 00:00:00";
				$this->endtime 	    = date("Y-m-d", strtotime("-1 day"))." 23:59:59";
				break;
			}
			case 3:
			{
				$this->starttime = $this->week_day(1)." 00:00:00";
				$this->endtime 	= $this->week_day(7)." 23:59:59";
				break;
			}
			case 4:
			{
				$this->starttime    = date("Y")."-".date("m")."-01"." 00:00:00";
				$this->endtime	    = date('Y-m-d',strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))))." 23:59:59";
				break;
			}
			default:
			{
                if ($this->starttime != "" && $this->endtime != "") {
					$this->starttime    = date("Y-m-d",strtotime($this->starttime))." 00:00:00";
					$this->endtime      = date("Y-m-d",strtotime($this->endtime))." 23:59:59";
				}else{
					$this->starttime    = date("Y-m-d")." 00:00:00";
					$this->endtime 	    = date("Y-m-d")." 23:59:59";
				}
				break;
			}
		}
	}

    /**
	* @uses Get Customerwise Collection Report Statistics
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-12
	*/
    public function customerwisecollection(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return AppointmentCollectionDetail::GetCustomerWiseCollection($Request,$this->starttime,$this->endtime);
    }

    /**
	* @uses Get Collection / Audit Variance Report
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-12
	*/
    public function collectionvariance(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return AppointmentCollectionDetail::GetCollectionVariance($Request,$this->starttime,$this->endtime);
    }

    /**
	* @uses Get Unitwise Collection
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-12
	*/
    public function unitwisecollection(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return AppointmentCollectionDetail::GetUnitwiseCollection($Request,$this->starttime,$this->endtime);
    }

    /**
	* @uses Audit Franchise Collection Statistics
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-12
	*/
    public function auditcollection(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return AppointmentCollectionDetail::GetAuditCollection($Request,$this->starttime,$this->endtime);
	}

	/**
	* @uses Get inert collection list
	* @param
	* @return
	* @author Axay Shah
   	 * @since 2019-03-26
	*/
    public function GetInertcollectionlist(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        /** PATCH BY KP @11-04-2019 06:46 AM */
        $starttime 	= ($Request->has('params.appointment_from') && !empty($Request->input('params.appointment_from')))? $Request->input('params.appointment_from') : $this->starttime;
        $endtime 	= ($Request->has('params.appointment_to') && !empty($Request->input('params.appointment_to')))? $Request->input('params.appointment_to') : $this->endtime;
        if ($starttime != '--undefined' && $endtime != '--undefined') {
        	$this->starttime    = date("Y-m-d",strtotime($starttime))." 00:00:00";
        	$this->endtime      = date("Y-m-d",strtotime($endtime))." 23:59:59";
        }
        /** PATCH BY KP @11-04-2019 06:46 AM */
        return AppointmentCollectionDetail::GetInertcollectionlist($Request,$this->starttime,$this->endtime,true);
	}

	/**
	* @uses Get Appointment Details By Vehicle
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-27
	*/
    public function GetAppointmentDetailsByVehicle(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return AppointmentCollectionDetail::GetAppointmentDetailsByVehicle($Request,$this->starttime,$this->endtime,true);
	}

	/**
	* @uses Get Today Appointment Summary
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-27
	*/
    public function GetTodayAppointmentSummary(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return Appoinment::GetTodayAppointmentSummary($Request,$this->starttime,$this->endtime);
	}

	/**
	* @uses Get Duplicate Collections
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-27
	*/
    public function GetDuplicateCollections(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
		$AdminUserCompanyID = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$product_id         = (isset($Request->product_id) && !empty($Request->input('product_id')))? $Request->input('product_id') : 0;
		$vehicle_id         = (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0;
		$city_id         	= (isset($Request->city_id) && !empty($Request->input('city_id')))? $Request->input('city_id') : 0;
		$arrFilter 			= array("AdminUserCompanyID"=>$AdminUserCompanyID,
									"product_id"=>$product_id,
									"vehicle_id"=>$vehicle_id,
									"city_id"=>$city_id);
        return AppointmentCollectionDetail::GetDuplicateCollection($this->starttime,$this->endtime,true,$arrFilter);
	}

	/**
	* @uses Get Tally Report
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-27
	*/
    public function GetTallyReport(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
		$AdminUserCompanyID 	= isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$vehicle_id         	= (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0;
		$city_id         		= (isset($Request->city_id) && !empty($Request->input('city_id')))? $Request->input('city_id') : array();
		$customer_type      	= (isset($Request->customer_type) && !empty($Request->input('customer_type')))? $Request->input('customer_type') : array();
		$payment_id      		= (isset($Request->payment_id) && !empty($Request->input('payment_id')))? $Request->input('payment_id') : array();
		$customer_group     	= (isset($Request->customer_group) && !empty($Request->input('customer_group')))? $Request->input('customer_group') : array();
		$importid         		= (isset($Request->import_id) && !empty($Request->input('import_id')))? $Request->input('import_id') : 0;
		$without_foc        	= (isset($Request->without_foc) && !empty($Request->input('without_foc')))? $Request->input('without_foc') : "";
		$exclude_customer_type	= (isset($Request->exclude_customer_type) && !empty($Request->input('exclude_customer_type')))? $Request->input('exclude_customer_type') : array();
		$exclude_city_id		= (isset($Request->exclude_city_id) && !empty($Request->input('exclude_city_id')))? $Request->input('exclude_city_id') : array();
		$customer_code      	= (isset($Request->code) && !empty($Request->input('code')))? $Request->input('code') : "";
		$arrFilter 				= array("AdminUserCompanyID"=>$AdminUserCompanyID,
										"vehicle_id"=>$vehicle_id,
										"city_id"=>$city_id,
										"customer_type"=>$customer_type,
										"customer_group"=>$customer_group,
										"importid"=>$importid,
										"payment_id"=>$payment_id,
										"exclude_customer_type"=>$exclude_customer_type,
										"exclude_city_id"=>$exclude_city_id,
										"without_foc"=>$without_foc,
										"code"=>$customer_code);
        return AppointmentCollectionDetail::GetTallyReport($this->starttime,$this->endtime,true,$arrFilter);
	}

	/**
	* @uses Get Customerwise Tally Report
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-27
	*/
    public function GetCustomerwiseTallyReport(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
		$AdminUserCompanyID 	= isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$vehicle_id         	= (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0;
		$city_id         		= (isset($Request->city_id) && !empty($Request->input('city_id')))? $Request->input('city_id') : array();
		$customer_type      	= (isset($Request->customer_type) && !empty($Request->input('customer_type')))? $Request->input('customer_type') : array();
		$customer_group     	= (isset($Request->customer_group) && !empty($Request->input('customer_group')))? $Request->input('customer_group') : array();
		$payment_id      		= (isset($Request->payment_id) && !empty($Request->input('payment_id')))? $Request->input('payment_id') : array();
		$importid         		= (isset($Request->import_id) && !empty($Request->input('import_id')))? $Request->input('import_id') : 0;
		$without_foc        	= (isset($Request->without_foc) && !empty($Request->input('without_foc')))? $Request->input('without_foc') : "";
		$exclude_customer_type	= (isset($Request->exclude_customer_type) && !empty($Request->input('exclude_customer_type')))? $Request->input('exclude_customer_type') : array();
		$exclude_city_id		= (isset($Request->exclude_city_id) && !empty($Request->input('exclude_city_id')))? $Request->input('exclude_city_id') : array();
		$customer_code      	= (isset($Request->code) && !empty($Request->input('code')))? $Request->input('code') : "";
		$arrFilter 				= array("AdminUserCompanyID"=>$AdminUserCompanyID,
										"vehicle_id"=>$vehicle_id,
										"city_id"=>$city_id,
										"customer_type"=>$customer_type,
										"customer_group"=>$customer_group,
										"payment_id"=>$payment_id,
										"importid"=>$importid,
										"exclude_customer_type"=>$exclude_customer_type,
										"exclude_city_id"=>$exclude_city_id,
										"without_foc"=>$without_foc,
										"code"=>$customer_code);
        return AppointmentCollectionDetail::GetCustomerwiseTallyReport($this->starttime,$this->endtime,true,$arrFilter);
	}

	/**
	* @uses Get Product Variance Report
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-27
	*/
    public function GetProductVarianceReport(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return AppointmentCollectionDetail::GetProductVariance($Request,$this->starttime,$this->endtime);
	}

	/**
	* @uses Get Vehicle Fill Level Statistics
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-27
	*/
    public function GetVehicleFillLevelStatistics(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return AppointmentCollectionDetail::GetVehicleFillLevelStatistics($Request,$this->starttime,$this->endtime);
	}

	/**
	* @uses Get Route Collection Details
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-27
	*/
    public function GetRouteCollectionDetails(Request $Request)
    {
        $Month  = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));
        $Year  	= intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
        $RouteID= intval((isset($Request->route) && !empty($Request->input('route')))? $Request->input('route') : 0);
        $Month 	= empty($Month)?date("m"):$Month;
        $Year 	= empty($Year)?date("Y"):$Year;
        $this->starttime 	= $Year."-".$Month."-01 00:00:00";
        $this->endtime		= date("Y-m-t",strtotime($this->starttime))." 23:59:59";
        return FocAppointmentStatus::GetFocLocationCollectionDetails($RouteID,$this->starttime,$this->endtime);
	}

	/**
	* @uses Get Customer Type wise collection details
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-04-29
	*/
    public function GetCustomerTypewiseCollection(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return AppointmentCollectionDetail::GetCustomerTypewiseCollection($Request,$this->starttime,$this->endtime);
	}

	/**
	* @uses Get Customer Type wise collection Trends
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-04-29
	*/
    public function GetCustomerTypewiseCollectionYTD(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return AppointmentCollectionDetail::GetCustomerTypewiseCollectionYTD($Request,$this->starttime,$this->endtime);
	}

	/**
	* @uses Get Batch Summary Details
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-04-29
	*/
    public function GetBatchSummaryDetails(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return WmBatchMaster::GetBatchSummaryDetails($Request,$this->starttime,$this->endtime);
	}

	/**
	* @uses Gross Margin Productwise
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-04-29
	*/
    public function GrossMarginProductwise(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return AppointmentCollectionDetail::GrossMarginProductwise($Request,$this->starttime,$this->endtime);
	}

	private function SetTrackingPeriod($Request)
	{
		// var $appointment_track_hours 	= array("1"=>"12:00 AM - 02:00 AM","2"=>"02:00 AM - 04:00 AM","3"=>"04:00 AM - 06:00 AM","4"=>"06:00 AM - 08:00 AM","5"=>"08:00 AM - 10:00 AM","6"=>"10:00 AM - 12:00 PM","7"=>"12:00 PM - 02:00 PM","8"=>"02:00 PM - 04:00 PM","9"=>"04:00 PM - 06:00 PM","10"=>"06:00 PM - 08:00 PM","11"=>"08:00 PM - 10:00 PM","12"=>"10:00 PM - 12:00 AM");
		$appointment_track_hours_db	= array("1"=>array("00:00:00","01:59:59"),"2"=>array("02:00:00","03:59:59"),"3"=>array("04:00:00","05:59:59"),"4"=>array("06:00:00","07:59:59"),"5"=>array("08:00:00","09:59:59"),"6"=>array("10:00:00","11:59:59"),"7"=>array("12:00:00","13:59:59"),"8"=>array("14:00:00","15:59:59"),"9"=>array("16:00:00","17:59:59"),"10"=>array("18:00:00","19:59:59"),"11"=>array("20:00:00","21:59:59"),"12"=>array("22:00:00","23:59:59"));
		return (isset($appointment_track_hours_db[$Request->slot_id])?$appointment_track_hours_db[$Request->slot_id]:$appointment_track_hours_db[$this->DEFAULT_SLOT_ID]);
	}

	/**
	* @uses Vehicle Tracking Points
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-04-29
	*/
    public function VehicleTrackingPoints(Request $Request)
    {
        $Slot_Time 	= $this->SetTrackingPeriod($Request);
        $Track_Date = isset($Request->track_date)?date("Y-m-d",strtotime($Request->track_date)):date("Y-m-d");
        $starttime 	= $Track_Date." ".$Slot_Time[0];
        $endtime 	= $Track_Date." ".$Slot_Time[1];
        return AdminGeoCode::GetVehicleTrackingPoints($Request,$starttime,$endtime);
	}

	/**
	* @uses ActionLogReport
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-04-29
	*/
    public function ActionLogReport(Request $Request)
    {
    	$this->SetDefaultReportTime($Request);
        return LogMaster::GetActionLogDetails($Request,$this->starttime,$this->endtime);
	}


	/**
	* @uses GetMissedPaidAppointment
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-04-29
	*/
    public function GetMissedPaidAppointment(Request $Request)
    {
    	$this->SetDefaultReportTime($Request);
    	$AdminUserCompanyID 	= isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$vehicle_id         	= (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0;
		$city_id         		= (isset($Request->city_id) && !empty($Request->input('city_id')))? $Request->input('city_id') : array();
		$customer_type      	= (isset($Request->customer_type) && !empty($Request->input('customer_type')))? $Request->input('customer_type') : array();
		$customer_group     	= (isset($Request->customer_group) && !empty($Request->input('customer_group')))? $Request->input('customer_group') : array();
		$exclude_customer_type	= (isset($Request->exclude_customer_type) && !empty($Request->input('exclude_customer_type')))? $Request->input('exclude_customer_type') : array();
		$exclude_city_id		= (isset($Request->exclude_city_id) && !empty($Request->input('exclude_city_id')))? $Request->input('exclude_city_id') : array();
		$arrFilter 				= array("AdminUserCompanyID"=>$AdminUserCompanyID,
										"para_status_id"=>array(APPOINTMENT_SCHEDULED,APPOINTMENT_SCHEDULED_CANCELLED),
										"vehicle_id"=>$vehicle_id,
										"foc"=>0,
										"city_id"=>$city_id,
										"customer_type"=>$customer_type,
										"customer_group"=>$customer_group,
										"exclude_customer_type"=>$exclude_customer_type,
										"exclude_city_id"=>$exclude_city_id);
        return Appoinment::GetMissedAppointment($AdminUserCompanyID,$this->starttime,$this->endtime,$arrFilter,true);
	}

	/**
	* @uses GetMissedFocAppointment
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-04-29
	*/
    public function GetMissedFocAppointment(Request $Request)
    {
    	$this->SetDefaultReportTime($Request);
    	$AdminUserCompanyID 	= isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$vehicle_id         	= (isset($Request->vehicle_id) && !empty($Request->input('vehicle_id')))? $Request->input('vehicle_id') : 0;
		$city_id         		= (isset($Request->city_id) && !empty($Request->input('city_id')))? $Request->input('city_id') : array();
		$customer_type      	= (isset($Request->customer_type) && !empty($Request->input('customer_type')))? $Request->input('customer_type') : array();
		$customer_group     	= (isset($Request->customer_group) && !empty($Request->input('customer_group')))? $Request->input('customer_group') : array();
		$exclude_customer_type	= (isset($Request->exclude_customer_type) && !empty($Request->input('exclude_customer_type')))? $Request->input('exclude_customer_type') : array();
		$exclude_city_id		= (isset($Request->exclude_city_id) && !empty($Request->input('exclude_city_id')))? $Request->input('exclude_city_id') : array();
		$route         			= (isset($Request->route) && !empty($Request->input('route')))? $Request->input('route') : 0;
		$arrFilter 				= array("AdminUserCompanyID"=>$AdminUserCompanyID,
										"vehicle_id"=>$vehicle_id,
										"city_id"=>$city_id,
										"customer_type"=>$customer_type,
										"customer_group"=>$customer_group,
										"exclude_customer_type"=>$exclude_customer_type,
										"exclude_city_id"=>$exclude_city_id,
										"route" => $route);
        return FocAppointmentStatus::GetMissedFocAppointmentLocation($AdminUserCompanyID,$this->starttime,$this->endtime,$arrFilter,true);
	}

	/**
	Use  	: List Action Log
	Author 	: Axay Shah
	Date 	: 21 June,2019
	*/
    public function ActionList(Request $request)
    {
    	$data = LogAction::LogActionListing();
    	(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	Use  	: List Action Log
	Author 	: Axay Shah
	Date 	: 21 June,2019
	*/
    public function TableList(Request $request)
    {

    	$array 	= array();
    	$data = \DB::select('SHOW TABLES');
    	foreach($data as $d){
    		$array[]['Tables_in_letsrecycle_admin'] = $d->Tables_in_letsrecycle_backoffice;
    	}
    	(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$array]);
	}

	/**
	* @uses Get Today Appointment Summary
	* @param
	* @return
	* @author Kalpak Prajapati
    * @since 2019-03-27
	*/
    public function GetTodayBOPAppointmentSummary(Request $Request)
    {
        $this->SetDefaultReportTime($Request);
        return Appoinment::GetTodayAppointmentSummary($Request,$this->starttime,$this->endtime,true);
	}

	/**
	Use  	: Sales Register Party wise report
	Author 	: Axay Shah
	Date 	: 03 June,2020
	*/
    public function SalesRegisterPartyWiseReport(Request $request)
    {
    	$data = WmDispatch::SalesRegisterPartyWiseReport($request);
    	(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	Use  	: Get OutWard List of Transfer and sales of product
	Author 	: Axay Shah
	Date 	: 05 Sep,2019
	*/
	public function GetOutwardList(Request $Request)
    {
        $Month  = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));
        $Year  	= intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
        $RouteID= intval((isset($Request->mrf_id) && !empty($Request->input('mrf_id')))? $Request->input('mrf_id') : 0);
        $Month 	= empty($Month)?date("m"):$Month;
        $Year 	= empty($Year)?date("Y"):$Year;
        $this->starttime 	= $Year."-".$Month."-01";
        $this->endtime		= date("Y-m-t",strtotime($this->starttime));
        return OutWardLadger::GetOutWordReport($RouteID,$this->starttime,$this->endtime);
	}

	/**
	Use  	: Transfer Report
	Author 	: Axay Shah
	Date 	: 1 June,2020
	*/
    public function TransferReport(Request $request)
    {
    	$data = WmTransferMaster::TransferReport($request);
    	(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	Use  	: Appointment invoice Report
	Author 	: Axay Shah
	Date 	: 1 June,2020
	*/
    public function PendingAppointmentInvoiceReport(Request $request)
    {
    	$data = Appoinment::PendingAppointmentInvoiceReport($request);
    	(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	Use  	: Customer Collection wise daily report
	Author 	: Axay Shah
	Date 	: 05 Feb 2021
	*/
    public function CustomerDailyCollectionReport(Request $request)
    {
    	$data = CustomerDailyCollectionReport::CustomerDailyCollectionReport($request);
    	(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	Use  	: Aggregator P&L report
	Author 	: Axay Shah
	Date 	: 12 May 2021
	*/
	public function AggregatorPLReport(Request $request)
    {
    	$data = WmDispatch::AggregatorPLReport($request);
    	(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	
	/**
	Use  	: Pending Invoice Payment Report
	Author 	: Axay Shah
	Date 	: 04 Octomber,2021
	*/
    public function PendingInvoicePaymentReport(Request $request)
    {
    	$data = Appoinment::PendingInvoicePaymentReport($request);
    	(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	Use  	: Pending Invoice Payment Report
	Author 	: Axay Shah
	Date 	: 04 Octomber,2021
	*/
    public function GetBankAccountDropDown(Request $request)
    {
    	$data = CompanyBankAccountMaster::GetBankAccountDropDown($request);
    	(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/**
	Use  	: GET HSN WISE REPORT
	Author 	: Axay Shah
	Date 	: 04 Octomber,2021
	*/
    public function GetHSNWiseReport(Request $request)
    {
    	$data = WmDispatch::GetHSNWiseSalesDetailsReport($request->all());
    	(count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/**
	Use 	: B2B Report for Account
	Author 	: Axay Shah
	Date 	: 25 May 2022
	*/
    public function B2BAccountReport(Request $request)
    {
    	$data = AccountReport::B2BAccountReport($request->all());
    	($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/**
	Use 	: B2B CN/DN Report for Account
	Author 	: Axay Shah
	Date 	: 25 May 2022
	*/
    public function B2BCnDnReport(Request $request)
    {
    	$data = AccountReport::B2BCnDnReport($request->all());
    	($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	* Function Name : SetVariables
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
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
		$this->client_ids 		= isset($request['client_ids'])?$request['client_ids']:array();
		$this->product_ids 		= isset($request['product_ids'])?$request['product_ids']:array();
		$this->summary_view 	= isset($request['summary_view'])?$request['summary_view']:0;
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

	/**
	* Function Name : getCCOFSummaryReport
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public function getCCOFSummaryReport(Request $request)
	{
		$this->SetVariables($request);
		$arrMRF 				= array();
		$arrResult 				= array();
		$this->report_starttime = !empty($this->report_starttime)?$this->report_starttime:"2022-03-01 ".GLOBAL_START_TIME;
		$this->report_endtime 	= !empty($this->report_endtime)?$this->report_endtime:date("Y-m-t",strtotime($this->report_starttime))." ".GLOBAL_END_TIME;
		$arrLocations 			= array();
		if (!empty($this->location_id)) {
			$arrCCOFLocationMaster = CCOFLocations::whereIn("id",$this->location_id)->where("status",1)->get();
			if (!empty($arrCCOFLocationMaster)) {
				foreach ($arrCCOFLocationMaster as $arrResult) {
					if ($arrResult->baselocation_id != "") array_push($this->basestation_id,$arrResult->baselocation_id);
					if ($arrResult->mrf_ids != "") array_push($this->mrf_id,$arrResult->mrf_ids);
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
		$TopSuppliers						= CCOF::getTopSuppliers($this->report_starttime,$this->report_endtime,$this->basestation_id,$this->mrf_id);
		$TopClients							= CCOF::getTopClients($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$InwardMaterialComposition			= CCOF::InwardMaterialComposition($this->report_starttime,$this->report_endtime,$this->basestation_id,true,$this->mrf_id);
		$OutwardMaterialComposition			= CCOF::OutwardMaterialComposition($this->report_starttime,$this->report_endtime,$this->mrf_id,true);
		$TotalMaterialProcessed				= CCOF::TotalMaterialProcessed($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalMaterialOutwardR				= CCOF::TotalMaterialOutward($this->report_starttime,$this->report_endtime,RECYCLEBLE_TYPE,$this->mrf_id,true);
		$TotalMaterialOutwardNR				= CCOF::TotalMaterialOutward($this->report_starttime,$this->report_endtime,NON_RECYCLEBLE_TYPE,$this->mrf_id,true);
		$TotalInertMaterial					= CCOF::TotalInertMaterial($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalRDFMaterial					= CCOF::TotalRDFMaterial($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$TotalInwardMaterialCost			= CCOF::TotalInwardMaterialCost($this->report_starttime,$this->report_endtime,$this->basestation_id,true,$this->mrf_id);
		$TotalSalesRevenueDetails			= CCOF::TotalSalesRevenueDetails($this->report_starttime,$this->report_endtime,$this->mrf_id,true);
		$TotalServicesRevenueDetails		= CCOF::TotalServicesRevenueDetails($this->report_starttime,$this->report_endtime,$this->mrf_id);
		$GetRetentionRatio 					= CCOF::GetRetentionRatio($this->report_starttime,$this->report_endtime,$arrLocations,$this->company_id);
		$GetCarbonMitigationAndEnergySaving = CCOF::GetCarbonMitigationAndEnergySaving($this->report_starttime,$this->report_endtime,$this->basestation_id,true,$this->mrf_id);
		$arrCCOFLocations 					= CCOFLocations::where("status",1)->pluck("location_title","id");
		$CCOF 								= new CCOF();
		$CCOF->TotalManpowerInformation 	= new \stdClass();
		$CCOF->ExpensesAndRevnue 			= new \stdClass();
		$CCOF->Compliance 					= new \stdClass();
		$CCOF->Grievance_Matrix 			= new \stdClass();
		$CCOF->Employment_Summary 			= new \stdClass();
		$CCOF->TotalManpowerInformation($this->report_starttime,$this->report_endtime,$arrLocations,$this->location_id,$this->company_id);
		$CCOF->GetEmploymentSummary($this->report_starttime,$this->report_endtime,$arrLocations,$this->company_id);

		$operations 								= [];
		$operating_financials 						= [];
		$employment_hr 								= [];
		$operations['TopSuppliers']					= $TopSuppliers;
		$operations['TopClients']					= $TopClients;
		$operations['InwardMaterialComposition']	= $InwardMaterialComposition;
		$operations['OutwardMaterialComposition']	= $OutwardMaterialComposition;
		$operations['OperationUtilities'] 			= array(array('text' => 'Total Material Processed (in MT)','value'=> $TotalMaterialProcessed),
															array('text' => 'Outward Material (Recyclable Material) (in MT)','value'=> $TotalMaterialOutwardR),
															array('text' => 'Residual Inert (in MT)','value'=> $TotalInertMaterial),
															array('text' => 'RDF (in MT)','value'=> $TotalRDFMaterial),
															array('text' => 'Electricity Consumed (Units)','value'=> $CCOF->ExpensesAndRevnue->Electricity_Consumed));
		$operations['Impact_data_operations'] 		= array(array('Particulars' => 'Male Waste Pickers','Details'=> $CCOF->ExpensesAndRevnue->Male_Waste_Pickers),
															array('Particulars' => 'Female Waste Pickers','Details'=> $CCOF->ExpensesAndRevnue->Female_Waste_Pickers),
															array('Particulars' => 'Customers','Details'=> $CCOF->ExpensesAndRevnue->Customers),
															array('Particulars' => 'New customers', 'Details'=> $CCOF->ExpensesAndRevnue->New_Customers),
															array('Particulars' => 'Diesel Consumption','Details'=> $CCOF->ExpensesAndRevnue->Diesel_Consumption));
		$Total_other_Revenue 						= number_format((_FormatNumber($TotalSalesRevenueDetails->Total_Revenue) + _FormatNumber($TotalServicesRevenueDetails->Total_Revenue) + _FormatNumber($CCOF->ExpensesAndRevnue->Other_Revenue)),2);
		$operating_financials['Revenue'] 			= array(array('name' => 'Sales from materials',
																'total_Revenue' => $TotalSalesRevenueDetails->Total_Revenue,
																'total_Tonne' => $TotalSalesRevenueDetails->Total_Tonne,
																'per_Tonne_Revenue' => $TotalSalesRevenueDetails->Per_Tonne_Revenue),
															array('name' => 'Long-term Service Contracts',
																'total_Revenue' => $TotalServicesRevenueDetails->Total_Revenue),
															array('name' => 'Other revenue',
																'total_Revenue' => $CCOF->ExpensesAndRevnue->Other_Revenue),
															array('name' => 'Total Revenue (INR in Mn)','total_Revenue' => $Total_other_Revenue));
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
		/** Added by Kalpak Based on Discussion with Ronak @since 01-11-2022 */
		$Total_Direct_Cost += $TotalInwardMaterialCost->Total_Cost;
		/** Added by Kalpak Based on Discussion with Ronak @since 01-11-2022 */

		$operating_financials['Cost'] 	= array('Material' 		=> array(array(	'name' => 'Inward',
																				'total_Cost' => $TotalInwardMaterialCost->Total_Cost,
																				'total_MT' => $TotalInwardMaterialCost->Weight_In_MT,
																				'per_MT' => $TotalInwardMaterialCost->Price_Per_MT)),
												'Labour' 		=> array(array('name' => 'Labour','total_Cost' => round($CCOF->ExpensesAndRevnue->Amount_Paid_To_Labour,2)),
																		array('name' => 'Overtime Paid','total_Cost' => round($CCOF->ExpensesAndRevnue->Overtime_Paid,2)),
																		array('name' => 'Benefits','total_Cost' => round($CCOF->ExpensesAndRevnue->Benefits_Paid,2))),
												'Operations' 	=> array(array('name' => 'Utilities','total_Cost' => round($CCOF->ExpensesAndRevnue->Utilities,2)),
																		array('name' => 'Maintenance & repairs','total_Cost' => round($CCOF->ExpensesAndRevnue->Maintenance_Repairs,2)),
																		array('name' => 'Other Direct Exp','total_Cost' => round($CCOF->ExpensesAndRevnue->Other_Direct_Exp,2)),
																		array('name' => 'Transportation','total_Cost' => round($CCOF->ExpensesAndRevnue->Transportation,2))),
												'Others' 		=> array(array('name' => 'SG&A','total_Cost' => round($CCOF->ExpensesAndRevnue->SGA,2)),
																		array('name' => 'Insurance','total_Cost' => round($CCOF->ExpensesAndRevnue->Insurance,2)),
																		array('name' => 'Total Direct Cost (INR in Mn)','total_Cost' => round($Total_Direct_Cost,2))));
		$workers_detail = [];
		foreach($CCOF->arrWorkers as $FieldType=>$FieldTitle)
		{
			$workers_data 			= [];
			$workers_data['title'] 	= $FieldTitle;
			switch($FieldType) {
				case 'TOTAL_WORKERS':
				case 'TOTAL_WORKERS_EX_NH':
				case 'TOTAL_NEW_WORKERS':
				case 'TOTAL_WORKERS_BENIFITS_PAID': {
					foreach($CCOF->arrWTypes as $WTitle=>$WType) {
						$FieldName 	= $WType."_".$FieldType;
						$FieldValue = (isset($CCOF->TotalManpowerInformation->$FieldName)?$CCOF->TotalManpowerInformation->$FieldName:0);
						$workers_data[$WType] = array('Male' => '0','Female' => '0','Common' => true,"Common_value" => $FieldValue);
					}
					break;
				}
				default: {
					foreach($CCOF->arrWTypes as $WTitle=>$WType) {
						foreach($CCOF->arrGender as $Gender) {
							$FieldName 	= $WType."_".$FieldType."_".$Gender;
							$FieldValue = (isset($CCOF->TotalManpowerInformation->$FieldName)?$CCOF->TotalManpowerInformation->$FieldName:0);
							$workers_data[$WType][$Gender] = $FieldValue;
						}
						$workers_data[$WType]['Common'] = false;
						$workers_data[$WType]['Common_value'] = '0';
					}
				}
			}
			$workers_detail[] = $workers_data;
		}
		$employment_hr['staff_workers_detail'] 	= $workers_detail;
		$employment_hr['retention_rate'] 		= array(array(	'Title' => 'staff',
																'Women' => $GetRetentionRatio->STAFF_F_RETENTION,
																'Man' => $GetRetentionRatio->STAFF_M_RETENTION),
														array(	'Title' => 'Workers',
																'Women' => $GetRetentionRatio->WORKER_F_RETENTION,
																'Man' => $GetRetentionRatio->WORKER_M_RETENTION));
		$employment_hr['statutory_compliance'] = [];
		if(!empty($CCOF->arrComplianceData)) {
			foreach($CCOF->arrComplianceData as $Field=>$FieldTitle) {
				$employment_hr['statutory_compliance'][] 	= array('Particulars' => str_replace("_"," ",$FieldTitle),
																	'Date_of_payment' => (isset($CCOF->Compliance->$Field)?$CCOF->Compliance->$Field:"-"));
			}
		}
		$employment_hr['employment_summary'] = [];
		if(!empty($CCOF->arrEmploymentSummary)) {
			foreach($CCOF->arrEmploymentSummary as $Field=>$FieldTitle) {
				$employment_hr['employment_summary'][] 	= array('title' => str_replace("_"," ",$FieldTitle),
																'value' => (isset($CCOF->Employment_Summary->$Field)?$CCOF->Employment_Summary->$Field:0));
			}
		}
		$employment_hr['grievance_matrix'] = [];
		if(!empty($CCOF->arrGrievanceMatrix)) {
			foreach($CCOF->arrGrievanceMatrix as $Field=>$FieldTitle) {
				$employment_hr['grievance_matrix'][] 	= array('title' => str_replace("_"," ",$FieldTitle),
																'value' => (isset($CCOF->Grievance_Matrix->$Field)?$CCOF->Grievance_Matrix->$Field:0));
			}
		}
		$result = array('operations' 						=> $operations,
						'operating_financials' 				=> $operating_financials,
						'employment' 						=> $employment_hr,
						'CarbonMitigationAndEnergySaving' 	=> $GetCarbonMitigationAndEnergySaving);
		return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $result]);
	}



	/**
	* Function Name : saveCCOFReportData
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public function saveCCOFReportData(Request $request)
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

	/**
	* Function Name : getDiversionCertificate
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-05-02
	*/
	public function getDiversionCertificate(Request $Request)
	{
		return CustomerMaster::getDiversionCertificateDetails($Request);
	}

	/**
	* Function Name : getRDFDashboardProducts
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-12-15
	*/
	public function getRDFDashboardProducts(Request $Request)
	{
		$arrProducts 	= array();
		$arrProductRow 	= WmProductMaster::where(array("wm_product_master.status"=>1))
							->leftjoin("wm_client_master_po_details","wm_client_master_po_details.wm_product_id","=", "wm_product_master.id")
							->whereNotNull("wm_client_master_po_details.id")
							->groupBy("wm_product_master.id")
							->orderBy("wm_product_master.title","ASC")
							->pluck("wm_product_master.title","wm_product_master.id");
		if (!empty($arrProductRow)) {
			foreach($arrProductRow as $ID=>$title) {
				$arrProducts[] = array("id"=>$ID,"title"=>$title);
			}
		}
		return response()->json(["code" => SUCCESS , "msg" =>"","data"=>$arrProducts]);
	}

	/**
	* Function Name : getRDFDashboardClients
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-12-15
	*/
	public function getRDFDashboardClients(Request $Request)
	{
		$arrClients 	= array();
		$arrClientsRow 	= WmClientMaster::where("wm_client_master.status","A")
							->leftjoin("wm_client_master_po_details","wm_client_master_po_details.wm_client_id","=", "wm_client_master.id")
							->whereNotNull("wm_client_master_po_details.id")
							->groupBy("wm_client_master.id")
							->orderBy("wm_client_master.client_name","ASC")
							->pluck("wm_client_master.client_name","wm_client_master.id");
		if (!empty($arrClientsRow)) {
			foreach($arrClientsRow as $ID=>$Client_Name) {
				$arrClients[] = array("id"=>$ID,"client_name"=>$Client_Name);
			}
		}
		return response()->json(["code" => SUCCESS , "msg" =>"","data"=>$arrClients]);
	}

	/**
	* Function Name : getRDFDashboardColumns
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-12-15
	*/
	public function getRDFDashboardColumns(Request $Request)
	{
		$arrColumns 	= array(["col_id"=>"INVOICE_NO","col_title"=>"Invoice No."],
								["col_id"=>"INVOICE_DATE","col_title"=>"Invoice Date"],
								["col_id"=>"SALES_QUANTITY","col_title"=>"Sales Qty."],
								["col_id"=>"SALES_REVENUE","col_title"=>"Sales Rev."],
								["col_id"=>"SALES_RATE_PER_KG","col_title"=>"Sales/KG"],
								["col_id"=>"FREIGHT_CHARGE","col_title"=>"Freight"],
								["col_id"=>"FREIGHT_RATE_PER_KG","col_title"=>"Freight/KG"],
								["col_id"=>"MRF_COST","col_title"=>"MRF Cost"],
								["col_id"=>"MRF_COST_PER_KG","col_title"=>"MRF Cost/KG"],
								["col_id"=>"CREDIT_AMT","col_title"=>"CN. Amt."],
								["col_id"=>"DEBIT_AMT","col_title"=>"DN. Amt."],
								["col_id"=>"MRF_COST_PER_KG","col_title"=>"MRF Cost/KG"],
								["col_id"=>"PROFIT_LOSS","col_title"=>"P & L"],
								["col_id"=>"PROFIT_LOSS_PER_KG","col_title"=>"P & L/KG"]);
		return response()->json(["code" => SUCCESS , "msg" =>"","data"=>$arrColumns]);
	}

	/**
	* Function Name : getRDFDashboard
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-12-15
	*/
	public function getRDFDashboard(Request $request)
	{
		$this->SetVariables($request);
		$STARTDATE 		= date("Y-m-d",strtotime($this->report_starttime));
		$ENDDATE 		= date("Y-m-d",strtotime($this->report_endtime));
		$WHERECOND 		= " AND (wm_product_master.is_afr = 1 OR wm_product_master.is_rdf = 1) ";
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
							wm_dispatch.approval_status,
							Trim(Replace(Replace(Replace(wm_department.department_name, 'MRF-', ''), 'MRF -', ''), 'MRF -', '')) AS Source,
							wm_client_master.client_name AS Destination,
							wm_product_master.id AS Product_ID,
							wm_product_master.is_rdf AS IS_RDF,
							wm_product_master.is_afr AS IS_AFR,
							wm_product_master.title AS Product_Name,
							DATE_FORMAT(wm_dispatch.dispatch_date,'%Y-%m-%d') AS Dispatch_Date,
							wm_dispatch.id AS Dispatch_ID,
							wm_dispatch.challan_no AS Invoice_No,
							DATE_FORMAT(wm_dispatch.dispatch_date,'%d-%b-%Y') AS Invoice_Date,
							wm_dispatch.bill_from_mrf_id AS MRF_ID,
							wm_dispatch_product.quantity AS DispatchQty,
							wm_dispatch_product.price AS Dispatch_Rate,
							wm_dispatch_product.net_amount AS Net_Amount,
							transporter_details_master.rate AS Transportation_Cost,
							transporter_details_master.demurrage as Demurrage,
							CASE WHEN 1=1 THEN (
								SELECT COUNT(0)
								FROM wm_dispatch AS MAP_INVOICE_DISPATCH
								WHERE MAP_INVOICE_DISPATCH.map_invoice_id = wm_dispatch.id
								AND wm_dispatch.dispatch_date BETWEEN '".$STARTDATE." ".GLOBAL_START_TIME."' AND '".$ENDDATE." ".GLOBAL_END_TIME."'
							) END AS MERGED_INVOICE_COUNT
							FROM wm_dispatch_product
							LEFT JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
							LEFT JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
							LEFT JOIN wm_department ON wm_department.id = wm_dispatch.bill_from_mrf_id
							LEFT JOIN transporter_details_master ON wm_dispatch.id = transporter_details_master.dispatch_id
							WHERE wm_dispatch.approval_status IN (1,4)
							AND wm_dispatch.dispatch_date BETWEEN '".$STARTDATE." ".GLOBAL_START_TIME."' AND '".$ENDDATE." ".GLOBAL_END_TIME."'
							$WHERECOND
							GROUP BY wm_dispatch_product.dispatch_id
							HAVING MERGED_INVOICE_COUNT <= 0
							ORDER BY Product_Name ASC, Source ASC, Destination ASC";
		$SELECTRES 		= DB::connection('master_database')->select($SELECT_SQL);
		$arrResult 		= array();
		$arrDates 		= array();
		$PrevID 		= 0;
		$T_S_R 			= 0;
		$T_S_Q 			= 0;
		$T_F_C 			= 0;
		$T_M_C 			= 0;
		$T_P_L 			= 0;
		$T_C_L 			= 0;
		$T_D_L 			= 0;
		if (!empty($SELECTRES))
		{
			$ROWID = 0;
			foreach ($SELECTRES as $SELECTROW)
			{
				$MATERIAL_TYPE = 105601; //default unshredded
				/** GET PROCESSING COST MONTHWISE FOR EACH MRF */
				if ($SELECTROW->IS_RDF) {
					$MATERIAL_TYPE = 105601;
				} else if ($SELECTROW->IS_AFR) {
					$MATERIAL_TYPE = 105602;
				}
				$Cost_Per_Kg 	= 0;
				$MRF_COST_SQL 	= "	SELECT none_shredding, single_shredding, double_shredding
									FROM wm_plant_rdf_cost_monthwise
									WHERE mrf_id = ".$SELECTROW->MRF_ID."
									AND c_year = ".date("Y",strtotime($SELECTROW->Dispatch_Date))."
									AND c_month = ".date("m",strtotime($SELECTROW->Dispatch_Date));
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
				$CN_Amount 		= 0;
				$DN_Amount 		= 0;
				$SALES_AMT 		= ($DispatchQty * $Dispatch_Rate);
				$CN_DN_SQL 		= "";
				if (!empty($SELECTROW->Dispatch_ID)) {
					$CN_DN_SQL 	= "	SELECT wm_invoices_credit_debit_notes_details.change_in, wm_invoices_credit_debit_notes_details.revised_rate,
									wm_invoices_credit_debit_notes_details.new_quantity,wm_invoices_credit_debit_notes_details.revised_net_amount,
									wm_invoices_credit_debit_notes.notes_type
									FROM wm_invoices_credit_debit_notes_details
									INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
									WHERE wm_invoices_credit_debit_notes.dispatch_id = ".$SELECTROW->Dispatch_ID."
									AND wm_invoices_credit_debit_notes_details.product_id = ".$SELECTROW->Product_ID."
									AND wm_invoices_credit_debit_notes.status = 3
									ORDER BY wm_invoices_credit_debit_notes.id DESC";
					$CN_DN_RES 	= DB::connection('master_database')->select($CN_DN_SQL);
					if (!empty($CN_DN_RES)) {
						$NDispatchQty 	= 0;
						$NDispatch_Rate = 0;
						foreach ($CN_DN_RES as $CN_DN_ROW) {
							if ($CN_DN_ROW->notes_type == 1) {
								$DN_Amount += $CN_DN_ROW->revised_net_amount;
							} else if ($CN_DN_ROW->notes_type == 0) {
								$CN_Amount += $CN_DN_ROW->revised_net_amount;
							}
						}
					} else {
						$SALES_AMT 	= round(($DispatchQty * $Dispatch_Rate),2);
					}
				} else {
					$SALES_AMT 	= round(($DispatchQty * $Dispatch_Rate),2);
				}
				$SALES_AMT 				+= $DN_Amount;
				$SALES_AMT 				= ($SALES_AMT - $CN_Amount);
				$SALES_AMT 				= round($SALES_AMT,2);
				$Dispatch_Rate 			= round((!empty($SALES_AMT)?($SALES_AMT / $DispatchQty):0),2);
				$FREIGHT_CHARGE 		= round(($SELECTROW->Transportation_Cost + $SELECTROW->Demurrage),2);
				$FREIGHT_RATE_PER_KG	= !empty($FREIGHT_CHARGE)?round(($FREIGHT_CHARGE/$DispatchQty),2):0;
				$MRF_COST				= round(($OriginalDisQty * $Cost_Per_Kg),2);
				$PROFIT_LOSS 			= round(($SALES_AMT - ($FREIGHT_CHARGE + $MRF_COST)),2);
				$PROFIT_LOSS_PER_KG 	= !empty($PROFIT_LOSS)?round(($PROFIT_LOSS/$OriginalDisQty),2):0;
				$CN_Amount 				= !empty($CN_Amount)?round($CN_Amount,2):0;
				$DN_Amount 				= !empty($DN_Amount)?round($DN_Amount,2):0;
				$arrResult[] 			= array("MRF"=>$SELECTROW->Source,
												"CLIENT"=>$SELECTROW->Destination,
												"PRODUCT"=>$SELECTROW->Product_Name,
												"INVOICE_NO"=>$SELECTROW->Invoice_No,
												"INVOICE_DATE"=>$SELECTROW->Invoice_Date,
												"SALES_QUANTITY"=>$SELECTROW->DispatchQty,
												"SALES_REVENUE"=>$SALES_AMT,
												"SALES_RATE_PER_KG"=>$Dispatch_Rate,
												"FREIGHT_CHARGE"=>$FREIGHT_CHARGE,
												"FREIGHT_RATE_PER_KG"=>$FREIGHT_RATE_PER_KG,
												"MRF_COST"=>$MRF_COST,
												"MRF_COST_PER_KG"=>$Cost_Per_Kg,
												"CREDIT_AMT"=>$CN_Amount,
												"DEBIT_AMT"=>$DN_Amount,
												"PROFIT_LOSS"=>$PROFIT_LOSS,
												"PROFIT_LOSS_PER_KG"=>$PROFIT_LOSS_PER_KG);
				$T_S_R += $SALES_AMT;
				$T_S_Q += $DispatchQty;
				$T_F_C += $FREIGHT_CHARGE;
				$T_M_C += $MRF_COST;
				$T_P_L += $PROFIT_LOSS;
				$T_C_L += $CN_Amount;
				$T_D_L += $DN_Amount;
			}
		}

		/** SUMMARY VIEW STARTS */
		$arrSummaryViewResult 			= array();
		$arrTempArray["SALES_QUANTITY"] = 0;
		$arrTempArray["SALES_REVENUE"] 	= 0;
		$arrTempArray["FREIGHT_CHARGE"] = 0;
		$arrTempArray["MRF_COST"] 		= 0;
		$arrTempArray["PROFIT_LOSS"] 	= 0;
		$PrevColGroup 					= "";
		$ColID 							= 0;
		if ($this->summary_view) {
			foreach ($arrResult as $RowID => $ResultRow) {
				$TempColGroup = md5(strtolower($ResultRow['PRODUCT'].$ResultRow['MRF'].$ResultRow['CLIENT']));
				if (!empty($PrevColGroup) && $PrevColGroup != $TempColGroup) {
					$arrTempArray["SALES_REVENUE"] 	= round($arrTempArray["SALES_REVENUE"],2);
					$arrTempArray["FREIGHT_CHARGE"] = round($arrTempArray["FREIGHT_CHARGE"],2);
					$arrTempArray["MRF_COST"] 		= round($arrTempArray["MRF_COST"],2);
					$arrTempArray["PROFIT_LOSS"] 	= round($arrTempArray["PROFIT_LOSS"],2);
					$SALES_RATE_PER_KG 				= round((!empty($arrTempArray["SALES_REVENUE"])?($arrTempArray["SALES_REVENUE"] / $arrTempArray["SALES_QUANTITY"]):0),2);
					$FREIGHT_RATE_PER_KG 			= round((!empty($arrTempArray["FREIGHT_CHARGE"])?($arrTempArray["FREIGHT_CHARGE"] / $arrTempArray["SALES_QUANTITY"]):0),2);
					$MRF_COST_PER_KG 				= round((!empty($arrTempArray["MRF_COST"])?($arrTempArray["MRF_COST"] / $arrTempArray["SALES_QUANTITY"]):0),2);
					$PROFIT_LOSS_PER_KG 			= round((!empty($arrTempArray["PROFIT_LOSS"])?($arrTempArray["PROFIT_LOSS"] / $arrTempArray["SALES_QUANTITY"]):0),2);
					$arrSummaryViewResult[$ColID] 	= array("PRODUCT"=>$arrTempArray['PRODUCT'],
															"MRF"=>$arrTempArray['MRF'],
															"CLIENT"=>$arrTempArray['CLIENT'],
															"SALES_QUANTITY"=>$arrTempArray["SALES_QUANTITY"],
															"SALES_REVENUE"=>$arrTempArray["SALES_REVENUE"],
															"SALES_RATE_PER_KG"=>$SALES_RATE_PER_KG,
															"FREIGHT_CHARGE"=>$arrTempArray["FREIGHT_CHARGE"],
															"FREIGHT_RATE_PER_KG"=>$FREIGHT_RATE_PER_KG,
															"MRF_COST"=>$arrTempArray["MRF_COST"],
															"MRF_COST_PER_KG"=>$MRF_COST_PER_KG,
															"PROFIT_LOSS"=>$arrTempArray["PROFIT_LOSS"],
															"PROFIT_LOSS_PER_KG"=>$PROFIT_LOSS_PER_KG);

					$SALES_RATE_PER_KG 				= 0;
					$FREIGHT_RATE_PER_KG 			= 0;
					$MRF_COST_PER_KG 				= 0;
					$PROFIT_LOSS_PER_KG 			= 0;
					$arrTempArray["SALES_QUANTITY"] = 0;
					$arrTempArray["SALES_REVENUE"] 	= 0;
					$arrTempArray["FREIGHT_CHARGE"] = 0;
					$arrTempArray["MRF_COST"] 		= 0;
					$arrTempArray["PROFIT_LOSS"] 	= 0;
					$PrevColGroup 					= $TempColGroup;
					$ColID++;
				} else {
					$PrevColGroup = $TempColGroup;
				}
				$arrTempArray["PRODUCT"] 		= $ResultRow['PRODUCT'];
				$arrTempArray["MRF"] 			= $ResultRow['MRF'];
				$arrTempArray["CLIENT"] 		= $ResultRow['CLIENT'];
				$arrTempArray["SALES_QUANTITY"] += floatval($ResultRow['SALES_QUANTITY']);
				$arrTempArray["SALES_REVENUE"] 	+= floatval($ResultRow['SALES_REVENUE']);
				$arrTempArray["FREIGHT_CHARGE"] += floatval($ResultRow['FREIGHT_CHARGE']);
				$arrTempArray["MRF_COST"] 		+= floatval($ResultRow['MRF_COST']);
				$arrTempArray["PROFIT_LOSS"] 	+= floatval($ResultRow['PROFIT_LOSS']);
			}
			$arrTempArray["SALES_REVENUE"] 	= round($arrTempArray["SALES_REVENUE"],2);
			$arrTempArray["FREIGHT_CHARGE"] = round($arrTempArray["FREIGHT_CHARGE"],2);
			$arrTempArray["MRF_COST"] 		= round($arrTempArray["MRF_COST"],2);
			$arrTempArray["PROFIT_LOSS"] 	= round($arrTempArray["PROFIT_LOSS"],2);
			$SALES_RATE_PER_KG 				= round((!empty($arrTempArray["SALES_REVENUE"])?($arrTempArray["SALES_REVENUE"] / $arrTempArray["SALES_QUANTITY"]):0),2);
			$FREIGHT_RATE_PER_KG 			= round((!empty($arrTempArray["FREIGHT_CHARGE"])?($arrTempArray["FREIGHT_CHARGE"] / $arrTempArray["SALES_QUANTITY"]):0),2);
			$MRF_COST_PER_KG 				= round((!empty($arrTempArray["MRF_COST"])?($arrTempArray["MRF_COST"] / $arrTempArray["SALES_QUANTITY"]):0),2);
			$PROFIT_LOSS_PER_KG 			= round((!empty($arrTempArray["PROFIT_LOSS"])?($arrTempArray["PROFIT_LOSS"] / $arrTempArray["SALES_QUANTITY"]):0),2);
			$arrSummaryViewResult[$ColID] 	= array("PRODUCT"=>$ResultRow['PRODUCT'],
													"MRF"=>$ResultRow['MRF'],
													"CLIENT"=>$ResultRow['CLIENT'],
													"SALES_QUANTITY"=>$arrTempArray["SALES_QUANTITY"],
													"SALES_REVENUE"=>$arrTempArray["SALES_REVENUE"],
													"SALES_RATE_PER_KG"=>$SALES_RATE_PER_KG,
													"FREIGHT_CHARGE"=>$arrTempArray["FREIGHT_CHARGE"],
													"FREIGHT_RATE_PER_KG"=>$FREIGHT_RATE_PER_KG,
													"MRF_COST"=>$arrTempArray["MRF_COST"],
													"MRF_COST_PER_KG"=>$MRF_COST_PER_KG,
													"PROFIT_LOSS"=>$arrTempArray["PROFIT_LOSS"],
													"PROFIT_LOSS_PER_KG"=>$PROFIT_LOSS_PER_KG);
			$arrResult 		= array();
			$PrevTitle 		= "";
			$ColID 			= 0;
			$arrTempArray 	= array();
			foreach ($arrSummaryViewResult as $arrSummaryViewRow) {
				if (!empty($PrevTitle) && $PrevTitle != $arrSummaryViewRow['PRODUCT']) {
					$arrResult[$ColID]['PRODUCT'] 	= $PrevTitle;
					$arrResult[$ColID]['ROWS'] 		= $arrTempArray;
					$arrTempArray 					= array();
					$PrevTitle 						= $arrSummaryViewRow['PRODUCT'];
					$ColID++;
				}
				$arrTempArray[] = array("MRF"=>$arrSummaryViewRow['MRF'],
										"CLIENT"=>$arrSummaryViewRow['CLIENT'],
										"SALES_QUANTITY"=>$arrSummaryViewRow["SALES_QUANTITY"],
										"SALES_REVENUE"=>$arrSummaryViewRow["SALES_REVENUE"],
										"SALES_RATE_PER_KG"=>$arrSummaryViewRow['SALES_RATE_PER_KG'],
										"FREIGHT_CHARGE"=>$arrSummaryViewRow["FREIGHT_CHARGE"],
										"FREIGHT_RATE_PER_KG"=>$arrSummaryViewRow['FREIGHT_RATE_PER_KG'],
										"MRF_COST"=>$arrSummaryViewRow["MRF_COST"],
										"MRF_COST_PER_KG"=>$arrSummaryViewRow['MRF_COST_PER_KG'],
										"PROFIT_LOSS"=>$arrSummaryViewRow["PROFIT_LOSS"],
										"PROFIT_LOSS_PER_KG"=>$arrSummaryViewRow['PROFIT_LOSS_PER_KG']);
				$PrevTitle = $arrSummaryViewRow['PRODUCT'];
			}
			$arrResult[$ColID]['PRODUCT'] 	= $PrevTitle;
			$arrResult[$ColID]['ROWS'] 		= $arrTempArray;
		}
		/** SUMMARY VIEW ENDS */
		$arrGrandTotal['SALES_REVENUE'] 		= _FormatNumberV2($T_S_R,2);
		$arrGrandTotal['SALES_QUANTITY'] 		= _FormatNumberV2($T_S_Q,2);
		$arrGrandTotal['SALES_RATE_PER_KG'] 	= _FormatNumberV2((!empty($T_S_Q)?($T_S_R/$T_S_Q):0),2);
		$arrGrandTotal['FREIGHT_CHARGE'] 		= _FormatNumberV2($T_F_C,2);
		$arrGrandTotal['FREIGHT_RATE_PER_KG'] 	= _FormatNumberV2((!empty($T_S_Q)?($T_F_C/$T_S_Q):0),2);
		$arrGrandTotal['MRF_COST'] 				= _FormatNumberV2($T_M_C,2);
		$arrGrandTotal['MRF_COST_PER_KG'] 		= _FormatNumberV2((!empty($T_S_Q)?($T_M_C/$T_S_Q):0),2);
		$arrGrandTotal['CREDIT_AMT'] 			= _FormatNumberV2($T_C_L,2);
		$arrGrandTotal['DEBIT_AMT'] 			= _FormatNumberV2($T_D_L,2);
		$arrGrandTotal['PROFIT_LOSS'] 			= _FormatNumberV2($T_P_L,2);
		$arrGrandTotal['PROFIT_LOSS_PER_KG'] 	= _FormatNumberV2((!empty($T_S_Q)?($T_P_L/$T_S_Q):0),2);
		$arrResultData['Page_Title'] 			= "RDF/AFR P & L Report For (".$STARTDATE." To ".$ENDDATE.")";
		$arrResultData['arrResult'] 			= $arrResult;
		$arrResultData['arrGrandTotal'] 		= $arrGrandTotal;
		// $arrResultData['SELECT_SQL'] 			= $SELECT_SQL;
		return response()->json(["code" => SUCCESS , "msg" =>"","data"=>$arrResultData]);
	}

	/**
	* Function Name : importSalesPaymentSheet
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-12-15
	*/
	public function importSalesPaymentSheet(Request $request)
	{
		if($request->hasFile('document'))
		{
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
		} else {
			return response()->json(['code' => ERROR, 'msg' => trans('message.SOMETHING_WENT_WRONG'),'data'=>'']);
		}
	}

	/**
	* Function Name : SalesPaymentOutStandingReportDropDown
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-12-15
	*/
	public function SalesPaymentOutStandingReportDropDown(Request $request)
	{
		$data['Customer'] 			= WmSalesPaymentDetails::select("Customer")->groupBy('Customer')->orderBy('Customer','ASC')->get();
		$data['CustomerCategory'] 	= WmSalesPaymentDetails::select("CustomerCategory")->groupBy('CustomerCategory')->orderBy('CustomerCategory','ASC')->get();
		$data['Location'] 			= WmSalesPaymentDetails::select("Location")->groupBy('Location')->orderBy('Location','ASC')->get();
		$data['TransactionType'] 	= WmSalesPaymentDetails::select("TransactionType")->groupBy('TransactionType')->orderBy('TransactionType','ASC')->get();
		$data['Remarks'] 			= WmSalesPaymentDetails::select("Remarks")->groupBy('Remarks')->orderBy('Remarks','ASC')->get();
		return response()->json(['code' => ERROR, 'msg' => trans('message.RECORD_FOUND'),'data'=>$data]);
	}

	/**
	* Function Name : SalesPaymentOutStandingReport
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-12-15
	*/
	public function SalesPaymentOutStandingReport(Request $request)
	{
		$data = WmSalesPaymentDetails::ListOutStandingReport($request->all());
		return response()->json(['code' => ERROR, 'msg' => trans('message.RECORD_FOUND'),'data'=>$data]);
	}
	/*
	Use 	:  Get LR Vendor Ledger Data from Bams
	Author 	:  Hardyesh Gupta
	Date 	:  18 Sep 2023
	*/
	public function GetVendorLedgerBalanceData(Request $request){
		$data 	= VendorLedgerBalanceMaster::GetVendorLedgerBalanceData($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	:  Get LR Vendor Ledger Balance Report
	Author 	:  Hardyesh Gupta
	Date 	:  20 Sep 2023
	*/
	public function VendorLedgerBalanceReport(Request $request){
		$data 	= VendorLedgerBalanceMaster::VendorLedgerBalanceReport($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
}