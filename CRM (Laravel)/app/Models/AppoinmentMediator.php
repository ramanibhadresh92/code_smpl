<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Appoinment;
class AppoinmentMediator extends Model
{
	//
	protected   $table      =   'appoinment_mediator';
	protected   $primaryKey =   'id'; // or null
	protected   $guarded    =   ['id'];
	public      $timestamps =   true;

	/*
	Use     :  Check Current mediator appointment 
	Author  :  Axay Shah
	Date    :  22 Jan,2019
	*/

	public static function checkCurrentMediatorAppointment($currDate = ""){
		$currDate   = (!empty($currDate)) ? date('Y-m-d',strtotime($currDate)) : date($currDate);
		$startDate  =   $currDate." ".GLOBAL_START_TIME;
		$endDate    =   $currDate." ".GLOBAL_END_TIME;
		$cityId 	= 	GetBaseLocationCity();
		return self::whereBetween('app_date_time', array($startDate,$endDate))
		->whereIn('city_id',$cityId)
		->where('company_id',Auth()->user()->company_id)
		->count();
	}
	/*
	Use     :  check current appointment count
	Author  :  Axay Shah
	Date    :  22 Jan,2019
	*/

	public static function checkCurrentAppointment($currDate = ""){
		$cityId 	= GetBaseLocationCity();
		$currDate   = (!empty($currDate)) ? date('Y-m-d',strtotime($currDate)) : date($currDate);
		$startDate  =   $currDate." ".GLOBAL_START_TIME;
		$endDate    =   $currDate." ".GLOBAL_END_TIME;
		return Appoinment::whereBetween('app_date_time', array($startDate,$endDate))
		->whereIn('city_id',$cityId)
		->where('company_id',Auth()->user()->company_id)
		->count();
	}

	/*
	Use     : Appointment Mediator insert record
	Author  : Axay Shah
	Date    : 28 Jan,2019
	*/

	public static function addAppointmentMediator($request){
		$appointment                    =   new self();
		$appointment->customer_group_id =   (isset($request->customer_group_id) && !empty($request->customer_group_id))     ? $request->customer_group_id   : 0;
		$appointment->mrf_dept_id       =   (isset($request->mrf_dept_id )      && !empty($request->mrf_dept_id ))          ? $request->mrf_dept_id         : 0;
		$appointment->app_date_time     =   (isset($request->app_date_time)     && !empty($request->app_date_time))         ? date("Y-m-d H:i:s",strtotime($request->app_date_time)) : "";
		$appointment->vehicle_id        =   (isset($request->vehicle_id )       && !empty($request->vehicle_id ))           ? $request->vehicle_id          : "";
		$appointment->reach_time        =   (isset($request->reach_time)        && !empty($request->reach_time))            ? date("Y-m-d H:i:s",strtotime($request->reach_time)) : "";
		$appointment->para_status_id    =   (isset($request->para_status_id)    && !empty($request->para_status_id))        ? $request->para_status_id      : "";
		$appointment->reach             =   (isset($request->reach)             && !empty($request->reach))                 ? $request->reach               : 0;
		$appointment->created_by        =   Auth()->user()->adminuserid ;
		$appointment->updated_by        =   Auth()->user()->adminuserid ;
		$appointment->city_id           =   (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
		$appointment->company_id        =   Auth()->user()->company_id ;
		if($appointment->save()){
			log_action('Appointment_Mediator_Added',$appointment->id,(new static)->getTable());
		}
		
	}


	public static function updateAppointmentMediatorReachStatus($request){
		$data = self::where('id',$request->app_mediator_id)->update(["reach"=>$request->reach,"reach_time"=>$request->reach_time,'updated_by'=>Auth()->user()->adminuserid,'updated_at'=>date('Y-m-d H:i:s')]);
		log_action('Appointment_Mediator_Updated',$request->app_mediator_id,(new static)->getTable());
		return $data;
	}
}
