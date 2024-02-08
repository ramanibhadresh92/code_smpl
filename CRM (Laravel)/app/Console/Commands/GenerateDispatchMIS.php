<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
class GenerateDispatchMIS extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateDispatchMIS';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command use to create shift for mrf';

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
		

		$fileName 	= storage_path().'/MIS_DISPATCH.csv';

		$SELECT_SQL = "	SELECT 
						IF (wm_dispatch.direct_dispatch=1,'VENDOR','MRF') AS DISPATCH_SOURCE,
						IF (wm_dispatch.direct_dispatch=1, 
							CONCAT(customer_master.first_name,' ',customer_master.middle_name,' ',customer_master.last_name),
							wm_department.department_name) AS Dispatch_From,
						wm_client_master.client_name AS Dispatch_To,
						DATE_FORMAT(wm_dispatch.dispatch_date,'%Y-%m-%d') AS Dispatch_Date,
						wm_dispatch.challan_no As Challan_No,
						CASE WHEN 1=1 THEN
						(
							SELECT SUM(quantity) FROM wm_dispatch_product
							WHERE wm_dispatch_product.dispatch_id = wm_dispatch.id
						) END AS Total_Quantity,
						CASE WHEN 1=1 THEN
						(
							SELECT SUM(gross_amount) FROM wm_dispatch_product
							WHERE wm_dispatch_product.dispatch_id = wm_dispatch.id
						) END AS Total_Gross_Amount,
						CASE WHEN 1=1 THEN
						(
							SELECT SUM(gst_amount) FROM wm_dispatch_product
							WHERE wm_dispatch_product.dispatch_id = wm_dispatch.id
						) END AS Total_GST_Amount,
						CASE WHEN 1=1 THEN
						(
							SELECT SUM(net_amount) FROM wm_dispatch_product
							WHERE wm_dispatch_product.dispatch_id = wm_dispatch.id
						) END AS Total_Net_Amount,
						IF (transporter_po_id > 0,transporter_details_master.rate,0) AS Transporter_Rate,
						IF (transporter_po_id > 0,transporter_details_master.demurrage,0) AS Transporter_Demurrage,
						UPPER(Origin_City_Master.city) As Dispatch_From_City,
						UPPER(Origin_City_Master.state) As Dispatch_From_State,
						UPPER(Destination_City_Master.city) As Dispatch_To_City,
						UPPER(Destination_City_Master.state) As Dispatch_To_State,
						IF (wm_dispatch.transporter_name IS NOT NULL, wm_dispatch.transporter_name,VM.vehicle_company) AS Transpoter,
						VM.vehicle_number AS Vehicle_Numer
						FROM wm_dispatch
						LEFT JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
						LEFT JOIN vehicle_master AS VM ON wm_dispatch.vehicle_id = VM.vehicle_id
						LEFT JOIN transporter_details_master ON transporter_details_master.id = wm_dispatch.transporter_po_id
						LEFT JOIN wm_department ON wm_department.id = wm_dispatch.master_dept_id
						LEFT JOIN customer_master ON wm_dispatch.origin = customer_master.customer_id
						LEFT JOIN location_master AS Origin_City_Master ON wm_dispatch.origin_city = Origin_City_Master.location_id
						LEFT JOIN location_master AS Destination_City_Master ON wm_dispatch.destination_city = Destination_City_Master.location_id
						WHERE wm_dispatch.dispatch_date > '2020-01-01'
						AND wm_dispatch.approval_status = 1
						ORDER BY wm_dispatch.dispatch_date ASC";
		$SelectRes  = DB::connection()->select($SELECT_SQL);
		$ExportFields 	= array("Sr No.","DISPATCH_SOURCE","Dispatch_From","Dispatch_To","Dispatch_Date",
								"Challan_No","Total_Quantity","Total_Gross_Amount","Total_GST_Amount",
								"Total_Net_Amount","Transporter_Rate","Transporter_Demurrage","Dispatch_From_City",
								"Dispatch_From_State","Dispatch_To_City","Dispatch_To_State","Transpoter","Vehicle_Numer");
		if (!empty($SelectRes))
		{
			$RowID 		= 1;
			foreach($SelectRes as $ReportRow)
			{
				if (!file_exists($fileName)) {
					$HeaderString 	= "";
					$seperator		= "";
					foreach ($ExportFields as $Field_Name) {
						$HeaderString 	.= $seperator.$Field_Name;
						$seperator      = ",";
					}
					echo "\r\n csvfilepath ==> ".basename($fileName)."\r\n";
					$HeaderString = rtrim($HeaderString,",");
					$HeaderString = $HeaderString."\r\n";
					$fp = fopen($fileName,"w+");
					fwrite($fp,$HeaderString);
					fclose($fp);
					chmod($fileName,0777);
					$HeaderString = "";
				}
				$seperator 			= "";
				$RowStringData 		= "";
				$RowString 			= "";
				foreach ($ExportFields as $Field_Name) {
					$RowData 		= "";
					switch ($Field_Name) {
						case 'Sr No.': {
							$RowData = $RowID;
							break;
						}
						default: {
							$RowData = isset($ReportRow->$Field_Name)?$ReportRow->$Field_Name:"";
							break;
						}
					}
					if ($RowData == "0000-00-00 00:00:00" || $RowData == "0000-00-00") {
						$RowData = "";
					}
					$RowStringData .= $seperator.'"'.$RowData.'"';
					$seperator 	= ",";
				}
				$RowID++;
				$RowStringData = rtrim($RowStringData,",");
				$RowString .= $RowStringData."\r\n";
				$fp = fopen($fileName,"a+");
				fwrite($fp,$RowString);
				fclose($fp);
				$RowString = "";
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}
