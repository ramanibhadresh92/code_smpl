<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class IncentiveApprovalMaster extends Model implements Auditable
{
    protected   $table      =   'incentive_approval_master';
	protected   $guarded    =   ['id'];
	protected   $primaryKey =   'id'; // or null
	public      $timestamps =   true;
	use AuditableTrait;

	/*
	Use 	: Store driver and helper Incentive
	Author 	: Axay Shah
	Date 	: 27 Feb,2019
	*/
	public static function SaveIncentive($request){
		$id 			= 0;
		$incentiveData 	= (isset($request->incentive_data)	&& !empty($request->incentive_data)) ? $request->incentive_data : "" ;
		$userType 		= (isset($request->user_type)) ? $request->user_type : NULL ;
		$user_id 		= (isset($request->user_id)		&& !empty($request->user_id)) 	? $request->user_id : 0 ;
		$Month 			= (isset($request->month)		&& !empty($request->month)) 	? $request->month : 0 ;
		$Year 			= (isset($request->year)		&& !empty($request->year)) 		? $request->year : 0000 ;
		$result 		= false;
		// var_dump($incentiveData);
		if(!empty($incentiveData)){
			$String 	= $userType.$user_id.$Month.$Year;
			$UNIQUE_ID 	= md5($String); 
			foreach($incentiveData as $raw){
				$add 					= new self();
				$add->incentive_id 		= (isset($raw["incentive_id"])	&& !empty($raw["incentive_id"])) 	? $raw["incentive_id"]	: 0 ;
				$add->amount 			= (isset($raw["amount"]) 		&& !empty($raw["amount"])) 		? $raw["amount"]	: 0 ;
				$add->vehicle_id 		= (isset($raw["vehicle_id"]) 	&& !empty($raw["vehicle_id"])) 	? $raw["vehicle_id"]		: 0 ;
				$add->user_id 			= $user_id;
				$add->unique_id 		= $UNIQUE_ID;
				$add->user_type 		= $userType;
				$add->incentive_date	= $Year."-".$Month."-01";
				$add->incentive_month	= $Month;
				$add->incentive_year	= $Year;
				$add->created_by 		= Auth()->user()->adminuserid ;
				$add->save();
				LR_Modules_Log_CompanyUserActionLog($request,$add->id);
			}
			$result = true;
		}
		return $result;
	}

	/*
	Use 	: List Incentive Approval Data
	Author 	: Axay Shah
	Date 	: 29 Feb,2019
	*/

	public static function ListIncentiveMaster($request,$isPainate=true){
		$data 		= array();
		$CityId 		= GetBaseLocationCity(Auth()->user()->base_location);
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";




		$recordPerPage  = ($request->has('size')   && !empty($request->input('size'))) ? $request->input('size') : 10;
		$pageNumber     = ($request->has('pageNumber')   && !empty($request->input('pageNumber'))) ? $request->input('pageNumber') : '';
		$Month     		= ($request->has('params.month')   && !empty($request->input('params.month'))) ? $request->input('params.month') :  date("m");
		$Year     		= ($request->has('params.year')   && !empty($request->input('params.year'))) ? $request->input('params.year') :  date("Y");
		$status     	= ($request->has('params.status')) ? $request->input('params.status') :  '';
		$HelperTbl		= new Helper();
		$Helper 		= $HelperTbl->getTable();
		$AdminTbl		= new AdminUser();
		$Admin 			= $AdminTbl->getTable();
		$Self 			= (new static)->getTable();
		$startDate 		= $Year."-".$Month."-01";
		$endDate 		= date("Y-m-t",strtotime($startDate));
		$list           = self::select("$Self.*",
							\DB::raw("SUM($Self.amount) as total_amount"),
							\DB::raw("CASE WHEN $Self.action_flag = 2 THEN 'Rejected'
								WHEN $Self.action_flag = 1 THEN 'Approved'
								WHEN $Self.action_flag = 3 THEN 'Rejected'
								ELSE 'Pending'
							END AS action_flag_name"),
							\DB::raw("CASE WHEN $Self.approval_stage = 2 THEN 'Approved'
								WHEN $Self.approval_stage = 1 THEN 'First Level approved'
								ELSE 'Pending'
							END AS stage_status"),
							\DB::raw("CASE WHEN $Self.user_type = 0 THEN 'Driver'
											ELSE 'Helper'
							END AS user_type_name"),
							\DB::raw("CASE WHEN $Self.user_type = 0 THEN CONCAT(AD.firstname,' ',AD.lastname) 
											ELSE CONCAT(HEL.first_name,' ',HEL.last_name) 
							END AS user_name"),
							\DB::raw("DATE_FORMAT('%Y-%m',$Self.incentive_date)  AS incentive_month_year")
							)
		->leftjoin("$Helper as HEL","$Self.user_id","=","HEL.id")
		->leftjoin("$Admin as AD","$Self.user_id","=","AD.adminuserid")
		// ->join("$Admin as AD1","$Self.created_by","=","AD.adminuserid")
		// ->leftjoin("$Admin as AD2","$Self.final_approval_by","=","AD.adminuserid")
		->whereBetween("$Self.incentive_date",[$startDate,$endDate]);
		if($status > 0 || $status == "0"){
			$list->where("action_flag",$status);
		}
		$list->groupBy("$Self.unique_id");
		// ->groupBy("$Self.user_type");
		// LiveServices::toSqlWithBinding($list);
		if($isPainate == true){
			$result =  $list->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			if($result->total()> 0){
				$data = $result->toArray();
				foreach($data['result'] as $key => $incentive){
					$data['result'][$key]['created_by_name'] = AdminUser::where("adminuserid",$incentive['created_by'])->value("username") ;
					$data['result'][$key]['approved_by_name'] = AdminUser::where("adminuserid",$incentive['final_approval_by'])->value("username") ;
				}
			}
		}
		if(empty($data)){
			$data['pageNumber']   	= $pageNumber;
            $data['result']       	= array();
            $data['size']         	= $recordPerPage;
            $data['totalElements']  = $pageNumber;
            $data['totalPages']     = $pageNumber;
		}
		return $data;
	}

	/*
	Use 	: Get By Id
	Author 	: Axay Shah
	Date 	: 29 Feb,2020
	*/

	public static function GetById($uniqueId = 0){
		$self 			= (new static)->getTable();
		$IncentiveTbl 	= new IncentiveRuleMaster();
		$IncentiveRule	= $IncentiveTbl->getTable();
		$data 	= self::join("$IncentiveRule as IRM","$self.incentive_id","=","IRM.id")
				->where("unique_id",$uniqueId)
				->get()
				->toArray();
		if(!empty($data)){
			foreach($data as $key => $raw){
				$vehicleNumber = "";
				if($raw['vehicle_id'] > 0){
					$vehicleNumber =  VehicleMaster::where("vehicle_id",$raw['vehicle_id'])->value("vehicle_number");
				}
				$data[$key]['vehicle_number'] = $vehicleNumber;
			}
		}
		return $data;
	}

	/*
	Use 	: Approve incentive 
	Author 	: Axay Shah
	Date 	: 29 Feb,2020
	*/

	public static function ApproveIncentive($uniqueId,$ids = '',$status = 0){
		$date =  date("Y-m-d H:i:s");
		if(!empty($ids)){
			$IDS_ARR 		= json_decode(json_encode($ids),true);
				$array 			= array();
				foreach($IDS_ARR as $raw){
					array_push($array,$raw['id']);
					
					$update 		= self::where("unique_id",$uniqueId)
									->where("action_flag",0)
									->where("incentive_id",$raw['id'])
						->update([
							"action_flag" 				=> $status,
							"first_approval_by" 		=> Auth()->user()->adminuserid,
							"first_approval_date" 		=> $date,
							"final_approval_by" 		=> Auth()->user()->adminuserid,
							"final_approval_date" 		=> $date,
							"approval_stage" 			=> 2,
							"updated_by" 				=> Auth()->user()->adminuserid
						]);
				}
				self::where("unique_id",$uniqueId)->whereNotIn("incentive_id",$array)->delete();
		}else{
			if($status == 2){
				$update = self::where("unique_id",$uniqueId)
						->where("action_flag",0)
				->update([
					"action_flag" 				=> $status,
					"first_approval_by" 		=> Auth()->user()->adminuserid,
					"first_approval_date" 		=> $date,
					"final_approval_by" 		=> Auth()->user()->adminuserid,
					"final_approval_date" 		=> $date,
					"approval_stage" 			=> 2,
					"updated_by" 				=> Auth()->user()->adminuserid
				]);
			}
		}
		return $update;
		
	}

	/*
	Use 	: Get Total Incentive by Month
	Author 	: Axay Shah
	Date 	: 04 March,2020
	*/
	public static function GetTotalIncentiveEarn($Month=0,$Year='',$userId=0,$userType=0){
		$Amount = self::where('incentive_month',$Month)
	    ->where('incentive_year',$Year)
	    ->where("user_id",$userId)
	    ->where("user_type",$userType)
	    ->where("action_flag",1)
	    ->sum("amount");

	    return $Amount;

	}

	/*
	Use 	: Get Vehicle List which are refer by driver
	Author 	: Axay Shah
	Date 	: 12 March,2020
	*/
	public static function GetReferredVehicle($userId,$userType=0){
		$CityId 		= 	GetBaseLocationCity(Auth()->user()->base_location);
		$tbl 			= 	(new static)->getTable();
		$result 		= 	array();
		$vehicleIds 	= 	self::whereIn("action_flag",[0,1])->where("user_id",$userId)
							->where("user_type",$userType)->groupBy("vehicle_id")->pluck("vehicle_id");
		$vehicleData 	= 	VehicleMaster::select("vehicle_id","vehicle_number","is_referal","ref_user_id")
							->where("is_referal",1)
							->where("ref_user_id",$userId)
							->whereIn("city_id",$CityId);
							if(!$vehicleIds->isEmpty()){
								$vehicleData->whereNotIn("vehicle_id",$vehicleIds);
							}
		$vehicle 		= 	$vehicleData->get()->toArray();
		

							
		if(!empty($vehicle)){
			$RuleValue = IncentiveRuleMaster::where("check_in_model",THREE_MONTH_RULE)->where("user_type",0)->value('rule_value');
			foreach($vehicle as $raw){

				$count 	= Appoinment::select(\DB::raw("DATE_FORMAT(app_date_time,'%Y-%m-%d')  as app_date"))
				->where("para_status_id",APPOINTMENT_COMPLETED)
				->where("vehicle_id",$raw['vehicle_id'])
				->groupBy("app_date")
				->get()
				->toArray();

				if(!empty($count) && count($count) >= $RuleValue){
					$result['count'] 	= count($count);
					$result['vehicle'][] 	= $raw;
				}
			}
		}
		return $result;
	}
}
