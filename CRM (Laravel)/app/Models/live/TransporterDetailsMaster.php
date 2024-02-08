<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationMaster;
use App\Models\LocationMaster;
use App\Models\TransporterMaster;
use App\Models\VehicleMaster;
use App\Facades\LiveServices;
use App\Models\ShippingAddressMaster;
use App\Models\WmSalesMaster;
use App\Models\WmClientMaster;
use App\Models\WmInvoices;
use App\Models\NetSuitMasterDataProcessMaster;
use App\Models\MasterCodes;
use App\Models\BaseLocationCityMapping;
use App\Models\CompanyMaster;
use App\Models\Parameter;
use App\Models\AdminUser;
use Roketin\Auditing\AuditingTrait;
use Mail;
class TransporterDetailsMaster extends Model
{
	//
	protected 	$table 		=	'transporter_details_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditingTrait;
	protected $casts = [

    ];
	public function SourceData(){
		return $this->belongsTo(LocationMaster::class,"source_id","location_id");
	}
	public function Destination(){
		return $this->belongsTo(LocationMaster::class,"destination_id","location_id");
	}
	public function Vehicle(){
		return $this->belongsTo(VehicleMaster::class,"vehicle_id","vehicle_id");
	}
	public function DispatchData(){
		return $this->belongsTo(WmDispatch::class,"dispatch_id");
	}
	public function TransporterData(){
		return $this->belongsTo(TransporterMaster::class,"transporter_id");
	}
	public function ApprovedData(){
		return $this->belongsTo(AdminUser::class,"approved_by","adminuserid");
	}

	/*
	Use 	:  Transporter Listing
	Author 	:  Axay Shah
	Date 	:  17 March 2021
	*/
	public static function ListTransporter($request,$isPainate=true){
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
		$createdAt 		= ($request->has('params.startDate') && $request->input('params.startDate')) ? date("Y-m-d",strtotime($request->input("params.startDate"))) : "";
		$createdTo 		= ($request->has('params.endDate') && $request->input('params.endDate')) ? date("Y-m-d",strtotime($request->input("params.endDate"))) : "";
		$data 			= self::select("$table.*",
										"TRN.id as transporter_id",
										"PARAM.para_value as po_for_name",
										\DB::raw("CONCAT(TRN.name,' (',TRN.code,')') as name"),
										\DB::raw("TPDM.vendor_name as transporter_name"),
										\DB::raw("IF($table.paid_by_party = '1','TP','') AS paid_by_party_type"),
										"VEH.vehicle_number",
										"SOURCE.city as source_city",
										"DEST.city as destination_city",
										"TPDM.po_no",
										"VTM.vehicle_type as vehicle_type_name",
										\DB::raw("IF($table.status = 1,'Active','Inactive') as status_name"),
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
										\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) as approved_by_name"),
										\DB::raw("(CASE WHEN $table.approval_status = 0 THEN 'P'
											WHEN $table.approval_status = 1 THEN 'A'
											WHEN $table.approval_status = 2 THEN 'R'
											END ) AS approval_status_name")
										)
		->join($Transporter->getTable()." as TRN","$table.transporter_id","=","TRN.id")
		->join($Vehicle->getTable()." as VEH","$table.vehicle_id","=","VEH.vehicle_id")
		->join($Location->getTable()." as SOURCE","$table.source_id","=","SOURCE.location_id")
		->join($Location->getTable()." as DEST","$table.destination_id","=","DEST.location_id")
		->leftjoin("transporter_po_details_master as TPDM","$table.po_detail_id","=","TPDM.id")
		->leftjoin("parameter as PARAM","$table.po_for","=","PARAM.para_id")
		->leftjoin($Admin->getTable()." as U1","$table.created_by","=","U1.adminuserid")
		->leftjoin($Admin->getTable()." as U2","$table.updated_by","=","U2.adminuserid")
		->leftjoin($Admin->getTable()." as U3","$table.approved_by","=","U3.adminuserid")
		->leftjoin("vehicle_type_master as VTM","$table.vehicle_type","=","VTM.id")
		->where("$table.company_id",Auth()->user()->company_id);
		if($request->has('params.vehicle_type') && !empty($request->input('params.vehicle_type')))
		{
			$data->where("$table.vehicle_type", $request->input('params.vehicle_type'));
		}
		if($request->has('params.vehicle_id') && !empty($request->input('params.vehicle_id')))
		{
			$data->where("$table.vehicle_id", $request->input('params.vehicle_id'));
		}
		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$id =  explode(",",$request->input('params.id'));
			$data->whereIn("$table.id",$id);
		}
		if($request->has('params.source_id') && !empty($request->input('params.source_id')))
		{
			$data->whereIn("$table.source_id", explode(",",$request->input('params.source_id')));
		}
		if($request->has('params.destination_id') && !empty($request->input('params.destination_id')))
		{
			$data->whereIn("$table.destination_id", explode(",",$request->input('params.destination_id')));
		}
		if($request->has('params.name') && !empty($request->input('params.name')))
		{
			$data->where("TRN.name",'like',"%".$request->input('params.name')."%");
		}
		if($request->has('params.status'))
		{
			$status 	=  $request->input('params.status');
			if($status == "0"){
				$data->where("$table.status",$status);
			}elseif($status == "1"){
				$data->where("$table.status",$status);
			}
		}

		if($request->has('params.approval_status'))
		{
			$approval_status 	=  $request->input('params.approval_status');
			if($approval_status == "0"){
				$data->where("$table.approval_status",$approval_status);
			}elseif($approval_status == "1" || $approval_status == "2" ){
				$data->where("$table.approval_status",$approval_status);
			}
		}
		if(!empty($createdAt) && !empty($createdTo)){
			$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		}elseif(!empty($createdAt)){
			$data->whereBetween("$table.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME]);
		}elseif(!empty($createdTo)){
			$data->whereBetween("$table.created_at",[$createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		}
		if($isPainate == true){
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}else{
			$result = $data->get();
		}
		return $result;
	}

	/*
	Use 	:  ADD OR UPDATE TRANSPOTER DATA
	Author 	:  Axay Shah
	Date 	:  17 March 2021
	*/
	public static function AddOrUpdateTransporter($request){

		$ID 				= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$NAME 				= (isset($request->name) && !empty($request->name)) ? ucwords(strtolower($request->name)) : "";
		$TRANSPORTER_NAME 	= (isset($request->transporter_name) && !empty($request->transporter_name)) ? ucwords(strtolower($request->transporter_name)) : "";
		$TRANSPOTER_ID 		= (isset($request->transporter_id) && !empty($request->transporter_id)) ? ucwords(strtolower($request->transporter_id)) : "";
		$VEHICLE_ID 		= (isset($request->vehicle_id) && !empty($request->vehicle_id)) ? $request->vehicle_id : 0;
		$SOURCE_ID 			= (isset($request->source_id) && !empty($request->source_id)) ? $request->source_id : 0;
		$DESTINATION_ID 	= (isset($request->destination_id) && !empty($request->destination_id)) ? $request->destination_id : 0;
		$RATE 				= (isset($request->rate) && !empty($request->rate)) ? $request->rate : 0;
		$PAID_BY_PARTY 		= (isset($request->paid_by_party) && !empty($request->paid_by_party)) ? ucwords(strtolower($request->paid_by_party)) : 0;
		$DEMURRAGE 			= (isset($request->demurrage) && !empty($request->demurrage)) ? $request->demurrage : 0;
		$STATUS 			= (isset($request->status) && !empty($request->status)) ? $request->status : 0;
		$DISPATCH_TYPE 		= (isset($request->dispatch_type) && !empty($request->dispatch_type)) ? $request->dispatch_type : 0;
		$PO_DETAIL_ID 		= (isset($request->po_detail_id) && !empty($request->po_detail_id)) ? $request->po_detail_id : 0;
		$VEHICLE_TYPE 		= (isset($request->vehicle_type) && !empty($request->vehicle_type)) ? $request->vehicle_type : 0;
		$collection_partner = (isset($request->collection_partner) && !empty($request->collection_partner)) ? $request->collection_partner : 0;
		$epr_po_no 			= (isset($request->epr_po_no) && !empty($request->epr_po_no)) ? $request->epr_po_no : 0;
		$gst_state_code 	= (isset($request->gst_state_code) && !empty($request->gst_state_code)) ? $request->gst_state_code : 0;
		$DATE 					= date("Y-m-d H:i:s");
		if(empty($TRANSPOTER_ID) && (!empty($NAME) || !empty($TRANSPORTER_NAME))){
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
		######## TRANSPORTER CODE AUTO GENERATED###########
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
		$SendEmailPendingForApproval 	= false;
		$ADD 							= self::find($ID);
		if($ADD){
			$ADD->updated_by 	= Auth()->user()->adminuserid;
			$ADD->updated_at 	= $DATE;
		} else {
			$ADD 				= new self();
			$ADD->created_by 	= Auth()->user()->adminuserid;
			$ADD->created_at 	= $DATE;
			$ADD->updated_by 	= Auth()->user()->adminuserid;
			$ADD->updated_at 	= $DATE;
			if($PAID_BY_PARTY == 1 || $DISPATCH_TYPE ==  RECYCLEBLE_TYPE || $PO_DETAIL_ID > 0) {
				$ADD->approval_status 	= 1;
				$ADD->approved_by 		= Auth()->user()->adminuserid;
				$ADD->approved_date 	= $DATE;
			} else {
				$SendEmailPendingForApproval = (isset($ADD->po_detail_id) && !empty($ADD->po_detail_id)) ? false : true;
			}
		}
		$PO_FOR 				= PARA_PO_FOR_SALES; 
		if($PO_DETAIL_ID > 0){
			$PO_FOR 			= TransporterPoDetailsMaster::where("id",$PO_DETAIL_ID)->value("po_for");
		}
		$ADD->po_for 			= $PO_FOR ;
		$ADD->po_detail_id 		= $PO_DETAIL_ID ;
		$ADD->transporter_id 	= $TRANSPOTER_ID ;
		$ADD->dispatch_type 	= $DISPATCH_TYPE ;
		$ADD->paid_by_party 	= $PAID_BY_PARTY ;
		$ADD->vehicle_id 		= $VEHICLE_ID ;
		$ADD->source_id 		= $SOURCE_ID ;
		$ADD->destination_id 	= $DESTINATION_ID ;
		$ADD->rate 				= $RATE ;
		$ADD->demurrage 		= $DEMURRAGE ;
		$ADD->status 			= 1 ;
		$ADD->vehicle_type 		= $VEHICLE_TYPE ;
		$ADD->company_id 		= Auth()->user()->company_id ;
		$ADD->epr_po_no 		= $epr_po_no ;
		$ADD->gst_state_code 	= $gst_state_code ;
		$ADD->collection_partner= $collection_partner ;
		if($ADD->save()) {
			$ID = $ADD->id;
			if ($SendEmailPendingForApproval) {
				self::SendEmailPendingForApproval($ID);
			}
			LR_Modules_Log_CompanyUserActionLog($request,$ID);
		}
		return $ID;
	}
	/*
	Use 	:  Get Transporter List
	Author 	:  Axay Shah
	Date 	:  17 March 2021
	*/
	

	// public static function GetTrasporter($request){

	// 	if(Auth()->user()->adminuserid == 1){
	// 		// return self::GetTrasporterNew($request);
	// 	}
	// 	$Location 			 	=  new LocationMaster();
	// 	$self 				 	=  (new static)->getTable();
	// 	$Transporter 		 	=  new TransporterMaster();
	// 	$TRN 			 	 	=  $Transporter->getTable();
	// 	$PO_FOR 		 		=  (isset($request->po_for) && !empty($request->po_for)) ? $request->po_for : 0;
	// 	$VEHICLE_ID 		 	=  (isset($request->vehicle_id) && !empty($request->vehicle_id)) ? $request->vehicle_id : 0;
	// 	$SOURCE 			 	=  (isset($request->source_id) && !empty($request->source_id)) ? $request->source_id : 0;
	// 	$DESTINATION 		 	=  (isset($request->destination_id) && !empty($request->destination_id)) ? $request->destination_id : 0;
	// 	$APPROVAL 			 	=  (isset($request->approval_status) && !empty($request->approval_status)) ? $request->approval_status : "";
	// 	$SHIPPING_ADDRESS_ID 	=  (isset($request->shipping_address_id) && !empty($request->shipping_address_id)) ? $request->shipping_address_id : 0;
	// 	$ORIGIN_ID 				=  (isset($request->origin_id) && !empty($request->origin_id)) ? $request->origin_id : 0;
	// 	$BILL_MRF_FROM_ID 		=  (isset($request->bill_from_department_id) && !empty($request->bill_from_department_id)) ? $request->bill_from_department_id : 0;
	// 	$DEPARTMENT_ID 		 	=  (isset($request->master_department_id) && !empty($request->master_department_id)) ? $request->master_department_id : 0;
	// 	$FROM_MRF 				=  (isset($request->from_mrf) && !empty($request->from_mrf) && $request->from_mrf == 1) ? true : false;

	// 	$DESTINATION 			=  ShippingAddressMaster::where("id",$SHIPPING_ADDRESS_ID)->value("city_id");
	// 	if($FROM_MRF){
	// 		$SOURCE 			=  BaseLocationCityMapping::getCityByBaseLocation();
	// 	}else{
	// 		$SOURCE 			=  CustomerMaster::where("customer_id",$ORIGIN_ID)->value("city");
	// 	}
	// 	$data 			= 	self::select(
	// 							"$self.id",
	// 							"$self.transporter_id",
	// 							"$TRN.name",
	// 							\DB::raw("CONCAT($TRN.name,'-',L1.city,'-',L2.city) as full_name")
	// 						)
	// 						->leftjoin($TRN,"$TRN.id","=","$self.transporter_id")
	// 						->leftjoin($Location->getTable()." as L1","$self.source_id","=","L1.location_id")
	// 						->leftjoin($Location->getTable()." as L2","$self.destination_id","=","L2.location_id")
	// 						->where("$self.status",1)
	// 						->where("$self.dispatch_id",0);
	// 	if($APPROVAL == 1){
	// 		$data->where("$self.approval_status",$APPROVAL);
	// 	}
	// 	if($FROM_MRF){
	// 		$data->whereIn("$self.source_id",$SOURCE);
	// 	}else{
	// 		$data->where("$self.source_id",$SOURCE);
	// 	}
	// 	// if($DESTINATION > 0){
	// 		$data->where("$self.destination_id",$DESTINATION);
	// 	// }
	// 	if($VEHICLE_ID > 0){
	// 		$data->where("$self.vehicle_id",$VEHICLE_ID);
	// 	}
	// 	$result = $data->get();
	// 	return $result;
	// }


	public static function GetTrasporter($request){
		$Location 			 	=  new LocationMaster();
		$self 				 	=  (new static)->getTable();
		$Transporter 		 	=  new TransporterMaster();
		$TRN 			 	 	=  $Transporter->getTable();
		$PO_FOR 		 		=  (isset($request->po_for) && !empty($request->po_for)) ? $request->po_for : 0;
		$VEHICLE_ID 		 	=  (isset($request->vehicle_id) && !empty($request->vehicle_id)) ? $request->vehicle_id : 0;
		$SOURCE 			 	=  (isset($request->source_id) && !empty($request->source_id)) ? $request->source_id : 0;
		$DESTINATION 		 	=  (isset($request->destination_id) && !empty($request->destination_id)) ? $request->destination_id : 0;
		$APPROVAL 			 	=  (isset($request->approval_status) && !empty($request->approval_status)) ? $request->approval_status : "";
		$SHIPPING_ADDRESS_ID 	=  (isset($request->shipping_address_id) && !empty($request->shipping_address_id)) ? $request->shipping_address_id : 0;
		$ORIGIN_ID 				=  (isset($request->origin_id) && !empty($request->origin_id)) ? $request->origin_id : 0;
		$BILL_MRF_FROM_ID 		=  (isset($request->bill_from_department_id) && !empty($request->bill_from_department_id)) ? $request->bill_from_department_id : 0;
		$DEPARTMENT_ID 		 	=  (isset($request->master_department_id) && !empty($request->master_department_id)) ? $request->master_department_id : 0;
		$FROM_MRF 				=  (isset($request->from_mrf) && !empty($request->from_mrf) && $request->from_mrf == 1) ? true : false;


		$data 	= 	self::select(
			"$self.id",
			"$self.transporter_id",
			"$TRN.name",
			\DB::raw("(CASE WHEN $self.paid_by_party = 1 OR $self.collection_partner = 1 THEN CONCAT($TRN.name,'-',L1.city,'-',L2.city)
						ELSE  CONCAT($TRN.name,'-',L1.city,'-',L2.city,'-',transporter_po_details_master.po_no)
						END) as full_name")
			// \DB::raw("CONCAT($TRN.name,'-',L1.city,'-',L2.city) as full_name")


		)
		->leftjoin("transporter_po_details_master","transporter_po_details_master.id","=","$self.po_detail_id")
		->leftjoin($TRN,"$TRN.id","=","$self.transporter_id")
		->leftjoin("parameter","parameter.para_id","=","$self.dispatch_type")
		->leftjoin($Location->getTable()." as L1","$self.source_id","=","L1.location_id")
		->leftjoin($Location->getTable()." as L2","$self.destination_id","=","L2.location_id")
		->where("$self.status",1);
		if($PO_FOR == PARA_PO_FOR_TRANSFER){
			$SOURCE 		= WmDepartment::where("id",$SOURCE)->value("location_id");
			$DESTINATION 	= WmDepartment::where("id",$DESTINATION)->value("location_id");
			$data->where("$self.ref_id",0);
			$data->where("$self.destination_id",$DESTINATION);
		}elseif($PO_FOR == PARA_PO_FOR_JOBWORK){
			$SOURCE 		= WmDepartment::where("id",$SOURCE)->value("location_id");
			$data->where("$self.ref_id",0);
			$data->where("$self.destination_id",$DESTINATION);
		}elseif($PO_FOR == PARA_PO_FOR_COLLECTION){
			$data->where("$self.ref_id",0);
			$data->where("$self.destination_id",$DESTINATION)->orWhere("$self.source_id",$DESTINATION);
		}else{
			$DESTINATION =  ShippingAddressMaster::where("id",$SHIPPING_ADDRESS_ID)->value("city_id");
			if($FROM_MRF){
				$SOURCE =  BaseLocationCityMapping::getCityByBaseLocation();
			}else{
				$SOURCE =  CustomerMaster::where("customer_id",$ORIGIN_ID)->value("city");
			}
			$data->where("$self.dispatch_id",0);
			$data->where("$self.destination_id",$DESTINATION);
		}
		if(!empty($PO_FOR)){
			$data->where("$self.po_for",$PO_FOR);
		}
		if($APPROVAL == 1){
			$data->where("$self.approval_status",$APPROVAL);
		}
		if($FROM_MRF){
			$data->whereIn("$self.source_id",$SOURCE);
		}else{
			$data->where("$self.source_id",$SOURCE);
		}
		
		if($VEHICLE_ID > 0){
			$data->where("$self.vehicle_id",$VEHICLE_ID);
		}
		// LiveServices::toSqlWithBinding($data);
		$result =  $data->get();
		return $result;
	}



	/*
	Use 	:  Approve Status of Trasporter
	Author 	:  Axay Shah
	Date 	:  18 March 2021
	*/
	public static function UpdateApprovalTransporter($request){
		$id 				=  (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$approval_status 	=  (isset($request->approval_status) && !empty($request->approval_status)) ? $request->approval_status : 0;
		$data 				=  self::where("id",$id)->update([
			"approval_status" 	=> $approval_status,
			"approved_by" 		=> Auth()->user()->adminuserid,
			"approved_date" 	=> date("Y-m-d H:i:s"),
			"updated_by" 		=> Auth()->user()->adminuserid,
			"updated_at" 		=> date("Y-m-d H:i:s")
		]);
		LR_Modules_Log_CompanyUserActionLog($request,$id);
		return $data;
	}

	/*
	Use 	: Transporter PO Module Report
	Author 	: Axay Shah
	Date 	: 23 March 2021
	*/
	public static function TransporterPOReportV2($request){
		$res 			= array();
		$self 			= (new static)->getTable();
		$Dispatch 		= new WmDispatch();
		$Location 		= new LocationMaster();
		$Vehicle 		= new VehicleMaster();
		$Transporter 	= new TransporterMaster();
		$Admin 			= new AdminUser();
		$WEIGHTED_AVG_PRICE 	= 0;
		$QTY_WITHOUT_ZERO_COST 	= 0;
		$challan_no 		= (isset($request->challan_no) && !empty($request->challan_no)) ? $request->challan_no : '';
		$transporter_name 	= (isset($request->transporter_name) && !empty($request->transporter_name)) ? $request->transporter_name : '';
		$transporter_id 	= (isset($request->transporter_id) && !empty($request->transporter_id)) ? $request->transporter_id : '';
		$destination_id 	= (isset($request->destination_id) && !empty($request->destination_id)) ? $request->destination_id : '';
		$source_id 			= (isset($request->source_id) && !empty($request->source_id)) ? $request->source_id : '';
		$approval_status 	= (isset($request->approval_status) && !empty($request->approval_status)) ? $request->approval_status : '';
		$paid_by_party 		= (isset($request->paid_by_party) && !empty($request->paid_by_party)) ? $request->paid_by_party : '';
		$destination 		= (isset($request->destination) && !empty($request->destination)) ? $request->destination : '';
		$source 			= (isset($request->source) && !empty($request->source)) ? $request->source : '';
		$createdAt 			= (isset($request->created_from) && !empty($request->created_from)) ? date("Y-m-d",strtotime($request->created_from)) : '';
		$createdTo 			= (isset($request->created_to) && !empty($request->created_to)) ? date("Y-m-d",strtotime($request->created_to)) : '';

		$data 		 	= self::select( "$self.*",
									\DB::RAW("IF($self.status = 1,'Active','Inactive') as status_name"),
									\DB::RAW("(CASE WHEN $self.approval_status = 1 THEN 'Approved'
											  WHEN $self.approval_status = 2 THEN 'Rejected'
											  ELSE 'Pending'
											  END) as approval_status_name
									"),
									"VEH.vehicle_number",
									"DESP.challan_no",
									\DB::RAW("IF(DESP.dispatch_type = 1032002,'NR','R') as Dispatch_Type"),
									"TAN.name",
									"L1.city as source_city",
									"L1.state as source_state",
									"L2.city as destination_city",
									"L2.state as destination_state",
									\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) AS approved_by_name"),
									\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) AS created_by_name"),
									\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) AS updated_by_name")
		)
		->join($Dispatch->getTable()." as DESP","$self.dispatch_id","=","DESP.id")
		->leftjoin($Vehicle->getTable()." as VEH","$self.vehicle_id","=","VEH.vehicle_id")
		->leftjoin($Transporter->getTable()." as TAN","$self.transporter_id","=","TAN.id")
		->leftjoin($Location->getTable()." as L1","$self.source_id","=","L1.location_id")
		->leftjoin($Location->getTable()." as L2","$self.destination_id","=","L2.location_id")
		->leftjoin($Admin->getTable()." as U1","$self.approved_by","=","U1.adminuserid")
		->leftjoin($Admin->getTable()." as U2","$self.created_by","=","U2.adminuserid")
		->leftjoin($Admin->getTable()." as U3","$self.updated_by","=","U3.adminuserid");

		if(!empty($challan_no)){
			$data->where("DESP.challan_no","like","%".$challan_no."%");
		}

		if(!empty($transporter_name)){
			$data->where("TAN.name","like","%".$transporter_name."%");
		}

		if(!empty($transporter_id)){
			$data->where("$self.transporter_id",$transporter_id);
		}

		if(!empty($destination_id)){
			$data->where("$self.destination_id",$destination_id);
		}

		if(!empty($source_id)){
			$data->where("$self.source_id",$source_id);
		}

		if(!empty($destination)){
			$data->where("L2.city","like","%".$destination."%");
		}

		if(!empty($source)) {
			$data->where("L1.city","like","%".$source."%");
		}

		if($approval_status)
		{
			if($approval_status == "0"){
				$data->where("$self.status",$approval_status);
			}elseif($approval_status == "1" || $approval_status == "2"){
				$data->where("$self.status",$approval_status);
			}
		}
		if($paid_by_party)
		{
			if($paid_by_party == "0"){
				$data->where("$self.paid_by_party",$paid_by_party);
			}elseif($paid_by_party == "1"){
				$data->where("$self.paid_by_party",$paid_by_party);
			}
		}
		if(!empty($createdAt) && !empty($createdTo)){
			$data->whereBetween("$self.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		}elseif(!empty($createdAt)){
			$data->whereBetween("$self.created_at",[$createdAt." ".GLOBAL_START_TIME,$createdAt." ".GLOBAL_END_TIME]);
		}elseif(!empty($createdTo)){
			$data->whereBetween("$self.created_at",[$createdTo." ".GLOBAL_START_TIME,$createdTo." ".GLOBAL_END_TIME]);
		}
		// $data->where("$self.dispatch_id"," > ",0);

		/** Added Approved Dispatch Condition */
		// $data->where("DESP.approval_status","=",1);
		/** Added Approved Dispatch Condition */
		LiveServices::toSqlWithBinding($data);
		$res 			= $data->get();
		$response 		= array();
		$TOTAL_AMOUNT 	= 0;
		$TOTAL_QTY 		= 0;
		if(!empty($res)){
			foreach($res as $key => $value){
				$qty 							= 	0;
				$rate 							=   (!empty($value->rate)) ? ucwords($value->rate) : 0;
				$demurrage 						=   (!empty($value->demurrage)) ? ucwords($value->demurrage) : 0;
				$truck_amount 					=   _FormatNumberV2($rate + $demurrage);
				$res[$key]['color_code'] 		= 	($rate > 0 && $demurrage > 0) ? "red" : "";
				$res[$key]['truck_amount'] 		= 	($truck_amount > 0) ? $truck_amount : 0;
				$res[$key]['destination_state'] = 	(!empty($value->destination_state)) ? ucwords($value->destination_state) : "";
				$res[$key]['destination_city'] 	= 	(!empty($value->destination_city)) ? ucwords($value->destination_city) : "";
				$res[$key]['source_city'] 		= 	(!empty($value->source_city)) ? ucwords($value->source_city) : "";
				$res[$key]['source_state'] 		= 	(!empty($value->source_state)) ? ucwords($value->source_state) : "";
				$invoiceGenrated				= 	(isset($value->DispatchData->invoice_generated)) ? $value->DispatchData->invoice_generated : 0;
				$url 							= "";
				if($invoiceGenrated > 0){
					$invoice = WmInvoices::where("dispatch_id",$value->dispatch_id)->first();
					if($invoice){
						$invoiceID 		= $invoice->id;
						$invoice_status = $invoice->invoice_status;
						$url 			= url('/invoice')."/".passencrypt($invoiceID);
					}
				}
				$qty 							= WmSalesMaster::where("dispatch_id",$value->dispatch_id)->sum("quantity");
				$res[$key]["invoice_url"] 		= $url;
				$res[$key]["qty"] 				= $qty;
				$res[$key]["price_per_kg"] 		= ($qty > 0) ? _FormatNumberV2($truck_amount / $qty) : 0;
				$res[$key]["display_invoice"] 	= 1;
				$res[$key]["Dispatch_Type"] 	= $value->Dispatch_Type;
				$TOTAL_QTY 		+= $qty;
				$TOTAL_AMOUNT 	+= $truck_amount;
				if($rate > 0){
					$QTY_WITHOUT_ZERO_COST += $qty;
				}
			}
		}
		$WEIGHTED_AVG_PRICE 			= ($QTY_WITHOUT_ZERO_COST > 0 && $TOTAL_AMOUNT > 0) ? _FormatNumberV2($TOTAL_AMOUNT / $QTY_WITHOUT_ZERO_COST) : 0;
		$response["result"] 			=  $res;
		$response["TOTAL_QTY"] 			=  _FormatNumberV2($TOTAL_QTY);
		$response["TOTAL_AMOUNT"] 		=  _FormatNumberV2($TOTAL_AMOUNT);
		$response["WEIGHTED_AVG_PRICE"] =  _FormatNumberV2($WEIGHTED_AVG_PRICE);

		return $response;
	}
	public static function TransporterPOReport($request)
	{
		$res 				= array();
		$self 				= (new static)->getTable();
		$Dispatch 			= new WmDispatch();
		$Client 			= new WmClientMaster();
		$Location 			= new LocationMaster();
		$Vehicle 			= new VehicleMaster();
		$Transporter 		= new TransporterMaster();
		$WDP 				= new WmDispatchProduct();
		$Admin 				= new AdminUser();
		$WEIGHTED_AVG_PRICE = 0;
		$QTY_WITHOUT_ZERO_COST 	= 0;
		$challan_no 		= (isset($request->challan_no) && !empty($request->challan_no)) ? $request->challan_no : '';
		$transporter_name 	= (isset($request->transporter_name) && !empty($request->transporter_name)) ? $request->transporter_name : '';
		$transporter_id 	= (isset($request->transporter_id) && !empty($request->transporter_id)) ? $request->transporter_id : '';
		$destination_id 	= (isset($request->destination_id) && !empty($request->destination_id)) ? $request->destination_id : '';
		$source_id 			= (isset($request->source_id) && !empty($request->source_id)) ? $request->source_id : '';
		$approval_status 	= (isset($request->approval_status) && !empty($request->approval_status)) ? $request->approval_status : '';
		$paid_by_party 		= (isset($request->paid_by_party) && !empty($request->paid_by_party)) ? $request->paid_by_party : '';
		$destination 		= (isset($request->destination) && !empty($request->destination)) ? $request->destination : '';
		$source 			= (isset($request->source) && !empty($request->source)) ? $request->source : '';
		$createdAt 			= (isset($request->created_from) && !empty($request->created_from)) ? date("Y-m-d",strtotime($request->created_from)) : '';
		$createdTo 			= (isset($request->created_to) && !empty($request->created_to)) ? date("Y-m-d",strtotime($request->created_to)) : '';
		$product_id 		= (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : array();
		$dispatch_type 		= (isset($request->dispatch_type)) ? $request->dispatch_type : '';
		$client_id 			= (isset($request->client_id) && !empty($request->client_id)) ? $request->client_id : '';
		$vehicle_type 		= (isset($request->vehicle_type) && !empty($request->vehicle_type)) ? $request->vehicle_type : '';
		$po_no 				= (isset($request->po_no) && !empty($request->po_no)) ? $request->po_no : '';
		if($dispatch_type == "1") {
			$dispatch_type = RECYCLEBLE_TYPE;
		} else if($dispatch_type == "0") {
			$dispatch_type = NON_RECYCLEBLE_TYPE;
		}
		$WHERE  = " WHERE $self.company_id=".Auth()->user()->company_id;
		if(!empty($challan_no)) {
			$WHERE .="  AND DESP.challan_no like '%".$challan_no."%'";
		}
		if(!empty($vehicle_type)){
			$WHERE .=" AND TPDM.vehicle_type = $vehicle_type";
			
		}
		if(!empty($client_id)) {
			$WHERE .=" AND CLIENT.id = $client_id";
		}
		if(!empty($dispatch_type)) {
			$WHERE .=" AND DESP.dispatch_type = $dispatch_type";
		}
		if(!empty($transporter_name)) {
			$WHERE .="  AND TAN.name like '%".$transporter_name."%'";
		}
		if(!empty($transporter_id)) {
			$WHERE .=" AND $self.transporter_id = $transporter_id";
		}
		if(!empty($destination_id)) {
			$WHERE .=" AND $self.destination_id = $destination_id";
		}
		if(!empty($source_id)) {
			$WHERE .=" AND $self.source_id = $source_id";
		}
		if(!empty($destination)) {
			$WHERE .="  AND L2.city like '%".$destination."%'";
		}
		if(!empty($source)) {
			$WHERE .="  AND L1.city like '%".$source."%'";
		}
		if(!empty($po_no)) {
			$WHERE .="  AND TPDM.po_no like '%".$po_no."%'";
		}
		if(!empty($product_id)) {
			$product_id = implode($product_id,",");
			$WHERE .=" AND WDP.product_id IN($product_id)";
		}
		if($approval_status) {
			if($approval_status == "0") {
				$WHERE .=" AND $self.status = $approval_status";
			} else if($approval_status == "1" || $approval_status == "2") {
				$WHERE .=" AND $self.status = $approval_status";
			}
		}
		if($paid_by_party) {
			if($paid_by_party == "0") {
				$WHERE .=" AND $self.paid_by_party = $paid_by_party";
			} else if($paid_by_party == "1") {
				$WHERE .=" AND $self.paid_by_party = $paid_by_party";
			}
		}
		if(!empty($createdAt) && !empty($createdTo)) {
			$WHERE .=" AND $self.created_at >= '".$createdAt." ".GLOBAL_START_TIME."' AND $self.created_at <= '".$createdTo." ".GLOBAL_END_TIME."'";
			
		} else if(!empty($createdAt)) {
			$WHERE .=" AND $self.created_at >= '".$createdAt." ".GLOBAL_START_TIME."' AND $self.created_at <= '".$createdAt." ".GLOBAL_END_TIME."'";
			
		} else if(!empty($createdTo)) {
			$WHERE .=" AND $self.created_at >= '".$createdTo." ".GLOBAL_START_TIME."' AND $self.created_at <= '".$createdTo." ".GLOBAL_END_TIME."'";
			
		}
		$WHERE 			.=" GROUP BY $self.dispatch_id";
		$SQL 			= 'CALL SP_TRANSPORTER_PO_DETAILS_REPORT("'.$WHERE.'")';
		$res 			= \DB::select($SQL);
		$response 		= array();
		$TOTAL_AMOUNT 	= 0;
		$TOTAL_QTY 		= 0;
		$QTY_WITHOUT_ZERO_COST = 0;
		if(!empty($res))
		{
			foreach($res as $key => $value)
			{
				$qty 							= 0;
				$rate 							= (!empty($value->rate)) ? ucwords($value->rate) : 0;
				$demurrage 						= (!empty($value->demurrage)) ? ucwords($value->demurrage) : 0;
				$truck_amount 					= _FormatNumberV2($rate + $demurrage);
				$res[$key]->color_code 			= ($rate > 0 && $demurrage > 0) ? "red" : "";
				$res[$key]->dispatch_date 		= !empty($value->dispatch_date) ? date("Y-m-d",strtotime($value->dispatch_date)) : "";
				$res[$key]->truck_amount 		= ($truck_amount > 0) ? $truck_amount : 0;
				$res[$key]->destination_state 	= (!empty($value->destination_state)) ? ucwords($value->destination_state) : "";
				$res[$key]->destination_city 	= (!empty($value->destination_city)) ? ucwords($value->destination_city) : "";
				$res[$key]->source_city 		= (!empty($value->source_city)) ? ucwords($value->source_city) : "";
				$res[$key]->source_state 		= (!empty($value->source_state)) ? ucwords($value->source_state) : "";
				$invoiceGenrated				= (isset($value->DispatchData->invoice_generated)) ? $value->DispatchData->invoice_generated : 0;
				$url 							= "";
				if($invoiceGenrated > 0) {
					$invoice = WmInvoices::where("dispatch_id",$value->dispatch_id)->first();
					if($invoice) {
						$invoiceID 		= $invoice->id;
						$invoice_status = $invoice->invoice_status;
						$url 			= url('/invoice')."/".passencrypt($invoiceID);
					}
				}
				$qty 							= WmDispatchProduct::where("dispatch_id",$value->dispatch_id)->sum("quantity");
				$res[$key]->demurrage_remarks 	= (empty($value->demurrage_remarks) || $value->demurrage_remarks == "null") ? "" : $value->demurrage_remarks;
				$res[$key]->expected_price_per_kg = (!empty($value->expected_price_per_kg)) ? $value->expected_price_per_kg : 0;
				$res[$key]->invoice_url 		= $url;
				$res[$key]->qty 				= $qty;
				$res[$key]->price_per_kg 		= ($qty > 0) ? _FormatNumberV2($truck_amount / $qty) : 0;
				$res[$key]->display_invoice 	= 0;
				$TOTAL_QTY 		+= $qty;
				$TOTAL_AMOUNT 	+= $truck_amount;
				if($rate > 0){
					$QTY_WITHOUT_ZERO_COST += $qty;
				}
			}
		}
		$WEIGHTED_AVG_PRICE 			= ($QTY_WITHOUT_ZERO_COST > 0 && $TOTAL_AMOUNT > 0) ? _FormatNumberV2($TOTAL_AMOUNT / $QTY_WITHOUT_ZERO_COST) : 0;
		$response["result"] 			=  $res;
		$response["TOTAL_QTY"] 			=  _FormatNumberV2($TOTAL_QTY);
		$response["TOTAL_AMOUNT"] 		=  _FormatNumberV2($TOTAL_AMOUNT);
		$response["WEIGHTED_AVG_PRICE"] =  _FormatNumberV2($WEIGHTED_AVG_PRICE);
		return $response;
	}
	/*
	Use 	:  Get Transporter List
	Author 	:  Axay Shah
	Date 	:  17 March 2021
	*/
	public static function GetById($id)
	{
		$Location 		= new LocationMaster();
		$Vehicle 		= new VehicleMaster();
		$Parameter 		= new Parameter();
		$Dispatch 		= new WmDispatch();
		$self 			= (new static)->getTable();
		$Transporter    = new TransporterMaster();
		$TRN 			= $Transporter->getTable();
		$data 			= self::select(	"$self.id",
										"$self.company_id",
										"$self.paid_by_party",
										"$self.transporter_id",
										"$self.rate",
										"DT.para_value as dispatch_type",
										"$TRN.name as transpoter_name",
										"L1.city as source",
										"L2.city as destination",
										"VEH.vehicle_number",
										"DSP.client_master_id")
							->join($TRN,"$TRN.id","=","$self.transporter_id")
							->join($Location->getTable()." as L1","$self.source_id","=","L1.location_id")
							->join($Location->getTable()." as L2","$self.destination_id","=","L2.location_id")
							->join($Vehicle->getTable()." as VEH","$self.vehicle_id","=","VEH.vehicle_id")
							->join($Parameter->getTable()." as DT","$self.dispatch_type","=","DT.para_id")
							->leftjoin($Dispatch->getTable()." as DSP","$self.dispatch_id","=","DSP.id")
							->where("$self.id",$id)
							->first();
		if($data) {
			$client_name 		= (isset($data->client_master_id) && !empty($data->client_master_id))?WmClientMaster::where("id",$data->client_master_id)->value("client_name"):"";
			$data->client_name 	= $client_name;
		}
		return $data;
	}

	/*
	Use 	: SendEmailPendingForApproval
	Date 	: 16 Sep 2022
	Author 	: Kalpak Prajapati
	*/
	public static function SendEmailPendingForApproval($po_id=0)
	{
		$TransporterPoDetails 	= self::GetById($po_id);
		if($TransporterPoDetails)
		{
			$CompanyMaster 		= CompanyMaster::find($TransporterPoDetails->company_id);
			if (empty($CompanyMaster->transpoter_po_approval_user)) return false;
			$transpoter_po_approval_user = explode(",",$CompanyMaster->transpoter_po_approval_user);
			foreach ($transpoter_po_approval_user as $APPROVED_BY) {
				$AdminUser = AdminUser::select("email")->where("status","A")->where("adminuserid",$APPROVED_BY)->first();
				if (!empty($AdminUser->email)) {
					$APPROVE_LINK 							= env("APP_URL")."/transpoter-po-approval/".encode($po_id)."/".encode($APPROVED_BY);
					$ToEmail 								= explode(",",$AdminUser->email);
					// $ToEmail 								= array("kalpak@nepra.co.in");
					$From_Email 							= array('Email'=>"reports@letsrecycle.co.in",'Name'=>"Nepra Resource Management Private Limited");
					$Subject 								= "Approval Request For Transporter PO ID : ".$po_id;
					$Attachment 							= array();
					$CCEmail 								= array();
					$TransporterPoDetail 					= $TransporterPoDetails->toArray();
					$TransporterPoDetail['APPROVE_LINK'] 	= $APPROVE_LINK;
					$TransporterPoDetail['client_name'] 	= $TransporterPoDetails->client_name;
					$sendEmail 								= Mail::send("email-template.TransporterPOApproval",$TransporterPoDetail,function ($message) use ($ToEmail,$From_Email,$CCEmail,$Subject,$Attachment) {
						$message->from($From_Email['Email'], $From_Email['Name']);
						$message->to($ToEmail);
						$message->subject($Subject);
						if (!empty($CCEmail)) $message->cc($CCEmail);
						// $message->bcc("kalpak@nepra.co.in");
						if (!empty($Attachment)) {
							$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
						}
					});
				}
			}
		}
	}

	/*
	Use 	: ApproveRecord
	Date 	: 16 Sep 2022
	Author 	: Kalpak Prajapati
	*/
	public static function ApproveRecord($PO_ID,$ApprovedBy,$approveStatus)
	{
		self::where("id",$PO_ID)->update(["approval_status"=>$approveStatus,"approved_by"=>$ApprovedBy,"approved_date"=>date("Y-m-d H:i:s")]);
		return true;
	}

	/*
	Use 	:  UPDATE vehicle cost type wise rate
	Author 	:  Axay Shah
	Date 	:  25 May 2023
	*/
	public static function updateRateForVehicleTypeWise($DETAIL_ID,$TOTAL_ACTUAL_QTY=0){
		$TRANSPORTER_PO_DETAILS = TransporterDetailsMaster::where("id",$DETAIL_ID)->first();
		if($TRANSPORTER_PO_DETAILS){
			$TRANSPORTER_PO_DATA 				 = TransporterPoDetailsMaster::find($TRANSPORTER_PO_DETAILS->po_detail_id);
			$RATE 								 = $TRANSPORTER_PO_DETAILS->rate;
			if($TRANSPORTER_PO_DATA && isset($TRANSPORTER_PO_DATA->vehicle_cost_type) && $TRANSPORTER_PO_DATA->vehicle_cost_type == PER_TONNE_ACTUAL_CAPACITY){
				$RATE = (isset($TRANSPORTER_PO_DATA->rate_per_kg)) ? $TRANSPORTER_PO_DATA->rate_per_kg * $TOTAL_ACTUAL_QTY : 0;
			}elseif($TRANSPORTER_PO_DATA && isset($TRANSPORTER_PO_DATA->vehicle_cost_type) && $TRANSPORTER_PO_DATA->vehicle_cost_type == PER_TONNE_AT_VEHICLE_CAPACITY){
				$RATE = (isset($TRANSPORTER_PO_DATA->rate_per_kg)) ? $TRANSPORTER_PO_DATA->rate_per_kg * $TRANSPORTER_PO_DATA->vehicle_capacity_in_kg : 0;
			}
			$TRANSPORTER_PO_DETAILS->rate = $RATE;
			$TRANSPORTER_PO_DETAILS->save();
		}
	}
}