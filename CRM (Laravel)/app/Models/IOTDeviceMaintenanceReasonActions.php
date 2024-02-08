<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\IOTDeviceMaintenanceParameters;

class IOTDeviceMaintenanceReasonActions extends Model
{
	protected 	$table 		= 'wm_iot_device_maintanance_reason_corrective_action';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;

	public static function getBreakdownReasons($request)
	{	
		$TYPE_AS_REASON = 1;
		$device_id 		= isset($request->device_id)?$request->device_id:0;
		$Device 		= IOTDeviceMaintenanceParameters::select("para_equipment_group_id")->where("id",$device_id)->first();
		$groupId 		= isset($Device->para_equipment_group_id)?$Device->para_equipment_group_id:0;
		$company_id 	= Auth()->user()->company_id;
		$arrReasons 	= self::select("id","title")
							->where("status",1)
							->where("company_id",$company_id)
							->where("para_equipment_group_id",$groupId)
							->where("type",$TYPE_AS_REASON)
							->orderBy("title","asc")
							->get()
							->toArray();
		$arrResult 	= array();
		if (!empty($arrReasons)) {
			foreach($arrReasons as $arrReason) {
				$arrResult[] = array("id"=>$arrReason['id'],"name"=>$arrReason['title']);
			}
		}
		$arrResult[] = array("id"=>NOTINLIST,"name"=>"Others");
		return $arrResult;
	}

	public static function getBreakdownReasonActions($request)
	{
		$TYPE_AS_REASON = 2;
		$company_id 	= Auth()->user()->company_id;
		$reason_id 		= isset($request->reason_id)?$request->reason_id:0;
		$arrReasons 	= self::select("id","title")
							->where("status",1)
							->where("company_id",$company_id)
							->where("reason_id",$reason_id)
							->where("type",$TYPE_AS_REASON)
							->orderBy("title","asc")
							->get()
							->toArray();
		$arrResult 	= array();
		if (!empty($arrReasons)) {
			foreach($arrReasons as $arrReason) {
				$arrResult[] = array("id"=>$arrReason['id'],"name"=>$arrReason['title']);
			}
		}
		$arrResult[] = array("id"=>NOTINLIST,"name"=>"Others");
		return $arrResult;
	}
}