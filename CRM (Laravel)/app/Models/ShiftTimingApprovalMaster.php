<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\Parameter;
use App\Models\WmDepartment;
use App\Models\AdminUser;
use App\Models\ShiftProductEntryMaster;
use App\Facades\LiveServices;
use DateTime;
class ShiftTimingApprovalMaster extends Model implements Auditable
{
    protected 	$table 		=	'shift_timing_approval_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;
	/*
	Use 	: Add Shift Timing Approval
	Author 	: Axay Shah
	Date  	: 01 April,2020
	*/
	public static function AddShiftTimingApproval($ShiftTimingId,$StartDate,$EndDate,$ShiftTypeId,$MRF){
		try{
			$id 					= 0;
			$date 					= date("Y-m-d");
			$data 					= new self();
			$data->shift_timing_id 	= $ShiftTimingId;
			$data->shift_id 		= $ShiftTypeId;
			$data->mrf_id 			= $MRF;
			$data->start_date 		= date("Y-m-d",strtotime($StartDate));
			$data->end_date 		= date("Y-m-d",strtotime($EndDate));;
			$data->start_time 		= date("H:i:s",strtotime($StartDate));;
			$data->end_time 		= date("H:i:s",strtotime($EndDate));;
			$data->startdatetime 	= $StartDate;
			$data->enddatetime 		= $EndDate;
			$data->created_by 		= Auth()->user()->adminuserid;
			$data->company_id 		= Auth()->user()->company_id;
			if($data->save()){
				$id = $data->id;
			}
			return $id;
		}catch(\Exception $e){
			Log::info($e->getMessage()." ".$e->getLine()." ".$e->getFile());
		}
	}

	

	/*
	Use 	: List Shift Data
	Author 	: Axay Shah
	Date  	: 02 April,2020
	*/
	public static function ListShiftTimingApproval($request){

		$Parameter 		= new Parameter();
		$Department 	= new WmDepartment();
		$self 			= (new static)->getTable();
		$Admin 			= new AdminUser();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy') && !empty($request->input('sortBy'))) ? $request->input('sortBy') : "id";
		$sortOrder      = ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size') : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber'): '';
		$cityId         = GetBaseLocationCity();

		$data = self::select("$self.*",
					\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
					\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
					\DB::raw("PARA.para_value as shift_name"),
					\DB::raw("MRF.department_name"),
				
					\DB::raw("( CASE WHEN $self.status = 1 THEN 'Approved'
								WHEN $self.status = 2 THEN 'Rejected'
								ELSE 'Pending'
								END
							) as approval_status_name")
					
				)
		->join($Parameter->getTable()." as PARA","$self.shift_id","=","PARA.para_id")
		->join($Department->getTable()." as MRF","$self.mrf_id","=","MRF.id")
		->leftjoin($Admin->getTable()." as U1","$self.created_by","=","U1.adminuserid")
		->leftjoin($Admin->getTable()." as U2","$self.updated_by","=","U2.adminuserid");
		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$id 	= $request->input('params.id');
			if(!is_array($request->input('params.id'))){
				$id = explode(",",$request->input("params.id"));	
			}
			$data->where("$self.id",$id);
		}

		if($request->has('params.status'))
		{
			if($request->input('params.status') == "0"){
				$data->where("$self.status",$request->input('params.status'));
			}elseif($request->input('params.status') == "1" || $request->input('params.status') == "2"){
				$data->where("$self.status",$request->input('params.status'));
			}
			
		}

		if($request->has('params.shift_id') && !empty($request->input('params.shift_id')))
		{
			$data->where("$self.shift_id",$request->input('params.shift_id'));
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$data->where("$self.mrf_id",$request->input('params.mrf_id'));
		}
		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			 $data->where("$self.start_date",date("Y-m-d", strtotime($request->input('params.startDate'))));
			 $data->where("$self.end_date",date("Y-m-d", strtotime($request->input('params.endDate'))));	
		}else if(!empty($request->input('params.startDate'))){
		   $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		    $data->where("$self.start_date",$datefrom);
		}else if(!empty($request->input('params.endDate'))){
		   $data->where("$self.end_date",date("Y-m-d", strtotime($request->input('params.endDate'))));
		}
		$data->whereIn("MRF.location_id",$cityId);
		$data->where("$self.company_id",Auth()->user()->company_id);
		// LiveServices::toSqlWithBinding($data);
		$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);

		if(!empty($result)){
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
				foreach($toArray['result'] as $key => $value){
				
				}
				$result = $toArray;
			}
		}
		return $result;
	}
	

	/*
	Use 	: Approve shift timing
	Author 	: Axay Shah
	Date  	: 02 April,2020
	*/
	public static function ApproveShiftTiming($id,$status=0){
		$data = self::find($id);
		if($data){
			$data->status 		=  $status;
			$data->updated_by 	=  Auth()->user()->adminuserid;
			$data->status 		=  $status;
			$data->save();
		}
		return $data;
	}

}
