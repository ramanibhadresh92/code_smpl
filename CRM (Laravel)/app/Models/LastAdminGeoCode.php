<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AppointmentCollectionDetail;
class LastAdminGeoCode extends Model
{
	protected 	$table 		=	'last_admin_geocodes';
	protected 	$primaryKey =	'adminuserid'; // or null
	public 		$timestamps = false;
	protected   $guarded    = [];
	/*
	Use     : to save last admin geo code
	Author  : Axay Shah
	Date    : 04 Mar,2019
	*/
	public static function saveLastAdminGeoCode($request){
		$data = self::where("adminuserid",$request->adminuserid)->first();
		if($data){
			$admin = self::where("adminuserid",$request->adminuserid)->update(["adminuserid"=>$request->adminuserid,"vehicle_id"=>$request->vehicle_id,"lat"=>$request->lat,"lon"=>$request->lon,"app_version"=>$request->app_version,"mobile_no"=>$request->mobile_no,"created_dt"=>date('Y-m-d H:i:s')]);
		}else{
			$admin = self::insert(["adminuserid"=>$request->adminuserid,"vehicle_id"=>$request->vehicle_id,"lat"=>$request->lat,"lon"=>$request->lon,"app_version"=>$request->app_version,"mobile_no"=>$request->mobile_no,"created_dt"=>date('Y-m-d H:i:s')]);
		}

	   return $admin;

	}
	/*
	Use     : Get geo tracking
	Author  : Axay Shah
	Date    : 04 Mar,2019
	*/
	public static function getGeoTracking()
	{
		$diffminutes 			= 60;
		$time_diff_string		= "-";
		$nolocation				= 1;
		$app_version			= "-";
		$mobile_no				= "-";
		$data       			= array();
		$StartTime 				= date("Y-m-d")." ".GLOBAL_START_TIME;
		$record 				= self::geoTrackingQuery();
		if(!empty($record)) {
			foreach($record as $res) {
				$image = '';
				if(isset($res->profile_photo) && !empty($res->profile_photo)) {
					$media = MediaMaster::find($res->profile_photo);
					if($media) {
						$image = $media->original_name;
					}
				}
				$res->profile_photo 	= $image;
				$res->app_version		= isset($res->app_version) && !empty($res->app_version) ? $res->app_version :"-";
				$res->mobile_no			= isset($res->mobile_no)   && !empty($res->mobile_no)   ? $res->mobile_no   :"-";
				$res->nolocation   	    = 0;
				$collection_by 			= (isset($res->adminuserid) && !empty($res->adminuserid)) ? $res->adminuserid : 0;
				$vehicle_id 			= $res->vehicle_id;
				$Collection_Statistics  = AppointmentCollectionDetail::getTodayAppointmentStats($collection_by,$StartTime,$vehicle_id);
				$timediff               = (strtotime(date("Y-m-d H:i:s")) - strtotime($res->created_dt));
				$res->name              = $res->vehicle_number." ".$res->name;
				$diffminutes			= ($timediff > 0 ? round($timediff/60,2):0);
				$time_diff_string		= timeAgo((intval($diffminutes)*60));
				$Today_Collection		= (!empty($res->today_collection)?$res->today_collection:"0.00");
				$Yesterday_Collection	= "0.00";
				$Average_Day_Collection = "0.00";
				if (!empty($res->month_collection)) {
					$Average_Day_Collection = intval($res->month_collection / $res->days_attended);
				}
				$res->month_collection      = _FormatNumberV2($Average_Day_Collection);
				$res->noupdate 				= ($diffminutes > VALIDATE_GPS_TIME_CHECK_MINS)?1:0;
				$res->collection_volume 	= (!empty($res->collection_volume)? _FormatNumberV2($res->collection_volume):0);
				$res->yesterday_collection 	= $Yesterday_Collection;
				$res->time_slot 			= $time_diff_string;
				if(!empty($Collection_Statistics)) {
					$res->fill_level 			= $Collection_Statistics['Vehicle_Fill_Level'];
					$res->vehicle_space_used 	= str_replace("%","",$Collection_Statistics['Vehicle_Fill_Level']);;
				}
				$data[] = $res;
			}
		}
		return $data;
	}

	public static function geoTrackingQuery()
	{
		$startDate 		= date('Y-m-d')." ".GLOBAL_START_TIME;
		$endDate 		= date('Y-m-d')." ".GLOBAL_END_TIME;
		$month 			= date('Y-m-01', strtotime($startDate)); // get month first date
		$startMonthDate = $month." ".GLOBAL_START_TIME;
		$cityId 		= 0;
		$companyId 		= Auth()->user()->company_id;
		$city 			= GetBaseLocationCity();
		if(!empty($city)) {
			$cityId = implode(",",$city);
		}
		$SELECT_SQL = "	SELECT adminuser.firstname as name,
						vehicle_master.vehicle_volume_capacity as vehicle_volume,
						last_admin_geocodes.adminuserid,
						last_admin_geocodes.vehicle_id,
						adminuser.profile_photo,
						last_admin_geocodes.lat,
						last_admin_geocodes.lon,
						last_admin_geocodes.app_version,
						last_admin_geocodes.mobile_no,
						last_admin_geocodes.created_dt,
						vehicle_master.vehicle_number,
						(
							CASE WHEN 1=1 THEN
							(
								SELECT SUM(CD.actual_coll_quantity)
								FROM appointment_collection_details CD
								INNER JOIN appointment_collection CM ON CM.collection_id =CD.collection_id
								WHERE CM.para_status_id IN (".COLLECTION_APPROVED.",".COLLECTION_NOT_APPROVED.",".COLLECTION_PENDING.")
								AND CM.collection_dt BETWEEN '$startDate' AND '$endDate'
								AND CM.collection_by = adminuser.adminuserid
								GROUP BY CM.collection_by
							) END
						) AS today_collection,
						(
							CASE WHEN 1=1 THEN
							(
								SELECT SUM(CD.actual_coll_quantity)
								FROM appointment_collection_details CD
								INNER JOIN appointment_collection CM ON CM.collection_id = CD.collection_id
								WHERE CM.para_status_id IN (".COLLECTION_APPROVED.",".COLLECTION_NOT_APPROVED.",".COLLECTION_PENDING.")
								AND CM.collection_dt BETWEEN '$startMonthDate' AND '$endDate'
								AND CM.collection_by = adminuser.adminuserid
								GROUP BY CM.collection_by
							) END
						) AS month_collection,
						(
							CASE WHEN 1=1 THEN
							(
								SELECT COUNT(DISTINCT DATE_FORMAT(CM.collection_dt,'%Y-%m-%d'))
								FROM appointment_collection_details CD
								INNER JOIN appointment_collection CM ON CM.collection_id =CD.collection_id
								WHERE CM.para_status_id IN (".COLLECTION_APPROVED.",".COLLECTION_NOT_APPROVED.",".COLLECTION_PENDING.")
								AND CM.collection_dt BETWEEN '$startMonthDate' AND '$endDate'
								AND CM.collection_by = adminuser.adminuserid
								GROUP BY CM.collection_by
							) END
						) AS days_attended,
						(
							CASE WHEN 1=1 THEN
							(
								SELECT SUM(IF(PM.product_volume > 0,CD.actual_coll_quantity/PM.product_volume,0))
								FROM appointment_collection_details CD
								INNER JOIN appointment_collection CM ON CM.collection_id = CD.collection_id
								INNER JOIN appoinment APP ON APP.appointment_id = CM.appointment_id
								INNER JOIN company_product_master PM ON PM.id = CD.product_id
								WHERE APP.para_status_id IN (".APPOINTMENT_COMPLETED.",".APPOINTMENT_SCHEDULED.") AND CM.audit_status = 0
								AND APP.app_date_time BETWEEN '$startDate' AND '$endDate'
								AND APP.collection_by = adminuser.adminuserid
							) END
						) AS collection_volume,
						(
							CASE WHEN 1=1 THEN
							(
								SELECT COUNT(APP.appointment_id)
								FROM appoinment APP
								WHERE APP.para_status_id IN (".APPOINTMENT_COMPLETED.")
								AND APP.app_date_time BETWEEN '$startDate' AND '$endDate'
								AND APP.collection_by = adminuser.adminuserid
							) END
						) AS today_taken_customer,
						(
							CASE WHEN 1=1 THEN
							(
								SELECT COUNT(APP.appointment_id)
								FROM appoinment APP
								WHERE APP.para_status_id IN (".APPOINTMENT_COMPLETED.",".APPOINTMENT_SCHEDULED.")
								AND APP.app_date_time BETWEEN '$startDate' AND '$endDate'
								AND APP.collection_by = adminuser.adminuserid
							) END
						) AS today_appointment
						FROM adminuser
						INNER JOIN last_admin_geocodes on adminuser.adminuserid =last_admin_geocodes.adminuserid
						INNER JOIN vehicle_master on last_admin_geocodes.vehicle_id = vehicle_master.vehicle_id
						WHERE adminuser.status = 'A' AND adminuser.city in($cityId) AND adminuser.company_id = $companyId AND last_admin_geocodes.created_dt BETWEEN '$startDate' AND '$endDate'
						ORDER BY last_admin_geocodes.`created_dt` DESC";
		$data 	= \DB::select($SELECT_SQL);
		return $data;
	}
}
