<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Validator;
use App\Models\AdminUser;
use App\Models\Company;
use App\Models\AdminTransactionGroups;
use App\Models\AdminTransaction;
use App\Models\AdminUserRights;
use App\Models\GroupMaster;
use App\Models\GroupRightsTransaction;
use App\Facades\LiveServices;
use DB;
use Mail;
class AdminUserRightsController extends LRBaseController
{
	/**
	* change user rights (edit user rights)
	* Input     : adminuserright and transactionIds array
	* Author    : Axay Shah
	* Date      : 24 Aug,2018 
	*/
	public function changeRights(Request $request){
		DB::beginTransaction();
		$data       = [];
		$msg        = trans('message.RIGHTS_UPDATED_SUCCESSFULLY');
		$trnId      = null;
		$validation = Validator::make($request->all(), [
			'adminuserid'      => 'required',
		]);
		if ($validation->fails()) {
			return response()->json(["code" => VALIDATION_ERROR , "msg" => $validation->messages(),"data" => ""
			]);
		}
		try {
			if(isset($request->role) && !empty($request->role)) {
				$user = GroupMaster::find($request->adminuserid);
				if($user){
					$role = $request->adminuserid;
					GroupRightsTransaction::where("group_id",$role)->delete();
					if(isset($request->transactionIds) && !empty($request->transactionIds)){
						foreach($request->transactionIds as $key => $value){
							GroupRightsTransaction::insert(array("group_id" => $role,"trn_id" => $value));
						}
					}
				}
				$data = AdminUserRights::getTrnPermission($role);    
			}else{
				$user = AdminUser::find($request->adminuserid);
				if($user){

					$userId = $request->adminuserid;
					AdminUserRights::removeUserRights($userId);
					if(isset($request->transactionIds) && !empty($request->transactionIds)){
						AdminUserRights::changeRights($request->transactionIds,$userId);
						$data = AdminUserRights::getTrnPermission($userId);    
					}
				}
			}
			DB::commit();
		}
		catch (\Exception $e) {
			DB::rollback();
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}
	/**
	* List group and its rights by module 
	* Author : Kalpak Prajapati
	* Date : 07 Feb, 2022
	*/
	public function showRights(Request $request)
	{
		$msg 			= trans('message.RECORD_NOT_FOUND');
		$showRights 	= array();
		$rights 		= array();
		$company 		= Company::find(Auth()->user()->company_id); 
		if(!empty($company) && !empty($company->module_ids))
		{
			$showRights = AdminTransactionGroups::whereHas('transaction',function ($query) {
					$query->where('showtrnflg','Y');
			})
			->whereIn('module_id',unserialize($company->module_ids))
			->orderBy('trngrouporder')
			->get();
			$array =  array();
			if(!empty($showRights))
			{
				foreach ($showRights as $key => $value)
				{
					AdminTransactionGroups::getMasterGroupID($value['trngroupid']);
					if($value['parent_id'] == 0) {
						$ParentMenuID 	= $value['parent_id'];
						$MenuTitle 		= $value['trngrouptitle'];
					} else {
						$ParentMenuID 	= AdminTransactionGroups::getMasterGroupID($value['trngroupid']);
						$MenuGroup 		= AdminTransactionGroups::where("trngroupid",$ParentMenuID)->first();
						$ParentMenuID 	= $ParentMenuID;
						$MenuTitle 		= $MenuGroup->trngrouptitle;
					}
					$data 								= AdminTransaction::where('trngroupid',$value['trngroupid'])->where('showtrnflg','Y')->orderBy('menuorder')->get();
					$showRights[$key]['transaction'] 	= $data;
					$array[$ParentMenuID]['id'] 		= $ParentMenuID;
					$array[$ParentMenuID]['parent_id'] 	= $value['parent_id'];
					$array[$ParentMenuID]['title'] 		= $MenuTitle;
					$array[$ParentMenuID]['trndata'][] 	= $showRights[$key];
				}
			}
			$msg  = trans('message.RECORD_FOUND');
		}
		$res = array();
		foreach($array as $key => $value){
			$res[] = $value;
		}
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$res]);
	}

	/**
	* List group and its rights by module 
	* Author : Axay Shah
	* Date : 24 Aug,2018 
	*/
	public function showRightsV1(Request $request)
	{
		$msg 			= trans('message.RECORD_NOT_FOUND');
		$showRights 	= array();
		$rights 		= array();
		$company 		= Company::find(Auth()->user()->company_id); 
		if(!empty($company) && !empty($company->module_ids)){
			$showRights = AdminTransactionGroups::whereHas('transaction',function ($query) {
					$query->where('showtrnflg','Y');
					$query->orderBy('menuorder');
			})
			->whereIn('module_id',unserialize($company->module_ids))
			->orderBy('trngrouporder')
			->get();
			$array =  array();
			if(!empty($showRights)){
				foreach ($showRights as $key => $value) {
					$data = AdminTransaction::where('trngroupid',$value['trngroupid'])->where('showtrnflg','Y')->orderBy('menuorder')->get();
					$showRights[$key]['transaction'] = $data;
					if($value['parent_id'] == 0) {
						$array[$value['parent_id']]['title']     = $value['trngrouptitle'];
					} else {
						$TEMP = AdminTransactionGroups::where("trngroupid",$value['parent_id'])->first();
						$array[$value['parent_id']]['title']        = $TEMP->trngrouptitle;
						$array[$value['parent_id']]['parent_id']    = $TEMP->parent_id;
					}
					$array[$value['parent_id']]['id']            =  $value['parent_id'];
					$array[$value['parent_id']]['trndata'][]      = $showRights[$key];
				}
			}
			$msg  = trans('message.RECORD_FOUND');
		}
		$res = array();
		foreach($array as $key => $value){
			$res[] = $value;
		}
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$res]);
	}

	/**
	* copy user rights to multiple user
	* Author : Axay Shah
	* Date : 24 Aug,2018 
	*/
	public function copyUserRights(Request $request){
	   
		return AdminUserRights::copyUserRights($request);
	}

	/**
	* copy user rights to multiple user
	* Author : Axay Shah
	* Date : 24 Aug,2018 
	*/
	public function listUserType(Request $request){
		$userType = GroupMaster::with(['userType' => function($query){
			$query->where('status','A');
			$query->select('adminuserid','firstname','lastname','user_type');
		}])
		->where('company_id',Auth()->user()->company_id)
		->where('status','Active')
		->get();
		return response()->json(['code' => SUCCESS , "msg"=>"","data"=>$userType]);
	}

	/**
	* List company Active user
	* Author    : Axay Shah
	* Date      : 04 Sep,2018 
	*/
	public function listUser(Request $request)
	{
		$SCREEN_ID			= isset($request->screen_id)?$request->screen_id:0;
		$MRF_ID				= isset($request->mrf_id)?$request->mrf_id:0;
		$BASE_LOCATION_ID	= isset($request->base_location_id)?$request->base_location_id:0;
		$ROLE				= isset($request->role)?$request->role:0;
		if($ROLE == 0)
		{
			if (empty($SCREEN_ID) && empty($MRF_ID)) {
				$baseLocation 	= GetBaseLocationCity();
				$users 			= AdminUser::where('status','A')
								->where('company_id',Auth()->user()->company_id)
								->whereIn("city",$baseLocation)
								->select('adminuserid',DB::raw('CONCAT(firstname," ",lastname) AS username'))
								->get();
			} else {
				if (!empty($BASE_LOCATION_ID)) {
					$users 	= AdminUser::where('adminuser.status','A')
							->where('adminuser.company_id',Auth()->user()->company_id)
							->leftjoin("user_base_location_mapping","user_base_location_mapping.adminuserid","=","adminuser.adminuserid")
							->where("user_base_location_mapping.base_location_id",$BASE_LOCATION_ID)
							->select('adminuser.adminuserid',DB::raw('CONCAT(adminuser.firstname," ",adminuser.lastname) AS username'))
							->get();
				} else {
					$users 	= AdminUser::where('status','A')
								->where('company_id',Auth()->user()->company_id)
								->where(DB::raw("FIND_IN_SET($MRF_ID,assign_mrf_id)"),">",0)
								->select('adminuserid',DB::raw('CONCAT(firstname," ",lastname) AS username'))
								->get();
				}
			}
		} else {
			$companyID 		= isset(Auth()->user()->company_id) ? Auth()->user()->company_id : 0;
			$users 			= GroupMaster::where('status','Active')
								->where('company_id',$companyID)
								->select('group_id as adminuserid',DB::raw('CONCAT(group_desc) AS username'))
								->get();
		}
		return response()->json(['code' => SUCCESS , "msg"=>"","data"=>$users]); 
	}

	/* 
	*   Use     :   Display current user rights on edit rights screen  
	*   Author  :   Axay Shah
	*   Date    :   05 Spe,2018
	*/
	public function currentUserRights(Request $request){
		try {
			######## USING ADMINUSER ID PARAMETER FOR BOTH ROLE WISE AND USER WISE RIGHTS - 14 FEB 2022
			$adminUserid  = (isset($request->adminuserid) && !empty($request->adminuserid)) ? $request->adminuserid : 0;
			$role  = (isset($request->role) && !empty($request->role)) ? $request->role : 0;
				if($role == 1){
				$users = GroupRightsTransaction::where('group_id',$adminUserid)->pluck('trn_id')->toArray();
			}else{
				$users = AdminUserRights::where('adminuserid',$adminUserid)->pluck('trnid')->toArray();
			}
			
			return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_FOUND'),"data"=>$users]); 
		}catch(\Exception $e){
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
		}
	}    

	public function test(Request $request){

	}
}
