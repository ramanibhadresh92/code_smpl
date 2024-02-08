<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\PurchaseCreditDebitNoteMaster;
use Mail;
class SendPurchaseCreditDebitNoteApprovalEmail extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SendPurchaseCreditDebitNoteApprovalEmail';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Credit Debit Note Approval Email for purchase transaction';

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

		$WmInvoicesCreditDebitNotes = new PurchaseCreditDebitNoteMaster();
		$created_at 				= date("Y-m-d",strtotime("-4 days"))." 00:00:00";
		$PendingApprovalRows 		= $WmInvoicesCreditDebitNotes->select("id","appointment_id","first_level_approved_by","first_level_approved_date","email_sent","collection_id","status")
										->whereIn("status",[0,1])
										->whereNotIn("email_sent",[2])
										->where("created_at",">=",$created_at)
										->get()
										->toArray();
		if (!empty($PendingApprovalRows)) {
			foreach($PendingApprovalRows as $PendingApprovalRow)
			{
				$Level 			= ($PendingApprovalRow['status'] == 0)?"first":"final";
				$creditNoteId 	= $PendingApprovalRow['id'];
				$appointment_id = $PendingApprovalRow['appointment_id'];
				$AdminUserRight = ($Level == "first")?PURCHASE_CN_DN_FIRST_LEVEL_APPROVAL:PURCHASE_CN_DN_FINAL_LEVEL_APPROVAL;
				echo "\r\n".$Level." --> ".$creditNoteId." --> ".$appointment_id." --> ".$AdminUserRight." -->".$PendingApprovalRow['email_sent']."\r\n";
				if ($Level == "first" && ($PendingApprovalRow['email_sent'] == 0 || empty($PendingApprovalRow['email_sent']))) {
					PurchaseCreditDebitNoteMaster::SendCreditDebitNoteApprovalEmail($Level,$creditNoteId,$appointment_id,$AdminUserRight);
					PurchaseCreditDebitNoteMaster::where("id",$PendingApprovalRow['id'])->update(['email_sent'=>1]);
				} else if ($Level == "final" && ($PendingApprovalRow['email_sent'] == 1 || $PendingApprovalRow['email_sent'] == 0 || empty($PendingApprovalRow['email_sent']))) {
					PurchaseCreditDebitNoteMaster::SendCreditDebitNoteApprovalEmail($Level,$creditNoteId,$appointment_id,$AdminUserRight);
					PurchaseCreditDebitNoteMaster::where("id",$PendingApprovalRow['id'])->update(['email_sent'=>2]);
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}