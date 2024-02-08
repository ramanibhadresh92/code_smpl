<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
class RejectPendingApprovalRequest extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'RejectPendingApprovalRequest';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command to use Reject Pending Approval Request which are 72 Hours Old.';

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
		
		$HOURS		= 72;
		$DATE 		= date("Y-m-d H:i:s",strtotime("-$HOURS hour"));
		$UPDATED_AT = date("Y-m-d H:i:s");
		$Remark 	= "Request has been rejected by System as it is not approved for $HOURS hours.";


		$SELECT_SQL = "SELECT * FROM invoice_approval_master WHERE created_at <= '$DATE' AND action_flag = 0";
		echo "\r\n--".$SELECT_SQL."--\r\n";

		$UpdatedRows = DB::table("invoice_approval_master")
						->where('created_at',"<=",$DATE)
						->where('action_flag',0)
						->update(['action_flag' => 2,'action_remark'=>$Remark,'updated_at'=>$UPDATED_AT]);

		echo "\r\n--".$UpdatedRows." changed in invoice_approval_master table.--\r\n";


		// $SELECT_SQL = "SELECT COUNT(0) AS CNT FROM wm_transfer_master WHERE created_at <= '$DATE' AND approval_status = 0";
		// echo "\r\n--".$SELECT_SQL."--\r\n";

		/*
		$UpdatedRows = DB::table("wm_transfer_master")
						->where('created_at',"<=",$DATE)
						->where('approval_status',0)
						->update(['approval_status' => 2,'action_remark'=>$Remark,'updated_at'=>$UPDATED_AT]);

		echo "\r\n--".$UpdatedRows." changed in wm_transfer_master table.--\r\n";
		*/

		$SELECT_SQL = "SELECT COUNT(0) AS CNT FROM wm_asset_master WHERE created_at <= '$DATE' AND approval_status = 0";
		echo "\r\n--".$SELECT_SQL."--\r\n";

		$UpdatedRows = DB::table("wm_asset_master")
						->where('created_at',"<=",$DATE)
						->where('approval_status',0)
						->update(['approval_status' => 2,'action_remark'=>$Remark,'updated_at'=>$UPDATED_AT]);

		echo "\r\n--".$UpdatedRows." changed in wm_asset_master table.--\r\n";

		// $SELECT_SQL = "SELECT COUNT(0) AS CNT FROM wm_invoices_credit_debit_notes WHERE created_at <= '$DATE' AND status = 0";
		// echo "\r\n--".$SELECT_SQL."--\r\n";

		// $UpdatedRows = DB::table("wm_invoices_credit_debit_notes")
		// 				->where('created_at',"<=",$DATE)
		// 				->where('status',0)
		// 				->update(['status' => 2,'action_remark'=>$Remark,'updated_at'=>$UPDATED_AT]);

		// echo "\r\n--".$UpdatedRows." changed in wm_invoices_credit_debit_notes table.--\r\n";

		$SELECT_SQL = "SELECT COUNT(0) AS CNT FROM wm_service_master WHERE created_at <= '$DATE' AND approval_status = 0";
		echo "\r\n--".$SELECT_SQL."--\r\n";

		$UpdatedRows = DB::table("wm_service_master")
						->where('created_at',"<=",$DATE)
						->where('approval_status',0)
						->update(['approval_status' => 2,'action_remark'=>$Remark,'updated_at'=>$UPDATED_AT]);

		echo "\r\n--".$UpdatedRows." changed in wm_service_master table.--\r\n";

		// $SELECT_SQL = "SELECT COUNT(0) AS CNT FROM purchase_credit_debit_note_master WHERE created_at <= '$DATE' AND status = 0";
		// echo "\r\n--".$SELECT_SQL."--\r\n";

		// $UpdatedRows = DB::table("purchase_credit_debit_note_master")
		// 				->where('created_at',"<=",$DATE)
		// 				->where('status',0)
		// 				->update(['status' => 2,'action_remark'=>$Remark,'updated_at'=>$UPDATED_AT]);

		// echo "\r\n--".$UpdatedRows." changed in purchase_credit_debit_note_master table.--\r\n";

		$SELECT_SQL = "SELECT COUNT(0) AS CNT FROM form_fields_approval_requests WHERE created_at <= '$DATE' AND status = 0";
		echo "\r\n--".$SELECT_SQL."--\r\n";

		$UpdatedRows = DB::table("form_fields_approval_requests")
						->where('created_at',"<=",$DATE)
						->where('status',0)
						->update(['status' => 2,'action_remark'=>$Remark,'updated_at'=>$UPDATED_AT]);

		echo "\r\n--".$UpdatedRows." changed in form_fields_approval_requests table.--\r\n";

		$SELECT_SQL = "SELECT COUNT(0) AS CNT FROM company_product_price_details_approval WHERE created_at <= '$DATE' AND approve_status = 0";
		echo "\r\n--".$SELECT_SQL."--\r\n";

		$UpdatedRows = DB::table("company_product_price_details_approval")
						->where('created_at',"<=",$DATE)
						->where('approve_status',0)
						->update(['approve_status' => 2,'action_remark'=>$Remark,'updated_at'=>$UPDATED_AT]);

		echo "\r\n--".$UpdatedRows." changed in company_product_price_details_approval table.--\r\n";

		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}
