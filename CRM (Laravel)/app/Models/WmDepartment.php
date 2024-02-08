<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationMaster;
use App\Models\LocationMaster;
use App\Models\UserBaseLocationMapping;
use App\Models\WmDepartmentTitleMaster;
use App\Models\GstStateData;
use App\Models\MasterCodes;
use App\Models\AdminUserRights;
use App\Models\WmSaleableProductTagging;
use App\Facades\LiveServices;
use DB;
class WmDepartment extends Model
{
	//
	protected 	$table 		=	'wm_department';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	protected $casts = [
        'latitude'     => 'float',
        'longitude' => 'float'
    ];
	public function LocationData(){
		$this->belongsTo(LocationMaster::class,"location_id","location_id");
	}

	public function TitleMaster(){
		return $this->hasMany(WmDepartmentTitleMaster::class,"mrf_id","id");
	}
	public function GstStateData(){
		return $this->belongsTo(GstStateData::class,"gst_state_code_id","id");
	}
	public function GetDepartmentGSTCode(){
		return $this->belongsTo(GSTStateCodes::class,"gst_state_code_id","id");
	}
	public function getGstPasswordAttribute($value)
	{
		
	  return passdecrypt($value);
	}
	public function getGstUsernameAttribute($value)
	{
	  return passdecrypt($value);
	}
	/*
	Use 	: Get Department
	Author 	: Axay Shah
	Date 	: 03 May 2019
	*/
	public static function getDepartment($isFromReport = false,$request = "",$mobile=false)
	{
		$TotalBaseLocation  = (isset($request->assigned_base_location) && !empty($request->assigned_base_location)) ? $request->assigned_base_location : UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id");
		$BaseLocationID  	= (isset($request->base_location_id) && !empty($request->base_location_id)) ? $request->base_location_id : "";
		$TRANSFER 	 		=  (isset($request->transfer) && !empty($request->transfer)) ? $request->transfer : '';
		$VIRTUAL_MRF 		=  (isset($request->virtual_mrf_none) && !empty($request->virtual_mrf_none)) ? $request->virtual_mrf_none : '';
		$ONLY_VIRTUAL_MRF 	=  (isset($request->only_virtual_mrf) && !empty($request->only_virtual_mrf)) ? $request->only_virtual_mrf : '';
		$FROM_DISPATCH 		=  (isset($request->from_dispatch) && !empty($request->from_dispatch)) ? $request->from_dispatch : '';
		$NEW_CONDITION 		=  (isset($request->new_condition) && !empty($request->new_condition)) ? $request->new_condition : 0;
		$BASE_LOCATION_FLAG =  (isset($request->base_location_flag) && !empty($request->base_location_flag)) ? $request->base_location_flag : 0;
		$SCREEN_ID 			=  (isset($request->screen_id) && !empty($request->screen_id)) ? $request->screen_id : 0;
		$VIRTUAL 			=  (isset($request->virtual) && !empty($request->virtual)) ? $request->virtual : 0;
		$cityId 	 		=  GetBaseLocationCity();
		$list   	 		=  self::with("TitleMaster")
									->select("wm_department.*",DB::raw("UPPER(TRIM(REPLACE(REPLACE(REPLACE(department_name,'MRF-',''),'MRF -',''),'MRF -',''))) as department_name"))
									->where('status',1);
		if($NEW_CONDITION > 0) {
			if(!empty($BASE_LOCATION_FLAG)) {
				$list->where('base_location_id',Auth()->user()->base_location);
			}
			if(!empty($VIRTUAL)) {
				if($VIRTUAL == 1) {
					$list->where('is_virtual',1);
				} elseif($VIRTUAL == -1) {
					$list->where('is_virtual',"<>",1);
				}
			}
		} else {
			if (!empty($SCREEN_ID)) {
				if (!empty($TotalBaseLocation)) {
					$arrBaseLocationID = array();
					foreach($TotalBaseLocation as $BaseLocation) {
						$BID = isset($BaseLocation['item_id'])?$BaseLocation['item_id']:$BaseLocation;
						array_push($arrBaseLocationID,$BID);
					}
					if (!empty($arrBaseLocationID)) {
						$list->whereIn('base_location_id',array_unique($arrBaseLocationID))->where("is_virtual",0);
					} else {
						$list->where('base_location_id',0);
					}
					$list->where('status',1);
				}
			} else if($FROM_DISPATCH == 1) {
				$list->where('status',1)->where('is_virtual',"!=",1);
			} elseif(!empty($BaseLocationID) && empty($TRANSFER)) {
				$count = AdminUserRights::where("adminuserid",Auth()->user()->adminuserid)->where("trnid",DISPLAY_ALL_MRF_UNLOAD)->count();
				if($count > 0){
					$baseLocationID = UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id")->toArray();
					$list->whereIn('base_location_id',$baseLocationID)->where("display_in_unload",1);
				}else{
					$list->where('base_location_id',Auth()->user()->base_location);
				}
			} else {
				if(!empty($ONLY_VIRTUAL_MRF) && $ONLY_VIRTUAL_MRF == 1) {
					$list->whereIn('base_location_id',$TotalBaseLocation);
					$list->where('is_virtual',1);
				} else {
					if(!empty($TRANSFER)) {
						if(!empty($BaseLocationID)) {
							$list->where('base_location_id',$BaseLocationID);
						} else {
							$list->where('is_virtual',"!=",1);
							$list->where('id',"!=",Auth()->user()->mrf_user_id);
						}
					} elseif(!empty($VIRTUAL_MRF)) {
						$list->where('is_virtual',"!=",1);
					} elseif($isFromReport) {
						$list->whereIn('base_location_id',$TotalBaseLocation);
					}
				}
			}
		}
		if($mobile){
			$list->where("display_in_unload","1");
		}
		$list->where('company_id',Auth()->user()->company_id);
		$result = $list->orderBy("department_name","ASC")->get();
		return $result;
	}

	/*
	Use 	: Get Department For Mobile
	Author 	: Axay Shah
	Date 	: 03 May 2019
	*/
	public static function getDepartmentForMobile($isFromReport = false){
		$TotalBaseLocation = UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id");
		$cityId =   GetBaseLocationCity();
		$list   =   self::where('status',1);
					$list->where('company_id',Auth()->user()->company_id);
					$list->where('is_virtual',"0");
					if($isFromReport == false){
						$list->whereIn('base_location_id',$TotalBaseLocation);
					}
		$result = 	$list->get();
		return $result;
	}
	/*
	Use 	: Get Virtual Department List
	Author 	: Axay Shah
	Date 	: 03 May 2019

	*/
	public static function getVirtualDepartment($isFromReport = false){

		$cityId =   GetBaseLocationCity();
		$list   =   self::where('status',1)->where('is_virtual',1);
					$list->where('company_id',Auth()->user()->company_id);
					$list->whereIn('location_id',$cityId);
		$result =	$list->get();
		return $result;
	}

	/*
	Use 	: List Department
	Author 	: Axay Shah
	Date 	: 03 July,2019
	*/
	public static function ListDepartment($request,$isPainate = true)
	{
		try{
			$table 			= (new static)->getTable();
			$BaseLocation 	= new BaseLocationMaster();
			$LocationMaster = new LocationMaster();
			$Today          = date('Y-m-d');
			$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
			$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
			$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
			$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
			$cityId         = GetBaseLocationCity();
			$createdAt 		= ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input("params.created_from"))) : "";
			$createdTo 		= ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input("params.created_to"))) : "";
			$data 			= self::select("$table.*","L.city as city_name","B.base_location_name")
								->join($LocationMaster->getTable()."  AS L","$table.location_id","=","L.location_id")
								->leftjoin($BaseLocation->getTable()."  AS B","$table.base_location_id","=","B.id")
								->where("$table.company_id",Auth()->user()->company_id);
			if($request->has('params.city_id') && !empty($request->input('params.city_id'))) {
				$data->whereIn("$table.location_id", explode(",",$request->input('params.city_id')));
			} else {
				$data->whereIn("$table.location_id",$cityId);
			}
			if($request->has('params.department_name') && !empty($request->input('params.department_name'))) {
				$data->where("$table.department_name",'like',"%".$request->input('params.department_name')."%");
			}
			if($request->has('params.is_virtual')) {
				$is_virtual =  $request->input('params.is_virtual');
				if($is_virtual == "0") {
					$data->where("$table.is_virtual",$is_virtual);
				} else if($is_virtual == "1") {
					$data->where("$table.is_virtual",$is_virtual);
				}
			}
			if($request->has('params.iot_enabled')) {
				$iot_enabled =  $request->input('params.iot_enabled');
				if($iot_enabled == "0") {
					$data->whereNotIn("$table.iot_enabled",[1]);
				} else if($iot_enabled == "1") {
					$data->whereIn("$table.iot_enabled",[1]);
				}
			}
			if($request->has('params.status')) {
				$status =  $request->input('params.status');
				if($status == "0") {
					$data->where("$table.status",$status);
				} else if($status == "1") {
					$data->where("$table.status",$status);
				}
			}
			if(!empty($createdAt) && !empty($createdTo)) {
				$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
			} else if(!empty($createdAt)) {
				$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME]);
			} else if(!empty($createdTo)) {
				$data->whereBetween("$table.created_at",[$createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
			}
			$data->where("$table.base_location_id",Auth()->user()->base_location);
			if($isPainate == true) {
				$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			} else {
				$result = $data->get();
			}
			return $result;
		}catch(\Exception $e){

		}

	}

	/*
	Use 	: Add Department
	Author 	: Axay Shah
	Date 	: 03 July,2019
	*/
	public static function AddDepartment($request)
	{
		$id 		= 0;
		$Department = new self();
		######## DEPARTMENT LR CODE AUTO GENERATED ###########
		$newCode 	 	= "";
		$newCreatedCode = "";
		$lastCusCode 	= MasterCodes::getMasterCode(MASTER_CODE_MRF);
		if($lastCusCode) {
			$newCreatedCode  = $lastCusCode->code_value + 1;
			$newCode         = $lastCusCode->prefix.''.LeadingZero($newCreatedCode);
		}
		$Department->code 				= $newCode;
		######## DEPARTMENT LR CODE AUTO GENERATED ###########
		$Department->vaf 		= (isset($request['vaf']) && !empty($request['vaf'])) ? $request['vaf'] : 0;
		$purchase_product_id 	= (isset($request['purchase_product_id']) && !empty($request['purchase_product_id'])) ? $request['purchase_product_id'] : array();
		$sales_product_id 		= (isset($request['sales_product_id']) && !empty($request['sales_product_id'])) ? $request['sales_product_id'] : array();
		if(is_array($purchase_product_id)){
			$purchase_product_id = implode(',',$purchase_product_id);	
		}
		if(is_array($sales_product_id)){
			$sales_product_id = implode(',',$sales_product_id);	
		}
		$Department->purchase_product_id 				= $purchase_product_id;
		$Department->sales_product_id 					= $sales_product_id;
		$Department->location_id 						= (isset($request['location_id']) && !empty($request['location_id'])) ? $request['location_id'] : 0;
		$Department->department_name					= (isset($request['department_name']) && !empty($request['department_name'])) ? $request['department_name'] : " ";
		$Department->address							= (isset($request['address']) && !empty($request['address'])) ? $request['address'] : " ";
		$Department->pincode							= (isset($request['pincode']) && !empty($request['pincode'])) ? $request['pincode'] : " ";
		$Department->title								= (isset($request['title']) && !empty($request['title'])) ? $request['title'] : " ";
		$Department->email								= (isset($request['email']) && !empty($request['email'])) ? strtolower($request['email']) : " ";
		$Department->latitude 							= (isset($request['latitude']) && !empty($request['latitude'])) ? $request['latitude'] : 0;
		$Department->longitude							= (isset($request['longitude']) && !empty($request['longitude'])) ? $request['longitude'] : 0;
		$Department->is_virtual 						= (isset($request['is_virtual']) && !empty($request['is_virtual'])) ? $request['is_virtual'] : 0;
		$Department->scheduler_time 					= (isset($request['scheduler_time']) && !empty($request['scheduler_time'])) ? $request['scheduler_time'] : 0;
		$Department->show_in_scheduler					= (isset($request['show_in_scheduler']) && !empty($request['show_in_scheduler'])) ? $request['show_in_scheduler']:0;
		$Department->net_suit_code						= (isset($request['net_suit_code']) && !empty($request['net_suit_code'])) ? $request['net_suit_code']:"";
		$Department->signature							= (isset($request['signature']) && !empty($request['signature'])) ? $request['signature']:"";
		$Department->gst_in								= (isset($request['gst_in']) && !empty($request['gst_in'])) ? $request['gst_in']:"";
		$Department->gst_state_code_id					= (isset($request['gst_state_code']) && !empty($request['gst_state_code'])) ? $request['gst_state_code']:"";
		$Department->status 							= (isset($request['status']) && !empty($request['status'])) ? $request['status'] : 1;
		$Department->mrf_head							= (isset($request['mrf_head']) && !empty($request['mrf_head'])) ? $request['mrf_head']:"";
		$Department->mrf_supervisors					= (isset($request['mrf_supervisors']) && !empty($request['mrf_supervisors'])) ? $request['mrf_supervisors']:"";
		$Department->is_service_mrf						= (isset($request['is_service_mrf']) && !empty($request['is_service_mrf'])) ? $request['is_service_mrf']:0;
		$Department->rate_approval_email				= (isset($request['rate_approval_email']) && !empty($request['rate_approval_email'])) ? $request['rate_approval_email']:"";
		$Department->internal_transfer_approval_email 	= (isset($request['internal_transfer_approval_email']) && !empty($request['internal_transfer_approval_email'])) ? $request['internal_transfer_approval_email']:"";
		$Department->month_recycle_production 			= (isset($request['month_recycle_production']) && !empty($request['month_recycle_production'])) ? $request['month_recycle_production'] : 0;
		$Department->month_afr_production 				= (isset($request['month_afr_production']) && !empty($request['month_afr_production'])) ? $request['month_afr_production'] : 0;
		$Department->month_power_consume_kwh 			= (isset($request['month_power_consume_kwh']) && !empty($request['month_power_consume_kwh'])) ? $request['month_power_consume_kwh'] : 0;
		$Department->base_location_id 					= Auth()->user()->base_location;
		$Department->created_by 						= Auth()->user()->adminuserid;
		$Department->company_id 						= Auth()->user()->company_id;
		$Department->bill_from_mrf 						= (isset($request['bill_from_mrf']) && !empty($request['bill_from_mrf'])) ? $request['bill_from_mrf'] : 0;
		$Department->iot_enabled 						= (isset($request['iot_enabled']) && !empty($request['iot_enabled'])) ? $request['iot_enabled'] : 0;
		if($Department->save()) {
			$id = $Department->id;
			if(!empty($newCreatedCode)) {
				MasterCodes::updateMasterCode(MASTER_CODE_MRF,$newCreatedCode);
			}
			if (isset($request['mrf_saleable_products']) && !empty($request['mrf_saleable_products'])) {
				WmSaleableProductTagging::UpdateProductTagging($id,$request['mrf_saleable_products'],Auth()->user()->adminuserid);
			}
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$id);
		}
		return $id;
	}

	/*
	Use 	: Update Department
	Author 	: Axay Shah
	Date 	: 03 July,2019
	*/
	public static function UpdateDepartment($request)
	{
		$id 		= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : 0;
		$Department = self::find($id);
		if($Department)
		{
			$Department->vaf 		= (isset($request['vaf']) && !empty($request['vaf'])) ? $request['vaf'] : 0;
			$purchase_product_id 	= (isset($request['purchase_product_id']) && !empty($request['purchase_product_id'])) ? $request['purchase_product_id'] : array();
			$sales_product_id 		= (isset($request['sales_product_id']) && !empty($request['sales_product_id'])) ? $request['sales_product_id'] : array();
			if(is_array($purchase_product_id)){
				$purchase_product_id = implode(',',$purchase_product_id);	
			}
			if(is_array($sales_product_id)){
				$sales_product_id = implode(',',$sales_product_id);	
			}
			$Department->purchase_product_id 				= $purchase_product_id;
			$Department->sales_product_id 					= $sales_product_id;
			$Department->location_id 						= (isset($request['location_id']) && !empty($request['location_id'])) ? $request['location_id'] : 0;
			$Department->department_name					= (isset($request['department_name']) && !empty($request['department_name'])) ? $request['department_name']:" ";
			$Department->address							= (isset($request['address']) && !empty($request['address'])) ? $request['address'] : " ";
			$Department->title								= (isset($request['title']) && !empty($request['title'])) ? $request['title'] : " ";
			$Department->email								= (isset($request['email']) && !empty($request['email'])) ? strtolower($request['email']) : " ";
			$Department->latitude 							= (isset($request['latitude']) && !empty($request['latitude'])) ? $request['latitude'] : 0;
			$Department->longitude							= (isset($request['longitude']) && !empty($request['longitude'])) ? $request['longitude'] : 0;
			$Department->is_virtual 						= (isset($request['is_virtual']) && !empty($request['is_virtual'])) ? $request['is_virtual'] : 0;
			$Department->scheduler_time 					= (isset($request['scheduler_time']) && !empty($request['scheduler_time'])) ? $request['scheduler_time'] : 0;
			$Department->show_in_scheduler					= (isset($request['show_in_scheduler']) && !empty($request['show_in_scheduler'])) ? $request['show_in_scheduler']:0;
			$Department->signature							= (isset($request['signature']) && !empty($request['signature'])) ? $request['signature']:"";
			$Department->gst_in								= (isset($request['gst_in']) && !empty($request['gst_in'])) ? $request['gst_in']:"";
			$Department->status 							= (isset($request['status'])) ? $request['status'] : 1;
			$Department->net_suit_code						= (isset($request['net_suit_code']) && !empty($request['net_suit_code'])) ? $request['net_suit_code']:"";
			$Department->gst_state_code_id					= (isset($request['gst_state_code']) && !empty($request['gst_state_code'])) ? $request['gst_state_code']:"";
			$Department->pincode							= (isset($request['pincode']) && !empty($request['pincode'])) ? $request['pincode'] : " ";
			$Department->mrf_head							= (isset($request['mrf_head']) && !empty($request['mrf_head'])) ? $request['mrf_head']:"";
			$Department->mrf_supervisors					= (isset($request['mrf_supervisors']) && !empty($request['mrf_supervisors'])) ? $request['mrf_supervisors']:"";
			$Department->is_service_mrf						= (isset($request['is_service_mrf']) && !empty($request['is_service_mrf'])) ? $request['is_service_mrf']:0;
			$Department->rate_approval_email				= (isset($request['rate_approval_email']) && !empty($request['rate_approval_email'])) ? $request['rate_approval_email']:"";
			$Department->internal_transfer_approval_email 	= (isset($request['internal_transfer_approval_email']) && !empty($request['internal_transfer_approval_email'])) ? $request['internal_transfer_approval_email']:"";
			$Department->month_recycle_production 			= (isset($request['month_recycle_production']) && !empty($request['month_recycle_production'])) ? $request['month_recycle_production'] : 0;
			$Department->month_afr_production 				= (isset($request['month_afr_production']) && !empty($request['month_afr_production'])) ? $request['month_afr_production'] : 0;
			$Department->month_power_consume_kwh 			= (isset($request['month_power_consume_kwh']) && !empty($request['month_power_consume_kwh'])) ? $request['month_power_consume_kwh'] : 0;
			$Department->base_location_id 					= Auth()->user()->base_location;
			$Department->updated_by 						= Auth()->user()->adminuserid;
			$Department->company_id 						= Auth()->user()->company_id;
			$Department->bill_from_mrf 						= (isset($request['bill_from_mrf']) && !empty($request['bill_from_mrf'])) ? $request['bill_from_mrf'] : 0;
			$Department->iot_enabled 						= (isset($request['iot_enabled']) && !empty($request['iot_enabled'])) ? $request['iot_enabled'] : 0;
			if($Department->save()){
				$id = $Department->id;
				if (isset($request['mrf_saleable_products']) && !empty($request['mrf_saleable_products'])) {
					WmSaleableProductTagging::UpdateProductTagging($id,$request['mrf_saleable_products'],Auth()->user()->adminuserid);
				}
				$requestObj = json_encode($request,JSON_FORCE_OBJECT);
				LR_Modules_Log_CompanyUserActionLog($requestObj,$id);
			}
		}
		return $id;
	}

	/*
	Use 	: Add Department
	Author 	: Axay Shah
	Date 	: 03 July,2019
	*/
	public static function GetDepartmentById($id = 0){
		$table 			= (new static)->getTable();
		$LocationMaster = new LocationMaster();
		$BaseLocation 	= new BaseLocationMaster();
		$data 			= self::select("$table.*","L.city as city_name","B.base_location_name")
							->join($LocationMaster->getTable()."  AS L","$table.location_id","=","L.location_id")
							->leftjoin($BaseLocation->getTable()."  AS B","$table.base_location_id","=","B.id")
							->where("$table.id",$id)
							->first();
		$data->purchase_product_list 		= self::GetPurchaseProductsList($data->purchase_product_id);
		$data->sales_product_list 			= self::GetSalesProductsList($data->sales_product_id);
		$data->internal_transfer_approval_email = ($data->internal_transfer_approval_email != "" && $data->internal_transfer_approval_email != null && $data->internal_transfer_approval_email != "null")?$data->internal_transfer_approval_email:"";
		$data->rate_approval_email 				= ($data->rate_approval_email != "" && $data->rate_approval_email != null && $data->rate_approval_email != "null")?$data->rate_approval_email:"";
		$data->mrf_saleable_products 			= WmSaleableProductTagging::GetMRFSaleableProducts($data->id);
		return $data;
	}

	/*
	Use 	: Get Department By Base Location ID
	Author 	: Axay Shah
	Date 	: 23 Auguest,2021
	*/
	public static function GetDepartmentByBaseLocationId($id = array(),$virtual=0){
		$data 	= array();
		$table 	= (new static)->getTable();
		if(!empty($id) && is_array($id)){
			$data = self::whereIn("base_location_id",$id)->where("status","1")->where("is_virtual",$virtual)->pluck("id");
		}
		return $data;
	}

	/*
	Use 	: Get Department with screen id
	Author 	: Axay Shah
	Date 	: 23 Auguest,2021
	*/
	public static function GetDeparmentByScreenID($request){
		$userID 			= (isset(Auth()->user()->adminuserid) ? Auth()->user()->adminuserid : 0);
		$screen_id 			= (isset($request->screen_id) && !empty($request->screen_id)) ? $request->screen_id : 0;
		$assign_mrf 		= (isset($request->assign_mrf) && !empty($request->assign_mrf)) ? $request->assign_mrf : 0;
		$keyword 			= (isset($request->keyword) && !empty($request->keyword)) ? $request->keyword : "";
		$page_from 			= (isset($request->page_from) && !empty($request->page_from)) ? $request->page_from : "";
		$base_station_id 	= (isset($request->base_station_id) && !empty($request->base_station_id)) ? $request->base_station_id : "";
		$CurrentBaseID 		= Auth()->user()->base_location;
		$BaseLocationList 	= GetUserAssignedBaseLocation($userID);
		if (empty($keyword)) {
			$Department 	= WmDepartment::select(	"id","code","net_suit_client_name","net_suit_client_id","net_suit_code",
												DB::raw("UPPER(TRIM(REPLACE(REPLACE(REPLACE(department_name,'MRF-',''),'MRF -',''),'MRF -',''))) as department_name"),
												"location_id","gst_state_code_id","company_id","base_location_id",
												"title","address","pincode","gst_in","latitude","longitude","show_in_scheduler",
												"is_virtual","scheduler_time","status","email","mobile","rate_approval_email",
												"details_from_mrf","is_service_mrf","mrf_head",
												"mrf_supervisors","display_in_sales_target","display_in_unload",
												"created_by","created_at","updated_by","updated_at")
										->where("status",1)
										->where("company_id",Auth()->user()->company_id);
		} else {
			$Department 	= WmDepartment::select("id",DB::raw("UPPER(TRIM(REPLACE(REPLACE(REPLACE(department_name,'MRF-',''),'MRF -',''),'MRF -',''))) as department_name"))
										->where("status",1)
										->where("company_id",Auth()->user()->company_id);
		}
		if (!empty($page_from)) {
			if (isset($request->base_station_id) && !empty($base_station_id)) {
				$Department->where("base_location_id",$base_station_id);
			}
			$Department->where("is_virtual",0);
			$Department->where("is_service_mrf",0);
			$Department->where("display_in_unload",1);
		} else if ($screen_id == VAF_REPORT_SCREEN_ID) {
			$Department->where("vaf",1)->where("status",1);
		} else if(in_array($screen_id,IOT_ENABLED_PLANT_SCREEN_ID)) {
			$Department->where("iot_enabled",1)->where("status",1);
		} else if(in_array($screen_id,BASE_MRF_SCREEN_IDS) && !in_array(Auth()->user()->adminuserid,ALLOW_FOR_UNLOAD_ANY_MRF_USER)) {
			if (isset($request->base_location_flag) && $request->base_location_flag == 1) {
				$Department->where("base_location_id",$CurrentBaseID);
			} else if  (isset($request->base_location_flag) && $request->base_location_flag == 0) {
				$Department->whereNotIn("base_location_id",array($CurrentBaseID));
			} else if  (isset($request->transfer) && $request->transfer == 1) {
				$Department->whereNotIn("base_location_id",array($CurrentBaseID));
			} else {
				$Department->where("base_location_id",$CurrentBaseID);
			}
			$Department->where("is_virtual",0);
		} else if(in_array($screen_id,ASSIGN_BASE_DEPT_IDS)) {
			$BASE_LOCATION_ID = UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id")->toArray();
			$Department->whereIn("base_location_id",$BASE_LOCATION_ID);
			$Department->where("is_virtual",0);
		}else if(in_array($screen_id,ASSIGN_BASE_DEPT_EXCLUDING_SERVICE)){
			$BASE_LOCATION_ID = UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id")->toArray();
			$Department->whereIn("base_location_id",$BASE_LOCATION_ID);
			$Department->where("is_virtual",0);
			$Department->where("is_service_mrf",0);
		}else if ($screen_id == 56037) {
			/** THIS IS FOR SERVICE */
			$Department->where("is_virtual",0);
			$Department->where(function($query) use ($CurrentBaseID) {
				$query->whereIn("base_location_id",array($CurrentBaseID))->orWhere("is_service_mrf",1);
			});
			/** THIS IS FOR SERVICE */
		} else if($assign_mrf == 1) {
			$AssignMRFIDS = AdminUser::where("adminuserid",$userID)->value("assign_mrf_id");
			$Department->whereIn("id",$AssignMRFIDS);
			$Department->where("is_virtual",0);
		}elseif(in_array($screen_id,ALL_MRF_UNLOAD_SCREEN_IDS)) {
			$Department->where("is_virtual",0);
			$Department->where("display_in_unload",1);
			$Department->where("is_service_mrf",0);
		}elseif(in_array($screen_id,BASE_MRF_UNLOAD_SCREEN_IDS)) {
			$Department->where("base_location_id",$CurrentBaseID);
			$Department->where("is_virtual",0);
			$Department->where("display_in_unload",1);
			$Department->where("is_service_mrf",0);
		}
		if (!empty($keyword)) {
			$Department->where("department_name","like","%".$keyword."%");
		}
		$result =  $Department->orderBy("department_name","ASC")->get()->toArray();
		return $result;
	}

	/*
	Use 	: Get Service Departments
	Author 	: Kalpak Prajapati
	Date 	: 18 April 2023
	*/
	public static function GetServiceMRFList($mrf_id=0)
	{
		$BASELOCATIONID = self::select("base_location_id")->where("id",$mrf_id)->first();
		if (isset($BASELOCATIONID->base_location_id) && !empty($BASELOCATIONID->base_location_id)) {
			$ServiceMRFList = self::where("base_location_id",$BASELOCATIONID->base_location_id)->status("status",PARA_STATUS_ACTIVE)->pluck("id","base_location_id")->toArray();
			return $ServiceMRFList;
		} else {
			return array();
		}
	}

	/*
	Use 	: Get Department By Base Location ID
	Author 	: Hardyesh Gupta
	Date 	: 23 Auguest,2021
	*/
	public static function GetDepartmentByBaseLocation($request){
		$id 	= (isset($request->id) && !empty($request->id)) ? $request->id : array();
		$virtual= (isset($request->virtual) && !empty($request->virtual)) ? $request->virtual : 0;
		$data 	= array();
		
		if(!empty($id) && is_array($id)){
			$data = self::select("base_location_master.base_location_name","wm_department.id","wm_department.department_name")
					->leftjoin("base_location_master","base_location_master.id","wm_department.base_location_id")
					->where("wm_department.status",1)
					->where("wm_department.is_virtual",$virtual)
					->whereIn('wm_department.base_location_id',$id)
					->orderBy("base_location_master.id","ASC")
					->get();
		}
		return $data;
	}
	/*
    Use     : Get Purchase & Sales Product List
    Author  : Hardyesh Gupta
    Date    : 27 September, 2023
    */
    public static function GetPurchaseProductsList($product_id){
    	$productArray = explode(',',$product_id);
        $data = "";
        $ProductQuality =  new CompanyProductQualityParameter();
        $ProductMaster  =  new CompanyProductMaster();
		$PMT            =  $ProductMaster->getTable();
        $data = CompanyProductMaster::select("$PMT.id as product_id",
                   DB::raw("CONCAT(name) AS product_name"),
                   DB::raw("CONCAT($PMT.name,' ',QAL.parameter_name) AS product_names")
                )
        		->join($ProductQuality->getTable()." as QAL","$PMT.id","=","QAL.product_id")
        ->whereIn("$PMT.id",$productArray)->get()->toArray();
        return $data;
    }
    
    /*
    Use     : Get Sales Product List
    Author  : Hardyesh Gupta
    Date    : 27 September, 2023
    */
    public static function GetSalesProductsList($product_id){
    	$productArray = explode(',',$product_id);
        $data = "";
        $data = WmProductMaster::select("id",
                     "title",
                     "description"
                )
        ->whereIn('id',$productArray)->get()->toArray();
        return $data;
    }

}