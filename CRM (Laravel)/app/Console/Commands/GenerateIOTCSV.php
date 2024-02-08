<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;
use DB;

class GenerateIOTCSV extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateIOTCSV';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Generate IOT CSV';

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
		$SelectSql			= "	SELECT wm_iot_equipment_master.title as EQ_TITLE, wm_iot_device_parameters.title AS PARA_TITLE,
								wm_iot_device_parameters.code, wm_iot_device_parameters.multiplication_factor,
								wm_iot_equipment_readings.reading,
								DATE_FORMAT(wm_iot_equipment_readings.reading_datetime, '%Y-%m-%d') as Reading_Date,
								DATE_FORMAT(wm_iot_equipment_readings.reading_datetime, '%H:%i') as Reading_Time
								FROM wm_iot_equipment_readings
								LEFT JOIN wm_iot_equipment_master ON wm_iot_equipment_master.slave_id = wm_iot_equipment_readings.slave_id
								LEFT JOIN wm_iot_device_parameters ON wm_iot_device_parameters.code = wm_iot_equipment_readings.device_code
								WHERE wm_iot_equipment_readings.reading_datetime BETWEEN '2022-07-01 06:00:00' AND '2022-07-25 20:00:00'
								AND wm_iot_equipment_readings.mrf_id = 48
								AND wm_iot_equipment_readings.device_code IN (400260)";
		$SelectRes  = DB::select($SelectSql);
		if (!empty($SelectRes)) {
			$FilePath 	= storage_path()."/import_collection/IOTData-".time().".csv";
			$HeaderRow 	= "EQ. Title,Parameter Name,Parameter Code,Multiplication Factor,Reading, Reading Date, Reading Time\r\n";
			$FP = fopen($FilePath,"a+");
			fwrite($FP,$HeaderRow);
			fclose($FP);
			foreach ($SelectRes as $SelectRow) {
				$Data 		= "";
				$seperator 	= "";
				$Data 		.= $SelectRow->EQ_TITLE;
				$seperator 	= ",";
				$Data 		.= $seperator.$SelectRow->PARA_TITLE;
				$Data 		.= $seperator.$SelectRow->code;
				$Data 		.= $seperator.$SelectRow->multiplication_factor;
				$Data 		.= $seperator.$SelectRow->reading;
				$Data 		.= $seperator.$SelectRow->Reading_Date;
				$Data 		.= $seperator.$SelectRow->Reading_Time;
				$Data 		.= "\r\n";
				$FP = fopen($FilePath,"a+");
				fwrite($FP,$Data);
				fclose($FP);
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}