<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\RatingMaster;
use App\Models\IncentiveRuleMaster;
use App\Models\IncentiveApprovalMaster;

class IncentiveController extends LRBaseController
{
	/*
	Use     : List Quality Rating List
	Author  : Axay Shah
	Date    : 14 Feb 2020
	*/
	public function GetRatingMasterList(){
		$data = RatingMaster::GetRatingMasterList();
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}

	/*
	Use     : Get Rules CheckBox
	Author  : Axay Shah
	Date    : 19 Feb 2020
	*/
	public function ListCheckBoxRules(Request $request){
		$type = (isset($request->user_type)) ? $request->user_type : 0;
		$data = IncentiveRuleMaster::ListCheckBoxRules($type);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}


	/*
	Use     : GET DRIVER INCENTIVE
	Author  : Axay Shah
	Date    : 20 Feb 2020
	*/
	public function DriverIncentiveCalculation(Request $request){
		$data       = IncentiveRuleMaster::DriverIncentiveCalculation($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}

	/*
	Use     : Referal Vehicle List
	Author  : Axay Shah
	Date    : 14 Feb 2020
	*/
	public function ReferalVehicleList(Request $request){
		$Month      = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));
		$Year       = intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
		$UserId     = intval((isset($Request->adminuserid) && !empty($Request->input('adminuserid')))? $Request->input('adminuserid') : 0);
		$Month      = empty($Month)?date("m"):$Month;
		$Year       = empty($Year)?date("Y"):$Year;
		$starttime  = $Year."-".$Month."-01";
		$endtime    = date("Y-m-t",strtotime($starttime));
		$starttime  = date("Y-m-d", strtotime("$starttime -2 Month")); 
		
		$data       = IncentiveRuleMaster::ReferalVehicleList($starttime,$endtime);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}


	/*
	Use     : Store Incentive Details
	Author  : Axay Shah
	Date    : 29 Feb 2020
	*/
	public function SaveIncentive(Request $request){

		$data  = IncentiveApprovalMaster::SaveIncentive($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}

	/*
	Use     : List Incentive Approval Master
	Author  : Axay Shah
	Date    : 29 Feb 2020
	*/
	public function ListIncentiveMaster(Request $request){

		$data  = IncentiveApprovalMaster::ListIncentiveMaster($request);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}
	

	/*
	Use     : Get Incentive Details by Unique id
	Author  : Axay Shah
	Date    : 29 Feb 2020
	*/
	public function GetIncentiveDetailsByUniqueID(Request $request){
		$unique_id   = (isset($request->unique_id) && !empty($request->input('unique_id')))? $request->input('unique_id') : 0;
		$data        = IncentiveApprovalMaster::GetById($unique_id);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}

	/*
	Use     : Approve incentive
	Author  : Axay Shah
	Date    : 29 Feb 2020
	*/
	public function ApproveIncentive(Request $request){
		$unique_id  = (isset($request->unique_id) 	&& !empty($request->input('unique_id')))? $request->input('unique_id') 	: 0;
		$status   	= (isset($request->status) 		&& !empty($request->input('status')))	? $request->input('status') 	: 0;
		$ids   		= (isset($request->incentive_id) 		&& !empty($request->input('incentive_id')))	? $request->input('incentive_id') 	: '';
		$data       = IncentiveApprovalMaster::ApproveIncentive($unique_id,$ids,$status);
		if($data == true){
			LR_Modules_Log_CompanyUserActionLog($request,$request->id);	
		}
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=> $data));
	}
	
}
