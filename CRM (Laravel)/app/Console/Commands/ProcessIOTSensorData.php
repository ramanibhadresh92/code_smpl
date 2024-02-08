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
class ProcessIOTSensorData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ProcessIOTSensorData';

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
		$this->ProcessIOTData();
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}

	public function ProcessIOTData()
	{
		$IOTSensorData 	= IOTSensorData::where("processed",0)->orderBy("id","ASC")->limit(1000)->get();
		/** UPDATE SELECTED RECORDS */
		$arrRow = array();
		foreach ($IOTSensorData as $IOTSensorRow) {
			array_push($arrRow,$IOTSensorRow->id);
		}
		IOTSensorData::whereIn("id",$arrRow)->update(["processed"=>1]);
		/** UPDATE SELECTED RECORDS */
		$IOTEquipmentParameters = IOTEquipmentParameters::where("status",STATUS_ACTIVE)->get()->toArray();
		$DEFAULT_MRF_ID			= 11;
		$arrParameters 			= array();
		if (!empty($IOTEquipmentParameters)) {
			foreach($IOTEquipmentParameters as $IOTEquipmentParameter) {
				$arrParameters[$IOTEquipmentParameter['code']] 	= array("multiplication_factor"=>$IOTEquipmentParameter['multiplication_factor'],
																		"reading_duration"=>$IOTEquipmentParameter['reading_duration']);
			}
		}
		if (!empty($IOTSensorData)) {
			foreach ($IOTSensorData as $IOTSensorRow) {
				// echo "\r\n--Row Starts::".date("Y-m-d H:i:s")."--\r\n";
				$JSON_DATA = json_decode($IOTSensorRow->row_data);
				if (!empty($JSON_DATA)) {
					foreach ($JSON_DATA as $JSON_ROW) {
						$SLAVE_ID 			= $JSON_ROW->slaveID;
						$MRF_ID 			= isset($JSON_ROW->MRFID)?$JSON_ROW->MRFID:$DEFAULT_MRF_ID;
						$reading_datetime 	= isset($JSON_ROW->dt)?$JSON_ROW->dt:$IOTSensorRow->created_at;
						$reading_datetime 	= $IOTSensorRow->created_at;
						$reading_datetime 	= date("Y-m-d H:i:s",strtotime($reading_datetime));
						foreach ($JSON_ROW->data as $device_code => $reading) {
							if (isset($arrParameters[$device_code])) {
								$reading = (($reading > 0)?(($reading/$arrParameters[$device_code]['multiplication_factor'])):$reading); //division factor
								$DeviceLastRecordReading = IOTDeviceLastReading::select("id")
															->where("slave_id",$SLAVE_ID)
															->where("mrf_id",$MRF_ID)
															->where("device_code",$device_code)
															->where("report_date",date("Y-m-d",strtotime($reading_datetime)))
															->first();
								if (!empty($DeviceLastRecordReading) && !empty($DeviceLastRecordReading->id)) {
									$DeviceLastRecordReading->last_reading = $reading;
									$DeviceLastRecordReading->save();
								} else {
									$IOTDeviceLastReading 				= new IOTDeviceLastReading;
									$IOTDeviceLastReading->mrf_id 		= $MRF_ID;
									$IOTDeviceLastReading->slave_id 	= $SLAVE_ID;
									$IOTDeviceLastReading->device_code 	= $device_code;
									$IOTDeviceLastReading->report_date 	= date("Y-m-d",strtotime($reading_datetime));
									$IOTDeviceLastReading->last_reading = $reading;
									$IOTDeviceLastReading->save();
								}
								$DeviceLastRecordTime 	= IOTEquipmentLastReading::select("id","last_reading_datetime")
															->where("slave_id",$SLAVE_ID)
															->where("mrf_id",$MRF_ID)
															->where("device_code",$device_code)
															->orderBy("id","DESC")
															->first();
								if (!empty($DeviceLastRecordTime) && isset($DeviceLastRecordTime->last_reading_datetime)) {
									$TimeDiff = strtotime($reading_datetime) - strtotime($DeviceLastRecordTime->last_reading_datetime);
									$TimeDiff = (!empty($TimeDiff) && $TimeDiff > 0)?round(abs($TimeDiff)/60,2):0;
									if ($arrParameters[$device_code]['reading_duration'] > 1) {
										if ($arrParameters[$device_code]['reading_duration'] >= $TimeDiff) {
											continue;
										}
									}
									IOTEquipmentLastReading::where("id",$DeviceLastRecordTime->id)->update(["last_reading_datetime"=>$reading_datetime]);
								} else {
									$Fields['slave_id'] 		= $SLAVE_ID;
									$Fields['mrf_id'] 			= $MRF_ID;
									$Fields['device_code'] 		= $device_code;
									$Fields['reading_datetime'] = $reading_datetime;
									IOTEquipmentLastReading::AddNewRecord($Fields);
								}
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
				$IOTSensorRow->processed = 2;
    			$IOTSensorRow->save();
    			// echo "\r\n--Row Ends::".date("Y-m-d H:i:s")."--\r\n";
			}
		}
	}
}
