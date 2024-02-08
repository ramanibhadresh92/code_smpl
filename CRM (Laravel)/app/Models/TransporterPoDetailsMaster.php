<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Models\VehicleTypes;
use App\Models\Parameter;
use App\Models\MasterCodes;
use App\Models\TransporterMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class TransporterPoDetailsMaster extends Model implements Auditable
{
	protected 	$table 		=	'transporter_po_details_master';
	protected 	$guarded 	=	['id'];
	protected 	$primaryKey =	'id'; // or null
	public 		$timestamps = 	true;
	use AuditableTrait;
	/*
	Use 	:  Get Vehicle Type 
	Author 	:  Axay Shah
	Date 	:  21 Sep 2021
	*/
	public static function SaveTransporterPOData($request){
		
		return self::SaveTransporterPODataV2($request);
		
		$vendor_id 				= (isset($request->transporter_id) && !empty($request->transporter_id)) ? $request->transporter_id : 0;
		$vehicle_type 			= (isset($request->vehicle_type) && !empty($request->vehicle_type)) ? $request->vehicle_type : '';
		$po_expiry 				= (isset($request->po_expiry) && !empty($request->po_expiry)) ? date("Y-m-d",strtotime($request->po_expiry)) : '';
		$city 					= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
		$mrf_id 				= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$cost_type 				= (isset($request->vehicle_cost_type) && !empty($request->vehicle_cost_type)) ? $request->vehicle_cost_type : '';
		$transporter_name 		= (isset($request->transporter_name) && !empty($request->transporter_name)) ? $request->transporter_name : '';
		$cost 					= (isset($request->vehicle_cost) && !empty($request->vehicle_cost)) ? $request->vehicle_cost : '';
		$id 					= (isset($request->id) && !empty($request->id)) ? $request->id : '';
		$po_id 					= (isset($request->po_id) && !empty($request->po_id)) ? $request->po_id : 0;
		$trip 					= (isset($request->trip) && !empty($request->trip)) ? $request->trip : 0;
		$advance_in_percentage 	= (isset($request->advance_in_percentage) && !empty($request->advance_in_percentage)) ? $request->advance_in_percentage : 0;
		$demurrage 				= (isset($request->demurrage) && !empty($request->demurrage)) ? $request->demurrage : 0;
		$rcm_applicable 		= (isset($request->rcm_applicable) && !empty($request->rcm_applicable)) ? $request->rcm_applicable : 0;
		$destination_city 		= (isset($request->destination_city) && !empty($request->destination_city)) ? $request->destination_city : 0;

		$rate_per_kg 			= (isset($request->rate_per_kg) && !empty($request->rate_per_kg)) ? $request->rate_per_kg : 0;
		$demurrage 				= (isset($request->demurrage) && !empty($request->demurrage)) ? $request->demurrage : 0;
		$createdAt 				= date("Y-m-d H:i:s");
		$adminuserid 			= Auth()->user()->adminuserid;
		$vehicle_capacity_in_kg = (isset($request->vehicle_capacity_in_kg) && !empty($request->vehicle_capacity_in_kg)) ? $request->vehicle_capacity_in_kg : 0;
		$approx_weight_in_kg = (isset($request->approx_weight_in_kg) && !empty($request->approx_weight_in_kg)) ? $request->approx_weight_in_kg : 0;
		$product_type 			= (isset($request->product_type) && !empty($request->product_type)) ? $request->product_type : '';
		$po_for 				= (isset($request->po_for) && !empty($request->po_for)) ? $request->po_for : PARA_PO_FOR_SALES;
		$gst_no 				= (isset($request->gst_no) && !empty($request->gst_no)) ? $request->gst_no: '';
		if($id > 0){
			$save  		= self::find($id);
		}else{
			$save  		= new self;
		}
		$save->vendor_id 			= $vendor_id;
		$save->trip 				= $trip;
		$save->vendor_name 			= $transporter_name;
		$save->po_id 				= $po_id;
		$save->po_expiry 			= $po_expiry;
		$save->city_id 				= $city;
		$save->mrf_id 				= $mrf_id;
		$save->vehicle_type 		= $vehicle_type;
		$save->rate_per_kg 			= $rate_per_kg;
		$save->vehicle_capacity_in_kg = $vehicle_capacity_in_kg;
		$save->approx_weight_in_kg 	= $approx_weight_in_kg;
		$save->vehicle_cost_type 	= $cost_type;
		$save->vehicle_cost 		= $cost;
		$save->created_at 			= $createdAt;
		$save->created_by 			= $adminuserid;
		$save->updated_by 			= $adminuserid;
		$save->updated_at 			= $createdAt;
		$save->demurrage 			= $demurrage;
		$save->destination_city 	= $destination_city;
		$save->rcm_applicable 		= $rcm_applicable;
		$save->advance_in_percentage = $advance_in_percentage;
		$save->product_type 		= $product_type;
		$save->po_for				= $po_for;
		$save->gst_no				= $gst_no;
		$save->company_id 			= Auth()->user()->company_id;
		if($save->save()){
			$DESTINATION_CITY_NAME 			= LocationMaster::where("location_id",$destination_city)->value("city");
			$SOURCE_CITY_NAME 				= LocationMaster::where("location_id",$city)->value("city");
			$vehicle_type_name 				= VehicleTypes::where("id",$vehicle_type)->value("vehicle_type");
			$vehicle_cost_type_name 		= Parameter::where("para_id",$cost_type)->value("para_value");
			$id 							= $save->id;
			$PerTripCost 					= ($cost > 0 && $trip > 0) ? _FormatNumberV2($cost / $trip) : 0;
			$capacityDesc 					= "";
			if($cost_type == PER_TONNE_AT_VEHICLE_CAPACITY){
				$capacityDesc = "vehicle Capacity in Kg.@ $vehicle_capacity_in_kg & rate per kg.@"._FormatNumberV2($rate_per_kg)." Rs.";
			}elseif($cost_type == PER_TONNE_ACTUAL_CAPACITY){
				$capacityDesc = "rate per kg.@"._FormatNumberV2($rate_per_kg)." Rs.";
			}
			$PRODUCT_TYPE_NAME 				= "";
			if(!empty($product_type)){
				$PRODUCT_TYPE_NAME 	= Parameter::where("para_id",$product_type)->value("para_value");
				$PRODUCT_TYPE_NAME = (!empty($PRODUCT_TYPE_NAME)) ? " For ".$PRODUCT_TYPE_NAME : "";
			}
			$PO_FOR 	= "";
			if(!empty($po_for)){
				$PO_FOR		= Parameter::where("para_id",$po_for)->value("para_value");
				$PO_FOR 	= (!empty($PO_FOR)) ? " PO For - ".$PO_FOR : "";
			}
			$createReq 						= array();
			$item 							= array();
			$item['item_id'] 				= BAMS_TRANSPORTE_ITEM_ID;
			$item['uom'] 					= ($cost_type == PER_TONNE_ACTUAL_CAPACITY) ? PARA_PRODUCT_UNIT_IN_KG : BAMS_TRANSPORTE_UOM_ID;
			$item['qty'] 					= ($cost_type == PER_TONNE_ACTUAL_CAPACITY) ? $approx_weight_in_kg :  $trip;     
			$item['rate'] 					= ($cost_type == PER_TONNE_ACTUAL_CAPACITY) ? _FormatNumberV2($rate_per_kg): $PerTripCost;
			$item['hsn_code'] 				= ($rcm_applicable == 1) ? 996791 : 996511;
			$item['hsn_code_id'] 			= ($rcm_applicable == 1) ? 17370 : 17334;
			$item['description'] 			= $vehicle_type_name." ".$vehicle_cost_type_name." ".$capacityDesc." "."(From city $SOURCE_CITY_NAME to $DESTINATION_CITY_NAME) ".$PRODUCT_TYPE_NAME." ".$PO_FOR;
			$createReq['rcm_applicable'] 	= $rcm_applicable;
			$createReq['vendor_id'] 		= $save->vendor_id;
			$createReq['po_expiry'] 		= $po_expiry;
			$createReq['is_open_po'] 		= 1;
			$createReq['lr_record_id'] 		= $id;
			$createReq['adv_type'] 			= BAMS_ADVANCE_PERCENTAGE_PARAMETER;
			$createReq['adv_amt'] 			= $advance_in_percentage;
			$createReq['item'][] 			= $item;
			$createReq['from_lr'] 			= 1;
			$createReq['mrf_id'] 			= $mrf_id;
			$createReq['companyBranch'] 	= $mrf_id;
			$createReq['id'] 				= $save->po_id;
			$createReq['po_for'] 			= $save->po_for;
			$token 							= self::LoginInBAMS($createReq);
			if(!empty($token)){
				self::SendVendorPODataToBAMS($createReq,$token);	
			}
			LR_Modules_Log_CompanyUserActionLog($request,$id);
		}
		return $id;
	}


	/*
	Use 	:  Get Vehicle Type 
	Author 	:  Axay Shah
	Date 	:  21 Sep 2021
	*/
	public static function SaveTransporterPODataV2($request){
		$vendor_id 				= (isset($request->transporter_id) && !empty($request->transporter_id)) ? $request->transporter_id : 0;
		$vehicle_type 			= (isset($request->vehicle_type) && !empty($request->vehicle_type)) ? $request->vehicle_type : '';
		$po_expiry 				= (isset($request->po_expiry) && !empty($request->po_expiry)) ? date("Y-m-d",strtotime($request->po_expiry)) : '';
		$city 					= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
		$mrf_id 				= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$cost_type 				= (isset($request->vehicle_cost_type) && !empty($request->vehicle_cost_type)) ? $request->vehicle_cost_type : '';
		$transporter_name 		= (isset($request->transporter_name) && !empty($request->transporter_name)) ? $request->transporter_name : '';
		$cost 					= (isset($request->vehicle_cost) && !empty($request->vehicle_cost)) ? $request->vehicle_cost : '';
		$id 					= (isset($request->id) && !empty($request->id)) ? $request->id : '';
		$po_id 					= (isset($request->po_id) && !empty($request->po_id)) ? $request->po_id : 0;
		$trip 					= (isset($request->trip) && !empty($request->trip)) ? $request->trip : 0;
		$advance_in_percentage 	= (isset($request->advance_in_percentage) && !empty($request->advance_in_percentage)) ? $request->advance_in_percentage : 0;
		$demurrage 				= (isset($request->demurrage) && !empty($request->demurrage)) ? $request->demurrage : 0;
		$rcm_applicable 		= (isset($request->rcm_applicable) && !empty($request->rcm_applicable)) ? $request->rcm_applicable : 0;
		$destination_city 		= (isset($request->destination_city) && !empty($request->destination_city)) ? $request->destination_city : 0;

		$rate_per_kg 			= (isset($request->rate_per_kg) && !empty($request->rate_per_kg)) ? $request->rate_per_kg : 0;
		$demurrage 				= (isset($request->demurrage) && !empty($request->demurrage)) ? $request->demurrage : 0;
		$createdAt 				= date("Y-m-d H:i:s");
		$adminuserid 			= Auth()->user()->adminuserid;
		$vehicle_capacity_in_kg = (isset($request->vehicle_capacity_in_kg) && !empty($request->vehicle_capacity_in_kg)) ? $request->vehicle_capacity_in_kg : 0;
		$approx_weight_in_kg = (isset($request->approx_weight_in_kg) && !empty($request->approx_weight_in_kg)) ? $request->approx_weight_in_kg : 0;
		$product_type 			= (isset($request->product_type) && !empty($request->product_type)) ? $request->product_type : '';
		$po_for 				= (isset($request->po_for) && !empty($request->po_for)) ? $request->po_for : PARA_PO_FOR_SALES;
		$gst_no 				= (isset($request->gst_no) && !empty($request->gst_no)) ? $request->gst_no: '';
		######## NEW CODE FOR TRANSPORTER ############
		$TRANSPOTER_ID 			= (isset($request->lr_transporter_id) && !empty($request->lr_transporter_id)) ? $request->lr_transporter_id : 0;
		$TRANSPORTER_NAME 		= $transporter_name;
		$NAME 					= $transporter_name;
		if(empty($TRANSPOTER_ID) || !empty($TRANSPORTER_NAME)){
			$TRANSPORTER_NAME 	= (!empty($TRANSPORTER_NAME)) ? str_replace(' ','',strtolower($TRANSPORTER_NAME)) : str_replace(' ','',strtolower($NAME));
			$TRANSPORTER_DATA 	= TransporterMaster::where("md5_code","=",md5($TRANSPORTER_NAME))->first();
		}else{
			$TRANSPORTER_DATA 	= TransporterMaster::find($TRANSPOTER_ID);
		}
		######## TRANSPORTER CODE AUTO GENERATED###########
		$newCode 	 	= "";
		$newCreatedCode = "";
		$lastCusCode 	= MasterCodes::getMasterCode(MASTER_CODE_TRANSPORTER);
		if($lastCusCode){
			$newCreatedCode  = $lastCusCode->code_value + 1;
			$newCode         = $lastCusCode->prefix.''.LeadingZero($newCreatedCode);
		}
		if(!$TRANSPORTER_DATA){
 			$TRANSPORTER_DATA 			= new TransporterMaster();
 			$TRANSPORTER_DATA->name 	= $NAME;
 			$TRANSPORTER_DATA->md5_code = md5(str_replace(' ','',strtolower($NAME)));
 			$TRANSPORTER_DATA->code 	= $newCode;
 			if($TRANSPORTER_DATA->save()){
 				$TRANSPOTER_ID = $TRANSPORTER_DATA->id;
 				if(!empty($newCreatedCode)){
					MasterCodes::updateMasterCode(MASTER_CODE_TRANSPORTER,$newCreatedCode);
				}
 			}
		}else{
			$TRANSPOTER_ID = $TRANSPORTER_DATA->id;
		}
		######################################
		if($id > 0){
			$save  		= self::find($id);
		}else{
			$save  		= new self;
		}
		$save->transporter_id		= $TRANSPOTER_ID;
		$save->vendor_id 			= $vendor_id;
		$save->trip 				= $trip;
		$save->vendor_name 			= $transporter_name;
		$save->po_id 				= $po_id;
		$save->po_expiry 			= $po_expiry;
		$save->city_id 				= $city;
		$save->mrf_id 				= $mrf_id;
		$save->vehicle_type 		= $vehicle_type;
		$save->rate_per_kg 			= $rate_per_kg;
		$save->vehicle_capacity_in_kg = $vehicle_capacity_in_kg;
		$save->approx_weight_in_kg 	= $approx_weight_in_kg;
		$save->vehicle_cost_type 	= $cost_type;
		$save->vehicle_cost 		= $cost;
		$save->created_at 			= $createdAt;
		$save->created_by 			= $adminuserid;
		$save->updated_by 			= $adminuserid;
		$save->updated_at 			= $createdAt;
		$save->demurrage 			= $demurrage;
		$save->destination_city 	= $destination_city;
		$save->rcm_applicable 		= $rcm_applicable;
		$save->advance_in_percentage = $advance_in_percentage;
		$save->product_type 		= $product_type;
		$save->po_for				= $po_for;
		$save->gst_no				= $gst_no;
		$save->company_id 			= Auth()->user()->company_id;
		if($save->save()){
			$DESTINATION_CITY_NAME 			= LocationMaster::where("location_id",$destination_city)->value("city");
			$SOURCE_CITY_NAME 				= LocationMaster::where("location_id",$city)->value("city");
			$vehicle_type_name 				= VehicleTypes::where("id",$vehicle_type)->value("vehicle_type");
			$vehicle_cost_type_name 		= Parameter::where("para_id",$cost_type)->value("para_value");
			$id 							= $save->id;
			$PerTripCost 					= ($cost > 0 && $trip > 0) ? _FormatNumberV2($cost / $trip) : 0;
			$capacityDesc 					= "";
			if($cost_type == PER_TONNE_AT_VEHICLE_CAPACITY){
				$capacityDesc = "vehicle Capacity in Kg.@ $vehicle_capacity_in_kg & rate per kg.@"._FormatNumberV2($rate_per_kg)." Rs.";
			}elseif($cost_type == PER_TONNE_ACTUAL_CAPACITY){
				$capacityDesc = "rate per kg.@"._FormatNumberV2($rate_per_kg)." Rs.";
			}
			$PRODUCT_TYPE_NAME 				= "";
			if(!empty($product_type)){
				$PRODUCT_TYPE_NAME 	= Parameter::where("para_id",$product_type)->value("para_value");
				$PRODUCT_TYPE_NAME = (!empty($PRODUCT_TYPE_NAME)) ? " For ".$PRODUCT_TYPE_NAME : "";
			}
			$PO_FOR 	= "";
			if(!empty($po_for)){
				
				$PO_FOR		= Parameter::where("para_id",$po_for)->value("para_value");
				$PO_FOR 	= (!empty($PO_FOR)) ? " PO For - ".$PO_FOR : "";
			}
			$createReq 						= array();
			$item 							= array();
			$item['item_id'] 				= BAMS_TRANSPORTE_ITEM_ID;
			$item['uom'] 					= ($cost_type == PER_TONNE_ACTUAL_CAPACITY) ? PARA_PRODUCT_UNIT_IN_KG : BAMS_TRANSPORTE_UOM_ID;
			$item['qty'] 					= ($cost_type == PER_TONNE_ACTUAL_CAPACITY) ? $approx_weight_in_kg :  $trip;     
			$item['rate'] 					= ($cost_type == PER_TONNE_ACTUAL_CAPACITY) ? _FormatNumberV2($rate_per_kg): $PerTripCost;
			$item['hsn_code'] 				= ($rcm_applicable == 1) ? 996791 : 996511;
			$item['hsn_code_id'] 			= ($rcm_applicable == 1) ? 17370 : 17334;
			$item['description'] 			= $vehicle_type_name." ".$vehicle_cost_type_name." ".$capacityDesc." "."(From city $SOURCE_CITY_NAME to $DESTINATION_CITY_NAME) ".$PRODUCT_TYPE_NAME." ".$PO_FOR;
			$createReq['rcm_applicable'] 	= $rcm_applicable;
			$createReq['vendor_id'] 		= $save->vendor_id;
			$createReq['po_expiry'] 		= $po_expiry;
			$createReq['is_open_po'] 		= 1;
			$createReq['lr_record_id'] 		= $id;
			$createReq['adv_type'] 			= BAMS_ADVANCE_PERCENTAGE_PARAMETER;
			$createReq['adv_amt'] 			= $advance_in_percentage;
			$createReq['item'][] 			= $item;
			$createReq['from_lr'] 			= 1;
			$createReq['mrf_id'] 			= $mrf_id;
			$createReq['companyBranch'] 	= $mrf_id;
			$createReq['id'] 				= $save->po_id;
			$createReq['po_for'] 			= $save->po_for;
			$token 							= self::LoginInBAMS($createReq);
			if(!empty($token)){
				self::SendVendorPODataToBAMS($createReq,$token);	
			}
			LR_Modules_Log_CompanyUserActionLog($request,$id);
		}
		return $id;
	}
	/*
	Use 	:  Transporter Listing
	Author 	:  Axay Shah
	Date 	:  17 March 2021
	*/
	public static function ListTransporterPODetails($request,$isPainate=true){
		$table 			= (new static)->getTable();
		$BaseLocation 	= new BaseLocationMaster();
		$Location 		= new LocationMaster();
		$Transporter 	= new TransporterMaster();
		$Vehicle 		= new VehicleMaster();
		$Admin 			= new AdminUser();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy') && !empty($request->input('sortBy')))  ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$createdAt 		= ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input("params.created_from"))) : "";
		$createdTo 		= ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input("params.created_to"))) : "";
		$po_no 			= ($request->has('params.po_no') && $request->input('params.po_no')) ? $request->input("params.po_no") : "";
		$vehicle_type 	= ($request->has('params.vehicle_type') && $request->input('params.vehicle_type')) ? $request->input("params.vehicle_type") : "";
		$vehicle_cost_type = ($request->has('params.vehicle_cost_type') && $request->input('params.vehicle_cost_type')) ? $request->input("params.vehicle_cost_type") : "";
		$city_id 	= ($request->has('params.city_id') && $request->input('params.city_id')) ? $request->input("params.city_id") : "";
		$mrf_id 	= ($request->has('params.mrf_id') && $request->input('params.mrf_id')) ? $request->input("params.mrf_id") : "";
		$vendor_name 	= ($request->has('params.vendor_name') && $request->input('params.vendor_name')) ? $request->input("params.vendor_name") : "";
		$product_type 	= ($request->has('params.product_type') && $request->input('params.product_type')) ? $request->input("params.product_type") : "";
		$po_for 		= ($request->has('params.po_for') && $request->input('params.po_for')) ? $request->input("params.po_for") : "";
		$data 	= self::select(
					"$table.*",
					\DB::raw("IF($table.status = 0,1,0) as canEdit"),
					\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
					\DB::raw("P1.para_value as vehicle_cost_type_name"),
					\DB::raw("P2.para_value as product_type"),
					\DB::raw("P3.para_value as po_for_name"),
					\DB::raw("transporter_po_details_master.po_for as po_for"),
					\DB::raw("VT.vehicle_type as vehicle_type_name"),
					\DB::raw("DEPT.department_name"),
					\DB::raw("CITY.city as city_name"),
					\DB::raw("(CASE WHEN $table.status = 0 THEN 'Pending'
					WHEN $table.status = 1 THEN 'Approved'
					WHEN $table.status = 3 THEN 'Verified'
					WHEN $table.status = 3 THEN 'Checked'
					WHEN $table.status = 2 THEN 'Rejected'
					END ) AS status_name")
		)
		->leftjoin($Admin->getTable()." as U1","$table.created_by","=","U1.adminuserid")
		->leftjoin("parameter as P1","$table.vehicle_cost_type","=","P1.para_id")
		->leftjoin("parameter as P2","$table.product_type","=","P2.para_id")
		->leftjoin("parameter as P3","$table.po_for","=","P3.para_id")
		->leftjoin("vehicle_type_master as VT","$table.vehicle_type","=","VT.id")
		->leftjoin("wm_department as DEPT","$table.mrf_id","=","DEPT.id")
		->leftjoin("location_master as CITY","$table.city_id","=","CITY.location_id")
		->where("$table.company_id",Auth()->user()->company_id);
		if(!empty($po_no)){
			$data->where("$table.po_no","like","%".$po_no."%");
		}
		if(!empty($vehicle_cost_type)){
			$data->where("$table.vehicle_cost_type",$vehicle_cost_type);
		}
		if(!empty($mrf_id)){
			$data->where("$table.mrf_id",$mrf_id);
		}
		if(!empty($city_id)){
			$data->where("$table.city_id",$city_id);
		}
		if(!empty($vehicle_type)){
			$data->where("$table.vehicle_type",$vehicle_type);
		}
		if(!empty($vendor_name)){
			$data->where("$table.vendor_name","like","%".$vendor_name."%");
		}
		if(!empty($po_no)){
			$data->where("$table.po_no","like","%".$po_no."%");
		}
		if(!empty($createdAt) && !empty($createdTo)){
			$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		}elseif(!empty($createdAt)){
			$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME]);
		}elseif(!empty($createdTo)){
			$data->whereBetween("$table.created_at",[$createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		}
		if(!empty($product_type)){
			$data->where("$table.product_type",$product_type);
		}
		if(!empty($po_for)){
			$data->where("$table.po_for",$po_for);
		}
		if($isPainate == true){
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}else{
			$result = $data->get();
		}
		return $result;
	}

	/*
	Use 	:  Transporter Listing
	Author 	:  Axay Shah
	Date 	:  06 Octomber 2022
	*/
	public static function GetById($id){
		$result = self::find($id);
		return $result;
	}
	/*
	Use 	:  Get Transporter List From BAMS
	Author 	:  Axay Shah
	Date 	:  06 Octomber 2022
	*/
	public static function GetVendorDataFromBAMS($request){
		$res 		= "";
		$token 		= self::LoginInBAMS($request);
		if(empty($token)){
			return "-1" ;
		}
		if(!empty($token)){
			$authorization = "Authorization: Bearer $token";
			$keyword 	= (isset($request->keyword) && !empty($request->keyword))?$request->keyword : "";
			$ch 		= curl_init();
	  		$apiURL 	= PROJECT_BAMS_VENDOR_DATA_URL;
		    $dataArray 	= ['keyword' => $keyword,"from_lr"=>1];
		  	$ch = curl_init();
		
			$authorization = "Authorization: Bearer $token";
			$curl = curl_init($apiURL);
	        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
	    	// curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($curl, CURLOPT_POST, true);
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataArray));
	        // curl_setopt($curl, CURLOPT_POSTFIELDS, $postRequest);
		    $response 	= curl_exec($curl);
	        $res 		= json_decode($response);
		    if(isset($res->data)){
				$res = $res->data;
       		}
		}
		return $res;
	}
	/*
	Use 	:  Send PO Details 
	Author 	:  Axay Shah
	Date 	:  13 Octomber 2022
	*/
	public static function SendVendorPODataToBAMS($request,$token){
		$ch = curl_init();
		$apiURL = PROJECT_BAMS_PO_CREATE_URL;
		$authorization = "Authorization: Bearer $token";
		$curl = curl_init($apiURL);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
    	// curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request));
        $response = curl_exec($curl);
        $response = json_decode($response);
       	self::AddRequestLog(Auth()->user()->orange_code,json_encode($request),json_encode($response),Auth()->user()->adminuserid,$apiURL);

        if(isset($response->data->po_id) && !empty($response->data->po_id)){
       		self::where("id",$request['lr_record_id'])->update(["po_id"=>$response->data->po_id,"po_no"=>$response->data->po_number]);
       	}
        curl_close($curl);
        return $response;
	}


	/*
	Use 	:  Send PO Details 
	Author 	:  Axay Shah
	Date 	:  13 Octomber 2022
	*/
	public static function LoginInBAMS($request,$ORANGE_CODE ="",$ADMIN_USER_ID=0)
	{
		$ch 		= curl_init();
		$apiURL 	= "https://bams.nepra.co.in/api/company/login";
       	$username 	=  (isset(Auth()->user()->orange_code) && !empty(Auth()->user()->orange_code)) ? Auth()->user()->orange_code : $ORANGE_CODE;
       	$ADMIN_USER_ID 	=  (isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid)) ? Auth()->user()->adminuserid : $ADMIN_USER_ID;
       	$password 	= "test";
       	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Header-Key: Header-Value',
		    'Header-Key-2: Header-Value-2'
		));
		$postRequest 	= array("username"=>$username,"password"=>$password,"from_lr"=>1);
		$cURLConnection = curl_init($apiURL);
		curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postRequest);
		curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
		$apiResponse 		= curl_exec($cURLConnection);
		$jsonArrayResponse 	= json_decode($apiResponse);

		curl_close($cURLConnection);
		self::AddRequestLog($username,json_encode($postRequest),$apiResponse,$ADMIN_USER_ID,$apiURL);

		if(isset($jsonArrayResponse->status) && $jsonArrayResponse->status == 1){
			return $token = $jsonArrayResponse->data->access_token;
		}
		return '';
	}

	/*
	Use 	:  Update Transporter PO DATA
	Author 	:  Axay Shah
	Date 	:  14 Octomber 2022
	*/
	public static function UpdatePOStatusFromBAMS($request){
		$lr_record_id 	= (isset($request->lr_record_id) && !empty($request->lr_record_id)) ? $request->lr_record_id : 0;
		$po_number 		= (isset($request->po_number) && !empty($request->po_number)) ? $request->po_number : 0;
		$status 		= (isset($request->status) && !empty($request->status)) ? $request->status : 0;
		if($status != 0){
			self::where("id",$lr_record_id)->update(array("status"=>$status,"po_no"=>$po_number));
			return true;
		}
		return false;
	}

	/*
	Use 	:  PO Dropdown 
	Author 	:  Axay Shah
	Date 	:  06 Octomber 2022
	*/
	public static function PODropDown($request)
	{
		$transporter_id 	= (isset($request->transporter_id) && !empty($request->transporter_id)) ? $request->transporter_id : 0;
		$cityId = GetBaseLocationCity();
		$po_id 	= (isset($request->po_id) && !empty($request->po_id)) ? $request->po_id : 0;
		$result = array();
		$data 	= self::select(	\DB::raw("transporter_po_details_master.*"),
								\DB::raw("CITY.city as city_name"),
								\DB::raw("sum(TDM.rate) as total_po_use_amt"))
					->leftjoin("location_master as CITY","transporter_po_details_master.city_id","=","CITY.location_id")
					->leftjoin("wm_department as WD","transporter_po_details_master.mrf_id","=","WD.id")
					->leftjoin("transporter_details_master as TDM","transporter_po_details_master.id","=","TDM.po_detail_id");
		if($transporter_id > 0){
			$data->where("transporter_po_details_master.transporter_id",$transporter_id);	
		}			
		if($po_id > 0) {
			$data->where("po_detail_id",$po_id);
		} else {
			$data->where("transporter_po_details_master.po_expiry",">=",date("Y-m-d"));
			$data->where("transporter_po_details_master.status",1);
		}
		if(!empty($po_for)){
			$data->where("transporter_po_details_master.po_for",$po_for);
		}
		// $data->whereIn("WD.location_id",$cityId);
		// LiveServices::toSqlWithBinding($data);
		$res = $data->orderBy("po_no")->groupBy("transporter_po_details_master.id")->get()->toArray();
		if(!empty($res))
		{
			foreach($res as $key => $value){
				$res[$key]['per_trip_rate'] 	= ($value['trip'] > 0) ? _FormatNumberV2($value['vehicle_cost'] / $value['trip']) : 0;
				$res[$key]['vehicle_cost'] 		= _FormatNumberV2($value['vehicle_cost']);
				$remainAmount 					= _FormatNumberV2($value['vehicle_cost'] - $value['total_po_use_amt']);
				$res[$key]['remain_amount'] 	= $remainAmount;
				if($po_id > 0){
					$result[] = $res[$key];
				}else{
					if($remainAmount > 0){
						$result[] = $res[$key];
					}
				}
			}
		}
		return $result;
	}
		/*
	Use 	:  Add Request Log
	Author 	:  Axay Shah
	Date 	:  06 Octomber 2022
	*/
	public static function AddRequestLog($emp_code,$req,$res,$request_by,$api_url)
	{
		\DB::table('lr_to_bams_request_log')->insert(
			array(
				"emp_code" 		=> $emp_code,
				"request_data" 	=> $req,
				"response_data" => $res,
				"created" 		=> date("Y-m-d H:i:s"),
				"request_by" 	=> $request_by,
				"api_url" 		=> $api_url
			)
		);
	}
	/*
	Use 	:  Send Request from LR To BAMS
	Author 	:  Axay Shah
	Date 	:  06 Octomber 2022
	*/
	public static function SendRequestFromLrToBams($request,$token)
	{
		$ch 			= curl_init();
		$apiURL 		= "https://bams.nepra.co.in/api/company/po/transporter/create";
		$authorization 	= "Authorization: Bearer $token";
		$curl 			= curl_init($apiURL);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
    	curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request));
        $response = curl_exec($curl);
        $response = json_decode($response);
        self::AddRequestLog(Auth()->user()->orange_code,json_encode($request),json_encode($response),Auth()->user()->adminuserid,$apiURL);
        if(isset($response->data->po_id) && !empty($response->data->po_id)){
       		self::where("id",$request['lr_record_id'])->update(["po_id"=>$response->data->po_id,"po_no"=>$response->data->po_number]);
       	}
        curl_close($curl);
        return $response;
	}

	/*
	Use 	:  Check PO From EPR REQUEST
	Author 	:  Hardyesh Gupta
	Date 	:  30 Septemper 2023
	*/
	public static function checkPOFromEPR($request){
		$res 			= array("code"=>"","status"=>"","data"=>"","message"=>"");
		$epr_po_no 		= (isset($request->epr_po_no) && !empty($request->epr_po_no)) ? $request->epr_po_no : 0;
		$gst_state_code = (isset($request->gst_state_code) && !empty($request->gst_state_code)) ? $request->gst_state_code : 0;
		$display_state_code = GSTStateCodes::where("id",$gst_state_code)->value("display_state_code");
		$createdby_userid  	= (\Auth::check()) ? Auth()->user()->adminuserid :  0;
		$dataArray 		= ['epr_po_no' => $epr_po_no,'display_state_code'=>$display_state_code,"state_code_id" => $gst_state_code];	
		$dataArrayJson  = json_encode($dataArray);
		$ch 			= curl_init();
		$apiURL 		= EPR_PO_CHECK_URL;
		$curl 			= curl_init($apiURL);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
    	curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
         // \Log::info(print_r($request->all(),true));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataArray));
        $responseAPI 		= curl_exec($curl);
        $response 			= json_decode($responseAPI);
       
        if(isset($response)){
		 	$res['code'] 				= (int)$response->StatusCode;
	   		$res['status'] 				= (int)$response->Status;
	   		if(isset($response->Data->Response) && !empty($response->Data->Response)){
	   			$res['data'] 				= (isset($response->Data->Response) && !empty($response->Data->Response)) ? $response->Data->Response : null;
	   			$res['data']->RatePerkg 	= (isset($response->Data->Response) && !empty($response->Data->Response)) ? $response->Data->Response->Price : "";
	   		}else{
	   			$res['data'] 				= $response->Data;
	   		}
	   		
	   		$res['message'] = $response->Message;
   		}
   		$InsertID = DB::table('po_request_from_lr_to_epr')->insertGetId(['input_parameter' => $dataArrayJson,'response_status_code'=>$res['code'],'response_parameter'=>$responseAPI,'created_by' => $createdby_userid,'created_at'=>date('Y-m-d H:i:s')]);
        curl_close($curl); 
        return $res;  
	}
}