<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Appoinment;
use App\Models\CustomerMaster;
use App\Models\FocAppointment;
use App\Models\AppoinmentMediator;
use App\Facades\LiveServices;
class AppoinmentSchedular extends Model
{
	protected 	$table 		=	'appoinment_schedular';
	protected 	$primaryKey =	'schedule_id'; // or null
	protected 	$guarded 	=	['schedule_id'];
	public      $timestamps =   true;

	public function customer()
	{
		return $this->hasOne('App\Models\CustomerMaster','customer_id');
	}
	public static function add($request){
		$appointment_on = '';
		if(isset($request->appointment_on) && !empty($request->appointment_on)){
			if(is_array($request->appointment_on)){
				$appointment_on = implode(',',$request->appointment_on);
			}else{
				$appointment_on = $request->appointment_on;
			}
		}
		$customer                             = new self();
		$customer->customer_id                = (isset($request->customer_id) && !empty($request->customer_id))? $request->customer_id :0;
		$customer->vehicle_id                 = (isset($request->vehicle_id) && !empty($request->vehicle_id))? $request->vehicle_id :0;
		$customer->collection_by              = (isset($request->collection_by) && !empty($request->collection_by))? $request->collection_by :0;
		$customer->para_status_id             = (isset($request->para_status_id) && !empty($request->para_status_id))? $request->para_status_id :1;
		$customer->appointment_on             =  $appointment_on;
		$customer->appointment_date           = (isset($request->appointment_date) && !empty($request->appointment_date))? $request->appointment_date :0;
		$customer->appointment_type           = (isset($request->appointment_type) && !empty($request->appointment_type))? $request->appointment_type :0;
		$customer->appointment_time           = (isset($request->appointment_time) && !empty($request->appointment_time))? $request->appointment_time :0;
		$customer->appointment_no_time        = (isset($request->appointment_no_time) && !empty($request->appointment_no_time))? $request->appointment_no_time :0;
		$customer->appointment_repeat_after   = (isset($request->appointment_repeat_after) && !empty($request->appointment_repeat_after))? $request->appointment_repeat_after :0;
		$customer->appointment_month_type     = (isset($request->appointment_month_type) && !empty($request->appointment_month_type))? $request->appointment_month_type :0;
		$customer->last_app_dt                = (isset($request->last_app_dt) && !empty($request->last_app_dt))? $request->last_app_dt :0;
		$customer->created_by                 = Auth()->user()->adminuserid;
	   
		$customer->save();
		return $customer;
	   
	}

	public static function updateRecord($request){
		$customer = self::where('customer_id',$request->customer_id)->first();
		if($customer){
			$appointment_on = '';
			if(isset($request->appointment_on) && !empty($request->appointment_on)){
				if(is_array($request->appointment_on)){
					$appointment_on = implode(',',$request->appointment_on);
				}else{
					$appointment_on = $request->appointment_on;
				}
			}
			$customer->customer_id                = (isset($request->customer_id)               && !empty($request->customer_id))               ? $request->customer_id             :$customer->customer_id;
			$customer->vehicle_id                 = (isset($request->vehicle_id)                && !empty($request->vehicle_id))                ? $request->vehicle_id              :$customer->vehical_id;
			$customer->collection_by              = (isset($request->collection_by)             && !empty($request->collection_by))             ? $request->collection_by           :$customer->collection_by;
			$customer->para_status_id             = (isset($request->para_status_id)            && !empty($request->para_status_id))            ? $request->para_status_id          :$customer->para_status_id;
			$customer->appointment_on             = $appointment_on;
			$customer->appointment_date           = (isset($request->appointment_date)          && !empty($request->appointment_date))          ? $request->appointment_date        :$customer->appointment_date;
			$customer->appointment_type           = (isset($request->appointment_type)          && !empty($request->appointment_type))          ? $request->appointment_type        :0;
			$customer->appointment_time           = (isset($request->appointment_time)          && !empty($request->appointment_time))          ? $request->appointment_time        :$customer->appointment_time;
			$customer->appointment_no_time        = (isset($request->appointment_no_time)       && !empty($request->appointment_no_time))       ? $request->appointment_no_time     :$customer->appointment_no_time;
			$customer->appointment_repeat_after   = (isset($request->appointment_repeat_after)  && !empty($request->appointment_repeat_after))  ? $request->appointment_repeat_after:$customer->appointment_repeat_after;
			$customer->appointment_month_type     = (isset($request->appointment_month_type)    && !empty($request->appointment_month_type))    ? $request->appointment_month_type  :$customer->appointment_month_type;
			$customer->last_app_dt                = (isset($request->last_app_dt)               && !empty($request->last_app_dt))               ? $request->last_app_dt             :$customer->last_app_dt;
			$customer->created_by                 = Auth()->user()->adminuserid;
		   
			$customer->save();
		}
		return $customer;
	}


	public static function updateScheduleRecord($request){
		$customer = self::find($request->schedule_id);
		if($customer){
			$appointment_on = '';
			if(isset($request->appointment_on) && !empty($request->appointment_on)){
				if(is_array($request->appointment_on)){
					$appointment_on = implode(',',$request->appointment_on);
				}else{
					$appointment_on = implode(',',json_decode($request->appointment_on));
				}
			}
			$customer->customer_id                = (isset($request->customer_id)               && !empty($request->customer_id))               ? $request->customer_id             :$customer->customer_id;
			$customer->vehicle_id                 = (isset($request->vehicle_id)                && !empty($request->vehicle_id))                ? $request->vehicle_id              :$customer->vehical_id;
			$customer->collection_by              = (isset($request->collection_by)             && !empty($request->collection_by))             ? $request->collection_by           :$customer->collection_by;
			$customer->para_status_id             = (isset($request->para_status_id)            && !empty($request->para_status_id))            ? $request->para_status_id          :0;
			$customer->appointment_on             = $appointment_on ;
			$customer->appointment_date           = (isset($request->appointment_date)          && !empty($request->appointment_date))          ? $request->appointment_date        :$customer->appointment_date;
			$customer->appointment_type           = (isset($request->appointment_type)          && !empty($request->appointment_type))          ? $request->appointment_type        :0;
			$customer->appointment_time           = (isset($request->appointment_time)          && !empty($request->appointment_time))          ? $request->appointment_time        :$customer->appointment_time;
			$customer->appointment_no_time        = (isset($request->appointment_no_time)       && !empty($request->appointment_no_time))       ? $request->appointment_no_time     :$customer->appointment_no_time;
			$customer->appointment_repeat_after   = (isset($request->appointment_repeat_after)  && !empty($request->appointment_repeat_after))  ? $request->appointment_repeat_after:$customer->appointment_repeat_after;
			$customer->appointment_month_type     = (isset($request->appointment_month_type)    && !empty($request->appointment_month_type))    ? $request->appointment_month_type  :$customer->appointment_month_type;
			$customer->last_app_dt                = (isset($request->last_app_dt)               && !empty($request->last_app_dt))               ? $request->last_app_dt             :$customer->last_app_dt;
			$customer->created_by                 = Auth()->user()->adminuserid;
			$customer->save();
			LR_Modules_Log_CompanyUserActionLog($request,$request->schedule_id);
		}
		return $customer;
	}
	/*
	Use     : Search schedular 
	Author  : Axay Shah
	Date    : 17 Dec,2018
	*/
	public static function searchSchedular($request){
		$Today          = date('Y-m-d');
		$sortBy         = (isset($request->sortBy) && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "appoinment_schedular.schedule_id";
		$sortOrder      = (isset($request->sortOrder) && !empty($request->input('sortOrder'))) ? $request->input('sortOrder')       : "ASC";
		$recordPerPage  = (isset($request->size) && !empty($request->input('size')))         ?   $request->input('size')            : 10;
		$pageNumber     = (isset($request->pageNumber) && !empty($request->input('pageNumber'))) ?   $request->input('pageNumber')  : '';
		$cityId         = GetBaseLocationCity();
		$date = date("Y-m-d H:i:s");
		$data = self::select("appoinment_schedular.*",
		\DB::raw("getNextScheduleDateTime('".date('Y-m-d', strtotime($date))."','".$date."',appoinment_schedular.customer_id,
			appoinment_schedular.schedule_id,
			'".Auth()->user()->city."',
			'".Auth()->user()->company_id."') AS scheduledt"),
			\DB::raw("CONCAT(C.first_name,
			' ',
			C.last_name) AS cust_name"),
			\DB::raw("CONCAT(CB.firstname,
			' ',
			CB.lastname) AS collection_by_user"),
			"C.city",
			"C.company_id",
			\DB::raw("L.city as city_name"),
			"U1.username AS `created`",
			"U2.username AS `updated`",
		\DB::raw("DATE_FORMAT(appoinment_schedular.created_at,'%Y-%m-%d') AS `date_create`"),
		\DB::raw("DATE_FORMAT(appoinment_schedular.updated_at,'%Y-%m-%d') AS `date_update`"))
		->leftjoin("adminuser as CB","appoinment_schedular.collection_by","=", "CB.adminuserid")
		->leftjoin("adminuser as U1","appoinment_schedular.created_by","=", "U1.adminuserid")
		->leftjoin("adminuser as U2","appoinment_schedular.updated_by","=", "U2.adminuserid")
		->join("customer_master as C","appoinment_schedular.customer_id","=", "C.customer_id")
		->join("location_master as L","C.city","=", "L.location_id");
		$data->where('C.company_id',Auth()->user()->company_id);
		if($request->has('params.city_id') && !empty($request->input('params.city_id')))
		{
			$data->where('C.city',$request->input('params.city_id'));
		}else{
			$data->whereIn('C.city',$cityId);
		}
		if($request->has('params.schedule_id') && !empty($request->input('params.schedule_id')))
		{
			$data->whereIn('appoinment_schedular.schedule_id', explode(",",$request->input('params.schedule_id')));
		}
		if($request->has('params.customer_name') && !empty($request->input('params.customer_name')))
		{
			$data->where(function($q) use($request) {
				$q->where('C.first_name','like', '%'.$request->input('params.customer_name').'%')
				->orWhere('C.last_name','like', '%'.$request->input('params.customer_name').'%');
			});
		}
		if($request->has('params.collection_by') && !empty($request->input('params.collection_by')))
		{
			$data->where('appoinment_schedular.collection_by', $request->input('params.collection_by'));
		}
		if($request->has('params.para_status_id') && !empty($request->input('params.para_status_id')))
		{
			$data->where('appoinment_schedular.para_status_id', $request->input('params.para_status_id'));
		}elseif ($request->input('params.para_status_id') == "0") {
			$data->where('appoinment_schedular.para_status_id', $request->input('params.para_status_id'));
		}
		if($request->has('params.appointment_type') && !empty($request->input('params.appointment_type')))
		{
			$data->where('appoinment_schedular.appointment_type', $request->input('params.appointment_type'));
		}
		if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_from')))
		{
			$data->whereBetween('appoinment_schedular.created_at',array(date("Y-m-d", strtotime($request->input('params.created_from'))),date("Y-m-d", strtotime($request->input('params.created_to')))));
		}else if(!empty($request->input('params.created_from'))){
		   $data->whereBetween('appoinment_schedular.created_at',array(date("Y-m-d", strtotime($request->input('params.created_from'))),$Today));
		}else if(!empty($request->input('params.created_to'))){
			$data->whereBetween('appoinment_schedular.created_at',array(date("Y-m-d", strtotime($request->input('params.created_to'))),$Today));
		}

		return $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	}

	/*
	Use     : Get By Id
	Author  : Axay Shah
	Date    : 09 Jan,2019 
	*/

	public static function getById($scheduleId){

		$data =  self::where('schedule_id',$scheduleId)->with(['customer'=>function($e){
			$e->select('customer_id',\DB::raw('CONCAT(first_name," ",last_name) as customer_name'));
		}])->first();
	   
		return $data;
	}

	/*
	Use     : get appointment by date list
	Author  : Axay Shah
	Date    : 24 Jan,2019 
	*/

	public static function getAppointmentsByDateList($appDate){
		$city  		= GetBaseLocationCity();
		$CityId 	= (!empty($city)) ? implode(",",$city) : 0;
		$datetime   = date("Y-m-d H:i:s",strtotime($appDate));
		$startDate  =   $appDate." ".GLOBAL_START_TIME;
		$endDate    =   $appDate." ".GLOBAL_END_TIME;
		$data =  \DB::select("SELECT appoinment_schedular.customer_id,appoinment_schedular.collection_by,appoinment_schedular.appointment_time,
						appoinment_schedular.last_app_dt,appoinment_schedular.appointment_no_time,
						getNextScheduleDateTime('$appDate','$datetime',appoinment_schedular.customer_id,appoinment_schedular.schedule_id,".Auth()->user()->city.",".Auth()->user()->company_id.") AS scheduledt,
						CONCAT(C.first_name,' ',C.last_name) AS cust_name,C.collection_type,C.lattitude,C.longitude,
						CONCAT(CB.firstname,' ',CB.lastname) AS collection_by_user,VM.vehicle_number,
						CASE WHEN 1=1 THEN (
							SELECT count(0) FROM company_parameter 
							WHERE map_customer_id = appoinment_schedular.customer_id 
							AND company_parameter.city_id = C.city
						) END AS map_customer_id,
						GROUP_CONCAT(CT.tag_id) as tag_ids,CA.app_time as coll_avg_time
						FROM appoinment_schedular
						LEFT JOIN customer_master C ON appoinment_schedular.customer_id = C.customer_id 
						LEFT JOIN customer_avg_collection CA ON CA.customer_id = C.customer_id
						LEFT JOIN adminuser CB ON appoinment_schedular.collection_by = CB.adminuserid 
						LEFT JOIN vehicle_master VM ON appoinment_schedular.vehicle_id = VM.vehicle_id
						LEFT JOIN customer_collection_tags CT ON C.customer_id = CT.customer_id
						WHERE C.longitude != '0' AND C.lattitude != '0' AND C.route = 0 AND appoinment_schedular.appointment_type != '".TYPE_ON_CALL."' AND C.city IN(".$CityId.") AND C.company_id = '".Auth()->user()->company_id."'
						GROUP BY C.customer_id
						HAVING (scheduledt BETWEEN '$startDate' AND '$endDate')
						AND map_customer_id = 0
						ORDER BY appoinment_schedular.appointment_time ASC, VM.vehicle_number ASC"); 
						$res = Appoinment::unassignCancleYesterdayAppoResponse($data,$appDate);
						return $res;
	}

	/*
	Use     : get Customer group list   
	Author  : Axay Shah
	Date    : 24 Jan,2019 
	*/

	public static function getCustomerGroupList($request){
		$cityId = GetBaseLocationCity();
		$data 	= ViewCompanyParameterParentChild::dropDownByRefId(PARA_CUSTOMER_GROUP)
		->where('show_in_scheduler','Y')
		->whereIn('city_id',$cityId)
		->where('company_id',Auth()->user()->company_id)
		->select('para_id as customer_group_id','para_value as customer_group_name','scheduler_time as coll_avg_time','latitude','longitude')
		->get();
		return $data;
	}

	/*
	Use     : Get getMRFDepartmentListV
	Author  : Axay Shah
	Date    : 24 Jan,2019 
	*/

	public static function getMRFDepartmentList($request){
		$cityId = GetBaseLocationCity();
		return WmDepartment::where('show_in_scheduler','Y')
		->whereIn('location_id',$cityId)
		->where('company_id',Auth()->user()->company_id)
		->select('id as mrf_dept_id','department_name','scheduler_time as coll_avg_time','latitude','longitude')
		->get();
	}
	/*
	Use     : Get getFOCRouteList
	Author  : Axay Shah
	Date    : 24 Jan,2019 
	*/

	public static function getFOCRouteList($request){
		$data 	= 	"";
		$cityId = 	GetBaseLocationCity();
		$getId 	= 	CompanyParameter::where("ref_para_id",PARA_COLLECTION_ROUTE)
					->whereIn('company_parameter.city_id',$cityId)
					->where('company_parameter.company_id',Auth()->user()->company_id)
					->first();
		if($getId){
			$data = CompanyParameter::select("para_id as route_id","para_value as route_name","CM.customer_id",\DB::raw("CONCAT(CM.first_name,' ',CM.last_name) AS cust_name"),"scheduler_time as coll_avg_time")
			->join("customer_master as CM","CM.customer_id","=","company_parameter.map_customer_id")
			->where("para_parent_id",$getId->para_id)
			->where('company_parameter.map_customer_id',"!=",0)->where('company_parameter.status','A')
			->whereIn('company_parameter.city_id',$cityId)
			->where('company_parameter.company_id',Auth()->user()->company_id)
			->get();
			if($data){
				foreach($data as $result){
					$location          = self::getFOCRouteCustomerLatLon($result->route_id);
					$result->longitude = (isset($location->longitude)) ? $location->longitude : 0;
					$result->latitude  = (isset($location->latitude)) ? $location->latitude : 0;
					$coll_avg_time     = (isset($result->coll_avg_time) && $result->coll_avg_time > MIN_FOC_COLL_AVG_TIME)?$result->coll_avg_time:MIN_FOC_COLL_AVG_TIME;
					$result->coll_avg_time = $coll_avg_time;
				}
			}
		}
	   
		return $data;
	}
	/*
	Use     : getFOCRouteCustomerLatLon
	Author  : Axay Shah
	Date    : 24 Jan,2019 
	*/

	public static function getFOCRouteCustomerLatLon($routeId = ''){
		$result = array();
		if(!empty($routeId)) {
			$result = CustomerMaster::where('route',$routeId)->select('longitude','lattitude AS latitude')->orderBy('route_appointment_order','ASC')->first();
		}
		return $result;
	}


	/*
	Use     : Appointment schedular update (From appointment_schedule)
	Author  : Axay Shah
	Date    : 28 Jan,2019 
	*/

	public static function masterSchedularDataUpdate($request){
		$data =  array();
		/*Create OR Update Appointment */
		if(isset($request->appointmentData) && !empty($request->appointmentData)){
			foreach($request->appointmentData as $app){
				if(empty($app['appointment_id'])){
					$app['remark']            = "Appointment Added from scheduler by admin ".Auth()->user()->username;
					$app['para_status_id']    = APPOINTMENT_SCHEDULED;
					$data                     = Appoinment::saveAppointment((object)$app);   
				}else{
					$app['remark']            = "Appointment updated from scheduler by admin ".Auth()->user()->username;
					$data                     = Appoinment::updateAppointment((object)$app);
				}
			}
		}
		/*Customer Group & department(MRF) api call*/ 
		if(isset($request->departmentData) && !empty($request->departmentData)){
			foreach($request->departmentData as $depgrp){
				$data  = AppoinmentMediator::addAppointmentMediator((object)$depgrp);
			}
		}
		
		if(isset($request->focAppointmentData) && !empty($request->focAppointmentData)){
			foreach($request->focAppointmentData as $foc){
				$mappedRoute = CustomerMaster::getCustomerMappedRoute($foc['customer_id']);
				if(!empty($mappedRoute)){
					if(empty($foc['appointment_id'])){
						$foc['route']   = (isset($mappedRoute->para_id)) ? $mappedRoute->para_id : 0;
						$foc['remark']  = 'Appointment Added from scheduler by admin '.Auth()->user()->username;
						$data = FocAppointment::saveFocAppointment((object)$foc,false);
					}else{
						$foc['route']   = (isset($mappedRoute->para_id)) ? $mappedRoute->para_id : 0;
						$foc['remark'] = 'Appointment Updated from scheduler by admin '.Auth()->user()->username;
						$data = FocAppointment::updateFocAppointment((object)$foc,false);
					}
				}
			}
		}
		return $data ;
	}

	/*
	Use : cancle appointment in appointment schedular
	
	*/
	public static function cancelAppointment($request){
		$update = false;
		$id     = (isset($request->appointment_id)    && !empty($request->appointment_id))   ? $request->appointment_id  : "";
		$type   = (isset($request->app_type)          && !empty($request->app_type))         ? $request->app_type        : "";
		$reason = (isset($request->cancel_reason)    && !empty($request->cancel_reason))   ? $request->cancel_reason  : "";
		if(!empty($id) && !empty($type)){
			if($type == "cust"){
				$update = Appoinment::where('appointment_id',$id)->update(['para_status_id'=>APPOINTMENT_CANCELLED,"cancel_reason"=>$reason,"updated_by"=>Auth()->user()->adminuserid]);
			}elseif($type == "group" || $type == "dept"){
				$update = AppoinmentMediator::where('id',$id)->update(['para_status_id'=>APPOINTMENT_CANCELLED]);
			}elseif($type == "route"){
				$update = Appoinment::where('appointment_id',$id)->update(['para_status_id'=>APPOINTMENT_CANCELLED,"cancel_reason"=>$reason,"updated_by"=>Auth()->user()->adminuserid]);
				$update = FocAppointment::where('map_appointment_id',$id)->update(['complete'=>FOC_APPOINTMENT_CANCEL,
					"cancel_reason"=>$reason,"updated_by"=>Auth()->user()->adminuserid]);
			}
			return $update;
		}
	}
	
}
