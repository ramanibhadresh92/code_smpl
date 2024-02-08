<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\Appoinment;
use App\Models\FocAppointment;
use Mail;

class AutoCancelAppointments extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'MarkAppointmentAsScheduledCancelled';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Mark Appointment As Scheduled Cancelled';

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

		$StartTime      = date("Y-m-d")." 00:00:00";
		$EndTime        = date("Y-m-d")." 23:59:59";
		
		// $StartTime 		= "2019-05-01 00:00:00";
		// $EndTime 		= "2019-05-23 23:59:59";

		$Appoinment 		= (new Appoinment)->getTable();
		$FocAppointment 	= (new FocAppointment)->getTable();
		$FocAppointmentSql 	= "	UPDATE $FocAppointment 
								INNER JOIN $Appoinment ON $FocAppointment.map_appointment_id = $Appoinment.appointment_id
								SET $FocAppointment.complete = ".FOC_APPOINTMENT_CANCEL.",
								$Appoinment.para_status_id = ".APPOINTMENT_SCHEDULED_CANCELLED.",
								$FocAppointment.updated_at = '".date("Y-m-d H:i:s")."',
								$Appoinment.updated_at = '".date("Y-m-d H:i:s")."'
								WHERE $FocAppointment.app_date_time BETWEEN '".$StartTime."' AND '".$EndTime."'
								AND $FocAppointment.complete = ".FOC_APPOINTMENT_PENDING;

		echo "\r\n--FocAppointmentSql::".$FocAppointmentSql."--\r\n";

		$AffectedRows = \DB::update($FocAppointmentSql);

		echo "\r\n--Auto Cancelled FOC Appointments ::".$AffectedRows."--\r\n";


		$AppointmentSql 	= "	UPDATE $Appoinment
								SET $Appoinment.para_status_id = ".APPOINTMENT_SCHEDULED_CANCELLED.",
								$Appoinment.updated_at = '".date("Y-m-d H:i:s")."'
								WHERE $Appoinment.app_date_time BETWEEN '".$StartTime."' AND '".$EndTime."'
								AND $Appoinment.para_status_id IN (".APPOINTMENT_SCHEDULED.",".APPOINTMENT_NOT_ASSIGNED.")";

		echo "\r\n--AppointmentSql::".$AppointmentSql."--\r\n";

		$AffectedRows = \DB::update($AppointmentSql);

		echo "\r\n--Auto Cancelled PAID Appointments ::".$AffectedRows."--\r\n";

		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}