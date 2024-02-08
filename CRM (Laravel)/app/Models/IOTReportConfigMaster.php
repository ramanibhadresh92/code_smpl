<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmProductionReportMaster;
use App\Models\IOTEquipments;
use App\Models\IOTDeviceLastReading;
use App\Facades\LiveServices;
use DB;
class IOTReportConfigMaster extends Model
{
	protected 	$table 				= 'iot_report_config_master';
	protected 	$primaryKey 		= 'id';
	protected 	$guarded 			= ['id'];
	public 		$timestamps 		= false;
	
	public static function GetIOTReportConfig($REPORT_NAME,$MRF_ID,$SLAVE_ID,$DEVICE_CODE){
		return self::where(array("report_name" => $REPORT_NAME,"slave_id" => $SLAVE_ID,"device_code"=>$DEVICE_CODE,"mrf_id"=>$MRF_id))->first();
	}
}