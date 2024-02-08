<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Traits\storeImage;
use App\Models\VehicleDocument;
use App\Models\VehicleDriverMappings;
use App\Models\AppointmentCollectionDetails;
use App\Models\AppointmentCollection;
use App\Models\RequestApproval;
use File;
use Image;
use App\Models\UserBaseLocationMapping;
use App\Models\LocationMaster;
use App\Models\BaseLocationCityMapping;
class VehicleMaster extends Model
{
	use storeImage;
	protected 	$table 		=	'vehicle_master';
	protected 	$primaryKey =	'vehicle_id'; // or null
	protected 	$guarded 	=	['vehicle_id'];
	public      $timestamps =   true;
	public $casts = [
		"ref_user_id" => "int"
	];
	public function vehicleEarning(){
		return  $this->hasMany(VehicleEarningMaster::class,'vehicle_id',"vehicle_id");
	}

	public function getVehicleAssetAttribute($value)
	{
		(!empty($value)) ?  $value = explode(",",$value) : $value =  array();
		return $value;
	}
	public function vehicleDocument(){
		return  $this->hasMany(VehicleDocument::class,'vehicle_id');
	}
	public function driver(){
		return $this->hasOne(VehicleDriverMappings::class,'vehicle_id');
	}
	/*
	use  	: Add Vehicle
	Author 	: Axay Shah
	Date 	: 24 Oct,2018
	*/
	public static function addVehicle($request,$fromDispatch=false){
		try{
			DB::beginTransaction();
			$vehicleID 	= 0;
			$vehicle 	= new self();
			$basecity 	= Auth()->user()->city;
			$GetBaseCity = BaseLocationCityMapping::where("base_location_id",Auth()->user()->base_location)->first();
			if($GetBaseCity){
				$basecity = $GetBaseCity->city_id;
			}
			$cityId  	= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : $basecity;
			$vehicle->vehicle_number 		= (isset($request->vehicle_number) && !empty($request->vehicle_number))                     ? $request->vehicle_number : " ";
			$vehicle->prge_vehicle_number 	= (isset($request->vehicle_number)  && !empty($request->vehicle_number))    ? strtolower(str_replace( array( '\'', '"',',' , ';', '<', '>','-',' ' ),'', $request->vehicle_number )) : " ";
			$vehicle->vehicle_name = (isset($request->vehicle_name) 	&& !empty($request->vehicle_name)) ? $request->vehicle_name : " ";
			$vehicle->vehicle_company = (isset($request->vehicle_company) && !empty($request->vehicle_company))? $request->vehicle_company : " ";
			$vehicle->vehicle_type= (isset($request->vehicle_type) 		&& !empty($request->vehicle_type))? $request->vehicle_type : " ";
			$vehicle->vehicle_cost                  = (isset($request->vehicle_cost)    && !empty($request->vehicle_cost))                       ? $request->vehicle_cost               : " ";
			$vehicle->vehicle_model                 = (isset($request->vehicle_model)   && !empty($request->vehicle_model))                      ? $request->vehicle_model              : " ";
			$vehicle->owner_name                    = (isset($request->owner_name)      && !empty($request->owner_name))                         ? $request->owner_name                 : " ";
			$vehicle->owner_aadhar_no               = (isset($request->owner_aadhar_no) && !empty($request->owner_aadhar_no))                    ? $request->owner_aadhar_no            : " ";
			$vehicle->owner_pancard_no              = (isset($request->owner_pancard_no)&& !empty($request->owner_pancard_no))                   ? $request->owner_pancard_no           : " ";
			$vehicle->owner_email_id                = (isset($request->owner_email_id)  && !empty($request->owner_email_id))                     ? $request->owner_email_id             : " ";
			$vehicle->driver_name                   = (isset($request->driver_name)     && !empty($request->driver_name))                        ? $request->driver_name                : " ";
			$vehicle->bank_name                     = (isset($request->bank_name)       && !empty($request->bank_name))                          ? $request->bank_name                  : " ";
			$vehicle->branch_name                   = (isset($request->branch_name)     && !empty($request->branch_name))                        ? $request->branch_name                : " ";
			$vehicle->ifsc_code                     = (isset($request->ifsc_code)       && !empty($request->ifsc_code))                          ? $request->ifsc_code                  : " ";
			$vehicle->account_no                    = (isset($request->account_no)      && !empty($request->account_no))                         ? $request->account_no                 : " ";
			$vehicle->owner_mobile_no               = (isset($request->owner_mobile_no) && !empty($request->owner_mobile_no))                    ? $request->owner_mobile_no            : " ";
			$vehicle->owner_mobile_no_2             = (isset($request->owner_mobile_no_2) && !empty($request->owner_mobile_no_2))                ? $request->owner_mobile_no_2          : " ";
			$vehicle->include_labour                = (isset($request->include_labour)  && !empty($request->include_labour))                     ? $request->include_labour             : " ";
			$vehicle->vehicle_asset                 = (isset($request->vehicle_asset) 	&& !empty($request->vehicle_asset)) ? $request->vehicle_asset : "" ;
			$vehicle->asset_remark                  = (isset($request->asset_remark)    && !empty($request->asset_remark))                       ? $request->asset_remark               : " ";
			$vehicle->status                        =  (isset($request->status)    && !empty($request->status))? $request->status: "P";
			$vehicle->vehicle_size                  = (isset($request->vehicle_size)    && !empty($request->vehicle_size))                       ? $request->vehicle_size               : " ";
			$vehicle->vehicle_volume_capacity       = (isset($request->vehicle_volume_capacity) && !empty($request->vehicle_volume_capacity))    ? $request->vehicle_volume_capacity    : " ";
			$vehicle->vehicle_empty_weight          = (isset($request->vehicle_empty_weight) && !empty($request->vehicle_empty_weight))          ? $request->vehicle_empty_weight       : " ";
			$vehicle->vehicle_rc_book_no            = (isset($request->vehicle_rc_book_no) && !empty($request->vehicle_rc_book_no))              ? $request->vehicle_rc_book_no         : " ";
			$vehicle->vehicle_insurance_no          = (isset($request->vehicle_insurance_no) && !empty($request->vehicle_insurance_no))          ? $request->vehicle_insurance_no       : " ";
			$vehicle->driver_licence_no             = (isset($request->driver_licence_no) && !empty($request->driver_licence_no))                ? $request->driver_licence_no           : " ";
			$vehicle->driver_licence_expiry_date    = (isset($request->driver_licence_expiry_date) && !empty($request->driver_licence_expiry_date)) ? $request->driver_licence_expiry_date : " ";
			$vehicle->vehicle_insurance_expiry_date = (isset($request->vehicle_insurance_expiry_date) && !empty($request->vehicle_insurance_expiry_date)) ? $request->vehicle_insurance_expiry_date : " ";
			$vehicle->account_holder_name           = (isset($request->account_holder_name) && !empty($request->account_holder_name))          ? $request->account_holder_name    : '';
			$vehicle->is_referal           			= (isset($request->is_referal) 				&& !empty($request->is_referal)) 			? $request->is_referal    : 0;
			$vehicle->ref_user_id           		= (isset($request->ref_user_id) 			&& !empty($request->ref_user_id))          	? $request->ref_user_id    : 0;
			$vehicle->created_by                    = Auth()->user()->adminuserid;
			$vehicle->city_id                       = $cityId;
			$vehicle->company_id                    = Auth()->user()->company_id;
			$vehicle->state_id                      = LocationMaster::where("location_id",$cityId)->value('state_id');
			$rc_book_no           					= (isset($request->rc_book_no) && !empty($request->rc_book_no)) ? $request->rc_book_no    : '';
			if($vehicle->save()){
				$vehicleID = $vehicle->vehicle_id; 
				if(isset($request->vehicleDocument) && !empty($request->vehicleDocument)){
					$vehicleDoc = json_decode($request->vehicleDocument);
					$i = 0;
					foreach($vehicleDoc as $vd){
						$request['vehicle_id']    =  $vehicle->vehicle_id;
						$request['document_type'] =  $vd->document_type;
						$request['document_name'] =  $vd->document_name;
						$request['document_note'] =  $vd->document_note;
						$request['document_change'] =  $vd->document_change;
						VehicleDocument::addVehicleDocument($request,$i);
						$i++;
					}
				}
				if($fromDispatch){
					$rc_book_no  = (isset($request->rc_book_no) && !empty($request->rc_book_no)) ? $request->rc_book_no    : '';
					if($request->hasFile("doc_1")){
						$i = 1;
						$request['vehicle_id']    	=  $vehicleID;
						$request['document_type'] 	=  RC_BOOK_ID;
						$request['document_name'] 	=  $rc_book_no;
						$request['document_note'] 	=  $rc_book_no;
						$request['document_change'] =  "";
						$request['document_change'] =  "";
						VehicleDocument::addVehicleDocument($request,$i);
					}
				}
				LR_Modules_Log_CompanyUserActionLog($request,$vehicle->vehicle_id);
			}
			DB::commit();
			if(!$fromDispatch){
				$veh = RequestApproval::saveDataChangeRequest(FORM_VEHICLE_ID,FILED_NAME_VEHICLE,$vehicle->vehicle_id,$vehicle,$cityId); 
			}
			return $vehicleID;
		}catch(\Exception $e) {
			DB::rollback();
			return false;
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
		}
	}
	/*
	use  	: Add Vehicle
	Author 	: Axay Shah
	Date 	: 24 Oct,2018
	*/
	public static function updateVehicle($request){
		try{
			DB::beginTransaction();
			$vehicle  = self::find($request->vehicle_id);
			if($vehicle){
				$cityId 		= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : $vehicle->city_id;
				$original 		= $vehicle->getOriginal();
				$checkStatus 	= RequestApproval::compairOldNewVal($original,$request,FORM_VEHICLE_ID);
				if($checkStatus){
					$vehicle 	= RequestApproval::saveDataChangeRequest(FORM_VEHICLE_ID,FILED_NAME_VEHICLE,$vehicle->vehicle_id,$request,$cityId); 
				}else{
					$vehicle->vehicle_number                = (isset($request->vehicle_number)  && !empty($request->vehicle_number))                     ? $request->vehicle_number             : $vehicle->vehicle_number  ;
					$vehicle->prge_vehicle_number 			= (isset($request->vehicle_number)  && !empty($request->vehicle_number))    ? strtolower(str_replace( array( '\'', '"',',' , ';', '<', '>','-',' ' ),'', $request->vehicle_number )) : strtolower(str_replace( array( '\'', '"',',' , ';', '<', '>','-',' ' ),'', $vehicle->vehicle_number ));
					$vehicle->vehicle_name                  = (isset($request->vehicle_name)    && !empty($request->vehicle_name))                       ? $request->vehicle_name               : $vehicle->vehicle_name    ;
					$vehicle->vehicle_company               = (isset($request->vehicle_company) && !empty($request->vehicle_company))                    ? $request->vehicle_company            : $vehicle->vehicle_company ;
					$vehicle->vehicle_type                  = (isset($request->vehicle_type)    && !empty($request->vehicle_type))                       ? $request->vehicle_type               : $vehicle->vehicle_type    ;
					$vehicle->vehicle_cost                  = (isset($request->vehicle_cost)    && !empty($request->vehicle_cost))                       ? $request->vehicle_cost               : $vehicle->vehicle_cost    ;
					$vehicle->vehicle_model                 = (isset($request->vehicle_model)   && !empty($request->vehicle_model))                      ? $request->vehicle_model              : $vehicle->vehicle_model   ;
					$vehicle->owner_name                    = (isset($request->owner_name)      && !empty($request->owner_name))                         ? $request->owner_name                 : $vehicle->owner_name      ;
					$vehicle->owner_pancard_no              = (isset($request->owner_pancard_no)&& !empty($request->owner_pancard_no))                   ? $request->owner_pancard_no           : $vehicle->owner_pancard_no;
					$vehicle->owner_email_id                = (isset($request->owner_email_id)  && !empty($request->owner_email_id))                     ? $request->owner_email_id             : $vehicle->owner_email_id  ;
					$vehicle->driver_name                   = (isset($request->driver_name)     && !empty($request->driver_name))                        ? $request->driver_name                : $vehicle->driver_name     ;
					$vehicle->bank_name                     = (isset($request->bank_name)       && !empty($request->bank_name))                          ? $request->bank_name                  : $vehicle->bank_name       ;
					$vehicle->branch_name                   = (isset($request->branch_name)     && !empty($request->branch_name))                        ? $request->branch_name                : $vehicle->branch_name     ;
					$vehicle->ifsc_code                     = (isset($request->ifsc_code)       && !empty($request->ifsc_code))                          ? $request->ifsc_code                  : $vehicle->ifsc_code       ;
					$vehicle->account_no                    = (isset($request->account_no)      && !empty($request->account_no))                         ? $request->account_no                 : $vehicle->account_no      ;
					$vehicle->owner_aadhar_no               = (isset($request->owner_aadhar_no) && !empty($request->owner_aadhar_no))                    ? $request->owner_aadhar_no            : $vehicle->owner_aadhar_no ;
					$vehicle->owner_mobile_no               = (isset($request->owner_mobile_no) && !empty($request->owner_mobile_no))                    ? $request->owner_mobile_no            : $vehicle->owner_mobile_no ;
					$vehicle->owner_mobile_no_2             = (isset($request->owner_mobile_no_2) && !empty($request->owner_mobile_no_2))                ? $request->owner_mobile_no_2          : " ";
					$vehicle->include_labour                = (isset($request->include_labour)  && !empty($request->include_labour))                     ? $request->include_labour             : $vehicle->include_labour  ;
					$vehicle->vehicle_asset                 = (isset($request->vehicle_asset) 	&& !empty($request->vehicle_asset)) ? $request->vehicle_asset : "" ;
					$vehicle->asset_remark                  = (isset($request->asset_remark)    && !empty($request->asset_remark))                       ? $request->asset_remark               : $vehicle->asset_remark    ;             
					$vehicle->status                        = (isset($request->status)          && !empty($request->status))                             ? $request->status                     : $vehicle->status          ;             
					$vehicle->vehicle_size                  = (isset($request->vehicle_size)    && !empty($request->vehicle_size))                       ? $request->vehicle_size               : " ";
					$vehicle->vehicle_volume_capacity       = (isset($request->vehicle_volume_capacity) && !empty($request->vehicle_volume_capacity))    ? $request->vehicle_volume_capacity    : $vehicle->vehicle_volume_capacity ;      
					$vehicle->vehicle_empty_weight          = (isset($request->vehicle_empty_weight) && !empty($request->vehicle_empty_weight))          ? $request->vehicle_empty_weight       : $vehicle->vehicle_empty_weight ;        
					$vehicle->vehicle_rc_book_no            = (isset($request->vehicle_rc_book_no) && !empty($request->vehicle_rc_book_no))              ? $request->vehicle_rc_book_no         : $vehicle->vehicle_rc_book_no ;          
					$vehicle->vehicle_insurance_no          = (isset($request->vehicle_insurance_no) && !empty($request->vehicle_insurance_no))          ? $request->vehicle_insurance_no       : $vehicle->vehicle_insurance_no ;        
					$vehicle->driver_licence_no             = (isset($request->driver_licence_no) && !empty($request->driver_licence_no))                ? $request->driver_licence_no           : $vehicle->driver_licence_no  ;          
					$vehicle->driver_licence_expiry_date    = (isset($request->driver_licence_expiry_date) && !empty($request->driver_licence_expiry_date)) ? $request->driver_licence_expiry_date : $vehicle->driver_licence_expiry_date ;   
					$vehicle->vehicle_insurance_expiry_date = (isset($request->vehicle_insurance_expiry_date) && !empty($request->vehicle_insurance_expiry_date)) ? $request->vehicle_insurance_expiry_date : $vehicle->vehicle_insurance_expiry_date ;
					$vehicle->account_holder_name           = (isset($request->account_holder_name) && !empty($request->account_holder_name))          ? $request->account_holder_name    : '';
					$vehicle->is_referal           			= (isset($request->is_referal) 				&& !empty($request->is_referal)) 			? $request->is_referal    : 0;
					$vehicle->ref_user_id           		= (isset($request->ref_user_id) 			&& !empty($request->ref_user_id))          	? $request->ref_user_id    : 0;
					$vehicle->updated_by                    = Auth()->user()->adminuserid;
					$vehicle->city_id                       = $cityId;
					$vehicle->company_id                    = Auth()->user()->company_id;
					$vehicle->state_id                      = Auth()->user()->state_id;
					if($vehicle->save()){
						
						if(isset($request->vehicleDocument) && !empty($request->vehicleDocument)){
							$vehicleDoc = json_decode($request->vehicleDocument);
							$i = 0;
							foreach($vehicleDoc as $vd){
								$request['vehicle_id']    =  $vehicle->vehicle_id;
								$request['document_type'] =  $vd->document_type;
								$request['document_name'] =  $vd->document_name;
								$request['document_note'] =  $vd->document_note;
								$request['document_change'] =  $vd->document_change;
								if(isset($vd->id) && !empty($vd->id)){
									$request['id'] =  $vd->id;
									
									VehicleDocument::UpdateVehicleDocument($request,$i);
								}else{
									if(isset($vd->document_change) && !empty($vd->document_change)){
										$request['id'] =  $vd->document_change;
										VehicleDocument::UpdateVehicleDocument($request,$i);
									}else{
										VehicleDocument::addVehicleDocument($request,$i);
									}
									
								}
								$i++;
							}
						}
						LR_Modules_Log_CompanyUserActionLog($request,$request->vehicle_id);
					}
				}
				DB::commit();
				return true;
				return $vehicle;
			}
		}catch(\Exception $e) {
			prd($e->getMessage());
			DB::rollback();
			return false;
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
		}
	}
	/*
	use  	: Add Vehicle
	Author 	: Axay Shah
	Date 	: 25 Oct,2018
	*/
	public static function getById($request){
		$data = self::with('vehicleDocument')->where('vehicle_id',$request->vehicle_id)->first();
		return $data;
	}
	/*
	use  	: list Vehicle
	Author 	: Axay Shah
	Date 	: 25 Oct,2018
	*/
	public static function list($request){
		$CityId 		= GetBaseLocationCity(Auth()->user()->base_location);
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "vehicle_id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$list           = self::select("vehicle_master.*",
							"a1.firstname as createdByName",
							"a2.firstname as updatedByName",
							"vdm.collection_by as driver_id",
							"LOC.city as city_name",
							DB::raw("CONCAT(COALESCE(a.firstname,''),' ',COALESCE(a.lastname,'')) AS driver_name")
							)
							->join('location_master as LOC',"vehicle_master.city_id","=","LOC.location_id")
							->leftjoin('vehicle_driver_mapping as vdm',"vehicle_master.vehicle_id","=","vdm.vehicle_id")
							->leftjoin('adminuser as a',"vdm.collection_by","=","a.adminuserid")
							->leftjoin('adminuser as a1',"vehicle_master.created_by","=","a1.adminuserid")
							->leftjoin('adminuser as a2',"vehicle_master.updated_by","=","a2.adminuserid");

		if($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id')))
		{
			$list->whereIn('vehicle_master.vehicle_id', explode(",",$request->input('params.vehicle_id')));
		}
		if($request->has('params.vehicle_number') && !empty($request->input('params.vehicle_number')))
		{
			$list->where('vehicle_master.vehicle_number','like','%'.$request->input('params.vehicle_number').'%');
		}
		if($request->has('params.vehicle_type') && !empty($request->input('params.vehicle_type')))
		{
			$list->where('vehicle_master.vehicle_type', $request->input('params.vehicle_type'));
		}
		if($request->has('params.status') && !empty($request->input('params.status')))
		{
			$list->where('vehicle_master.status', $request->input('params.status'));
		}
		if($request->has('params.vehicle_company') && !empty($request->input('params.vehicle_company')))
		{
			$list->where('vehicle_master.vehicle_company','like','%'.$request->input('params.vehicle_company').'%');
		}
		if($request->has('params.city_id') && !empty($request->input('params.city_id')))
		{
			$list->where('vehicle_master.city_id','like',$request->input('params.city_id'));
		}
		if(!empty($request->input('params.created_from')) && !empty($request->input('params.created_to')))
		{
			$list->whereBetween('vehicle_master.created_at',array(date("Y-m-d", strtotime($request->input('params.created_from'))),date("Y-m-d", strtotime($request->input('params.created_to')))));
		}else if(!empty($request->input('params.created_from'))){
		   $list->whereBetween('vehicle_master.created_at',array(date("Y-m-d", strtotime($request->input('params.created_from'))),$Today));
		}else if(!empty($request->input('params.created_to'))){
			$list->whereBetween('vehicle_master.created_at',array(date("Y-m-d", strtotime($request->input('params.created_to'))),$Today));
		}
		$list->whereIn('vehicle_master.city_id',$CityId)
		->where('vehicle_master.company_id',Auth()->user()->company_id)
		->whereNotIn('vehicle_master.status',array(VEHICLE_STATUS_PENDING,VEHICLE_STATUS_REJECT));
		return $list->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	}
	
	/*
	use  	: List vehicle
	Author 	: Axay Shah
	Date 	: 26 Oct,2018
	*/
	// public static function listVehicleNo($report = 0){
	// 	$locationMaster = new LocationMaster();
	// 	$locationTbl 	= $locationMaster->getTable();
	// 	$vehicle 		= (new static)->getTable();
	// 	$CityId 		= GetBaseLocationCity(Auth()->user()->base_location);
	// 	if(!empty($report)){
	// 		$CityId = UserBaseLocationMapping::GetBaseLocationCityListByUser(Auth()->user()->adminuserid);
	// 	}
	// 	return  self::select("$vehicle.vehicle_id","$vehicle.city_id","$vehicle.vehicle_number","$vehicle.vehicle_name","$vehicle.company_id","$vehicle.status","$vehicle.vehicle_type","$vehicle.owner_name","$vehicle.vehicle_cost",
	// 			\DB::raw("$locationTbl.city as city_name"))
	// 			->join("$locationTbl","$vehicle.city_id","=","$locationTbl.location_id")
	// 			->whereIn("$vehicle.city_id",$CityId)
	// 			->where("$vehicle.company_id",Auth()->user()->company_id)
	// 			->where("$vehicle.status",'A')
	// 			->get();
	// }
	public static function listVehicleNo($report = 0,$request){
		$locationMaster = new LocationMaster();
		$locationTbl 	= $locationMaster->getTable();
		$vehicle 		= (new static)->getTable();
		$CityId 		= GetBaseLocationCity(Auth()->user()->base_location);
		$ACCESS_KEY 	= (isset($request->accesskey) && !empty($request->accesskey)) ? $request->accesskey : 0;
		if(!empty($report) || $ACCESS_KEY == 56008){
			$CityId = UserBaseLocationMapping::GetBaseLocationCityListByUser(Auth()->user()->adminuserid);
		}
		$data 		=  self::select("$vehicle.vehicle_id","$vehicle.city_id","$vehicle.vehicle_number","$vehicle.vehicle_name","$vehicle.company_id","$vehicle.status","$vehicle.vehicle_type","$vehicle.owner_name","$vehicle.vehicle_cost",
			\DB::raw("$locationTbl.city as city_name"))
			->join("$locationTbl","$vehicle.city_id","=","$locationTbl.location_id")
			->whereIn("$vehicle.city_id",$CityId)
			->where("$vehicle.company_id",Auth()->user()->company_id)
			->where("$vehicle.status",'A')
			->get()->toArray();
			if(!empty($data)){
				foreach($data as $key => $value){
					$rc_book_no 	= "";
					$rc_book_url 	= "";
					$vehicleDoc = VehicleDocument::where("vehicle_id",$value['vehicle_id'])->where("document_type",RC_BOOK_ID)->first();
					if(!empty($vehicleDoc)){
						$rc_book_no 	= $vehicleDoc->document_name;
						$rc_book_url 	= $vehicleDoc->document_file;
					}
					$data[$key]['rc_book_no']  = $rc_book_no;
					$data[$key]['rc_book_url'] = $rc_book_url;
				}
			}
		return $data;
	}
	/*
	use  	: Get All Vehicle
	Author 	: Axay Shah
	Date 	: 26 Oct,2018
	*/
	public static function getAllVehicle(){
		$CityId 			= 	GetBaseLocationCity();
		$vehicle 			= 	(new self)->getTable();
		$vehicleDriver 		= 	new VehicleDriverMappings();
		$AdminUser 			= 	new AdminUser();
		$Admin 				= 	$AdminUser->getTable();
		$vehicleDriverMpg	= 	$vehicleDriver->getTable();
		$data 				= 	self::select("$vehicle.vehicle_id","$vehicle.vehicle_number",
								DB::raw("CONCAT($Admin.firstname,' ',$Admin.lastname) as driver_name"),"$Admin.adminuserid as driver_id")
								->leftjoin($vehicleDriverMpg,"$vehicle.vehicle_id","=","$vehicleDriverMpg.vehicle_id")
								->leftjoin($Admin,"$vehicleDriverMpg.collection_by","=","$Admin.adminuserid")
								->whereIn("$vehicle.city_id",$CityId)
								->where("$vehicle.company_id",Auth()->user()->company_id)
								->where("$vehicle.status","A")
								->groupBy("$vehicle.vehicle_id")
								->orderBy("$vehicleDriverMpg.updated_date","DESC")
								->get();
		return $data;
	}


	/*
	use  	: List vehicle
	Author 	: Axay Shah
	Date 	: 26 Oct,2018
	*/
	public static function changeStatus($request){
		if(isset($request->vehicle_id) && !empty($request->vehicle_id)){
			if(isset($request->status) && !empty($request->status)){
				 self::where('vehicle_id',$request->vehicle_id)->update(['status'=>$request->status]);
				 LR_Modules_Log_CompanyUserActionLog($request,$request->vehicle_id);
				 return true;
			}
		}else{
			return false;
		}
		
	}

	public static function getVehicleFillLevel($collection_by=0,$StartTime="",$vehicle_id=0){
		$details        = new AppointmentCollectionDetails();
		$productMaster  = new CompanyProductMaster();
		$appointment    = new Appoinment();
		$collection     = new AppointmentCollection();
		$ACD            = $details->getTable(); 
		$PM             = $productMaster->getTable(); 
		$APP            = $appointment->getTable(); 
		$COLL           = $collection->getTable(); 
		$date 			= empty($StartTime)?date("Y-m-d"):$StartTime;
		$StartDate 		= date("Y-m-d",strtotime($date))." ".GLOBAL_START_TIME;
		$EndDate 		= date("Y-m-d",strtotime($date))." ".GLOBAL_END_TIME;

		
		$fillLevel = AppointmentCollectionDetails::select(\DB::raw("SUM(IF(PM.product_volume > 0,$ACD.quantity/PM.product_volume,0)) as collection_volume"))
					->JOIN("$COLL AS CM","CM.collection_id","=","CD.collection_id")
					->JOIN("$APP AS APP","APP.appointment_id","=","CM.appointment_id")
					->JOIN("$PM, AS PM","PM.id","=","CD.product_id")
					->whereIn("APP.para_status_id",array([APPOINTMENT_COMPLETED,APPOINTMENT_SCHEDULED]))
					->whereBetween("APP.app_date_time",[$StartDate,$EndDate])
					->where("APP.collection_by",$collection_by)
					->where("APP.vehicle_id",$vehicle_id)
					->get();

		return $fillLevel;
									
	}

	/**
	* Function Name : GetCollectionVehicles
	* @param
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get All Vehicle Details
	*/
	public static function GetCollectionVehicles() 
	{
		$vehicle 			= 	(new self)->getTable();
		$vehicleDriver 		= 	new VehicleDriverMappings();
		$AdminUser 			= 	new AdminUser();
		$Admin 				= 	$AdminUser->getTable();
		$vehicleDriverMpg	= 	$vehicleDriver->getTable();
		$data 				= 	self::select("$vehicle.vehicle_id","$vehicle.vehicle_number",
								DB::raw("CONCAT($Admin.firstname,' ',$Admin.lastname) as driver_name"),"$Admin.adminuserid as driver_id")
								->leftjoin($vehicleDriverMpg,"$vehicle.vehicle_id","=","$vehicleDriverMpg.vehicle_id")
								->leftjoin($Admin,"$vehicleDriverMpg.collection_by","=","$Admin.adminuserid")
								->groupBy("$vehicle.vehicle_id")
								->orderBy("$vehicleDriverMpg.updated_date","DESC")
								->get();
		$arrVehicles 	= $data->toArray();
		$arrResult 		= array();
		foreach ($arrVehicles as $key => $arrVehicle) 
		{
			$vehicle_number 			= strtolower(preg_replace("/[^\da-z]/i","",$arrVehicle['vehicle_number']));
			$arrResult[$vehicle_number] = $arrVehicle;
		}
		return $arrResult;
	}

	/**
	* Function Name : GetVehicleFillLevelPercentage
	* @param integer $vehicle_id
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to Get Vehicle Fill Level Statistics
	*/
	public static function GetVehicleFillLevelPercentage($vehicle_id=0,$StartDate,$EndDate)
	{
		$SelectSql	= "	SELECT vehicle_master.vehicle_id,
						vehicle_master.vehicle_volume_capacity as Vehicle_Volume,
						CASE WHEN 1=1 THEN (
							SELECT SUM(IF(PM.product_volume > 0,CD.quantity/PM.product_volume,0))
							FROM appointment_collection_details CD
							INNER JOIN appointment_collection CM ON CM.collection_id = CD.collection_id
							INNER JOIN appoinment APP ON APP.appointment_id = CM.appointment_id
							INNER JOIN company_product_master PM ON PM.id = CD.product_id
							WHERE APP.para_status_id IN ('".APPOINTMENT_COMPLETED."','".APPOINTMENT_SCHEDULED."')
							AND APP.app_date_time BETWEEN '$StartDate' AND '$EndDate'
							AND APP.vehicle_id = vehicle_master.vehicle_id
						) END AS collection_volume
						FROM vehicle_master
						WHERE vehicle_master.status 	= '".SHORT_ACTIVE_STATUS."'
						AND vehicle_master.vehicle_id IN (".$vehicle_id.")";
		$SelectRes  			= DB::select($SelectSql);
		$Vehicle_Fill_Level     = 0;
		if (!empty($SelectRes)) {
			foreach ($SelectRes as $SelectRows) {
				if ($SelectRows->Vehicle_Volume > 0 && $SelectRows->collection_volume > 0 && is_int($SelectRows->Vehicle_Volume) && is_int($SelectRows->collection_volume)) {
					$Vehicle_Fill_Level = intval(($SelectRows->collection_volume * 100)/$SelectRows->Vehicle_Volume);
				}
			}
		}
		return $Vehicle_Fill_Level;
	}


	/*
	use  	: List vehicle
	Author 	: Axay Shah
	Date 	: 12 Aug,2019
	*/
	public static function ListVehicleForChart($CityId = 0){
		$list 			= 	array();
		$vehicle 		= 	(new static)->getTable();
		
		$data 			= 	self::select(\DB::raw("$vehicle.vehicle_id as id"),
										\DB::raw("$vehicle.vehicle_number as name")
							)
							->where("$vehicle.company_id",Auth()->user()->company_id)
							->where("$vehicle.status",'A');
							if(!empty($CityId)){
								$data->where("$vehicle.city_id",$CityId);
							}else{
								$CityId 		= 	GetBaseLocationCity();
								$data->whereIn("$vehicle.city_id",$CityId);
							}	
		$list = $data->orderBy("$vehicle.vehicle_number")->get();
		return $list;
	}

	/*
	Use 	: List Owner Name
	Author 	: Axay Shah
	Date 	: 29 Nov,2019
	*/

	public static function listVehicleOwner(){
		$CityId = GetBaseLocationCity(Auth()->user()->base_location);
		$data 	= self::with(["vehicleDocument" =>function($query){
					$query->whereIn("document_type",[1017001,1017009]);
		}])->select("vehicle_id","owner_name","owner_aadhar_no",
				"owner_pancard_no","owner_email_id","bank_name","account_holder_name",
				"branch_name","ifsc_code","account_no","owner_mobile_no","owner_mobile_no_2")
		->where("company_id",Auth()->user()->company_id)
		->whereNotNull('owner_name')
		->where('owner_name','!=','')
		->whereIn("city_id",$CityId)
		->groupBy("owner_name")
		->get();
		
		return $data;
	}
}	
