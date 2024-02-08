<?php

namespace App\Models;

use App\Facades\LiveServices;
use Illuminate\Database\Eloquent\Model;
use App\Models\ViewFocAppointment;
use App\Models\VehicleDriverMappings;
use App\Models\Appoinment;
use App\Models\ViewFocAppointmentCustomer;
use App\Models\AppointmentCollectionDetail;
use App\Models\AppointmentCollection;
use App\Models\ViewCustomerMaster;
use App\Models\ViewProductMaster;
use App\Models\CompanyProductMaster;
use App\Models\SlabRateCardMaster;
use App\Models\ViewCompanyParameterParentChild;
use App\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class FocAppointment extends Model implements Auditable
{
	protected $table = 'foc_appointment';
	protected $primaryKey = 'appointment_id'; // or null
	protected $guarded = ['appointment_id'];
	public $timestamps = true;
	use AuditableTrait;
	/*
	Use     : Search & filter FOC appointment
	Author  : Axay Shah
	Date    : 11 Dec,2018
	*/
	public static function searchFocAppointment($request)
	{
		$starttime 		= "";
		$endtime 		= "";
		$cityId         = 	GetBaseLocationCity();
		$Today          = 	date('Y-m-d');
		$period 		= 	($request->has('params.period') && !empty($request->input('params.period'))) ? $request->input('params.period') : 1;
		$sortBy         = 	($request->has('sortBy') && !empty($request->input('sortBy'))) ? $request->input('sortBy') : "foc_appointment.appointment_id";
		$sortOrder      = 	($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = 	!empty($request->input('size')) ? $request->input('size') : DEFAULT_SIZE;
		$pageNumber     = 	!empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$Location       = 	new LocationMaster();
		$data           = 	self::SELECT("foc_appointment.appointment_id",
									"foc_appointment.map_appointment_id",
									"foc_appointment.route",
									"foc_appointment.customer_id",
									"foc_appointment.vehicle_id",
									"foc_appointment.collection_by",
									"foc_appointment.company_id",
									"foc_appointment.city_id",
									"foc_appointment.app_date_time",
									"foc_appointment.complete",
									"foc_appointment.cancel_reason",
									"foc_appointment.created_at",
									"foc_appointment.created_by",
									"foc_appointment.updated_at",
									"foc_appointment.updated_by",
									"u4.username",
									"vm.vehicle_number",
									\DB::raw("L.city as city_name"),
									\DB::raw("u1.para_value as route_name"),
									\DB::raw("CONCAT(u3.firstname,' ',u3.lastname) AS collection_by_user"),
									\DB::raw("CONCAT(cm.first_name,' ',cm.last_name) AS FOC_CUSTOMER_NAME"),
									\DB::raw("DATE_FORMAT(
									    foc_appointment.created_at,
									    '%Y-%m-%d'
									  ) AS date_create"
									),
									\DB::raw("DATE_FORMAT(
									    foc_appointment.updated_at,
									    '%Y-%m-%d'
									  ) AS date_update"
									),
									\DB::raw("(
									    CASE WHEN(foc_appointment.complete = 0) THEN 'Pending' WHEN(foc_appointment.complete = 1) THEN 'Complete' WHEN(foc_appointment.complete = 2) THEN 'Cancel' ELSE '-' END
									  ) AS status"
									),
									\DB::raw("(
									    CASE WHEN(1 = 1) THEN(
									    SELECT
									      SUM(cd.quantity)
									    FROM
									      
									        appointment_collection_details cd
									      LEFT JOIN
									        appointment_collection ac ON ac.collection_id = cd.collection_id
									        WHERE ac.appointment_id = foc_appointment.map_appointment_id
									  	) END
									  ) AS APP_QTY"
									),
									\DB::raw("(
									    CASE WHEN(1 = 1) THEN(
									    SELECT
									      SUM(fas.collection_qty)
									    FROM
									      foc_appointment_status fas
									    WHERE fas.appointment_id = foc_appointment.appointment_id
									  ) END
									  ) AS WEIGH_SCALE_QTY"
									)
								)
								->LEFTJOIN("customer_master as cm","foc_appointment.customer_id","=","cm.customer_id")
								->LEFTJOIN("company_parameter as u1","foc_appointment.route","=","u1.para_id")
								->LEFTJOIN("adminuser as u2","foc_appointment.created_by","=","u2.adminuserid")
								->LEFTJOIN("adminuser as u3","foc_appointment.collection_by","=","u3.adminuserid")
								->LEFTJOIN("adminuser as u4","foc_appointment.updated_by","=","u4.adminuserid")
								->LEFTJOIN("vehicle_master as vm","foc_appointment.vehicle_id","=","vm.vehicle_id")
								->JOIN($Location->getTable()." as L","foc_appointment.city_id","=","L.location_id")
								->where('foc_appointment.company_id', Auth()->user()->company_id);

				if ($request->has('params.appointment_id') && !empty($request->input('params.appointment_id'))) {
					$data->whereIn('foc_appointment.appointment_id', explode(",", $request->input('params.appointment_id')));
				}
				if ($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id'))) {
					$data->where('foc_appointment.vehicle_id', $request->input('params.vehicle_id'));
				}
				if ($request->has('params.route') && !empty($request->input('params.route'))) {
					$data->whereIn('foc_appointment.route', array($request->input('params.route')));
				}
				if ($request->has('params.city_id') && !empty($request->input('params.city_id'))) {
					$data->where('foc_appointment.city_id',$request->input('params.city_id'));
				}else{
					$data->whereIn('foc_appointment.city_id',$cityId);
				}
				if($request->has('params.status')){
					$is_status = $request->input('params.status');
					if($is_status != 0){
						$data->where('foc_appointment.complete',intval($is_status));
					}else if($is_status == "0"){
						$data->where('foc_appointment.complete',intval(0));	
					}
				}
				if (!empty($request->input('params.appointment_from')) && !empty($request->input('params.appointment_from'))) {
					$starttime  = date("Y-m-d", strtotime($request->input('params.appointment_from')))." 00:00:00";
					$endtime    = date("Y-m-d", strtotime($request->input('params.appointment_to')))." 23:59:59";
				} else if (!empty($request->input('params.appointment_from'))) {
					$starttime  = date("Y-m-d", strtotime($request->input('params.appointment_from')))." 00:00:00";
					$endtime    = date("Y-m-d", strtotime($request->input('params.appointment_from')))." 23:59:59";
				} else if (!empty($request->input('params.appointment_to'))) {
					$starttime  = date("Y-m-d", strtotime($request->input('params.appointment_to')))." 00:00:00";
					$endtime    = date("Y-m-d", strtotime($request->input('params.appointment_to')))." 23:59:59";
				} 
				if($period != 4 && !empty($starttime) && !empty($endtime)){
					$data->whereBetween('foc_appointment.app_date_time', [$starttime,$endtime]);
				}
				$data->orderBy($sortBy, $sortOrder);
				// LiveServices::toSqlWithBindingV2($data);

				if($request->has('ex') && !empty($request->input('ex')) && $request->input('ex') == EXPORT_ALL)
				{
					$recordPerPage = $data->count();
				}
				return $data->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	}
	
	/*
	Use     : Save Foc Appointment
	Author  : Axay Shah
	Date    : 11 Dec,2018
	*/
	public static function saveFocAppointment($request, $flag = true)
	{
		$collection_by  = VehicleDriverMappings::getVehicleMappedCollectionBy($request->vehicle_id);
		if (empty($collection_by)) $collection_by = 0;
		if ($flag == true) {
			$validation = Appoinment::setAppointmentValidation($request);
			if (isset($validation['code']) && !empty($validation['code'])) {
				return $validation;
			}
		}
		$customerId                 = (isset($request->customer_id) && !empty($request->customer_id)) ? $request->customer_id : 0;
		$cityId = 0 ;
		$route 						= (isset($request->route) && !empty($request->route)) ? $request->route : "";
		$address_id 				= 0;
		$billing_address_id 		= 0;
		$cityId = 0 ;
		if(!empty($route)){
			$cityId      			= ViewCompanyParameterParentChild::where('para_id',$route)->value('city_id');
			$address_id     		= CustomerAddress::where('customer_id',$customerId)->where('city',$cityId)->value('id');
			$billing_address_id 	= $address_id;
		}
		// if(!empty($customerId)){
		// 	$cityId = CustomerMaster::where('customer_id',$request->customer_id)->value('city');
		// }
		$foc = new self();
		$foc->customer_id           = $customerId;
		$foc->vehicle_id            = (isset($request->vehicle_id)      && !empty($request->vehicle_id)) ? $request->vehicle_id : 0;
		$foc->app_date_time         = (isset($request->app_date_time)   && !empty($request->app_date_time)) ? date("Y-m-d H:i:s", strtotime($request->app_date_time)) : "";
		$foc->route                 = (isset($request->route) && !empty($request->route)) ? $request->route : "";
		$foc->collection_by         = $collection_by;
		$foc->map_appointment_id    = (isset($request->map_appointment_id) && !empty($request->map_appointment_id)) ? $request->map_appointment_id : 0;
		$foc->created_by            = Auth()->user()->adminuserid;
		$foc->created_at            = date('Y-m-d H:i:s');
		$foc->city_id               = $cityId;
		$foc->cancel_reason 		= (isset($request->cancel_reason) && !empty($request->cancel_reason)) ? $request->cancel_reason : "";
		$foc->privious_app_id       = (isset($request->privious_app_id)      && !empty($request->privious_app_id)) ? $request->privious_app_id : 0;
		$foc->address_id            = $address_id;
		$foc->billing_address_id    = $billing_address_id;
		$foc->company_id            = Auth()->user()->company_id;
		if ($foc->save()) {
			$request->collection_by = $collection_by;
			$request->city_id       = $cityId;
			log_action('FOC_Appointment_Added', $foc->appointment_id, (new static)->getTable());
			self::addAppointmentInAppointmentMaster($foc->map_appointment_id, $request,$foc->appointment_id);
			LR_Modules_Log_CompanyUserActionLog($request,$foc->appointment_id);
			return true;
		} else {
			return false;
		}
	}

	/*
	Use     : Save Foc Appointment
	Author  : Axay Shah
	Date    : 11 Dec,2018
	*/

	public static function updateFocAppointment($request,$flag = true){
		$collection_by = VehicleDriverMappings::getVehicleMappedCollectionBy($request->vehicle_id);
		if (empty($collection_by)) $collection_by = 0;
		$route 						= (isset($request->route) && !empty($request->route)) ? $request->route : "";
		$address_id 				= 0;
		$billing_address_id 		= 0;
		// $cityId = 0 ;
		// if(!empty($customerId)){
		// 	$cityId = CustomerMaster::where('customer_id',$request->customer_id)->value('city');
		// }
		$cityId = 0 ;
		if(!empty($route)){
			$cityId      			= ViewCompanyParameterParentChild::where('para_id',$route)->value('city_id');
			$address_id     		= CustomerAddress::where('customer_id',$customerId)->where('city',$cityId)->value('id');
			$billing_address_id 	= $address_id;
		}
		if($flag == true){
			$validation = Appoinment::setAppointmentValidation($request);
			if(isset($validation['code']) && !empty($validation['code'])){
				return $validation;
			}

		}
		$foc                        = self::find($request->appointment_id);
		$foc->customer_id           = (isset($request->customer_id) && !empty($request->customer_id)) ? $request->customer_id : $foc->customer_id;
		$foc->vehicle_id            = (isset($request->vehicle_id) && !empty($request->vehicle_id)) ? $request->vehicle_id : $foc->vehicle_id;
		$foc->app_date_time         = (isset($request->app_date_time) && !empty($request->app_date_time)) ? date("Y-m-d H:i:s", strtotime($request->app_date_time)) : $foc->app_date_time;
		$foc->route                 = (isset($request->route) && !empty($request->route)) ? $request->route : $foc->route;
		$foc->collection_by         = $collection_by;
		$foc->map_appointment_id    = (isset($request->map_appointment_id) && !empty($request->map_appointment_id)) ? $request->map_appointment_id : $foc->map_appointment_id;
		$foc->complete              = (isset($request->complete) && !empty($request->complete)) ? $request->complete : $foc->complete;
		$foc->created_by            = Auth()->user()->adminuserid;
		$foc->created_at            = date('Y-m-d H:i:s');
		$foc->city_id               = $foc->city_id;
		$foc->company_id            = Auth()->user()->company_id;
		$foc->cancel_reason 		= (isset($request->cancel_reason) && !empty($request->cancel_reason)) ? $request->cancel_reason : "";
		$foc->privious_app_id       = (isset($request->privious_app_id)      && !empty($request->privious_app_id)) ? $request->privious_app_id : 0;
		$foc->address_id            = $address_id;
		$foc->billing_address_id    = $billing_address_id;
		
		if ($foc->save()) {
			log_action('FOC_Appointment_Updated', $foc->appointment_id, (new static)->getTable());
			self::addAppointmentInAppointmentMaster($foc->map_appointment_id, $foc,$foc->appointment_id);
			LR_Modules_Log_CompanyUserActionLog($request,$foc->appointment_id);
			return true;
		} else {
			return false;
		}
	}

	/*
	Use     : retrieveFOCAppointment
	Author  : Axay Shah
	Date    : 11 Dec,2018
	*/
	public static function retrieveFOCAppointment($appointment_id)
	{
		$query =    \DB::select("SELECT foc_appointment.*,
								CASE WHEN 1 = 1 THEN(
									SELECT SUM(collection_qty) 
									FROM foc_appointment_status 
									WHERE appointment_id = foc_appointment.appointment_id 
									GROUP BY appointment_id
								) END as collection_qty
							FROM foc_appointment WHERE foc_appointment.appointment_id = '$appointment_id'");
		if (count($query) <= 0) {
			return false;
		}
		return $query;
	}

	/*
	Use     : retrieveFOCAppointment
	Author  : Axay Shah
	Date    : 11 Dec,2018
	*/

	public static function addAppointmentInAppointmentMaster($appointment_id = 0, $request = "",$focAppointmentId = 0)
	{
		
		try{
			if (!empty($appointment_id)) {
				$appointment = Appoinment::getById($appointment_id);
				if ($appointment) {
					if(isset($request->vehicle_id)){
						$CollectionBy = VehicleDriverMappings::getVehicleMappedCollectionBy($request->vehicle_id);
						Appoinment::where('appointment_id', $appointment_id)->update(['vehicle_id' => $request->vehicle_id,'collection_by'=>$CollectionBy,"app_date_time"=>$request->app_date_time]);
	                    AppointmentCollection::where('appointment_id', $appointment_id)->update(['vehicle_id' => $request->vehicle_id,'collection_by'=>$CollectionBy]);
					}
					if (isset($request->vehicle_id) && !empty($request->vehicle_id) && $appointment->para_status_id == APPOINTMENT_NOT_ASSIGNED) {
						$request->para_status_id = APPOINTMENT_SCHEDULED;
					} else {
						$request->para_status_id = $appointment->para_status_id;
					}
				}
				$saveAppointmentRequest = Appoinment::saveAppointmentRequest($request,true);
				$LogRemark              = "Appointment $appointment->appointment_id updated in Appointment Master Table against FOC Appointment [$request->appointment_id].";
				log_action("FOC_Appointment_Updated", $request->appointment_id, (new static)->getTable(), false, $LogRemark);
			} else {

				if (!empty($request->vehicle_id)) {
					$request->para_status_id = APPOINTMENT_SCHEDULED;
				} else {
					$request->para_status_id = APPOINTMENT_NOT_ASSIGNED;
				}
				$request->app_type      = 0;
				$request->foc           = 1;
				$request->earn_type     = EARN_TYPE_FREE;
				$billing_address_id 	= 0;
				if(!empty($request->route)){
					$cityId      			= ViewCompanyParameterParentChild::where('para_id',$request->route)->value('city_id');
					$billing_address_id     = CustomerAddress::where('customer_id',$request->customer_id)->where('city',$cityId)->value('id');
				}
				$request->address_id 			= $billing_address_id;
				$request->billing_address_id 	= $billing_address_id;
				$saveAppointmentRequest = Appoinment::saveAppointmentRequest($request);
				if (!empty($saveAppointmentRequest)) {
					$data       = self::UpdateMapAppointmentID($saveAppointmentRequest->appointment_id, $focAppointmentId);
					$LogRemark  = "Appointment [$saveAppointmentRequest->appointment_id] added in Appointment Master Table against FOC Appointment [$focAppointmentId].";
					log_action('FOC_Appointment_Updated', $focAppointmentId, (new static)->getTable(), false, $LogRemark);
				}
			}
		}catch(\Exception $e){
			prd($e->getMessage()." ".$e->getLine()." ".$e->getFile());
		}
	}

	/*
	Use     : Update map appointment id
	Author  : Axay Shah
	Date    : 11 Dec,2018
	*/
	public static function UpdateMapAppointmentID($appointment_id, $focAppointmentId)
	{
		self::where('appointment_id', $focAppointmentId)
			->update([
				"map_appointment_id" => $appointment_id,
				"updated_at" => date("Y-m-d H:i:s"),
				"updated_by" => Auth()->user()->adminuserid
			]);
	}

	/*
	Use     : get By Id
	Author  : Axay Shah
	Date    : 12 Dec,2018
	*/
	public static function getById($appointment_id)
	{
		return self::find($appointment_id);
	}

	/*
	Use     :  View Customer for foc appointment
	Author  :  Axay Shah
	Date    :  12 Dec,2018
	*/
	public static function searchFOCAppointmentCustomer($request)
	{

		$Today          =   date('Y-m-d');
		$sortBy         =   ($request->has('sortBy') && !empty($request->input('sortBy'))) ? $request->input('sortBy') : "route_appointment_order";
		$sortOrder      =   ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  =   !empty($request->input('size')) ? $request->input('size') : DEFAULT_SIZE;
		$pageNumber     =   !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$cityId         =   GetBaseLocationCity();
		$data           =   ViewFocAppointmentCustomer::whereIn('city',$cityId)
							->where('company_id', Auth()->user()->company_id);
		if ($request->has('params.appointment_id') && !empty($request->input('params.appointment_id'))) {
			$data->whereIn('appointment_id', explode(",", $request->input('params.appointment_id')));
		}
		if ($request->has('params.map_appointment_id') && !empty($request->input('params.map_appointment_id'))) {
			$data->whereIn('map_appointment_id', explode(",", $request->input('params.map_appointment_id')));
		}
		$data->whereIn('collection_type',array(COLLECTION_TYPE_FOC,COLLECTION_TYPE_FOC_PAID));
		return $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	}


	/*
	Use     : cancle appointment
	Author  :  Axay Shah
	Date    :  12 Dec,2018
	*/
	public static function cancelFOCAppointment($request)
	{
		if(isset($request->appointment_id)) {
			$focAppointment = self::retrieveFOCAppointment($request->appointment_id);
			if (count($focAppointment) > 0) {
				$comment = (isset($request->cancel_reason) && !empty($request->cancel_reason)) ? $request->cancel_reason : "";
				foreach ($focAppointment as $foc) {
					if ($foc->complete != FOC_APPOINTMENT_CANCEL) {
						self::where('appointment_id', $foc->appointment_id)->update([
							"complete" 		=> FOC_APPOINTMENT_CANCEL,
							"cancel_reason" => $comment,
							"updated_at" 	=> date('Y-m-d H:i:s'),
							"updated_by" 	=> Auth()->user()->adminuserid
						]);
						log_action('FOC_Appointment_Cancel', $foc->appointment_id, (new static)->getTable());
						LR_Modules_Log_CompanyUserActionLog($request,$foc->appointment_id);
						if (!empty($foc->map_appointment_id)) {
							Appoinment::markAppointmentAsCancelled($foc->map_appointment_id,$comment);
						}
					}
				}
			}
			return true;
		} else {
			return false;
		}
	}

	/*
	Use     : Update Appointment ReachTime
	Author  : Sachin Shah
	Date    : 30 Jan,2018
	*/
	public static function UpdateFocAppointmentReachTime($appointment_id)
	{
		if (!empty($appointment_id)) {

			/*
				BECAUSE OF APPOINTMENT TIME REPORT ISSUE IMPLIMENTED COMMON LOGIC FOR APPOINTMENT & FOC APPOINTMENT
				DATE : 12 JULY 2019
			*/

			$created_by = Auth()->user()->adminuserid;
			$appData 	= self::getAppointmentReachAndFinalTime($appointment_id);

			$reachTime 		= (isset($appData->reached_time)) 	? $appData->reached_time 		: '0000-00-00 00:00:00';
			$finalTime 		= (isset($appData->final_time)) 	? $appData->final_time 			: '0000-00-00 00:00:00';
			$vehicle_id 	= (isset($appData->vehicle_id)) 	? $appData->vehicle_id 			: 0;
			$collection_by 	= (isset($appData->collection_by)) 	? $appData->collection_by 	: 0;
			$app_date_time 	= (isset($appData->app_date_time)) 	? $appData->app_date_time 	: '0000-00-00 00:00:00';
			AppointmentTimeReport::InsertTimeReportLog($appointment_id,$vehicle_id,$collection_by,$app_date_time,$reachTime,$finalTime,$created_by,$created_by);
			return true;

			/* NOT USING FOR NOW BECAUSE OF */

			// AppointmentTimeReport::find($appointment_id)
			// 	->where('para_report_status_id',APPOINTMENT_ACCEPTED)
			// 	->update(['endtime'=>$reachTime]);

			// AppointmentTimeReport::find($appointment_id)
			// 	->where('para_report_status_id',COLLECTION_STARTED)
			// 	->update(['starttime'=>$reachTime,'endtime'=>$finalTime]);

			// $selectData =   AppointmentTimeReport::where('appointment_id',$appointment_id)
			// 	->where('para_report_status_id',COLLECTION_COMPLETED)->get();
			// if(empty($selectData)){
			// 	AppointmentTimeReport::find($appointment_id)
			// 		->where('para_report_status_id',COLLECTION_COMPLETED)
			// 		->update(['starttime'=>$reachTime,'endtime'=>$finalTime]);
			// }
		}
	}

	/*
	Use     : getAppointmentReachAndFinalTime
	Author  : Sachin Shah
	Date    : 30 Jan,2018
	*/
	public static function getAppointmentReachAndFinalTime($appointment_id = 0)
	{
		$result = array();
		if (!empty($appointment_id)) {
			$query =    \DB::select("SELECT FA.appointment_id,F.*,
									CASE WHEN 1=1 THEN (
										SELECT created_date FROM foc_appointment_status WHERE appointment_id = FA.appointment_id ORDER BY created_date ASC LIMIT 1
									) END AS reached_time,
									CASE WHEN 1=1 THEN (
										SELECT created_date FROM foc_appointment_status WHERE appointment_id = FA.appointment_id ORDER BY created_date DESC LIMIT 1
									) END AS final_time 
									FROM foc_appointment_status FA 
									INNER JOIN foc_appointment F ON F.appointment_id = FA.appointment_id
									WHERE F.map_appointment_id = '" . $appointment_id . "' LIMIT 1");
			if ($query) {
				return $query[0];
			}
			return $query;
		}
	}


	/*
	Use      : pendingFocAppointment
	Author   : Sachin Patel
	Date     : 30 Jan,2019
	*/
	public static function pendingFocAppointment($request){
		$cityId         =   GetBaseLocationCity();
		$arrResult      =   array();
		$Today          =   date('Y-m-d');
		$sortBy         =   ($request->has('sortBy') && !empty($request->input('sortBy'))) ? $request->input('sortBy') : "appointment_id";
		$sortOrder      =   ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  =   !empty($request->input('size')) ? $request->input('size') : DEFAULT_SIZE;
		$pageNumber     =   !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$startDate      =   date('Y-m-d')." ".GLOBAL_START_TIME;
		$endDate        =   date('Y-m-d')." ".GLOBAL_END_TIME;
		
		$data           = ViewFocAppointment::select("view_foc_appointment.appointment_id",'view_foc_appointment.app_date_time','U4.customer_id','U4.code as customr_code','U4.address1',               'U4.address2','U4.zipcode',\DB::raw("CONCAT(U4.address1,' ',U4.address2,' ',U4.zipcode) AS landmark"),'U4.price_group','U4.lattitude','U4.longitude',
							'U4.vat', 'U4.vat_val', 'U4.additional_info', 'U4.collection_type','U4.QC_REQUIRED')
						->join('customer_master as U4','U4.customer_id','=','view_foc_appointment.customer_id')
						->whereIn('view_foc_appointment.city_id',$cityId)
						->where('view_foc_appointment.company_id', Auth()->user()->company_id);
						
		if ($request->has('params.appointment_id') && !empty($request->input('params.appointment_id'))) {
			$data->whereIn('appointment_id', explode(",", $request->input('params.appointment_id')));
		}
		if ($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id'))) {
			$data->where('vehicle_id', $request->input('params.vehicle_id'));
		}
		if ($request->has('params.route') && !empty($request->input('params.route'))) {
			$data->whereIn('route', array($request->input('params.route')));
		}
		/*FOR MOBILE SIDE CONDITION*/
		// if(isset($request->collection_by) && $request->collection_by !=""){
		// 	$data->where('view_foc_appointment.collection_by',$request->collection_by);
		// }
		if(isset($request->vehicle_id) && $request->vehicle_id !=""){
			$data->where('view_foc_appointment.vehicle_id',$request->vehicle_id);
		} else {
			if (isset(Auth()->user()->adminuserid)) {
				$data->where('view_foc_appointment.collection_by',Auth()->user()->adminuserid);
			} else {
				$data->where('view_foc_appointment.collection_by',0);
			}
		}


		/*END CONDITION*/
		$data->whereBetween('view_foc_appointment.app_date_time', array($startDate,$endDate));
		$data->where('complete',FOC_APPOINTMENT_PENDING);
		if(isset($request->limit) && $request->limit !=""){
			$data->limit($request->limit);
		}
		$result = $data->orderBy($sortBy, $sortOrder)->get();
		if(!empty($result)){
			$TotalRows  = 0;
			foreach ($result as $row){
				$row->app_date_time         = _FormatedDate($row->app_date_time);
				$row->app_date_time1        = _FormatedDate($row->app_date_time);
				$arrResult['APP_DATA'][]    = $row;
				$data                       = self::GetCustomerListByRoute($row);
				$TotalRows                  += (!empty($data)?sizeof($data):0);
				$arrResult[$row->appointment_id]['DATA']        = $data;

			}
			$arrResult['total_row'] = $TotalRows;
			return $arrResult;
		}
	}


	public static function GetCustomerListByRoute($request){
		$arrResult  = array();
		$data       = DB::table('customer_master')
					->select(
							'customer_master.customer_id',
							'customer_master.slab_id',
							'customer_master.code as customr_code','customer_master.address1',
							'customer_master.address2',
							'customer_master.zipcode',
							'customer_master.landmark',
							'customer_master.price_group',
							'customer_master.lattitude',
							'customer_master.longitude',
							'customer_master.vat',
							'customer_master.vat_val',
							'customer_master.additional_info',
							'customer_master.collection_type',
							'parameter.para_value as collection_type_name',
							'customer_master.QC_REQUIRED',DB::raw('1 as foc'),
							'customer_master.appointment_radius',
							DB::raw('IF (customer_master.last_name != "",CONCAT(customer_master.first_name," ",
							customer_master.last_name),customer_master.first_name) as customer_name'),'foc_appointment.appointment_id','foc_appointment.collection_by',
							'foc_appointment.app_date_time','CM.city_name as city','SM.state_name AS state',DB::raw('"'.CUSTOMER_DEFAULT_COUNTRY.'" as country'),"foc_appointment.privious_app_id")
					->leftJoin('city_master as CM','customer_master.city' ,'=','CM.city_id')
					->leftJoin('state_master as SM','customer_master.state','=','SM.state_id')
					->leftJoin('foc_appointment','customer_master.route','=','foc_appointment.route')
					->leftJoin('foc_appointment_status',function($join){
						$join->on('customer_master.customer_id','=','foc_appointment_status.customer_id');
						$join->on('foc_appointment.appointment_id', '=', 'foc_appointment_status.appointment_id');
					})
					->leftJoin('parameter','parameter.para_id','=','customer_master.collection_type')
					->whereNull('foc_appointment_status.customer_id')
					->where('customer_master.longitude','!=','0');
		if ($request->appointment_id != "" && preg_match("/[^0-9, ]/",$request->appointment_id) == false) {
			$data->whereIn('foc_appointment.appointment_id', array($request->appointment_id));
		}
		if ($request->route != "" && preg_match("/[^0-9, ]/",$request->route) == false) {
			$data->whereIn('foc_appointment.route',array($request->route));
		}
		if ($request->collection_by != "" && preg_match("/[^0-9, ]/",$request->collection_by) == false) {
			$data->whereIn('foc_appointment.collection_by',array($request->collection_by));
		}
		$data->whereIn('customer_master.collection_type',array(COLLECTION_TYPE_FOC,COLLECTION_TYPE_FOC_PAID));
		$data->where('foc_appointment.complete',FOC_APPOINTMENT_PENDING);


		$data->orderBy('customer_master.route_appointment_order','ASC');
		$data->groupBy('customer_master.customer_id');
		$result = $data->get();

		foreach ($result as $row){
			$slab_id 					= $row->slab_id;
			$row->app_type              = APPOINTMENT_TYPE_FOC;
			$row->app_date_time         = _FormatedDate($row->app_date_time);
			$row->inert_deduct_amount   = 0;
			$row->balance_amount        = 0;
			$row->earn_type             = EARN_TYPE_FREE;
			$row->is_bop_cust           = ((!empty($row->ctype) && $row->ctype == CUSTOMER_TYPE_BOP)?1:0);;
			$row->tags                  = CollectionTags::retrieveTagsByCustomer($row->customer_id,true);
			$profilePic                 = MediaMaster::find($row->customer_id);
			$row->cus_img               = (!empty($profilePic)) ? $profilePic->server_name : "";
			$mobile                     = CustomerContactDetails::where('customer_id',$row->customer_id)->first();
			$row->mobile_no             = (!empty($mobile)) ? $mobile->mobile : "";
			$row->appointment_radius    = (!empty($row->appointment_radius)?$row->appointment_radius:APPOINTMENT_RADIUS);
			$row->product_price_details = CompanyProductPriceDetail::getCustomerPriceGroupData($row->price_group);
			$row->product_price_details = array();
			if($slab_id > 0){
				$row->slab_product_details  = SlabRateCardMaster::GetSlabRateByID($slab_id);
			}else{
				$row->slab_product_details  = SlabRateCardMaster::GetFocProductDataByID();
			}
			if(!empty($row->privious_app_id)){
				$count = FocAppointmentStatus::where("appointment_id",$row->privious_app_id)->where("customer_id",$row->customer_id)->count();
				if($count == 0){
					$arrResult[]   = $row;
				}
			}else{
				$arrResult[]   = $row;
			}
		}
		return $arrResult;
	}

	public static function ChangeVehicleFocAppointment($request){
		$FocAppId 		= (isset($request->appointment_id) && !empty($request->appointment_id)) ? $request->appointment_id : 0;
		$map_appointment_id = (isset($request->map_appointment_id) && !empty($request->map_appointment_id)) ? $request->map_appointment_id : 0;
		$remarks 		= (isset($request->remarks) && !empty($request->remarks)) ? $request->remarks : "";
		$vehicle_id 	= (isset($request->vehicle_id) && !empty($request->vehicle_id)) ? $request->vehicle_id : 0;
		$route 			= (isset($request->route) && !empty($request->route)) ? $request->route : 0;
		$app_date_time 	= (isset($request->app_date_time) && !empty($request->app_date_time)) ? $request->app_date_time : date("Y-m-d H:i:s");
		$appointment_date = (isset($request->appointment_date) && !empty($request->appointment_date)) ? $request->appointment_date : date("Y-m-d H:i:s");
		$customer_id 	= (isset($request->customer_id) && !empty($request->customer_id)) ? $request->customer_id : 0;
		
		$FOC_DATA = self::find($FocAppId);
		
		if($FOC_DATA){
			$FOC_UPDATE 	= self::where("appointment_id",$FocAppId)->update(
				array(
					"change_vehicle"		=> 1,
					"change_vehicle_remark"	=> $remarks,
					"complete"				=> FOC_APPOINTMENT_COMPLETE
				)
			);
			LR_Modules_Log_CompanyUserActionLog($request,$FocAppId);
			Appoinment::where("appointment_id",$map_appointment_id)->update([
				"para_status_id"=> 2006,
			]);
			$collection_id 			= 0;
			$CheckCollectionIdExits = AppointmentCollection::where("appointment_id",$map_appointment_id)->first();
			if($CheckCollectionIdExits) {
				$collection_id = $CheckCollectionIdExits->collection_id;
				AppointmentCollection::where("collection_id",$CheckCollectionIdExits->collection_id)
				->update(['para_status_id'=>COLLECTION_NOT_APPROVED,'collection_dt'=>date("Y-m-d H:i:s")]);
			}else{
				$collection = new AppointmentCollection();
				$collection->appointment_id    =   $map_appointment_id;
				$collection->vehicle_id        =   $FOC_DATA->vehicle_id;
				$collection->collection_by     =   $FOC_DATA->collection_by;
				$collection->para_status_id    =   COLLECTION_NOT_APPROVED;
				$collection->collection_dt     =   date("Y-m-d H:i:s");
				$collection->amount            =   0;
				$collection->payable_amount    =   0;
				$collection->created_by        =   Auth()->user()->adminuserid;
				$collection->company_id        =   Auth()->user()->company_id;
				$collection->city_id           =   Auth()->user()->city;
				$collection->created_at        =   date("Y-m-d H:i:s");
				$collection->updated_by        =   Auth()->user()->adminuserid;
				$collection->updated_at        =   date("Y-m-d H:i:s");

				if($collection->save()){
					$collection_id = $collection->collection_id;
					log_action('Collection_Added',$collection->collection_id,(new static)->getTable());
				}
			}
			
				$focAppointment 					= FocAppointment::retrieveFOCAppointment($FocAppId);
				$clsproduct                         = CompanyProductMaster::find(FOC_PRODUCT);
				$clsproductparams                   = ViewProductMaster::find(FOC_PRODUCT_QUALITY);
				$detailsData                        = new \stdClass();
				$detailsData->para_quality_price     = 0.00;
				if ($clsproductparams->para_rate_in == $clsproductparams->PARA_RATE_IN_PERCENTAGE) {
					$detailsData->para_quality_price = number_format(($clsproduct->price-(($clsproduct->price*$clsproductparams->para_rate)/ 100)),2);
				} else {
					$detailsData->para_quality_price = number_format($clsproduct->price-$clsproductparams->para_rate,2);
				}
				
				$collection_qty = 0;
				$price 			= (isset($clsproduct->price) && !empty($clsproduct->price)) ? $clsproduct->price : 0;

				if(!empty($focAppointment)){
					$collection_qty = $focAppointment[0]->collection_qty;
				}
				$detailsData->quantity                           = $collection_qty;
				$detailsData->price                              = _FormatNumber(number_format(($collection_qty * $price),2));
				$detailsData->collection_id                      = $request->collection_id;
				$detailsData->category_id                        = FOC_CATEGORY;
				$detailsData->product_id                         = FOC_PRODUCT;
				$detailsData->company_product_quality_id         = FOC_PRODUCT_QUALITY;
				$priceDetail                                    = CustomerMaster::GetCustomerPrice($request->collection_id,FOC_PRODUCT);
				
				$detailsData->collection_id                      = $collection_id;
				$detailsData->product_customer_price             = (isset($priceDetail['price'])?$priceDetail['price']:0);
				$detailsData->product_inert                      = (isset($priceDetail['product_inert'])?$priceDetail['product_inert']:0);
				$detailsData->product_para_unit_id               = $clsproduct->para_unit_id;
				$detailsData->para_quality_price                 = $detailsData->para_quality_price;
				$detailsData->product_quality_para_rate          = $clsproductparams->para_rate;
				$detailsData->product_quality_para_rate_in       = $clsproductparams->para_rate_in;
				$detailsData->actual_coll_quantity               = $collection_qty;
				$detailsData->para_status_id                     = PARA_STATUS_APPROVED;
				$detailsData->created_by                         = Auth()->user()->admineruserid;
				$detailsData->appointment_id                     = $map_appointment_id;
				$detailsData->collection_detail_id               = 0;
				$CheckForUpdate = AppointmentCollectionDetail::where("category_id",FOC_CATEGORY)
								->where("product_id",FOC_PRODUCT)
								->where("product_quality_para_id",FOC_PRODUCT_QUALITY)
								->where("collection_id",$collection_id)
								->first();
				if($CheckForUpdate){
					$detailsData->collection_detail_id = $CheckForUpdate->collection_detail_id;
				}
				AppointmentCollectionDetail::saveCollectionDetails($detailsData);
				if (!empty($map_appointment_id)) {
					FocAppointment::UpdateFocAppointmentReachTime($map_appointment_id);
				}
			############# generate new appointment ###############
			$requestArray = array(
				"privious_app_id" 	=> $FocAppId,
				"appointment_date" 	=> $app_date_time,
				"vehicle_id" 		=> $vehicle_id,
				"customer_id" 		=> $customer_id,
				"route" 			=> $route,
				"app_date_time" 	=> $app_date_time
			);
			$myRequest = new \Illuminate\Http\Request();
			$myRequest->setMethod('POST');
			$myRequest->request->add($requestArray);
			return self::saveFocAppointment($myRequest);	
		}
	}
}