<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Log;
use App\Models\CompanyParameter;
use App\Models\AdminUser;
use App\Facades\LiveServices;
class ViewRequestApproval extends Model
{
	protected 	$table 		        =   'view_request_approval';
	public static $companyParaInput =  array("ctype","cust_group","type_of_collection","route");
	public static $accountInput     =  array("account_manager");
	/*
	Use     : List Request approval
	Author  : Axay Shah
	Date    : 2 Nov,2018
	*/
	public static function list($request){
		$Today          = date('Y-m-d H:i:s');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "DESC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$LocationMaster = new LocationMaster();
		$LocationTbl    = $LocationMaster->getTable();
		$RequestTbl     = (new static)->getTable();
		$CityId        = GetBaseLocationCity(Auth()->user()->base_location);
		$list           = self::select("$RequestTbl.*",\DB::raw("$LocationTbl.city as city_name"))
						->whereIn('city_id',$CityId)
						->leftjoin("$LocationTbl","city_id","=","$LocationTbl.location_id")
						->where('company_id',Auth()->user()->company_id);
		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$list->whereIn("$RequestTbl.id", explode(",",$request->input('params.id')));
		}
		if($request->has('params.city_id') && !empty($request->input('params.city_id')))
		{
			$list->whereIn("$RequestTbl.city_id", explode(",",$request->input('params.city_id')));
		}
		if($request->has('params.request_type') && !empty($request->input('params.request_type')))
		{
			$list->where("$RequestTbl.module_id",$request->input('params.request_type'));
		}
		if($request->has('params.status') && !empty($request->input('params.status')))
		{
			$list->where("$RequestTbl.status", $request->input('params.status'));
		}elseif($request->has('params.status') && $request->input('params.status') == "0"){
			$list->where("$RequestTbl.status", $request->input('params.status'));
		}
		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$list->whereBetween("$RequestTbl.updated_at",array(date("Y-m-d", strtotime($request->input('params.startDate')))." ".GLOBAL_START_TIME,date("Y-m-d", strtotime($request->input('params.endDate')))." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('params.startDate'))){
		   $list->whereBetween("$RequestTbl.updated_at",array(date("Y-m-d", strtotime($request->input('params.startDate')))." ".GLOBAL_START_TIME,$Today));
		}else if(!empty($request->input('params.endDate'))){
			$list->whereBetween("$RequestTbl.updated_at",array(date("Y-m-d", strtotime($request->input('params.endDate')))." ".GLOBAL_START_TIME,$Today));
		}
		
		// LiveServices::toSqlWithBinding($list);
		return $list->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);
	} 

	/*
	Use     : get By Id
	Author  : Axay Shah
	Date    : 2 Nov,2018
	*/
	public static function getById($request){
		$data                   = self::find($request->id);
		$child_arr              =  array();
		$child_arr['old_filed'] =  array();
		$child_arr['new_filed'] =  array();
		if(isset($data->old_field_values) && !empty($data->old_field_values)){
			$jsonOldData = json_decode($data->old_field_values);
			foreach($jsonOldData as $key => $value){
				$child_arr['old_filed'][$key] = $value;
				if(in_array($key,self::$companyParaInput)) {
					$child_arr['old_filed'][$key] = CompanyParameter::where('para_id',$value)->value('para_value');;
				}elseif(in_array($key,self::$accountInput)){
					$adminUser = AdminUser::find($value);
					(!empty($adminUser))? $child_arr['old_filed'][$key] = $adminUser->firstname." ".$adminUser->lastname : $child_arr['new_filed'][$key] = "";
				}
			}
		}
		if(isset($data->field_values) && !empty($data->field_values)){
			$jsonNewData = json_decode($data->field_values);
			foreach($jsonNewData as $key => $value){
				$child_arr['new_filed'][$key] = $value;
				if(in_array($key,self::$companyParaInput)) {
					$child_arr['new_filed'][$key] = CompanyParameter::where('para_id',$value)->value('para_value');
				}elseif(in_array($key,self::$accountInput)){
					$adminUser = AdminUser::find($value);
					(!empty($adminUser))? $child_arr['new_filed'][$key] = $adminUser->firstname." ".$adminUser->lastname : $child_arr['new_filed'][$key] = "";
				}
			}
		}
		$data['decoded'] = $child_arr;
		return $data;
	} 
}
