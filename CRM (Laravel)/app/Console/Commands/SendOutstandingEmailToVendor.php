<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\AdminUser;
use App\Models\VendorLedgerBalanceMaster;
use App\Models\WmDepartment;
use App\Models\CustomerMaster;
use Mail;
use Carbon\Carbon;

class SendOutstandingEmailToVendor extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SendOutstandingEmailVendor';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Outstanding Email to Vendor';

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
		// $StartTime      = date("Y-m-d")." 00:00:00";
		// $EndTime        = date("Y-m-d")." 23:59:59";
		$VLBM     	= new VendorLedgerBalanceMaster;
		$table 		= $VLBM->getTable();
		$Customer   = new CustomerMaster();
		$Department = new WmDepartment();	
		$qry 		= self::select(
						"$table.id as id",
						"$table.bill_date as bill_date",
						"$table.bill_type as bill_type",
						"$table.vendor_code as vendor_code",
						"$table.bill_no as bill_no",
						"$table.balance_amount as balance_amount",
						"$table.mrf_ns_id as mrf_ns_id",
						"$table.mrf_name as mrf_name",
						"$table.mrf_id as mrf_id",
						"$table.vendor_id as vendor_id",
						"$table.created_at as created_from",
						"CM.email as email",
						\DB::raw("CONCAT(CM.first_name,' ',CM.last_name) AS vendor_name"),
						\DB::raw("DEPT.department_name")
					)
					->join($Customer->getTable()." as CM","$table.vendor_code","=","CM.net_suit_code")
					->leftjoin($Department->getTable()." as DEPT","$table.mrf_id","=","DEPT.id");
		$resultData = $qry->get()->toArray();
		if(!empty($resultData)){
			foreach($resultData as $key => $val){
				if(!empty($val['email'])){
					$ToEmail				= $val['email'];
					$FromEmail 				= array('Email'=>"donotreply@nepra.co.in",'Name'=>'Nepra');
					$Subject 				= "Ledger Report";
					$Message        		= "Hello All";
					$EmailContent			= array();
					$EmailContent['ROWS']	= array();
					$EmailContent['ROWS']['INVOICE_NO'] 		= $val['bill_no'];
					$EmailContent['ROWS']['INVOICE_DATE'] 		= $val['bill_date'];
					$EmailContent['ROWS']['CURRENCY'] 			= "IND";
					$EmailContent['ROWS']['INVOICE_AMOUNT'] 	= $val['balance_amount'];
					$EmailContent['ROWS']['BAL_OUTSTANDING'] 	= $val['balance_amount'];
					$EmailContent['ROWS']['LOCATION'] 			= $val['mrf_name'];
					$EmailContent['ROWS']['VENDOR_CODE'] 		= $val['vendor_code'];
					$EmailContent['ROWS']['VENDOR_NAME'] 		= $val['vendor_name'];
					$sendEmail 		= Mail::send("email-template.VendorOutstandingBalanceMail",$EmailContent, function ($message) use ($ToEmail,$FromEmail,$Subject){
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to(explode(",",$ToEmail));
							// $message->bcc(explode(",",$BccEMail));
							$message->subject($Subject);
							});	
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}