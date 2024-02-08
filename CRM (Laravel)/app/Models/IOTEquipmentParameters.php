<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IOTEquipmentParameters extends Model
{
	protected 	$table 		= 'wm_iot_device_parameters';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = false;

	public static function AddNewRecord($rowarray=array())
	{

	}
}