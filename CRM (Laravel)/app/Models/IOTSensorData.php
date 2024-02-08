<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IOTSensorData extends Model
{
	protected 	$table 		= 'iot_sensor_raw_data';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;

	public static function AddNewRecord($request)
	{
		$row_data 	 			= (isset($request['row_data']) && !empty($request['row_data']))?$request['row_data']:"";
		if (!empty($row_data) && $row_data != "[]") {
			$NewRecord 				= new self();
			$NewRecord->row_data 	= $row_data;
			$NewRecord->save();
		}
	}
}