<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use DB;
class IOTEquipmentLastReading extends Model
{
	protected 	$table 		= 'wm_iot_parameter_last_reading';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = false;

	public static function AddNewRecord($rowarray=array())
	{
		$NewRecord 							= new self();
		$NewRecord->slave_id 				= isset($rowarray['slave_id'])?$rowarray['slave_id']:0;
		$NewRecord->mrf_id 					= isset($rowarray['mrf_id'])?$rowarray['mrf_id']:0;
		$NewRecord->device_code 			= isset($rowarray['device_code'])?$rowarray['device_code']:0;
		$NewRecord->last_reading_datetime 	= isset($rowarray['reading_datetime'])?$rowarray['reading_datetime']:date("Y-m-d H:i:s");
		$NewRecord->save();
	}
}