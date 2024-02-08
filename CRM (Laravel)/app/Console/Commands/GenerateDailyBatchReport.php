<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\WmDepartment;
use App\Models\WmBatchMaster;
use App\Models\BaseLocationMaster;
use App\Models\BaseLocationCityMapping;
use Mail;

class GenerateDailyBatchReport extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SendDailyBatchReport';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Missed Appointment Report';

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
		$Day 			= "0";
		$StartTime      = date("Y-m-d",strtotime("-$Day days"))." 00:00:00";
		$EndTime        = date("Y-m-d",strtotime("-$Day days"))." 23:59:59";

		echo "\r\n--StartTime::".$StartTime." EndTime::".$EndTime."--\r\n";

		$CompanyMaster  			= new CompanyMaster;
		$WmDepartment  				= new WmDepartment;
        $BaseLocationCityMapping    = new BaseLocationCityMapping;
		$arrCompany     			= $CompanyMaster->select('company_id','company_name','company_email','certificate_logo')->where('status','Active')->get();
		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				$arrMRF     = $WmDepartment->select('id','department_name','location_id')
                                            ->where('company_id',$Company->company_id)
                                            ->where('status','1')->get();
                if (!empty($arrMRF))
                {
                    foreach($arrMRF as $MRF)
                    {
                        echo "\r\n--Company ID::".$Company->company_id." MRF ID::".$MRF->id."--\r\n";

                        $BaseLocation       	= $BaseLocationCityMapping->select('base_location_id')
                                                                        ->where('company_id',$Company->company_id)
                                                                        ->where('city_id',$MRF->location_id)
                                                                        ->first();
                        $BaseLocationDetails    = BaseLocationMaster::find($BaseLocation->base_location_id);
                        $arrBatchReport 		= WmBatchMaster::GetBatchSummary($Company->company_id,$MRF->id,$StartTime,$EndTime);
                        if (!empty($arrBatchReport))
						{
							$Attachments    = array($arrBatchReport);
							$ToEmail        = !empty($BaseLocationDetails->contact_email_address)?$BaseLocationDetails->contact_email_address:BATCH_SUMMARY_EMAIL_TO;
							$FromEmail      = array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
							$Subject        = $Company->company_name.' - Batch Summary Report Of '.$MRF->department_name.' for Date '._FormatedDate($StartTime,false,"d-M-Y");
							$Message        = " Hello All,<br />
												Please find attached Batch Summary Report for Date "._FormatedDate($StartTime,false,"d-M-Y")." unloaded at ".$MRF->department_name."<br />
												Thanks,<br />
												".$Company->company_name." Admin ";
							$EmailContent 	= array("Message"=>$Message);
							$sendEmail      = Mail::send("email-template.send_mail_blank_template",$EmailContent, function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
													$message->from($FromEmail['Email'], $FromEmail['Name']);
													$message->to(explode(",",$ToEmail));
													$message->bcc(explode(",",BCC_ALL_REPORTS_TO));
													$message->subject($Subject);
													if (!empty($Attachments)) {
														foreach($Attachments as $Attachment) {
															$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
														}
													}
												});
							if (!empty($Attachments)) {
								foreach($Attachments as $Attachment) {
									if (file_exists($Attachment)) {
										unlink($Attachment);
									}
								}
							}
						}
                    }
                }
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}