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

class UpdateAppointmentTimeReportLog extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateAppointmentTimeReportLog';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Update Appointment TimeReport';

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
		$AppointmentRequestLog 	= AppointmentRequest::select("appointment_id","request_data")
									->whereBetween("created_date",[$StartDate,$EndDate])
									->orderby("created_date","ASC")
									->get();
		if (!empty($AppointmentRequestLog)) {
			foreach ($AppointmentRequestLog as $AppointmentRequest) {

				// dd($AppointmentRequest);

				$Collection_Data 		= json_decode($AppointmentRequest->request_data);

				// dd($Collection_Data);

				$Appointment 			= Appoinment::find($AppointmentRequest->appointment_id);

				if (!isset($Appointment->appointment_id)) continue;

				// echo "\r\n--appointment_id::".$AppointmentRequest->appointment_id."--\r\n";

				$starttime 				= $Appointment->app_date_time;
				$reached_time 			= "0000-00-00 00:00:00";
				$collection_dt 			= "0000-00-00 00:00:00";
				if (isset($Collection_Data->collection->reached_time)) {
					if (strtotime($Appointment->app_date_time) > strtotime($Collection_Data->collection->reached_time)) {
						$starttime 	= $Collection_Data->collection->reached_time;
					}
					$collection_dt 	= $Collection_Data->collection->collection_dt;
					$reached_time 	= $Collection_Data->collection->reached_time;
				} else {
					if (strtotime($Appointment->app_date_time) > strtotime($Collection_Data->reached_time)) {
						$starttime 	= $Collection_Data->reached_time;
					}
					$reached_time 	= $Collection_Data->reached_time;
					$collection_dt 	= $Collection_Data->collection_dt;
				}

				$collection_by = $Appointment->collection_by;
				if (isset($Collection_Data->adminuserid)) {
					$collection_by = $Collection_Data->adminuserid;
				}

				/** UPDATE APPOINTMENT ACCEPT TIME  */
				$AppointmentAcceptTime 	= AppointmentTimeReport::select("time_report_id")->where("para_report_status_id",APPOINTMENT_ACCEPTED)->where("appointment_id",$AppointmentRequest->appointment_id)->first();
				if (empty($AppointmentAcceptTime)) 
				{
					$clsappointmentreport 							= new AppointmentTimeReport();
		            $clsappointmentreport->appointment_id 			= $AppointmentRequest->appointment_id;
		            $clsappointmentreport->collection_by 			= $collection_by;
		            $clsappointmentreport->vehicle_id 				= $Appointment->vehicle_id;
		            $clsappointmentreport->starttime 				= $starttime;
		            $clsappointmentreport->endtime 				    = $reached_time;
		            $clsappointmentreport->para_report_status_id 	= APPOINTMENT_ACCEPTED;
		            $clsappointmentreport->created_by				= $collection_by;
		            $clsappointmentreport->updated_by				= $collection_by;
		            $clsappointmentreport->updated_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->created_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->save();
				} else {
					$UpdateFields = ['starttime'=>$starttime,"endtime"=>$reached_time];
					AppointmentTimeReport::where('time_report_id',$AppointmentAcceptTime->time_report_id)->update($UpdateFields);
				}
				/** UPDATE APPOINTMENT ACCEPT TIME  */

				/** UPDATE APPOINTMENT REACH TIME  */
				$AppointmentReachTime 	= AppointmentTimeReport::select("time_report_id")->where("para_report_status_id",COLLECTION_STARTED)->where("appointment_id",$AppointmentRequest->appointment_id)->first();
				if (empty($AppointmentReachTime)) 
				{
					$clsappointmentreport 							= new AppointmentTimeReport();
		            $clsappointmentreport->appointment_id 			= $AppointmentRequest->appointment_id;
		            $clsappointmentreport->collection_by 			= $collection_by;
		            $clsappointmentreport->vehicle_id 				= $Appointment->vehicle_id;
		            $clsappointmentreport->starttime 				= $starttime;
		            $clsappointmentreport->endtime 				    = $reached_time;
		            $clsappointmentreport->para_report_status_id 	= COLLECTION_STARTED;
		            $clsappointmentreport->created_by				= $collection_by;
		            $clsappointmentreport->updated_by				= $collection_by;
		            $clsappointmentreport->updated_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->created_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->save();
				} else {
					$UpdateFields = ['starttime'=>$starttime,"endtime"=>$reached_time];
					AppointmentTimeReport::where('time_report_id',$AppointmentReachTime->time_report_id)->update($UpdateFields);
				}
				/** UPDATE APPOINTMENT REACH TIME  */

				/** UPDATE APPOINTMENT COMPLETED TIME  */

				if (strtotime($reached_time) > strtotime($collection_dt)) {
					$date 			= $collection_dt." pm";
					$collection_dt 	= date("Y-m-d H:i:s",strtotime($date));
				}
				$AppointmentCompletedTime 	= AppointmentTimeReport::select("time_report_id")->where("para_report_status_id",COLLECTION_COMPLETED)->where("appointment_id",$AppointmentRequest->appointment_id)->first();
				if (empty($AppointmentCompletedTime)) 
				{
					$clsappointmentreport 							= new AppointmentTimeReport();
		            $clsappointmentreport->appointment_id 			= $AppointmentRequest->appointment_id;
		            $clsappointmentreport->collection_by 			= $collection_by;
		            $clsappointmentreport->vehicle_id 				= $Appointment->vehicle_id;
		            $clsappointmentreport->starttime 				= $reached_time;
		            $clsappointmentreport->endtime 				    = $collection_dt;
		            $clsappointmentreport->para_report_status_id 	= COLLECTION_COMPLETED;
		            $clsappointmentreport->created_by				= $collection_by;
		            $clsappointmentreport->updated_by				= $collection_by;
		            $clsappointmentreport->updated_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->created_at				= date("Y-m-d H:i:s");
		            $clsappointmentreport->save();
				} else {
					$UpdateFields = ['starttime'=>$reached_time,"endtime"=>$collection_dt];
					AppointmentTimeReport::where('time_report_id',$AppointmentCompletedTime->time_report_id)->update($UpdateFields);
				}
				/** UPDATE APPOINTMENT REACH TIME  */
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}