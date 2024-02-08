<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;
use App\Models\AdminUserRights;
use App\Models\AdminUser;
use App\Models\AdminTransaction;
use App\Facades\LiveServices;
class AdminUserRights extends Authenticatable
{
	protected 	$table 				= 'adminuserrights';
	public static $MenuGroupTrns	= array();
	public static $return_output	= array();
	public static $scriptdata 		= '';
	public static $counter			= 0;
	public static $sub_counter		= 0;
	public static $arr_childs		= array();
	public static $path				= '';
	public static $selTrnGroup1		= array();
	public static $finalOutputGroup	= array();

	public function user_list_by_trn() {
		return $this->hasMany(AdminUser::class,'adminuserid','adminuserid');
	}


	/**
	 * getMenudata
	 * Behaviour : Public
	 * @param : passed looged in user id   :
	 * @defination : Method is use to set MenuGroupTrns array according to rights and permission.
	 **/
	public static function getMenudata($adminuserid)
	{
		$results 		= self::resultuserpermission($adminuserid);
		$MenuGroupItems = array();
		$rowarray 		= array();
		$MenuGroup 		= array();
		foreach ($results as $item) {
			$rowarray[$item->trnid] = array("trngroupid" 	=> $item->trngroupid,
											"trngrouptitle" => $item->trngrouptitle,
											"trnid"        	=> $item->trnid,
											"trnname"       => $item->trnname,
											"pageurl"       => $item->pageurl,
											"menutitle"     => $item->menutitle,
											"insubmenu"     => $item->insubmenu);
			if (isset($MenuGroupItems[$item->trngroupid])) {
				$tempVar 							= $MenuGroupItems[$item->trngroupid];
				$MenuGroupItems[$item->trngroupid] 	= $tempVar+1;
			} else {
				$MenuGroupItems[$item->trngroupid] 	= 1;
			}
			if($item->insubmenu == "Y"){
				array_push($MenuGroup,$item->trngroupid);
			}
			self::$MenuGroupTrns[$item->trngroupid][]	= $rowarray[$item->trnid];
		}
		$selTrnGroup = array();
		foreach(array_unique($MenuGroup) as $trn_group) {
			$selTrnGroup   = self::getGroupdata($trn_group);
		}
		self::$finalOutputGroup = array_merge(array_unique($MenuGroup),array_unique($selTrnGroup));
		$MainMenu 				= self::getMenuGroups();
		return $MainMenu;
	}
	/**
	 * resultuserpermission
	 *
	 * Behaviour : Public
	 *
	 * @param : passed looged in user id   :
	 *
	 * @defination : Method is use to fetch data from permission query.
	 **/
	public static function resultuserpermission($adminuserid)
	{
		$admintra_group_items = self::select('atg.trngroupid','atg.trngrouptitle','at.trnid','at.trnname','at.pageurl','at.menutitle','at.insubmenu')
									->leftJoin('admintransaction as at','adminuserrights.trnid','=','at.trnid')
									->leftJoin('admintrngroups as atg','at.trngroupid','=','atg.trngroupid')
									->where(array('adminuserrights.adminuserid'=>$adminuserid,'showtrnflg'=>'Y'))
									->orderByRaw('atg.trngrouporder, atg.trngrouptitle, at.menuorder, at.trnname');
		if(ROLE_WISE_TRN_FLAG) {
			$AdminUser = AdminUser::select("is_superadmin")->where("adminuserid",$adminuserid)->first();
			if ($AdminUser->is_superadmin) {
				$admintra_group_items = \DB::table('grouprights_trn as gt')
									->select('atg.trngroupid','atg.trngrouptitle','at.trnid','at.trnname','at.pageurl','at.menutitle','at.insubmenu')
									->leftJoin('groupmaster as gm','gt.group_id','=','gm.group_id')
									->leftJoin('adminuser as adu','gm.group_id','=','adu.user_type')
									->leftJoin('admintransaction as at','gt.trn_id','=','at.trnid')
									->leftJoin('admintrngroups as atg','at.trngroupid','=','atg.trngroupid')
									->where(array('adu.adminuserid'=>$adminuserid,'showtrnflg'=>'Y'))
									->orderByRaw('atg.trngrouporder, atg.trngrouptitle, at.menuorder, at.trnname')
									->groupBy('at.trnid');
			} else {
				$admintra_group_items = \DB::table('grouprights_trn as gt')
										->select('atg.trngroupid','atg.trngrouptitle','at.trnid','at.trnname','at.pageurl','at.menutitle','at.insubmenu')
										->leftJoin('groupmaster as gm','gt.group_id','=','gm.group_id')
										->leftJoin('adminuser as adu','gm.group_id','=','adu.user_type')
										->leftJoin('admintransaction as at','gt.trn_id','=','at.trnid')
										->leftJoin('admintrngroups as atg','at.trngroupid','=','atg.trngroupid')
										->where(array('adu.adminuserid'=>$adminuserid,'showtrnflg'=>'Y',"gm.company_id"=>Auth()->user()->company_id))
										->orderByRaw('atg.trngrouporder, atg.trngrouptitle, at.menuorder, at.trnname')
										->groupBy('at.trnid');
			}
		}
		// $admintra_group_items_sql = LiveServices::toSqlWithBindingV2($admintra_group_items,true);
		// \Log::info("===============");
		// \Log::info($admintra_group_items_sql);
		// \Log::info("===============");
		return ($admintra_group_items->get());
	}
	/**
	 * getTrnPermission
	 *
	 * Behaviour : Public
	 *
	 * @param : passed looged in user id   :
	 *
	 * @defination : Method is use return array of admin transaction id.
	 **/
	public static function getTrnPermission($adminuserid)
	{
		$results 		= self::resultuserpermission($adminuserid);
		$arr_rights     = array();
		foreach($results as $item) {
			$arr_rights[] = (int)$item->trnid;
		}
		return $arr_rights;
	}
	/**
	*getMenuGroups
	*Behaviour : Public
	*@param : passed parent_id of permission :
	*@defination : Recurssion method is use tree structure or hierarchy of menu items and subitems.
	**/
	public static function getMenuGroups($parent_id=0)
	{
		$rowarray	= array();
		$child_data = \DB::table('admintrngroups as atg')->select('*')->where('parent_id',$parent_id)->whereIn('trngroupid',self::$finalOutputGroup)->orderByRaw('trngrouporder ASC, trngroupid ASC')->get();
		if(count($child_data) > 0)
		{
			foreach($child_data as $child)
			{
				$child->title 					= $child->trngrouptitle;
				$child->route 					= 'javacript:;';
				$selected_field['trngroupid']	= $child->trngroupid;
				$selected_field['title']		= $child->trngrouptitle;
				$selected_field['path'] 		= 'javacript:;';
				$selected_field['icon'] 		= 'mdi mdi-view-dashboard';
				$selected_field['submenu']		= self::getMenuGroups($child->trngroupid);
				$selected_field['ddclass']		= '';
				$selected_field['extralink']	= false;
				if(count($selected_field['submenu'])>0)
				{
					$selected_field['haschild']	= 1;
					$selected_field['class']	= 'has-arrow';
					if(array_key_exists($child->trngroupid, self::$MenuGroupTrns))
					{
						$childs_items=array();
						$counter_i=count($selected_field['submenu']);
						foreach(self::$MenuGroupTrns[$child->trngroupid] as $row) {
							if($row["insubmenu"]=="Y") {
								$childs_items[$counter_i]['trngroupid'] = $child->trngroupid;
								$childs_items[$counter_i]['title'] 		= $row["trnname"];
								$childs_items[$counter_i]['path']		= $row["pageurl"];
								$childs_items[$counter_i]['icon'] 		= 'mdi mdi-view-dashboard';
								$childs_items[$counter_i]['submenu']	= array();
								$childs_items[$counter_i]['haschild']	= 0;
								$childs_items[$counter_i]['class']		= '';
								$childs_items[$counter_i]['ddclass']	= '';
								$childs_items[$counter_i]['extralink']	= false;
								$counter_i++;
							}
						}
						$selected_field['submenu']=array_merge($selected_field['submenu'],$childs_items);
					}
				}
				else
				{
					$selected_field['haschild']	= 0;
					$selected_field['class']	= '';
					if(array_key_exists($child->trngroupid, self::$MenuGroupTrns))
					{
						$childs_items=array();
						$counter_i=0;
						foreach(self::$MenuGroupTrns[$child->trngroupid] as $row) {
							if($row["insubmenu"]=="Y") {
								$parent_get_data = \DB::table('admintrngroups as atg')->select('*')->where('trngroupid',$child->trngroupid)->first();
								$append_str='';
								if(!empty($parent_get_data))
								{
									if($parent_get_data->parent_id==0)
									{
										$append_str = strtolower($parent_get_data->trngrouptitle);
									}
									else
									{
										$parent_get_data = \DB::table('admintrngroups as atg')->select('*')->where('trngroupid',$parent_get_data->parent_id)->first();
										$append_str = strtolower($parent_get_data->trngrouptitle);
									}
									$append_str = str_replace(array(" "), array(""), $append_str);
									$append_str = $append_str."/";
								}

								//$parent_data_id=self::$MenuGroupTrns[$child->trngroupid];
								//print_r($parent_data_id);
								$childs_items[$counter_i]['trngroupid']	= $child->trngroupid;
								$childs_items[$counter_i]['title']		= $row["trnname"];
								$childs_items[$counter_i]['path']  		= $append_str.$row["pageurl"];
								$childs_items[$counter_i]['icon'] 		= 'mdi mdi-view-dashboard';
								$childs_items[$counter_i]['submenu']	= array();
								$childs_items[$counter_i]['haschild']	= 0;
								$childs_items[$counter_i]['class']		= '';
								$childs_items[$counter_i]['ddclass']	= '';
								$childs_items[$counter_i]['extralink']	= false;
								$counter_i++;
							}
						}
						$selected_field['submenu']=$childs_items;
					}
				}
				//$rowarray[] 	= $child;
				$rowarray[]   = $selected_field;
			}
		}
		return $rowarray;
	}
	/**
	* Function    	: addUserRightsByTrnId
	* @param 		: pass transection ids
	* @defination 	: Add user rights by its admintransection id in adminuserrights
	* Author 		: Axay Shah
	**/
	public static function addUserRightsByTrnId($trnId,$userId){
		$count = self::where(['trnid'=>$trnId,"adminuserid"=>$userId])->count();
		if($count == 0){
			self::insert(['trnid'=>$trnId,'adminuserid'=>$userId]);
		}
	}
	/**
	* Function    	: removeUserRightsByTrnId
	* @param 		: pass transection id and userId
	* @defination 	: remove rights of user from adminuserrights
	* Author 		: Axay Shah
	**/
	public static function removeUserRightsByTrnId($trnId = null,$userId){
		$query = self::where('adminuserid',$userId);
		if ($trnId != null)
		{
			$query->when($trnId, function ($query) use ($trnId) {
				return $query->where('trnid',$trnId);
			});
		}
		return $query->delete();
	}
	/**
	 * Function    	: removeUserRights
	 * @param 		: pass userId
	 * @defination 	: remove user rights from adminuserrights
	 * Author 		: Axay Shah
	 **/
	public static function removeUserRights($userId){
		$query = self::where('adminuserid',$userId)->delete();
	}
	/**
	* Function    	: removeUserRights
	* @param 		: pass userId & trnId
	* @defination 	: Add user rights in adminuserrights
	* Author 		: Axay Shah
	**/
	public static function changeRights($trnId,$userId){
		foreach($trnId as $trn){
			self::insert(['trnid'=>$trn,'adminuserid'=>$userId]);
		}
	}

	/**
	 * getMenudata
	 *
	 * Behaviour : Public
	 *
	 * @param : passed looged in user id   :
	 *
	 * @defination : Method is use to set MenuGroupTrns array according to rights and permission.
	 **/
	public static function getGroupdata($trngroupId)
	{
		$arrSelftTrn = array();
		$child_data = \DB::table('admintrngroups')->select('*')->where('trngroupid',$trngroupId)->first();
		if(!empty($child_data))
		{
			if($child_data->parent_id>0)
			{
				self::$selTrnGroup1[] = $child_data->parent_id;
				self::getGroupdata($child_data->parent_id);
			}
		}
		return self::$selTrnGroup1;
	}

	/**
	* Function    	: rules
	* @param 		: input
	* @defination 	: validation for copyUserRights()
	* Author 		: Axay Shah
	**/
	private static function rules($input) {
		return $rules = array('copyFrom' => 'required|exists:adminuserrights,adminuserid','copyTo' => 'required');
	}
	/**
	* Function    	: removeUserRights
	* @param 		: pass userId & trnId
	* @defination 	: Add user rights in adminuserrights
	* Author 		: Axay Shah
	**/
	public static function copyUserRights($request)
	{
		$msg 		= trans('message.RIGHTS_COPY_SUCCESSFULLY');
		$validation = Validator::make($request->all(),self::rules($request->all()));
		if ($validation->fails()) {
			return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$validation->messages(),"data" =>""]);
		}
		foreach((array)$request->copyTo as $copyTo){
			self::removeUserRights($copyTo);
			$fromUserRights = self::where('adminuserid',$request->copyFrom)->select('trnid')->get();
			if($fromUserRights->isNotEmpty()){
				foreach($fromUserRights as $UR){
					self::addUserRightsByTrnId($UR->trnid,$copyTo);
				}
			}
		}
		return response()->json(["code" =>SUCCESS,"msg" => $msg,"data" =>""]);
	}

	/**
	* Function    	: checkUserAuthorizeForTrn
	* @param 		: integer $trnid
	* @param 		: integer $adminuserid
	* @defination 	: Check user rights in adminuserrights / group rights
	* Author 		: Kalpak Prajapati
	**/
	public static function checkUserAuthorizeForTrn($trnId, $adminuserid=0)
	{
		if($adminuserid != 0) {
			if(ROLE_WISE_TRN_FLAG) {
				return $admintra_group_items = \DB::table('grouprights_trn as gt')
												->select('at.trnid')
												->leftJoin('groupmaster as gm','gt.group_id','=','gm.group_id')
												->leftJoin('adminuser as adu','gm.group_id','=','adu.user_type')
												->leftJoin('admintransaction as at','gt.trn_id','=','at.trnid')
												->leftJoin('admintrngroups as atg','at.trngroupid','=','atg.trngroupid')
												->where(array('adu.adminuserid'=>$adminuserid,'showtrnflg'=>'Y','at.trnid'=>$trnId))
												->count();
			} else {
				return self::where('adminuserid',$adminuserid)->where('trnid',$trnId)->count();
			}
		} else {
			if(ROLE_WISE_TRN_FLAG) {
				return $admintra_group_items = \DB::table('grouprights_trn as gt')
													->select('at.trnid')
													->leftJoin('groupmaster as gm','gt.group_id','=','gm.group_id')
													->leftJoin('adminuser as adu','gm.group_id','=','adu.user_type')
													->leftJoin('admintransaction as at','gt.trn_id','=','at.trnid')
													->leftJoin('admintrngroups as atg','at.trngroupid','=','atg.trngroupid')
													->where(array('adu.adminuserid'=>Auth()->user()->adminuserid,'showtrnflg'=>'Y','at.trnid'=>$trnId))
													->count();
			} else {
				return self::where('adminuserid',Auth()->user()->adminuserid)->where('trnid',$trnId)->count();
			}
		}
	}
}