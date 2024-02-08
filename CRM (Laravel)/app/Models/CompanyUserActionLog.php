<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUserActionLog extends Model
{
	protected 	$table 		    = 'company_user_action_log';
	public      $timestamps     = false;
	protected   $primaryKey     =   'id'; // or null
	protected   $guarded        =   ['id'];

	/*
	Use     : Company User Action Log Report
	Author  : Axay Shah
	Date    : 03 May,2019
	*/
	public static function LogReport($request)
	{
		$res            = array();
		$self           = (new static)->getTable();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 50;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$action_by      = $request->has('params.action_by') && !empty($request->input('params.action_by')) ? $request->input('params.action_by') : '';
		$owner_id       = $request->has('params.owner_id') && !empty($request->input('params.owner_id')) ? $request->input('params.owner_id') : '';
		$request_from   = $request->has('params.request_from') && !empty($request->input('params.request_from')) ? $request->input('params.request_from') : '';
		$action_id      = $request->has('params.action_id') && !empty($request->input('params.action_id')) ? $request->input('params.action_id') : '';
		$ip             = $request->has('params.ip') && !empty($request->input('params.ip')) ? $request->input('params.ip') : '';
		$createdAt      = $request->has('params.created_from') && !empty($request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input('params.created_from'))) : date("Y-m-d");
		$createdTo    = $request->has('params.created_to') && !empty($request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input('params.created_to'))) : date("Y-m-d");
		$data = self::select("$self.*",
							\DB::raw("company_user_action_master.action_title as action_title"),
							\DB::raw("IF($self.request_from = 1,'Mobile','Web') as RequestFrom"),
							\DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname) as action_by"))
					->join("company_user_action_master","$self.action_id","=","company_user_action_master.action_id")
					->leftJoin("adminuser","adminuser.adminuserid","=","$self.created_by");
		if(!empty($request_from)) {
			if (strtolower($request_from) == "mobile") {
				$data->where("$self.request_from",1);
			} else if (strtolower($request_from) == "web") {
				$data->where("$self.request_from",0);
			}
		}
		if(!empty($action_by)) {
			$data->where(\DB::raw("CONCAT(adminuser.firstname,' ',adminuser.lastname)"),"like","%".$action_by."%");
		}
		if(!empty($ip)){
		   $data->where("$self.ip","like","%".$ip."%");
		}
		if(!empty($action_id)) {
			$data->where("$self.action_id",$action_id);
		}
		if(!empty($createdAt) && !empty($createdTo)) {
			$data->whereBetween("$self.created_at",array($createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME));
		} else if(!empty($createdAt)) {
			$data->whereBetween("$self.created_at",array($createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME));
		} else if(!empty($createdTo)) {
			$data->whereBetween("$self.created_at",array($createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME));
		}
		$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		return $result;
	}
}
