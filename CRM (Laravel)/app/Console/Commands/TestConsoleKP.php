<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminTransactionGroups;
use App\Models\InvoiceAdditionalCharges;
use App\Models\WmDispatch;
use App\Facades\LiveServices;
use Mail;
use DB;
use DateTime;
class TestConsoleKP extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'TestConsoleKP';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Test Reports';

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


		// $datetime1 = new DateTime('2014-02-11 04:04:26');
		// $datetime2 = new DateTime('2014-02-21 05:36:56');
		// $interval = $datetime1->diff($datetime2);
		// echo $interval->format('%h')." Hours ".$interval->format('%i')." Minutes";


		// $SELECTSQL = "SELECT time_format(SUM(abs(timediff('2014-02-11 04:04:26','2014-02-21 05:36:56'))),'%H:%i:%s') as TimeTaken";
		// $SELECTRES = DB::select($SELECTSQL);
		// dd($SELECTRES[0]->TimeTaken);

		// $base_path = public_path()."/employeeqrcode/";
		// if(!is_dir($base_path)) {
		// 	mkdir($base_path,0777,true);
		// }
		// $CSVFileName		= storage_path()."/import_collection/employeeqrcode.csv";
		// $CSV_File_Name 		= basename($CSVFileName);
		// $SERVER_FILE_PATH 	= $CSVFileName;
		// echo "\r\n--SERVER_FILE_PATH::".$SERVER_FILE_PATH."--\r\n";
		// if (file_exists($SERVER_FILE_PATH))
		// {
		// 	$counter					= 0;
		// 	$ImportData 				= true;
		// 	$no_of_lines 				= 0;
		// 	$file_handle 				= fopen($SERVER_FILE_PATH, 'r');
		// 	while (!feof($file_handle))
		// 	{
		// 		$line_of_text = array();
		// 		$line_of_text = fgetcsv($file_handle);
		// 		if($no_of_lines > 0)
		// 		{
		// 			if(!empty($line_of_text[0]) && !empty($line_of_text[1]) && !empty($line_of_text[2]))
		// 			{
		// 				$EmployeeID 	= trim($line_of_text[0]);
		// 				$File_Path  	= $base_path.$EmployeeID.".png";
		// 				$QRCodeString 	= "Employee Name: ".trim($line_of_text[1]);
		// 				$QRCodeString 	.= "\r\nDate Of Join: ".trim($line_of_text[2]);
		// 				$QRCodeString 	.= "\r\nEmployee Code: ".$EmployeeID;
		// 				$QRCodeString 	.= "\r\nBlood Group: ".trim(isset($line_of_text[3]) && !empty($line_of_text[3])?$line_of_text[3]:"-");
		// 				$QRCodeString 	.= "\r\nEmergency Contact: ".trim(isset($line_of_text[4]) && !empty($line_of_text[4])?$line_of_text[4]:"-");
		// 				$QRCodeString 	.= "\r\nBranch: ".trim(isset($line_of_text[5]) && !empty($line_of_text[5])?$line_of_text[5]:"-");
		// 				$data 			= GetQRCodeV2($QRCodeString,$File_Path);
		// 			}
		// 		}
		// 		$no_of_lines++;
		// 	}
		// }

		WmDispatch::SendRecyclebleDispatchToEPR();

		// $START_TIME = "2023-11-17 10:00:00";
		// $END_TIME 	= "2023-11-17 17:00:00";
		// $client_id 	= 567;

		// $dispatch_data 	= WmDispatch::select(DB::raw("WPM.title as product_name"),
		// 									DB::raw("(	CASE
		// 														WHEN WPM.is_afr = 1 THEN 'Shredded'
		// 														WHEN WPM.is_rdf = 1 THEN 'Unshredded'
		// 														ELSE '-'
		// 													END) as product_type"),
		// 									"wm_dispatch.id as dispatch_id",
		// 									"wm_dispatch.dispatch_date",
		// 									"wm_dispatch.vehicle_id",
		// 									"wm_dispatch.challan_no",
		// 									"vehicle_master.vehicle_number",
		// 									"WDESL.dispatch_id as disp_id",
		// 									"location_master.city as vehicle_from",
		// 									DB::raw("SUM(WDP.quantity) as quantity"))
		// 							->leftjoin("wm_dispatch_email_send_log as WDESL","wm_dispatch.id","=","WDESL.dispatch_id")
		// 							->leftjoin("wm_dispatch_product as WDP","wm_dispatch.id","=","WDP.dispatch_id")
		// 							->leftjoin("wm_product_master as WPM","WDP.product_id","=","WPM.id")
		// 							->leftjoin("vehicle_master","wm_dispatch.vehicle_id","=","vehicle_master.vehicle_id")
		// 							->leftjoin("location_master","wm_dispatch.origin_city","=","location_master.location_id")
		// 							->whereNull("WDESL.dispatch_id")
		// 							->whereIn("wm_dispatch.approval_status",array(1,4))
		// 							->whereBetween("wm_dispatch.challan_date",array($START_TIME,$END_TIME))
		// 							->where("wm_dispatch.client_master_id",$client_id)
		// 							->groupBy("wm_dispatch.id")
		// 							->groupBy("WDP.product_id");

		// LiveServices::toSqlWithBinding($dispatch_data);
		// echo "\r\n--".$SQL."--\r\n";

		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}