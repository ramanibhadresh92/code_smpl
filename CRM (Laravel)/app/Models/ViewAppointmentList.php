<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Input;
use App\Models\Appoinment;
use App\Models\AppoinmentMediator;
use App\Models\AppointmentImages;
use App\Models\AppointmentCollection;
use DB;
use App\Models\AppointmentRunningLate;
use App\Facades\LiveServices;
class ViewAppointmentList extends Model
{
	protected $table = 'view_appointment_list';

	public static function searchAppointment($request,$isPainate = false)
	{
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "appointment_id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId         = GetBaseLocationCity(Auth()->user()->base_location);
		$data 			= Appoinment::SELECT('appoinment.appointment_id',
											'appoinment.company_id',
											'appoinment.city_id',
											'appoinment.customer_id',
											'appoinment.product_id',
											'appoinment.vehicle_id',
											'appoinment.collection_by',
											'appoinment.supervisor_id',
											'appoinment.para_status_id',
											'appoinment.is_donation',
											'appoinment.charity_id',
											'appoinment.app_date_time',
											'appoinment.remark',
											'appoinment.cancel_reason',
											'appoinment.dustbin_id',
											'appoinment.app_type',
											\DB::raw('CAST(appoinment.foc as UNSIGNED) as foc'),
											'appoinment.earn_type',
											'appoinment.partner_appointment',
											'appoinment.extra_pickup',
											'appoinment.longitude',
											'appoinment.lattitude',
											'appoinment.payment_status',
											'appoinment.amount',
											'appoinment.payment_id',
											'appoinment.waybridge_approved_by',
											'appoinment.waybridge_approved_date',
											'appoinment.comment',
											'appoinment.approval_status',
											'appoinment.client_id',
											'appoinment.direct_dispatch',
											'appoinment.dispatch_status',
											'appoinment.created_by',
											'appoinment.updated_by',
											'appoinment.created_at',
											'appoinment.updated_at',
											'appoinment.import_id',
											'appoinment.invoice_no',
											'appoinment.bill_from_mrf_id',
											'DEPT.department_name',
											'appoinment.dummy_appointment','u8.collection_id AS collection_id',
											DB::raw('if(appoinment.app_date_time < (now() - INTERVAL 1 DAY),0,1) as reopen_app_enable'),
											DB::raw('CONCAT(u3.firstname, " ", u3.lastname) AS collection_by_user'),
											DB::raw('CONCAT(u4.address1," ", u4.address2) AS address'),
											DB::raw('CONCAT(u4.first_name, " ", u4.last_name) AS customer_name'),
											DB::raw('CONCAT(u9.firstname, " ", u9.lastname) AS waybridge_approved_name'),
											'lo.city AS city_name',
											'sm.state_name AS state_name',
											'co.country_name AS country_name',
											'u4.zipcode AS zipcode',
											'u4.code AS customer_code',
											'u5.dustbin_code AS allocated_dustbin_code',
											'u6.vehicle_number AS vehicle_number',
											'u7.para_value AS appointment_status',
											DB::raw('CONCAT(u1.firstname, " ", u1.lastname) AS created'),
											DB::raw('CONCAT(u2.firstname, " ", u2.lastname) AS updated'),
											DB::raw('DATE_FORMAT(appoinment.created_at, "%Y-%m-%d") AS date_create'),
											DB::raw('DATE_FORMAT(appoinment.updated_at, "%Y-%m-%d") AS date_update'))
							->LEFTJOIN('adminuser as u1', 'appoinment.created_by' ,'=', 'u1.adminuserid')
							->LEFTJOIN('adminuser as u2', 'appoinment.updated_by' ,'=', 'u2.adminuserid')
							->LEFTJOIN('adminuser as u3', 'appoinment.collection_by' ,'=', 'u3.adminuserid')
							->JOIN('customer_master as u4', 'appoinment.customer_id' ,'=', 'u4.customer_id')
							->LEFTJOIN('wm_department as DEPT','appoinment.bill_from_mrf_id' ,'=', 'DEPT.id')
							->LEFTJOIN('location_master as lo', 'u4.city' ,'=', 'lo.location_id')
							->LEFTJOIN('state_master as sm', 'u4.state' ,'=', 'sm.state_id')
							->LEFTJOIN('country_master as co', 'u4.country' ,'=', 'co.country_id')
							->LEFTJOIN('dustbin_master as u5', 'appoinment.dustbin_id' ,'=', 'u5.dustbin_id')
							->LEFTJOIN('vehicle_master as u6', 'appoinment.vehicle_id' ,'=', 'u6.vehicle_id')
							->LEFTJOIN('adminuser as u9','appoinment.waybridge_approved_by' ,'=', 'u9.adminuserid')
							->JOIN('parameter as u7', 'appoinment.para_status_id' ,'=','u7.para_id')
							->leftjoin('appointment_collection as u8', 'appoinment.appointment_id' ,'=', 'u8.appointment_id')
							->where('appoinment.company_id',Auth()->user()->company_id);
		
		if($request->has('params.dummy_appointment') && !empty($request->input('params.dummy_appointment')))
		{
			$data->where('appoinment.dummy_appointment',$request->input('params.dummy_appointment'));
		}
		if($request->has('params.invoice_no') && !empty($request->input('params.invoice_no')))
		{
			$data->where('appoinment.invoice_no',$request->input('params.invoice_no'));
		}
		if($request->has('params.city_id') && !empty($request->input('params.city_id')))
		{
			$data->whereIn('appoinment.city_id', explode(",",$request->input('params.city_id')));
		}else{
			$data->whereIn("appoinment.city_id",$cityId);
		}
		if($request->has('params.appointment_id') && !empty($request->input('params.appointment_id')))
		{
			$data->whereIn('appoinment.appointment_id', explode(",",$request->input('params.appointment_id')));
		}
		if($request->has('params.customer_code') && !empty($request->input('params.customer_code')))
		{
			$data->where('u4.code','like', '%'.$request->input('params.customer_code').'%');
		}
		if($request->has('params.customer_name') && !empty($request->input('params.customer_name')))
		{
			$data->where(function($q) use($request) {
					$q->where('u4.first_name', 'like', '%'.$request->input('params.customer_name').'%')
				  ->orWhere('u4.last_name', 'like', '%'.$request->input('params.customer_name').'%');
			});
		}
		if($request->has('params.para_status_id') && !empty($request->input('params.para_status_id')))
		{
			if(!is_array($request->input('params.para_status_id'))){
				$request->para_status_id = explode(",",$request->input('params.para_status_id'));
			}else{
				$request->para_status_id = $request->input('params.para_status_id');
			}
			$data->whereIn('appoinment.para_status_id',$request->para_status_id);
		}
		if($request->has('params.allocated_dustbin_code') && !empty($request->input('params.allocated_dustbin_code')))
		{
			$data->where('u5.dustbin_code','like', '%'.$request->input('params.allocated_dustbin_code','%'));
		}
		if($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id')))
		{
			$data->where('appoinment.vehicle_id',$request->input('params.vehicle_id'));
		}
		
		if(!empty($request->input('params.appointment_from')) && !empty($request->input('params.appointment_to')))
		{
			$data->whereBetween('appoinment.app_date_time',array(date("Y-m-d H:i:s", strtotime($request->input('params.appointment_from')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.appointment_to')." ".GLOBAL_END_TIME))));
		}else if(!empty($request->input('params.appointment_from'))){
		   $datefrom = date("Y-m-d", strtotime($request->input('params.appointment_from')));
		   $data->whereBetween('appoinment.app_date_time',array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('params.appointment_to'))){
		   $data->whereBetween('appoinment.app_date_time',array(date("Y-m-d", strtotime($request->input('params.appointment_to'))),$Today));
		}
		if($request->has('params.donation_to') && !empty($request->input('params.donation_to')))
		{
			$data->where('appoinment.charity_id',$request->input('params.is_donation'));
		}
		if($request->has('params.earn_type') && !empty($request->input('params.earn_type')))
		{
			$data->where('appoinment.earn_type',$request->input('params.earn_type'));
		}
		if($request->has('params.partner_appointment') && !empty($request->input('params.partner_appointment')))
		{
			($request->input('params.partner_appointment') == 'Y' ? $partner = 1 : $partner = 0);
			$data->where('appoinment.partner_appointment',$partner);
		}
		if($request->has('params.extra_pickup') && !empty($request->input('params.extra_pickup')))
		{
			($request->input('params.extra_pickup') == 'Y' ? $pickup = 1 : $pickup = 0);
			$data->where('appoinment.extra_pickup',$pickup);
		}
		
		if($request->has('params.is_approval') && !empty($request->input('params.is_approval')))
		{
		   $data->whereNull('appoinment.approval_status');
		}
		$query = LiveServices::toSqlWithBinding($data,true);
		if($isPainate == true) {
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			if($result->total()> 0) {
				$data = $result->toArray();
				foreach($data['result'] as $key => $collection) {
					$data['result'][$key]['foc'] 						= (int)$collection['foc'];
					$data['result'][$key]['no_of_appointment_images'] 	= AppointmentImages::where("appointment_id",$collection['appointment_id'])->count();
					if($collection['collection_id'] <= 0) {
						$saveCollection = AppointmentCollection::addCollection($collection);
						if($saveCollection) {
							$data['result'][$key]['collection_id'] = $saveCollection->collection_id;
						}
					}
				}
			}
		} else {
			$result = $data->get();
			if(count($result) > 0) {
				$data = $result->toArray();
				foreach($data as $key => $collection) {
					$data['result'][$key]['foc'] 						= (int)$collection['foc'];
					$data['result'][$key]['no_of_appointment_images'] 	= AppointmentImages::where("appointment_id",$collection['appointment_id'])->count();
					if($collection['collection_id'] <= 0) {
						$saveCollection = AppointmentCollection::addCollection($collection);
						if($saveCollection) {
							$data[$key]['collection_id'] = $saveCollection->collection_id;
						}
					}
				}
			}
		}
		return $result;
	} 
	
	public static function getAppointmentList(){
		$cityId  = GetBaseLocationCity();
		return self::SELECT('view_appointment_list.*','u8.collection_id AS collection_id','lo.city AS city_name',
		'sm.state_name AS state_name',
		'co.country_name AS country_name',
		'u5.dustbin_code AS allocated_dustbin_code',
		'u6.vehicle_number AS vehicle_number')
		->LEFTJOIN('location_master as lo', 'view_appointment_list.city' ,'=', 'lo.location_id')
		->LEFTJOIN('state_master as sm', 'view_appointment_list.state' ,'=', 'sm.state_id')
		->LEFTJOIN('country_master as co', 'view_appointment_list.country' ,'=', 'co.country_id')
		->LEFTJOIN('dustbin_master as u5', 'appoinment.dustbin_id' ,'=', 'u5.dustbin_id')
		->LEFTJOIN('vehicle_master as u6', 'appoinment.vehicle_id' ,'=', 'u6.vehicle_id')
		->whereIn('view_appointment_list.city_id',$cityId)
		->where('view_appointment_list.company_id',Auth()->user()->company_id)
		->paginate();
		
	}
	/* 
	Use : 
	  
	*/
	   
	public static function showCurrentAppointmentClientData($request){
		$appDate        =   (!empty($request->appointment_cur_date)) ? date('Y-m-d',strtotime($request->appointment_cur_date)) : date('Y-m-d');
		$startDate      =   $appDate." ".GLOBAL_START_TIME;
		$endDate        =   $appDate." ".GLOBAL_END_TIME;
		$vehicleList    =   self::vehicleList($request);
		$currApp        =   self::getCurrentAppointmentData($appDate);
		$kms            =   0;
		/* if count greter then 0 then call ShowCurrentScheduledClientData() in OLD LR in new LR we will display only*/
		foreach ($vehicleList as $ID => $NAME)
		{
			$Vehicle_Fill_Level     = "0";
			$Today_Collection       = 0.00;
			$Expected_Collection    = 0.00;
			$collectionBy   = 0;
			$SelectRows     = AppointmentCollectionDetail::getTodayAppointmentStats($collectionBy,$startDate,$NAME->vehicle_id);
			$appointmentTbl = (new Appoinment())->getTable();
			$cusAvgCollTbl  = (new CustomerAvgCollection())->getTable();
			$customerTbl    = (new CustomerMaster())->getTable();
			$avgData        = CustomerAvgCollection::select(\DB::raw("sum($cusAvgCollTbl.collection) as collection"),
							\DB::raw("sum($cusAvgCollTbl.amount) as price"))
							->join($appointmentTbl,"$cusAvgCollTbl.customer_id","=","$appointmentTbl.customer_id")
							->where("$appointmentTbl.vehicle_id",$NAME['vehicle_id'])
							->whereBetween("$appointmentTbl.app_date_time",[$startDate,$endDate])
							->where("$appointmentTbl.para_status_id",'!=',APPOINTMENT_CANCELLED)
							->get();
			$avgKms         = Appoinment::select("$customerTbl.longitude","$customerTbl.lattitude")
							->whereBetween("app_date_time",[$startDate,$endDate])
							->join($customerTbl,"$appointmentTbl.customer_id",'=',"$customerTbl.customer_id")
							->where("$appointmentTbl.para_status_id",'!=',APPOINTMENT_CANCELLED)
							->where("$appointmentTbl.vehicle_id",$NAME['vehicle_id'])
							->get();
			if(!empty($SelectRows)){
				$Today_Collection		= _FormatNumberV2(!empty($SelectRows['Today_Collection'])?$SelectRows['Today_Collection']:0.00);
				$Expected_Collection	= _FormatNumberV2(!empty($SelectRows['Expected_Collection'])?$SelectRows['Expected_Collection']:0.00); 
				if ($SelectRows['vehicle_volume'] > 0 && $SelectRows['collection_volume'] > 0) {
					$Vehicle_Fill_Level = intval(($SelectRows['collection_volume'] * 100 )/($SelectRows['vehicle_volume']* 68.15));
				}  
			}
									 
							
			if($avgData){
				if (isset($currApp[$ID]) && !empty($currApp[$ID]))
				{
					$app = (array)$currApp[$ID];
					$kms = self::calculateAppRouteEastimatedKilometer($avgKms);
				}
				$NAME['avg_qty'] = _FormatNumberV2($avgData[0]['collection']);
				$NAME['avg_amt'] = _FormatNumberV2($avgData[0]['price']);
				$NAME['avg_km']  = _FormatNumberV2($kms);
			}else{
				$NAME['avg_qty'] = _FormatNumberV2(0);
				$NAME['avg_amt'] = _FormatNumberV2(0);
				$NAME['avg_km']  = _FormatNumberV2(0);
			}
				$NAME['today_collection']       = $Today_Collection;
				$NAME['expected_collection']    = $Expected_Collection;
				$NAME['fill_level']             = $Vehicle_Fill_Level;

		}    
		foreach($currApp as $a){
			if(empty($a->coll_avg_time) || $a->coll_avg_time < MIN_COLL_AVG_TIME){
				$a->coll_avg_time   = MIN_COLL_AVG_TIME;
			}
		}
		$data['vehicleList'] = $vehicleList;
		$data['appointment'] = $currApp;
		return $data;
	}
	
	/*
	use     : list current appointment list according to vehicle
	Author  : Axay Shah
	Date    : 23 Jan,2019
	*/
	public static function getCurrentAppointmentData($appDate){
		$city 			= GetBaseLocationCity();
		$cityId 		= (!empty($city)) ? implode(",",$city) : 0;
		$appDate        = (!empty($appDate)) ? date('Y-m-d',strtotime($appDate)) : date('Y-m-d');
		$startDate      =   $appDate." ".GLOBAL_START_TIME;
		$endDate        =   $appDate." ".GLOBAL_END_TIME;
		// $data           = \DB::select('CALL SP_GET_CURRENT_APPOINTMENT_DATA('.Auth()->user()->company_id.',"'.$cityId.'","'.$startDate.'","'.$endDate.'",'.APPOINTMENT_CANCELLED.')');
		$data = self::SPGetCurrentAppointmentData($startDate,$endDate,Auth()->user()->company_id,$cityId,APPOINTMENT_CANCELLED);
		return $data;
	}
	
	/*
	use     : display current scheduled client data
	Author  : Axay Shah
	Date    : 23 Jan,2019
	*/
	public static function showCurrentScheduledClientData($request){
		$appDate        = (!empty($request->appointment_cur_date)) ? date('Y-m-d',strtotime($request->appointment_cur_date)) : date('Y-m-d');
		$startDate      =   $appDate." ".GLOBAL_START_TIME;
		$endDate        =   $appDate." ".GLOBAL_END_TIME;
		$vehicleList    =  self::vehicleList($request);
		$data['vehicleList'] = $vehicleList;
		return $data;
	}

	/*
	use     : Check current apointment count
	Author  : Axay Shah
	Date    : 23 Jan,2019
	*/
	public static function checkCurrentAppointment($appDate)
	{
		$cityId 		=   GetBaseLocationCity();
		$appDate        =   (!empty($appDate)) ? date('Y-m-d',strtotime($appDate)) : date('Y-m-d');
		$startDate      =   $appDate." ".GLOBAL_START_TIME;
		$endDate        =   $appDate." ".GLOBAL_END_TIME;
		$noa            =   Appoinment::whereBetween('app_date_time',array($startDate,$endDate))->where("company_id",Auth()->user()->company_id)->whereIn("city_id",$cityId)->count();
		$nom            =   AppoinmentMediator::whereBetween('app_date_time',array($startDate,$endDate))->where("company_id",Auth()->user()->company_id)->whereIn("city_id",$cityId)->count();
		$noa            =   $noa + $nom;
		return $noa;
	}

	/*
	use     : Get By Id
	Author  : Axay Shah
	Date    : 28 Jan,2019
	*/
	public static function getById($id,$FromReport=false) {
		$cityId 			=   GetBaseLocationCity();
		$AppointmentDetails = 	Appoinment::SELECT('appoinment.*','u8.collection_id AS collection_id',
		DB::raw('CONCAT(u3.firstname, " ", u3.lastname) AS collection_by_user'),
		DB::raw('CONCAT(u4.address1," ", u4.address2) AS address'),
		DB::raw('CONCAT(u4.first_name, " ", u4.last_name) AS customer_name'),
		'lo.city AS city_name',
		'sm.state_name AS state_name',
		'co.country_name AS country_name',
		'u4.zipcode AS zipcode',
		'u4.code AS customer_code',
		'u5.dustbin_code AS allocated_dustbin_code',
		'u6.vehicle_number AS vehicle_number',
		'u7.para_value AS appointment_status',
		'u1.username AS created',
		'u2.username AS updated',
		DB::raw('DATE_FORMAT(appoinment.created_at, "%Y-%m-%d") AS date_create'),
		DB::raw('DATE_FORMAT(appoinment.updated_at, "%Y-%m-%d") AS date_update'),
		DB::raw('(CASE WHEN (1 = 1) THEN (SELECT COUNT(appointment_images.id) FROM appointment_images
		WHERE (appointment_images.appointment_id = appoinment.appointment_id))
		END) AS no_of_appointment_images'))
		->LEFTJOIN('adminuser as u1', 'appoinment.created_by' ,'=', 'u1.adminuserid')
		->LEFTJOIN('adminuser as u2', 'appoinment.updated_by' ,'=', 'u2.adminuserid')
		->LEFTJOIN('adminuser as u3', 'appoinment.collection_by' ,'=', 'u3.adminuserid')
		->JOIN('customer_master as u4', 'appoinment.customer_id' ,'=', 'u4.customer_id')
		->LEFTJOIN('location_master as lo', 'u4.city' ,'=', 'lo.location_id')
		->LEFTJOIN('state_master as sm', 'u4.state' ,'=', 'sm.state_id')
		->LEFTJOIN('country_master as co', 'u4.country' ,'=', 'co.country_id')
		->LEFTJOIN('dustbin_master as u5', 'appoinment.dustbin_id' ,'=', 'u5.dustbin_id')
		->LEFTJOIN('vehicle_master as u6', 'appoinment.vehicle_id' ,'=', 'u6.vehicle_id')
		->JOIN('parameter as u7', 'appoinment.para_status_id' ,'=', 'u7.para_id')
		->JOIN('appointment_collection as u8', 'appoinment.appointment_id' ,'=', 'u8.appointment_id');
		if (!$FromReport) {
			$AppointmentDetails->whereIn('appoinment.city_id',$cityId);
		}
		$AppointmentDetails->where('appoinment.company_id',Auth()->user()->company_id);
		$AppointmentDetails->where('appoinment.appointment_id',$id);
		return $AppointmentDetails->first();
	}

	public static function vehicleList($request){
		$CityId 		= GetBaseLocationCity();
		$sortBy         = ($request->has('sortBy')              && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "vehicle_id";
		$sortOrder      = ($request->has('sortOrder')           && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 5;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$vehicleList    =   VehicleMaster::where('status','A')
							->whereIn('city_id',$CityId)
							->select('vehicle_id','city_id','vehicle_number',\DB::raw('0 as avg_qty'),\DB::raw('0 as avg_amt'),\DB::raw('0 as avg_km'))
		->where('company_id',Auth()->user()->company_id);
		if($request->has('params.vehicle_number') && !empty($request->input('params.vehicle_number')))
		{
			$vehicleList->where('vehicle_number','like', '%'.$request->input('params.vehicle_number').'%');
		}
		if($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id')))
		{
			$vehicleList->where('vehicle_id',$request->input('params.vehicle_id'));
		}
		$result =  $vehicleList->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $result;
	}

	/*
	Use     : Check current product wise appointment
	Author  : Axay Shah
	Date    : 12 Feb,2019
	*/
	public static function checkCurrentProductAppointment($appDate)
	{
		$cityId 		= 	GetBaseLocationCity();
		$appDate        =   (!empty($appDate)) ? date('Y-m-d',strtotime($appDate)) : date('Y-m-d');
		$startDate      =   $appDate." ".GLOBAL_START_TIME;
		$endDate        =   $appDate." ".GLOBAL_END_TIME;
		$noa            =   Appoinment::whereBetween('app_date_time',array($startDate,$endDate))
							->where('product_id',">",0)
							->where("company_id",Auth()->user()->company_id)
							->whereIn('city_id',$cityId)
							->count();
		return $noa;
	}

	/* 
	Use : 
	*/
	public static function showCurrentProductAppointmentClientData($request){
		$appDate        = (!empty($request->appointment_cur_date)) ? date('Y-m-d',strtotime($request->appointment_cur_date)) : date('Y-m-d');
		$startDate      =   $appDate." ".GLOBAL_START_TIME;
		$endDate        =   $appDate." ".GLOBAL_END_TIME;
		$vehicleList    =   self::vehicleList($request);
		$currApp        =   self::getCurrentProductAppointmentData($appDate);
		/* if count greter then 0 then call ShowCurrentScheduledClientData() in OLD LR in new LR we will display only current product appointment*/
		$data['vehicleList'] = $vehicleList;
		foreach($currApp as $a){
			if(empty($a->coll_avg_time) || $a->coll_avg_time < MIN_COLL_AVG_TIME)
				$a->coll_avg_time   = MIN_COLL_AVG_TIME;
			}
		$data['appointment']    = $currApp;
		return $data;
	}

	/*
	use     : list current product appointment list according to vehicle
	Author  : Axay Shah
	Date    : 12 Feb,2019
	*/
	public static function getCurrentProductAppointmentData($appDate){
		$city 			= GetBaseLocationCity();
		$CityId 		= (!empty($city)) ? implode(",", $city) : 0;
		$appDate        = (!empty($appDate)) ? date('Y-m-d',strtotime($appDate)) : date('Y-m-d');
		$startDate      =   $appDate." ".GLOBAL_START_TIME;
		$endDate        =   $appDate." ".GLOBAL_END_TIME;
		// $data           =  \DB::select('CALL SP_GET_CURRENT_PRODUCT_APPOINTMENT_DATA('.Auth()->user()->company_id.','.$cityId.',"'.$startDate.'","'.$endDate.'",'.APPOINTMENT_CANCELLED.')');
		$data 			= self::SPGetCurrentProductAppointmentData($startDate,$endDate,Auth()->user()->company_id,$cityId,APPOINTMENT_CANCELLED);
		return $data;
	}
  
	/*
	Use     : Calculate app route estimated kilometer
	Author  : Axay Shah
	Date    : 25 Mar,2019
	*/
	public static function calculateAppRouteEastimatedKilometer($appData){
		$Kms 		= 0;
		$total_km	= 0;
		$prev_lat	= 0;
		$prev_lon	= 0;
		$curr_lat	= 0;
		$curr_lon 	= 0;
		$row 		= $appData;
		$priviousLat= NULL;
		$priviousLon= NULL;
		if(!empty($row)) { 
			for($i=0;$i<count($row);$i++) {
			   /* Check previous cordinates and set them.*/
				if(!empty($row[$i-1]['lattitude']) && !empty($row[$i-1]['longitude'])) {
					$prev_lat	= (isset($row[$i-1]['lattitude'])?$row[$i-1]['lattitude']:0);
					$prev_lon	= (isset($row[$i-1]['longitude'])?$row[$i-1]['longitude']:0);
				}
				/* Check current cordinates and set them.*/
				if(!empty($row[$i]['lattitude']) && !empty($row[$i]['longitude'])) {
					$curr_lat	= $row[$i]['lattitude'];
					$curr_lon	= $row[$i]['longitude'];
				}
				/* For first row data set kilometer 0.*/
				if($i==0) {
					$Kms = 0;
				} else {
					$Kms = _FormatNumberV2(distance($prev_lat,$prev_lon,$curr_lat,$curr_lon));
				}
				$total_km	+= $Kms;
			}
		}
		return $total_km;
	}

	/*
	Use     : Get appointment summery in details
	Author  : Axay Shah
	Date    : 03 April,2019
	*/
	public static function GetTodayAppointmentList($request,$FromBOP = false)
	{
		$city               = GetBaseLocationCity();
		$cityId 						= (!empty($city)) ? implode(",",$city) : 0;
		$company_id         = Auth()->user()->company_id;
		$startTime          = date("Y-m-d H:i:s");
		$endTime            = date("Y-m-d")." ".GLOBAL_END_TIME;
		$arrResult 	        = array();
		$AppointmentTbl     =   new Appoinment();
		$CollectionTbl      =   new AppointmentCollection();
		$DetailTbl          =   new AppointmentCollectionDetail();
		$CustomerTbl        =   new CustomerMaster();
		$VehicleTbl         =   new VehicleMaster();
		$LateRunning        =   new AppointmentRunningLate();
		$AdminUserTbl       =   new AdminUser();
		$APP                =   $AppointmentTbl->getTable();
		$COLL               =   $CollectionTbl->getTable();
		$CUS                =   $CustomerTbl->getTable();
		$DETAIL             =   $DetailTbl->getTable();
		$VEHICLE            =   $VehicleTbl->getTable();
		$ADMIN              =   $AdminUserTbl->getTable();
		$LATE 				=   $LateRunning->getTable();
		$AppParaSearch      =   (isset($request->parameter_search) && !empty($request->parameter_search)) ? $request->parameter_search : "";
		$sortBy             =   (isset($request->sortBy)      && !empty($request->sortBy))    ? $request->sortBy 	: "appointment_id";
		$sortOrder          =   (isset($request->sortOrder)   && !empty($request->sortOrder)) ? $request->sortOrder : "DESC";
		$recordPerPage      =   (!empty($request->size))       ?   $request->size         : 100;
		$pageNumber         =   (!empty($request->pageNumber)) ?   $request->pageNumber   : 0;
		$statusId           =   (isset($request->status) && !empty($request->status)) ? $request->status : "";
		$offset             =   0;      
								if(!empty($pageNumber) ) {
									$pageNumber = $pageNumber + 1;
									$offset = $recordPerPage * $pageNumber ;
								}
		 
		$query              =   "SELECT $APP.appointment_id, $COLL.collection_id, $APP.para_status_id, $VEHICLE.vehicle_number, $APP.app_date_time, 
								CONCAT($ADMIN.firstname,' ',$ADMIN.lastname) as driver_name, $APP.foc, IF (U4.last_name != '',
								CONCAT(U4.first_name,' ',U4.last_name),U4.first_name) As customer_name,$APP.city_id,$APP.company_id,
								TIMESTAMPDIFF(SECOND,appoinment.app_date_time,'$startTime') AS time_difference, 
								CASE WHEN 1=1 THEN (
									SELECT AP.app_date_time
									FROM $APP AP
									WHERE AP.customer_id = $APP.customer_id AND AP.para_status_id = 2006 
									ORDER BY AP.appointment_id DESC LIMIT 1 
								) End AS last_app_date, 
								CASE WHEN 1=1 THEN ( 
									SELECT SUM(CD.quantity) 
									FROM $DETAIL CD 
									INNER JOIN $COLL AC ON AC.collection_id = CD.collection_id 
									WHERE AC.appointment_id = $APP.appointment_id
								) END AS collection_qty ,
								$LATE.id
								FROM $APP 
								LEFT JOIN $CUS as `U4` on $APP.customer_id = U4.customer_id 
								LEFT JOIN $COLL on $APP.appointment_id = $COLL.appointment_id
								LEFT JOIN $VEHICLE on $APP.vehicle_id = $VEHICLE.vehicle_id 
								LEFT JOIN $ADMIN on $APP.`collection_by` = $ADMIN.adminuserid
								LEFT JOIN $LATE on $APP.`appointment_id` = $LATE.appointment_id";
								
								$cond 	    = (empty($statusId)) ? " AND ".$APP.".para_status_id != ".APPOINTMENT_CANCELLED."  HAVING collection_qty IS NULL OR collection_qty < '".MIN_COLLECTION_QTY."' ":'';        
								$WHERE      = " WHERE $APP.city_id IN ($cityId) and $APP.company_id= $company_id";
								if($FromBOP == false){
									$WHERE .= " AND U4.ctype != ".CUSTOMER_TYPE_BOP;
								}else{
									$WHERE .= " AND U4.ctype = ".CUSTOMER_TYPE_BOP;
								}
								$WHERE      .= "	AND DATE_FORMAT(".$APP.".app_date_time,'%Y-%m-%d') = '".date('Y-m-d')."' $cond ";
								
								
								if(!empty($statusId)){
									if($statusId == APP_TYPE_RUNNING_LATE){
										// $WHERE .= " AND ".$APP.".para_status_id = ".APPOINTMENT_SCHEDULED." HAVING time_difference > 0 AND time_difference < 1800 AND collection_qty IS NULL OR collection_qty < '".MIN_COLLECTION_QTY."' ";
										$WHERE .= " AND ".$APP.".para_status_id = ".APPOINTMENT_SCHEDULED." AND $LATE.id IS NOT NULL";
									}elseif($statusId == APP_TYPE_MISSED){
										$WHERE .= " AND ".$APP.".para_status_id = ".APPOINTMENT_SCHEDULED." AND $LATE.id IS NULL HAVING time_difference > 1800 AND collection_qty IS NULL OR collection_qty < '".MIN_COLLECTION_QTY."' ";
									}elseif($statusId == APP_TYPE_COMPLETED){
										$WHERE .= " AND ".$APP.".para_status_id = ".APPOINTMENT_COMPLETED;
									}elseif ($statusId == APP_TYPE_CANCEL) {
										$WHERE .= " AND ".$APP.".para_status_id = ".APPOINTMENT_CANCELLED;
									}
								}
								if(!empty($statusId) && $statusId == APPOINTMENT_SCHEDULED){
									$WHERE 	.= " AND ".$APP.".para_status_id = ".APPOINTMENT_SCHEDULED." HAVING time_difference < 0 "; 
								}
								$WHERE          .= " ORDER BY $APP.appointment_id DESC";
								$SQL            = $query.$WHERE;
								$countQuery     = \DB::select($SQL);
								$totalRecord    = count($countQuery);
								$totalPage      = round($totalRecord / $recordPerPage);
								$WHERE 	.= "LIMIT $offset, $recordPerPage";
								$data   =   \DB::select($SQL);
								if(count($data) > 0) { 
									$result 	= array(); 
									foreach($data as $row){
										$diff           = $row->time_difference;
										$status         = '';
										$color_class    = '';
										if($row->para_status_id == APPOINTMENT_SCHEDULED && ($diff > 1800) && $row->id == NULL) {
											$status 		= "Missed";
											$color_class 	= "app-type-missed";					
										// } elseif($row->para_status_id == APPOINTMENT_SCHEDULED && ($diff > 0 && $diff < 1800)) {
										} elseif($row->para_status_id == APPOINTMENT_SCHEDULED && $row->id != NULL) {
											$status 		= "Running late";
											$color_class 	= "app-type-running-late";					
										} elseif($row->para_status_id == APPOINTMENT_COMPLETED) {
											if ($row->collection_qty <= MIN_COLLECTION_QTY) {
												$status = "Doubtful";	
											} else {
												$status = "Completed";
											}
											$color_class 	= "app-type-done";
										} elseif($row->para_status_id == APPOINTMENT_SCHEDULED) {
											$status 		= "Pending";
											$color_class 	= "app-type-pending";					
										}elseif($row->para_status_id == APPOINTMENT_CANCELLED){
											$status 		= "Cancel";
											$color_class 	= "text-danger";	
										}
										$row->app_status_type = $status;
										$row->app_color_class = $color_class;
										$result[] = $row;
									}
									$arrResult['totalPages']    = $totalPage;
									$arrResult['size']          = $recordPerPage;
									$arrResult['totalElements'] = $totalRecord;
									$arrResult['totalElements'] = $totalRecord;
									$arrResult['result']	    = $result;
								}
							   
								return $arrResult;
	}


	/*
	NOTE 	: replace store procedure to Model function because of issue of comma seprated city id
	Use     : SP_GET_CURRENT_APPOINTMENT_DATA 
	Author  : Axay Shah
	Date    : 08 May,2019
	*/
	public static function SPGetCurrentAppointmentData($startDate,$endDate,$company_id,$cityId,$status){
		return DB::select("	SELECT appoinment.appointment_id,appoinment.customer_id,appoinment.vehicle_id,'' AS cust_group,appoinment.para_status_id,RIGHT(appoinment.app_date_time,8) AS appointment_time,CONCAT(U3.firstname,' ',U3.lastname) AS collection_by_user,
    				VM.vehicle_number,IF(U4.last_name != '',CONCAT(U4.first_name,' ',U4.last_name),U4.first_name) AS customer_name,
    				U4.code AS customr_code,U4.longitude,U4.lattitude,
    				IF(foc = 1,P1.scheduler_time,CA.app_time) AS coll_avg_time,
				    IF(foc = 1,'route','cust') AS app_type,
				    P1.para_value AS route_name
				  	FROM
				    	appoinment
				  	LEFT JOIN
				    	adminuser U1 ON appoinment.created_by = U1.adminuserid
				  	LEFT JOIN
				    	adminuser U2 ON appoinment.updated_by = U2.adminuserid
				  	LEFT JOIN
				   		adminuser U3 ON appoinment.collection_by = U3.adminuserid
				  	LEFT JOIN
				    	vehicle_master VM ON appoinment.vehicle_id = VM.vehicle_id
				  	LEFT JOIN
				    	customer_master U4 ON appoinment.customer_id = U4.customer_id
				  	LEFT JOIN
				    	customer_avg_collection CA ON CA.customer_id = U4.customer_id
				  	LEFT JOIN
				   		company_parameter P1 ON P1.map_customer_id = appoinment.customer_id
				  	LEFT JOIN
				    	dustbin_master U5 ON appoinment.dustbin_id = U5.dustbin_id
				  	WHERE
				    	1 = 1 AND(
				      	appoinment.para_status_id NOT IN($status)
				    	) AND(
				      	appoinment.app_date_time BETWEEN '$startDate' AND '$endDate'
				    	) AND appoinment.product_id =0 AND appoinment.city_id IN ($cityId) AND appoinment.company_id = $company_id
				UNION
					SELECT
				  	AM.id AS appointment_id,
					  IF(
					    AM.customer_group_id != '',
					    AM.customer_group_id,
					    AM.mrf_dept_id
					  ) AS customer_id,
					  AM.vehicle_id,
					  IF(
					    AM.customer_group_id != '',
					    AM.customer_group_id,
					    ''
					  ) AS cust_group,
					  '' AS para_status_id,
					  RIGHT(AM.app_date_time,
					  8) AS appointment_time,
					  VM.vehicle_number AS collection_by_user,
					  VM.vehicle_number,
					  IF(
					    AM.customer_group_id != '',
					    PR.para_value,
					    DP.department_name
					  ) AS customer_name,
					  '' AS customr_code,
					  IF(
					    AM.customer_group_id != '',
					    PR.longitude,
					    DP.longitude
					  ) AS longitude,
					  IF(
					    AM.customer_group_id != '',
					    PR.latitude,
					    DP.latitude
					  ) AS lattitude,
					  IF(
					    AM.customer_group_id != '',
					    PR.scheduler_time,
					    DP.scheduler_time
					  ) AS coll_avg_time,
					  IF(
					    AM.customer_group_id != '',
					    'group',
					    'dept'
					  ) AS app_type,
					  '' AS route_name
					FROM
					  appoinment_mediator AM
					LEFT JOIN
					  vehicle_master VM ON AM.vehicle_id = VM.vehicle_id
					LEFT JOIN
					  wm_department DP ON AM.mrf_dept_id = DP.id
					LEFT JOIN
					  company_parameter PR ON AM.customer_group_id = PR.para_id
					WHERE
					  (
					    AM.para_status_id NOT IN($status)
					  ) AND(
					    AM.app_date_time BETWEEN '$startDate' AND '$endDate'
					  ) AND  AM.city_id IN ($cityId) AND AM.company_id = $company_id");
	}

	/*
	NOTE 	: REPLACE STORE PROCEDURE TO MODEL FUNCTION BECAUSE OF STRING ISSUE IN CITY DATA
	USE 	: SP_GET_CURRENT_PRODUCT_APPOINTMENT_DATA
	AUTHOR 	: AXAY SHAH
	DATE 	: 08,MAY 2019
	*/

	public static function SPGetCurrentProductAppointmentData($startDate,$endDate,$company_id,$cityId,$status){
		return DB::select("SELECT appoinment.appointment_id,appoinment.customer_id,appoinment.vehicle_id,'' AS cust_group,
			appoinment.para_status_id,RIGHT(appoinment.app_date_time,8) AS appointment_time,
			CONCAT(U3.firstname,' ',U3.lastname) AS collection_by_user,VM.vehicle_number,
			IF(U4.last_name != '',CONCAT(U4.first_name,' ',U4.last_name),U4.first_name) AS customer_name,
	    	U4.code AS customr_code,U4.longitude,U4.lattitude,
	    	IF(
	      		foc = 1,
	      		P1.scheduler_time,
	      		CA.app_time
	    	) AS coll_avg_time,
	    	IF(foc = 1,
	    		'route',
	    		'cust') AS app_type,
	    	P1.para_value AS route_name
		FROM
		    appoinment
		LEFT JOIN
		    adminuser U1 ON appoinment.created_by = U1.adminuserid
		LEFT JOIN
		    adminuser U2 ON appoinment.updated_by = U2.adminuserid
		LEFT JOIN
		    adminuser U3 ON appoinment.collection_by = U3.adminuserid
		LEFT JOIN
		    vehicle_master VM ON appoinment.vehicle_id = VM.vehicle_id
		LEFT JOIN
		    customer_master U4 ON appoinment.customer_id = U4.customer_id
		LEFT JOIN
		    customer_avg_collection CA ON CA.customer_id = U4.customer_id
		LEFT JOIN
		    company_parameter P1 ON P1.map_customer_id = appoinment.customer_id
		LEFT JOIN
		    dustbin_master U5 ON appoinment.dustbin_id = U5.dustbin_id
		WHERE
	    1 = 1 AND(
	      appoinment.para_status_id NOT IN($status)
	    ) AND(
	      appoinment.app_date_time BETWEEN '$startDate' AND '$endDate'
	    ) AND appoinment.product_id > 0 AND appoinment.city_id IN ($cityId) AND appoinment.company_id = $company_id");
	}
}
