<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmProductionReportMaster;
use App\Models\IOTEquipments;
use App\Models\IOTDeviceLastReading;
use App\Models\IOTReportConfigMaster;
use App\Facades\LiveServices;
use DB;
use DateTime;
use DatePeriod;
use DateInterval;
class IOTEquipmentReading extends Model
{
	protected 	$table 				= 'wm_iot_equipment_readings';
	protected 	$primaryKey 		= 'id';
	protected 	$guarded 			= ['id'];
	public 		$timestamps 		= false;
	public $AMP_DEVICE_CODE 		= 400222;
	public $RUN_HR_DEVICE_CODE 		= 400296;
	public $KWH_DEVICE_CODE 		= 400260;
	public $GRABBER_SLAVE_ID 		= 12;
	public $GRABBER_DEF_WEIGHT 		= 50;
	public $MAIN_METER_SLAVE_ID 	= 5;
	public $POWER_FACTOR_SLAVE_ID 	= 5;
	public $POWER_FACTOR_CODE 		= 400232;
	public $BALLASTIC_SLAVE_ID 		= 6;
	public $PLANT_START_TIME 		= "00:00:00";
	public $PLANT_END_TIME 			= "23:59:59";

	public static function AddNewRecord($rowarray=array())
	{
		$NewRecord 						= new self();
		$NewRecord->slave_id 			= isset($rowarray['slave_id'])?$rowarray['slave_id']:0;
		$NewRecord->mrf_id 				= isset($rowarray['mrf_id'])?$rowarray['mrf_id']:0;
		$NewRecord->device_code 		= isset($rowarray['device_code'])?$rowarray['device_code']:0;
		$NewRecord->reading 			= isset($rowarray['reading'])?$rowarray['reading']:0;
		$NewRecord->reading_datetime 	= isset($rowarray['reading_datetime'])?$rowarray['reading_datetime']:date("Y-m-d H:i:s");
		$NewRecord->reading_month 		= isset($rowarray['reading_month'])?$rowarray['reading_month']:date("m");
		$NewRecord->reading_year 		= isset($rowarray['reading_year'])?$rowarray['reading_year']:date("Y");
		$NewRecord->created_at 			= date("Y-m-d H:i:s");
		$NewRecord->save();
	}

	public function getKGPerHourReading($MRF_ID=0,$SLAVE_ID=0,$StartDate,$EndDate,$DeviceTitle)
	{
		$arrReturn 				= array();
		$READING_DATE_ARR[] 	= array();
		$PRODUCTION_QTY_ARR[] 	= array();
		$PRODUCTION_HR_ARR[] 	= array();
		$RUN_HOUR_ARR[] 		= array();
		$StartTime 		= date("Y-m-d",strtotime($StartDate))." ".GLOBAL_START_TIME;
		$EndTime 		= date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME;
		$arrReadingData = self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%Y-%m-%d') AS ReadingDate"),
										DB::raw("DATE_SUB(DATE_FORMAT(reading_datetime,'%Y-%m-%d'), INTERVAL 1 DAY) AS PREV_DATE"),
										DB::raw("CASE WHEN 1=1 THEN (
												SELECT last_reading
												FROM wm_iot_device_last_reading
												WHERE report_date = ReadingDate
												AND slave_id = ".$this->BALLASTIC_SLAVE_ID."
												AND mrf_id = ".$MRF_ID."
												AND device_code = ".$this->RUN_HR_DEVICE_CODE."
												) END AS TODAY_READING"),
										DB::raw("CASE WHEN 1=1 THEN (
												SELECT last_reading
												FROM wm_iot_device_last_reading
												WHERE report_date = PREV_DATE
												AND slave_id = ".$this->BALLASTIC_SLAVE_ID."
												AND mrf_id = ".$MRF_ID."
												AND device_code = ".$this->RUN_HR_DEVICE_CODE."
												) END AS PREV_DAY_READING"))
							->whereBetween("reading_datetime",[$StartTime,$EndTime])
							->where("slave_id",$this->BALLASTIC_SLAVE_ID)
							->where("mrf_id",$MRF_ID)
							->where("device_code",$this->RUN_HR_DEVICE_CODE)
							->where("reading",">",0);
		$DeviceReadings = $arrReadingData->groupBy("ReadingDate")->get()->toArray();

		if (!empty($DeviceReadings))
		{
			$arrReturn[] = array("Date","Production Qty","Production/Hr","Run-Hour");
			$arrReturn[] = array("Date","Production Qty","Production/Hr","Run-Hour");
			
			foreach ($DeviceReadings as $RowID=>$DeviceReading) {
				$Production_Qty 							= WmProductionReportMaster::getDailyTotalProduction($DeviceReading['ReadingDate'],$MRF_ID);
				$GRABER_WEIGHT 								= $this->getGrabberPickupCounterWithWeigth($DeviceReading['ReadingDate'],$MRF_ID);
				$DeviceReadings[$RowID]['Production_Qty']	= isset($Production_Qty['PRODUCTION_QTY']) && !empty($Production_Qty['PRODUCTION_QTY'])?($Production_Qty['PRODUCTION_QTY']):0;
				$DeviceReadings[$RowID]['Processed_Qty']	= isset($Production_Qty['PROCESSED_QTY']) && !empty($Production_Qty['PROCESSED_QTY'])?($Production_Qty['PROCESSED_QTY']):0;
				$RUNNING_HOUR 								= (!empty($DeviceReading['TODAY_READING']) && !empty($DeviceReading['PREV_DAY_READING']))?($DeviceReading['TODAY_READING']-$DeviceReading['PREV_DAY_READING']):0;
				$DeviceReadings[$RowID]['RUNNING_HOUR']		= $RUNNING_HOUR;
				$DeviceReadings[$RowID]['Processed_Qty']	= round($DeviceReadings[$RowID]['Processed_Qty'],2);
				$DeviceReadings[$RowID]['Production_Qty']	= round($DeviceReadings[$RowID]['Production_Qty'],2);
				$DeviceReadings[$RowID]['GRABER_WEIGHT']	= round($GRABER_WEIGHT['GRABER_WEIGHT'],2);
				$DeviceReadings[$RowID]['GRABER_COUNT']		= round($GRABER_WEIGHT['GRABBER_COUNT'],2);
				$DeviceReadings[$RowID]['AVG_PRO_PER_HR'] 	= !empty($DeviceReadings[$RowID]['Production_Qty'] && !empty($RUNNING_HOUR))?floor($DeviceReadings[$RowID]['Production_Qty']/$RUNNING_HOUR):0;

				$arrReturn[] 				= array($DeviceReadings[$RowID]['ReadingDate'],
													intval($DeviceReadings[$RowID]['Production_Qty']),
													intval($DeviceReadings[$RowID]['AVG_PRO_PER_HR']),
													intval($DeviceReadings[$RowID]['RUNNING_HOUR']));
				$READING_DATE_ARR[$RowID] 	= $DeviceReadings[$RowID]['ReadingDate'];
				$PRODUCTION_QTY_ARR[$RowID] = intval($DeviceReadings[$RowID]['Production_Qty']);
				$PRODUCTION_HR_ARR[$RowID] 	= intval($DeviceReadings[$RowID]['AVG_PRO_PER_HR']);
				$RUN_HOUR_ARR[$RowID] 		= intval($DeviceReadings[$RowID]['RUNNING_HOUR']);

			}
		}
		$YesterdayPowerConsumption 								= self::getYesterdayPowerConsumption($MRF_ID);
		$YesterdayPowerFactor 									= self::getYesterdayPowerFactor($MRF_ID);
		$arrResult['ChartData']['yesterday_power_consumption'] 	= $YesterdayPowerConsumption;
		$arrResult['ChartData']['yesterday_power_factor'] 		= $YesterdayPowerFactor;
		$arrResult['ChartData']['reading_date_data'] 			= $READING_DATE_ARR;
		$arrResult['ChartData']['production_qty_data'] 			= $PRODUCTION_QTY_ARR;
		$arrResult['ChartData']['avg_pro_per_hr_data'] 			= $PRODUCTION_HR_ARR;
		$arrResult['ChartData']['running_hr_data'] 				= $RUN_HOUR_ARR;
		$arrResult['TabularData'] 								= $DeviceReadings;
		return $arrResult;
	}

	public function getKGPerHourReadingV1($MRF_ID=0,$SLAVE_ID=0,$StartDate,$EndDate,$DeviceTitle)
	{
		$arrReturn 		= array();
		$StartTime 		= date("Y-m-d",strtotime($StartDate))." ".GLOBAL_START_TIME;
		$EndTime 		= date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME;
		$arrReadingData = self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%Y-%m-%d') AS ReadingDate"),
										DB::raw("DATE_SUB(DATE_FORMAT(reading_datetime,'%Y-%m-%d'), INTERVAL 1 DAY) AS PREV_DATE"),
										DB::raw("CASE WHEN 1=1 THEN (
												SELECT last_reading
												FROM wm_iot_device_last_reading
												WHERE report_date = ReadingDate
												AND slave_id = ".$this->GRABBER_SLAVE_ID."
												AND mrf_id = ".$MRF_ID."
												AND device_code = ".$this->RUN_HR_DEVICE_CODE."
												) END AS TODAY_READING"),
										DB::raw("CASE WHEN 1=1 THEN (
												SELECT last_reading
												FROM wm_iot_device_last_reading
												WHERE report_date = PREV_DATE
												AND slave_id = ".$this->GRABBER_SLAVE_ID."
												AND mrf_id = ".$MRF_ID."
												AND device_code = ".$this->RUN_HR_DEVICE_CODE."
												) END AS PREV_DAY_READING"))
							->whereBetween("reading_datetime",[$StartTime,$EndTime])
							->where("slave_id",$this->GRABBER_SLAVE_ID)
							->where("mrf_id",$MRF_ID)
							->where("device_code",$this->RUN_HR_DEVICE_CODE)
							->where("reading",">",0);
		$DeviceReadings = $arrReadingData->groupBy("ReadingDate")->get()->toArray();
		if (!empty($DeviceReadings))
		{
			$arrReturn[] = array("Date","Production Qty","Production/Hr","Run-Hour");
			foreach ($DeviceReadings as $RowID=>$DeviceReading) {
				$GRABER_WEIGHT 								= $this->getGrabberPickupCounterWithWeigth($DeviceReading['ReadingDate'],$MRF_ID);
				$DeviceReadings[$RowID]['Production_Qty']	= isset($Production_Qty['PRODUCTION_QTY']) && !empty($Production_Qty['PRODUCTION_QTY'])?($Production_Qty['PRODUCTION_QTY']):0;
				$DeviceReadings[$RowID]['Processed_Qty']	= isset($Production_Qty['PROCESSED_QTY']) && !empty($Production_Qty['PROCESSED_QTY'])?($Production_Qty['PROCESSED_QTY']):0;
				$RUNNING_HOUR 								= (!empty($DeviceReading['TODAY_READING']) && !empty($DeviceReading['PREV_DAY_READING']))?($DeviceReading['TODAY_READING']-$DeviceReading['PREV_DAY_READING']):0;
				$DeviceReadings[$RowID]['RUNNING_HOUR']		= $RUNNING_HOUR;
				$DeviceReadings[$RowID]['Processed_Qty']	= round($DeviceReadings[$RowID]['Processed_Qty'],2);
				$DeviceReadings[$RowID]['Production_Qty']	= round($DeviceReadings[$RowID]['Production_Qty'],2);
				$DeviceReadings[$RowID]['GRABER_WEIGHT']	= round($GRABER_WEIGHT['GRABER_WEIGHT'],2);
				$DeviceReadings[$RowID]['GRABER_COUNT']		= round($GRABER_WEIGHT['GRABBER_COUNT'],2);
				$DeviceReadings[$RowID]['AVG_PRO_PER_HR'] 	= !empty($DeviceReadings[$RowID]['Production_Qty'] && !empty($RUNNING_HOUR))?floor($DeviceReadings[$RowID]['Production_Qty']/$RUNNING_HOUR):0;

				$arrReturn[] = array($DeviceReadings[$RowID]['ReadingDate'],
									intval($DeviceReadings[$RowID]['Production_Qty']),
									intval($DeviceReadings[$RowID]['AVG_PRO_PER_HR']),
									intval($DeviceReadings[$RowID]['RUNNING_HOUR']));
			}
		}
		$arrResult['ChartData'] 	= $arrReturn;
		$arrResult['TabularData'] 	= $DeviceReadings;
		return $arrResult;
	}

	public function getEquipmentAvgReading($MRF_ID=0,$DEVICE_CODE=0,$StartDate,$EndDate,$ParameterUOM)
	{
		$arrReturn 		= array();
		$ReadingTable 	= (new self)->getTable();
		$IOTEquipments 	= (new IOTEquipments)->getTable();
		$StartTime 		= date("Y-m-d",strtotime($StartDate));
		$EndTime 		= date("Y-m-d",strtotime($EndDate));
		if (strtotime(date("Y-m-d",strtotime($StartDate))) == strtotime(date("Y-m-d",strtotime($EndDate)))) {
			$StartTime = date('Y-m-d', strtotime($StartDate .' -1 day'));
		}
		$DeviceReadings = IOTEquipments::select("$IOTEquipments.title",
												DB::raw("GetiOTDeviceReading($IOTEquipments.slave_id,$DEVICE_CODE,'$StartTime','$EndTime',0,".$MRF_ID.") AS READING"))
												->where("$IOTEquipments.mrf_id",$MRF_ID)
												->orderBy("READING","DESC")
												->get()
												->toArray();
		if (!empty($DeviceReadings))
		{
			foreach ($DeviceReadings as $RowID=>$DeviceReading) {
				$tempArray 						= array();
				$DeviceReading['DeviceReading'] = intval($DeviceReading['READING']);
				$tempArray['name'] 				= $DeviceReading['title'];
				$tempArray['value'] 			= $DeviceReading['READING'];
				$arrReturn[] 					= $tempArray;
			}
		}
		$arrResult['ChartData'] 	= $arrReturn;
		$arrResult['TabularData'] 	= $DeviceReadings;
		return $arrResult;
	}

	public function getEquipmentTimeAnalysisReading($MRF_ID=0,$SLAVE_ID=0,$ReportDate,$ParameterUOM)
	{
		$READING_DATE_ARR 	= array();
		$DEVICE_READING 	= array();
		$arrReturn 			= array();
		$ReadingTable 		= (new self)->getTable();
		$IOTEquipments 		= (new IOTEquipments)->getTable();
		$StartTime 			= date("Y-m-d",strtotime($ReportDate))." ".$this->PLANT_START_TIME;
		$EndTime 			= date("Y-m-d",strtotime($ReportDate))." ".$this->PLANT_END_TIME;
		$arrReadingData 	= self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%H:%i') AS ReadingTime"),DB::raw("IF(reading > 0,1,0) AS AMP_USED"))
								->whereBetween("reading_datetime",[$StartTime,$EndTime])
								->where("mrf_id",$MRF_ID)
								->where("slave_id",$SLAVE_ID)
								->where("device_code",$this->AMP_DEVICE_CODE);
		$DeviceReadings = $arrReadingData->orderby("ReadingTime","ASC")->get()->toArray();
		if (!empty($DeviceReadings))
		{
			foreach ($DeviceReadings as $RowID=>$DeviceReading) {
				$arrReturn[] 				= array($DeviceReading['ReadingTime'],intval($DeviceReading['AMP_USED']));
				$READING_DATE_ARR[$RowID] 	= $DeviceReading['ReadingTime'];
				$DEVICE_READING[$RowID] 	= intval($DeviceReading['AMP_USED']);
				
			}
		}
		$arrResult['ChartData']['reading_time_date'] 	= $READING_DATE_ARR;
		$arrResult['ChartData']['amp_use_data'] 		= $DEVICE_READING;
		return $arrResult;
	}

	public function getEquipmentAmpReading($MRF_ID=0,$SLAVE_ID=0,$ReportDate,$ParameterUOM,$StartDate="",$EndDate="",$OnOff=false)
	{
		$READING_DATE_ARR 	= array();
		$DEVICE_READING 	= array();
		$arrReturn 			= array();
		$ReadingTable 		= (new self)->getTable();
		$IOTEquipments 		= (new IOTEquipments)->getTable();
		if (!empty($ReportDate)) {
			$StartTime 			= date("Y-m-d",strtotime($ReportDate))." ".$this->PLANT_START_TIME;
			$EndTime 			= date("Y-m-d",strtotime($ReportDate))." ".$this->PLANT_END_TIME;
		} else if (!empty($StartDate) && !empty($EndDate)) {
			$StartTime 			= date("Y-m-d",strtotime($StartDate))." ".$this->PLANT_START_TIME;
			$EndTime 			= date("Y-m-d",strtotime($EndDate))." ".$this->PLANT_END_TIME;
		} else {
			$StartDate 			= date("Y-m-d");
			$StartTime 			= date("Y-m-d",strtotime($StartDate))." ".$this->PLANT_START_TIME;
			$EndTime 			= date("Y-m-d",strtotime($EndDate))." ".$this->PLANT_END_TIME;
		}
		$TimeDiff = strtotime($EndTime)-strtotime($StartTime);
		$DayDiff = round($TimeDiff / (60 * 60 * 24));
		if ($DayDiff <= 1) {
			if ($OnOff) {
				$arrReadingData 	= self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%H:%i') AS ReadingTime"),
													DB::raw("IF(reading > 0,1,0) AS AMP_USED"))
											->whereBetween("reading_datetime",[$StartTime,$EndTime])
											->where("mrf_id",$MRF_ID)
											->where("slave_id",$SLAVE_ID)
											->where("device_code",$this->AMP_DEVICE_CODE);
			} else {
				$arrReadingData 	= self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%H:%i') AS ReadingTime"),
													DB::raw("reading AS AMP_USED"))
											->whereBetween("reading_datetime",[$StartTime,$EndTime])
											->where("mrf_id",$MRF_ID)
											->where("slave_id",$SLAVE_ID)
											->where("device_code",$this->AMP_DEVICE_CODE);
			}
		} else {
			if ($OnOff) {
				$arrReadingData 	= self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%b-%d %H') AS ReadingTime"),
													DB::raw("IF(AVG(reading) > 0,1,0) AS AMP_USED"))
											->whereBetween("reading_datetime",[$StartTime,$EndTime])
											->where("mrf_id",$MRF_ID)
											->where("slave_id",$SLAVE_ID)
											->where("device_code",$this->AMP_DEVICE_CODE)
											->groupBy("ReadingTime");
			} else {
				$arrReadingData 	= self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%b-%d %H') AS ReadingTime"),
													DB::raw("AVG(reading) AS AMP_USED"))
											->whereBetween("reading_datetime",[$StartTime,$EndTime])
											->where("mrf_id",$MRF_ID)
											->where("slave_id",$SLAVE_ID)
											->where("device_code",$this->AMP_DEVICE_CODE)
											->groupBy("ReadingTime");
			}
		}
		$DeviceReadings = $arrReadingData->orderby("ReadingTime","ASC")->get()->toArray();
		if (!empty($DeviceReadings))
		{
			foreach ($DeviceReadings as $RowID=>$DeviceReading) {
				$arrReturn[] 				= array($DeviceReading['ReadingTime'],floatval($DeviceReading['AMP_USED']));
				$READING_DATE_ARR[$RowID] 	= $DeviceReading['ReadingTime'];
				$DEVICE_READING[$RowID] 	= intval($DeviceReading['AMP_USED']);
			}
		}
		
		$arrResult['ChartData']['reading_time_date'] 	= $READING_DATE_ARR;
		$arrResult['ChartData']['amp_use_data'] 		= $DEVICE_READING;
		return $arrResult;
	}

	public function getGrabberPickupCounterWithWeigth($ReportDate,$MRF_ID=0)
	{
		$arrReturn 		= array("GRABBER_COUNT"=>0,"GRABER_WEIGHT"=>0);
		$ReadingTable 	= (new self)->getTable();
		$IOTEquipments 	= (new IOTEquipments)->getTable();
		$StartTime 		= date("Y-m-d",strtotime($ReportDate))." ".$this->PLANT_START_TIME;
		$EndTime 		= date("Y-m-d",strtotime($ReportDate))." ".$this->PLANT_END_TIME;
		$arrReadingData = self::select(DB::raw("SUM(IF(reading > 6.90,1,0)) AS GRABBER_COUNT"))
							->whereBetween("reading_datetime",[$StartTime,$EndTime])
							->where("mrf_id",$MRF_ID)
							->where("slave_id",$this->GRABBER_SLAVE_ID)
							->where("device_code",$this->AMP_DEVICE_CODE);
		$DeviceReadings = $arrReadingData->get()->toArray();
		if (!empty($DeviceReadings))
		{
			foreach ($DeviceReadings as $RowID=>$DeviceReading) {
				$arrReturn["GRABBER_COUNT"] = $DeviceReading['GRABBER_COUNT'];
			}
		}
		$arrReturn['GRABER_WEIGHT'] = $arrReturn["GRABBER_COUNT"] * $this->GRABBER_DEF_WEIGHT;
		return $arrReturn;
	}

	public function getKGPerkWH($MRF_ID=0,$StartDate,$EndDate)
	{
		$arrReturn 			= array();
		$ReadingTable 		= (new self)->getTable();
		$IOTEquipments 		= (new IOTEquipments)->getTable();
		$arrReturn 			= array();
		$StartTime 			= date("Y-m-d",strtotime($StartDate))." ".GLOBAL_START_TIME;
		$EndTime 			= date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME;
		$reading_date_data 	= array();
		$Kg_kWH_data 		= array();
		$kWH_ARR 			= array();
		$arrReadingData 	= self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%Y-%m-%d') AS ReadingDate"),
											DB::raw("DATE_SUB(DATE_FORMAT(reading_datetime,'%Y-%m-%d'), INTERVAL 1 DAY) AS PREV_DATE"),
											DB::raw("CASE WHEN 1=1 THEN (
													SELECT last_reading
													FROM wm_iot_device_last_reading
													WHERE report_date = ReadingDate
													AND slave_id = ".$this->MAIN_METER_SLAVE_ID."
													AND mrf_id = ".$MRF_ID."
													AND device_code = ".$this->KWH_DEVICE_CODE."
													) END AS TODAY_READING"),
											DB::raw("CASE WHEN 1=1 THEN (
													SELECT last_reading
													FROM wm_iot_device_last_reading
													WHERE report_date = PREV_DATE
													AND slave_id = ".$this->MAIN_METER_SLAVE_ID."
													AND mrf_id = ".$MRF_ID."
													AND device_code = ".$this->KWH_DEVICE_CODE."
													) END AS PREV_DAY_READING"))
								->whereBetween("reading_datetime",[$StartTime,$EndTime])
								->where("slave_id",$this->MAIN_METER_SLAVE_ID)
								->where("mrf_id",$MRF_ID)
								->where("device_code",$this->KWH_DEVICE_CODE);
		$DeviceReadings = $arrReadingData->groupBy("ReadingDate")->get()->toArray();
		if (!empty($DeviceReadings))
		{
			$arrReturn[] 		= array("Date","Kg/kWH","kWH");
			foreach ($DeviceReadings as $RowID=>$DeviceReading) {
				$Production_Qty = WmProductionReportMaster::getDailyTotalProduction($DeviceReading['ReadingDate'],$MRF_ID);
				$READING 		= round(floatval($DeviceReading['TODAY_READING']-$DeviceReading['PREV_DAY_READING']),2);
				$KgPerkWH 		= round((($Production_Qty['PRODUCTION_QTY'] > 0 && $READING > 0)?($Production_Qty['PRODUCTION_QTY']/$READING):0),2);
				$arrReturn[] 				= array($DeviceReading['ReadingDate'],$KgPerkWH,$READING);
				$reading_date_data[$RowID] 	= $DeviceReading['ReadingDate'];
				$Kg_kWH_data[$RowID] 		= $KgPerkWH;
				$kWH_ARR[$RowID] 			= $READING;

			}
		}
		$arrResult['ChartData']['result'] 				= $arrReturn;
		$arrResult['ChartData']['reading_date_data'] 	= $reading_date_data;
		$arrResult['ChartData']['Kg_kWH_data'] 			= $Kg_kWH_data;
		$arrResult['ChartData']['kWH'] 					= $kWH_ARR;
		
		return $arrResult;
	}

	public function getKGPerkWHV2($MRF_ID=0,$StartDate,$EndDate)
	{
		$arrReturn 			= array();
		$ReadingTable 		= (new self)->getTable();
		$IOTEquipments 		= (new IOTEquipments)->getTable();
		$arrReturn 			= array();
		$StartTime 			= date("Y-m-d",strtotime($StartDate))." ".GLOBAL_START_TIME;
		$EndTime 			= date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME;
		$reading_date_data 	= array();
		$Kg_kWH_data 		= array();
		$kWH_ARR 			= array();
		$arrReadingData 	= self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%Y-%m-%d') AS ReadingDate"),
											DB::raw("DATE_SUB(DATE_FORMAT(reading_datetime,'%Y-%m-%d'), INTERVAL 1 DAY) AS PREV_DATE"),
											DB::raw("CASE WHEN 1=1 THEN (
													SELECT last_reading
													FROM wm_iot_device_last_reading
													WHERE report_date = ReadingDate
													AND slave_id = ".$this->MAIN_METER_SLAVE_ID."
													AND mrf_id = ".$MRF_ID."
													AND device_code = ".$this->KWH_DEVICE_CODE."
													) END AS TODAY_READING"),
											DB::raw("CASE WHEN 1=1 THEN (
													SELECT last_reading
													FROM wm_iot_device_last_reading
													WHERE report_date = PREV_DATE
													AND slave_id = ".$this->MAIN_METER_SLAVE_ID."
													AND mrf_id = ".$MRF_ID."
													AND device_code = ".$this->KWH_DEVICE_CODE."
													) END AS PREV_DAY_READING"))
								->whereBetween("reading_datetime",[$StartTime,$EndTime])
								->where("slave_id",$this->MAIN_METER_SLAVE_ID)
								->where("mrf_id",$MRF_ID)
								->where("device_code",$this->KWH_DEVICE_CODE);
		$DeviceReadings = $arrReadingData->groupBy("ReadingDate")->get()->toArray();
		if (!empty($DeviceReadings))
		{
			$arrReturn[] 		= array("Date","Kg/kWH","kWH");
			foreach ($DeviceReadings as $RowID=>$DeviceReading) {
				$Production_Qty 			= WmProductionReportMaster::getDailyTotalProduction($DeviceReading['ReadingDate'],$MRF_ID);
				$READING 					= round(floatval($DeviceReading['TODAY_READING']-$DeviceReading['PREV_DAY_READING']),2);
				$KgPerkWH 					= round((($Production_Qty['PRODUCTION_QTY'] > 0 && $READING > 0)?($Production_Qty['PRODUCTION_QTY']/$READING):0),2);
				$arrReturn[] 				= array($DeviceReading['ReadingDate'],$KgPerkWH,$READING);
				$reading_date_data[$RowID] 	= $DeviceReading['ReadingDate'];
				$Kg_kWH_data[$RowID] 		= $KgPerkWH;
				$kWH_ARR[$RowID] 			= $READING;
			}
		}
		$arrResult['ChartData'] 				= $arrReturn;
		return $arrResult;
	}

	public function getPowerFactorChart($MRF_ID=0,$StartDate,$EndDate)
	{
		$arrReturn 		= array();
		$ReadingTable 	= (new self)->getTable();
		$IOTEquipments 	= (new IOTEquipments)->getTable();
		$StartTime 		= date("Y-m-d",strtotime($StartDate))." ".$this->PLANT_START_TIME;
		$EndTime 		= date("Y-m-d",strtotime($EndDate))." ".$this->PLANT_END_TIME;
		$PF_DATA 		= array();
		$READING_DATA 	= array();
		$arrReadingData = self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%Y-%m-%d') AS ReadingDate"),
										DB::raw("reading AS PF"))
							->whereBetween("reading_datetime",[$StartTime,$EndTime])
							->where("mrf_id",$MRF_ID)
							->where("slave_id",$this->POWER_FACTOR_SLAVE_ID)
							->where("device_code",$this->POWER_FACTOR_CODE);
		$DeviceReadings = $arrReadingData->groupBy("ReadingDate")->orderby("ReadingDate","ASC")->get()->toArray();
		if (!empty($DeviceReadings))
		{
			$arrReturn[] = array("Date","Reading");
			foreach ($DeviceReadings as $RowID=>$DeviceReading) {
				$arrReturn[] 				= array($DeviceReading['ReadingDate'],floatval($DeviceReading['PF']));
				$READING_DATA[$RowID] 		= $DeviceReading['ReadingDate'];
				$PF_DATA[$RowID] 			= floatval($DeviceReading['PF']);
			}
		}
		$arrResult['ChartData']['result'] 		= $arrReturn;
		$arrResult['ChartData']['reading_date'] = $READING_DATA;
		$arrResult['ChartData']['PF'] 			= $PF_DATA;
		return $arrResult;
	}

	public function getPowerFactorChartV2($MRF_ID=0,$StartDate,$EndDate)
	{
		$arrReturn 		= array();
		$ReadingTable 	= (new self)->getTable();
		$IOTEquipments 	= (new IOTEquipments)->getTable();
		$StartTime 		= date("Y-m-d",strtotime($StartDate))." ".$this->PLANT_START_TIME;
		$EndTime 		= date("Y-m-d",strtotime($EndDate))." ".$this->PLANT_END_TIME;
		$PF_DATA 		= array();
		$READING_DATA 	= array();
		$arrReadingData = self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%Y-%m-%d') AS ReadingDate"),
										DB::raw("reading AS PF"))
							->whereBetween("reading_datetime",[$StartTime,$EndTime])
							->where("mrf_id",$MRF_ID)
							->where("slave_id",$this->POWER_FACTOR_SLAVE_ID)
							->where("device_code",$this->POWER_FACTOR_CODE);
		$DeviceReadings = $arrReadingData->groupBy("ReadingDate")->orderby("ReadingDate","ASC")->get()->toArray();
		if (!empty($DeviceReadings))
		{
			$arrReturn[] = array("Date","Reading");
			foreach ($DeviceReadings as $RowID=>$DeviceReading) {
				$arrReturn[] 				= array($DeviceReading['ReadingDate'],floatval($DeviceReading['PF']));
				$READING_DATA[$RowID] 		= $DeviceReading['ReadingDate'];
				$PF_DATA[$RowID] 			= floatval($DeviceReading['PF']);
			}
		}
		$arrResult['ChartData']	= $arrReturn;
		return $arrResult;
	}

	public function getYesterdayPowerConsumption($MRF_ID=0)
	{
		$Yesterday 			= date("Y-m-d",strtotime("Yesterday"));
		$DayBeforeYesterday = date("Y-m-d",strtotime("-2 days"));
		$DBYReading 		= IOTDeviceLastReading::select("last_reading")
								->where("report_date",$DayBeforeYesterday)
								->where("slave_id",$this->MAIN_METER_SLAVE_ID)
								->where("device_code",$this->KWH_DEVICE_CODE)
								->where("mrf_id",$MRF_ID)
								->first();
		$YesterDayReading 	= IOTDeviceLastReading::select("last_reading")
								->where("report_date",$Yesterday)
								->where("slave_id",$this->MAIN_METER_SLAVE_ID)
								->where("device_code",$this->KWH_DEVICE_CODE)
								->where("mrf_id",$MRF_ID)
								->first();
		$FinalReading 		= isset($YesterDayReading->last_reading)?$YesterDayReading->last_reading:0;
		$FinalReading 		= isset($YesterDayReading->last_reading) && isset($DBYReading->last_reading)?($YesterDayReading->last_reading-$DBYReading->last_reading):$FinalReading;
		return _FormatNumberV2($FinalReading);
	}

	public function getYesterdayPowerFactor($MRF_ID=0)
	{
		$Yesterday 			= date("Y-m-d",strtotime("Yesterday"));
		$YesterDayReading 	= IOTDeviceLastReading::select("last_reading")
								->where("report_date",$Yesterday)
								->where("slave_id",$this->POWER_FACTOR_SLAVE_ID)
								->where("device_code",$this->POWER_FACTOR_CODE)
								->where("mrf_id",$MRF_ID)
								->first();
		$FinalReading 		= isset($YesterDayReading->last_reading)?$YesterDayReading->last_reading:0;
		return _FormatNumberV2($FinalReading);
	}

	public static function GetIotDashboardData($request)
	{
		$MRF_ID 		= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
		$START_DATE 	= (isset($request['report_starttime']) && !empty($request['report_starttime'])) ? $request['report_starttime'] : date("Y-m-d");
		$END_DATE 		= (isset($request['report_endtime']) && !empty($request['report_endtime'])) ? $request['report_endtime'] : date("Y-m-d");
		$arrReturn 		= array();
		$ReadingTable 	= (new self)->getTable();
		$IOTEquipments 	= (new IOTEquipments)->getTable();
		$StartTime 		= date("Y-m-d",strtotime($START_DATE));
		$EndTime 		= date("Y-m-d",strtotime($END_DATE));
		$SameDay 		= false;
		$Today 			= date("Y-m-d");
		$DaysDiff 		= strtotime($END_DATE) - strtotime($START_DATE);
		if ($DaysDiff > 0) {
			$DaysDiff = round($DaysDiff / (60 * 60 * 24));
		} else {
			$DaysDiff = 1;
		}
		if (strtotime(date("Y-m-d",strtotime($START_DATE))) == strtotime(date("Y-m-d",strtotime($END_DATE)))) {
			$StartTime 	= date('Y-m-d', strtotime($START_DATE .' -1 day'));
			$SameDay 	= true;
		}
		if (strtotime($EndTime) >= strtotime($Today)) {
			$EndTime = $Today;
		}
		$MRF_ID 		= (!empty($MRF_ID)) ? explode(",",$MRF_ID) : "";
		$array_data 	= array();
		$IOT_DATA 		= IOTReportConfigMaster::select("iot_report_config_master.*","wm_department.department_name","wm_department.id as mrf_id")
							->leftjoin("wm_department","wm_department.id","=","iot_report_config_master.mrf_id")
							->whereIn("iot_report_config_master.mrf_id",$MRF_ID)
							->where("iot_report_config_master.status","1")
							->where("iot_report_config_master.tabular",1)
							->get()
							->toArray();
		if(!empty($IOT_DATA))
		{
			foreach($IOT_DATA AS $iot_key => $iot_value)
			{
				$MRF_ID 			= $iot_value['mrf_id'];
				$run_hours 			= 0;
				$power_cons 		= 0;
				$production_qty 	= 0;
				$MRF_NAME 			= "";
				$SQLQUERY 			= "";
				$report_details 	= DB::table("iot_report_config_details")->where("config_id",$iot_value['id'])->get()->toArray();
				if(!empty($report_details))
				{
					foreach($report_details as $rk => $rv)
					{
						$DEVICE_CODE 	= $rv->device_code;
						$SLAVE_ID 		= $rv->slave_id;
						$MIN_VAL 		= $rv->min_value;
						$AFR 			= $rv->afr;
						switch($rv->report_name)
						{
							case "YESTERDAY_RUN_HOURS" :
								$DeviceReadings = IOTEquipments::select("$IOTEquipments.title",
																		DB::raw("GetiOTDeviceReading($SLAVE_ID,$DEVICE_CODE,'$StartTime','$EndTime',0,".$MRF_ID.") AS READING"))
																		->where("$IOTEquipments.mrf_id",$MRF_ID)
																		->where("$IOTEquipments.slave_id",$SLAVE_ID)
																		->first();
								$run_hours 		= (isset($DeviceReadings->READING)) ? $DeviceReadings->READING : 0;
								$run_hours 		= ($run_hours < ($MIN_VAL*$DaysDiff)) ? "<font style='color:red;font-weight:bold'>".$run_hours."</font>" : "<font style='color:green;font-weight:bold'>".$run_hours."</font>";
							break;
							case "YESTERDAY_POWER_CONSUMPTION":
								$prev_power_cons 	= IOTDeviceLastReading::select("last_reading")
														->where("report_date",$StartTime)
														->where("slave_id",$SLAVE_ID)
														->where("device_code",$DEVICE_CODE)
														->where("mrf_id",$MRF_ID)
														->first();
								$ReadingDate = $START_DATE;
								if (!$SameDay) {
									$ReadingDate = $EndTime;
								}
								$SQL 	= IOTDeviceLastReading::select("last_reading")
														->where("report_date",$ReadingDate)
														->where("slave_id",$SLAVE_ID)
														->where("device_code",$DEVICE_CODE)
														->where("mrf_id",$MRF_ID);
								$power_cons 		= IOTDeviceLastReading::select("last_reading")
														->where("report_date",$ReadingDate)
														->where("slave_id",$SLAVE_ID)
														->where("device_code",$DEVICE_CODE)
														->where("mrf_id",$MRF_ID)
														->first();
								$prev_power_cons_reading	= (isset($prev_power_cons->last_reading)) ? $prev_power_cons->last_reading : 0;
								$power_cons_reading 		= (isset($power_cons->last_reading)) ? $power_cons->last_reading : 0;
								$date_power_cons			= round(($power_cons_reading - $prev_power_cons_reading),2);
								$power_cons 				= ($date_power_cons < ($MIN_VAL*$DaysDiff))?"<font style='color:red;font-weight:bold'>".$date_power_cons."</font>":"<font style='color:green;font-weight:bold'>".$date_power_cons."</font>";
							break;
							case "PRODUCTION_REPORT" :
								$production_qty = WmProductionReportMaster::GetTotalProductionReportByDate($MRF_ID,$START_DATE,$END_DATE,$AFR);
								$production_qty = ($production_qty > 0) ? _FormatNumberV2($production_qty / 1000) : 0;
								$production_qty = ($production_qty < ($MIN_VAL*$DaysDiff)) ? "<font style='color:red;font-weight:bold'>".$production_qty."</font>" : "<font style='color:green;font-weight:bold'>".$production_qty."</font>";
							break;
						}
						if ($AFR) {
							$MRF_NAME = $iot_value['department_name']." - AFR";
						} else {
							$MRF_NAME = $iot_value['department_name'];
						}
					}
				}
				$array_data[$iot_key]['mrf_name'] 						= !empty($MRF_NAME)?$MRF_NAME:$iot_value['department_name'];
				$array_data[$iot_key]['mrf_id'] 						= $iot_value['mrf_id'];
				$array_data[$iot_key]['yesterday_run_hours'] 			= $run_hours;
				$array_data[$iot_key]['yesterday_power_consumption'] 	= $power_cons;
				$array_data[$iot_key]['production_report'] 				= $production_qty;
				$array_data[$iot_key]['start_date'] 					= $StartTime;
				$array_data[$iot_key]['end_date'] 						= $EndTime;
			}
		}
		return $array_data;
	}

	public static function GetTotalizerData($MRF_ID=0,$SLAVE_ID=0,$DEVICE_CODE=0,$ReportDate,$ParameterUOM,$StartDate="",$EndDate="",$OnOff=false)
	{
		$READING_DATE_ARR 	= array();
		$DEVICE_READING 	= array();
		$arrReturn 			= array();
		$ReadingTable 		= (new self)->getTable();
		$IOTEquipments 		= (new IOTEquipments)->getTable();
		if (!empty($ReportDate)) {
			$StartTime 			= date("Y-m-d",strtotime($ReportDate))." ".GLOBAL_START_TIME;
			$EndTime 			= date("Y-m-d",strtotime($ReportDate))." ".GLOBAL_END_TIME;
		} else if (!empty($StartDate) && !empty($EndDate)) {
			$StartTime 			= date("Y-m-d",strtotime($StartDate))." ".GLOBAL_START_TIME;
			$EndTime 			= date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME;
		} else {
			$StartDate 			= date("Y-m-d");
			$StartTime 			= date("Y-m-d",strtotime($StartDate))." ".GLOBAL_START_TIME;
			$EndTime 			= date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME;
		}
		
		$TimeDiff 	= strtotime($EndTime)-strtotime($StartTime);
		$DayDiff 	= round($TimeDiff / (60 * 60 * 24));
		if ($DayDiff <= 1) {
			if ($OnOff) {
				$arrReadingData 	= self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%H:%i') AS ReadingTime"),
													DB::raw("AVG(reading) AS AMP_USED"))
											->whereBetween("reading_datetime",[$StartTime,$EndTime])
											->where("mrf_id",$MRF_ID)
											->where("slave_id",$SLAVE_ID)
											->where("device_code",$DEVICE_CODE);
			} else {
				$arrReadingData 	= self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%H:%i') AS ReadingTime"),
													DB::raw("AVG(reading) AS AMP_USED"))
											->whereBetween("reading_datetime",[$StartTime,$EndTime])
											->where("mrf_id",$MRF_ID)
											->where("slave_id",$SLAVE_ID)
											->where("device_code",$DEVICE_CODE);
			}
				$DeviceReadings = $arrReadingData->groupBy("ReadingTime")->orderby("ReadingTime","ASC")->get()->toArray();
		} else {
			if ($OnOff) {
				$arrReadingData 	= self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%b-%d %H') AS ReadingTime"),
													DB::raw("AVG(reading) AS AMP_USED"))
											->whereBetween("reading_datetime",[$StartTime,$EndTime])
											->where("mrf_id",$MRF_ID)
											->where("slave_id",$SLAVE_ID)
											->where("device_code",$DEVICE_CODE)
											->groupBy("ReadingTime");
			} else {
				$arrReadingData 	= self::select(	DB::raw("DATE_FORMAT(reading_datetime,'%b-%d %H') AS ReadingTime"),
													DB::raw("AVG(reading) AS AMP_USED"))
											->whereBetween("reading_datetime",[$StartTime,$EndTime])
											->where("mrf_id",$MRF_ID)
											->where("slave_id",$SLAVE_ID)
											->where("device_code",$DEVICE_CODE)
											->groupBy("ReadingTime");
			}
			$DeviceReadings = $arrReadingData->orderby("ReadingTime","ASC")->get()->toArray();
		}
		if (!empty($DeviceReadings))
		{
			foreach ($DeviceReadings as $RowID=>$DeviceReading) {
				$arrReturn[] 				= array($DeviceReading['ReadingTime'],floatval($DeviceReading['AMP_USED']));
				$READING_DATE_ARR[$RowID] 	= $DeviceReading['ReadingTime'];
				$DEVICE_READING[$RowID] 	= intval($DeviceReading['AMP_USED']);
				if($SLAVE_ID == 72){
					$DEVICE_READING[$RowID] = (intval($DeviceReading['AMP_USED']) > 0) ? _FormatNumberV2(intval($DeviceReading['AMP_USED']) / "7.2") : intval($DeviceReading['AMP_USED']);
				}else{
					$DEVICE_READING[$RowID] 	= intval($DeviceReading['AMP_USED']);
				}
			}
		}
		$arrResult['ChartData']['reading_time_date'] 	= $READING_DATE_ARR;
		$arrResult['ChartData']['amp_use_data'] 		= $DEVICE_READING;
		
		return $arrResult;
	}

	public static function feedRateofTotalizerData($MRF_ID=0,$SLAVE_ID=0,$DEVICE_CODE=0,$ReportDate)
	{
		$sum 				= 0;
		$i 					= 0;
		$ReadingTable 		= (new self)->getTable();
		$IOTEquipments 		= (new IOTEquipments)->getTable();
		$result 			= array();
		if (!empty($ReportDate)) {
			$StartTime 			= date("Y-m-d",strtotime($ReportDate))." ".GLOBAL_START_TIME;
			$EndTime 			= date("Y-m-d",strtotime($ReportDate))." ".GLOBAL_END_TIME; // AS discuss with jaydeep sir make time 22:59:00 - 21/06/2023
			$StartTime 			= date("Y-m-d",strtotime($ReportDate))." 00:30:00";
		}
		$SQL 	= '	SELECT reading as AMP_USED,
					DATE_ADD("'.$StartTime.'" ,Interval CEILING(TIMESTAMPDIFF(MINUTE,"'.$StartTime.'" , reading_datetime) / 30) * 30 minute) AS ReadingTime
					FROM wm_iot_equipment_readings
					WHERE  reading_datetime between "'.$StartTime.'" and "'.$EndTime.'"
					AND device_code = "'.$DEVICE_CODE.'"
					AND slave_id = "'.$SLAVE_ID.'"
					AND mrf_id = "'.$MRF_ID.'"
					GROUP BY ReadingTime
					ORDER BY ReadingTime';
		$arrReadingData = DB::select($SQL);
		if(!empty($arrReadingData))
		{
			foreach($arrReadingData as $key => $value){
				if($i == 0){
					$AMP_USED 		= ($value->AMP_USED) ? _FormatNumberV2($value->AMP_USED)  : 0;
					$prev_reading 	= $AMP_USED;
				}else{
					$AMP_USED 		= ($value->AMP_USED) ? _FormatNumberV2($value->AMP_USED) - $prev_reading: 0;
					$prev_reading 	= ($value->AMP_USED) ? _FormatNumberV2($value->AMP_USED) : 0;
				}
				if($SLAVE_ID == 72){
					$arrReadingData[$key]->AMP_USED 	= (!empty($AMP_USED)) ?  round(_FormatNumberV2(($AMP_USED / 1000)))  : 0;
					$arrReadingData[$key]->AMP_USED 	= ($arrReadingData[$key]->AMP_USED > 0) ? _FormatNumberV2($arrReadingData[$key]->AMP_USED / "7.2") : $arrReadingData[$key]->AMP_USED;
				}else{
					$arrReadingData[$key]->AMP_USED 	= (!empty($AMP_USED)) ?  round(_FormatNumberV2(($AMP_USED / 1000)))  : 0;
				}
				$arrReadingData[$key]->ReadingTime 	= date("H:i",strtotime($value->ReadingTime));
				$sum += (!empty($AMP_USED)) ?  round(_FormatNumberV2(($AMP_USED / 1000)))  : 0;
				$i++;
			}
		}
		$result['graph'] 	=  $arrReadingData;
		$result['total_mt'] =  ($sum > 0 && $SLAVE_ID == 72) ? _FormatNumberV2($sum / "7.2",1) : $sum;
		return $result;
	}

	public static function feedRateofTotalizerDataV1($MRF_ID=0,$SLAVE_ID=0,$DEVICE_CODE=0,$StartDate="",$EndDate="",$OnOff=false)
	{
		$sum 				= 0;
		$result 			= array();
		$READING_DATE_ARR 	= array();
		$DEVICE_READING 	= array();
		$arrReturn 			= array();
		$ReadingTable 		= (new self)->getTable();
		$IOTEquipments 		= (new IOTEquipments)->getTable();
		$StartTime 			= "";
		$EndTime 			= "";
		if (!empty($StartDate) && !empty($EndDate)) {
			$StartTime 	= date("Y-m-d",strtotime($StartDate))." ".GLOBAL_START_TIME;
			$EndTime 	= date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME;
		} else if (!empty($StartDate)) {
			$StartTime 	= date("Y-m-d",strtotime($StartDate))." ".GLOBAL_START_TIME;
			$EndTime 	= date("Y-m-d",strtotime($StartDate))." ".GLOBAL_END_TIME;
		} else if(!empty($EndDate)) {
			$StartDate 	= date("Y-m-d");
			$StartTime 	= date("Y-m-d",strtotime($EndDate))." ".GLOBAL_START_TIME;
			$EndTime 	= date("Y-m-d",strtotime($EndDate))." ".GLOBAL_END_TIME;
		}
		
		$TimeDiff 	= strtotime($EndTime)-strtotime($StartTime);
		$DayDiff 	= round($TimeDiff / (60 * 60 * 24));
		if ($DayDiff > 1) {
			$arrReadingData = self::select(	DB::raw("reading_datetime AS ReadingDateTime"),
											DB::raw("DATE_FORMAT(reading_datetime, '%Y-%m-%d') AS ReadingTime"),
											DB::raw("round(reading) AS AMP_USED"))
									->whereBetween("reading_datetime",[$StartTime,$EndTime])
									->where("reading_datetime","like","%23:55:%")
									->where("mrf_id",$MRF_ID)
									->where("slave_id",$SLAVE_ID)
									->where("device_code",$DEVICE_CODE)
									->groupBy("ReadingTime");
			$DeviceReadings = $arrReadingData->orderby("ReadingDateTime","ASC")->get();
			if (!empty($DeviceReadings))
			{
				foreach ($DeviceReadings as $RowID=>$DeviceReading) {
					$DeviceReadings[$RowID]->AMP_USED 		= (!empty($DeviceReading->AMP_USED)) ?  round(_FormatNumberV2(($DeviceReading->AMP_USED / 1000)))  : 0;
					$sum += $DeviceReadings[$RowID]->AMP_USED;
					if($SLAVE_ID == 72){
						$DeviceReadings[$RowID]->AMP_USED 		= ($DeviceReadings[$RowID]->AMP_USED > 0) ? _FormatNumberV2($DeviceReadings[$RowID]->AMP_USED / "7.2") : $DeviceReadings[$RowID]->AMP_USED;
					}
				}
			}
			$result['graph'] 	=  $DeviceReadings;
			$result['total_mt'] =  ($sum > 0 && $SLAVE_ID == 72) ? _FormatNumberV2($sum / "7.2") : $sum;
		} else {
			$result = self::feedRateofTotalizerData($MRF_ID,$SLAVE_ID,$DEVICE_CODE,$StartDate);
		}
		return $result;
	}

	public static function PlantOprationChartDataALre($request)
	{
		$DaysDiff 		= "";
		$MRF_ID 		= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
		$START_DATE 	= (isset($request['start_date']) && !empty($request['start_date'])) ? $request['start_date'] : date("Y-m-d");
		$END_DATE 		= (isset($request['end_date']) && !empty($request['end_date'])) ? $request['end_date'] : date("Y-m-d");
		$GRAPH_CODE 	= (isset($request['graph_code']) && !empty($request['graph_code'])) ? $request['graph_code'] : "";
		$StartTime 		= date("Y-m-d",strtotime($START_DATE))." ".GLOBAL_START_TIME;
		$EndTime 		= date("Y-m-d",strtotime($END_DATE))." ".GLOBAL_END_TIME;
		if (!empty($START_DATE) && !empty($END_DATE)) {
			$StartTime 	= date("Y-m-d",strtotime($START_DATE))." ".GLOBAL_START_TIME;
			$EndTime 	= date("Y-m-d",strtotime($END_DATE))." ".GLOBAL_END_TIME;
		} else if (!empty($START_DATE)) {
			$StartTime 	= date("Y-m-d",strtotime($START_DATE))." ".GLOBAL_START_TIME;
			$EndTime 	= date("Y-m-d",strtotime($START_DATE))." ".GLOBAL_END_TIME;
		} else if(!empty($END_DATE)) {
			$END_DATE 	= date("Y-m-d");
			$StartTime 	= date("Y-m-d",strtotime($END_DATE))." ".GLOBAL_START_TIME;
			$EndTime 	= date("Y-m-d",strtotime($END_DATE))." ".GLOBAL_END_TIME;
		}
		$TimeDiff 		= strtotime($EndTime)-strtotime($StartTime);
		$DaysDiff 		= round($TimeDiff / (60 * 60 * 24));
		$GRAPH_DATA 	= DB::table("plant_opration_chart_master")
							->join("plant_opration_chart_master_details","plant_opration_chart_master.id","=","plant_opration_chart_master_details.chart_master_id")
							->where("plant_opration_chart_master_details.mrf_id",$MRF_ID)
							->get()
							->toArray();
		$result 		= array();
		$data 			= array();		
		if(!empty($GRAPH_DATA))
		{
			foreach($GRAPH_DATA AS $KEY => $VALUE)
			{
				$IS_AFR 						= $VALUE->afr;
				$GRAPH_DATA[$KEY]->graph 		= array();
				$GRAPH_DATA[$KEY]->graph['afr'] = array();
				$GRAPH_DATA[$KEY]->graph['mrf'] = array();
				$AFR 							= array();
				$MRF 							= array();
				$MIN_VAL = $VALUE->min_value;
				if($DaysDiff <= 1) {
					$SQL = 'SELECT reading as AMP_USED,
							DATE_ADD("'.$StartTime.'" ,Interval CEILING(TIMESTAMPDIFF(MINUTE,"'.$StartTime.'" , reading_datetime) / 30) * 30 minute) AS ReadingTime
							FROM wm_iot_equipment_readings
							WHERE  reading_datetime between ("'.$StartTime.'" and "'.$EndTime.'")
							AND device_code = "'.$VALUE->device_code.'" 
							AND slave_id = "'.$VALUE->slave_id.'" 
							AND mrf_id = "'.$VALUE->mrf_id.'"
							GROUP BY ReadingTime
							ORDER BY ReadingTime';
					$result = DB::select($SQL);
				}else{
					$SQL = 'SELECT last_reading as AMP_USED,
							report_date AS ReadingTime
							FROM wm_iot_device_last_reading
							WHERE  report_date between "'.$START_DATE.'" and "'.$END_DATE.'"
							AND device_code = "'.$VALUE->device_code.'" 
							AND slave_id = "'.$VALUE->slave_id.'" 
							AND mrf_id = "'.$VALUE->mrf_id.'"
							GROUP BY ReadingTime
							ORDER BY ReadingTime DESC';
					$result = DB::select($SQL);
				}
				if(!empty($result))
				{
					foreach($result as $key => $value) {
						$result[$key]->ACTUAL_AMP_USED 	= $MIN_VAL; 
						$result[$key]->color 			= ($MIN_VAL > 0 && $MIN_VAL < $value->AMP_USED) ? "red" : "green"; 
					}
				}
				if($IS_AFR) {
					$data[$VALUE->id]['afr'] = $result;
				}else{
					$data[$VALUE->id]['mrf'] = $result;
				}
			}
		}
		return $data;
	}

	public static function GetTotlizerDataByDateTime($DEVICE_CODE,$SLAVE_ID,$MRF_ID,$StartTime,$EndTime)
	{
		$sum 				= 0;
		$i 					= 0;
		$ReadingTable 		= (new self)->getTable();
		$IOTEquipments 		= (new IOTEquipments)->getTable();
		$result 			= array();
		$TimeDiff 			= strtotime($EndTime)-strtotime($StartTime);
		$DayDiff 			= round($TimeDiff / (60 * 60 * 24));
		if ($DayDiff > 1)
		{
			$arrReadingData = IOTDeviceLastReading::select(	DB::raw("report_date AS ReadingTime"),DB::raw("round(last_reading) AS AMP_USED"))
								->whereBetween("report_date",[$StartTime,$EndTime])
								->where("mrf_id",$MRF_ID)
								->where("slave_id",$SLAVE_ID)
								->where("device_code",$DEVICE_CODE)
								->groupBy("ReadingTime");

			$DeviceReadings = $arrReadingData->orderby("ReadingTime","ASC")->get();
			if (!empty($DeviceReadings))
			{
				foreach ($DeviceReadings as $RowID=>$DeviceReading) {
					$DeviceReadings[$RowID]->AMP_USED 		= (!empty($DeviceReading->AMP_USED)) ?  round(_FormatNumberV2(($DeviceReading->AMP_USED / 1000)))  : 0;
					$sum += $DeviceReadings[$RowID]->AMP_USED;
					if($SLAVE_ID == 72) {
						$DeviceReadings[$RowID]->AMP_USED 	= ($DeviceReadings[$RowID]->AMP_USED > 0) ? _FormatNumberV2($DeviceReadings[$RowID]->AMP_USED / "7.2") : $DeviceReadings[$RowID]->AMP_USED;
					}
				}
			}
			$result['graph'] 	= $DeviceReadings;
			$result['total_mt'] = ($sum > 0 && $SLAVE_ID == 72) ? _FormatNumberV2($sum / "7.2") : $sum;
		} else {
			$SQL = "SELECT reading as AMP_USED,
					DATE_ADD('".$StartTime."',Interval CEILING(TIMESTAMPDIFF(MINUTE,'".$StartTime."',reading_datetime) / 30) * 30 minute) AS ReadingTime
					FROM wm_iot_equipment_readings
					WHERE  reading_datetime between '".$StartTime."' and '".$EndTime."'
					AND device_code = '".$DEVICE_CODE."'
					AND slave_id = '".$SLAVE_ID."'
					AND mrf_id = '".$MRF_ID."'
					GROUP BY ReadingTime
					ORDER BY ReadingTime";
			$arrReadingData = DB::select($SQL);
			if(!empty($arrReadingData))
			{
				foreach($arrReadingData as $key => $value)
				{
					if($i == 0) {
						$AMP_USED 		= ($value->AMP_USED) ? _FormatNumberV2($value->AMP_USED)  : 0;
						$prev_reading 	= $AMP_USED;
					} else {
						$AMP_USED 		= ($value->AMP_USED) ? _FormatNumberV2($value->AMP_USED) - $prev_reading: 0;
						$prev_reading 	= ($value->AMP_USED) ? _FormatNumberV2($value->AMP_USED) : 0;
					}
					if($SLAVE_ID == 72) {
						$arrReadingData[$key]->AMP_USED 	= (!empty($AMP_USED)) ?  round(_FormatNumberV2(($AMP_USED / 1000)))  : 0;
						$arrReadingData[$key]->AMP_USED 	= ($arrReadingData[$key]->AMP_USED > 0) ? _FormatNumberV2($arrReadingData[$key]->AMP_USED / "7.2") : $arrReadingData[$key]->AMP_USED;
					} else {
						$arrReadingData[$key]->AMP_USED 	= (!empty($AMP_USED)) ?  round(_FormatNumberV2(($AMP_USED / 1000)))  : 0;
					}
					$arrReadingData[$key]->ReadingTime 	= date("H:i",strtotime($value->ReadingTime));
					$sum += (!empty($AMP_USED)) ?  round(_FormatNumberV2(($AMP_USED / 1000)))  : 0;
					$i++;
				}
			}
			$result['graph'] 	=  $arrReadingData;
			$result['total_mt'] =  ($sum > 0 && $SLAVE_ID == 72) ? _FormatNumberV2($sum / "7.2",1) : $sum;
		}
	}

	public static function PlantOprationChartDataOld($request)
	{

		if(Auth()->user()->adminuserid == 1){
			return self::PlantOprationChartDataAxay($request);
		}
		$MRF_ID 		= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
		$START_DATE 	= (isset($request['start_date']) && !empty($request['start_date'])) ? $request['start_date'] : date("Y-m-d");
		$END_DATE 		= (isset($request['end_date']) && !empty($request['end_date'])) ? $request['end_date'] : date("Y-m-d");
		$SameDay 		= false;
		$Today 			= date("Y-m-d");
		$StartTime 		= date("Y-m-d",strtotime($START_DATE));
		$EndTime 		= date("Y-m-d",strtotime($END_DATE));
		$DaysDiff 		= strtotime($END_DATE) - strtotime($START_DATE);
		if ($DaysDiff > 0) {
			$DaysDiff = round($DaysDiff / (60 * 60 * 24));
		} else {
			$DaysDiff = 1;
		}
		if (strtotime(date("Y-m-d",strtotime($START_DATE))) == strtotime(date("Y-m-d",strtotime($END_DATE)))) {
			$StartTime 	= date('Y-m-d', strtotime($START_DATE .' -1 day'));
			$SameDay 	= true;
		}
		if (strtotime($EndTime) >= strtotime($Today)) {
			$EndTime = $Today;
		}
		$PlantDashboardDetails 	= DB::table("plant_opration_chart_master_details")
									->join("wm_department","wm_department.id","=","plant_opration_chart_master_details.mrf_id")
									->where("plant_opration_chart_master_details.status",1)
									->groupBy("plant_opration_chart_master_details.afr")
									->groupBy("plant_opration_chart_master_details.mrf_id")
									->get([	"plant_opration_chart_master_details.mrf_id","plant_opration_chart_master_details.id",
											"plant_opration_chart_master_details.afr","plant_opration_chart_master_details.chart_master_id",
											"wm_department.department_name"]);
		$PlantDashboardResult	= $PlantDashboardDetails->toArray();
		$result 				= array();
		if(!empty($PlantDashboardResult))
		{
			foreach($PlantDashboardResult as $key => $value)
			{
				$MRF_ID 													= $value->mrf_id;
				\Log::info($MRF_ID);
				$AFR 														= $value->afr;
				$PlantDashboardResult[$key]->department_name 				= ($AFR == 1)?str_replace("MRF","AFR",$value->department_name):$value->department_name;
				$PlantDashboardResult[$key]->yesterday_run_hours 			= "";
				$PlantDashboardResult[$key]->yesterday_power_consumption 	= "";
				$PlantDashboardResult[$key]->production 					= "";
				$PlantDashboardResult[$key]->power_cost 					= "";
				$PlantDashboardResult[$key]->process_qty 					= "";
				$PlantDashboardResult[$key]->feed_rate 						= "";
				$PlantDashboardResult[$key]->color 							= "";

				$result 	= DB::table("plant_opration_chart_master")
								->leftjoin("plant_opration_chart_master_details","plant_opration_chart_master.id","=","plant_opration_chart_master_details.chart_master_id")
								->where("plant_opration_chart_master_details.status",1)
								->where("plant_opration_chart_master_details.mrf_id",$MRF_ID)
								->where("plant_opration_chart_master_details.afr",$AFR)
								->get()
								->toArray();
				if(!empty($result))
				{
					foreach($result as $rk => $rv)
					{
						$GRAPH_CODE 							= "";
						$PRODUCTION_QTY 						= 0;
						$GRAPH_CODE 							= $rv->code;
						$DEVICE_CODE 							= $rv->device_code;
						$SLAVE_ID 								= $rv->slave_id;
						$MIN_VAL 								= $rv->min_value;
						$AVG 									= 0;
						$PRODUCTION 							= WmProductionReportMaster::GetTotalProductionReportByDate($MRF_ID,$START_DATE,$END_DATE,$AFR);
						$chart_id 								= DB::table("plant_opration_chart_master")->where(array("code"=>"PRODUCTION"))->value("id");
						$production_chart_id 					= DB::table("plant_opration_chart_master_details")->where(array("chart_master_id"=>$chart_id,"afr"=>$AFR))->value("id");
						$production_qty 						= ($PRODUCTION > 0) ? _FormatNumberV2($PRODUCTION / 1000) : 0;
						$production_qty 						= "<a data-id='".$production_chart_id."'>".$production_qty."</a>";
						$PlantDashboardResult[$key]->production = $production_qty;
						
						if($GRAPH_CODE == "YRH")
						{
							
							$DeviceReadings = IOTEquipments::select("wm_iot_equipment_master.title",
																	DB::raw("GetiOTDeviceReading($SLAVE_ID,$DEVICE_CODE,'$StartTime','$EndTime',0,".$MRF_ID.") AS READING"))
												->where("wm_iot_equipment_master.mrf_id",$MRF_ID)
												->where("wm_iot_equipment_master.slave_id",$SLAVE_ID)
												->first();
								$var 												= "<a data-id='".$rv->id."'></a>";
								$run_hours 											= (isset($DeviceReadings->READING)) ? $DeviceReadings->READING : 0;
								$run_hours 											= "<a data-id='".$rv->id."'>".$run_hours."</a>";
								$PlantDashboardResult[$key]->yesterday_run_hours 	= _FormatNumberV2($run_hours);
						}

						if($GRAPH_CODE == "YPC")
						{
							$prev_power_cons 	= IOTDeviceLastReading::select("last_reading")
													->where("report_date",$StartTime)
													->where("slave_id",$SLAVE_ID)
													->where("device_code",$DEVICE_CODE)
													->where("mrf_id",$MRF_ID)
													->first();
							$ReadingDate = $START_DATE;
							if (!$SameDay) {
								$ReadingDate = $EndTime;
							}
							$power_cons = IOTDeviceLastReading::select("last_reading")
											->where("report_date",$ReadingDate)
											->where("slave_id",$SLAVE_ID)
											->where("device_code",$DEVICE_CODE)
											->where("mrf_id",$MRF_ID)
											->first();
							$prev_power_cons_reading									= (isset($prev_power_cons->last_reading)) ? $prev_power_cons->last_reading : 0;
							$power_cons_reading 										= (isset($power_cons->last_reading)) ? $power_cons->last_reading : 0;
							$date_power_cons											= round(($power_cons_reading - $prev_power_cons_reading),2);
							
							$PlantDashboardResult[$key]->yesterday_power_consumption 	= "<a data-id='".$rv->id."'>".$date_power_cons."</a>";
						}

						if($GRAPH_CODE == "PC")
						{
							// \Log::info($MIN_VAL." PRODUCTION ".$PRODUCTION." ".$MRF_ID);
							$PlantDashboardResult[$key]->power_cost = ($MIN_VAL > 0 && $PRODUCTION > 0) ? _FormatNumberV2(($MIN_VAL * 9) / $PRODUCTION) : 0;
							$PlantDashboardResult[$key]->power_cost = ($MIN_VAL > 0 && $PRODUCTION > 0)  ? "<a data-id='".$rv->id."'>"._FormatNumberV2(($MIN_VAL * 9) / $PRODUCTION)."</a>" : "<a  data-id='".$rv->id."'>0</a>";
						}

						if($GRAPH_CODE == "PQ")
						{
							$s_time 									= ($SLAVE_ID == 73) ? "07:00:00" : "00:15:00";
							$e_time 									= ($SLAVE_ID == 73) ? "18:00:00" : "23:50:00";
							$t_data 									= self::GetCustomTotlizerData($MRF_ID,$SLAVE_ID,$DEVICE_CODE,$START_DATE,$END_DATE,$s_time,$e_time);
							$PlantDashboardResult[$key]->process_qty 	= (isset($t_data['total_mt']) && !empty($t_data['total_mt']))?$t_data['total_mt']:0;
						}

						if($GRAPH_CODE == "FR")
						{
							$s_time 								= ($SLAVE_ID == 73) ? "07:00:00" : "00:15:00";
							$e_time 								= ($SLAVE_ID == 73) ? "18:00:00" : "23:50:00";
							$DeviceReadings 						= IOTDeviceLastReading::whereBetween("report_date",[$START_DATE,$END_DATE])
																		->where("mrf_id",$MRF_ID)
																		->where("slave_id",$SLAVE_ID)
																		->where("device_code",$DEVICE_CODE)
																		->groupBy("slave_id")
																		->avg("last_reading");
							$PlantDashboardResult[$key]->feed_rate = round($DeviceReadings);
						}
					}
				}
			}
			return $PlantDashboardResult;
		}
	}


	public static function PlantOprationChartData($request)
	{
		$MRF_ID 		= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
		$START_DATE 	= (isset($request['start_date']) && !empty($request['start_date'])) ? $request['start_date'] : date("Y-m-d");
		$END_DATE 		= (isset($request['end_date']) && !empty($request['end_date'])) ? $request['end_date'] : date("Y-m-d");
		$SameDay 		= false;
		$Today 			= date("Y-m-d");
		$StartTime 		= date("Y-m-d",strtotime($START_DATE));
		$EndTime 		= date("Y-m-d",strtotime($END_DATE));
		$DaysDiff 		= strtotime($END_DATE) - strtotime($START_DATE);
		if ($DaysDiff > 0) {
			$DaysDiff = round($DaysDiff / (60 * 60 * 24));
		} else {
			$DaysDiff = 1;
		}
		if (strtotime(date("Y-m-d",strtotime($START_DATE))) == strtotime(date("Y-m-d",strtotime($END_DATE)))) {
			$StartTime 	= date('Y-m-d', strtotime($START_DATE .' -1 day'));
			$SameDay 	= true;
		}
		if (strtotime($EndTime) >= strtotime($Today)) {
			$EndTime = $Today;
		}
		$PlantDashboardDetails 	= DB::table("plant_opration_chart_master_details")
									->join("wm_department","wm_department.id","=","plant_opration_chart_master_details.mrf_id")
									->where("plant_opration_chart_master_details.status",1)
									->groupBy("plant_opration_chart_master_details.afr")
									->groupBy("plant_opration_chart_master_details.mrf_id")
									->get([	"plant_opration_chart_master_details.mrf_id","plant_opration_chart_master_details.id",
											"plant_opration_chart_master_details.afr","plant_opration_chart_master_details.chart_master_id",
											"wm_department.department_name"]);
		$PlantDashboardResult	= $PlantDashboardDetails->toArray();
		$result 				= array();
		if(!empty($PlantDashboardResult))
		{
			foreach($PlantDashboardResult as $key => $value)
			{
				$MRF_ID 													= $value->mrf_id;
				\Log::info($MRF_ID);
				$AFR 														= $value->afr;
				$PlantDashboardResult[$key]->department_name 				= ($AFR == 1)?str_replace("MRF","AFR",$value->department_name):$value->department_name;
				$PlantDashboardResult[$key]->yesterday_run_hours 			= "";
				$PlantDashboardResult[$key]->yesterday_power_consumption 	= "";
				$PlantDashboardResult[$key]->production 					= "";
				$PlantDashboardResult[$key]->power_cost 					= "";
				$PlantDashboardResult[$key]->process_qty 					= "";
				$PlantDashboardResult[$key]->feed_rate 						= "";
				$PlantDashboardResult[$key]->color 							= "";

				$result 	= DB::table("plant_opration_chart_master")
								->leftjoin("plant_opration_chart_master_details","plant_opration_chart_master.id","=","plant_opration_chart_master_details.chart_master_id")
								->where("plant_opration_chart_master_details.status",1)
								->where("plant_opration_chart_master_details.mrf_id",$MRF_ID)
								->where("plant_opration_chart_master_details.afr",$AFR)
								->get()
								->toArray();
				if(!empty($result))
				{
					foreach($result as $rk => $rv)
					{
						$GRAPH_CODE 							= "";
						$PRODUCTION_QTY 						= 0;
						$GRAPH_CODE 							= $rv->code;
						$DEVICE_CODE 							= $rv->device_code;
						$SLAVE_ID 								= $rv->slave_id;
						$MIN_VAL 								= $rv->min_value;
						$AVG 									= 0;
						$PRODUCTION 							= WmProductionReportMaster::GetTotalProductionReportByDate($MRF_ID,$START_DATE,$END_DATE,$AFR);
						$chart_id 								= DB::table("plant_opration_chart_master")->where(array("code"=>"PRODUCTION"))->value("id");
						$production_chart_id 					= DB::table("plant_opration_chart_master_details")->where(array("chart_master_id"=>$chart_id,"afr"=>$AFR))->value("id");
						$production_qty 						= ($PRODUCTION > 0) ? _FormatNumberV2($PRODUCTION / 1000) : 0;
						$production_qty 						= "<a data-id='".$production_chart_id."'>".$production_qty."</a>";
						$PlantDashboardResult[$key]->production = $production_qty;
						
						if($GRAPH_CODE == "YRH")
						{
							
							$DeviceReadings = IOTEquipments::select("wm_iot_equipment_master.title",
																	DB::raw("GetiOTDeviceReading($SLAVE_ID,$DEVICE_CODE,'$StartTime','$EndTime',0,".$MRF_ID.") AS READING"))
												->where("wm_iot_equipment_master.mrf_id",$MRF_ID)
												->where("wm_iot_equipment_master.slave_id",$SLAVE_ID)
												->first();
								$var 												= "<a data-id='".$rv->id."'></a>";
								$run_hours 											= (isset($DeviceReadings->READING)) ? $DeviceReadings->READING : 0;
								$run_hours 											= "<a data-id='".$rv->id."'>".$run_hours."</a>";
								$PlantDashboardResult[$key]->yesterday_run_hours 	= _FormatNumberV2($run_hours);
						}

						if($GRAPH_CODE == "YPC")
						{
							$prev_power_cons 	= IOTDeviceLastReading::select("last_reading")
													->where("report_date",$StartTime)
													->where("slave_id",$SLAVE_ID)
													->where("device_code",$DEVICE_CODE)
													->where("mrf_id",$MRF_ID)
													->first();
							$ReadingDate = $START_DATE;
							if (!$SameDay) {
								$ReadingDate = $EndTime;
							}
							$power_cons = IOTDeviceLastReading::select("last_reading")
											->where("report_date",$ReadingDate)
											->where("slave_id",$SLAVE_ID)
											->where("device_code",$DEVICE_CODE)
											->where("mrf_id",$MRF_ID)
											->first();
							$prev_power_cons_reading									= (isset($prev_power_cons->last_reading)) ? $prev_power_cons->last_reading : 0;
							$power_cons_reading 										= (isset($power_cons->last_reading)) ? $power_cons->last_reading : 0;
							$date_power_cons											= round(($power_cons_reading - $prev_power_cons_reading),2);
							\Log::info("Privious power ".$prev_power_cons_reading." power cons".$power_cons_reading. " difference ".$date_power_cons);
							$PlantDashboardResult[$key]->yesterday_power_consumption 	= "<a data-id='".$rv->id."'>".$date_power_cons."</a>";
						}

						if($GRAPH_CODE == "PC")
						{
							
							$PlantDashboardResult[$key]->power_cost = ($MIN_VAL > 0 && $PRODUCTION > 0) ? _FormatNumberV2(($MIN_VAL * 9) / $PRODUCTION) : 0;
							$PlantDashboardResult[$key]->power_cost = ($MIN_VAL > 0 && $PRODUCTION > 0)  ? "<a data-id='".$rv->id."'>"._FormatNumberV2(($MIN_VAL * 9) / $PRODUCTION)."</a>" : "<a  data-id='".$rv->id."'>0</a>";
						}

						if($GRAPH_CODE == "PQ")
						{
							$s_time 									= ($SLAVE_ID == 73) ? "07:00:00" : "00:15:00";
							$e_time 									= ($SLAVE_ID == 73) ? "18:00:00" : "23:50:00";
							$t_data 									= self::GetCustomTotlizerData($MRF_ID,$SLAVE_ID,$DEVICE_CODE,$START_DATE,$END_DATE,$s_time,$e_time);
							
							$PlantDashboardResult[$key]->process_qty 	= (isset($t_data['total_mt']) && !empty($t_data['total_mt']))?$t_data['total_mt']:0;
						}

						if($GRAPH_CODE == "FR")
						{
							$s_time 								= ($SLAVE_ID == 73) ? "07:00:00" : "00:15:00";
							$e_time 								= ($SLAVE_ID == 73) ? "18:00:00" : "23:50:00";
							$TotalHours 							= ($SLAVE_ID == 73) ? 11 : 24;
							$TotalFeedRateHours 					= $TotalHours * $DaysDiff;
							$DeviceReadings 						= IOTDeviceLastReading::whereBetween("report_date",[$START_DATE,$END_DATE])
																		->where("mrf_id",$MRF_ID)
																		->where("slave_id",$SLAVE_ID)
																		->where("device_code",$DEVICE_CODE)
																		->groupBy("slave_id")
																		->avg("last_reading");
							// $DeviceReadings 						= !empty($DeviceReadings)?round(($DeviceReadings / $DaysDiff)/1000):0;
							$PlantDashboardResult[$key]->feed_rate 	= $DeviceReadings;
						}
					}
				}
			}
			return $PlantDashboardResult;
		}
	}


	public static function GetCustomTotlizerData($MRF_ID,$SLAVE_ID,$DEVICE_CODE,$START_DATE,$END_DATE,$S_TIME,$E_TIME)
	{
		$i 					= 0;
		$sum 				= 0;
		$result 			= array();
		$READING_DATE_ARR 	= array();
		$DEVICE_READING 	= array();
		$arrReturn 			= array();
		$ReadingTable 		= (new self)->getTable();
		$IOTEquipments 		= (new IOTEquipments)->getTable();
		$S_TIME 			= (!empty($S_TIME)) ? $S_TIME : GLOBAL_START_TIME;
		$E_TIME 			= (!empty($E_TIME)) ? $E_TIME : GLOBAL_END_TIME;
		$StartTime 			= "";
		$EndTime 			= "";
		if (!empty($START_DATE) && !empty($END_DATE)) {
			$StartTime 	= date("Y-m-d",strtotime($START_DATE))." ".$S_TIME;
			$EndTime 	= date("Y-m-d",strtotime($END_DATE))." ".$E_TIME;
		} else if (!empty($START_DATE)) {
			$StartTime 	= date("Y-m-d",strtotime($START_DATE))." ".$S_TIME;
			$EndTime 	= date("Y-m-d",strtotime($START_DATE))." ".$E_TIME;
		} else if(!empty($END_DATE)) {
			$START_DATE 	= date("Y-m-d");
			$StartTime 	= date("Y-m-d",strtotime($END_DATE))." ".$S_TIME;
			$EndTime 	= date("Y-m-d",strtotime($END_DATE))." ".$E_TIME;
		}
		$TimeDiff 	= strtotime($EndTime)-strtotime($StartTime);
		$DayDiff 	= round($TimeDiff / (60 * 60 * 24));
		if ($DayDiff > 1) {
			$SQL 			= '	SELECT reading as AMP_USED,
								DATE_ADD("'.$StartTime.'" ,Interval CEILING(TIMESTAMPDIFF(MINUTE,"'.$StartTime.'" , reading_datetime) / 60) * 60 minute) AS ReadingTime
								FROM wm_iot_equipment_readings
								WHERE  reading_datetime between "'.$StartTime.'" and "'.$EndTime.'"
								AND (hour(reading_datetime) >= 07 and hour(reading_datetime) < 18)
								AND device_code = "'.$DEVICE_CODE.'"
								AND slave_id = "'.$SLAVE_ID.'"
								AND mrf_id = "'.$MRF_ID.'"
								GROUP BY ReadingTime
								ORDER BY ReadingTime';
								// \Log::info($SQL);
			$arrReadingData = DB::select($SQL);
		} else {

			$SQL 			= '	SELECT reading as AMP_USED,
								DATE_ADD("'.$StartTime.'" ,Interval CEILING(TIMESTAMPDIFF(MINUTE,"'.$StartTime.'" , reading_datetime) / 30) * 30 minute) AS ReadingTime
								FROM wm_iot_equipment_readings
								WHERE  reading_datetime between "'.$StartTime.'" and "'.$EndTime.'"
								AND device_code = "'.$DEVICE_CODE.'"
								AND slave_id = "'.$SLAVE_ID.'"
								AND mrf_id = "'.$MRF_ID.'"
								GROUP BY ReadingTime
								ORDER BY ReadingTime';
								// \Log::info($SQL);
			$arrReadingData = DB::select($SQL);
			

		}
		if(!empty($arrReadingData))
		{
			foreach($arrReadingData as $key => $value)
			{
				if($i == 0){
					$AMP_USED 		= ($value->AMP_USED) ? _FormatNumberV2($value->AMP_USED)  : 0;
					$prev_reading 	= $AMP_USED;
				}else{
					$AMP_USED 		= ($value->AMP_USED) ? _FormatNumberV2($value->AMP_USED) - $prev_reading: 0;
					$prev_reading 	= ($value->AMP_USED) ? _FormatNumberV2($value->AMP_USED) : 0;
				}
				if($SLAVE_ID == 72){
					$arrReadingData[$key]->AMP_USED 	= (!empty($AMP_USED)) ?  round(_FormatNumberV2(($AMP_USED / 1000)))  : 0;
					$arrReadingData[$key]->AMP_USED 	= ($arrReadingData[$key]->AMP_USED > 0) ? _FormatNumberV2($arrReadingData[$key]->AMP_USED / "7.2") : $arrReadingData[$key]->AMP_USED;
				}else{
					$arrReadingData[$key]->AMP_USED 	= (!empty($AMP_USED)) ?  round(_FormatNumberV2(($AMP_USED / 1000)))  : 0;
				}
				$arrReadingData[$key]->ReadingTime 	= date("H:i",strtotime($value->ReadingTime));
				$sum += (!empty($AMP_USED)) ?  round(_FormatNumberV2(($AMP_USED / 1000)))  : 0;
				$i++;
			}
		}
		$result['graph'] 	= $arrReadingData;
		$result['total_mt'] = ($sum > 0 && $SLAVE_ID == 72) ? _FormatNumberV2($sum / "7.2",1) : $sum;
		return $result;
	}

	public static function GetChartDataIdWise($request)
	{
		$id 		= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$start_date = (isset($request->start_date) && !empty($request->start_date)) ? $request->start_date : date("Y-m-d");
		$end_date 	= (isset($request->end_date) && !empty($request->end_date)) ? $request->end_date : date("Y-m-d");
		$EndTime 	= "";
		$StartTime 	= "";
		$title 		= "";
		$value_in 	= "";
		$department_name 	= "";
		if (!empty($start_date) && !empty($end_date)) {
			$StartTime 	= date("Y-m-d",strtotime($start_date))." ".GLOBAL_START_TIME;
			$EndTime 	= date("Y-m-d",strtotime($end_date))." ".GLOBAL_END_TIME;
		} else if (!empty($start_date)) {
			$StartTime 	= date("Y-m-d",strtotime($start_date))." ".GLOBAL_START_TIME;
			$EndTime 	= date("Y-m-d",strtotime($start_date))." ".GLOBAL_END_TIME;
		} else if(!empty($end_date)) {
			$start_date 	= date("Y-m-d");
			$StartTime 	= date("Y-m-d",strtotime($end_date))." ".GLOBAL_START_TIME;
			$EndTime 	= date("Y-m-d",strtotime($end_date))." ".GLOBAL_END_TIME;
		}
		
		$TimeDiff 	= strtotime($EndTime)-strtotime($StartTime);
		$StartTime 	= date('Y-m-d H:i:s', strtotime($StartTime . ' -1 day'));
		$DaysDiff 	= round($TimeDiff / (60 * 60 * 24));
		if(!empty($id))
		{
			$data 	= DB::table("plant_opration_chart_master_details")
						->select("wm_department.department_name","plant_opration_chart_master_details.*","plant_opration_chart_master.code",
								"plant_opration_chart_master.chart_name","plant_opration_chart_master.value_in")
						->join("plant_opration_chart_master","plant_opration_chart_master.id","=","plant_opration_chart_master_details.chart_master_id")
						->join("wm_department","wm_department.id","=","plant_opration_chart_master_details.mrf_id")
						->where("plant_opration_chart_master_details.status",1)
						->where("plant_opration_chart_master_details.id",$id)
						->first();
			if($data)
			{
				$department_name 	= ($data->afr == 0) ? $data->department_name : "AFR-".$data->department_name;
				$slave_id 			= $data->slave_id;
				$mrf_id 			= $data->mrf_id;
				$device_code 		= $data->device_code;
				$min_value 			= $data->min_value;
				$afr 				= $data->afr;
				$result 			= array();
				$min_value_array 	= array();
				$actual_array 		= array();
				$reading_date 		= array();
				$priviousReading 	= 0;
				$i 					= 0;
				if($data->code == "PRODUCTION" || $data->code == "PC") {
					$PRODUCTION = WmProductionReportMaster::GetTotalProductionReportDateWise($mrf_id,$start_date,$end_date,$afr);
					if(!empty($PRODUCTION))
					{
						foreach($PRODUCTION AS $key => $value)
						{
							$actual_value 		= ($data->code == "PC") ? _FormatNumberV2($min_value * 9 / $value->FG_QTY) * 1000 : round($value->FG_QTY / 1000);
							$actual_array[]		= $actual_value;
							$reading_date[]		= date("Y-m-d",strtotime($value->production_date));
							$min_value_array[]	= $min_value;
						}
					}
				} else {
					$DeviceReadings = IOTDeviceLastReading::select(	DB::raw("report_date AS ReadingTime"),
																	DB::raw("round(last_reading) AS AMP_USED"))
										->whereBetween("report_date",[$StartTime,$EndTime])
										->where("mrf_id",$mrf_id)
										->where("slave_id",$slave_id)
										->where("device_code",$device_code)
										->groupBy("ReadingTime");
					$arrReadingData = $DeviceReadings->orderby("ReadingTime","ASC")->get();
					foreach($arrReadingData as $key => $value) {
						if($i == 0) {
							$priviousReading 	= $value->AMP_USED;
						} else {
							$actual_value 	 	= $value->AMP_USED - $priviousReading;
							$priviousReading 	= $value->AMP_USED;
							$actual_array[]		= $actual_value;
							$reading_date[]		= date("Y-m-d",strtotime($value->ReadingTime));
							$min_value_array[]	= $min_value;
						}
						$i++;
					}
				}
				$title		= $data->chart_name; 
				$value_in	= $data->value_in;
			}

			$result['department_name'] 	= $department_name; 
			$result['title'] 			= $title; 
			$result['y_value'] 			= $value_in; 
			$result['x_value'] 			= "Date"; 
			$result['actual_reading'] 	= $actual_array; 
			$result['plan_reading'] 	= $min_value_array;
			$result['reading_date'] 	= $reading_date;
			return $result;
		}
	} 

	public static function GetChartDataByDetails($mrf_id,$slave_id,$device_code,$StartTime,$EndTime)
	{
		$DeviceReadings = IOTDeviceLastReading::select(DB::raw("report_date AS ReadingTime"),DB::raw("round(last_reading) AS AMP_USED"))
							->whereBetween("report_date",[$StartTime,$EndTime])
							->where("mrf_id",$mrf_id)
							->where("slave_id",$slave_id)
							->where("device_code",$device_code)
							->groupBy("ReadingTime");
		$arrReadingData = $DeviceReadings->orderby("ReadingTime","ASC")->get();
		return $arrReadingData;
	}
}