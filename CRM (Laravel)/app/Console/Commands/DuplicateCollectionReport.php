<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\AppointmentCollectionDetail;

class DuplicateCollectionReport extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SendDuplicateCollectionReport';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Duplicate Collection Report';

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
		$CompanyMaster  = new CompanyMaster;
		$arrCompany     = $CompanyMaster->select('company_id','company_name','company_email')->where('status','Active')->get();
		$StartTime      = date("Y-m-d")." 00:00:00";
		$EndTime        = date("Y-m-d")." 23:59:59";
		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				echo "\r\n--Company ID::".$Company->company_id." StartTime ::".$StartTime." EndTime ::".$EndTime."--\r\n";
				$arrFilter 			= array("AdminUserCompanyID"=>$Company->company_id);
				$arrDuplicateCollection = AppointmentCollectionDetail::GetDuplicateCollection($StartTime,$EndTime,false,$arrFilter);
				if (!empty($arrDuplicateCollection['duplicateRows']))
				{
					$ToEmail        = DUPLICATE_COLLECTION_REPORT_TO;
					$FromEmail      = array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
					$DuplicateAmt   = isset($arrDuplicateCollection['TotalPrice'])?" - Amount - ".$arrDuplicateCollection['TotalPrice']:"";
                    $Subject        = $Company->company_name.' - Duplicate Collection Report for Date '._FormatedDate($StartTime,false,"d-M-Y").$DuplicateAmt;
					AppointmentCollectionDetail::SendDuplicateCollectionEmail($arrDuplicateCollection,$FromEmail,$ToEmail,$Subject);
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}