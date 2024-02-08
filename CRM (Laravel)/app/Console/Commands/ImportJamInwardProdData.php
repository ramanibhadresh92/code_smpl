<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerMaster;
use App\Models\Appoinment;
use App\Models\VehicleMaster;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use Mail;
use DB;

class ImportJamInwardProdData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ImportJamInwardProdData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Import Jam Inward Production Data';

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

		$DIR_TO_SCAN		= storage_path()."/jam-data/*.csv";
		$CSV_File_Names 	= glob($DIR_TO_SCAN);
		foreach ($CSV_File_Names as $CSVFileName)
		{
			$CSV_File_Name 		= basename($CSVFileName);
			$ImportID 			= date("Y-m-d");
			$SERVER_FILE_PATH 	= $CSVFileName;
			echo "\r\n--SERVER_FILE_PATH::".$SERVER_FILE_PATH."--\r\n";
			if (file_exists($SERVER_FILE_PATH))
			{
				$counter					= 0;
				$ImportData 				= true;
				$no_of_lines 				= 0;
				$file_handle 				= fopen($SERVER_FILE_PATH, 'r');
				while (!feof($file_handle))
				{
					$line_of_text = array();
					$line_of_text = fgetcsv($file_handle);
					if($no_of_lines > 0)
					{
						if(!empty($line_of_text[1]) && !empty($line_of_text[2]) && !empty($line_of_text[3]) && !empty($line_of_text[3]) && !empty($line_of_text[5]))
						{
							$inward_date 		= date("Y-m-d",strtotime($line_of_text[1]));
							$vehicleNumber 		= $line_of_text[2];
							$gross_weight 		= $line_of_text[3];
							$tare_weight 		= $line_of_text[4];
							$net_weight 		= $line_of_text[5];
							$aglo 				= $line_of_text[6]; //aglo
							$granules 			= $line_of_text[7]; //granules
							$total_prod_qty 	= $line_of_text[7]; //total production qty
							
							$INSERTROW 	= "	INSERT INTO jam_inward_master SET 
											product_id 		= 50,
											mrf_id 			= 27,
											vehicle_no 		= '".$vehicleNumber."',
											tare_weight 	= '".$tare_weight."',
											gross_weight 	= '".$gross_weight."',
											net_weight 		= '".$net_weight."',
											inward_date 	= '".$inward_date."',
											created_by 		= 1,
											created_at 		= '".$inward_date." ".$this->GetCollectionTime()."'";
							DB::insert($INSERTROW);

							if (!empty($aglo))
							{
								$INSERTROW 	= "	INSERT INTO jam_production_master SET 
												product_id 		= 430,
												mrf_id 			= 27,
												quantity 		= '".$aglo."',
												production_date = '".$inward_date."',
												created_by 		= 1,
												created_at 		= '".$inward_date." ".$this->GetCollectionTime()."'";
								DB::insert($INSERTROW);
							}

							if (!empty($granules))
							{
								$INSERTROW 	= "	INSERT INTO jam_production_master SET 
												product_id 		= 364,
												mrf_id 			= 27,
												quantity 		= '".$granules."',
												production_date = '".$inward_date."',
												created_by 		= 1,
												created_at 		= '".$inward_date." ".$this->GetCollectionTime()."'";
								DB::insert($INSERTROW);
							}
						}
					}
					$no_of_lines++;
				}
				unlink($SERVER_FILE_PATH);
			} else {
				echo "\r\nfile not found.\r\n";
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}

	public function GetCollectionTime()
	{
		$hour 		= rand(10,19);
		$hour 		= ($hour) < 10?"0".$hour:$hour;
		$minutes 	= array(10,15,20,25,30,35,40,45,50,55);
		$key 		= array_rand($minutes);
		$minute 	= isset($minutes[$key])?$minutes[$key]:"00";
		return $hour.":".$minute.":00";
	}
}