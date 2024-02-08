<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockLadger;
use Mail;
use DB;

class UpdateStockLedger extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateStockLedger';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Update Product Stock Manually';

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
		// $arrFiles = array("11_P.csv","11_S.csv","23_P.csv","23_S.csv");
		// $arrFiles = array("3_P.csv","3_S.csv");
		$arrFiles = array("27_S.csv");
		foreach ($arrFiles as $key => $CSV_File_Name)
		{
			$SERVER_FILE_PATH 	= storage_path($CSV_File_Name);
			$OPENING_STOCK_DATE	= date("Y-m-d");
			$FILE_NAME 			= explode("_",str_replace(".csv","",$CSV_File_Name));
			$PRODUCT_TYPE 		= (isset($FILE_NAME[1]) && $FILE_NAME[1] == "P")?1:0; //1 purchase 2 sales
			$PRODUCT_TYPE 		= (isset($FILE_NAME[1]) && $FILE_NAME[1] == "S")?2:$PRODUCT_TYPE; //1 purchase 2 sales
			$TYPE 				= ($PRODUCT_TYPE == 1?"P":0); //P purchase S sales
			$TYPE 				= ($PRODUCT_TYPE == 2?"S":$TYPE); //P purchase S sales
			$COMPANY_ID 		= 1;
			$MRF_ID 			= isset($FILE_NAME[0])?$FILE_NAME[0]:0;
			$STOCK_UPDATED 		= 1;
			if (file_exists($SERVER_FILE_PATH) && !empty($PRODUCT_TYPE) && !empty($TYPE) && !empty($MRF_ID))
			{
				echo "\r\n--SERVER_FILE_PATH::".$SERVER_FILE_PATH."--\r\n";
				$Icounter					= 0;
				$Ucounter					= 0;
				$no_of_lines 				= 0;
				$file_handle 				= fopen($SERVER_FILE_PATH, 'r');
				while (!feof($file_handle))
				{
					$line_of_text = array();
					$line_of_text = fgetcsv($file_handle);
					if($no_of_lines > 0)
					{
						if(!empty($line_of_text[0]))
						{
							$ExistingRow 	= StockLadger::where("product_id",$line_of_text[0])
												->where("product_type",$PRODUCT_TYPE)
												->where("type",$TYPE)
												->where("company_id",$COMPANY_ID)
												->where("mrf_id",$MRF_ID)
												->where("stock_date",$OPENING_STOCK_DATE)
												->first();
							if (!empty($ExistingRow)) {
								$ExistingRow->opening_stock = isset($line_of_text[1]) && !empty($line_of_text[1])?$line_of_text[1]:0;
								$ExistingRow->save();
								$Ucounter++;
							} else {
								$StockLadger 				= new StockLadger;
								$StockLadger->product_id 	= $line_of_text[0];
								$StockLadger->product_type 	= $PRODUCT_TYPE;
								$StockLadger->opening_stock = isset($line_of_text[1]) && !empty($line_of_text[1])?$line_of_text[1]:0;
								$StockLadger->inward 		= 0;
								$StockLadger->outward 		= 0;
								$StockLadger->closing_stock = 0;
								$StockLadger->company_id 	= $COMPANY_ID;
								$StockLadger->type 			= $TYPE;
								$StockLadger->mrf_id 		= $MRF_ID;
								$StockLadger->stock_date 	= $OPENING_STOCK_DATE;
								$StockLadger->stock_updated = $STOCK_UPDATED;
								$StockLadger->save();
								$Icounter++;
							}
						}
					}
					$no_of_lines++;
				}
				echo "\r\n"."MRF_ID :: ".$MRF_ID." ".$Icounter." -- Stock data inserted successfully.\r\n";
				echo "\r\n"."MRF_ID :: ".$MRF_ID." ".$Ucounter." -- Stock data updated successfully.\r\n";
			} else {
				echo "\r\nfile not found ==> ".$SERVER_FILE_PATH."\r\n";
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}