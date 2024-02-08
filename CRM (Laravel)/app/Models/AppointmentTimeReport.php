<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;
class AppointmentTimeReport extends Model implements Auditable
{
	protected 	$table 		=	'appointment_time_report';
	protected 	$primaryKey =	'time_report_id'; // or null
	protected 	$guarded 	=	['time_report_id'];
	public      $timestamps =   true;
	use AuditableTrait;

	public static function InsertTimeReportLog($appointmentId,$vehicle_id,$collection_by,$app_date_time,$reach_time,$collection_time,$created_by = 0,$updated_by = 0)
	{
		$clsappointmentreport                           = new self();
		$clsappointmentreport->appointment_id           = $appointmentId;
		$clsappointmentreport->collection_by            = $collection_by;
		$clsappointmentreport->vehicle_id               = $vehicle_id;
		$clsappointmentreport->starttime                = $app_date_time;
		$clsappointmentreport->endtime                  = $reach_time;
		$clsappointmentreport->para_report_status_id    = APPOINTMENT_ACCEPTED;
		$clsappointmentreport->created_by               = $created_by;
		$clsappointmentreport->updated_by               = $updated_by;
		$clsappointmentreport->save();


		$clsappointmentreport                           = new self();
		$clsappointmentreport->appointment_id           = $appointmentId;
		$clsappointmentreport->collection_by            = $collection_by;
		$clsappointmentreport->vehicle_id               = $vehicle_id;
		$clsappointmentreport->starttime                = $reach_time;
		$clsappointmentreport->endtime                  = $reach_time;
		$clsappointmentreport->para_report_status_id    = COLLECTION_STARTED;
		$clsappointmentreport->created_by               = $created_by;
		$clsappointmentreport->updated_by               = $updated_by;
		$clsappointmentreport->save();


		$clsappointmentreport                           = new self();
		$clsappointmentreport->appointment_id           = $appointmentId;
		$clsappointmentreport->collection_by            = $collection_by;
		$clsappointmentreport->vehicle_id               = $vehicle_id;
		$clsappointmentreport->starttime                = $reach_time;
		$clsappointmentreport->endtime                  = $collection_time;
		$clsappointmentreport->para_report_status_id    = COLLECTION_COMPLETED;
		$clsappointmentreport->created_by               = $created_by;
		$clsappointmentreport->updated_by               = $updated_by;
		$clsappointmentreport->save();
	}


	/* ORIGINAL FUNCTION -11 JULY 2019
	NOTE IF ANY ISSUE OCCURE UNCOMMENT ORIGINAL FUNCTION AND COMMENT BUG FIXING FUNCTION
	*/
	// public static function saveAppointmentReport($appointmentId,$collectionBy,$status){
	//             $clsappointmentreport 							= new self();
				// $clsappointmentreport->appointment_id 			= $appointmentId;
				// $clsappointmentreport->collection_by 			= (!empty($collectionBy)) ? $collectionBy : 0;
				// $clsappointmentreport->starttime 				= date("Y-m-d H:i:s");
				// $clsappointmentreport->para_report_status_id 	= $status;
				// $clsappointmentreport->created_by				= Auth()->user()->adminuserid;
	//             $clsappointmentreport->updated_by				= Auth()->user()->adminuserid;
	//             $clsappointmentreport->save();
	//             return $clsappointmentreport;
	// }
	/* TIME REPORT BUG FIXING FUNCTION - 11 JULY 2019*/
	public static function saveAppointmentReport($appointmentId,$collectionBy,$status,$endTime = ''){
		return false;
		$clsappointmentreport                           = new self();
		$clsappointmentreport->appointment_id           = $appointmentId;
		$clsappointmentreport->collection_by            = (!empty($collectionBy)) ? $collectionBy : 0;
		$clsappointmentreport->starttime                = date("Y-m-d H:i:s");
		$clsappointmentreport->para_report_status_id    = $status;
		$clsappointmentreport->created_by               = Auth()->user()->adminuserid;
		$clsappointmentreport->updated_by               = Auth()->user()->adminuserid;
		if(!empty($endTime)){
			$clsappointmentreport->endtime              = $endTime;
		}
		$clsappointmentreport->save();
		return $clsappointmentreport;
	}
	/*
	Use     :  Appointment collection done (ORIGINAL FUNCTION )
	Author  :  Axay Shah
	Date    :  30 Nov,2018
	*/
	// public static function appointmentCollectionDone($appointmentId = 0){

	//     $table      =   (new static)->getTable();
	//     $createdBy  =   Auth()->user()->adminuserid;
	//     $date       =   date("Y-m-d H:i:s");
	//     $arrFilter 	=   array("appointment_id" =>$appointmentId, "para_report_status_id" => COLLECTION_STARTED);

	//     $getData    =   self::retrieveReportDataByAppointmentId($arrFilter);
	//     if($getData)
	//     {
	//             $reportId =  $getData->time_report_id;


	//             if($reportId > 0 && $getData->endtime == APPOINTMENT_REPORT_DEFAULT_END_TIME){
	//             $update =   self::updateAppointmentReportDaynamically($arrFilter,$reportId);
	//             // $sql    =   DB::select("INSERT INTO ".$table."
	//             //             (appointment_id,collection_by,starttime,endtime,para_report_status_id,
	//             //             created_by,created_at,updated_by,updated_at)
	//             //             (SELECT appointment_id,collection_by,'".$date."','".$date."','".COLLECTION_COMPLETED."',
	//             //             '".$createdBy."', '".$date."','".$createdBy."','".$date."'
	//             //             FROM ".$table."
	//             //             WHERE time_report_id = '".$time_report_id."')");
	//             $appointmentTime    = self::find($reportId);
	//             if($appointmentTime){
	//                     $insert = self::saveAppointmentReport($appointmentTime->appointment_id,$appointmentTime->collection_by,COLLECTION_COMPLETED);
	//             }
	//             }elseif(empty($getData->time_report_id)){
	//             // $sql    =   DB::select("INSERT INTO ".$table."
	//             //             (appointment_id,collection_by,starttime,endtime,para_report_status_id,
	//             //             created_by,created_at,updated_by,updated_at)
	//             //             (SELECT appointment_id,collection_by,'".$date."','".$date."','".COLLECTION_STARTED."',
	//             //             '".$createdBy."', '".$date."','".$createdBy."','".$date."'
	//             //             FROM appoinment WHERE appointment_id = '".$appointment_id."')");
	//             $appointment = Appoinment::find($appointment_id);
	//             if($appointment){
	//                 $insert = self::saveAppointmentReport($appointment_id,$appointment->collection_by,COLLECTION_STARTED);
	//             }
	//             }

	//     }
	// }
	/*
	Use     :  Appointment collection done
	Author  :  Axay Shah
	Date    :  19 JULY 2019
	*/
	public static function appointmentCollectionDone($appointmentId = 0,$FromWeb = false){
		return false;
		$table      =   (new static)->getTable();
		$createdBy  =   Auth()->user()->adminuserid;
		$date       =   date("Y-m-d H:i:s");
		$arrFilter  =   array("appointment_id" =>$appointmentId, "para_report_status_id" => COLLECTION_STARTED);
		$now        =   "";
		$getData    =   self::retrieveReportDataByAppointmentId($arrFilter);
		if($getData)
		{
			$reportId =  $getData->time_report_id;
			if($reportId > 0 && ($getData->endtime == APPOINTMENT_REPORT_DEFAULT_END_TIME || $FromWeb == true)){
				if($FromWeb){
				   $arrFilter   =   array("appointment_id" =>$appointmentId, "para_report_status_id" => COLLECTION_STARTED,"endtime"=>$getData->endtime);
				}
				$update =   self::updateAppointmentReportDaynamically($arrFilter,$reportId);
				$appointmentTime    = self::find($reportId);
					if($appointmentTime){
						if($FromWeb){
							$now = $date;
						}
						$insert = self::saveAppointmentReport($appointmentTime->appointment_id,$appointmentTime->collection_by,COLLECTION_COMPLETED,$now);
					}
				}elseif(empty($getData->time_report_id)){
					$appointment = Appoinment::find($appointment_id);
				if($appointment){
					$insert = self::saveAppointmentReport($appointment_id,$appointment->collection_by,COLLECTION_STARTED);
				}
			}
		}
	}

	/*
	USE     : UPDATE DAYNAMIC APPOINTMENT TIME REPORT
	AUTHOR  : AXAY SHAH
	DATE    : 30 NOV,2018
	*/
	public static function updateAppointmentReportDaynamically($arrFill="",$reportId = 0){
		return false;
		if(is_array($arrFill)){
			self::where('time_report_id',$reportId)->update($arrFill);
		}
	}
	/*
	Use     : retrieveReportDataByAppointmentId
	Author  : AXAY SHAH
	Date    : 30 NOV,2018
	*/
	public static function retrieveReportDataByAppointmentId($arrFilter=""){
		// $query 	= self::query();
		// dd($arrFilter);
		if (is_array($arrFilter) && !empty($arrFilter)) {
			$row = self::where($arrFilter)->first();
		} else{
			$row = self::all();
		}

		return $row;
	}


	/**
	* Use       : Appointment Destination reached (ORIGINAL FUNCTION )
	* Author    : Axay Shah
	* Date      : 10 Dec,2018
	*/
	// public static function appointmentDestinationReached($appointment_id=0)
	// {
 //        $now        = date("Y-m-d H:i:s");
 //        $created_By = Auth()->user()->adminuserid;
	// 	$arrFilter 	= array("appointment_id"        => $appointment_id,
	// 						"para_report_status_id" => APPOINTMENT_ACCEPTED);
 //        $appointmentReport = self::retrieveReportDataByAppointmentId($arrFilter);

 //        if (!$appointmentReport) {
 //            $appointment = Appoinment::find($appointment_id);
 //            if($appointment){
 //                $insert = self::saveAppointmentReport($appointment_id,$appointment->collection_by,APPOINTMENT_ACCEPTED);
 //            }

 //            // $InsertSql = "INSERT INTO ".(new static)->getTable()."
	// 		// 			(appointment_id,collection_by,starttime,endtime,para_report_status_id,
	// 		// 			created_by,created_at,updated_by,updated_at)
	// 		// 			(SELECT appointment_id,collection_by,'".$now."','".$now."','".APPOINTMENT_ACCEPTED."',
	// 		// 			'".$created_By."', '".$now."','".$created_By."','".$now."'
 //            //             FROM appoinment WHERE appointment_id = '".$appointment_id."')";
 //        }else if ($appointmentReport->time_report_id > 0 && $appointmentReport->endtime == "0000-00-00 00:00:00") {

 //            $array              = array('endtime'=>$now,'updated_by'=>$appointmentReport->updated_by,'updated_at'=>$now);
 //            $UpdateReport       = self::updateAppointmentReportDaynamically($array,$appointmentReport->time_report_id);
 //            $appointmentTime    = self::find($appointmentReport->time_report_id);
 //            if($appointmentTime){
 //                $insert = self::saveAppointmentReport($appointmentTime->appointment_id,$appointmentTime->collection_by,COLLECTION_STARTED);
 //            }

 //            // $InsertSql = DB::select("INSERT INTO appointment_time_report
	// 		// 			(appointment_id,collection_by,starttime,para_report_status_id,
	// 		// 			created_by,created_at,updated_by,updated_at)
	// 		// 			(SELECT appointment_id,collection_by,'".$now."',".COLLECTION_STARTED.",
	// 		// 			'".$created_By."', '".$now."','".$created_By."','".$now."'
	// 		// 			FROM appointment_time_report
	// 		// 			WHERE time_report_id = '".$appointmentReport->time_report_id."')");
 //        }

 //    }

	/**
	* Use       : Appointment Destination reached
	* Author    : Axay Shah
	* Date      : 11 JULY 2019
	*/
	public static function appointmentDestinationReached($appointment_id=0,$FromWeb = false)
	{
		return false;
		$now        = date("Y-m-d H:i:s");
		$date       = "";
		$created_By = Auth()->user()->adminuserid;
		$arrFilter  = array("appointment_id"        => $appointment_id,
							"para_report_status_id" => APPOINTMENT_ACCEPTED);
		// $appointmentReport = self::retrieveReportDataByAppointmentId($arrFilter);
		$appointmentReport = self::where("appointment_id",$appointment_id)->where("para_report_status_id",APPOINTMENT_ACCEPTED)->first();
		if (!$appointmentReport) {
			$appointment = Appoinment::find($appointment_id);
			if($appointment){
				$insert = self::saveAppointmentReport($appointment_id,$appointment->collection_by,APPOINTMENT_ACCEPTED);
			}
		}else if ($appointmentReport->time_report_id > 0 && $appointmentReport->endtime == "0000-00-00 00:00:00") {
			$array              = array('endtime'=>$now,'updated_by'=>$appointmentReport->updated_by,'updated_at'=>$now);
			$UpdateReport       = self::updateAppointmentReportDaynamically($array,$appointmentReport->time_report_id);
			$appointmentTime    = self::find($appointmentReport->time_report_id);
			if($appointmentTime){
				if($FromWeb){
					$date = $now;
				}
				$insert = self::saveAppointmentReport($appointmentTime->appointment_id,$appointmentTime->collection_by,COLLECTION_STARTED,$date);
			}
		}
	}

	/**
	* Use       : save appointment report
	* Author    : Axay Shah
	* Date      : 10 Dec,2018
	*/
	public static function saveReportData($time_report_id=0,$appointmentId=0,$collectionBy=0,$endTime="",$status=""){
		return false;
		$now  = date("Y-m-d H:i:s");
		if($time_report_id == 0){
			$clsappointmentreport 							= new self();
			$clsappointmentreport->appointment_id 			= $appointmentId;
			$clsappointmentreport->collection_by 			= (!empty($collectionBy)) ? $collectionBy : 0;
			$clsappointmentreport->starttime 				= $now;
			$clsappointmentreport->endtime 				    = $endTime;
			$clsappointmentreport->para_report_status_id 	= $status;
			$clsappointmentreport->created_by				= Auth()->user()->adminuserid;
			$clsappointmentreport->updated_by				= Auth()->user()->adminuserid;
			$clsappointmentreport->updated_at				= $now;
			$clsappointmentreport->created_at				= $now;
			$clsappointmentreport->save();
		}else{
			$clsappointmentreport 							    = self::find($time_report_id);
			if($clsappointmentreport){
				$clsappointmentreport->appointment_id 			= (!emtpy($appointmentId)) ? $appointmentId : $clsappointmentreport->appointment_id;
				$clsappointmentreport->collection_by 			= (!emtpy($collectionBy))  ? $collectionBy : $clsappointmentreport->collection_by;
				$clsappointmentreport->starttime 				= $now;
				$clsappointmentreport->endtime 				    = (!emtpy($endTime))  ? $endTime : $clsappointmentreport->endtime;
				$clsappointmentreport->para_report_status_id 	= (!emtpy($status))  ? $collectionBy : $clsappointmentreport->para_report_status_id;
				$clsappointmentreport->created_by				= Auth()->user()->adminuserid;
				$clsappointmentreport->updated_by				= Auth()->user()->adminuserid;
				$clsappointmentreport->updated_at				= $now;
				$clsappointmentreport->created_at				= $now;
				$clsappointmentreport->save();
			}

		}
	}

	/**
	 * Function Name : updateAppointmentReachedTime
	 * @param array $appointment_id
	 * @return
	 * @author Sachin Patel
	 */
	public static function updateAppointmentReachedTime($request)
	{
		return false;
		self::where('appointment_id',$request->appointment_id)->where('para_report_status_id',APPOINTMENT_ACCEPTED)->update(['endtime'=>$request->starttime]);
		self::where('appointment_id',$request->appointment_id)->where('para_report_status_id',COLLECTION_STARTED)->update(['starttime'=>$request->starttime]);


	}

}