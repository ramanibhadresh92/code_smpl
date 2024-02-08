<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IOTEquipments extends Model
{
	protected 	$table 		= 'wm_iot_equipment_master';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = false;

	public static function AddNewRecord($rowarray=array())
	{

	}
}