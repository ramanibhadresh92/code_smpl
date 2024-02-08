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

class InsertAppointmentTimeReportLog extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'InsertAppointmentTimeReportLog';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Insert Appointment TimeReport';

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

		$SelectSql	= "	SELECT APP.appointment_id,appointment_request.id
						FROM appoinment APP
						LEFT JOIN appointment_collection ON APP.appointment_id = appointment_collection.appointment_id
						LEFT JOIN appointment_request ON APP.appointment_id = appointment_request.appointment_id
						WHERE appointment_request.id IS NULL
						AND appointment_collection.collection_dt BETWEEN '$StartDate' AND '$EndDate'
						ORDER BY APP.appointment_id ASC";
		echo "\r\n--SelectSql::".$SelectSql."--\r\n";
		$SelectRes  = DB::select($SelectSql);
		if (!empty($SelectRes)) {
			foreach ($SelectRes as $SelectRows) {

				$Appointment 			= Appoinment::find($SelectRows->appointment_id);

				if (!isset($Appointment->appointment_id)) continue;
				
				$AppointmentCollection 	= AppointmentCollection::where("appointment_id",$Appointment->appointment_id)->first();
				
				if (!isset($AppointmentCollection->collection_id)) continue;

				$starttime 				= $Appointment->app_date_time;
				$reached_time 			= $AppointmentCollection->collection_dt;
				$collection_dt 			= $AppointmentCollection->collection_dt;

				/** UPDATE APPOINTMENT ACCEPT TIME  */
				$AppointmentAcceptTime 	= AppointmentTimeReport::select("time_report_id")->where("para_report_status_id",APPOINTMENT_ACCEPTED)->where("appointment_id",$Appointment->appointment_id)->first();
				// dd($AppointmentAcceptTime);
				if (empty($AppointmentAcceptTime)) 
				{
					$clsappointmentreport 							= new AppointmentTimeReport();
		            $clsappointmentreport->appointment_id 			= $Appointment->appointment_id;
		            $clsappointmentreport->collection_by 			= $AppointmentCollection->collection_by;
		            $clsappointmentreport->vehicle_id 				= $Appointment->vehicle_id;
		            $clsappointmentreport->starttime 				= $starttime;
		            $clsappointmentreport->endtime 				    = $reached_time;
		            $clsappointmentreport->para_report_status_id 	= APPOINTMENT_ACCEPTED;
		            $clsappointmentreport->created_by				= $AppointmentCollection->collection_by;
		            $clsappointmentreport->updated_by				= $AppointmentCollection->collection_by;
		            $clsappointmentreport->updated_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->created_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->save();
				} else {
					$UpdateFields = ['starttime'=>$starttime,"endtime"=>$reached_time,"vehicle_id"=>$Appointment->vehicle_id,"collection_by"=>$AppointmentCollection->collection_by];
					AppointmentTimeReport::where('time_report_id',$AppointmentAcceptTime->time_report_id)->update($UpdateFields);
				}
				/** UPDATE APPOINTMENT ACCEPT TIME  */

				/** UPDATE APPOINTMENT REACH TIME  */
				$AppointmentReachTime 	= AppointmentTimeReport::select("time_report_id")->where("para_report_status_id",COLLECTION_STARTED)->where("appointment_id",$Appointment->appointment_id)->first();
				if (empty($AppointmentReachTime)) 
				{
					$clsappointmentreport 							= new AppointmentTimeReport();
		            $clsappointmentreport->appointment_id 			= $Appointment->appointment_id;
		            $clsappointmentreport->collection_by 			= $AppointmentCollection->collection_by;
		            $clsappointmentreport->vehicle_id 				= $Appointment->vehicle_id;
		            $clsappointmentreport->starttime 				= $starttime;
		            $clsappointmentreport->endtime 				    = $reached_time;
		            $clsappointmentreport->para_report_status_id 	= COLLECTION_STARTED;
		            $clsappointmentreport->created_by				= $AppointmentCollection->collection_by;
		            $clsappointmentreport->updated_by				= $AppointmentCollection->collection_by;
		            $clsappointmentreport->updated_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->created_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->save();
				} else {
					$UpdateFields = ['starttime'=>$starttime,"endtime"=>$reached_time,"vehicle_id"=>$Appointment->vehicle_id,"collection_by"=>$AppointmentCollection->collection_by];
					AppointmentTimeReport::where('time_report_id',$AppointmentReachTime->time_report_id)->update($UpdateFields);
				}
				/** UPDATE APPOINTMENT REACH TIME  */

				/** UPDATE APPOINTMENT COMPLETED TIME  */

				if (strtotime($reached_time) > strtotime($collection_dt)) {
					$date 			= $collection_dt." pm";
					$collection_dt 	= date("Y-m-d H:i:s",strtotime($date));
				}
				$AppointmentCompletedTime 	= AppointmentTimeReport::select("time_report_id")->where("para_report_status_id",COLLECTION_COMPLETED)->where("appointment_id",$Appointment->appointment_id)->first();
				if (empty($AppointmentCompletedTime)) 
				{
					$clsappointmentreport 							= new AppointmentTimeReport();
		            $clsappointmentreport->appointment_id 			= $Appointment->appointment_id;
		            $clsappointmentreport->collection_by 			= $AppointmentCollection->collection_by;
		            $clsappointmentreport->vehicle_id 				= $Appointment->vehicle_id;
		            $clsappointmentreport->starttime 				= $reached_time;
		            $clsappointmentreport->endtime 				    = $collection_dt;
		            $clsappointmentreport->para_report_status_id 	= COLLECTION_COMPLETED;
		            $clsappointmentreport->created_by				= $AppointmentCollection->collection_by;
		            $clsappointmentreport->updated_by				= $AppointmentCollection->collection_by;
		            $clsappointmentreport->updated_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->created_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->save();
				} else {
					$UpdateFields = ['starttime'=>$reached_time,"endtime"=>$collection_dt,"vehicle_id"=>$Appointment->vehicle_id,"collection_by"=>$AppointmentCollection->collection_by];
					AppointmentTimeReport::where('time_report_id',$AppointmentCompletedTime->time_report_id)->update($UpdateFields);
				}
				/** UPDATE APPOINTMENT REACH TIME  */
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}