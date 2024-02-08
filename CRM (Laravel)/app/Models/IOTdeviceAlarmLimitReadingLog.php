<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\IOTEquipmentReading;
use App\Facades\LiveServices;
use DB;
use Log;
class IOTdeviceAlarmLimitReadingLog extends Model
{
	protected 	$table 		= 'iot_device_alarm_limit_reading_log';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = false;

	public static function GetAlarmWidgetReportPROD($request)
	{
		$start_date = (isset($request->from_date) && !empty($request->from_date)) ? date("Y-m-d",strtotime($request->from_date))." ".GLOBAL_START_TIME : date("Y-m-d");
		$end_date   = (isset($request->to_date) && !empty($request->to_date)) ? date("Y-m-d",strtotime($request->to_date))." ".GLOBAL_END_TIME : date("Y-m-d");
		$mrf_id   	= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;		
		$SQL 		= "	SELECT  IDALRL.slave_id,IDALRL.mrf_id,WIEM.title,count(0) as cnt,WD.department_name,IDALRL.device_code,WIDP.title as device_name
						FROM iot_device_alarm_limit_reading_log AS IDALRL
						left JOIN wm_iot_equipment_master as WIEM ON IDALRL.mrf_id = WIEM.mrf_id  AND IDALRL.slave_id = WIEM.slave_id
						left JOIN wm_iot_device_parameters as WIDP ON IDALRL.device_code = WIDP.code
						left JOIN wm_department as WD ON IDALRL.mrf_id = WD.id
						WHERE IDALRL.reading_datetime BETWEEN '".$start_date."' AND '".$end_date."'";
		if($mrf_id == 0) {
			$SQL .= " AND WD.is_virtual = 0 AND WD.status = 1";
		} else {
			$SQL .= " AND IDALRL.mrf_id=$mrf_id";
		}
		$SQL .=	" 	GROUP BY IDALRL.mrf_id,IDALRL.slave_id,IDALRL.device_code
					ORDER BY cnt DESC, WIEM.title ASC,WD.department_name ASC ";
		$DATA = DB::select($SQL);
		return $DATA;
	}

	public static function GetAlarmWidgetReport($request)
	{
		$sortBy 		= (isset($request->sortBy) && !empty($request->sortBy)) ? $request->sortBy : "title";
		$sortOrder 		= (isset($request->sortOrder) && !empty($request->sortOrder)) ? $request->sortOrder : "ASC";
		$start_date 	= (isset($request->from_date) && !empty($request->from_date)) ? date("Y-m-d",strtotime($request->from_date))." ".GLOBAL_START_TIME : date("Y-m-d");
		$end_date   	= (isset($request->to_date) && !empty($request->to_date)) ? date("Y-m-d",strtotime($request->to_date))." ".GLOBAL_END_TIME : date("Y-m-d");
		$mrf_id   		= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;		
		$device_code    = (isset($request->device_code) && !empty($request->device_code)) ?   $request->device_code   : '';
		$device_name	= (isset($request->device_name) && !empty($request->device_name)) ? $request->device_name : "";
		$SQL 			= "	SELECT IDALRL.slave_id,IDALRL.mrf_id,WIEM.title,count(0) as cnt,WD.department_name,IDALRL.device_code,WIDP.title as device_name
							FROM iot_device_alarm_limit_reading_log AS IDALRL
							LEFT JOIN wm_iot_equipment_master as WIEM ON IDALRL.mrf_id = WIEM.mrf_id  AND IDALRL.slave_id = WIEM.slave_id
							LEFT JOIN wm_iot_device_parameters as WIDP ON IDALRL.device_code = WIDP.code
							LEFT JOIN wm_department as WD ON IDALRL.mrf_id = WD.id
							WHERE IDALRL.reading_datetime BETWEEN '".$start_date."' AND '".$end_date."'";
		if(empty($mrf_id)) {
			$SQL .= " AND WD.is_virtual = 0 AND WD.status = 1";
		} else {
			$SQL .= " AND IDALRL.mrf_id = ".intval($mrf_id);
		}
		if(!empty($device_code)) {
			$SQL .= " AND IDALRL.device_code like '%$device_code%' ";
		}
		if(!empty($device_name)) {
			$SQL .= " AND WIEM.title like '%$device_name%' ";
		}
		$SQL .=	" GROUP BY IDALRL.mrf_id,IDALRL.slave_id,IDALRL.device_code ";
		if($sortBy == "title") {
			$SQL .=	" ORDER BY WIEM.title $sortOrder";
		} else if($sortBy == "department_name") {
			$SQL .=	" ORDER BY WD.department_name $sortOrder";
		} else {
			$SQL .=	"ORDER BY WIEM.title, WD.department_name $sortOrder";
		}
		$DATA = DB::select($SQL);
		return $DATA;
	}

	/*
	Use 	: Give Perticular details of device and slave alarm alert
	Author 	: Axay Shah
	Date 	: 03-10-2023
	*/
	public static function GetAlarmDetailsForPerticularDeviceOldd($request)
	{
		$sortBy     	= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$device_code  	= !empty($request->input('params.device_code'))       ?   $request->input('params.device_code')   : "";
		$mrf_id     	= !empty($request->input('params.mrf_id')) ?   $request->input('params.mrf_id')   : '';
		$slave_id     	= !empty($request->input('params.slave_id')) ?   $request->input('params.slave_id')   : '';
		$from_date 		= ($request->has('params.from_date') && $request->input('params.from_date')) ? date("Y-m-d",strtotime($request->input("params.from_date"))) : "";
		$to_date 		= ($request->has('params.to_date') && $request->input('params.to_date')) ? date("Y-m-d",strtotime($request->input("params.to_date"))) : "";
		$SQL 			= self::select(	DB::raw("count(0) as cnt"),"iot_device_alarm_limit_reading_log.*","WIEM.title","WD.department_name",
										DB::raw("DATE_FORMAT(iot_device_alarm_limit_reading_log.reading_datetime, '%Y-%m-%d') as report_date"),
										"WIDP.title as device_name",DB::raw("avg(iot_device_alarm_limit_reading_log.reading) as reading"))
							->leftjoin("wm_iot_equipment_master as WIEM",function($q){
								$q->on("iot_device_alarm_limit_reading_log.mrf_id","=","WIEM.mrf_id")
								->on("iot_device_alarm_limit_reading_log.slave_id","=","WIEM.slave_id");
							})
							->leftjoin("wm_department as WD","iot_device_alarm_limit_reading_log.mrf_id","=","WD.id")

							->leftjoin("wm_iot_device_parameters as WIDP","iot_device_alarm_limit_reading_log.device_code","=","WIDP.code")
							->where("iot_device_alarm_limit_reading_log.mrf_id",$mrf_id)
							->where("iot_device_alarm_limit_reading_log.slave_id",$slave_id)
							->where("iot_device_alarm_limit_reading_log.device_code",$device_code)
							->whereBetween("iot_device_alarm_limit_reading_log.reading_datetime",array($from_date.' '.GLOBAL_START_TIME,$to_date.' '.GLOBAL_END_TIME))
							->groupBy(\DB::raw("DATE_FORMAT(iot_device_alarm_limit_reading_log.reading_datetime, '%Y-%m-%d')"))
							->orderBy("iot_device_alarm_limit_reading_log.reading_datetime","ASC")
							->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		$DATA = DB::select($SQL);
		return $DATA;
	}

	public static function GetAlarmDetailsForPerticularDevicePROD($request)
	{
		$sortBy     	= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$device_code  	= (isset($request->device_code) && !empty($request->device_code)) ? $request->device_code   : "";
		$mrf_id     	= (isset($request->mrf_id) && !empty($request->mrf_id)) ?   $request->mrf_id   : '';
		$slave_id     	= (isset($request->slave_id) && !empty($request->slave_id)) ?   $request->slave_id   : '';
		$from_date 		= (isset($request->from_date) && !empty($request->from_date)) ?  date("Y-m-d",strtotime($request->from_date))." ".GLOBAL_START_TIME : date("Y-m-d");
		$to_date 		= (isset($request->to_date) && !empty($request->to_date))  ? date("Y-m-d",strtotime($request->to_date))." ".GLOBAL_END_TIME : date("Y-m-d");

		$ReportSql 		= self::select(	"WIEM.title", "WD.department_name",
										"iot_device_alarm_limit_reading_log.slave_id",
										"iot_device_alarm_limit_reading_log.device_code",
										"iot_device_alarm_limit_reading_log.mrf_id",
										DB::raw("DATE_FORMAT(iot_device_alarm_limit_reading_log.reading_datetime,'%Y-%m-%d %r') as reading_datetime"),
										DB::raw("iot_device_alarm_limit_reading_log.reading_datetime as reading_date"),
										"WIDP.title as device_name","iot_device_alarm_limit_reading_log.reading",
										DB::raw("count(0) as cnt"))
							->leftjoin("wm_iot_equipment_master as WIEM",function($q){
								$q->on("iot_device_alarm_limit_reading_log.mrf_id","=","WIEM.mrf_id")
								->on("iot_device_alarm_limit_reading_log.slave_id","=","WIEM.slave_id");
							})
							->leftjoin("wm_department as WD","iot_device_alarm_limit_reading_log.mrf_id","=","WD.id")
							->leftjoin("wm_iot_device_parameters as WIDP","iot_device_alarm_limit_reading_log.device_code","=","WIDP.code")
							->where("iot_device_alarm_limit_reading_log.mrf_id",$mrf_id)
							->where("iot_device_alarm_limit_reading_log.slave_id",$slave_id)
							->where("iot_device_alarm_limit_reading_log.device_code",$device_code)
							->whereBetween("iot_device_alarm_limit_reading_log.reading_datetime",array($from_date.' '.GLOBAL_START_TIME,$to_date.' '.GLOBAL_END_TIME))
							->groupBy(DB::raw("reading_datetime, reading"))
							->orderBy("iot_device_alarm_limit_reading_log.reading","DESC")
							->orderBy("iot_device_alarm_limit_reading_log.reading_datetime","ASC");
		$ReportResults 	= $ReportSql->get();
		return $ReportResults;
	}

	public static function GetAlarmDetailsForPerticularDevice($request)
	{
		$sortBy 		= (isset($request->sortBy) && !empty($request->sortBy)) ? $request->sortBy : "reading_datetime";
		$sortOrder 		= (isset($request->sortOrder) && !empty($request->sortOrder)) ? $request->sortOrder : "ASC";
		$device_code  	= (isset($request->device_code) && !empty($request->device_code)) ? $request->device_code   : "";
		$mrf_id     	= (isset($request->mrf_id) && !empty($request->mrf_id)) ?   $request->mrf_id   : '';
		$slave_id     	= (isset($request->slave_id) && !empty($request->slave_id)) ?   $request->slave_id   : '';
		$from_date 		= (isset($request->from_date) && !empty($request->from_date)) ?  date("Y-m-d",strtotime($request->from_date))." ".GLOBAL_START_TIME : date("Y-m-d");
		$to_date 		= (isset($request->to_date) && !empty($request->to_date))  ? date("Y-m-d",strtotime($request->to_date))." ".GLOBAL_END_TIME : date("Y-m-d");
		$device_name	= (isset($request->device_name) && !empty($request->device_name)) ? $request->device_name : "";
		$ReportSql 		= self::select(	"WIEM.title", "WD.department_name",
										"iot_device_alarm_limit_reading_log.slave_id",
										"iot_device_alarm_limit_reading_log.device_code",
										"iot_device_alarm_limit_reading_log.mrf_id",
										DB::raw("DATE_FORMAT(iot_device_alarm_limit_reading_log.reading_datetime,'%Y-%m-%d %r') as reading_datetime"),
										DB::raw("iot_device_alarm_limit_reading_log.reading_datetime as reading_date"),
										"WIDP.title as device_name","iot_device_alarm_limit_reading_log.reading",
										DB::raw("count(0) as cnt"))
							->leftjoin("wm_iot_equipment_master as WIEM",function($q){
								$q->on("iot_device_alarm_limit_reading_log.mrf_id","=","WIEM.mrf_id")
								->on("iot_device_alarm_limit_reading_log.slave_id","=","WIEM.slave_id");
							})
							->leftjoin("wm_department as WD","iot_device_alarm_limit_reading_log.mrf_id","=","WD.id")
							->leftjoin("wm_iot_device_parameters as WIDP","iot_device_alarm_limit_reading_log.device_code","=","WIDP.code")
							->where("iot_device_alarm_limit_reading_log.mrf_id",$mrf_id)
							->where("iot_device_alarm_limit_reading_log.slave_id",$slave_id)
							->where("iot_device_alarm_limit_reading_log.device_code",$device_code)
							->where("WIDP.title",'like','%'.$device_name.'%')
							->whereBetween("iot_device_alarm_limit_reading_log.reading_datetime",array($from_date.' '.GLOBAL_START_TIME,$to_date.' '.GLOBAL_END_TIME))
							->groupBy(\DB::raw("HOUR(iot_device_alarm_limit_reading_log.reading_datetime)"))
							->groupBy(DB::raw("reading_datetime, reading"))
							->orderBy($sortBy,$sortOrder);
		$ReportResults 	= $ReportSql->get();
		return $ReportResults;
	}

	/*
	Use 	: Last 
	Author 	: Axay Shah
	Date 	: 03-10-2023
	*/
	public static function GetAlarmGraphPROD($request)
	{
		$slave_id 		= (isset($request->slave_id) && !empty($request->slave_id)) ? $request->slave_id : "";
		$mrf_id 		= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$device_code 	= (isset($request->device_code) && !empty($request->device_code)) ? $request->device_code : "";
		$start_date 	= (isset($request->from_date) && !empty($request->from_date)) ? date("Y-m-d",strtotime($request->from_date))." ".GLOBAL_START_TIME : date("Y-m-d");
		$end_date 		= (isset($request->to_date) && !empty($request->to_date)) ? date("Y-m-d",strtotime($request->to_date))." ".GLOBAL_END_TIME : date("Y-m-d");
		$SQL 			= "	SELECT  WIER.*,IF(IDALR.record_id = WIER.id,1,0) danger_alert,
							IDALR.reading as log_reading,
							IDATD.trigger_limit as trigger_limit
							FROM wm_iot_equipment_readings AS WIER
							inner JOIN wm_iot_device_parameters as WIDP ON WIER.device_code = WIDP.code
							inner JOIN wm_department as WD ON WIER.mrf_id = WD.id
							left JOIN iot_device_alarm_limit_reading_log as IDALR ON IDALR.record_id  = WIER.id
							inner JOIN iot_device_alarm_trigger_details as IDATD ON (WIER.device_code = IDATD.device_id AND WIER.slave_id = IDATD.slave_id AND  WIER.mrf_id = IDATD.mrf_id)
							WHERE WIER.reading_datetime BETWEEN '".$start_date."' AND '".$end_date."' AND WIER.mrf_id=$mrf_id
							AND WIER.device_code = $device_code and WIER.slave_id = $slave_id
							ORDER BY WIER.reading_datetime ASC";
		$DATA 			= DB::select($SQL);
		$result 		= array();
		$MAX_READING 	= 0;
		$MIN_READING 	= 0;
		if(!empty($DATA))
		{
			$i = 0;
			$result["ALERT_ARRAY"] = array();
			foreach($DATA as $RAW => $VALUE)
			{
				$result["X_AXIS"][] =  $VALUE->reading_datetime; 
				$result["Y_AXIS"][] =  $VALUE->reading; 
				$THRESHOLD_LIMIT 	= $VALUE->trigger_limit;
				if($VALUE->danger_alert == 1)
				{
					$result["ALERT_ARRAY"][$i]['X_AXIS'] =  $VALUE->reading_datetime; 
					$result["ALERT_ARRAY"][$i]['Y_AXIS'] =  $VALUE->log_reading; 
					$i++;
					if ($MAX_READING < $VALUE->log_reading) {
						$MAX_READING = $VALUE->log_reading;
					}
					if (empty($MIN_READING) || $MIN_READING > $VALUE->log_reading) {
						$MIN_READING = $VALUE->log_reading;
					}
				}
			}
			$result["MIN_READING"] 		= $MIN_READING;
			$result["MAX_READING"] 		= $MAX_READING;
			$result["THRESHOLD_LIMIT"] 	= $THRESHOLD_LIMIT;
		}
		return $result;
	}

	public static function GetAlarmGraph($request)
	{
		$sortBy 		= (isset($request->sortBy) && !empty($request->sortBy)) ? $request->sortBy : "reading_datetime";
		$sortOrder 		= (isset($request->sortOrder) && !empty($request->sortOrder)) ? $request->sortOrder : "ASC";
		$slave_id 		= (isset($request->slave_id) && !empty($request->slave_id)) ? $request->slave_id : "";
		$mrf_id 		= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$device_code 	= (isset($request->device_code) && !empty($request->device_code)) ? $request->device_code : "";
		$start_date 	= (isset($request->from_date) && !empty($request->from_date)) ? date("Y-m-d",strtotime($request->from_date))." ".GLOBAL_START_TIME : date("Y-m-d");
		$end_date 		= (isset($request->to_date) && !empty($request->to_date)) ? date("Y-m-d",strtotime($request->to_date))." ".GLOBAL_END_TIME : date("Y-m-d");
		$SQL 			= "	SELECT  WIER.*,IF(IDALR.record_id = WIER.id,1,0) danger_alert,IDALR.reading as log_reading,IDATD.trigger_limit as trigger_limit
							FROM wm_iot_equipment_readings AS WIER
							inner JOIN wm_iot_device_parameters as WIDP ON WIER.device_code = WIDP.code
							inner JOIN wm_department as WD ON WIER.mrf_id = WD.id
							left JOIN iot_device_alarm_limit_reading_log as IDALR ON IDALR.record_id  = WIER.id
							inner JOIN iot_device_alarm_trigger_details as IDATD ON (WIER.device_code = IDATD.device_id AND WIER.slave_id = IDATD.slave_id AND  WIER.mrf_id = IDATD.mrf_id)
							WHERE WIER.reading_datetime BETWEEN '".$start_date."' AND '".$end_date."' AND WIER.mrf_id=$mrf_id
							AND WIER.device_code = $device_code and WIER.slave_id = $slave_id
							ORDER BY WIER.reading_datetime ASC";
		$DATA 			= DB::select($SQL);
		$result 		= array();
		$MAX_READING 	= 0;
		$MIN_READING 	= 0;
		if(!empty($DATA))
		{
			$i = 0;
			$result["ALERT_ARRAY"] = array();
			foreach($DATA as $RAW => $VALUE)
			{
				$result["X_AXIS"][] =  $VALUE->reading_datetime; 
				$result["Y_AXIS"][] =  $VALUE->reading; 
				if($VALUE->danger_alert == 1)
				{
					$result["ALERT_ARRAY"][$i]['X_AXIS'] =  $VALUE->reading_datetime; 
					$result["ALERT_ARRAY"][$i]['Y_AXIS'] =  $VALUE->log_reading; 
					$i++;
					if ($MAX_READING < $VALUE->log_reading) {
						$MAX_READING = $VALUE->log_reading;
					}
					if (empty($MIN_READING) || $MIN_READING > $VALUE->log_reading) {
						$MIN_READING = $VALUE->log_reading;
					}
					$THRESHOLD_LIMIT = $VALUE->trigger_limit;
				}
			}
			$result["MIN_READING"] 		= $MIN_READING;
			$result["MAX_READING"] 		= $MAX_READING;
			$result["THRESHOLD_LIMIT"] 	= $THRESHOLD_LIMIT;
		}
		return $result;

	}
}