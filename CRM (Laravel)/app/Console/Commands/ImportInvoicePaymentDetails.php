<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmInvoices;
use App\Models\WmPaymentReceive;
use App\Models\Parameter;
use Mail;
use DB;

class ImportInvoicePaymentDetails extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ImportInvoicePayment';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Import Invoice Payment Details';

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
		$DIR_TO_SCAN		= storage_path()."/import_payment_collection/*.csv";
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
				$errormsg 					= array();
				$ImportData 				= true;
				$no_of_lines 				= 0;
				$file_handle 				= fopen($SERVER_FILE_PATH, 'r');
				while (!feof($file_handle))
				{
					$line_of_text = array();
					$line_of_text = fgetcsv($file_handle);
					if($no_of_lines > 0)
					{
						if(!empty($line_of_text[0]) && !empty($line_of_text[1]) && !empty($line_of_text[2]))
						{
							$invoice_no 		= $line_of_text[0];
							$payment_date 		= $line_of_text[1];
							$received_amount	= $line_of_text[2];
							$payment_type		= (isset($line_of_text[3])?$line_of_text[3]:1030003);
							$remarks			= (isset($line_of_text[4])?$line_of_text[4]:"");
							$dateArr 			= explode("-",$payment_date);
							if (sizeof($dateArr) == 3) {
								$payment_date  	= date("Y-m-d",strtotime($dateArr[2]."-".$dateArr[1]."-".$dateArr[0]));
							} else {
								$dateArr 			= explode("/", $payment_date);
								$payment_date  	= date("Y-m-d",strtotime($dateArr[2]."-".$dateArr[1]."-".$dateArr[0]));
							}
							if (sizeof($dateArr) == 3) {
								$InvoiceDetails 	= WmInvoices::select('wm_invoices.id','wm_invoices.invoice_no',
																		DB::raw("CASE WHEN 1=1 THEN
																					(
																						SELECT SUM(net_amount) FROM
																						wm_sales_master
																						WHERE wm_sales_master.dispatch_id
																						= wm_invoices.dispatch_id
																					) END AS Invoice_Amount"),
																		DB::raw("CASE WHEN 1=1 THEN
																					(
																						SELECT SUM(received_amount) FROM
																						wm_payment_receive
																						WHERE wm_payment_receive.invoice_id
																						= wm_invoices.id
																					) END AS Received_Amount"),
																		DB::raw("CASE WHEN 1=1 THEN
																					(
																						SELECT SUM(net_amount) FROM
																						wm_invoices_credit_debit_notes_details
																						INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes_details.cd_notes_id = wm_invoices_credit_debit_notes.id
																						WHERE wm_invoices_credit_debit_notes.invoice_id
																						= wm_invoices.id
																						AND wm_invoices_credit_debit_notes.notes_type = 0
																					) END AS Credit_Note_Amount"),
																		DB::raw("CASE WHEN 1=1 THEN
																					(
																						SELECT SUM(net_amount) FROM
																						wm_invoices_credit_debit_notes_details
																						INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes_details.cd_notes_id = wm_invoices_credit_debit_notes.id
																						WHERE wm_invoices_credit_debit_notes.invoice_id
																						= wm_invoices.id
																						AND wm_invoices_credit_debit_notes.notes_type = 1
																					) END AS Debit_Note_Amount"))
														->where('wm_invoices.invoice_no',trim($invoice_no))->first();
								if(!empty($InvoiceDetails) && $ImportData && $payment_date != "1970-01-01")
								{
									$Total_Invoice_Amount 				= $InvoiceDetails->Invoice_Amount;
									$Total_Received_Amount 				= $InvoiceDetails->Received_Amount;
									$CreditNoteAmount 					= $InvoiceDetails->Credit_Note_Amount;
									$DebitNoteAmount 					= $InvoiceDetails->Debit_Note_Amount;
									$Total_Pending_Amount 				= (($Total_Invoice_Amount + $DebitNoteAmount) - ($Total_Received_Amount + $CreditNoteAmount));
									if ($Total_Pending_Amount <= $received_amount) {
										$objInvoicePayment 					= new WmPaymentReceive;
										$objInvoicePayment->invoice_id 		= $InvoiceDetails->id;
										$objInvoicePayment->payment_date 	= $payment_date;
										$objInvoicePayment->received_amount = $received_amount;
										$objInvoicePayment->payment_type 	= $payment_type;
										$objInvoicePayment->collect_by 		= 1;
										$objInvoicePayment->remarks 		= $remarks;
										$objInvoicePayment->created_by 		= 1;
										$objInvoicePayment->updated_by 		= 1;
										$objInvoicePayment->created_at 		= date("Y-m-d H:i:s");
										$objInvoicePayment->updated_at 		= date("Y-m-d H:i:s");
										if($objInvoicePayment->save())
										{
											if (($Total_Pending_Amount + $received_amount) >= $Total_Invoice_Amount) {
												$InvoiceStatus = 2; //Paid
											} else {
												$InvoiceStatus = 3; //Partial Paid
											}
											WmInvoices::where("id",$InvoiceDetails->id)->update(["invoice_status"=>$InvoiceStatus]);
										}
										$counter++;
									} else {
										$errormsg[] = $invoice_no." -- Received Amount is greater than invoice amount";
										echo "\r\n".$invoice_no." -- Received Amount is greater than invoice amount.\r\n";	
									}
								} else {
									$errormsg[] = $invoice_no." -- Invoice Not Found.";
									echo "\r\n".$invoice_no." -- Invoice Not Found.\r\n";
								}
							} else {
								$errormsg[] = $invoice_no." -- Invoice Not Found.";
								echo "\r\n".$invoice_no." -- Invalid Payment Date Format.\r\n";
								break;
							}
						}
					}
					$no_of_lines++;
				}
				echo "\r\n".$counter." -- payment data updated successfully.\r\n";
				$Attachments 	= array($SERVER_FILE_PATH);
				$ToEmail        = "kalpak@nepra.co.in";
				$FromEmail      = ["Email"=>"reports@letsrecycle.co.in","Name"=>"LR ERP Admin"];
				$Subject        = "Bulk Payment Update Report - ".date("Y-m-d H:i:s");
				$Message        = "Hello All,<br /> Below are the list of Invoices having error in payment update via Bulk Import process (".$CSV_File_Name.").<br /><br /><b>".$counter." invoice rows updated for payment.</b><br /><br />========INVOICES HAVING ISSUES IN BULK UPDATE============<br />".implode("<br />",$errormsg)."<br />========INVOICES HAVING ISSUES IN BULK UPDATE============<br /><br />Let's Recycle Admin";
				$EmailContent 	= array("Message"=>$Message);
				$sendEmail      = Mail::send("email-template.send_mail_blank_template",$EmailContent, function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
										$message->from($FromEmail['Email'], $FromEmail['Name']);
										$message->to(explode(",",$ToEmail));
										$message->subject($Subject);
										if (!empty($Attachments)) {
											foreach($Attachments as $Attachment) {
												$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
											}
										}
									});
				unlink($SERVER_FILE_PATH);
			} else {
				echo "\r\nfile not found.\r\n";
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}