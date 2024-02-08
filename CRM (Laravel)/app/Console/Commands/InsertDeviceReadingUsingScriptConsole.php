<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IOTEquipmentReading;
use Mail;
use DB;
class InsertDeviceReadingUsingScriptConsole extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'InsertDeviceReadingUsingScript';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'TO INSERT FROM ARCHIVE TO wm_iot_equipment_readings table';

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
		echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";

		$last_id = IOTEquipmentReading::where("id",">",796)->where("id","<",25570713)->min("id");
		echo $last_id."<br/>";
		if($last_id > 0){
			DB::statement("INSERT INTO wm_iot_equipment_readings(id,slave_id,mrf_id,device_code,reading,reading_datetime,reading_year,reading_month,created_at) 
				SELECT id,slave_id,mrf_id,device_code,reading,reading_datetime,YEAR(reading_datetime),MONTH(reading_datetime),created_at
				FROM wm_iot_equipment_readings_archieve
				where  id > 796 and id < $last_id order by id desc limit 0,100000");
		}

		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}