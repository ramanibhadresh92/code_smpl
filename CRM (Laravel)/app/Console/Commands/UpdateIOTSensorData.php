<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IOTSensorData;
use App\Models\IOTEquipments;
use App\Models\IOTEquipmentParameters;
use App\Models\IOTEquipmentReading;
use App\Models\IOTEquipmentLastReading;
use App\Models\IOTDeviceLastReading;
use App\Facades\LiveServices;
use DB;
class UpdateIOTSensorData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateIOTSensorData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command use to Process IOT Sensor Data';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', '-1');
		set_time_limit(0);
		echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";

		$StartDate 	= "2022-06-06";
		$EndDate 	= "2022-06-10";

		$IOTEquipmentParameters = IOTEquipmentParameters::where("status",STATUS_ACTIVE)->get()->toArray();
		$DEFAULT_MRF_ID			= 11;
		$arrParameters 			= array();
		if (!empty($IOTEquipmentParameters)) {
			foreach($IOTEquipmentParameters as $IOTEquipmentParameter) {
				$arrParameters[$IOTEquipmentParameter['code']] 	= array("multiplication_factor"=>$IOTEquipmentParameter['multiplication_factor'],
																		"reading_duration"=>$IOTEquipmentParameter['reading_duration']);
			}
		}
		$SLAVEID 			= 12;
		$arrDeviceParams 	= array(400206,400214,400216,400218,400220,400232,400258,400260,400262,400264,400266,400268,400292,400296);
		while(strtotime($StartDate) <= strtotime($EndDate))
		{
			foreach ($arrDeviceParams as $DEVICECODE)
			{
				$IOTSensorData 	= IOTSensorData::whereIn("processed",[0,2])
								->whereBetween("created_at",[$StartDate." ".GLOBAL_START_TIME,$StartDate." ".GLOBAL_END_TIME])
								->where("row_data",'LIKE','%'.$DEVICECODE.'%')
								->where("row_data",'LIKE','%slaveID":"'.$SLAVEID.'"%')
								->orderBy("created_at","ASC")
								->limit(1)
								->get();
				if (!empty($IOTSensorData)) {
					foreach ($IOTSensorData as $IOTSensorRow) {
						$JSON_DATA = json_decode($IOTSensorRow->row_data);
						if (!empty($JSON_DATA)) {
							foreach ($JSON_DATA as $JSON_ROW) {
								$SLAVE_ID 			= $JSON_ROW->slaveID;
								$MRF_ID 			= isset($JSON_ROW->MRFID)?$JSON_ROW->MRFID:$DEFAULT_MRF_ID;
								$reading_datetime 	= $IOTSensorRow->created_at;
								$READING_DATE 		= date("Y-m-d",strtotime($reading_datetime));
								foreach ($JSON_ROW->data as $device_code => $reading) {
									if (!in_array($device_code,$arrDeviceParams)) continue;
									if (!in_array($SLAVE_ID,array($SLAVEID))) continue;
									if (isset($arrParameters[$device_code])) {
										$reading = (($reading > 0)?(($reading/$arrParameters[$device_code]['multiplication_factor'])):$reading); //division factor

										echo "DEVICECODE --> ".$DEVICECODE." -- reading_datetime --> ".$reading_datetime." -- reading --> ".$reading."\r\n";

										$DeviceLastRecordReading = IOTDeviceLastReading::select("id")
																	->where("slave_id",$SLAVE_ID)
																	->where("mrf_id",$MRF_ID)
																	->where("device_code",$device_code)
																	->where("report_date",$READING_DATE)
																	->first();
										if (!empty($DeviceLastRecordReading) && !empty($DeviceLastRecordReading->id)) {
											$DeviceLastRecordReading->last_reading = $reading;
											$DeviceLastRecordReading->save();
										} else {
											$IOTDeviceLastReading 				= new IOTDeviceLastReading;
											$IOTDeviceLastReading->mrf_id 		= $MRF_ID;
											$IOTDeviceLastReading->slave_id 	= $SLAVE_ID;
											$IOTDeviceLastReading->device_code 	= $device_code;
											$IOTDeviceLastReading->report_date 	= $READING_DATE;
											$IOTDeviceLastReading->last_reading = $reading;
											$IOTDeviceLastReading->save();
										}
										$IOTEquipmentReading 	= IOTEquipmentReading::select("id")
																	->where("slave_id",$SLAVE_ID)
																	->where("mrf_id",$MRF_ID)
																	->where("device_code",$device_code)
																	->orderBy("id","DESC")
																	->whereBetween("reading_datetime",[$READING_DATE." ".GLOBAL_START_TIME,$READING_DATE." ".GLOBAL_END_TIME])
																	->first();
										if (!empty($IOTEquipmentReading)) {
											$IOTEquipmentReading->reading = $reading;
											$IOTEquipmentReading->save();
										} else {
											$Fields['slave_id'] 		= $SLAVE_ID;
											$Fields['mrf_id'] 			= $MRF_ID;
											$Fields['device_code'] 		= $device_code;
											$Fields['reading'] 			= $reading;
											$Fields['reading_datetime'] = $reading_datetime;
											IOTEquipmentReading::AddNewRecord($Fields);
										}
									}
								}
							}
						}
					}
				}
			}
			$StartDate = date("Y-m-d",strtotime("$StartDate + 1 Day"));
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}
