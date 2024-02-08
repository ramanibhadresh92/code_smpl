<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmClientMaster;
use App\Models\WmDispatch;
use App\Models\WmPaymentReceive;
use Mail;
use DB;
class UpdateVendorPaymentDetails extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateVendorPaymentDetails';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Update Vendor Payment Details';

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

		$CSVFileName 		= storage_path()."/vendorpayment.csv";
		$CSV_File_Name 		= basename($CSVFileName);
		$SERVER_FILE_PATH 	= $CSVFileName;
		$DispatchStartDate	= "2021-04-01 00:00:00";
		$DispatchEndDate	= date("Y-m-d")." 23:59:59";
		$SystemUserID 		= 0;
		$PAYMENT_TYPE 		= 1030003;
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
					// 0 = NETSUIT CODE OF VENDOR
					// 1 = VENDOR NAME
					// 2 = PAYMENT AMOUNT
					// 3 = PAYMENT DATE
					// 4 = PAYMENT REF. DETAILS
					if(!empty($line_of_text[0]) && !empty($line_of_text[2]) && !empty($line_of_text[3]))
					{
						$PaidAmount = $line_of_text[3];

						$VendorDetails = WmClientMaster::where("netsuit_code",$line_of_text[0])->first();
						if (!empty($VendorDetails))
						{
							$VendorDispatchDetails = WmDispatch::select("id")->whereBetween("dispatch_date",[$DispatchStartDate,$DispatchEndDate])->where("approval_status",1)->get();
							if (!empty($VendorDispatchDetails))
							{
								foreach($VendorDispatchDetails as $VendorDispatchDetail)
								{
									$InvoiceAmountSql 	= "	SELECT
															CASE WHEN 1=1 THEN
															(
																SELECT SUM(gross_amount) FROM wm_dispatch_product WHERE dispatch_id = ".$VendorDispatchDetail->id."
															) END AS Invoice_Amount,
															CASE WHEN 1=1 THEN
															(
																SELECT SUM(wm_invoices_credit_debit_notes_details.revised_net_amount)
																FROM wm_invoices_credit_debit_notes_details
																INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
																INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
																WHERE wm_dispatch.approval_status = 1
																AND wm_invoices_credit_debit_notes.notes_type = 0
																AND wm_invoices_credit_debit_notes.status = 3
																AND wm_dispatch.id = ".$VendorDispatchDetail->id."
															) END AS CN_Amount,
															CASE WHEN 1=1 THEN
															(
																SELECT SUM(wm_invoices_credit_debit_notes_details.revised_net_amount)
																FROM wm_invoices_credit_debit_notes_details
																INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
																INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
																WHERE wm_dispatch.approval_status = 1
																AND wm_invoices_credit_debit_notes.notes_type = 1
																AND wm_invoices_credit_debit_notes.status = 3
																AND wm_dispatch.id = ".$VendorDispatchDetail->id."
															) END AS DN_Amount,
															CASE WHEN 1=1 THEN
															(
																SELECT wm_invoices.id
																FROM wm_invoices
																INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices.dispatch_id
																WHERE wm_dispatch.approval_status = 1
																AND wm_dispatch.id = ".$VendorDispatchDetail->id."
															) END AS Invoice_ID";
									$SELECTRES 			= DB::connection('master_database')->select($Invoice_Amount_Sql);
									if (!empty($SELECTRES) && isset($SELECTRES[0]->Invoice_Amount) && !empty($SELECTRES[0]->Invoice_ID))
									{
										$Invoice_Amount 		= !empty($SELECTRES[0]->Invoice_Amount)?$SELECTRES[0]->Invoice_Amount:0;
										$CN_Amount 				= !empty($SELECTRES[0]->CN_Amount)?$SELECTRES[0]->CN_Amount:0;
										$DN_Amount 				= !empty($SELECTRES[0]->DN_Amount)?$SELECTRES[0]->DN_Amount:0;
										$Invoice_ID 			= !empty($SELECTRES[0]->Invoice_ID)?$SELECTRES[0]->Invoice_ID:0;
										$Total_Invoice_Amount	= round((($Invoice_Amount + $DN_Amount) - $CN_Amount),2);
										if ($PaidAmount > 0 && $Total_Invoice_Amount > 0 && !empty($Invoice_ID)) {
											if ($PaidAmount >= $Total_Invoice_Amount) {
												$Invoice_Paid_Amount 	= $Invoice_Amount;
												$PaidAmount 			= $PaidAmount - $Invoice_Paid_Amount;
											} else {
												$Invoice_Paid_Amount = $PaidAmount;
												$PaidAmount = 0;
											}
											/** INSERT DATA IN PAYMENT RECEIVED TABLE */
											$WmPaymentReceive 					= new WmPaymentReceive;
											$WmPaymentReceive->invoice_id 		= $Invoice_ID;
											$WmPaymentReceive->collect_by 		= $SystemUserID;
											$WmPaymentReceive->received_amount 	= $Invoice_Paid_Amount;
											$WmPaymentReceive->payment_type 	= $PAYMENT_TYPE;
											/** INSERT DATA IN PAYMENT RECEIVED TABLE */
										}
									}
									if ($PaidAmount <= 0) {
										break; // All Amount is setoff against invoice so break the loop;
									}
								}
							}
						}
					}
				}
				$no_of_lines++;
			}
		}
		
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}