<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserDeviceInfo;
use App\Models\AdminUser;
use App\Facades\LiveServices;
use App\Models\AdminGeoCode;
use App\Models\HelperAttendance;
use App\Models\Helper;

class CFMAttendanceMaster extends Model
{
 //  	public static function GetAttendanceForCFM(){
	// 	try{
	// 		echo "calling function";
	// 		$Date 		= date('Y-m-d',strtotime("-2 days"));
	// 		// $Date 		= date("2020-01-30");
	// 		$result 	= array();
	// 		$SQL 		= "SELECT
	// 					  MIN(user_device_info.last_login) AS in_time,
	// 					  MAX(user_device_info.last_login) AS out_time,
	// 					  MIN(GEO.created_at) AS first_geo_time,
	// 					  MAX(GEO.created_at) AS last_geo_time,
	// 					  GEO.lat AS in_lat,
	// 					  GEO.lon AS in_lon,
	// 					  GEO.lat AS out_lat,
	// 					  GEO.lon AS out_lon,
	// 					  `user_device_info`.`info_id`,
	// 					  `user_device_info`.`user_id`,
	// 					  `AD`.`CFM_CODE`
	// 					FROM
	// 					  `user_device_info`
	// 					INNER JOIN
	// 					  `adminuser` AS `AD` ON `user_device_info`.`user_id` = `AD`.`adminuserid`
	// 					LEFT JOIN
	// 					  `admin_geocodes` AS `GEO` ON `user_device_info`.`user_id` = `GEO`.`adminuserid`
	// 					WHERE
	// 					  DATE(user_device_info.last_login) = '".$Date."' AND `AD`.`CFM_CODE` IS NOT NULL
	// 					GROUP BY
	// 					  `user_device_info`.`user_id`
	// 					UNION ALL
	// 					SELECT
	// 					  MIN(
	// 					    helper_attendance.attendance_date
	// 					  ) AS in_time,
	// 					  MAX(
	// 					    helper_attendance.attendance_date
	// 					  ) AS out_time,
	// 					  '' AS first_geo_time,
	// 					  '' AS last_geo_time,
	// 					  '' AS in_lat,
	// 					  '' AS in_lon,
	// 					  '' AS out_lat,
	// 					  '' AS out_lon,
	// 					  `helper_attendance`.`id` AS `info_id`,
	// 					  `HEL`.`id` AS `user_id`,
	// 					  `HEL`.`CFM_CODE`
	// 					FROM
	// 					  `helper_attendance`
	// 					INNER JOIN
	// 					  `helper_master` AS `HEL` ON `helper_attendance`.`adminuserid` = `HEL`.`id`
	// 					WHERE
	// 					  DATE(
	// 					    helper_attendance.attendance_date
	// 					  ) = '".$Date."' AND `HEL`.`CFM_CODE` IS NOT NULL
	// 					GROUP BY
	// 					  `helper_attendance`.`adminuserid`";
	// 					  echo $SQL;
	// 					  exit;
	// 					$data = \DB::select($SQL);

	// 					$endpoint 	= "https://cfm.yugtia.com/set-attendance";

	// 					$client 	= new \GuzzleHttp\Client();
	// 				    $response = $client->request('POST', $endpoint, [
	// 				        'form_params' => [
	// 				            'data' => $data,
	// 				        ]
	// 				    ]);
	// 				    $response = $response->getBody()->getContents();

	// 		return $response;
	// 	}catch(\Exception $e){

	// 	}
	// }

	public static function GetAttendanceForCFM(){
		try{
			$Date 		= date('Y-m-d',strtotime("-1 days"));
			// $Date 		= date("2020-01-30");

			$startDate  = $Date." 00:00:00";
			$endDate  	= $Date." 23:59:00";
			$result 	= array();
			$SQL 		= 	'(
								SELECT adminuser.adminuserid, adminuser.CFM_CODE, "1" as from_user_device,
								(CASE WHEN 1 = 1 THEN
									(
										SELECT user_device_info.last_login
										FROM user_device_info
										WHERE user_device_info.user_id = adminuser.adminuserid
										AND user_device_info.last_login BETWEEN "'.$startDate.'" AND "'.$endDate.'"
										ORDER BY last_login ASC LIMIT 1
									) END
								) AS in_time,
								(CASE WHEN 1 = 1 THEN
									(
									  SELECT last_admin_geocodes.created_dt
									  FROM last_admin_geocodes
									  WHERE last_admin_geocodes.adminuserid = adminuser.adminuserid
									  AND last_admin_geocodes.created_dt BETWEEN "'.$startDate.'" AND "'.$endDate.'"
									  ORDER BY last_admin_geocodes.created_dt DESC
									  LIMIT 1
									) END
								) AS out_time
								FROM adminuser
							  	INNER JOIN user_device_info on  user_device_info.user_id = adminuser.adminuserid
								WHERE adminuser.cfm_code IS NOT NULL
								AND adminuser.CFM_CODE != "null"
								AND adminuser.CFM_CODE != ""
							  	GROUP BY adminuser.adminuserid
							  	HAVING (in_time IS NOT NULL OR out_time IS NOT NULL)
						  	)
							UNION ALL

							(
								SELECT helper_attendance.adminuserid, helper_master.CFM_CODE, "0" as from_user_device,
								MIN(attendance_date) AS in_time, MAX(attendance_date) AS out_time
								FROM helper_attendance
								INNER JOIN helper_master ON helper_attendance.adminuserid = helper_master.id
								WHERE helper_master.CFM_CODE IS NOT NULL
						  		AND helper_master.CFM_CODE != "null"
						  		AND helper_master.CFM_CODE != ""
						  		AND helper_attendance.attendance_date BETWEEN "'.$startDate.'" AND "'.$endDate.'"
								GROUP BY helper_attendance.adminuserid
								HAVING (in_time IS NOT NULL OR out_time IS NOT NULL)
							)';

						$data = \DB::select($SQL);
						echo "\r\n".$SQL."\r\n";
						// exit;
						if(!empty($data))
						{
							foreach($data as $key => $raw)
							{
								// dd($raw);
								$InLat 		= "";
								$OutLon 	= "";
								$LastInLat 	= "";
								$LastOutLon = "";
								if($raw->from_user_device == "1"){
									$FirstGetLogIn = AdminGeoCode::select(
																\DB::raw("lat as in_lat"),
																\DB::raw("lon as out_lon"),
																"created_at"
															)->where("adminuserid",$raw->adminuserid)->whereBetween("created_at",[$startDate,$endDate])->orderBy("created_at")->limit(1)->get()->toArray();
									$LastGetLogIn = LastAdminGeoCode::select(
																\DB::raw("lat as in_lat"),
																\DB::raw("lon as out_lon"),
																"created_dt"
															)->where("adminuserid",$raw->adminuserid)->whereBetween("created_dt",[$startDate,$endDate])->orderBy("created_dt","DESC")->limit(1)->get()->toArray();

									if(!empty($FirstGetLogIn)){
										$InLat 	= $FirstGetLogIn[0]['in_lat'];
										$OutLon = $FirstGetLogIn[0]['out_lon'];
									}
									if(!empty($LastGetLogIn)){
										$LastInLat 	= $LastGetLogIn[0]['in_lat'];
										$LastOutLon = $LastGetLogIn[0]['out_lon'];
									}
								}
								$data[$key]->in_time 	= (isset($raw->in_time) && !empty($raw->in_time)) ? $raw->in_time : "";
								$data[$key]->out_time 	= (isset($raw->out_time) && !empty($raw->out_time))? $raw->out_time : "";
								$data[$key]->in_lat 	= $InLat;
								$data[$key]->in_lon 	= $OutLon;
								$data[$key]->out_lat 	= $LastInLat;
								$data[$key]->out_lon 	= $LastOutLon;


							}
						}
						// echo "<pre>";
						// return print_r($data);
						$endpoint 	= "https://cfm.yugtia.com/set-attendance";
						$client 	= new \GuzzleHttp\Client();
					    $response 	= $client->request('POST', $endpoint, ['form_params' => ['data' => $data]]);
					    $response 	= $response->getBody()->getContents();
					    echo "\r\n".print_r($response,true)."\r\n";
					    // exit;
						return $response;
						// echo "<pre>";
						// return print_r($data);
		}catch(\Exception $e){
			dd($e);
		}
	}
}
