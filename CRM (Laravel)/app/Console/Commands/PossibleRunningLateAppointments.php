<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerMaster;
use App\Models\Appoinment;
use App\Models\VehicleMaster;
use App\Models\AppointmentCollection;
use App\Models\AppointmentCollectionDetail;
use App\Models\LateRunningAppointment;
use App\Facades\LiveServices;
use Mail;
use DB;

class PossibleRunningLateAppointments extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'PossibleRunningLateAppointments';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Console To Check Appointments Running Late';

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
		$Appoinment 		= new Appoinment;
		$CustomerMaster 	= new CustomerMaster;
		$StartTime 			= date("Y-m-d H").":00:00";
		$EndTime 			= date("Y-m-d")." 23:59:59";
		LateRunningAppointment::query()->truncate();
		$DistinctVehicle 	= "	SELECT distinct vehicle_id 
								FROM ".$Appoinment->getTable()." 
								WHERE ".$Appoinment->getTable().".app_date_time BETWEEN '$StartTime' AND '$EndTime'
								AND ".$Appoinment->getTable().".para_status_id = ".APPOINTMENT_SCHEDULED;
		// echo "\r\n--DistinctVehicle::".$DistinctVehicle."--\r\n";
		$SelectRes  = DB::select($DistinctVehicle);
		if (!empty($SelectRes)) {
			foreach ($SelectRes as $SelectRow) {
				$StartDate			= date("Y-m-d",strtotime($StartTime))." 00:00:00";
				$EndDate			= date("Y-m-d",strtotime($StartTime))." 23:59:59";
				$VehicleFillLevel 	= VehicleMaster::GetVehicleFillLevelPercentage($SelectRow->vehicle_id,$StartDate,$EndDate);
				echo "\r\n--Vehicle_ID::".$SelectRow->vehicle_id." -- VehicleFillLevel::".$VehicleFillLevel."--\r\n";
				if ($VehicleFillLevel > 70)
				{
					$StartTime 			= date("Y-m-d H:i:s",strtotime("+10 minutes"));
					$RunningLateApps 	= Appoinment::select(	$Appoinment->getTable().'.appointment_id',
																$Appoinment->getTable().'.app_date_time',
																$CustomerMaster->getTable().'.longitude',
																$CustomerMaster->getTable().'.lattitude',
																$Appoinment->getTable().'.customer_id')
											->join($CustomerMaster->getTable(),$CustomerMaster->getTable().".customer_id","=",$Appoinment->getTable().".customer_id")
											->where($Appoinment->getTable().'.vehicle_id',$SelectRow->vehicle_id)
											->where($Appoinment->getTable().'.para_status_id','=',APPOINTMENT_SCHEDULED)
											->whereBetween($Appoinment->getTable().'.app_date_time' ,array($StartTime,$EndTime));
					// $ReportQuery = LiveServices::toSqlWithBinding($RunningLateApps,true);
					// echo "\r\n--ReportQuery::".$ReportQuery."--\r\n";
					$Appointments = $RunningLateApps->get()->toArray();
					if (!empty($Appointments)) 
					{
						$LateAppointments 	= array();
						$PrevLat 			= 0;
						$PrevLong 			= 0;
						$DefaultLateMins 	= 60;
						foreach ($Appointments as $Appointment)
						{
							$TravelTime = 0;
							if (!empty($PrevLong) && !empty($PrevLat)) {
								$Distance 		= distance($PrevLat, $PrevLong, $Appointment['lattitude'], $Appointment['longitude']);
								echo "\r\n--Distance::".$Distance."--\r\n";
								$TravelTime 	= ceil($Distance * 60 / VEHICLE_HOUR_SPEED);
								echo "\r\n--TravelTime::".$TravelTime."--\r\n";
							}
							$LateMinutes		= $DefaultLateMins + $TravelTime;
							$NEW_APP_TIME 		= date("Y-m-d H:i:s",strtotime("+$LateMinutes minutes",strtotime($Appointment['app_date_time'])));
							echo "\r\n--NEW_APP_TIME::".$NEW_APP_TIME."--\r\n";
							$LateAppointments[] = array("vehicle_id"=>$SelectRow->vehicle_id,
														"appointment_id"=>$Appointment['appointment_id'],
														"customer_id"=>$Appointment['customer_id'],
														"app_date_time"=>$Appointment['app_date_time'],
														"new_date_time"=>$NEW_APP_TIME,
														"created_at"=>date("Y-m-d H:i:s"));
							$PrevLong 	= $Appointment['longitude'];
							$PrevLat 	= $Appointment['lattitude'];
						}
						if (!empty($LateAppointments)) {
							LateRunningAppointment::insert($LateAppointments);
						}
					}
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}