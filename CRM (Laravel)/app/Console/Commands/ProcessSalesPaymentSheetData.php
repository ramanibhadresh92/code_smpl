<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmClientMaster;
use App\Models\WmSalesPaymentDetails;
use App\Models\WmSalesPaymentDetailsLog;
use App\Models\SalesPaymentDetails;
use Mail;
use DB;

class ProcessSalesPaymentSheetData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ProcessSalesPaymentSheetData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Process Sales Payment Details updated from ONS';

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
		$TODAY 					= date("Y-m-d");
		$STARTTIME 				= $TODAY." ".GLOBAL_START_TIME;
		$ENDTIME 				= $TODAY." ".GLOBAL_END_TIME;
		$SalesPaymentDetails 	= new SalesPaymentDetails;
		// SalesPaymentDetails::where("created_at","<",$TODAY." ".GLOBAL_END_TIME)->where("processed",0)->update(["processed"=>1]);
		$PaymentDetailsRows 	= $SalesPaymentDetails->where("processed",0)->get()->toArray();
		if (!empty($PaymentDetailsRows))
		{
			$INSERTLOGSQL 	= "REPLACE INTO wm_sales_payment_details_log (SELECT * FROM wm_sales_payment_details)";
			$LOGRES 		= DB::statement($INSERTLOGSQL);
			echo "\r\n--LOGRES::".$LOGRES."--\r\n";
			WmSalesPaymentDetails::whereNotNull('id')->delete();
			foreach($PaymentDetailsRows as $PaymentDetailsRow)
			{
				$ONSCODE 		= trim(strtok($PaymentDetailsRow['Customer'], ' '));
				$WmClientMaster = WmClientMaster::select("id")->where("net_suit_code",$ONSCODE)->first();
				$WM_CLIENT_ID 	= (!empty($WmClientMaster))?$WmClientMaster->id:0;

				$WmSalesPaymentDetails 						= new WmSalesPaymentDetails;
				$WmSalesPaymentDetails->wm_client_id 		= $WM_CLIENT_ID;
				$WmSalesPaymentDetails->Customer			= $PaymentDetailsRow['Customer'];
				$WmSalesPaymentDetails->Location 			= $PaymentDetailsRow['Location'];
				$WmSalesPaymentDetails->TransactionType 	= $PaymentDetailsRow['TransactionType'];
				$WmSalesPaymentDetails->CustomerCategory 	= $PaymentDetailsRow['CustomerCategory'];
				$WmSalesPaymentDetails->Date 				= $PaymentDetailsRow['Date'];
				$WmSalesPaymentDetails->DocumentNumber 		= $PaymentDetailsRow['DocumentNumber'];
				$WmSalesPaymentDetails->PONo 				= $PaymentDetailsRow['PONo'];
				$WmSalesPaymentDetails->DueDate 			= $PaymentDetailsRow['DueDate'];
				$WmSalesPaymentDetails->Age 				= $PaymentDetailsRow['Age'];
				$WmSalesPaymentDetails->AmountGross 		= $PaymentDetailsRow['AmountGross'];
				$WmSalesPaymentDetails->OpenBalance 		= $PaymentDetailsRow['OpenBalance'];
				$WmSalesPaymentDetails->DaysTillNetDue 		= $PaymentDetailsRow['DaysTillNetDue'];
				$WmSalesPaymentDetails->RDueDate 			= $PaymentDetailsRow['RDueDate'];
				$WmSalesPaymentDetails->Remarks 			= $PaymentDetailsRow['Remarks'];
				$WmSalesPaymentDetails->epr_bach_no 		= $PaymentDetailsRow['epr_bach_no'];
				$WmSalesPaymentDetails->save();
				SalesPaymentDetails::where("id",$PaymentDetailsRow['id'])->where("processed",0)->update(["processed"=>1]);
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}