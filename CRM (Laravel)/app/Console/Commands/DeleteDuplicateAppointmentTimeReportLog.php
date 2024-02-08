<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerMaster;
use App\Models\Appoinment;
use App\Models\VehicleMaster;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use App\Models\AppointmentRequest;
use App\Models\AppointmentTimeReport;
use Mail;
use DB;

class DeleteDuplicateAppointmentTimeReportLog extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'DeleteDuplicateAppointmentTimeReportLog';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Delete Duplicate Appointment TimeReport';

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
		$StartDate				= date("Y-m-d")." 00:00:00";
		$EndDate				= date("Y-m-d")." 23:59:59";
		
		$StartDate				= "2019-07-01 00:00:00";
		$EndDate				= "2019-07-12 23:59:59";

		$S_9001_CNT 			= 0;
		$S_9002_CNT 			= 0;
		$S_9003_CNT 			= 0;

		$SelectSql	= "	SELECT APP.appointment_id,
						CASE WHEN 1=1 THEN (
							SELECT COUNT(time_report_id)
							FROM appointment_time_report
							WHERE appointment_time_report.appointment_id = APP.appointment_id
							AND appointment_time_report.para_report_status_id = 9001
							GROUP BY appointment_time_report.appointment_id
						) END AS S_9001_CNT,
						CASE WHEN 1=1 THEN (
							SELECT COUNT(time_report_id)
							FROM appointment_time_report
							WHERE appointment_time_report.appointment_id = APP.appointment_id
							AND appointment_time_report.para_report_status_id = 9002
							GROUP BY appointment_time_report.appointment_id
						) END AS S_9002_CNT,
						CASE WHEN 1=1 THEN (
							SELECT COUNT(time_report_id)
							FROM appointment_time_report
							WHERE appointment_time_report.appointment_id = APP.appointment_id
							AND appointment_time_report.para_report_status_id = 9003
							GROUP BY appointment_time_report.appointment_id
						) END AS S_9003_CNT
						FROM appoinment APP 
						WHERE APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
						HAVING (S_9001_CNT > 1 OR S_9002_CNT > 1 OR S_9003_CNT > 1)
						ORDER BY APP.appointment_id ASC";
		echo "\r\n--SelectSql::".$SelectSql."--\r\n";
		$SelectRes  = DB::select($SelectSql);
		if (!empty($SelectRes)) {
			foreach ($SelectRes as $SelectRows) {

				if ($SelectRows->S_9001_CNT > 1)
				{
					$SelectSql2	= "	SELECT appointment_id, COUNT(time_report_id) AS CNT,
									GROUP_CONCAT(time_report_id ORDER BY time_report_id ASC) as time_report_ids
									FROM appointment_time_report
									WHERE appointment_time_report.appointment_id = ".$SelectRows->appointment_id."
									AND appointment_time_report.para_report_status_id = 9001
									HAVING CNT > 1";
					$SelectRes2  = DB::select($SelectSql2);
					if (!empty($SelectRes2)) 
					{
						foreach ($SelectRes2 as $SelectRow2) {
							$time_report_ids = explode(",",$SelectRow2->time_report_ids);
							if (!empty($time_report_ids) && count($time_report_ids) > 1) 
							{
								array_shift($time_report_ids);
								if (!empty($time_report_ids)) {
									AppointmentTimeReport::whereIn("time_report_id",$time_report_ids)->delete();
									$S_9001_CNT++;
								}
							}
						}
					}
				}
				if ($SelectRows->S_9002_CNT > 1)
				{
					$SelectSql2	= "	SELECT appointment_id, COUNT(time_report_id) AS CNT,
									GROUP_CONCAT(time_report_id ORDER BY time_report_id ASC) as time_report_ids
									FROM appointment_time_report
									WHERE appointment_time_report.appointment_id = ".$SelectRows->appointment_id."
									AND appointment_time_report.para_report_status_id = 9002
									HAVING CNT > 1";
					$SelectRes2  = DB::select($SelectSql2);
					if (!empty($SelectRes2)) 
					{
						foreach ($SelectRes2 as $SelectRow2) {
							$time_report_ids = explode(",",$SelectRow2->time_report_ids);
							if (!empty($time_report_ids) && count($time_report_ids) > 1) 
							{
								array_shift($time_report_ids);
								if (!empty($time_report_ids)) {
									AppointmentTimeReport::whereIn("time_report_id",$time_report_ids)->delete();
									$S_9002_CNT++;
								}
							}
						}
					}
				}
				if ($SelectRows->S_9003_CNT > 1)
				{
					$SelectSql2	= "	SELECT appointment_id, COUNT(time_report_id) AS CNT,
									GROUP_CONCAT(time_report_id ORDER BY time_report_id ASC) as time_report_ids
									FROM appointment_time_report
									WHERE appointment_time_report.appointment_id = ".$SelectRows->appointment_id."
									AND appointment_time_report.para_report_status_id = 9003
									HAVING CNT > 1";
					$SelectRes2  = DB::select($SelectSql2);
					if (!empty($SelectRes2)) 
					{
						foreach ($SelectRes2 as $SelectRow2) {
							$time_report_ids = explode(",",$SelectRow2->time_report_ids);
							if (!empty($time_report_ids) && count($time_report_ids) > 1) 
							{
								array_shift($time_report_ids);
								if (!empty($time_report_ids)) {
									AppointmentTimeReport::whereIn("time_report_id",$time_report_ids)->delete();
									$S_9003_CNT++;
								}
							}
						}
					}
				}
			}
		}
		echo "\r\n--S_9001_CNT::".$S_9001_CNT."--\r\n";
		echo "\r\n--S_9002_CNT::".$S_9002_CNT."--\r\n";
		echo "\r\n--S_9003_CNT::".$S_9003_CNT."--\r\n";
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}