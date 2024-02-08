<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use DB;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Support\Facades\Http;
use App\Models\WmDepartment;
use App\Facades\LiveServices;
class WaybridgeModuleMaster extends Model implements Auditable
{
    protected 	$table 		=	'waybridge_module_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	public function DepartmentData()
	{
	    return $this->belongsTo(WmDepartment::class,"mrf_id");
	}

	/*
	Use 	: List Way Bridge
	Author 	: Axay Shah
	Date 	: 24 Feb,2021
	*/
	public static function ListWayBridge($request,$isPainate = true){
		$table 			= (new static)->getTable();
		$BaseLocation 	= new BaseLocationMaster();
		$Department 	= new WmDepartment();
		$cityId         = GetBaseLocationCity();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$createdAt 		= ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input("params.created_from"))) : "";
		$createdTo 		= ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input("params.created_to"))) : "";


		$data 	= self::select("$table.*","CMS.department_name",\DB::raw("(CASE WHEN $table.status = 0 THEN 'Active'
								WHEN $table.status = 0 THEN 'Inactive'
								END ) AS status_name")
				)
				->leftjoin($Department->getTable()." AS CMS","$table.mrf_id","=","CMS.id")
				->where("$table.company_id",Auth()->user()->company_id)
				->whereIn("CMS.location_id",$cityId);

		if($request->has('params.waybridge_name') && !empty($request->input('params.waybridge_name')))
		{
			$data->where("$table.waybridge_name",'like',"%".$request->input('params.waybridge_name')."%");
		}
		if($request->has('params.code') && !empty($request->input('params.code')))
		{
			$data->where("$table.code",'like',"%".$request->input('params.code')."%");
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$data->where("$table.mrf_id",$request->input('params.mrf_id'));
		}
		if($request->has('params.status'))
		{
			$status =  $request->input('params.status');
			if($status == "0"){
				$data->where("$table.status",$status);
			}elseif($status == "1"){
				$data->where("$table.status",$status);
			}
		}
		if(!empty($createdAt) && !empty($createdTo)){
			$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		}elseif(!empty($createdAt)){
			$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME]);
		}elseif(!empty($createdTo)){
			$data->whereBetween("$table.created_at",[$createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		}
		// LiveServices::toSqlWithBinding($data);
		if($isPainate == true){
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}else{
			$result = $data->get();
		}
		return $result;

	}

	/*
	Use 	: List Way Bridge
	Author 	: Axay Shah
	Date 	: 24 Feb,2021
	*/
	public static function GetById($ID=0)
	{
		$data = self::find($ID);
		if($data){
			$data->mrf_name = $data->DepartmentData->department_name;
		}
		return $data;
	}


	/*
	Use 	: List Way Bridge
	Author 	: Axay Shah
	Date 	: 24 Feb,2021
	*/
	public static function CreateWayBridge($request){
		try{

			$ID 					= 0;
			$CODE 					= "";
			$BASE_LOCATION_ID 		= Auth()->user()->base_location;
			$STATUS 				= (isset($request['status']) && !empty($request['status'])) ? $request['status'] : 0;
			$MRF_ID 				= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
			$WAYBRIDGE_NAME 		= (isset($request['waybridge_name']) && !empty($request['waybridge_name'])) ? ucwords(strtolower($request['waybridge_name'])) : "";
			$MRF_NAME 				= (!empty($MRF_ID)) ? WmDepartment::where("id",$MRF_ID)->value("department_name") : "";

			$MRF_NAME 				= str_replace(array("V-MRF - ","MRF-","MRF - ","V-MRF-"),array("","","",""),$MRF_NAME);
			$MRF_NAME 				= str_replace(array("V-"),array(""),$MRF_NAME);
			$BASE_LOCATION_NAME 	= (!empty($BASE_LOCATION_ID)) ? BaseLocationMaster::where("id",$BASE_LOCATION_ID)->value("base_location_name") : "";
			$BASE_LOCATION_NAME 	= (!empty($BASE_LOCATION_NAME)) ? strtoupper(strtolower(substr($BASE_LOCATION_NAME, 0, 3))) : "";
			$CODE 					= "WB-".$MRF_NAME."-".$BASE_LOCATION_NAME;
			$save 					= new self();
			$save->waybridge_name 	= $WAYBRIDGE_NAME;
			$save->mrf_id 			= $MRF_ID;
			$save->status 			= $STATUS;
			$save->company_id 		= Auth()->user()->company_id;
			if($save->save()){
				$ID = $save->id;
				$save->code = $CODE."-".$ID;
				$save->save();
				$requestObj = json_encode($request,JSON_FORCE_OBJECT);
				LR_Modules_Log_CompanyUserActionLog($requestObj,$ID);
			}
			return $ID;
		}catch(\Exception $e){
			\Log::error("ERROR".$e->getMessage()." LINE".$e->getLine()." FILE ".$e->getFile());
		}
	}

	/*
	Use 	: List Way Bridge
	Author 	: Axay Shah
	Date 	: 24 Feb,2021
	*/
	public static function UpdateWayBridge($request){
		try{
			$BASE_LOCATION_ID 		= Auth()->user()->base_location;
			$STATUS 				= (isset($request['status']) && !empty($request['status'])) ? $request['status'] : 0;
			$ID 					= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : 0;
			$MRF_ID 				= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
			$WAYBRIDGE_NAME 		= (isset($request['waybridge_name']) && !empty($request['waybridge_name'])) ? ucwords(strtolower($request['waybridge_name'])) : "";
			$save 					= self::find($ID);
			if($save){
				$save->waybridge_name 	= $WAYBRIDGE_NAME;
				$save->mrf_id 			= $MRF_ID;
				$save->status 			= $STATUS;
				$save->status 			= Auth()->user()->company_id;
				if($save->save()){
					$requestObj = json_encode($request,JSON_FORCE_OBJECT);
					LR_Modules_Log_CompanyUserActionLog($requestObj,$ID);
					return true;
				}
			}
			return false;
		}catch(\Exception $e){
			\Log::error("ERROR".$e->getMessage()." LINE".$e->getLine()." FILE ".$e->getFile());
		}
	}

	/*
	Use 	: Get Way Bridge Dropdown
	Author 	: Axay Shah
	Date 	: 24 Feb,2021
	*/
	public static function GetWayBridgeDropDown($request){
		try{
			$Department = new WmDepartment();
			$self 		= (new static)->getTable();
			$BASE_LOCATION_ID 	= Auth()->user()->base_location;
			$data = self::select("$self.code","$self.id","$self.waybridge_name","$self.status","$self.mrf_id")
			->leftjoin($Department->getTable()." as DEP","$self.mrf_id","=","DEP.id")
			->where("DEP.base_location_id",$BASE_LOCATION_ID)
			->where("$self.status",1)
			->where("$self.company_id",Auth()->user()->company_id)
			->orderBy("$self.waybridge_name","ASC")
			->get();
			return $data;
		}catch(\Exception $e){
			\Log::error("ERROR".$e->getMessage()." LINE".$e->getLine()." FILE ".$e->getFile());
		}
	}
}
