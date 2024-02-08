<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IOTDeviceLastReading extends Model
{
	protected 	$table 		= 'wm_iot_device_last_reading';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;

	public static function AddNewRecord($rowarray=array())
	{

	}
}