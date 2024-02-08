<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WaybridgeModuleMaster;
use App\Models\AutoWayBridgeDetails;
use App\Models\InwardPlantDetails;
use App\Facades\LiveServices;
class InwardVehicleMaster extends Model implements Auditable
{
    protected 	$table 		=	'inward_vehicle_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;

	/*
	Use 	: Add Vehicle
	Date 	: 18 Dec 2019
	Author 	: Axay Shah
	*/
	public static function AddVehicle($vehicleNo = ''){
		$id = 0;
		if(!empty($vehicleNo)){
			$vehicleMD5 = md5($vehicleNo);
			$id = self::where("vehicle_md5",$vehicleMD5)->where('company_id',Auth()->user()->company_id)->value('id');
			if(empty($id)){
				$add = new self();
				$add->vehicle_number 	=  $vehicleNo;
				$add->vehicle_md5 		=  md5($vehicleNo);
				$add->created_by 		=  Auth()->user()->adminuserid;
				$add->company_id 		=  Auth()->user()->company_id;
				if($add->save()){
					$id = $add->id;
				}
			}
		}
		return $id;
	}

	/*
	Use 	: List Vehicle
	Date 	: 18 Dec 2019
	Author 	: Axay Shah
	*/

	public static function ListInwardVehicle(){
		return self::where('status',1)->where("company_id",Auth()->user()->company_id)->get();
	}

	/*
	Use 	: Vehicle inward in-out
	Date 	: 29 June,2021
	Author 	: Hasmukhi
	*/
	public static function SaveVehicleInwardInOutDetail($request){
		$vehicle_no = (isset($request->vehicle_number) && (!empty($request->vehicle_number)) ? trim($request->vehicle_number) : "");
		$in_time 	= (isset($request->in_time) && (!empty($request->in_time)) ? date("Y-m-d H:i:s",strtotime($request->in_time)) : "");
		$id 		= 0;
		$vehicleInwardData 					= new self;
		$vehicleInwardData->vehicle_number 	= $vehicle_no;
		$vehicleInwardData->vehicle_md5		= md5($vehicle_no);
		$vehicleInwardData->in_time			= date("Y-m-d H:i:s");
		$vehicleInwardData->mrf_id			= Auth()->user()->mrf_user_id;
		$vehicleInwardData->created_by		= Auth()->user()->adminuserid;
		$vehicleInwardData->company_id		= Auth()->user()->company_id;
		if($vehicleInwardData->save()){
			$id = $vehicleInwardData->id;
			LR_Modules_Log_CompanyUserActionLog($request,$vehicleInwardData->id);
		}
		return $id;
	}

	/*
	Use 	: Vehicle inward in-out list
	Date 	: 29 June,2021
	Author 	: Hasmukhi
	*/
	public static function VehicleInwardInOutList($request) {
		$startDate 					= date("Y-m-d")." ".GLOBAL_START_TIME; 
		$endDate 					= date("Y-m-d H:i:s");
		$startDate 					= date("H:i:s",strtotime('-30 minutes'));
		$endDate 					= date("H:i:s",strtotime('+30 minutes'));
		$AutoWayBridgeDetails 		= new AutoWayBridgeDetails();
		$startDate 					= date("Y-m-d")." ".$startDate;
		$endDate 					= date("Y-m-d")." ".$endDate;
		$AutoWayBridgeDetailsTbl 	= $AutoWayBridgeDetails->getTable();
		$data = self::select('inward_vehicle_master.*','AWBD.net_weight','AWBD.is_used')
					->leftjoin($AutoWayBridgeDetailsTbl.' as AWBD',\DB::raw('LOWER(REPLACE(inward_vehicle_master.vehicle_number," ",""))'),\DB::raw('LOWER(REPLACE(AWBD.vehicle_no," ",""))'))
					->where("AWBD.is_used",0)
					->where("AWBD.tran_tag",1)
					->where("inward_vehicle_master.company_id",Auth()->user()->company_id)
					->where("inward_vehicle_master.mrf_id",Auth()->user()->mrf_user_id)
					->whereBetween("AWBD.created_at",[$startDate,$endDate])
					// ->where("inward_vehicle_master.out_time","=","0000-00-00 00:00:00")
					->get();
		// LiveServices::toSqlWithBinding($data);
		foreach($data as $key=>$value) {
			$data[$key]['net_weight'] 	= (isset($value->net_weight) && (!empty($value->net_weight)) ? $value->net_weight : 0);
			$data[$key]['in_time'] 		= (isset($value->in_time) && (!empty($value->in_time) && ($value->in_time != "0000-00-00 00:00:00")) ? $value->in_time : "");
			$data[$key]["show_out_time"]=  (isset($value->out_time) && (!empty($value->out_time) && ($value->out_time != "0000-00-00 00:00:00")) ? false : true);
			$data[$key]['out_time'] 	= (isset($value->out_time) && (!empty($value->out_time) && ($value->out_time != "0000-00-00 00:00:00")) ? $value->out_time : "");

		}
		return $data;
	}


	/*
	Use 	: Vehicle inward in-out list
	Date 	: 29 June,2021
	Author 	: Hasmukhi
	*/
	public static function UpdateVehicleInwardOutTime($request){
		$id 					= (isset($request->id) && (!empty($request->id)) ? $request->id : 0);
		$InwardVehicleData 		= self::where('id',$id)->first();
		$vehicle_number 		= (isset($InwardVehicleData->vehicle_number) && (!empty($InwardVehicleData->vehicle_number)) ? $InwardVehicleData->vehicle_number : NULL);
		$mrf_id  				= array();
		$mrf_id 				= explode(' ',Auth()->user()->mrf_user_id);
		$CurrentDate 			= date("Y-m-d");
		$CurrentTime 			= date("H:i:s");
		$MinusMinute 			= date("H:i:s",strtotime('-30 minutes'));
		$PlusMinute 			= date("H:i:s",strtotime('+30 minutes'));
		$AutoWayBridgeWbCode 	= WaybridgeModuleMaster::whereIn('mrf_id',$mrf_id)->orderBy('id','desc')->pluck('code');
		$AutoWayBridgeDetailData= AutoWayBridgeDetails::where(\DB::raw('LOWER(REPLACE(vehicle_no," ",""))'),str_replace(' ', '', strtolower($vehicle_number)))
														->whereIn('wb_id',$AutoWayBridgeWbCode)
														->where('gross_date',$CurrentDate)
														->where('gross_time','>=',$MinusMinute)
														->where('tare_time','<=',$PlusMinute)
														->where('tran_tag','=',1)
														->where('is_used',0)
														->first();

		//LiveServices::toSqlWithBinding($AutoWayBridgeDetailData);
		$InwardVehicleData->out_time= date("Y-m-d H:i:s");
		if($InwardVehicleData->save()){
			if(!empty($AutoWayBridgeDetailData)){
				$request['vehicle_no']				= $vehicle_number;
				$request['auto_waybridge_ref_id']	= (isset($AutoWayBridgeDetailData->id) && (!empty($AutoWayBridgeDetailData->id)) ? $AutoWayBridgeDetailData->id : NULL);
				$request['inward_date'] 			= (isset($AutoWayBridgeDetailData->gross_date) && (!empty($AutoWayBridgeDetailData->gross_date)) ? $AutoWayBridgeDetailData->gross_date : NULL);
				$request['inward_time'] 			= (isset($AutoWayBridgeDetailData->gross_time) && (!empty($AutoWayBridgeDetailData->gross_time)) ? $AutoWayBridgeDetailData->gross_time : NULL);
				$request['inward_qty'] 				= (isset($AutoWayBridgeDetailData->net_weight) && (!empty($AutoWayBridgeDetailData->net_weight)) ? $AutoWayBridgeDetailData->net_weight : NULL);
				$request['mrf_id'] 					= (isset($InwardVehicleData->mrf_id) && (!empty($InwardVehicleData->mrf_id)) ? $InwardVehicleData->mrf_id : NULL);
				$request['company_id'] 				= (isset($InwardVehicleData->company_id) && (!empty($InwardVehicleData->company_id)) ? $InwardVehicleData->company_id : NULL);
				$request['is_used'] 				= 1;

				InwardPlantDetails::StoreInwardDetail($request,1);
			}
			LR_Modules_Log_CompanyUserActionLog($request,$request->id);	
		}
		return $InwardVehicleData->id;
	}
}
