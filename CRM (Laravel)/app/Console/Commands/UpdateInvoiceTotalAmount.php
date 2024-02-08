<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\NetSuit;
use Mail;
use DB;

class UpdateInvoiceTotalAmount extends Command
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
	protected $signature = 'UpdateInvoiceTotalAmount';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Update Invoice Total Amount';

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
		$SELECT_SQL 			= "SELECT id FROM wm_dispatch WHERE approval_status = 1 ";
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
				$SQL_QUERY 		= "	SELECT wm_dispatch.challan_no AS INVOICE_NO,
									SUM(wm_sales_master.gross_amount) AS GROSS_AMT,
									SUM(wm_sales_master.gst_amount) AS GST_AMT,
									SUM(wm_sales_master.net_amount) AS NET_AMT,
									SUM(wm_dispatch.rent_amt) AS FREIGHT_GROSS,
									SUM(wm_dispatch.rent_gst_amt) AS FREIGHT_GST,
									SUM(wm_dispatch.total_rent_amt) AS FREIGHT_NET,
									SUM(wm_dispatch.tcs_amount) AS TCS_AMT
									FROM wm_sales_master
									INNER JOIN wm_dispatch on wm_sales_master.dispatch_id = wm_dispatch.id
									WHERE wm_dispatch.id = ".$SELECT_ROW->id."
									GROUP BY wm_dispatch.id";
				$SELECTRES  	= DB::connection()->select($SQL_QUERY);
				$INVOICE_NO 	= 0;
				$GROSS_AMT 		= 0;
				$GST_AMT 		= 0;
				$NET_AMT 		= 0;
				$FREIGHT_GROSS 	= 0;
				$FREIGHT_GST 	= 0;
				$FREIGHT_NET 	= 0;
				$TCS_AMT 		= 0;
				if (isset($SELECTRES[0]->INVOICE_NO)) {
					$INVOICE_NO 	= floatval($SELECTRES[0]->INVOICE_NO);
					$GROSS_AMT 		= floatval($SELECTRES[0]->GROSS_AMT);
					$GST_AMT 		= floatval($SELECTRES[0]->GST_AMT);
					$NET_AMT 		= floatval($SELECTRES[0]->NET_AMT);
					$FREIGHT_GROSS 	= floatval($SELECTRES[0]->FREIGHT_GROSS);
					$FREIGHT_GST 	= floatval($SELECTRES[0]->FREIGHT_GST);
					$FREIGHT_NET 	= floatval($SELECTRES[0]->FREIGHT_NET);
					$TCS_AMT 		= floatval($SELECTRES[0]->TCS_AMT);
				}
				$AdditionalChargesSql 	= "	SELECT
											SUM(wm_invoice_additional_charges.gross_amount) AS CHARGE_GROSS,
											SUM(wm_invoice_additional_charges.gst_amount) AS CHARGE_GST,
											SUM(wm_invoice_additional_charges.net_amount) AS CHARGE_NET
											FROM wm_invoice_additional_charges
											WHERE wm_invoice_additional_charges.dispatch_id = ".$SELECT_ROW->id;
				$AdditionalChargesRes 	= DB::connection()->select($AdditionalChargesSql);
				$CHARGE_GROSS 	= 0;
				$CHARGE_GST 	= 0;
				$CHARGE_NET 	= 0;
				if (isset($AdditionalChargesRes[0]->CHARGE_GROSS)) {
					$CHARGE_GROSS 	= floatval($AdditionalChargesRes[0]->CHARGE_GROSS);
					$CHARGE_GST 	= floatval($AdditionalChargesRes[0]->CHARGE_GST);
					$CHARGE_NET 	= floatval($AdditionalChargesRes[0]->CHARGE_NET);
				}
				$FINAL_INV_AMOUNT = floatval($NET_AMT) + floatval($FREIGHT_NET) + floatval($TCS_AMT) + floatval($CHARGE_NET);
				$GRAND_TOTAL_NET_AMT += $FINAL_INV_AMOUNT;
				$DISPATCH_ID = $SELECT_ROW->id;
				$INSERT_SQL = "	REPLACE INTO wm_dispatch_final_amount_master SET
								dispatch_id 		= '".$DISPATCH_ID."',
								invoice_no 			= '".$INVOICE_NO."',
								gross_amount 		= '".$GROSS_AMT."',
								gst_amount 			= '".$GST_AMT."',
								net_amount 			= '".$NET_AMT."',
								freight_gross_amount= '".$FREIGHT_GROSS."',
								freight_gst_amount	= '".$FREIGHT_GST."',
								freight_net_amount 	= '".$FREIGHT_NET."',
								tcs_amount 			= '".$TCS_AMT."',
								charges_gross_amount= '".$CHARGE_GROSS."',
								charges_gst_amount	= '".$CHARGE_GST."',
								charges_net_amount 	= '".$CHARGE_NET."',
								final_inv_amount 	= '".$FINAL_INV_AMOUNT."',
								created_at 			= '".date("Y-m-d H:i:s")."',
								updated_at 			= '".date("Y-m-d H:i:s")."'";
				DB::connection()->statement($INSERT_SQL);
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}