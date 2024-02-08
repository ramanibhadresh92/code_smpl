<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\HelperAttendance;
use App\Models\VehicleMaster;
use App\Models\IncentiveApprovalMaster;
use App\Facades\LiveServices;
class IncentiveRuleMaster extends Model
{
	protected 	$table 		=	'incentive_rule_master';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public      $timestamps =   true;
    
    /*
	Use 	: List Check Box for incentive module rules
	Author 	: Axay shah
	Date 	: 19 Feb 2020
	*/
	public static function ListCheckBoxRules($type = 0){
		return 	self::where("status",STATUS_ACTIVE)
				->where("is_display",1)
				->where('company_id',Auth()->user()->company_id)
				->where("user_type",$type)
				->get();
	}

	/*
	Use 	: List Vehicle For Incentive three month rule
	Author 	: Axay shah
	Date 	: 28 Feb 2020
	*/
	public static function ReferalVehicleList($startDate,$endDate){
		$cityId         = GetBaseLocationCity(Auth()->user()->base_location);
		$VehicleMaster 			=  new VehicleMaster();
		$IncentiveApprovalTbl 	=  new IncentiveApprovalMaster();
		$Vehicle 				=  $VehicleMaster->getTable();
		$IAM 					=  $IncentiveApprovalTbl->getTable();

		$vehicleData = IncentiveApprovalMaster::whereNotBetween("$IAM.incentive_date",[$startDate,$endDate])
		->leftjoin($Vehicle,"$IAM.vehicle_id","=","$Vehicle.vehicle_id")
		->whereIn("$Vehicle.city_id",$cityId)
		->pluck("$IAM.vehicle_id");
		$result = VehicleMaster::where("is_referal",1);
		if(!empty($vehicleData)){
			$result->whereNotIn("vehicle_id",$vehicleData);
		}
		$data = $result->get(["vehicle_id","vehicle_number","vehicle_type","is_referal"]);

		return $data;
	}
		
    





}
