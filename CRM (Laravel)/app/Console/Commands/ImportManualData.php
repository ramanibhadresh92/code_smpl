<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use DB;

class ImportManualData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ImportManualData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'ImportManualData';

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
		$DIR_TO_SCAN		= storage_path()."/manual-data/*.csv";
		$CSV_File_Names 	= glob($DIR_TO_SCAN);
		foreach ($CSV_File_Names as $CSVFileName)
		{
			$CSV_File_Name 		= basename($CSVFileName);
			$SERVER_FILE_PATH 	= $CSVFileName;
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
						$net_suit_code 		= trim($line_of_text[0]);
						$email 				= trim($line_of_text[1]);
						$contact_no 		= trim($line_of_text[2]);
						$contact_person 	= trim($line_of_text[3]);
						$contact_no 		= preg_replace("/[^0-9]/", "",$contact_no);
						$contact_person 	= preg_replace("/[^A-Za-z0-9]/", "",$contact_person);
						$UPDATE_SQL			= "	UPDATE wm_client_master SET
												email = '".$email."',
												email_for_notification = '".$email."',
												contact_person = '".$contact_person."',
												contact_no = '".$contact_no."',
												mobile_no = '".$contact_no."'
												WHERE net_suit_code = '".$net_suit_code."'";
						DB::statement($UPDATE_SQL);
						// echo "\r\n--UPDATE_SQL::".$UPDATE_SQL."--\r\n";
						$counter++;
					}
					$no_of_lines++;
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}