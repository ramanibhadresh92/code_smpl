<?php

namespace App\Jobs;

use App\Models\CompanyMaster;
use App\Models\CustomerMaster;
use App\Models\ViewRequestApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\VehicleMaster;
use Log;
use Mail;
use DB;
use PDF;

class AdminApprovalEmail implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $customer;
	protected $company_detail;
	protected $result;
	protected $approval;
	protected $adminuser;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($result, $approval,$adminuser)
	{
		$this->result = $result;
		$this->approval = $approval;
		$this->adminuser = $adminuser;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{

		$result = $this->result;
		$adminuser = $this->adminuser;
		if($result->module_id == 1){
			$datachange         = "vehicle";
			$customer           = VehicleMaster::find($result->vehicle_id);
			($customer) ? $id   = $customer->vehicle_id : $id = 0;
		}else{
			$datachange         = "customer";
			$customer           = CustomerMaster::find($result->customer_id);
			($customer) ? $id   = $customer->customer_id : $id = 0;
		}

		$company_detail = CompanyMaster::find($result->company_id);
		$result         = ViewRequestApproval::getById($this->approval);

		$FILENAME = "ADMIN_APPROVAL_" . $id . ".pdf";
		$pdf = PDF::loadView('email-template.admin_approval', compact('customer', 'company_detail', 'result','adminuser'));
		$pdf->setPaper("A4", "portrait");
		ob_get_clean();

		$path = public_path("/") . PATH_COLLECTION_RECIPT_PDF;
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		$pdf->save(public_path("/") . PATH_COLLECTION_RECIPT_PDF . $FILENAME, true);
		$filePath   = public_path("/") . PATH_COLLECTION_RECIPT_PDF . $FILENAME;

		$Email_text  = "Hello User,<br /><br />";
		$Email_text .= "Please find attachment for $datachange data change request approval.<br /><br />";
		$Email_text .= "Thanks,<br /><br />" . $company_detail->company_name;

		try {
			$sendEmail = Mail::send('email-template.admin_approval',compact('customer', 'company_detail', 'result','adminuser'), function ($message) use ($filePath, $company_detail, $Email_text, $result, $adminuser,$datachange,$FILENAME) {
				//$message->setBody($Email_text, 'text/html');
				$message->from($company_detail->company_email, $company_detail->company_name);
				$message->to($adminuser->email);
				$message->subject($company_detail->company_name . " - $datachange data change Approval Request");
				/*$message->attach($filePath, [
					'as' => $FILENAME,
					'mime' => 'application/pdf'
				]);*/
			});
			if (file_exists($filePath)) @unlink($filePath);
		}catch(\Exception $ex){
			\Log::error($ex->getMessage());
		}


	}

}
