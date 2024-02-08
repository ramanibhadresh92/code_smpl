<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IOTDeviceLastReading;
use DB;

class UpdateIOTDeviceLastReading extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateIOTDeviceLastReading';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Generate IOT Device Last Reading';

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
		$READING_START_DATETIME = date("Y-m-d");
		$FIRST_DATE 			= "SELECT reading_datetime FROM wm_iot_equipment_readings ORDER BY reading_datetime ASC LIMIT 1";
		$SelectRes  			= DB::select($FIRST_DATE);
		$Today 					= date("Y-m-d");
		if (!empty($SelectRes)) {
			$READING_START_DATE = date("Y-m-d",strtotime($SelectRes[0]->reading_datetime));
		}
		$READING_START_DATE = "2022-06-06";
		while (strtotime($READING_START_DATE) <= strtotime($Today)) {
			echo "\r\nREADING_START_DATE --> ".$READING_START_DATE."\r\n";
			$SelectSql			= "	SELECT wm_iot_equipment_readings.reading,
									MAX(wm_iot_equipment_readings.reading_datetime) AS reading_datetime,
									wm_iot_equipment_readings.slave_id,
									wm_iot_equipment_readings.device_code,
									wm_iot_equipment_readings.mrf_id
									FROM wm_iot_equipment_readings
									WHERE reading_datetime BETWEEN '$READING_START_DATE 00:00:00' AND '$READING_START_DATE 23:59:59'
									AND device_code = 400222
									AND slave_id = 5
									GROUP BY slave_id,device_code,mrf_id
									ORDER BY device_code ASC,  slave_id ASC, mrf_id ASC";
			$SelectRes  = DB::select($SelectSql);
			if (!empty($SelectRes)) {
				foreach ($SelectRes as $SelectRow) {

					dd($SelectRow);

					$IOTDeviceLastReading = IOTDeviceLastReading::where("report_date",$READING_START_DATE)
											->where("device_code",$SelectRow->device_code)
											->where("slave_id",$SelectRow->slave_id)
											->where("mrf_id",$SelectRow->mrf_id)
											->first();
					if (!empty($IOTDeviceLastReading) && !empty($IOTDeviceLastReading->id)) {
						$IOTDeviceLastReading->last_reading = $SelectRow->reading;
						$IOTDeviceLastReading->save();
					} else {
						$IOTDeviceLastReading 				= new IOTDeviceLastReading;
						$IOTDeviceLastReading->mrf_id 		= $SelectRow->mrf_id;
						$IOTDeviceLastReading->slave_id 	= $SelectRow->slave_id;
						$IOTDeviceLastReading->device_code 	= $SelectRow->device_code;
						$IOTDeviceLastReading->report_date 	= $READING_START_DATE;
						$IOTDeviceLastReading->last_reading = $SelectRow->reading;
						$IOTDeviceLastReading->save();
					}
				}
			}
			$READING_START_DATE = date("Y-m-d",strtotime("$READING_START_DATE + 1 Day"));
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}