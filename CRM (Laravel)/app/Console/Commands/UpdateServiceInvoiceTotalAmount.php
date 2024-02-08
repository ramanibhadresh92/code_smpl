<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\NetSuit;
use Mail;
use DB;

class UpdateServiceInvoiceTotalAmount extends Command
{
	/** @var OAuth */
	public $oauth;

	/** @var cURL */
	public $curl;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateServiceInvoiceTotalAmount';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Update Service Invoice Total Amount';

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
		$Last_Updated_Time 		= date("Y-m-d H:i:s",strtotime("-5 minutes"));
		$ALLRECORDS 			= false;
		$SELECT_SQL 			= "SELECT id FROM wm_service_master WHERE approval_status = 1 ";
		if (!$ALLRECORDS) {
			$SELECT_SQL .= " AND updated_at >= '$Last_Updated_Time'";
		}
		$SELECT_SQL .= " ORDER BY id ASC";
		echo "\r\n--SELECT_SQL::".$SELECT_SQL."--\r\n";
		$SELECT_RES 			= DB::connection()->select($SELECT_SQL);
		$GRAND_TOTAL_NET_AMT 	= 0;
		if (!empty($SELECT_RES)) {
			foreach ($SELECT_RES as $SELECT_ROW) {
				echo "\r\n--DISPATCH ID::".$SELECT_ROW->id."--\r\n";
				$SQL_QUERY 		= "	SELECT WSM.serial_no AS INVOICE_NO,
									SUM(WSPM.gross_amt) AS GROSS_AMT,
									SUM(WSPM.gst_amt) AS GST_AMT,
									SUM(WSPM.net_amt) AS NET_AMT,
									SUM(WSPM.net_amt) AS FINAL_INV_AMOUNT
									FROM wm_service_product_mapping WSPM
									INNER JOIN wm_service_master as WSM on WSM.id = WSPM.service_id
									WHERE WSM.id = ".$SELECT_ROW->id."
									GROUP BY WSM.id";
				$SELECTRES  	= DB::connection()->select($SQL_QUERY);
				$INVOICE_NO 	= 0;
				$GROSS_AMT 		= 0;
				$GST_AMT 		= 0;
				$NET_AMT 		= 0;
				if (isset($SELECTRES[0]->INVOICE_NO)) {
					$INVOICE_NO 		= floatval($SELECTRES[0]->INVOICE_NO);
					$GROSS_AMT 			= floatval($SELECTRES[0]->GROSS_AMT);
					$GST_AMT 			= floatval($SELECTRES[0]->GST_AMT);
					$NET_AMT 			= floatval($SELECTRES[0]->NET_AMT);
					$FINAL_INV_AMOUNT 	= floatval($SELECTRES[0]->FINAL_INV_AMOUNT);
				}
				$SERVICE_ID = $SELECT_ROW->id;
				$INSERT_SQL = "	REPLACE INTO wm_service_final_amount_master SET
								service_id 			= '".$SERVICE_ID."',
								invoice_no 			= '".$INVOICE_NO."',
								gross_amount 		= '".$GROSS_AMT."',
								gst_amount 			= '".$GST_AMT."',
								net_amount 			= '".$NET_AMT."',
								final_inv_amount 	= '".$FINAL_INV_AMOUNT."',
								created_at 			= '".date("Y-m-d H:i:s")."',
								updated_at 			= '".date("Y-m-d H:i:s")."'";
				DB::connection()->statement($INSERT_SQL);
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}