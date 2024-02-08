<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\Parameter;
use App\Models\WmDepartment;
use App\Models\AdminUser;
use App\Models\ShiftProductEntryMaster;
use App\Models\ShiftTimingApprovalMaster;
use App\Facades\LiveServices;
use DateTime;
class MrfShiftTimingMaster extends Model implements Auditable
{
    protected 	$table 		=	'mrf_shift_timing_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;

	

	/*
	Use 	: Add Shift Timing 
	Author 	: Axay Shah
	Date  	: 01 April,2020
	*/
	public static function AddMrfShift($request){
		try{
			$id 		= 0;
			$date 		= date("Y-m-d");
			$SHIFT_ID 	= (isset($request->shift_id) && !empty($request->shift_id)) ? $request->shift_id : 0;
			$MRF_ID 	= (isset($request->mrf_id) && !empty($request->mrf_id)) ?  $request->mrf_id : 0;
			$STATUS 	= (isset($request->status) && !empty($request->status)) ?  $request->status : 0;
			$START_TIME = (isset($request->start_time) && !empty($request->start_time)) ?  date("H:i:s",strtotime($request->start_time)) : "";
			$END_TIME 	= (isset($request->end_time) && !empty($request->end_time)) ?  date("H:i:s",strtotime($request->end_time)) : "";
			$data 				= self::firstOrCreate(array('shift_id' => $SHIFT_ID,"mrf_id"=>$MRF_ID));
			$data->shift_id 	= $SHIFT_ID;
			$data->start_time 	= $START_TIME;
			$data->end_time 	= $END_TIME;
			$data->company_id 	= Auth()->user()->company_id;
			$data->status 		= $STATUS;
			$data->created_by 	= Auth()->user()->adminuserid;
			$data->updated_by 	= Auth()->user()->adminuserid;
			if($data->save()){
				$ID = $data->id;
				LR_Modules_Log_CompanyUserActionLog($request,$ID);
			}
			return $ID;
		}catch(\Exception $e){
			dd($e);
		}
	}

	/*
	Use 	: List Shift Data
	Author 	: Axay Shah
	Date  	: 21 May,2020
	*/
	public static function ListMRFShift($request){

		$Parameter 		= new Parameter();
		$self 			= (new static)->getTable();
		$Department 	= new WmDepartment();
		$Admin 			= new AdminUser();
		$MRF_ID     	= !empty($request->input('mrf_id')) ?   $request->input('mrf_id'): '';
		$data = self::select("$self.*",
					\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
					\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
					\DB::raw("PARA.para_value as shift_name"),
					\DB::raw("MRF.department_name")
				)
		->leftjoin($Parameter->getTable()." as PARA","$self.shift_id","=","PARA.para_id")
		->leftjoin($Department->getTable()." as MRF","$self.mrf_id","=","MRF.id")
		->leftjoin($Admin->getTable()." as U1","$self.created_by","=","U1.adminuserid")
		->leftjoin($Admin->getTable()." as U2","$self.updated_by","=","U2.adminuserid");
		$data->where("$self.mrf_id",$MRF_ID);
		$data->where("$self.company_id",Auth()->user()->company_id);
		$result =  $data->orderBy("$self.id","ASC")->get();
		return $result;
	}

	

	

}
