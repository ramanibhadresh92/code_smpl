<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerMaster;
use App\Models\Appoinment;
use App\Models\VehicleMaster;
use App\Models\VehicleDriverMappings;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use Mail;
use DB;

class ImportIOTData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ImportIOTData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Import IOT Data';

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
		$CSV_File_Name 		= "iot-data.csv";
		$SERVER_FILE_PATH 	= storage_path($CSV_File_Name);

		echo "\r\n--SERVER_FILE_PATH::".$SERVER_FILE_PATH."--\r\n";

		if (file_exists($SERVER_FILE_PATH)) 
		{
			$counter					= 0;
			$ImportData 				= true;
			$no_of_lines 				= 0;
			$file_handle 				= fopen($SERVER_FILE_PATH, 'r');
			while (!feof($file_handle))
			{
				$line_of_text 	= array();
				$line_of_text 	= fgetcsv($file_handle);
				$group 			= isset($line_of_text[0])?$line_of_text[0]:"";
				$reason 		= isset($line_of_text[1])?$line_of_text[1]:"";
				$type 			= 1;
				$company_id 	= 1;
				$status 		= 1;
				$created_at 	= date("Y-m-d H:i:s");
				$updated_at 	= date("Y-m-d H:i:s");
				$created_by 	= 1;
				$updated_by 	= 1;
				$INSERT_SQL 	= "	INSERT INTO wm_iot_device_maintanance_reason_corrective_action SET
									company_id 		= '".$company_id."',
									type 		 	= '".$type."',
									title 		 	= '".$reason."',
									status 		 	= '".$status."',
									reason_text 	= '".$reason."',
									group_text 		= '".$group."',
									created_at 		= '".$created_at."',
									updated_at 		= '".$updated_at."',
									created_by 		= '".$created_by."',
									updated_by 		= '".$updated_by."'";
				echo "\r\n".$INSERT_SQL.";\r\n";

				foreach ($line_of_text as $key => $line_text) {
					if ($key == 0 || $key == 1 || empty($line_text)) continue;
					$group 			= isset($line_of_text[0])?$line_of_text[0]:"";
					$reason 		= isset($line_of_text[1])?$line_of_text[1]:"";
					$type 			= 2;
					$company_id 	= 1;
					$status 		= 1;
					$created_at 	= date("Y-m-d H:i:s");
					$updated_at 	= date("Y-m-d H:i:s");
					$created_by 	= 1;
					$updated_by 	= 1;
					$INSERT_SQL 	= "	INSERT INTO wm_iot_device_maintanance_reason_corrective_action SET
										company_id 		= '".$company_id."',
										type 		 	= '".$type."',
										title 		 	= '".$line_text."',
										status 		 	= '".$status."',
										reason_text 	= '".$reason."',
										group_text 		= '".$group."',
										created_at 		= '".$created_at."',
										updated_at 		= '".$updated_at."',
										created_by 		= '".$created_by."',
										updated_by 		= '".$updated_by."'";
					echo "\r\n".$INSERT_SQL.";\r\n";
				}
				$no_of_lines++;
			}
			echo "\r\n".$no_of_lines." -- data imported successfully.\r\n";
		} else {
			echo "\r\nfile not found.\r\n";
		}

		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}