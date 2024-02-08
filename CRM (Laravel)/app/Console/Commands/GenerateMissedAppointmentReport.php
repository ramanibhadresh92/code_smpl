<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\BaseLocationMaster;
use App\Models\Appoinment;
use App\Models\FocAppointmentStatus;
use Mail;

class GenerateMissedAppointmentReport extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'SendMissedAppointmentReport';

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
		$Day 			= 0;
		$StartTime      = date("Y-m-d",strtotime("-$Day days"))." 00:00:00";
		$EndTime        = date("Y-m-d",strtotime("-$Day days"))." 23:59:59";

		$CompanyMaster  = new CompanyMaster;
		$arrCompany     = $CompanyMaster->select('company_id','company_name','company_email','certificate_logo')->where('status','Active')->get();
		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				$BaseLocationDetails = BaseLocationMaster::GetCompanyBaseLocations($Company->company_id);
                if (!empty($BaseLocationDetails))
                {
                    foreach ($BaseLocationDetails as $BaseLocationDetail)
                    {
						$arrFilter 				= array("city_id"=>explode(",",$BaseLocationDetail['Base_Location_City']),"foc"=>0);
						$PaidAppointment        = Appoinment::GetMissedAppointment($Company->company_id,$StartTime,$EndTime,$arrFilter);
						// echo "\r\n--StartTime::".$PaidAppointment."--\r\n";
						if (!empty($PaidAppointment))
						{
							$ToEmail        = (!empty($BaseLocationDetail['Report_Email_Address'])?$BaseLocationDetail['Report_Email_Address']:PAID_MISSED_REPORT_EMAIL_TO);
							$FromEmail      = array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
							$Subject        = $Company->company_name.' - '.$BaseLocationDetail['Base_Location'].' - Missed PAID Appointment Report for Date '._FormatedDate($StartTime,false,"d-M-Y");
							$Message        = " Hello All,<br />
												Please find attached list of Missed PAID Appointment at '".$BaseLocationDetail['Base_Location']."' for Date "._FormatedDate($StartTime,false,"d-M-Y")."<br />
												Thanks,<br />
												".$Company->company_name." Admin ";
							$EmailContent 	= array("Message"=>$Message);
							$sendEmail      = Mail::send("email-template.send_mail_blank_template",$EmailContent, function ($message) use ($ToEmail,$FromEmail,$Subject,$PaidAppointment) {
										$message->from($FromEmail['Email'], $FromEmail['Name']);
										$message->to(explode(",",$ToEmail));
										$message->bcc(explode(",",BCC_ALL_REPORTS_TO));
										$message->subject($Subject);
										if (!empty($PaidAppointment)) {
											foreach($PaidAppointment as $Attachment) {
												$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
											}
										}
									});
							if (!empty($PaidAppointment)) {
								foreach($PaidAppointment as $Attachment) {
									if (file_exists($Attachment)) {
										unlink($Attachment);
									}
								}
							}
						}

						$arrFilter 				= array("city_id"=>explode(",",$BaseLocationDetail['Base_Location_City']));
						$FocAppointmentPoints   = FocAppointmentStatus::GetMissedFocAppointmentLocation($Company->company_id,$StartTime,$EndTime,$arrFilter);
						// echo "\r\n--StartTime::".$FocAppointmentPoints."--\r\n";
						if (!empty($FocAppointmentPoints))
						{
							$ToEmail        = (!empty($BaseLocationDetail['Report_Email_Address'])?$BaseLocationDetail['Report_Email_Address']:FOC_MISSED_REPORT_EMAIL_TO);
							$FromEmail      = array('Email'=>$Company->company_email,'Name'=>$Company->company_name);
							$Subject        = $Company->company_name.' - '.$BaseLocationDetail['Base_Location'].' - Missed FOC Collection Point Report for Date '._FormatedDate($StartTime,false,"d-M-Y");
							$Message        = " Hello All,<br />
												Please find attached list of Missed FOC Collection Point at '".$BaseLocationDetail['Base_Location']."' Report for Date "._FormatedDate($StartTime,false,"d-M-Y")."<br />
												Thanks,<br />
												".$Company->company_name." Admin ";
							$EmailContent 	= array("Message"=>$Message);
							$sendEmail      = Mail::send("email-template.send_mail_blank_template",$EmailContent, function ($message) use ($ToEmail,$FromEmail,$Subject,$FocAppointmentPoints) {
										$message->from($FromEmail['Email'], $FromEmail['Name']);
										$message->to(explode(",",$ToEmail));
										$message->bcc(explode(",",BCC_ALL_REPORTS_TO));
										$message->subject($Subject);
										if (!empty($FocAppointmentPoints)) {
											foreach($FocAppointmentPoints as $Attachment) {
												$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
											}
										}
									});
							if (!empty($FocAppointmentPoints)) {
								foreach($FocAppointmentPoints as $Attachment) {
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