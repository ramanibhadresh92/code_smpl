<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class ShiftRunningHoursMaster extends Model implements Auditable
{
    protected 	$table 		=	'shift_running_hours_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;

	/*
	Use 	: ADD SHIFT REST HOURS
	Author 	: Axay Shah
	Date  	: 02 April,2020
	*/
	public static function AddShiftRunningHours($shift_id=0,$shift_timing_id=0,$mrf_id=0,$start_time='',$end_time='',$start_date,$end_date){
		$ID 					= 0;
		$ADD 					= new self();
		$ADD->shift_id 			= $shift_id;
		$ADD->mrf_id 			= $mrf_id;
		$ADD->shift_timing_id 	= $shift_timing_id;
		$ADD->start_time 		= (!empty($start_time)) ? date("H:i:s",strtotime($start_time)) : "";
		$ADD->end_time 			= (!empty($end_time)) 	? date("H:i:s",strtotime($end_time)) : "";
		$ADD->start_date 		= (!empty($start_date)) ? date("Y-m-d",strtotime($start_date)) : "";
		$ADD->end_date 			= (!empty($end_date)) 	? date("Y-m-d",strtotime($end_date)) : "";
		$ADD->created_by 		= Auth()->user()->adminuserid;
		$ADD->company_id 		= Auth()->user()->company_id;
		if($ADD->save()){
			$ID = $ADD->id;
		}
		return $ID;
	}
}
