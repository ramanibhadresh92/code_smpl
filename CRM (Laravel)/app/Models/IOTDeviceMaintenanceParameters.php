<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IOTDeviceMaintenanceParameters extends Model
{
	protected 	$table 		= 'wm_iot_device_maintanance_parameters';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;

	public static function getBreakdownDevices($request)
	{
		$mrf_id 	= isset($request['mrf_id'])?$request['mrf_id']:0;
		$arrDevices	= self::where("mrf_id",$mrf_id)->orderBy("title","asc")->get()->toArray();
		$arrResult 	= array();
		if (!empty($arrDevices)) {
			foreach($arrDevices as $arrDevice) {
				$arrResult[] = array("id"=>$arrDevice['id'],"name"=>$arrDevice['title']);
			}
		}
		return $arrResult;
	}
}