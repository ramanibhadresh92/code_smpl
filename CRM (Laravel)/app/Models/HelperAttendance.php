<?php

namespace App\Models;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;
use App\Models\Helper;
use App\Models\RatingMaster;
use App\Models\IncentiveRuleMaster;
use App\Models\IncentiveApprovalMaster;
use App\Facades\LiveServices;
class HelperAttendance extends Model implements Auditable
{
	protected   $table      =   'helper_attendance';
	protected   $guarded    =   ['id'];
	protected   $primaryKey =   'id'; // or null
	public      $timestamps =   true;
	use AuditableTrait;
	
	
	/*
	Use     : List Driver Attandance Month Day wise
	Author  : Axay shah
	Date    : 31 July,2019
	*/
	public static function ListDriverAttandance($adminuserId = 0,$StartTime,$EndTime,$Month,$Year)
	{
		$START_DATE				= date("Y-m-d",strtotime($StartTime));
		$END_DATE 				= date("Y-m-d",strtotime($EndTime)); 
		$TOTAL_DAYS_OF_MONTH    = (int)date("t",strtotime($StartTime));
		$baseLocation           = GetBaseLocationCity();
		if(!empty($baseLocation) && is_array($baseLocation)){
			$baseLocation   = implode(",",$baseLocation);
		}
		$where              = ($adminuserId > 0) ? "ATT.adminuserid = ".$adminuserId." AND ": '';
		$AdminUser          = new AdminUser;
		$LocationMaster     = new LocationMaster;
		$Attendance         = (new self)->getTable();
		$SelectSql          = "SELECT ATT.adminuserid,ATT.type,AU.per_day_earning,
							CONCAT(AU.firstname,' ',AU.lastname) AS driver_name
							FROM ".$Attendance." AS ATT
							INNER JOIN ".$AdminUser->getTable()." AS AU ON ATT.adminuserid = AU.adminuserid
							WHERE 
							$where ATT.attendance_date BETWEEN '".$StartTime."' AND '".$EndTime."' 
							AND AU.company_id = '".Auth()->user()->company_id."' AND type ='D'
							AND AU.city IN (".$baseLocation.")
							GROUP BY ATT.adminuserid
							ORDER BY ATT.adminuserid ASC";
		
		$SelectRes          = \DB::select($SelectSql);
		$result             = array();
		$titleDetails       = array();
		$Full_Day 			= 1;
		$Half_Day 			= 0.5;
		$EditRow 			= array();
		for($i=1;$i<=$TOTAL_DAYS_OF_MONTH;$i++){
			array_push($EditRow,(int)$i);
		}
		if (!empty($SelectRes)) 
		{
			foreach ($SelectRes as $Key=>$SelectRow) 
			{
				$PER_DAY_EARNING 		= (!empty($SelectRow->per_day_earning)) ? $SelectRow->per_day_earning : 0;
				$ADMIN_USER_ID 			= $SelectRow->adminuserid;
				$CAN_EDIT_INCENTIVE 	= IncentiveApprovalMaster::where("user_id",$ADMIN_USER_ID)
											->whereBetween("incentive_date",[$StartTime,$EndTime])
											// ->where("action_flag","!=",0)
											->where("user_type","0")
											->count();
				$TotalIncentiveEarning 	= IncentiveApprovalMaster::GetTotalIncentiveEarn($Month,$Year,$ADMIN_USER_ID);
				$VehicleReffrence 		= IncentiveApprovalMaster::GetReferredVehicle($ADMIN_USER_ID);
				$TotalAttendance    	= 0;
				$TotalPerDayEarning 	= 0;
				$TodayEarning 			= 0; 
				$result[$Key]['adminuserid']    = $SelectRow->adminuserid;
				$result[$Key]['driver_name']    = $SelectRow->driver_name;
				$attendance 	= self::select(\DB::raw("DATE_FORMAT(attendance_date,'%Y-%m-%d') as App_Date"),
									\DB::raw("(CASE
													WHEN attendance_type = 1 THEN 'F'
													WHEN attendance_type = 2 THEN 'H'
													ELSE 'A'
												END) as attendance,
											(CASE
													WHEN attendance_type = 1 THEN 'green'
													WHEN attendance_type = 2 THEN 'yellow'
													ELSE 'red'
											END) as attendance_color")
									)
								->whereBetween("attendance_date",[$StartTime,$EndTime])
								->where("adminuserid",$SelectRow->adminuserid)
								->where("type",$SelectRow->type)
								->orderBy("attendance_date","DESC")
								->groupBy("App_Date")
								->get();
				if(!$attendance->isEmpty()){
					foreach($attendance as $att){
						if($att->App_Date < date("Y-m-d")){
							$FaceLogin  = 1;
							$Day        = date("d",strtotime($att->App_Date));
							$LoginType  = UserDeviceInfo::where("user_id",$SelectRow->adminuserid)
										->whereBetween("last_login",array($att->App_Date." ".GLOBAL_START_TIME,$att->App_Date." ".GLOBAL_END_TIME))
										->orderBy("last_login","ASC")
										->first();
							
							$Count      = Appoinment::where("collection_by",$SelectRow->adminuserid)
										->where("para_status_id",APPOINTMENT_COMPLETED)
										->whereBetween("app_date_time",array($att->App_Date." ".GLOBAL_START_TIME,$att->App_Date." ".GLOBAL_END_TIME))
										->count();

							/*NOW ONWORD USER CAN EDIT ATTENDANCE OF APCENT DATA SO NO NEED OF COUNT*/
								// $ATTENDANCE_TYPE = ($Count > 0) ? $att->attendance : 'A';
							/*NOW ONWORD USER CAN EDIT ATTENDANCE OF APCENT DATA SO NO NEED OF COUNT*/
							$ATTENDANCE_TYPE = (isset($att->attendance)) ? $att->attendance : 'A';
							/*COMMENT ONLY EDIT FULL DAY HALF DAY LOGIC  BECAUSE OF HEMAL SIR USER CAN ADD APPECENT */
								// ($Count > 0) ? array_push($EditRow,(int)$Day ) : "";
								// array_push($EditRow,(int)$Day );
							if($LoginType){
								$FaceLogin = $LoginType->login_type;
							}

							if($ATTENDANCE_TYPE == "F"){
								$TotalAttendance 	= $TotalAttendance + $Full_Day;

							}elseif($ATTENDANCE_TYPE == "H"){
								
								$TotalAttendance 	= $TotalAttendance + $Half_Day;
							}
							$result[$Key]['Row'][$Day] 	= $ATTENDANCE_TYPE."-".$FaceLogin;
						}
					}
					
				}
				$INCENTIVE_ENABLE_FLAG = ($END_DATE < date("Y-m-d")) ? 1 : 0 ;
				$result[$Key]['checkbox_array'] 	= array();

				if($INCENTIVE_ENABLE_FLAG == 1){
					/*INCENTIVE CALCULATION CODE*/
					$RATING_DATA 						= RatingMaster::GetAvgRating($ADMIN_USER_ID,$StartTime,$EndTime);
					$AVG_RATING 						= (!empty($RATING_DATA)) ? $RATING_DATA['avg_rating'] : 0;
					$RATING_AMOUNT 						= (!empty($RATING_DATA)) ? _FormatNumberV2($RATING_DATA['rating_amount']) : 0;
					$result[$Key]['avg_rating']         = (!empty($RATING_DATA)) ? $RATING_DATA['avg_rating'] : 0;
					$result[$Key]['rating_amount']      = (!empty($RATING_DATA)) ? _FormatNumberV2($RATING_DATA['rating_amount']) : 0;
					$CHECK_INCENTIVE 					= IncentiveRuleMaster::where("is_display",1)->where("user_type",0)->where("status",1)->get()->toArray();
					if(!empty($CHECK_INCENTIVE)){
						foreach($CHECK_INCENTIVE as $RAW){
							$RAW['vehicle_drop_down'] = 0;
							$RULE_TYPE 		= $RAW['rule_type'];
							$RULE_VALUE 	= $RAW['rule_value'];
							if($RULE_TYPE == 1){
								if($RULE_VALUE <= $TotalAttendance && $RAW['is_checkbox'] == 1){
									$RAW['vehicle_drop_down'] = 0;
									if($RAW['check_in_model'] == WGNA_RULE){
										$ComplainCount = WorkComplain::where("collection_by",$ADMIN_USER_ID)->whereBetween("complain_date",[$START_DATE,$END_DATE])->count();
										if($ComplainCount == 0){
											$result[$Key]['checkbox_array'][] = 	$RAW;
										}
									}else{
											$result[$Key]['checkbox_array'][] = 	$RAW;
									}
								}
								// else{
								// 	switch ($TotalAttendance) {
								// 		case ($TotalAttendance >= MAX_ATTENDANCE_DAY && $RULE_VALUE == MAX_ATTENDANCE_DAY):
								// 			$result[$Key]['checkbox_array'][] = 	$RAW;
								// 			break;
								// 		case ($TotalAttendance == $RULE_VALUE) :
								// 			$result[$Key]['checkbox_array'][] = 	$RAW;
								// 			break;
								// 		case ($TotalAttendance == $RULE_VALUE):
								// 			$result[$Key]['checkbox_array'][] = 	$RAW;
								// 		break;
								// 		default:
								// 		break;
								// 	}

								// }
							}elseif($RULE_TYPE == 2){

								if(!empty($VehicleReffrence)){
									$RAW['vehicle_drop_down'] 				= 	1;
									$result[$Key]['checkbox_array'][] 		= 	$RAW;
								}
							}elseif($RULE_TYPE == 0){
								if($RULE_VALUE == $AVG_RATING){
									$result[$Key]['checkbox_array'][] 	= 	$RAW;
								}
							}
						}
					}
				}
				
				/*INCENTIVE CALCULATION CODE*/
				$TodayEarning 								= $PER_DAY_EARNING *  $TotalAttendance;
				$result[$Key]['total_per_day_earning'] 		= _FormatNumberV2($TotalPerDayEarning + $TodayEarning);
				$result[$Key]['total_incentive_earning'] 	= _FormatNumberV2($TotalIncentiveEarning);
				$result[$Key]['total_earning'] 				= _FormatNumberV2($TotalIncentiveEarning + $TodayEarning);
				$result[$Key]['can_edit_incentive'] 		= ($CAN_EDIT_INCENTIVE 	== 0) ? 1 : 0 ;
				$result[$Key]['incentive_enable']   		= $INCENTIVE_ENABLE_FLAG;	
				$result[$Key]['total_attendance']   		= $TotalAttendance;
				$result[$Key]['canEditDay']         		= $EditRow;
				$result[$Key]['vehicle_list'] 				= $VehicleReffrence;

			}
		}
		return $result;
		if (empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
		}
	}

	/*
	Use     :  Edit Driver Attendance
	Author  :  Axay Shah
	Date    :  1 Aug,2019 
	*/

	public static function EditAttendance($userId = 0,$InTime = "",$OutTime = "",$Type = 0,$userType = 0){
		if($userId > 0){
			$userName   	= AdminUser::where("adminuserid",Auth()->user()->adminuserid)->value("username");
			$remark     	= "Attendance Updated By :- ".$userName;
			$StartDate  	= date("Y-m-d",strtotime($InTime))." ".GLOBAL_START_TIME;
			$EndDate    	= date("Y-m-d",strtotime($OutTime))." ".GLOBAL_END_TIME;
			$UserTypeName 	= ($userType == 0) ?  "D" : "H";
			if($userType == 0 ){
				$userDevice = UserDeviceInfo::where("user_id",$userId)->where("start_day_flag",1)->where("last_login",">",$StartDate)
				->where("last_login","<=",$EndDate)
				->orderBy('info_id','DESC')
				->first();   
				if($userDevice){
					$endOfDay = 0;
					if($Type == 1){
						$endOfDay = 1;
					}
					$userDevice->end_of_day_flag    =  $endOfDay;
					$userDevice->last_login     	=  $InTime;
					$userDevice->last_logout    	=  $OutTime;
					$userDevice->updated_at     	=  date("Y-m-d H:i:s");
					$userDevice->save();
				}
				$Update = self::where("adminuserid",$userId)->where("type",$UserTypeName)->whereBetween("attendance_date",[$StartDate,$EndDate])->orderBy("id","DESC")->first();
				if($Update){
					$Update->attendance_type    = $Type;
					$Update->updated_by         = Auth()->user()->adminuserid;
					$Update->remark             = $remark;
					$Update->save();
					return true;
				}else{
					self::InsertAttendance($userId,$UserTypeName,$Type,$InTime,$remark);
					return true;
				}
			}elseif($userType == 1){
				$Update = self::where("adminuserid",$userId)->where("type",$UserTypeName)->whereBetween("attendance_date",[$StartDate,$EndDate])->orderBy("id","DESC")->first();
				if($Update){
					$Update->attendance_type    = $Type;
					$Update->updated_by         = Auth()->user()->adminuserid;
					$Update->remark             = $remark;
					$Update->save();
					return true;
				}else{
					self::InsertAttendance($userId,$UserTypeName,$Type,$InTime,$remark);
					return true;
				}
			}
		}
		return false;
	}

	/*
	Use     : Insert Attendance
	Author  : Axay Shah
	Date    : 01 Aug,2019
	*/
	public static function InsertAttendance($adminUserid =0,$UserType ="D",$AttendanceType =0,$inTime ="",$remark ="",$BatchId =0){
		$inTime = (empty($inTime)) ? date("Y-m-d H:i:s") : $inTime;
		if($adminUserid > 0){
			if($UserType == "H"){
				$code =  Helper::where("id",$adminUserid)->value("code");
			}else{
				$code =  AdminUser::where("adminuserid",$adminUserid)->value("profile_photo_tag");
			}
			$insert  = new self();
			$insert->batch_id           = $BatchId;
			$insert->code               = $code;
			$insert->attendance_type    = $AttendanceType;
			$insert->attendance_date    = $inTime;
			$insert->adminuserid        = $adminUserid;
			$insert->type               = $UserType;
			$insert->created_by         = Auth()->user()->adminuserid;
			$insert->remark             = $remark;
			$insert->save();
		}
	}

	/*
	Use     : Helper Attendance List
	Author  : Axay shah
	Date    : 01 Aug,2019
	*/
	public static function ListHelperAttandance($adminuserId = 0,$StartTime,$EndTime,$Month,$Year)
	{
		$baseLocation       = GetBaseLocationCity();
		if(!empty($baseLocation) && is_array($baseLocation)){
			$baseLocation = implode(",",$baseLocation);
		}
		$START_DATE				= date("Y-m-d",strtotime($StartTime));
		$END_DATE 				= date("Y-m-d",strtotime($EndTime)); 
		$TOTAL_DAYS_OF_MONTH    = (int)date("t",strtotime($StartTime));
		$Helper             = new Helper;
		$LocationMaster     = new LocationMaster;
		$Attendance         = (new self)->getTable();
		$SelectSql          = "SELECT ATT.adminuserid,ATT.type,AU.per_day_earning,
							CONCAT(AU.first_name,' ',AU.last_name) AS helper_name
							FROM ".$Attendance." AS ATT
							INNER JOIN ".$Helper->getTable()." AS AU ON ATT.adminuserid = AU.id
							WHERE 
							ATT.attendance_date BETWEEN '".$StartTime."' AND '".$EndTime."' 
							AND AU.company_id = '".Auth()->user()->company_id."' AND type ='H'
							AND AU.city_id IN (".$baseLocation.")
							GROUP BY ATT.adminuserid
							ORDER BY ATT.adminuserid DESC";
		
		$SelectRes      = \DB::select($SelectSql);
		$Full_Day 		= 1;
		$Half_Day 		= 0.5;
		$result         = array();
		$titleDetails   = array();
		$EditRow 		= array();
		for($i=1;$i<=$TOTAL_DAYS_OF_MONTH;$i++){
			array_push($EditRow,(int)$i);
		}
		if (!empty($SelectRes)) 
		{
			foreach ($SelectRes as $Key=>$SelectRow) 
			{
				$PER_DAY_EARNING 		= (!empty($SelectRow->per_day_earning)) ? $SelectRow->per_day_earning : 0;
				$ADMIN_USER_ID 					= $SelectRow->adminuserid;
				$CAN_EDIT_INCENTIVE 			= IncentiveApprovalMaster::where("user_id",$ADMIN_USER_ID)->whereBetween("incentive_date",[$StartTime,$EndTime])->where("user_type",1)->count();
				$TotalIncentiveEarning 	= IncentiveApprovalMaster::GetTotalIncentiveEarn($Month,$Year,$ADMIN_USER_ID,1);
				$TotalAttendance    	= 0;
				$TotalPerDayEarning 	= 0;
				$TodayEarning 			= 0; 
				
				$result[$Key]['adminuserid']    = $SelectRow->adminuserid;
				$result[$Key]['helper_name']    = $SelectRow->helper_name;
				
				$attendance = self::select(\DB::raw("DATE_FORMAT(attendance_date,'%Y-%m-%d') as App_Date"),
									\DB::raw("(CASE
													WHEN attendance_type = 1 THEN 'F'
													WHEN attendance_type = 2 THEN 'H'
													ELSE 'A'
												END) as attendance,
											(CASE
													WHEN attendance_type = 1 THEN 'green'
													WHEN attendance_type = 2 THEN 'yellow'
													ELSE 'red'
											END) as attendance_color")
									)
								->whereBetween("attendance_date",[$StartTime,$EndTime])
								->where("adminuserid",$SelectRow->adminuserid)
								->where("type",$SelectRow->type)
								->orderBy("attendance_date","DESC")
								->groupBy("App_Date")
								->get();
				
				if(!$attendance->isEmpty()){
					$TotalAttendance    = 0;
					
					foreach($attendance as $att){
						$FaceLogin  = 2;
						$Day        = date("d",strtotime($att->App_Date));
						$result[$Key]['Row'][date("d",strtotime($att->App_Date))] = $att->attendance;
						if($att->App_Date < date("Y-m-d")){
							$ATTENDANCE_TYPE = $att->attendance;
							if($ATTENDANCE_TYPE == "F"){
								$TotalAttendance = $TotalAttendance + $Full_Day;
							}elseif($ATTENDANCE_TYPE == "H"){
								$TotalAttendance = $TotalAttendance + $Half_Day;
							}
							$result[$Key]['Row'][$Day] 	= $ATTENDANCE_TYPE."-".$FaceLogin;
						}
						
						$INCENTIVE_ENABLE_FLAG = ($END_DATE <= date("Y-m-d")) ? 1 : 0 ;
						$result[$Key]['checkbox_array'] 	= array();
						if($INCENTIVE_ENABLE_FLAG == 1){
						/*INCENTIVE CALCULATION CODE*/
						$RATING_DATA 						= RatingMaster::GetHelperAvgRating($ADMIN_USER_ID,$StartTime,$EndTime);
						$RATING_DATA 						= 0;
						$AVG_RATING 						= (!empty($RATING_DATA)) ? $RATING_DATA['avg_rating'] : 0;
						$RATING_AMOUNT 						= (!empty($RATING_DATA)) ? _FormatNumberV2($RATING_DATA['rating_amount']) : 0;
						$result[$Key]['avg_rating']         = (!empty($RATING_DATA)) ? $RATING_DATA['avg_rating'] : 0;
						$result[$Key]['rating_amount']      = (!empty($RATING_DATA)) ? _FormatNumberV2($RATING_DATA['rating_amount']) : 0;
						$CHECK_INCENTIVE 					= IncentiveRuleMaster::where("is_display",1)->where("user_type",1)->where("status",1)->get()->toArray();
							if(!empty($CHECK_INCENTIVE)){
								foreach($CHECK_INCENTIVE as $RAW){
									$RAW['vehicle_drop_down'] = 0;
									$RULE_TYPE 		= $RAW['rule_type'];
									$RULE_VALUE 	= $RAW['rule_value'];
									if($RULE_TYPE == 1){
										if($RULE_VALUE <= $TotalAttendance && $RAW['is_checkbox'] == 1){
											$RAW['vehicle_drop_down'] = 0;
											$result[$Key]['checkbox_array'][] = 	$RAW;
											
										}
										
										// else{
										// 	switch ($TotalAttendance) {
										// 		case ($TotalAttendance >= MAX_ATTENDANCE_DAY && $RULE_VALUE == MAX_ATTENDANCE_DAY):
										// 			$result[$Key]['checkbox_array'][] = 	$RAW;
										// 			break;
										// 		case ($TotalAttendance == $RULE_VALUE) :
										// 			$result[$Key]['checkbox_array'][] = 	$RAW;
										// 			break;
										// 		case ($TotalAttendance == $RULE_VALUE):
										// 			$result[$Key]['checkbox_array'][] = 	$RAW;
										// 		break;
										// 		default:
										// 		break;
										// 	}

										// }
									}elseif($RULE_TYPE == 2){
										$RAW['vehicle_drop_down'] 				= 	1;
										$result[$Key]['checkbox_array'][] 		= 	$RAW;
									}elseif($RULE_TYPE == 0){
										if($RULE_VALUE == $AVG_RATING){
											$result[$Key]['checkbox_array'][] 	= 	$RAW;
										}
									}
								}

								// $TodayEarning 								= $PER_DAY_EARNING *  $TotalAttendance;
								// $result[$Key]['total_per_day_earning'] 		= _FormatNumberV2($TotalPerDayEarning + $TodayEarning);
								// $result[$Key]['total_incentive_earning'] 	= _FormatNumberV2($TotalIncentiveEarning);
								// $result[$Key]['total_earning'] 				= _FormatNumberV2($TotalIncentiveEarning + $TodayEarning);
								// $result[$Key]['can_edit_incentive'] 		= ($CAN_EDIT_INCENTIVE == 0) ? 1 : 0 ;
								// $result[$Key]['incentive_enable']   		= $INCENTIVE_ENABLE_FLAG;	
								// $result[$Key]['total_attendance'] 			= $TotalAttendance;
								// $result[$Key]['canEditDay']         		= $EditRow;
							}
						}
					}
				}
				/*INCENTIVE CALCULATION CODE*/
				$TodayEarning 								= $PER_DAY_EARNING *  $TotalAttendance;
				$result[$Key]['total_per_day_earning'] 		= _FormatNumberV2($TotalPerDayEarning + $TodayEarning);
				$result[$Key]['total_incentive_earning'] 	= _FormatNumberV2($TotalIncentiveEarning);
				$result[$Key]['total_earning'] 				= _FormatNumberV2($TotalIncentiveEarning + $TodayEarning);
				$result[$Key]['can_edit_incentive'] 		= ($CAN_EDIT_INCENTIVE 	== 0) ? 1 : 0 ;
				$result[$Key]['incentive_enable']   		= $INCENTIVE_ENABLE_FLAG;	
				$result[$Key]['total_attendance']   		= $TotalAttendance;
				$result[$Key]['canEditDay']         		= $EditRow;
			}
			
		}
		if (empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
		}
	}

	
}
