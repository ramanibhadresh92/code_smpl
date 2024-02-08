<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Appoinment;
use App\Facades\LiveServices;
use Mail;
class SendPurchaseInvoiceApprovalEmail extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SendPurchaseInvoiceApprovalEmail';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Purchase Invoice Approval Email';

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
		$Appoinment 			= new Appoinment();
		$created_at 			= date("Y-m-d",strtotime("-4 days"))." 00:00:00";
		$PendingApprovalRows 	= $Appoinment->select("appoinment.appointment_id")
										->join('customer_master','appoinment.customer_id','=','customer_master.customer_id')
										->whereIn("appoinment.invoice_approved",[0])
										->whereIn("appoinment.approval_email_sent",[0])
										->where("appoinment.invoice_media_id",">",0)
										->where("appoinment.created_at",">=",$created_at)
										->where("customer_master.ctype",CUSTOMER_TYPE_BULK_AGGREGATOR)
										->get()
										->toArray();
		if (!empty($PendingApprovalRows)) 
		{
			foreach($PendingApprovalRows as $PendingApprovalRow)
			{
				Appoinment::SendEmailInvoicePendingForApproval($PendingApprovalRow['appointment_id'],true);
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}