<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\AdminUser;
use App\Models\Helper;
use App\Models\HelperAttendance;
use App\Facades\LiveServices;
class HelperAttendanceApproval extends Model implements Auditable
{
    protected   $table      =   'helper_attendance_approval';
	protected   $guarded    =   ['id'];
	protected   $primaryKey =   'id'; // or null
	public      $timestamps =   true;
	use AuditableTrait;

	/*
	Use 	: Insert Helper Attendance Approval
	Author 	: Axay Shah
	Date 	: 20 March,2020 
	*/
	public static function InsertAttendanceApproval($adminUserid =0,$UserType =0,$AttendanceType =0,$inTime ="",$remark ="",$BatchId =0,$reason=''){
		$UserType = ($UserType == 1) ? TYPE_HELPER : TYPE_DRIVER;
		$inTime = (empty($inTime)) ? date("Y-m-d H:i:s") : $inTime;
		if($adminUserid > 0){
			if($UserType == "H"){
				$HelperData = Helper::where("id",$adminUserid)->get(["code","city_id"])->toArray();
				$code 		= (!empty($HelperData)) ? $HelperData[0]['code'] : "";
				$cityID 	= (!empty($HelperData)) ? $HelperData[0]['city_id'] : 0;
				
			}else{
				$Adminuser 	=  AdminUser::where("adminuserid",$adminUserid)->get(["profile_photo_tag","city"])->toArray();
				;
				$code 		= (!empty($Adminuser)) ? $Adminuser[0]['profile_photo_tag'] : "";
				$cityID 	= (!empty($Adminuser)) ? $Adminuser[0]['city'] : 0;
			}
			$insert  					= new self();
			$insert->batch_id           = $BatchId;
			$insert->code               = $code;
			$insert->attendance_type    = $AttendanceType;
			$insert->attendance_date    = $inTime;
			$insert->adminuserid        = $adminUserid;
			$insert->type               = $UserType;
			$insert->city_id            = $cityID;
			$insert->created_by         = Auth()->user()->adminuserid;
			$insert->company_id         = Auth()->user()->company_id;
			$insert->remark             = $remark;
			$insert->reason             = $reason;
			if($insert->save()){
				return true;
			}
			return false;
		}
	}

	/*
	Use 	: List Helper Attendance Approval
	Author 	: Axay Shah
	Date 	: 23 March,2020 
	*/
	public static function ListHelperAttendanceApproval($request){

		$Today          = date('Y-m-d');
		$cityId         = GetBaseLocationCity();
		$LocationMaster	= new LocationMaster();
		$AdminUser		= new AdminUser();
		$Helper			= new Helper();
		$self 			= (new static)->getTable();
		$sortBy         = ($request->has('sortBy') && !empty($request->input('sortBy'))) ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber') : '';

		$result = 	self::select(
								"$self.id",
								"$self.code",
								"$self.attendance_type",
								"$self.attendance_date",
								"$self.adminuserid",
								"$self.city_id",
								"$self.company_id",
								"$self.type",
								"$self.reason",
								"$self.approval_status",
								"$self.created_by",
								"$self.updated_by",
								"$self.remark",
								"$self.created_at",
								"$self.updated_at",
								\DB::raw("L1.city as city_name"),
								\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
								\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as approval_by"),
								\DB::raw("DATE_FORMAT($self.attendance_date,'%Y-%m-%d') as attendance_date"),
								\DB::raw("(CASE
												WHEN attendance_type = 1 THEN 'F'
												WHEN attendance_type = 2 THEN 'H'
												ELSE 'A'
											END) as attendance"),
								\DB::raw("(CASE
												WHEN attendance_type = 1 THEN 'Full Day'
												WHEN attendance_type = 2 THEN 'Half Day'
												ELSE 'Absent'
										END) as attendance_type_name"),
								\DB::raw("(CASE
												WHEN approval_status = 1 THEN 'Approved'
												WHEN approval_status = 2 THEN 'Rejcted'
												ELSE 'Pending'
											END) as approval_status_name"),
								\DB::raw("(CASE
												WHEN type = 'H' THEN CONCAT(U3.first_name,' ',U3.last_name)
												WHEN type = 'D' THEN CONCAT(U4.firstname,' ',U4.lastname)
												ELSE $self.code
											END) as user_name")
								)
		->join($LocationMaster->getTable()." as L1","city_id","=","L1.location_id")
		->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid")
		->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid")
		->leftjoin($Helper->getTable()." as U3","$self.adminuserid","=","U3.id")
		->leftjoin($AdminUser->getTable()." as U4","$self.adminUserid","=","U4.adminuserid")
		->where("$self.company_id",Auth()->user()->company_id);
		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$result->where("$self.id",$request->input('params.id'));
		}
		if($request->has('params.approve_by') && !empty($request->input('params.approve_by')))
		{
			$result->where(function($query) use($request){
				$query->where("U2.firstname",'like','%'.$request->input('params.approve_by').'%');
				$query->orWhere("U2.lastname",'like','%'.$request->input('params.approve_by').'%');
			});
		}

		if($request->has('params.attendance_by') && !empty($request->input('params.attendance_by')))
		{
			$result->where(function($query) use($request){
				$query->where("U1.firstname",'like','%'.$request->input('params.attendance_by').'%');
				$query->orWhere("U1.lastname",'like','%'.$request->input('params.attendance_by').'%');
			});
		}

		if($request->has('params.attendance_type'))
		{
			$attendance_type = $request->input("params.attendance_type");

			if($attendance_type > "0" || $attendance_type == "0"){
				$result->where("$self.attendance_type",$attendance_type);
			}
		}

		if($request->has('params.approval_status'))
		{
			$status = $request->input("params.approval_status");
			if($status > "0" || $status == "0"){
				$result->where("$self.approval_status",$status);
			}
		}
		if($request->has('params.user_type'))
		{
			$userType 	= $request->input("params.user_type");
			
			if($userType == "0"|| $userType == 0 && $userType != NULL){
				$result->where("$self.type",TYPE_DRIVER);
			}elseif($userType == "1"|| $userType == 1){
				$result->where("$self.type",TYPE_HELPER);
			}
		}
		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$result->whereBetween("$self.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('params.startDate')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.endDate')." ".GLOBAL_END_TIME))));
		}else if(!empty($request->input('params.startDate'))){
		   $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		   $result->whereBetween("$self.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('params.endDate'))){
		   $result->whereBetween("$self.created_at",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}
		if($request->has('params.city_id') && !empty($request->input('params.city_id')))
		{
			$result->where("$self.city_id",$request->input('params.city_id'));
		}else{
			$result->whereIn("$self.city_id",$cityId)	;
		}
		// LiveServices::toSqlWithBinding($result);
		$data = $result->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $data;
	}

	/*
	Use 	: Approve Attendance Status
	Author 	: Axay Shah
	Date 	: 23 March,2020 
	*/
	public static function ApproveAttendanceRequest($status=0,$id=0){
		try{
			$approval = self::find($id);
			if($approval){
				$approval->approval_status 	= $status;
				$approval->updated_by 		= Auth()->user()->adminuserid;
				if($approval->save()){
					if($status == "1"){
						HelperAttendance::InsertAttendance($approval->adminuserid,$approval->type,$approval->attendance_type,$approval->attendance_date,$approval->remark);
					}
					return $id;
				}
			}
		}catch(\Exception $e){
			return 0;
		}
	}
}
