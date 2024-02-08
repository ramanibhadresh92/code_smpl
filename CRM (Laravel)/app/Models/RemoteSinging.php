<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\WmDispatchProduct;
use App\Models\Parameter;
use App\Models\CustomerMaster;
use App\Models\WmClientMaster;
use App\Models\VehicleMaster;
use App\Models\WmProcessMaster;
use App\Models\WmDispatchPlan;
use App\Models\WmProductMaster;
use App\Models\WmPurchaseToSalesMap;
use App\Models\GSTStateCodes;
use App\Models\WmSalesProductPrice;
use App\Classes\EwayBill;
use App\Models\WmBatchMaster;
use App\Models\StateMaster;
use App\Models\ShippingAddressMaster;
use App\Models\BailOutwardLedger;
use App\Models\WmDispatchMediaMaster;
use App\Models\JobWorkMaster;
use App\Models\LrEprMappingMaster;
use App\Models\EwayBillApiLogger;
use App\Models\UserCityMpg;
use App\Models\WmProductClientPriceMaster;
use App\Models\ProductInwardLadger;
use App\Models\OutWardLadger;
use App\Models\WmInvoices;
use App\Models\WaybridgeModuleVehicleInOut;
use App\Models\TransporterDetailsMaster;
use App\Models\TransactionMasterCodesMrfWise;
use App\Models\EinvoiceApiLogger;
use App\Models\Appoinment;
use App\Models\AppointmentCollectionDetail;
use App\Models\AppointmentCollection;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Models\WmInvoicesCreditDebitNotes;
use App\Models\WmInvoicesCreditDebitNotesDetails;
use App\Models\WmDispatchSalesProductAvgPrice;
use App\Models\UserBaseLocationMapping;
use App\Models\WmBatchProductDetail;
use App\Models\WmDispatchBrandingImgMapping;
use App\Models\InvoiceAdditionalCharges;
use App\Models\StockLadger;
use App\Models\VehicleDocument;
use App\Facades\LiveServices;
use Mail;
use PDF;
use DB;
use Image;
use Imagick;
use File;
 
use Illuminate\Support\Facades\Http;
class WmDispatch extends Model
{
	protected 	$table 		=	'wm_dispatch';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	  
	protected $casts = [
		"challan_no" 		=> "string",
		"master_dept_id" 	=> "Integer",
		"vehicle_id" 		=> "Integer",
	];
	public function DispatchImages(){
		return $this->hasMany(WmDispatchMediaMaster::class,"dispatch_id","id");
	}

	public function DispatchProductData(){
		return $this->hasMany(WmDispatchProduct::class,"dispatch_id","id");
	}

	public function DispatchSalesData(){
		return $this->hasMany(WmSalesMaster::class,"dispatch_id","id");
	}

	public function DepartmentData()
	{
		return $this->belongsTo(WmDepartment::class,"master_dept_id");
	}
	public function ClientData()
	{
		return $this->belongsTo(WmClientMaster::class,"client_master_id");
	}
	public function Vehicle()
	{
		return $this->belongsTo(VehicleMaster::class,"vehicle_id");
	}
	/*
	Use 	: List Dispatch
	Author 	: Axay Shah
	Date 	: 04 June,2019
	*/
	public static function ListDispatch($request,$FromMobile = false)
	{
		$WmDispatchProductTbl 	= new WmDispatchProduct();
		$WmProductMasterTbl 	= new WmProductMaster();
		$VehicleMasterTbl		= new VehicleMaster();
		$LR_EPR_MAP 			= new LrEprMappingMaster();
		$ClientMasterTbl 		= new WmClientMaster();
		$CustomerMasterTbl 		= new CustomerMaster();
		$WmProcessMasterTbl 	= new WmProcessMaster();
		$WmDispatchPlanTbl 		= new WmDispatchPlan();
		$DepartmentMaster 		= new WmDepartment();
		$Department				= $DepartmentMaster->getTable();
		$WmDispatchProduct 		= $WmDispatchProductTbl->getTable();
		$WmProductMaster 		= $WmProductMasterTbl->getTable();
		$VehicleMaster			= $VehicleMasterTbl->getTable();
		$ClientMaster 			= $ClientMasterTbl->getTable();
		$CustomerMaster 		= $CustomerMasterTbl->getTable();
		$WmProcessMaster 		= $WmProcessMasterTbl->getTable();
		$WmDispatchPlan 		= $WmDispatchPlanTbl->getTable();
		$LrEPRMapping 			= $LR_EPR_MAP->getTable();
		$Dispatch 				= (new static)->getTable();
		$AdminUser 				= new AdminUser();
		$Admin 					= $AdminUser->getTable();
		$AdminUserID 			= Auth()->user()->adminuserid;
		$Today          		= date('Y-m-d');
		$sortBy         		= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      		= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  		= !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     		= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId         		= GetBaseLocationCity();

		$data = self::select("$Dispatch.*",
					\DB::raw("'' as dispatch_raw"),
					\DB::raw("'' as invoice_url"),
					\DB::raw("$VehicleMaster.vehicle_number"),
					\DB::raw("DATE_FORMAT($Dispatch.dispatch_date,'%d-%m-%Y') AS dispatch_date"),
					\DB::raw("CONCAT($CustomerMaster.first_name,' ',$CustomerMaster.last_name) as origin_name"),
					\DB::raw("$ClientMaster.client_name"),
					\DB::raw("$ClientMaster.address"),
					\DB::raw("$ClientMaster.VAT"),
					\DB::raw("$ClientMaster.mobile_no"),
					\DB::raw("$ClientMaster.gstin_no"),
					\DB::raw("(CASE WHEN $Dispatch.approval_status = 0 THEN 'Pending'
								WHEN $Dispatch.approval_status = 1 THEN 'Approved'
								WHEN $Dispatch.approval_status = 2 THEN 'Rejected'
								END ) AS approval_status_name"),
					\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as approved_by_name"),
					\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as created_by_name"),
					\DB::raw("EPR.epr_track_id"),
					\DB::raw("IF(EPR.process = 1,0,0) AS process"),
					\DB::raw("MDI.location_id"),
					\DB::raw("MDI.is_virtual")

				)
		->leftjoin($VehicleMaster,"$Dispatch.vehicle_id","=","$VehicleMaster.vehicle_id")
		->leftjoin($ClientMaster,"$Dispatch.client_master_id","=","$ClientMaster.id")
		->leftjoin($CustomerMaster,"$Dispatch.origin","=","$CustomerMaster.customer_id")
		->leftjoin($Department." AS CMS","$Dispatch.origin","=","CMS.id")
		->leftjoin($Department." AS MDI","$Dispatch.master_dept_id","=","MDI.id")
		->leftjoin($ClientMaster." as d","$Dispatch.destination","=","d.id")
		->leftjoin($Admin." as U1","$Dispatch.approved_by","=","U1.adminuserid")
		->leftjoin($Admin." as U2","$Dispatch.created_by","=","U2.adminuserid")
		->leftjoin($LrEPRMapping." as EPR","$Dispatch.id","=","EPR.dispatch_id");

		$data->where("$Dispatch.invoice_cancel",'0');
		if($request->has('params.id') && !empty($request->input('params.id')))
		{
			$id 	= $request->input('params.id');
			if(!is_array($request->input('params.id'))){
				$id = explode(",",$request->input("params.id"));
			}

			$data->where("$Dispatch.id",$id);
		}
		if($request->has('params.dispatch_type') && !empty($request->input('params.dispatch_type')))
		{
			$data->where("$Dispatch.dispatch_type",$request->input('params.dispatch_type'));
		}
		if($request->has('params.unloading_slip') && !empty($request->input('params.unloading_slip')))
		{
			$unloading_slip = $request->input('params.unloading_slip');
			if($unloading_slip == '-1'){
				$data->whereNull("$Dispatch.unloading_slip_media_id");
			}
			if($unloading_slip == 1){
				$data->where("$Dispatch.unloading_slip_media_id",">",$unloading_slip);
			}
			
		}
		if($request->has('params.vehicle_no') && !empty($request->input('params.vehicle_no')))
		{
			$data->where("$VehicleMaster.vehicle_number","like","%".$request->input('params.vehicle_no')."%");
		}
		if($request->has('params.challan_no') && !empty($request->input('params.challan_no')))
		{
			$data->where("$Dispatch.challan_no","like","%".$request->input('params.challan_no')."%");
		}
		if($request->has('params.transport_cost_id') && !empty($request->input('params.transport_cost_id')))
		{
			$data->where("$Dispatch.transport_cost_id",$request->input('params.transport_cost_id'));
		}
		if($request->has('params.client_name') && !empty($request->input('params.client_name')))
		{
			$data->where("$ClientMaster.client_name","like","%".$request->input('params.client_name')."%");
		}

		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$data->where("$Dispatch.master_dept_id",$request->input('params.mrf_id'));
		}

		if($request->has('params.nespl') && !empty($request->input('params.nespl')))
		{
			$data->where("$Dispatch.nespl",$request->input('params.nespl'));
		}elseif($request->has('params.nespl') && $request->input('params.nespl') == '0'){
			$data->where("$Dispatch.nespl",$request->input('params.nespl'));
		}
		if($request->has('params.approval_status'))
		{
			if($request->input('params.approval_status') == "0"){
				$data->where("$Dispatch.approval_status",$request->input('params.approval_status'));
			}elseif($request->input('params.approval_status') == "1" || $request->input('params.approval_status') == "2"){
				$data->where("$Dispatch.approval_status",$request->input('params.approval_status'));
			}
		}

		if($request->has('params.origin_name') && !empty($request->input('params.origin_name')))
		{
			$data->where(function($query) use($request,$CustomerMaster){
				$query->where("$CustomerMaster.first_name","like","%".$request->input('params.origin_name')."%");
				$query->orWhere("$CustomerMaster.last_name","like","%".$request->input('params.origin_name')."%");
				$query->orWhere("CMS.department_name","like","%".$request->input('params.origin_name')."%");
			});
		}

		if($request->has('params.dispatch_date') && !empty($request->input('params.dispatch_date')))
		{
			$DispatchDate 	= date("Y-m-d",strtotime($request->input('params.dispatch_date')));
			$data->whereBetween("$Dispatch.dispatch_date",array($DispatchDate." ",GLOBAL_START_TIME,$DispatchDate." ".GLOBAL_END_TIME));
		}

		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$data->whereBetween("$Dispatch.dispatch_date",array(date("Y-m-d H:i:s", strtotime($request->input('params.startDate')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.endDate')." ".GLOBAL_END_TIME))));
		}else if(!empty($request->input('params.startDate'))){
		   $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		   $data->whereBetween("$Dispatch.dispatch_date",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('params.endDate'))){
		   $data->whereBetween("$Dispatch.dispatch_date",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}

		$data->where(function($query) use($request,$Dispatch,$cityId){
				$query->whereIn("MDI.base_location_id",array(Auth()->user()->base_location));
				// $query->orWhereIn("$Dispatch.destination_city",$cityId);
		});
		$data->where("$Dispatch.company_id",Auth()->user()->company_id);

		/* FROM MOBILE SIDE EPR DOCUMENT UPLOAD FILTER - 01 JULY 2020*/
		if($FromMobile){
			$MOB_DISPATCH_ID = (isset($request->dispatch_id)) ? $request->dispatch_id :0;
			$MOB_CHALLAN 	 = (isset($request->challan_number)) ? $request->challan_number :"";
			$MOB_FROM_DOC 	 = (isset($request->from_document)) ? $request->from_document : 0;
			if($request->has('approval_status'))
			{
				if($request->input('approval_status') == "0"){
					$data->where("$Dispatch.approval_status",$request->input('approval_status'));
				}elseif($request->input('approval_status') == "1" || $request->input('approval_status') == "2"){
					$data->where("$Dispatch.approval_status",$request->input('approval_status'));
				}
			}
			if(!empty($MOB_FROM_DOC)){
				$MRFIDS 	= AdminUser::where("adminuserid",$AdminUserID)->value("assign_mrf_id");
				if(!empty($MRFIDS)){
					$data->whereIn("$Dispatch.master_dept_id",$MRFIDS);

					if(!empty($MOB_DISPATCH_ID))
					{
						$data->where("$Dispatch.id","like","%".$MOB_DISPATCH_ID."%");
					}
					if(!empty($MOB_CHALLAN)){
						$data->where("$Dispatch.challan_no","like","%".$MOB_CHALLAN."%");
					}
				}else{
					$data->where("$Dispatch.id",0);
				}
			}
		}
			/* FROM MOBILE SIDE EPR DOCUMENT UPLOAD FILTER - 01 JULY 2020 - AXAY SHAH*/


		// LiveServices::toSqlWithBinding($data);
		$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);

		if(!empty($result)){
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
				foreach($toArray['result'] as $key => $value){
					$InvoiceId 	= "";
					$url 		= '';
					$invoiceEdit = 0;
					$toArray['result'][$key]['dispatch_raw'] = url('/getChallan')."/".passencrypt($toArray['result'][$key]['id']);
					if($value['invoice_generated'] == 1 && $value['invoice_cancel'] != 1){
						$InvoiceId 		= WmInvoices::where("dispatch_id",$value['id'])->value('id');
						$invoiceEdit 	= WmInvoices::where("id",$InvoiceId)->value('invoice_edit');
					}
					if(!empty($InvoiceId)){
						$url = url('/invoice')."/".passencrypt($InvoiceId);
					}
					$toArray['result'][$key]['edit_invoice_enable'] = (!empty($InvoiceId) ? 1 : 0 );
					// $toArray['result'][$key]['invoice_edit'] 		= (!empty($invoiceEdit) ? 1 : 0 );
					$toArray['result'][$key]['invoice_edit'] 			= 0;
					$toArray['result'][$key]['invoice_id'] 				= $InvoiceId;
					$toArray['result'][$key]['invoice_url'] 			= $url;
					$toArray['result'][$key]['eway_bill_upload_msg'] 	= trans("message.EWAY_BILL_UPLOAD_MSG");
					$toArray['result'][$key]['eway_bill_required'] 		= false;
					$toArray['result'][$key]['cancel_ewaybill_flag'] 	= (!empty($value['eway_bill_no'])) ? true : false;
					################# DOWNLOAD INVOICE BY FLAG CODE ###################
					$toArray['result'][$key]['invoice_download_flag'] 	= 1;
					$TotalInvoiceAmount 			= WmSalesMaster::CalculateTotalInvoiceAmount($value['id']);

					############# BILL FROM AND DESTINATION GST STATE CODE CONDITION AS DISCUSS WITH ACCOUNT TEAM ##################
					$BILL_DISPLAY_GST_CODE 			= WmDepartment::where("id",$value["bill_from_mrf_id"])->leftjoin("view_city_state_contry_list","gst_state_id","=","gst_state_code_id")->value("display_state_code");
					$DEST_DISPLAY_GST_CODE 			= GSTStateCodes::where("id",$value["destination_state_code"])->value("display_state_code");
					$toArray['result'][$key]['bill_from_gst_display_state_code'] 	= $BILL_DISPLAY_GST_CODE;
					$toArray['result'][$key]['dest_gst_display_state_code'] 		= $DEST_DISPLAY_GST_CODE;
					############# BILL FROM AND DESTINATION GST STATE CODE CONDITION AS DISCUSS WITH ACCOUNT TEAM ##################
					if($TotalInvoiceAmount >= 0 && ($TotalInvoiceAmount > EWAY_BILL_MIN_AMOUNT || $value['dispatch_type'] == NON_RECYCLEBLE_TYPE ) || (!empty($BILL_DISPLAY_GST_CODE) && !empty($DEST_DISPLAY_GST_CODE) && $BILL_DISPLAY_GST_CODE != $DEST_DISPLAY_GST_CODE)){
						$toArray['result'][$key]['eway_bill_required']  = (!empty($value['eway_bill_no']) || $value['approval_status'] != 1) ? false : true;
						if(empty($value['eway_bill_no'])){
							$toArray['result'][$key]['invoice_download_flag'] = 0;
						}
					}
					if(empty($value['epr_waybridge_no']) || empty($value['epr_waybridge_slip_id'])){
						$toArray['result'][$key]['invoice_download_flag'] = 0;
					}

					if(isset(Auth()->user()->adminuserid) && Auth()->user()->adminuserid == 553){
						$toArray['result'][$key]['invoice_download_flag'] = 1;
					}
					if($TotalInvoiceAmount > 0 && empty($value['e_invoice_no']) &&  $value['dispatch_type'] != NON_RECYCLEBLE_TYPE){
						$toArray['result'][$key]['invoice_download_flag'] 	= 1;
						$toArray['result'][$key]['eway_bill_upload_msg'] 	= trans("message.E_INVOICE_UPLOAD_MSG");;
					}

					#################### VIRTUAL MRF APPROVAL AND MAIN APPRLVAL RIGHTS LOGIC ################
					$toArray['result'][$key]['show_dispatch_approval'] 	= 0;
					$MainApproval = AdminUserRights::where("adminuserid",Auth()->user()->adminuserid)->where("trnid",DISPATCH_RATE_APPROVAL_RIGHTS)->count();
					if($MainApproval > 0){
						$toArray['result'][$key]['show_dispatch_approval'] 	= 1;
					}else{
						$VirtualApproval = AdminUserRights::where("adminuserid",Auth()->user()->adminuserid)->where("trnid",DISPATCH_VIRTUAL_RATE_APPROVAL_RIGHTS)->count();
						if($VirtualApproval > 0){
							$toArray['result'][$key]['show_dispatch_approval'] 	= 1;
						}
					}
					#################### VIRTUAL MRF APPROVAL AND MAIN APPRLVAL RIGHTS LOGIC ################

					######### E INVOICE #############
					$SALES_GST_AMT 	= WmSalesMaster::where("dispatch_id",$value["id"])->sum("gst_amount");
					$CLIENT_GST 	= (!empty($value['gstin_no']) ? $value["gstin_no"] : "");

					$toArray['result'][$key]['generate_einvoice'] 	= ($SALES_GST_AMT > 0 && !empty($CLIENT_GST) && empty($value['e_invoice_no'])) ? 1 : 0;
					$toArray['result'][$key]['cancel_einvoice'] 	= (!empty($value['e_invoice_no'])) ? 1 : 0;
					######### E INVOICE #############

					################# DOWNLOAD INVOICE BY FLAG CODE ###################
					$toArray['result'][$key]['epr_rate_update_flag'] = ($value['approval_status'] == 1 && $value['epr_rate_added'] == 0) ? 1 : 0;
					############### MISSING DOCUMENT FLAG SHOWING  CHALLAN CODE REMAIN ###################

					$COLOR_RED 		= "red";
					$COLOR_GREEN 	= "green";
					$toArray['result'][$key]['badge_ewaybill'] 				= "E";
					$toArray['result'][$key]['badge_billt'] 				= "B";
					$toArray['result'][$key]['badge_challan'] 				= "";
					$toArray['result'][$key]['badge_waybridge'] 			= "W";
					$toArray['result'][$key]['badge_einvoice'] 				= "EI";
					$toArray['result'][$key]['badge_unload_slip'] 			= "US";
					$toArray['result'][$key]['badge_transporter_invoice'] 	= "TI";
					$toArray['result'][$key]['badge_color_einvoice'] 		= (empty($value['e_invoice_no'])) 	? $COLOR_RED : $COLOR_GREEN;
					$toArray['result'][$key]['badge_color_ewaybill'] 		= (empty($value['epr_ewaybill_media_id'])) 	? $COLOR_RED : $COLOR_GREEN;
					$toArray['result'][$key]['badge_color_billt'] 			= (empty($value['epr_billt_media_id']))		? $COLOR_RED : $COLOR_GREEN;
					$toArray['result'][$key]['badge_color_challan'] 		= (empty($value['epr_challan_media_id'])) 	? $COLOR_RED : $COLOR_GREEN;
					$toArray['result'][$key]['badge_color_waybridge'] 		= (empty($value['epr_waybridge_slip_id'])) 	? $COLOR_RED : $COLOR_GREEN;
					$toArray['result'][$key]['badge_color_transporter_invoice'] = (empty($value['transporter_invoice_media_id'])) 	? $COLOR_RED : $COLOR_GREEN;
					$toArray['result'][$key]['badge_color_unloading_slip'] 	= (empty($value['unloading_slip_media_id'])) 		? $COLOR_RED : $COLOR_GREEN;
					############### MISSING DOCUMENT FLAG SHOWING ###################

					################# MAP INVOICE FLAG ###################
					$toArray['result'][$key]['can_map_invoice'] = ($value['approval_status'] == 1)?1:0;
					################# MAP INVOICE FLAG ###################
					
					
					if($value['from_mrf'] == "Y") {
						$toArray['result'][$key]['origin_name'] = WmDepartment::where("id",$value['origin'])->value("department_name");
					}
					if (strtolower($value['approval_status_name']) == "rejected") {
						$toArray['result'][$key]['invoice_generated'] = 0;
					}
				}
				$result = $toArray;
			}
		}


		return $result;

	}

	/*
	Use 	: List Dispatch
	Author 	: Axay Shah
	Date 	: 04 June,2019
	*/
	public static function GetById($DispatchId,$ShowNameWithGST = false)
	{
		$CustomerMaster 	= new CustomerMaster();
		$LocationMaster 	= new LocationMaster();
		$CompanyMaster		= new CompanyMaster();
		$VehicleMaster		= new VehicleMaster();
		$ClientMaster 		= new WmClientMaster();
		$BatchMediaMas 		= new WmBatchMediaMaster();
		$MediaMaster 		= new MediaMaster();
		$DepartmentMaster 	= new WmDepartment();
		$StateMaster		= new StateMaster();
		$WMProductPrice		= new WmProductClientPriceMaster();

		$self 				= (new static)->getTable();
		$Customer 			= $CustomerMaster->getTable();
		$Location 			= $LocationMaster->getTable();
		$Company 			= $CompanyMaster->getTable();
		$Vehicle 			= $VehicleMaster->getTable();
		$Client 			= $ClientMaster->getTable();
		$BatchMedia			= $BatchMediaMas->getTable();
		$Media 				= $MediaMaster->getTable();
		$Department			= $DepartmentMaster->getTable();
		$State				= $StateMaster->getTable();

		$data =  self::with(["DispatchImages" =>function($q){
								$q->select("*",\DB::raw("'' as image_url"));
							}])->select(
										\DB::raw("CASE
											WHEN($Customer.first_name = '') THEN Concat($Customer.last_name,'-',$Customer.code)
											WHEN($Customer.last_name = '') THEN Concat($Customer.first_name,'-',$Customer.code)
											WHEN($Customer.last_name = '' AND $Customer.first_name = '') THEN $Customer.code
										ELSE
											Concat($Customer.first_name,' ',$Customer.last_name,'-',$Customer.code)
										END AS origin_name"),
										\DB::raw("
											Concat($Customer.first_name,' ',$Customer.last_name)
										AS origin_full_name"),
										"$self.*",
										\DB::raw("(IF ($self.master_dept_state_code = $self.destination_state_code,'Y', 'N')) as is_from_same_state"),
										\DB::raw("CONCAT($Customer.address1,' ',$Customer.address2) as origin_address"),
										"$Customer.mobile_no",
										"$Customer.zipcode",
										"$Location.city as origin_city_name",
										"COM.company_id",
										"COM.company_name",
										"COM.address1",
										"COM.address2",
										"COM.city",
										"COM.zipcode",
										"C.client_name",
										"C.address as client_address",
										"C.gstin_no",
										"DOC.city as destination_city_name",
										"ORS.state_name as Origin_state",
										"DRS.state_name as Client_state",
										"$self.shipping_address",
										"DEPT.gst_state_code_id as bill_from_gst_state_id",
										"C.days as terms_of_payment",
										\DB::raw("IF(C.payment_mode = 1, 'Cash', 'Cheque') as payment_mode_name"),
										"C.payment_mode",
										\DB::raw("COM.phone_office as company_phone"),
										\DB::raw("COM.gst_no as company_gst_no"),
										"V.vehicle_number",
										"V.vehicle_rc_book_no as rc_book_no",
										\DB::raw("LOC.city as company_city"))
										->with(['DispatchProductData'=>function($query){
											$query->join("wm_product_master AS WPM","wm_dispatch_product.product_id","=","WPM.id");
											$query->select("wm_dispatch_product.*","WPM.title","WPM.hsn_code","WPM.cgst","WPM.igst","WPM.sgst");
											$query->orderBy("wm_dispatch_product.id","ASC");
										}])
									->leftjoin($Customer,"$self.origin","=","$Customer.customer_id")
									->leftjoin("shipping_address_master as SAM","$self.shipping_address_id","=","SAM.id")
									->leftjoin($Location,"$Customer.city","=","$Location.location_id")
									->leftjoin("$Company as COM","$self.company_id","=","COM.company_id")
									->leftjoin("$Location as LOC","COM.city","=","LOC.location_id")
									->leftjoin("$Location as DOC","$self.destination_city","=","DOC.location_id")
									->leftjoin("$Vehicle as V","$self.vehicle_id","=","V.vehicle_id")
									->leftjoin("$Department as DEPT","$self.bill_from_mrf_id","=","DEPT.id")
									->leftjoin("$Client as C","$self.destination","=","C.id")
									->leftjoin("$State as ORS","LOC.state_id","=","ORS.state_id")
									->leftjoin("$State as DRS","DOC.state_id","=","DRS.state_id")
									->leftjoin("$BatchMedia as BAT","$self.id","=","BAT.dispatch_id")
									->leftjoin("$Media as M","BAT.image_id","=","M.id")
									->where("$self.id",$DispatchId)
									->first();
		$data['images'] 		= array();
		$data['salesProduct'] 	= array();
		$Title 					= "";
		$Address 				= "";
		$isVirtual 				= "";
		$mrfGstIn 				= "";
		$images 				= WmBatchMediaMaster::where("dispatch_id",$DispatchId)->pluck('image_id');
		if($data){
			########### VEHICLE INOUT API CALL IN EDIT DEPEND ON FLAG  SINCE - 17 MARCH 2021 ###########
			$data['vehicle_tare_gross_weight_api_flag'] = false;
			$data["auto_waybridge_ids"] = \DB::table("auto_way_bridge_details")->where("dispatch_id",$DispatchId)->pluck("id");
			########### VEHICLE INOUT API CALL IN EDIT DEPEND ON FLAG  SINCE - 17 MARCH 2021 ###########
			$ShippingAddress 		= (isset($data->shipping_address_id)) ? $data->shipping_address_id : 0;
			$data['consignee_name'] = ShippingAddressMaster::where('id',$ShippingAddress)->value('consignee_name');
			$data['dispatch_raw'] 	= url('/getChallan')."/".passencrypt($DispatchId);
			$data['DC_Date'] 		= (isset($data->dispatch_date) && !empty($data->dispatch_date)) ? date("Y-m-d",strtotime($data->dispatch_date)) : date("Y-m-d");
			$data['Origin_state'] 	= (isset($data->Origin_state) && !empty($data->Origin_state)) ? $data->Origin_state : "";
			$data['Client_state'] 	= (isset($data->Client_state) && !empty($data->Client_state))  ? $data->Client_state : "";
			$data['destination_state_code'] 	= GSTStateCodes::where("id",$data['destination_state_code'])->value("display_state_code");
			$BILL_FROM_STATE_DISPLAY_CODE = GSTStateCodes::where("id",$data['bill_from_gst_state_id'])->value("display_state_code");

			if($BILL_FROM_STATE_DISPLAY_CODE == $data["destination_state_code"])
			{
				$data->is_from_same_state = "Y";
			}else{
				$data->is_from_same_state = "N";
			}
			if($data->from_mrf == "Y") {
				$MRF = WmDepartment::find($data->origin);
				$data->origin_name = ($MRF) ? $MRF->department_name : $data->origin_name;
				$data->origin_city_name = ($MRF) ? LocationMaster::where("location_id",$MRF->location_id)->value('city') : "";
			} else {
				$MRF = WmDepartment::find($data->master_dept_id);
			}
			if($data->nespl == "1") {
				$Title		= NESPL_TITLE;
				$Address 	= $MRF->address;
				$isVirtual 	= $MRF->is_virtual;
				$mrfGstIn 	= NESPL_GST;
			} elseif($MRF) {
				$Title		= $data->company_name;
				$Address 	= $MRF->address;
				$isVirtual 	= $MRF->is_virtual;
				$mrfGstIn 	= $MRF->gst_in;
			}
			$data['mrf_title'] 		= $Title;
			$data['mrf_address'] 	= $Address;
			$data['is_virtual'] 	= $isVirtual;
			$data['mrf_gst_in'] 	= $mrfGstIn;
			if(!empty($images)) {
				$media 			= MediaMaster::whereIn("id",$images)->get();
				$data['images'] = $media;
			}
			$EPR_IMAGES = self::getEprDocument($DispatchId);
			$data['epr_challan_url'] 		= !empty($EPR_IMAGES['epr_challan_url']) ? $EPR_IMAGES['epr_challan_url'] : "";
			$data['epr_billt_url'] 			= !empty($EPR_IMAGES['epr_billt_url']) ? $EPR_IMAGES['epr_billt_url'] : "";
			$data['epr_waybridge_url'] 		= !empty($EPR_IMAGES['epr_waybridge_url']) ? $EPR_IMAGES['epr_waybridge_url'] : "";
			$data['epr_eway_bill_url'] 		= !empty($EPR_IMAGES['epr_eway_bill_url']) ? $EPR_IMAGES['epr_eway_bill_url'] : "";
			$data['epr_waybridge_url_data'] = isset($EPR_IMAGES['epr_waybridge_url_data']) ? $EPR_IMAGES['epr_waybridge_url_data'] : "";
			$data['epr_billt_url_data'] 	= isset($EPR_IMAGES['epr_billt_url_data']) ? $EPR_IMAGES['epr_billt_url_data'] : "";
			$data['epr_waybridge_url_data'] = isset($EPR_IMAGES['epr_waybridge_url_data']) ? $EPR_IMAGES['epr_waybridge_url_data'] : "";
			$data['epr_eway_bill_url_data'] = isset($EPR_IMAGES['epr_eway_bill_url_data']) ? $EPR_IMAGES['epr_eway_bill_url_data'] : "";
			$data['transporter_invoice_url_data'] = isset($EPR_IMAGES['transporter_invoice_url_data']) ? $EPR_IMAGES['transporter_invoice_url_data'] : "";
			$data['unloading_slip_url_data'] = isset($EPR_IMAGES['unloading_slip_url_data']) ? $EPR_IMAGES['unloading_slip_url_data'] : "";
			$data['salesProduct'] 	= $data->DispatchProductData;
			$FinalTotalPrice 		= 0;
			$ShowProductPriceTrend	= false;
			$FROM_SAME_STATE 		= $data->is_from_same_state;
			$OriginID 				= ($data['from_mrf'] == "N") ? $data['origin'] : 0;
			if(!empty($data['salesProduct'])) {
				foreach($data['salesProduct'] as $raw) {
					$GST_NAME 		= "";
					if($ShowNameWithGST){
						$GST_NAME 	= ($FROM_SAME_STATE == "Y") ? "(CGST@".$raw->cgst_rate."% SGST@".$raw->sgst_rate."%)" : "(IGST@".$raw->igst_rate."%)";
					}
					$raw->title 			= $raw->title." ".$GST_NAME;
					$TotalPrice 			= 0;
					$price  				= (isset($raw->price) && !empty($raw->price)) ? $raw->price : 0;
					$Qty 					= (isset($raw->quantity) && !empty($raw->quantity)) ? $raw->quantity : 0;
					$TotalPrice 			= $price * $Qty;
					$raw->totalMul 			= _FormatNumberV2($TotalPrice);
					$FinalTotalPrice 		= $FinalTotalPrice + $TotalPrice;
					$raw->Max_Sales_Rate 	= $WMProductPrice->getMaxProductPrice($raw->product_id,$data->company_id);
					$raw->ProductPriceTrend = $WMProductPrice->getMaxProductPriceTrend($raw->product_id,$data->company_id);
					$ShowProductPriceTrend	= (count($raw->ProductPriceTrend) > 0)?true:$ShowProductPriceTrend;
					############### ADD PRICE FROM SALES ORDER IF IT IS APPROVED ###############
					$GET_RATE_DATA 				= WmProductMaster::GetSalesOrderClientRate($data->dispatch_date,$raw->product_id,$data->master_dept_id,$data->destination,$OriginID);
					if(!empty($GET_RATE_DATA)){
						$raw->price		= _FormatNumberV2($GET_RATE_DATA[0]->rate);
					}

					############### ADD PRICE FROM SALES ORDER IF IT IS APPROVED ###############
				}
			}
			$data['totalPrice'] 			= _FormatNumberV2($FinalTotalPrice);
			$data['ShowProductPriceTrend']	= $ShowProductPriceTrend;

		}
		return $data;
	}

	/*
	Use 	: Add Dispatch
	Author 	: Axay Shah
	Date 	: 29 May,2019
	*/
	public static function InsertDispatch($request){
		try{
			$dispatchPlanId 		= '';
			$OriginCity 			= 0;
			$DestinationCity 		= 0;
			$OriginStateCode 		= 0;
			$DestinationStateCode 	= 0;
			$DISPATCH_ID 			= 0;
			$CODE 					= 0;
			$challan_no				= 0;
			$BASE_LOCATION_ID 		= Auth()->user()->base_location;
			$ProductConsumed 		= array();
			$FromMrf 				= (isset($request['from_mrf']) && !empty($request['from_mrf'])) ? $request['from_mrf'] : 'N';
			$Dated 					= (isset($request['dated']) && !empty($request['dated'])) ? $request['dated'] : '';
			$bill_of_lading 		= (isset($request['bill_of_lading']) && !empty($request['bill_of_lading'])) ? $request['bill_of_lading'] : '';
			$DeptId 				= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ? $request['master_dept_id'] : 0;
			$ShippingAddress 		= (isset($request['shipping_address']) && !empty($request['shipping_address'])) ? strtolower($request['shipping_address']) : "";
			$ShippingAddressId 		= (isset($request['shipping_address_id']) && !empty($request['shipping_address_id'])) ? $request['shipping_address_id'] : "";
			$ShippingState 			= (isset($request['shipping_state']) && !empty($request['shipping_state'])) ? $request['shipping_state'] : "";
			$ShippingPinCode		= (isset($request['shipping_pincode']) && !empty($request['shipping_pincode'])) ? $request['shipping_pincode'] : "";
			$ShippingCity 			= (isset($request['shipping_city']) && !empty($request['shipping_city'])) ? $request['shipping_city'] : "";
			$ShippingStateCode		= (isset($request['shipping_state_code']) && !empty($request['shipping_state_code'])) ? $request['shipping_state_code'] : "";
			$consignee_name			= (isset($request['consignee_name']) && !empty($request['consignee_name'])) ? $request['consignee_name'] : "";
			$ClientId				= (isset($request['client_id']) && !empty($request['client_id'])) ? $request['client_id'] : 0;
			$NESPL					= (isset($request['nespl']) && !empty($request['nespl'])) ? $request['nespl'] : 0;
			$FROM_JOBWORK			= (isset($request['from_jobwork']) && !empty($request['from_jobwork'])) ? $request['from_jobwork'] : 0;
			$JOBWORK_ID				= (isset($request['jobwork_id']) && !empty($request['jobwork_id'])) ? $request['jobwork_id'] : 0;
			$TYPE_OF_TRANSACTION 	= (isset($request['type_of_transaction']) && !empty($request['type_of_transaction'])) ? $request['type_of_transaction'] : 0;
			$RENT_AMT 			 	= (isset($request['rent_amount']) && !empty($request['rent_amount'])) ? $request['rent_amount'] : 0;
			$DISCOUNT_AMT 		 	= (isset($request['discount_amount']) && !empty($request['discount_amount'])) ? $request['discount_amount'] : 0;
			$TARE_WEIGHT 		 	= (isset($request['tare_weight']) && !empty($request['tare_weight'])) ? $request['tare_weight'] : 0;
			$GROSS_WEIGHT 		 	= (isset($request['gross_weight']) && !empty($request['gross_weight'])) ? $request['gross_weight'] : 0;
			$SALES_ORDER_ID 		= (isset($request['sales_order_id']) && !empty($request['sales_order_id'])) ? $request['sales_order_id'] : 0;
			$BILL_FROM_MRF_ID 		= (isset($request['bill_from_mrf_id']) && !empty($request['bill_from_mrf_id'])) ? $request['bill_from_mrf_id'] : $DeptId;
			$TRANSPORTER_NAME 		= (isset($request['transporter_name']) && !empty($request['transporter_name'])) ? $request['transporter_name'] : "";
			$VEHICLE_IN_OUT_ID 		= (isset($request['vehicle_in_out_id']) && !empty($request['vehicle_in_out_id'])) ? $request['vehicle_in_out_id'] : 0;
			$VENDOR_NAME_FLAG 		= (isset($request['show_vendor_name_flag']) && !empty($request['show_vendor_name_flag'])) ? $request['show_vendor_name_flag'] : 0;
			$TRANSPORTER_PO_ID 		= (isset($request['transporter_po_id']) && !empty($request['transporter_po_id'])) ? $request['transporter_po_id'] : 0;
			$VIRTUAL_TARGET 		= (isset($request['virtual_target']) && !empty($request['virtual_target'])) ? $request['virtual_target'] : 0;
			$COL_CYCLE_TERM 		= (isset($request['collection_cycle_term']) && !empty($request['collection_cycle_term'])) ? $request['collection_cycle_term'] : 0;
			/* GET TRANSFER TRANS AS PER TYPE OF TRANSACTION - 23 APRIL 2020 -AXAY SHAH */
			########## FINACIAL YEAR 2021 REGARDING CHANGES #########
			$DISPATCH_TYPE_TRN 		= (isset($request['dispatch_type']) && !empty($request['dispatch_type'])) ? $request['dispatch_type'] : 0;
			$TRANSFER_TRANS 		= TransactionMasterCodesMrfWise::GetTrnType($DISPATCH_TYPE_TRN);
			$AUTO_WAYBRIDGE_IDS 	= (isset($request['auto_waybridge_ids']) && !empty($request['auto_waybridge_ids'])) ? $request['auto_waybridge_ids'] : 0;
			$TRANSPORT_COST_ID 		= (isset($request['transport_cost_id']) && !empty($request['transport_cost_id'])) ? $request['transport_cost_id'] : 0;
			$REMARKS 				= (isset($request['remark']) && !empty($request['remark'])) ? $request['remark'] : "";
			$RELATION_SHIP_ID = (isset($request['relationship_manager_id']) && !empty($request['relationship_manager_id'])) ? $request['relationship_manager_id'] : 0;
			########## FINACIAL YEAR 2021 REGARDING CHANGES #########
			if(empty($TRANSFER_TRANS)) {
				return false;
			}

			if(!empty($TRANSFER_TRANS)){
				$GET_CODE 			= TransactionMasterCodesMrfWise::GetLastTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS);
				if($GET_CODE){
					$CODE 			= 	$GET_CODE->code_value + 1;
					$challan_no 	=   $GET_CODE->group_prefix.LeadingZero($CODE);
					$challan_flag 	= 	true;
				}
			}

			/* GET TRANSFER TRANS AS PER TYPE OF TRANSACTION - 23 APRIL 2020 -AXAY SHAH */
			/* IF DIRECT DISPATCH FROM MRF THEN ADD MRF ID IN ORIGIN AND CITY OF MRF IN ORIGIN CITY - 03 JULY 2019*/
			if(isset($request['origin']) && !empty($request['origin'])){
				if($FromMrf == "N"){
					$OriginCity 	= CustomerMaster::where("customer_id",$request['origin'])->value('city');
				}else{
					$OriginCity 	= WmDepartment::where("id",$request['origin'])->value('location_id');
					$DeptId 		= $request['origin'];
				}
			}
			/* ####### MASTER DEPARTMENT GST STATE CODE CHANGES ####### */
			$MasterDeptStateID = 0;
			if(!empty($BILL_FROM_MRF_ID)){
				$MasterDeptStateID = WmDepartment::where("id",$BILL_FROM_MRF_ID)->value('gst_state_code_id');
			}
			/* ####### MASTER DEPARTMENT GST STATE CODE CHANGES ####### */
			if(isset($request['destination']) && !empty($request['destination'])){
				$DestinationCity 	= WmClientMaster::where("id",$request['destination'])->value('city_id');
			}
			$OriginGSTStateCodeData = StateMaster::GetGSTCodeByCustomerCity($OriginCity);
			if(isset($OriginGSTStateCodeData->state_code)){
				$OriginStateCode 	= $OriginGSTStateCodeData->state_code;
			}

			$ClientMasterData			= WmClientMaster::find($request['destination']);
			if($ClientMasterData){
				$DestinationStateCode 	= $ClientMasterData->gst_state_code;
				if(empty($DestinationStateCode)){
					/* PRODUCT CONSUMED OF CLIENT - 29 JULY 2019*/
					if(isset($ClientMasterData->product_consumed) && !empty($ClientMasterData->product_consumed)){
						$ProductConsumed = explode(",",$ClientMasterData->product_consumed);
					}
					/*END PRODUCT CONSUMED OF CLIENT*/
					$getCode = StateMaster::GetGSTCodeByCustomerCity($ClientMasterData->city_id);
					if(isset($getCode->state_code)){
						$DestinationStateCode 	= $getCode->state_code;
					}
				}
			}

			/* END AUTO GENERATED CHALLAN NO*/
			$ShippingState 					= GSTStateCodes::where("state_code",$ShippingStateCode)->value('state_name');
			$Dispatch 						= new WmDispatch();
			$ddate 							= date('Y-m-d',strtotime($request['dispatch_date']));
			$total_qty 						= $request['total_qty'];
			$Dispatch->dispatchplan_id		= $dispatchPlanId;
			$Dispatch->shipping_address		= $ShippingAddress;
			$Dispatch->shipping_address_id	= $ShippingAddressId;
			$Dispatch->shipping_state		= $ShippingState;
			$Dispatch->shipping_state_code	= $ShippingStateCode;
			$Dispatch->shipping_city		= $ShippingCity;
			$Dispatch->shipping_pincode		= $ShippingPinCode;
			$Dispatch->client_master_id  	= $ClientId;
			$Dispatch->quantity				= $total_qty;
			$Dispatch->vehicle_id			= $request['vehicle_id'];
			$Dispatch->origin				= $request['origin'];
			$Dispatch->origin_state_code 	= $OriginStateCode;
			$Dispatch->destination_state_code = $DestinationStateCode;
			$Dispatch->origin_city			= $OriginCity;
			$Dispatch->from_mrf				= $FromMrf;
			$Dispatch->company_id			= Auth()->user()->company_id;
			$Dispatch->destination			= $request['destination'];
			$Dispatch->destination_city		= $DestinationCity;
			$Dispatch->driver_name			= (isset($request['dr_name'])?$request['dr_name']:'');
			$Dispatch->driver_mob_no		= (isset($request['dr_mobile'])?$request['dr_mobile']:'');
			$Dispatch->master_dept_id		= $DeptId;
			$Dispatch->dispatch_date		= date('Y-m-d',strtotime($request['dispatch_date']));
			$Dispatch->dispatch_type		= (isset($request['dispatch_type'])?$request['dispatch_type']:0);
			$Dispatch->recyclable_type		= (isset($request['recyclable_type'])?$request['recyclable_type']:0);
			$Dispatch->created_by			= Auth()->user()->adminuserid;
			$Dispatch->created_at			= date('Y-m-d H:i:s');
			$Dispatch->invoice_generated	= 0;
			$Dispatch->challan_no 			= $challan_no;
			$Dispatch->type_of_transaction 	= $TYPE_OF_TRANSACTION;
			$Dispatch->nespl 				= $NESPL;
			$Dispatch->bill_of_lading 		= $bill_of_lading;
			$Dispatch->dated 				= $Dated;
			$Dispatch->from_jobwork			= $FROM_JOBWORK;
			$Dispatch->jobwork_id			= $JOBWORK_ID;
			$Dispatch->rent_amt				= $RENT_AMT;
			$Dispatch->discount_amt			= $DISCOUNT_AMT;
			$Dispatch->tare_weight			= $TARE_WEIGHT;
			$Dispatch->gross_weight			  = $GROSS_WEIGHT;
			$Dispatch->challan_date 		  = (!empty($challan_no)) ? date("Y-m-d H:i:s") : "";
			$Dispatch->eway_bill_no			  = (isset($request['eway_bill_no']) && !empty($request['eway_bill_no'])?$request['eway_bill_no']:'');
			$Dispatch->dispatchplan_id		  = $SALES_ORDER_ID;
			$Dispatch->master_dept_state_code = $MasterDeptStateID;
			$Dispatch->bill_from_mrf_id		  = $BILL_FROM_MRF_ID;
			$Dispatch->virtual_target		  = $VIRTUAL_TARGET;
			$Dispatch->transporter_name		  = (!empty($TRANSPORTER_PO_ID)) ? TransporterMaster::where("id",$TRANSPORTER_PO_ID)->value('name') : $TRANSPORTER_NAME;

			$Dispatch->show_vendor_name_flag  = $VENDOR_NAME_FLAG;
			$Dispatch->transporter_po_id  	  = $TRANSPORTER_PO_ID;
			$Dispatch->collection_cycle_term  = $COL_CYCLE_TERM;
			$Dispatch->remarks  	  	  	  = $REMARKS;
			$Dispatch->transport_cost_id 	  = $TRANSPORT_COST_ID;
			$Dispatch->relationship_manager_id = $RELATION_SHIP_ID;
			$TranspoterData = TransporterDetailsMaster::find($TRANSPORTER_PO_ID);
			if($TranspoterData){
				$TRANSPORTER_NAME =  TransporterMaster::where("id",$TranspoterData->transporter_id)->value('name');
			}
			$Dispatch->transporter_name   	= $TRANSPORTER_NAME;
			if($Dispatch->save()){
				$DISPATCH_ID 		= $Dispatch->id;
				$IS_DIRECT_DISPATCH = ($Dispatch->appointment_id > 0) ? 1 : 0;
				if(!empty($AUTO_WAYBRIDGE_IDS)){
					$AUTO_WAYBRIDGE_IDS = explode(",",$AUTO_WAYBRIDGE_IDS);
					\DB::table("auto_way_bridge_details")->whereIn("id",$AUTO_WAYBRIDGE_IDS)->update(["dispatch_id"=>$DISPATCH_ID,"is_used"=>"1"]);
				}
				/* UPDATE CODE IN TRANSACTION MASTER TABLE SINCE- 09 APRIL 2020*/
				TransactionMasterCodesMrfWise::UpdateTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS,$CODE);
				/* UPDATE CODE IN TRANSACTION MASTER TABLE SINCE- 09 APRIL 2020*/
				######### VEHICLE INOUT PROCESS FLAG UPDATE - SINCE 08 MARCH 2021  #############
				WaybridgeModuleVehicleInOut::UpdateVehicleInOutFlag($VEHICLE_IN_OUT_ID,WAYBRIDGE_MODULE_DISPATCH,$DISPATCH_ID,1);
				######### VEHICLE INOUT PROCESS FLAG UPDATE - SINCE 08 MARCH 2021  #############
				$product 		= array();
				$ApprovalRequestProduct = array();
				$DirectApproveFlag 		= true;
				$ORID 					= ($Dispatch->from_mrf == "Y") ? 0 : $Dispatch->origin;
				if(isset($request['sales_product']) && !empty($request['sales_product'])){
					$product 		 	= json_decode($request['sales_product'],true);
					$i 				 	= 0;
					$totalProductCnt 	= count($product);
					$totalTrueFlagCnt 	= 0;
					foreach($product as $value)
					{
						$GET_RATE_DATA = WmProductMaster::GetSalesOrderClientRate(date("Y-m-d"),$value['product_id'],$Dispatch->master_dept_id,$Dispatch->client_master_id,$ORID);
						if(!empty($GET_RATE_DATA)){
							$client_rate 	= _FormatNumberV2($GET_RATE_DATA[0]->rate);
							$is_disable 	= 1;
							$qty 			= $GET_RATE_DATA[0]->qty;
							if($client_rate == $value['price']){
								$DirectApproveFlag 	= true;
								$SALES_ORDER_ID 	= $GET_RATE_DATA[0]->dispatch_plan_id;
								$totalTrueFlagCnt++;
							}else{
								$DirectApproveFlag 	= false;
								$SALES_ORDER_ID 	= 0;
							}
						}else{
							$DirectApproveFlag 		= false;
						}
						############### IF DISABLE FLAG TRUE THEN AUTO APPROVE ##############
						$DESCRIPTION 	= (isset($value['description']) 		&& !empty($value['description'])) 		? $value['description'] 		: "";
						$IS_BAILING 	= (isset($value['is_bailing']) 			&& !empty($value['is_bailing'])) 		? $value['is_bailing'] 			: 0;
						$BALINIGTYPE 	= (isset($value['bailing_type']) 		&& !empty($value['bailing_type'])) 		? $value['bailing_type'] 		: 0;
						$BALINIG_QTY 	= (isset($value['bail_qty']) 			&& !empty($value['bail_qty'])) 			? $value['bail_qty'] 			: 0;
						$BAILING_ID 	= (isset($value['bailing_master_id']) 	&& !empty($value['bailing_master_id'])) ? $value['bailing_master_id'] 	: 0;
						$QTY = $value['quantity'];
						if(!in_array($value['product_id'],$ProductConsumed)){
							array_push($ProductConsumed,$value['product_id']);
						}
						$ins_prd['dispatch_plan_id']	= 0;
						$ins_prd['dispatch_id']			= $DISPATCH_ID;
						$ins_prd['product_id']			= $value['product_id'];
						$ins_prd['description']			= $DESCRIPTION;
						$ins_prd['quantity']			= $QTY;
						$ins_prd['is_bailing']			= $IS_BAILING;
						$ins_prd['bailing_type']		= $BALINIGTYPE;
						$ins_prd['bail_qty']			= $BALINIG_QTY;
						$ins_prd['bailing_master_id'] 	= $BAILING_ID;
						$ins_prd['price']				= _FormatNumberV2($value['price']);
						$DispatchProductInsertedID 		= WmDispatchProduct::insertGetId($ins_prd);
						if($IS_BAILING == "1"){
							/*BAIL OUTWARD ENTRY productId,$bailQty,$bailType,$bailMasterId,$MRFID,$dispatchID,$OutwardDate */
							BailOutwardLedger::AddBailOutWard($value['product_id'],$BALINIG_QTY,$BALINIGTYPE,$BAILING_ID,$DeptId,$DISPATCH_ID,$Dispatch->dispatch_date);
						}
						/* MANAGE STOCK UPDATES IN PROCESS */
							$OUTWORDDATA 							= array();
							$OUTWORDDATA['sales_product_id'] 		= $value['product_id'];
							$OUTWORDDATA['product_id'] 				= 0;
							$OUTWORDDATA['production_report_id']	= 0;
							$OUTWORDDATA['ref_id']					= $Dispatch->id;
							$OUTWORDDATA['quantity']				= $QTY;
							$OUTWORDDATA['type']					= TYPE_DISPATCH;
							$OUTWORDDATA['product_type']			= PRODUCT_SALES;
							$OUTWORDDATA['mrf_id']					= $Dispatch->master_dept_id;
							$OUTWORDDATA['company_id']				= $Dispatch->company_id;
							$OUTWORDDATA['outward_date']			= date("Y-m-d",strtotime($Dispatch->dispatch_date));
							$OUTWORDDATA['created_by']				= Auth()->user()->adminuserid;
							$OUTWORDDATA['updated_by']				= Auth()->user()->adminuserid;
							OutWardLadger::AutoAddOutward($OUTWORDDATA);

							############ AVG PRICE FOR DIRECT DISPATCH AND NORMAL DISPATCH #################
							$AVG_PRICE 	= 	StockLadger::where("product_type",PRODUCT_SALES)
											->where("mrf_id",$Dispatch->bill_from_mrf_id)
											->where("stock_date",date("Y-m-d"))
											->where("product_id",$value['product_id'])
											->value("avg_price");
							// WmDispatchSalesProductAvgPrice::insert([
							// 	"dispatch_id" 			=> $DISPATCH_ID,
							// 	"collection_id" 		=> 0,
							// 	"direct_dispatch" 		=> 0,
							// 	"collection_detail_id" 	=> 0,
							// 	"purchase_product_id" 	=> 0,
							// 	"sales_product_id" 		=> $value['product_id'],
							// 	"price" 				=> 0,
							// 	"avg_price" 			=> _FormatNumberV2($AVG_PRICE),
							// 	"created_at" 			=> date("Y-m-d H:i:s"),
							// 	"updated_at" 			=> date("Y-m-d H:i:s"),
							// ]);
							############ AVG PRICE FOR DIRECT DISPATCH AND NORMAL DISPATCH #################
							/* MANAGE STOCK UPDATES IN PROCESS */
							/*THIS IS USE FOR SALES ORDER APPROVAL REQUEST IF SALES ORDER ID IS COME*/
							// if($SALES_ORDER_ID > 0){
								$ApprovalRequestProduct[$i]['id'] 			= $DispatchProductInsertedID;
								$ApprovalRequestProduct[$i]['product_id'] 	= $value['product_id'];
								$ApprovalRequestProduct[$i]['description'] 	= $DESCRIPTION;
								$ApprovalRequestProduct[$i]['quantity'] 	= $QTY;
								$ApprovalRequestProduct[$i]['rate'] 		= _FormatNumberV2($value['price']);
								$ApprovalRequestProduct[$i]['price'] 		= _FormatNumberV2($value['price']);
								$i++;
							// }
					}
					$SALES_ORDER_ID 	= ($totalProductCnt > 0 && $totalProductCnt == $totalTrueFlagCnt) ? $SALES_ORDER_ID : 0;
					$DirectApproveFlag 	= ($totalProductCnt > 0 && $totalProductCnt == $totalTrueFlagCnt) ? $DirectApproveFlag : false;
					
					/*Product Consume update in client master*/
					if(!empty($ProductConsumed)){
						$ProductConsumedString = implode(",",$ProductConsumed);
						WmClientMaster::where("id",$request['destination'])->update(["product_consumed"=>$ProductConsumedString]);
					}
					/*ADD SHIPING ADDRESS OF CLIENT*/
					// $AddAddress = ShippingAddressMaster::AddShippingAddress($ClientId,$ShippingAddress,$ShippingCity,$ShippingState,$ShippingStateCode,$ShippingPinCode,$consignee_name);
					// if($AddAddress > 0){
					// 	self::where("id",$Dispatch->id)->update(["shipping_address_id" => $AddAddress]);
					// }
					/*END SHIPPING ADDRESS CODE*/
					/*End code Product Consume update in client master*/



					if(isset($request['weight_count']) && !empty($request['weight_count'])){
						for($i = 0; $i < $request['weight_count']; $i++){
							$companyId = Auth()->user()->company_id;

							$path 				= PATH_COMPANY."/".PATH_DISPATCH;
							$destinationPath 	= public_path('/').$path;

							if(!is_dir($destinationPath)) {
								mkdir($destinationPath,0777,true);
							}

							$image 		= $request["scal_photo_".$i];
							$name 		= "scal_".$i.time().'.'.$image->getClientOriginalExtension();

							$image->move($destinationPath, $name);
							$media = WmDispatchMediaMaster::AddDispatchMedia($DISPATCH_ID,$name,$name,$path,PARA_WAYBRIDGE);
						}
					}

					/*UPDATE STATUS FOR APPROVAL RATE */
					$company 		= CompanyMaster::find(Auth()->user()->company);
					$FromEmail 		= array(
						"Name"	=>	env("MAIL_FROM_NAME"),
						"Email"	=>	env("MAIL_FROM_ADDRESS"),
					);
					if($company){
						$FromEmail 	= array (
							"Name" 	=> $company->company_name,
							"Email" => $company->company_email
						);
					}
					$DispatchData 		= WmDispatchProduct::GetProductByDispatchId($Dispatch->id);
					$Subject 			= "Dispatch Product Rate Approval";
					$Body 				= "Hello,<br>";
					$Body 				.= "Here are Dispatch Product Rate Added By ".Auth()->user()->firstname." ".Auth()->user()->lastname.".<br /><br />";
					$Body 				.= " Challan No is : <b>".$challan_no."</b>";

					/*IF MASTER DEPT. CITY IS INDOR THEN NO NEED TO SEND EMAIL AND SMS TO RK - 09-MARCH-2020*/
					$DeptLocId 			= WmDepartment::where("id",$Dispatch->master_dept_id)->first();
					if($DeptLocId){
						if(!empty($DeptLocId->mobile)){
							$MOBILE 	=  explode(",",$DeptLocId->mobile);
						}
						if(!empty($DeptLocId->rate_approval_email)){
							$GetApprovalEmail 	=  explode(",",$DeptLocId->rate_approval_email);
						}
					}
					
					// if(!empty($GetApprovalEmail)){
					// self::SendDispatchRateUpdateEmail($DispatchData,$FromEmail,$GetApprovalEmail,$Subject,$Body);
					// }
					// /* SEND SMS TO DEFINE MOBILE NO*/
					// if(!empty($MOBILE)){
					// 	foreach($MOBILE AS $RAW){
					// 		$message = "New Dispatch added for approval by ".Auth()->user()->firstname." ".Auth()->user()->lastname." with Challan no".$challan_no;
					// 		\App\Classes\SendSMS::sendMessage($RAW,$message);
					// 	}
					// }
					
					
					/* SEND SMS TO DEFINE MOBILE NO*/

					/*IF ENTRY FROM JOBWORK THEN UPDATE JOBWORK TABLE DATA OF THAT RELETED ID - 12 MARCH 2020*/
					if($SALES_ORDER_ID > 0) {
						$ApprovedBy 		= WmDispatchPlan::where("id",$SALES_ORDER_ID)->value("approved_by");
						$ApprovalRequest 	= new \Illuminate\Http\Request();
						$ApprovalRequest->request->add([
							'approval_status' 		=> 1,
							"challan_no" 			=> $challan_no,
							"dispatch_id" 			=> $DISPATCH_ID,
							"eway_bill_no" 			=> $Dispatch->eway_bill_no,
							"type_of_transaction" 	=> $TYPE_OF_TRANSACTION,
							"dispatchplan_id" 		=> $SALES_ORDER_ID,
							"product" 				=> $ApprovalRequestProduct,
							"approved_by" 			=> $ApprovedBy
						]);
						/* UPDATE */

						$ApprovalRequest = $ApprovalRequest->request->all();

						self::DispatchRateApproval((object)$ApprovalRequest);
						WmDispatchPlan::where("id",$SALES_ORDER_ID)->update(["is_dispatch"=>1]);


					}
				}
			}
			if(isset($request['dispatch_plan_id']) && !empty($request['dispatch_plan_id']))
			{
				$dispatchplan_id_str = rtrim(implode(',',$request['dispatch_plan_id']),',');
				self::updateDispatchPlanStatus(1,$dispatchplan_id_str);
			}

			/*IF ENTRY FROM JOBWORK THEN UPDATE JOBWORK TABLE DATA OF THAT RELETED ID - 12 MARCH 2020*/
			if($JOBWORK_ID > 0 && $FROM_JOBWORK == 1){
				JobWorkMaster::where("id",$JOBWORK_ID)->update(["is_dispatched"=>1]);
			}
			return $Dispatch;
		}catch(\Exception $e){
			dd($e->getMessage()." ".$e->getLine()." ".$e->getFile());
		}
	}

	/*
	Use 	: Update Dispatch
	Author 	: Axay Shah
	Date 	: 29 May,2019
	*/
	public static function UpdateDispatch($request){
		try{
			$deleteStockFlag 		= 0;
			$priviousChallan 		= 0;
			$dispatchPlanId 		= '';
			$OriginCity 			= 0;
			$DestinationCity 		= 0;
			$OriginStateCode		= 0;
			$DestinationStateCode 	= 0;
			$ProductConsumed 		= array();
			$CODE 					= 0;
			$CHALLAN_UPDATE 		= false;
			$BASE_LOCATION_ID 		= Auth()->user()->base_location;
			$Dated 					= (isset($request['dated']) && !empty($request['dated'])) ? $request['dated'] : '';
			$bill_of_lading 		= (isset($request['bill_of_lading']) && !empty($request['bill_of_lading'])) ? $request['bill_of_lading'] : '';
			$DispatchId 			= (isset($request['dispatch_id']) && !empty($request['dispatch_id'])) ? $request['dispatch_id']: 0;
			$DeptId 				= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ? $request['master_dept_id'] : 0;
			$Dispatch_date 			= (isset($request['dispatch_date']) && !empty($request['dispatch_date'])) ? date('Y-m-d',strtotime($request['dispatch_date'])) : date("Y-m-d");
			$FromMrf 				= (isset($request['from_mrf']) && !empty($request['from_mrf'])) ? $request['from_mrf'] : 'N';
			
			$ShippingAddressId 		= (isset($request['shipping_address_id']) && !empty($request['shipping_address_id'])) ? ($request['shipping_address_id']) : 0;
			$ShippingAddress 		= (isset($request['shipping_address']) && !empty($request['shipping_address'])) ? strtolower($request['shipping_address']) : "";
			$ShippingState 			= (isset($request['shipping_state']) && !empty($request['shipping_state'])) ? $request['shipping_state'] : "";
			$ShippingPinCode		= (isset($request['shipping_pincode']) && !empty($request['shipping_pincode'])) ? $request['shipping_pincode'] : "";
			$ShippingCity 			= (isset($request['shipping_city']) && !empty($request['shipping_city'])) ? $request['shipping_city'] : "";
			$ShippingStateCode		= (isset($request['shipping_state_code']) && !empty($request['shipping_state_code'])) ? $request['shipping_state_code'] : "";
			$consignee_name			= (isset($request['consignee_name']) && !empty($request['consignee_name'])) ? $request['consignee_name'] : "";
			$ClientId				= (isset($request['client_id']) && !empty($request['client_id'])) ? $request['client_id'] : 0;
			$NESPL					= (isset($request['nespl']) && !empty($request['nespl'])) ? $request['nespl'] : 0;
			$TYPE_OF_TRANSACTION 	= (isset($request['type_of_transaction']) && !empty($request['type_of_transaction'])) ? $request['type_of_transaction'] : 0;
			$RENT_AMT 			 	= (isset($request['rent_amount']) && !empty($request['rent_amount'])) ? $request['rent_amount'] : 0;
			$DISCOUNT_AMT 		 	= (isset($request['discount_amount']) && !empty($request['discount_amount'])) ? $request['discount_amount'] : 0;
			$TARE_WEIGHT 		 	= (isset($request['tare_weight']) && !empty($request['tare_weight'])) ? $request['tare_weight'] : 0;
			$GROSS_WEIGHT 		 	= (isset($request['gross_weight']) && !empty($request['gross_weight'])) ? $request['gross_weight'] : 0;
			$BILL_FROM_MRF_ID 		= (isset($request['bill_from_mrf_id']) && !empty($request['bill_from_mrf_id'])) ? $request['bill_from_mrf_id'] : $DeptId;
			$TRANSPORTER_NAME 		= (isset($request['transporter_name']) && !empty($request['transporter_name'])) ? $request['transporter_name'] : "";
			$VENDOR_NAME_FLAG 		= (isset($request['show_vendor_name_flag']) && !empty($request['show_vendor_name_flag'])) ? $request['show_vendor_name_flag'] : 0;
			$TRANSPORTER_PO_ID 		= (isset($request['transporter_po_id']) && !empty($request['transporter_po_id'])) ? $request['transporter_po_id'] : 0;
			$DISPATCH_TYPE_TRN 		= (isset($request['dispatch_type']) && !empty($request['dispatch_type'])) ? $request['dispatch_type'] : 0;
			$VIRTUAL_TARGET 		= (isset($request['virtual_target']) && !empty($request['virtual_target'])) ? $request['virtual_target'] : 0;
			$COL_CYCLE_TERM 		= (isset($request['collection_cycle_term']) && !empty($request['collection_cycle_term'])) ? $request['collection_cycle_term'] : 0;
			$TRANSPORT_COST_ID 		= (isset($request['transport_cost_id']) && !empty($request['transport_cost_id'])) ? $request['transport_cost_id'] : 0;
			$REMARKS 				= (isset($request['remark']) && !empty($request['remark'])) ? $request['remark'] : "";
			$RELATION_SHIP_ID 		= (isset($request['relationship_manager_id']) && !empty($request['relationship_manager_id'])) ? $request['relationship_manager_id'] : 0;
			/* IF DIRECT DISPATCH FROM MRF THEN ADD MRF ID IN ORIGIN AND CITY OF MRF IN ORIGIN CITY - 03 JULY 2019*/
			if(isset($request['origin']) && !empty($request['origin'])){
				if($FromMrf == "N"){
					$OriginCity 		= CustomerMaster::where("customer_id",$request['origin'])->value('city');
				}else{
					$OriginCity 		= WmDepartment::where("id",$request['origin'])->value('location_id');
					// $DeptId 			= $request['origin'];
				}
			}
			if(isset($request['destination']) && !empty($request['destination'])){
				$DestinationCity 	= WmClientMaster::where("id",$request['destination'])->value('city_id');
			}

			if(isset($request['destination']) && !empty($request['destination'])){
				$DestinationCity 	= WmClientMaster::where("id",$request['destination'])->value('city_id');
			}
			$OriginGSTStateCodeData = StateMaster::GetGSTCodeByCustomerCity($OriginCity);
			if(isset($OriginGSTStateCodeData->state_code)){
				$OriginStateCode = $OriginGSTStateCodeData->state_code;
			}
			/* ####### MASTER DEPARTMENT GST STATE CODE CHANGES ####### */
			$MasterDeptStateID = 0;
			// if(isset($request['master_dept_id']) && !empty($request['master_dept_id'])){
			// 	$MasterDeptStateID = WmDepartment::where("id",$request['master_dept_id'])->value('gst_state_code_id');
			// }
			if(!empty($BILL_FROM_MRF_ID)){
				$MasterDeptStateID = WmDepartment::where("id",$BILL_FROM_MRF_ID)->value('gst_state_code_id');
			}
			/* ####### MASTER DEPARTMENT GST STATE CODE CHANGES ####### */
			$ClientMasterData 			= WmClientMaster::find($request['destination']);
			if($ClientMasterData){
				$DestinationStateCode 	= $ClientMasterData->gst_state_code;
				/* PRODUCT CONSUMED OF CLIENT - 29 JULY 2019*/
				if(isset($ClientMasterData->product_consumed) && !empty($ClientMasterData->product_consumed)){
					$ProductConsumed 	= explode(",",$ClientMasterData->product_consumed);
				}
				/*END PRODUCT CONSUMED OF CLIENT*/
				if(empty($DestinationStateCode)){
					$getCode = StateMaster::GetGSTCodeByCustomerCity($ClientMasterData->city_id);
					if(isset($getCode->state_code)){
						$DestinationStateCode 	= $getCode->state_code;
					}
				}
			}

			$Dispatch = self::find($DispatchId);
			if($Dispatch){
			/* GET TRANSFER TRANS AS PER TYPE OF TRANSACTION - 23 APRIL 2020 -AXAY SHAH */
				$challan_no = $Dispatch->challan_no;

				if($Dispatch->dispatch_type != $DISPATCH_TYPE_TRN){
					$TRANSFER_TRANS = TransactionMasterCodesMrfWise::GetTrnType($DISPATCH_TYPE_TRN);
					if(empty($TRANSFER_TRANS)){
						return false;
					}
					$GET_CODE 		= TransactionMasterCodesMrfWise::GetLastTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS);
					if($GET_CODE){
						$CODE 			= $GET_CODE->code_value + 1;
						$challan_no 	= $GET_CODE->group_prefix.LeadingZero($CODE);
						$CHALLAN_UPDATE = true;
					}
				}else{
					if(empty($challan_no) && $Dispatch->dispatch_type == $DISPATCH_TYPE_TRN){
						$TRANSFER_TRANS = TransactionMasterCodesMrfWise::GetTrnType($DISPATCH_TYPE_TRN);
						if(empty($TRANSFER_TRANS)){
							return false;
						}
						$GET_CODE 			= TransactionMasterCodesMrfWise::GetLastTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS);
						if($GET_CODE){
							$CODE 			= $GET_CODE->code_value + 1;
							$challan_no 	= $GET_CODE->group_prefix.LeadingZero($CODE);
							$CHALLAN_UPDATE = true;
						}
					}
				}

			/* GET TRANSFER TRANS AS PER TYPE OF TRANSACTION - 23 APRIL 2020 -AXAY SHAH */
				$ShippingState 						= GSTStateCodes::where("state_code",$ShippingStateCode)->value('state_name');
				$priviousChallan 					= $challan_no;
				$ddate 								= $Dispatch_date;
				$total_qty 							= (isset($request['total_qty']) && !empty($request['total_qty'])) ? $request['total_qty'] : 0;
				$Dispatch->dispatchplan_id			= $dispatchPlanId;
				$Dispatch->client_master_id  		= $ClientId;
				$Dispatch->quantity					= $total_qty;
				$Dispatch->vehicle_id				= (isset($request['vehicle_id']) && !empty($request['vehicle_id'])) ? $request['vehicle_id'] : 0;
				$Dispatch->shipping_address			= $ShippingAddress;
				$Dispatch->shipping_address_id		= $ShippingAddressId;
				$Dispatch->shipping_state			= $ShippingState;
				$Dispatch->shipping_state_code		= $ShippingStateCode;
				$Dispatch->shipping_city			= $ShippingCity;
				$Dispatch->shipping_pincode			= $ShippingPinCode;
				$Dispatch->origin					= (isset($request['origin']) && !empty($request['origin'])) ? $request['origin'] : 0;
				$Dispatch->origin_city				= $OriginCity;
				$Dispatch->origin_state_code 		= $OriginStateCode;
				$Dispatch->destination_state_code 	= $DestinationStateCode;
				$Dispatch->dispatch_type			= $DISPATCH_TYPE_TRN;
				$Dispatch->recyclable_type			= (isset($request['recyclable_type'])?$request['recyclable_type']:0);
				$Dispatch->company_id				= Auth()->user()->company_id;
				$Dispatch->destination				= (isset($request['destination']) && !empty($request['destination'])) ? $request['destination'] : 0;
				$Dispatch->destination_city			= $DestinationCity;
				$Dispatch->driver_name				= (isset($request['dr_name'])?$request['dr_name']:'');
				$Dispatch->driver_mob_no			= (isset($request['dr_mobile'])?$request['dr_mobile']:'');
				$Dispatch->master_dept_id			= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ? $DeptId  : 0;
				$Dispatch->from_mrf					= $FromMrf;
				$Dispatch->dispatch_date			= $Dispatch_date;
				$Dispatch->updated_by				= Auth()->user()->adminuserid;
				$Dispatch->invoice_generated		= 0;
				$Dispatch->challan_no 				= $challan_no;
				$Dispatch->type_of_transaction 		= $TYPE_OF_TRANSACTION;
				$Dispatch->nespl 					= $NESPL;
				$Dispatch->bill_of_lading 			= $bill_of_lading;
				$Dispatch->dated 					= $Dated;
				$Dispatch->rent_amt					= $RENT_AMT;
				$Dispatch->discount_amt				= $DISCOUNT_AMT;
				$Dispatch->tare_weight				= $TARE_WEIGHT;
				$Dispatch->gross_weight				= $GROSS_WEIGHT;
				$Dispatch->eway_bill_no				= (isset($request['eway_bill_no']) && !empty($request['eway_bill_no'])?$request['eway_bill_no']:'');
				$Dispatch->master_dept_state_code 	= $MasterDeptStateID;
				$Dispatch->bill_from_mrf_id			= $BILL_FROM_MRF_ID;
				$Dispatch->virtual_target			= $VIRTUAL_TARGET;
				$Dispatch->collection_cycle_term 	= $COL_CYCLE_TERM;
				$Dispatch->show_vendor_name_flag 	= $VENDOR_NAME_FLAG;
				$Dispatch->transporter_po_id 		= $TRANSPORTER_PO_ID;
				$Dispatch->remarks  	  	  	  	= $REMARKS;
				$Dispatch->transport_cost_id 	  	= $TRANSPORT_COST_ID;
				$Dispatch->relationship_manager_id 	= $RELATION_SHIP_ID;
				$TranspoterData = TransporterDetailsMaster::find($TRANSPORTER_PO_ID);
				if($TranspoterData){
					$TRANSPORTER_NAME =  TransporterMaster::where("id",$TranspoterData->transporter_id)->value('name');
				}
				$Dispatch->transporter_name   	= $TRANSPORTER_NAME;
				if($Dispatch->save()){
					$IS_DIRECT_DISPATCH = ($Dispatch->appointment_id > 0) ? 1 : 0;
					if($CHALLAN_UPDATE){

					/* UPDATE CODE IN TRANSACTION MASTER TABLE SINCE- 09 APRIL 2020*/
						TransactionMasterCodesMrfWise::UpdateTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS,$CODE);
					/* UPDATE CODE IN TRANSACTION MASTER TABLE SINCE- 09 APRIL 2020*/
					}
					$product = array();
					$DirectApproveFlag 	= false;
					if(isset($request['sales_product']) && !empty($request['sales_product'])){
						$product 	= json_decode($request['sales_product'],true);
						/* GET EXISTING PRODUCTS */
						$arrExistingDispatchProducts	= array();
						$arrOldProducts					= array();
						$arrNewProducts					= array();
						$arrExistingDispatchItems 		= WmDispatchProduct::where("dispatch_id",$DispatchId)->get();
						if (!empty($arrExistingDispatchItems)) {
							foreach ($arrExistingDispatchItems as $arrExistingDispatchItem) {
								$arrExistingDispatchProducts[$arrExistingDispatchItem->product_id] = $arrExistingDispatchItem;
								array_push($arrOldProducts,$arrExistingDispatchItem->product_id);
							}
						}
						/* GET EXISTING PRODUCTS */

						/** REMOVE ALL PRODUCTS */
						WmDispatchProduct::where("dispatch_id",$DispatchId)->delete();
						/** REMOVE ALL PRODUCTS */
						$ApprovalRequestProduct = array();
						$i = 0;
						$totalProductCnt 	= count($product);
						$totalTrueFlagCnt 	= 0;
						$ORID 				= ($Dispatch->from_mrf == "Y") ? 0 : $Dispatch->origin;
						foreach($product as $value)
						{
							$GET_RATE_DATA = WmProductMaster::GetSalesOrderClientRate(date("Y-m-d"),$value['product_id'],$Dispatch->master_dept_id,$Dispatch->client_master_id,$ORID);
							if(!empty($GET_RATE_DATA)){
								$client_rate 	= _FormatNumberV2($GET_RATE_DATA[0]->rate);
								$is_disable 	= 1;
								$qty 			= $GET_RATE_DATA[0]->qty;
								if($client_rate == $value['price']){
									$DirectApproveFlag 	= true;
									$SALES_ORDER_ID 	= $GET_RATE_DATA[0]->dispatch_plan_id;
									$totalTrueFlagCnt++;
								}else{
									$DirectApproveFlag 	= false;
									$SALES_ORDER_ID 	= 0;
								}
							}else{
								$DirectApproveFlag 		= false;
							}
							############### IF DISABLE FLAG TRUE THEN AUTO APPROVE ##############
							$IS_BAILING 	= (isset($value['is_bailing']) 	&& !empty($value['is_bailing'])) 		? $value['is_bailing'] 			: 0;
							$DESCRIPTION 	= (isset($value['description']) && !empty($value['description'])) 		? $value['description'] : "";
							$BALINIGTYPE 	= (isset($value['bailing_type']) && !empty($value['bailing_type'])) 		? $value['bailing_type'] 		: 0;
							$BALINIG_QTY 	= (isset($value['bail_qty']) && !empty($value['bail_qty'])) 			? $value['bail_qty'] 			: 0;
							$BAILING_ID 	= (isset($value['bailing_master_id']) && !empty($value['bailing_master_id'])) ? $value['bailing_master_id'] : 0;
							$QTY = $value['quantity'];
							########### NEW DEVELOPMENT OF DIRECT DISPATCH REPORT RELETED ##############
							$collectionDetailsId = 0 ;
							foreach($arrExistingDispatchItems as $key => $oldProduct){
								if($oldProduct['product_id'] == $value['product_id']  && $oldProduct['quantity'] == $value['quantity']){
									$collectionDetailsId = $oldProduct['collection_detail_id'];
								}
							}
							$ins_prd['collection_detail_id'] 	= $collectionDetailsId;
							########### NEW DEVELOPMENT OF DIRECT DISPATCH REPORT RELETED ##############
							$ins_prd['dispatch_plan_id']	= 0;
							$ins_prd['dispatch_id']			= $Dispatch->id;
							$ins_prd['product_id']			= $value['product_id'];
							$ins_prd['description']			= $DESCRIPTION;
							$ins_prd['quantity']			= $QTY;
							$ins_prd['price']				= _FormatNumberV2($value['price']);
							$ins_prd['is_bailing']			= $IS_BAILING;
							$ins_prd['bailing_type']		= $BALINIGTYPE;
							$ins_prd['bail_qty']			= $BALINIG_QTY;
							$ins_prd['bailing_master_id'] 	= $BAILING_ID;
							$DispatchProductInsertedID 		= WmDispatchProduct::insertGetId($ins_prd);
							if(!in_array($value['product_id'],$ProductConsumed)){
								array_push($ProductConsumed,$value['product_id']);
							}
							$ApprovalRequestProduct[$i]['id'] 			= $DispatchProductInsertedID;
							$ApprovalRequestProduct[$i]['product_id'] 	= $value['product_id'];
							$ApprovalRequestProduct[$i]['description'] 	= $DESCRIPTION;
							$ApprovalRequestProduct[$i]['quantity'] 	= $QTY;
							$ApprovalRequestProduct[$i]['rate'] 		= _FormatNumberV2($value['price']);
							$ApprovalRequestProduct[$i]['price'] 		= _FormatNumberV2($value['price']);
							$i++;

							/* MANAGE STOCK UPDATES IN PROCESS */
							/* MANAGE STOCK UPDATES IN PROCESS */
							if($deleteStockFlag == 0){
								$outwardCount = OutWardLadger::where("product_id",0)
								->where("production_report_id",0)
								->where("type","D")
								->where("ref_id",$Dispatch->id)
								->count();
								if($outwardCount > 0){
									$OUTWARD_SQL = "INSERT INTO outward_ledger_log_table  (SELECT * FROM outward_ledger WHERE type='D' and ref_id=".$Dispatch->id.")";
									\DB::statement($OUTWARD_SQL);
								}
									OutWardLadger::where("product_id",0)
									->where("production_report_id",0)
									->where("type","D")
									->where("ref_id",$Dispatch->id)
									->delete();
								
								$deleteStockFlag = 1;
							}

							
							array_push($arrNewProducts,$value['product_id']);
							if (isset($arrExistingDispatchProducts[$value['product_id']]))
							{
								############# STOCK OUTWARD ENTRY ISSUE CODDING RESOLVE ###########3
								$INWARDDATA 							= array();
								$OUTWORDDATA 							= array();
								$OUTWORDDATA['sales_product_id'] 		= $value['product_id'];
								$OUTWORDDATA['product_id'] 				= 0;
								$OUTWORDDATA['production_report_id']	= 0;
								$OUTWORDDATA['ref_id']					= $Dispatch->id;
								$OUTWORDDATA['quantity']				= $value['quantity'];
								$OUTWORDDATA['type']					= TYPE_DISPATCH;
								$OUTWORDDATA['product_type']			= PRODUCT_SALES;
								$OUTWORDDATA['direct_dispatch']			= ($Dispatch->appointment_id > 0) ? 1 : 0;
								$OUTWORDDATA['mrf_id']					= $Dispatch->bill_from_mrf_id;
								$OUTWORDDATA['company_id']				= $Dispatch->company_id;
								$OUTWORDDATA['outward_date']			= date("Y-m-d",strtotime($Dispatch->dispatch_date));
								$OUTWORDDATA['created_by']				= Auth()->user()->adminuserid;
								$OUTWORDDATA['updated_by']				= Auth()->user()->adminuserid;
								OutWardLadger::AutoAddOutward($OUTWORDDATA);
								############# STOCK OUTWARD ENTRY ISSUE CODDING RESOLVE ###########3

								$OLD_QTY 	= $arrExistingDispatchProducts[$value['product_id']]['quantity'];
								$NEW_QTY	= $value['quantity'];
								$STOCK_TYPE	= "";
								if ($OLD_QTY > $NEW_QTY) {
									$STOCK_TYPE = "I";
									$STOCK_QTY	= ($OLD_QTY - $NEW_QTY);
								} else if ($OLD_QTY < $NEW_QTY) {
									$STOCK_TYPE = "O";
									$STOCK_QTY	= ($NEW_QTY - $OLD_QTY);
								}
							} else {
								$OUTWORDDATA 						= array();
								$OUTWORDDATA['sales_product_id'] 	= $value['product_id'];
								$OUTWORDDATA['product_id'] 			= 0;
								$OUTWORDDATA['production_report_id']= 0;
								$OUTWORDDATA['ref_id']				= $Dispatch->id;
								$OUTWORDDATA['quantity']			= $value['quantity'];
								$OUTWORDDATA['type']				= TYPE_DISPATCH;
								$OUTWORDDATA['product_type']		= PRODUCT_SALES;
								$OUTWORDDATA['direct_dispatch']		= ($Dispatch->appointment_id > 0) ? 1 : 0;
								$OUTWORDDATA['mrf_id']				= $Dispatch->bill_from_mrf_id;
								$OUTWORDDATA['company_id']			= $Dispatch->company_id;
								$OUTWORDDATA['outward_date']		= date("Y-m-d",strtotime($Dispatch->dispatch_date));
								$OUTWORDDATA['created_by']			= Auth()->user()->adminuserid;
								$OUTWORDDATA['updated_by']			= Auth()->user()->adminuserid;
								OutWardLadger::AutoAddOutward($OUTWORDDATA);
							}
							/* MANAGE STOCK UPDATES IN PROCESS */
						}
						$SALES_ORDER_ID 	= ($totalProductCnt > 0 && $totalProductCnt == $totalTrueFlagCnt) ? $SALES_ORDER_ID : 0;
						$DirectApproveFlag 	= ($totalProductCnt > 0 && $totalProductCnt == $totalTrueFlagCnt) ? $DirectApproveFlag : false;
						/** INWARD PRODUCTS WHICH ARE REMOVED FROM DISPATCH ITEMS */
						$arrProductsRemovedFromDispatch = array_diff($arrOldProducts,$arrNewProducts);
						if (!empty($arrProductsRemovedFromDispatch))
						{
							foreach ($arrProductsRemovedFromDispatch as $PRODUCT_ID)
							{
								$STOCK_QTY							= $arrExistingDispatchProducts[$PRODUCT_ID]['quantity'];
								$INWARDDATA 						= array();
								$INWARDDATA['purchase_product_id'] 	= 0;
								$INWARDDATA['product_id'] 			= $PRODUCT_ID;
								$INWARDDATA['production_report_id']	= 0;
								$INWARDDATA['ref_id']				= $Dispatch->id;
								$INWARDDATA['quantity']				= $STOCK_QTY;
								$INWARDDATA['type']					= TYPE_DISPATCH;
								$INWARDDATA['product_type']			= PRODUCT_SALES;
								$INWARDDATA['batch_id']				= 0;
								$INWARDDATA['direct_dispatch']		= ($Dispatch->appointment_id > 0) ? 1 : 0;
								$INWARDDATA['mrf_id']				= $Dispatch->bill_from_mrf_id;
								$INWARDDATA['company_id']			= $Dispatch->company_id;
								$INWARDDATA['inward_date']			= date("Y-m-d");
								$INWARDDATA['remarks']				= "Sales Product was earlier in Dispatch #".$Dispatch->id.", later on removed by ".Auth()->user()->firstname." ".Auth()->user()->lastname.".";
								$INWARDDATA['created_by']			= Auth()->user()->adminuserid;
								$INWARDDATA['updated_by']			= Auth()->user()->adminuserid;
								if(date("Y-m-d",strtotime($Dispatch->dispatch_date)) != date("Y-m-d")){
									ProductInwardLadger::AutoAddInward($INWARDDATA);
								}
							}
						}
						/** INWARD PRODUCTS WHICH ARE REMOVED FROM DISPATCH ITEMS */
						/*Product Consume update in client master*/
						if(!empty($ProductConsumed)){
							$ProductConsumedString = implode(",",$ProductConsumed);
							WmClientMaster::where("id",$request['destination'])->update(["product_consumed"=>$ProductConsumedString]);
						}
						/*End code Product Consume update in client master*/

						/*ADD SHIPING ADDRESS OF CLIENT*/
						// $AddAddress = ShippingAddressMaster::AddShippingAddress($ClientId,$ShippingAddress,$ShippingCity,$ShippingState,$ShippingStateCode,$ShippingPinCode,$consignee_name);
						// if($AddAddress > 0){
						// 	self::where("id",$Dispatch->id)->update(["shipping_address_id" => $AddAddress]);
						// }
						/*END SHIPPING ADDRESS CODE*/
						/*UPDATE STATUS FOR APPROVAL RATE */
						$company 		= CompanyMaster::find(Auth()->user()->company);
						$FromEmail 		= array(
							"Name"	=>	env("MAIL_FROM_NAME"),
							"Email"	=>	env("MAIL_FROM_ADDRESS"),
						);
						if($company){
							$FromEmail 	= array (
								"Name" 	=> $company->company_name,
								"Email" => $company->company_email
							);
						}
						// $GetApprovalEmail 	= self::GetApprovalRights(FIRST_LEVEL_APPROVAL_RIGHTS);
						$DispatchData 	= WmDispatchProduct::GetProductByDispatchId($Dispatch->id);
						$Subject 		= "Dispatch Product Rate Approval";
						$Body 			= "Hello,<br>";
						$Body 			.= "Here are Dispatch Product Rate Added By ".Auth()->user()->firstname." ".Auth()->user()->lastname.".<br /><br />";
						$Body .= " Challan No is : <b>".$challan_no."</b>";
						/*IF MASTER DEPT. CITY IS INDOR THEN NO NEED TO SEND EMAIL AND SMS TO RK - 09-MARCH-2020*/
						$DeptLocId 			= WmDepartment::where("id",$Dispatch->master_dept_id)->first();
						if($DeptLocId){
							if(!empty($DeptLocId->mobile)){
								$MOBILE 	=  explode(",",$DeptLocId->mobile);
							}
							if(!empty($DeptLocId->rate_approval_email)){
								$GetApprovalEmail 	=  explode(",",$DeptLocId->rate_approval_email);
							}
						}
						// if(!empty($GetApprovalEmail)){
						// 	self::SendDispatchRateUpdateEmail($DispatchData,$FromEmail,$GetApprovalEmail,$Subject,$Body);
						// }
						// /* SEND SMS TO DEFINE MOBILE NO*/
						// if(!empty($MOBILE)){
						// 	foreach($MOBILE AS $RAW){
						// 		$message = "New Dispatch added for approval by ".Auth()->user()->firstname." ".Auth()->user()->lastname." with Challan no".$challan_no;
						// 		\App\Classes\SendSMS::sendMessage($RAW,$message);
						// 	}
						// }
						/* SEND SMS TO DEFINE MOBILE NO*/
					}
					if(isset($request['dispatch_plan_id']) && !empty($request['dispatch_plan_id']))
					{
						$dispatchplan_id_str = rtrim(implode(',',$request['dispatch_plan_id']),',');
						self::updateDispatchPlanStatus(1,$dispatchplan_id_str);
					}

					if(isset($request['weight_count']) && !empty($request['weight_count'])){
						WmDispatchMediaMaster::where("dispatch_id",$DispatchId)->delete();
						for($i = 0; $i < $request['weight_count']; $i++){
							$companyId = Auth()->user()->company_id;
							$path 				= PATH_COMPANY."/".PATH_DISPATCH;
							$destinationPath 	= public_path('/').$path;

							if(!is_dir($destinationPath)) {
								mkdir($destinationPath,0777,true);
							}

							$image 		= $request["scal_photo_".$i];
							$name 		= "scal_".$i.time().'.'.$image->getClientOriginalExtension();

							$image->move($destinationPath, $name);
							$media = WmDispatchMediaMaster::AddDispatchMedia($DispatchId,$name,$name,$path,PARA_WAYBRIDGE);
						}
					}




					/*BECAUSE OF AUTO GENERATED CHALLAN NOW ONWARD THEIR IS NO USE OF BELOW CODE- 13 APRIL,2020*/

					if($challan_no != $priviousChallan){
						WmDispatch::where("id",$DispatchId)->update(["challan_no"=>$request['challan_no'],"challan_date"=>date("Y-m-d H:i:s")]);
						$data 	= WmDispatch::GetById($DispatchId);

						if($data){
							$array = array(
								"data"=> $data
							);
							$partialPath = PATH_CHALLAN;
							if(!is_dir(public_path(PATH_IMAGE.'/').$partialPath)) {
								mkdir(public_path(PATH_IMAGE.'/').$partialPath,0777,true);
							}
							$FILENAME   = "challan_".$Dispatch->id.".pdf";
							$PDF        = PDF::loadView('email-template.challan',$array);
							$PDF->setPaper("letter","A4");
							$PDF->save(public_path("/").PATH_IMAGE."/".PATH_CHALLAN."/".$FILENAME,true);
							$filePath   = public_path("/").PATH_IMAGE."/".PATH_CHALLAN."/".$FILENAME;
							$MediaId = MediaMaster::AddMedia($FILENAME,$FILENAME,$partialPath,$Dispatch->company_id);
							if($MediaId > 0){
								self::where("id",$DispatchId)->update(["challan_media_id"=>$MediaId]);
							}
						}
					}

					/*IF ENTRY FROM JOBWORK THEN UPDATE JOBWORK TABLE DATA OF THAT RELETED ID - 12 MARCH 2020*/
					if($SALES_ORDER_ID > 0) {
						$ApprovedByID 	=  WmDispatchPlan::where("id",$Dispatch->dispatchplan_id)->value("approved_by");
						$ApprovedByID 	= (!empty($ApprovedByID)) ? $ApprovedByID : Auth()->user()->adminuserid;
						$ApprovalRequest = new \Illuminate\Http\Request();
						$ApprovalRequest->request->add([
							'approval_status' 		=> 1,
							"challan_no" 			=> $challan_no,
							"dispatch_id" 			=> $DispatchId,
							"eway_bill_no" 			=> $Dispatch->eway_bill_no,
							"type_of_transaction" 	=> $TYPE_OF_TRANSACTION,
							"dispatchplan_id" 		=> 0,
							"product" 				=> $ApprovalRequestProduct,
							"approved_by" 			=> $ApprovedByID
						]);
						$ApprovalRequest = $ApprovalRequest->request->all();
						self::DispatchRateApproval((object)$ApprovalRequest);
					}
				}
			}


			/*NOTE : FOR NOW IT IS NOT IN PROCESS SO WHEN IT APPLIED JUST UNCOMMENT THE CODE - 03 JUNE,2019*/
			/*
			$batchIdArr = self::segregationQtyUpdate($product,SALES_BATCH_TYPE,$batchId='', $Dispatch->id, $request['master_dept_id'], date("Y-m-d H:i:s"), Auth()->user()->adminuserid, $request['vehicle_id']);
			*/
			return $Dispatch;
		}catch(\Exception $e){
			\Log::info($e->getMessage()." ".$e->getLine()." ".$e->getFile());
			dd($e);
		}

	}

	/*
	Use 	: SendDuplicateCollectionEmail
	Author 	: Axay Shah
	Date 	: 29 May,2019
	*/
	public static function SendDispatchRateUpdateEmail($DispatchData,$FromEmail,$ToEmail,$Subject,$Body = "")
	{
		$Attachments    = array();
		$sendEmail      = Mail::send("email-template.dispatchProductRateApproval",array("DispatchData"=>$DispatchData,"HeaderTitle"=>$Subject,"Body"=>$Body), function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
			$message->from($FromEmail['Email'], $FromEmail['Name']);
			$message->to($ToEmail);
			$message->subject($Subject);
		});
	}

	/*
	Use 	: Add Dispatch from mobile
	Author 	: Axay Shah
	Date 	: 29 May,2019
	*/
	public static function InsertDispatchMobile($request)
	{
		try{
			\DB::beginTransaction();
			$BatchId 		= 0;
			$dispatchPlanId 	= '';
			$OriginCity 		= 0;
			$DestinationCity 	= 0;
			$MRFId 			= 0;
			$origin 	 	= 0;
			$destination 		= 0;
			$OriginStateCode	= 0;
			$DestinationStateCode 	= 0;
			$CODE 			= 0;
			$challan_no		= 0;
			$challan_flag 		= false;
			$BASE_LOCATION_ID 	= Auth()->user()->base_location;
			$vehicle_id 		= (isset($request['vehicle_id']) && !empty($request['vehicle_id'])) ? $request['vehicle_id'] :  0;
			// $challan_no 		= (isset($request['challan_no']) && !empty($request['challan_no'])) ? $request['challan_no'] :  0;
			$total_qty 		= (isset($request['total_qty']) && !empty($request['total_qty'])) ? $request['total_qty'] :  0;
			$dispatch_date 		= (isset($request['dispatch_date']) && !empty($request['dispatch_date'])) ? date('Y-m-d',strtotime($request['dispatch_date'])) :  date("Y-m-d");
			$RENT_AMT 		= (isset($request['rent_amount']) && !empty($request['rent_amount'])) ? $request['rent_amount'] : 0;
			$DISCOUNT_AMT 		= (isset($request['discount_amount']) && !empty($request['discount_amount'])) ? $request['discount_amount'] : 0;
			$TYPE_OF_TRANSACTION 	= (isset($request['type_of_transaction']) && !empty($request['type_of_transaction'])) ? $request['type_of_transaction'] : "";
			$TARE_WEIGHT 		= (isset($request['tare_weight']) && !empty($request['tare_weight'])) ? $request['tare_weight'] : 0;
			$GROSS_WEIGHT 		= (isset($request['gross_weight']) && !empty($request['gross_weight'])) ? $request['gross_weight'] : 0;
			$DeptId 		= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ? $request['master_dept_id'] : 0;
			$BILL_FROM_MRF_ID 	= (isset($request['bill_from_mrf_id']) && !empty($request['bill_from_mrf_id'])) ? $request['bill_from_mrf_id'] : $DeptId;
			$TRANSPORTER_NAME 	= (isset($request['transporter_name']) && !empty($request['transporter_name'])) ? $request['transporter_name'] : "";
			$VEHICLE_IN_OUT_ID 	= (isset($request['vehicle_in_out_id']) && !empty($request['vehicle_in_out_id'])) ? $request['vehicle_in_out_id'] : 0;
			$DISPATCH_TYPE_TRN 	= (isset($request['dispatch_type']) && !empty($request['dispatch_type'])) ? $request['dispatch_type'] : 0;
			$VIRTUAL_TARGET 		= (isset($request['virtual_target']) && !empty($request['virtual_target'])) ? $request['virtual_target'] : 0;
			$RELATION_SHIP_ID = (isset($request['relationship_manager_id']) && !empty($request['relationship_manager_id'])) ? $request['relationship_manager_id'] : 0;
			/* GENERATE AUTO GENERATED CHALLAN NO FROM NOW ONWORD - */
			$TRANSFER_TRANS  	= TransactionMasterCodesMrfWise::GetTrnType($TYPE_OF_TRANSACTION);
			if(!empty($TRANSFER_TRANS)){
				$GET_CODE 			= TransactionMasterCodesMrfWise::GetLastTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS);
				if($GET_CODE){
					$CODE 			= 	$GET_CODE->code_value + 1;
					$challan_no 	=   $GET_CODE->group_prefix.LeadingZero($CODE);
					$challan_flag 	= 	true;
				}
			}
			/* END AUTO GENERATED CHALLAN NO*/


			$appointment 		= Appoinment::find($request['appointment_id']);
			$DispatchId 		= 0;
			if($appointment){
				$collectionData				= AppointmentCollection::where("appointment_id",$appointment->appointment_id)->first();
				if($collectionData){
					 $collectionId 			= $collectionData->collection_id;
					 $collectionBy 			= $collectionData->collection_by;
				}

				$request['collection_by'] 	= $collectionBy;
				$request['collection_id'] 	= $collectionId;
				$Department 				= WmDepartment::where("base_location_id",Auth()->user()->base_location)->where("status",1)->where("is_virtual",1)->first();

				if($Department){
					$MRFId 	= $Department->id;
				}

				/*Getting state code of customer and client For GST OR IGST */

				if(isset($appointment->customer_id) && !empty($appointment->customer_id)){
					$origin 	= $appointment->customer_id;
					$OriginCity = CustomerMaster::where("customer_id",$origin)->value('city');
					$OriginGSTStateCodeData = StateMaster::GetGSTCodeByCustomerCity($OriginCity);
					if(isset($OriginGSTStateCodeData->state_code)){
						$OriginStateCode 	= $OriginGSTStateCodeData->state_code;
					}
				}
				if(isset($appointment->client_id) && !empty($appointment->client_id)){
					$destination 		= $appointment->client_id;
					$DestinationCity 	= WmClientMaster::where("id",$destination)->value('city_id');
					$ClientMasterData			= WmClientMaster::find($destination);
					if($ClientMasterData){
						$DestinationStateCode 	= $ClientMasterData->gst_state_code;
						if(empty($DestinationStateCode)){
							$getCode = StateMaster::GetGSTCodeByCustomerCity($ClientMasterData->city_id);
							if(isset($getCode->state_code)){
								$DestinationStateCode 	= $getCode->state_code;
							}
						}
					}
				}
				/* ####### MASTER DEPARTMENT GST STATE CODE CHANGES ####### */
				$MasterDeptStateID = 0;
				// if(isset($request['master_dept_id']) && !empty($request['master_dept_id'])){
				// 	$MasterDeptStateID = WmDepartment::where("id",$request['master_dept_id'])->value('gst_state_code_id');
				// }
				if(isset($BILL_FROM_MRF_ID) && !empty($BILL_FROM_MRF_ID)){
					$MasterDeptStateID = WmDepartment::where("id",$BILL_FROM_MRF_ID)->value('gst_state_code_id');
				}
				/* ####### MASTER DEPARTMENT GST STATE CODE CHANGES ####### */
				/* INSERT DISPATCH */
					$BatchIdArr = AppointmentCollection::DirectDispatchBatchForUpdatedMobile($request);
					if(!empty($BatchIdArr)){
						$BatchId =  (is_array($BatchIdArr)) ? implode(",",$BatchIdArr) : $BatchIdArr;
					}
					if(isset($request['cust_waybridge_slip_count']) && $request['cust_waybridge_slip_count'] > 0){
						$path = PATH_COMPANY."/".Auth()->user()->company_id."/".DIRECT_DISPATCH_IMG;
						/* IMAGE UPLOAD */
						for($i=0;$i < $request['cust_waybridge_slip_count'];$i++){
							if(!is_dir(public_path(PATH_IMAGE.'/').$path)) {
								mkdir(public_path(PATH_IMAGE.'/').$path,0777,true);
							}
							$image = $request["cust_waybridge_slip_".$i];
							$input['imagename'] = "cust_waybridge_slip_".$i."_".time().'.'.$image->getClientOriginalExtension();
							$destinationPath = public_path(PATH_IMAGE."/".$path);
							$image->move($destinationPath, $input['imagename']);
							$MediaMaster 	= MediaMaster::AddMedia($input['imagename'],$input['imagename'],$path,Auth()->user()->company_id);
							$MEDIA 			= WmBatchMediaMaster::InsertBatchMedia($BatchId,$MediaMaster,"G",1,$DispatchId,Auth()->user()->adminuserid);
					}
					/* END IMAGE UPLOAD */
					/* INSERT DISPATCH */

					$Dispatch 							= new WmDispatch();
					$total_qty 							= $total_qty;
					$Dispatch->dispatchplan_id			= $dispatchPlanId;
					$Dispatch->client_master_id  		= $appointment->client_id;
					$Dispatch->quantity					= $total_qty;
					$Dispatch->vehicle_id				= $vehicle_id;
					$Dispatch->origin					= $origin;
					$Dispatch->origin_city				= $OriginCity;
					$Dispatch->company_id				= Auth()->user()->company_id;
					$Dispatch->destination				= $destination;
					$Dispatch->destination_city			= $DestinationCity;
					$Dispatch->origin_state_code 		= $OriginStateCode;
					$Dispatch->destination_state_code 	= $DestinationStateCode;
					$Dispatch->direct_dispatch			= 1;
					$Dispatch->driver_name				= (isset($request['dr_name'])?$request['dr_name']:'');
					$Dispatch->driver_mob_no			= (isset($request['dr_mobile'])?$request['dr_mobile']:'');
					$Dispatch->master_dept_id			= $MRFId;
					$Dispatch->dispatch_date			= $dispatch_date;
					$Dispatch->created_by				= Auth()->user()->adminuserid;
					$Dispatch->created_at				= date('Y-m-d H:i:s');
					$Dispatch->invoice_generated		= 0;
					$Dispatch->rent_amt					= $RENT_AMT;
					$Dispatch->discount_amt				= $DISCOUNT_AMT;
					// $Dispatch->challan_no 				= $challan_no;
					$Dispatch->type_of_transaction 		= $TYPE_OF_TRANSACTION;
					$Dispatch->dispatch_type 			= RECYCLEBLE_TYPE;
					$Dispatch->tare_weight				= $TARE_WEIGHT;
					$Dispatch->gross_weight				= $GROSS_WEIGHT;
					$Dispatch->master_dept_state_code	= $MasterDeptStateID;
					$Dispatch->bill_from_mrf_id			= $BILL_FROM_MRF_ID;
					$Dispatch->transporter_name			= $TRANSPORTER_NAME;
					$Dispatch->virtual_target			= $VIRTUAL_TARGET;
					$Dispatch->relationship_manager_id 	= $RELATION_SHIP_ID;
					if($Dispatch->save()){
						/* UPDATE CODE IN TRANSACTION MASTER TABLE SINCE- 09 APRIL 2020*/
						if($challan_flag){
							// TransactionMasterCodesMrfWise::UpdateTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS,$CODE);
						}
						/* start of save conidtion*/
						$DispatchId 				= $Dispatch->id;
						WaybridgeModuleVehicleInOut::UpdateVehicleInOutFlag($VEHICLE_IN_OUT_ID,WAYBRIDGE_MODULE_DISPATCH,$DispatchId,1);
						$product 					= array();
						if(isset($request['sales_product']) && !empty($request['sales_product'])){
							$product 				= json_decode($request['sales_product'],true);
							foreach($product as $value)
							{
								$salesProduct 					= $value['sales_product_id'];
								$PurchaseProduct 				= $value['product_id'];
								$ins_prd['dispatch_plan_id']	= 0;
								$ins_prd['dispatch_id']			= $Dispatch->id;
								$ins_prd['product_id']			= $salesProduct;
								$ins_prd['quantity']			= $value['quantity'];
								$PRODUCT_INSERT = WmDispatchProduct::create($ins_prd);
								/* MANAGE STOCK UPDATES IN PROCESS */
								$OUTWORDDATA 						= array();
								$OUTWORDDATA['product_id'] 			= $salesProduct;
								$OUTWORDDATA['production_report_id']= 0;
								$OUTWORDDATA['ref_id']				= $DispatchId;
								$OUTWORDDATA['quantity']			= $value['quantity'];
								$OUTWORDDATA['type']				= TYPE_DISPATCH;
								$OUTWORDDATA['product_type']		= PRODUCT_SALES;
								$OUTWORDDATA['mrf_id']				= $Dispatch->master_dept_id;
								$OUTWORDDATA['company_id']			= $Dispatch->company_id;
								$OUTWORDDATA['outward_date']		= date("Y-m-d",strtotime($Dispatch->dispatch_date));
								$OUTWORDDATA['created_by']			= Auth()->user()->adminuserid;
								$OUTWORDDATA['updated_by']			= Auth()->user()->adminuserid;
								ProductInwardLadger::AutoAddInward($OUTWORDDATA);

								$OUTWORDDATA 						= array();
								$OUTWORDDATA['sales_product_id'] 	= $salesProduct;
								$OUTWORDDATA['product_id'] 			= $PurchaseProduct;
								$OUTWORDDATA['product_id'] 			= 0;
								$OUTWORDDATA['production_report_id']= 0;
								$OUTWORDDATA['ref_id']				= $DispatchId;
								$OUTWORDDATA['quantity']			= $value['quantity'];
								$OUTWORDDATA['type']				= TYPE_DISPATCH;
								$OUTWORDDATA['product_type']		= PRODUCT_SALES;
								$OUTWORDDATA['mrf_id']				= $Dispatch->master_dept_id;
								$OUTWORDDATA['company_id']			= $Dispatch->company_id;
								$OUTWORDDATA['outward_date']		= date("Y-m-d",strtotime($Dispatch->dispatch_date));
								$OUTWORDDATA['created_by']			= Auth()->user()->adminuserid;
								$OUTWORDDATA['updated_by']			= Auth()->user()->adminuserid;
								OutWardLadger::AutoAddOutward($OUTWORDDATA);
								/* MANAGE STOCK UPDATES IN PROCESS */
							}
						}

						if(isset($request['dispatch_plan_id']) && !empty($request['dispatch_plan_id']))
						{
							$dispatchplan_id_str = rtrim(implode(',',$request['dispatch_plan_id']),',');
							self::updateDispatchPlanStatus(1,$dispatchplan_id_str);
						}
						/* end of save*/
					}
				}
			}
			$data['dispatch_id'] 	= $DispatchId;
			$data['batch_id'] 		= $BatchId;
			$data['collection_id'] 	= $collectionId;
			\Log::info("*******************************RESPONSE******************************".PRINT_R($data,true));
			\DB::commit();
			return $data;
		}catch(\Exception $e){
			\DB::rollback();
			\Log::info("*******************************RESPONSE******************************".$e->getMessage()." ".$e->getLine()." ".$e->getFile());
			dd($e);
		}
	}
	/*
	Use 	: Dispatch Rate approval
	Author 	: Axay Shah
	Date 	: 26 June,2019
	*/
	public static function DispatchRateApproval($request,$fromMobile = false)
	{
		$ApprovedBy 		= (isset($request->approved_by) && !empty($request->approved_by)) ? $request->approved_by : Auth()->user()->adminuserid;
		$WMProductPrice		= new WmProductClientPriceMaster();
		$CODE 				= 0;
		$ARR_GST_PERCENT 	= array();
		$MAX_GST_PERCENT 	= 0;
		$BASE_LOCATION_ID 	= Auth()->user()->base_location;
		$CHALLAN_UPDATE 	= false;
		$DispatchId 		= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : 0;
		$Status 			= (isset($request->approval_status) && !empty($request->approval_status)) ? $request->approval_status : 0;
		$Product 			= (isset($request->product) && !empty($request->product)) ? $request->product : "";
		$eway_bill_no 		= (isset($request->eway_bill_no) && !empty($request->eway_bill_no)) ? $request->eway_bill_no : "";
		$challan_no 		= (isset($request->challan_no) && !empty($request->challan_no)) ? $request->challan_no : "";
		$DispatchPlanID 	= (isset($request->dispatchplan_id) && !empty($request->dispatchplan_id)) ? $request->dispatchplan_id : 0;
		$TYPE_OF_TRANSACTION= (isset($request->type_of_transaction) && !empty($request->type_of_transaction)) ? $request->type_of_transaction : 0;
		$RATE_CHANGE_REMARK = (isset($request->rate_change_remarks)?$request->rate_change_remarks:"");
		$REMARK_ID 			= (isset($request->remark_id)?$request->remark_id:0);
		$data 				= array();
		$res 				= array();
		// \Log::info("******************APPROVAL***************".print_r($request->all(),true));
		if(!empty($DispatchId) && !empty($Status))
		{
			$DispatchData 		= self::find($DispatchId);
			$DISPATCH_TYPE_TRN  = $DispatchData->dispatch_type;
			$isFromSameState 	= false;
			if($DispatchData)
			{
				$IS_DIRECT_DISPATCH = ($DispatchData->appointment_id > 0) ? 1 : 0;
				$BILL_FROM_MRF_ID 	= ($DispatchData->bill_from_mrf_id > 0) ? $DispatchData->bill_from_mrf_id : 0;
				$DISPATCH_PO_ID 	= ($DispatchData->transporter_po_id > 0) ? $DispatchData->transporter_po_id : 0;
				TransporterDetailsMaster::where("id",$DISPATCH_PO_ID)->update(["dispatch_id"=>$DispatchId,"po_date"=>date("Y-m-d H:i:s")]);


				$challan_no 	 = $DispatchData->challan_no;
				if(isset($DispatchData->master_dept_state_code) && $DispatchData->master_dept_state_code > 0){
					$isFromSameState = ($DispatchData->master_dept_state_code == $DispatchData->destination_state_code) ? true : false;
				}else{
					$isFromSameState = ($DispatchData->origin_state_code == $DispatchData->destination_state_code) ? true : false;
				}
				$TRANSFER_TRANS  = TransactionMasterCodesMrfWise::GetTrnType($DISPATCH_TYPE_TRN);
				if(empty($TRANSFER_TRANS)) {
					return false;
				}
				/* GET TRANSFER TRANS AS PER TYPE OF TRANSACTION - 23 APRIL 2020 -AXAY SHAH */
				if($DispatchData->dispatch_type != $DISPATCH_TYPE_TRN) {
					$GET_CODE 		= TransactionMasterCodesMrfWise::GetLastTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS);
					if($GET_CODE) {
						$CODE 			= $GET_CODE->code_value + 1;
						$challan_no 	= $GET_CODE->group_prefix.LeadingZero($CODE);
						$CHALLAN_UPDATE = true;
					}
				} elseif(empty($DispatchData->challan_no)) {
					/* GENERATE AUTO GENERATED CHALLAN NO FROM NOW ONWORD - */
					$GET_CODE 		= TransactionMasterCodesMrfWise::GetLastTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS);
					if($GET_CODE) {
						$CODE 			= $GET_CODE->code_value + 1;
						$challan_no 	= $GET_CODE->group_prefix.LeadingZero($CODE);
						$CHALLAN_UPDATE = true;
					}
				}

				if($CHALLAN_UPDATE) {
					/* UPDATE CODE IN TRANSACTION MASTER TABLE SINCE- 09 APRIL 2020*/
					TransactionMasterCodesMrfWise::UpdateTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS,$CODE);
					/* UPDATE CODE IN TRANSACTION MASTER TABLE SINCE- 09 APRIL 2020*/
				}
				/* GET TRANSFER TRANS AS PER TYPE OF TRANSACTION - 23 APRIL 2020 -AXAY SHAH */

				/** UPDATE PRODUCT RATE */
				if(!empty($Product))
				{
					########### NEW DEVELOPMENT OF DIRECT DISPATCH REPORT RELETED ##############
					$arrExistingDispatchItems = WmDispatchProduct::where("dispatch_id",$DispatchId)->get();
					########### NEW DEVELOPMENT OF DIRECT DISPATCH REPORT RELETED ##############
					// WmDispatchProduct::where("dispatch_id",$DispatchId)->delete();
					if($fromMobile) {
						$Product = json_decode($Product,true);
					}
					$FINAL_AMT = 0;
					foreach($Product as $pro)
					{
						########### NEW DEVELOPMENT OF DIRECT DISPATCH REPORT RELETED ##############
						$collectionDetailsId = 0 ;
						foreach($arrExistingDispatchItems as $key => $oldProduct){
							if($oldProduct['product_id'] == $pro['product_id']  && $oldProduct['quantity'] == $pro['quantity']){
								$collectionDetailsId = $oldProduct['collection_detail_id'];
							}
						}
						########### NEW DEVELOPMENT OF DIRECT DISPATCH REPORT RELETED ##############
						$dispatch_plan_id 		= 0 ;
						$DIS_PRODUCT_ID 	 	= (isset($pro['id']) && !empty($pro['id'])) ? $pro['id'] : 0;
						$DISPATCH_PRODUCT_DATA 	= WmDispatchProduct::find($DIS_PRODUCT_ID);
						if($DISPATCH_PRODUCT_DATA){
							$collectionDetailsId 	= $DISPATCH_PRODUCT_DATA->collection_detail_id;
							$dispatch_plan_id 		= $DISPATCH_PRODUCT_DATA->dispatch_plan_id;
						}
						########### NEW DEVELOPMENT OF DIRECT DISPATCH REPORT RELETED ##############
						########### NEW DEVELOPMENT OF DIRECT DISPATCH REPORT RELETED ##############
						$GST_ARR 						= WmProductMaster::calculateProductGST($pro['product_id'],$pro['quantity'],$pro['rate'],$isFromSameState);
						$Max_Sales_Rate 				= $WMProductPrice->getMaxProductPrice($pro['product_id']);
						$ins_prd 						= array();
						$ins_prd['collection_detail_id'] = $collectionDetailsId;
						$ins_prd['dispatch_plan_id']	= $dispatch_plan_id;
						$ins_prd['dispatch_id']			= $DispatchId;
						$ins_prd['product_id']			= $pro['product_id'];
						$ins_prd['description']			= $pro['description'];
						$ins_prd['quantity']			= $pro['quantity'];
						$ins_prd['price']				= _FormatNumberV2($pro['rate']);
						$ins_prd['cgst_rate'] 			= $GST_ARR['CGST_RATE'];
						$ins_prd['sgst_rate'] 			= $GST_ARR['SGST_RATE'];
						$ins_prd['igst_rate'] 			= $GST_ARR['IGST_RATE'];
						$ins_prd['gst_amount'] 			= $GST_ARR['TOTAL_GST_AMT'];
						$ins_prd['net_amount'] 			= $GST_ARR['TOTAL_NET_AMT'];
						$ins_prd['gross_amount'] 	    = $GST_ARR['TOTAL_GR_AMT'];
						array_push($ARR_GST_PERCENT,$GST_ARR['SUM_GST_PERCENT']);
						if($DIS_PRODUCT_ID > 0){
							WmDispatchProduct::where("id",$DIS_PRODUCT_ID)->update($ins_prd);
						}else{
							WmDispatchProduct::insert($ins_prd);
						}
						############ AVG PRICE FOR DIRECT DISPATCH AND NORMAL DISPATCH #################
						### NEW CHANGES REGARDING COGS FOR DIRECT DISPATCH AND DISPATCH - 17 MARCH 2022 ###
						$collectionData 	= AppointmentCollectionDetail::where("collection_detail_id",$collectionDetailsId)->first();
						$collection_id 		= ($collectionData) ? $collectionData->collection_id : 0;
						$collectionPrice 	= ($collectionData) ? $collectionData->product_customer_price : 0;
						$purchaseProductId 	= ($collectionData) ? $collectionData->product_id : 0;
						$AVG_PRICE 			= 	StockLadger::where("product_type",PRODUCT_SALES)
												->where("mrf_id",$BILL_FROM_MRF_ID)
												->where("stock_date",date("Y-m-d"))
												->where("product_id",$pro['product_id'])
												->value("avg_price");
						$AVG_PRICE 			= (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) :0;
						WmDispatchSalesProductAvgPrice::insert([
							"dispatch_id" 			=> $DispatchId,
							"dispatch_product_id" 	=> $DIS_PRODUCT_ID,
							"collection_id" 		=> $collection_id,
							"direct_dispatch" 		=> $IS_DIRECT_DISPATCH,
							"collection_detail_id" 	=> $collectionDetailsId,
							"purchase_product_id" 	=> $purchaseProductId,
							"sales_product_id" 		=> $pro['product_id'],
							"price" 				=> $collectionPrice,
							"avg_price" 			=> $AVG_PRICE,
							"created_at" 			=> date("Y-m-d"),
							"updated_at" 			=> date("Y-m-d"),

						]);
						#####NEW CHANGES REGARDING COGS FOR DIRECT DISPATCH AND DISPATCH-17 MARCH 2022 ###

						$FINAL_AMT += $GST_ARR['TOTAL_NET_AMT'];
						/*INSERT DISPACTH INWARD RECORD IF DISPATCH GOT REJECTED */
						if($Status == DISPATCH_REJECTED){
							$INWARDDATA['purchase_product_id'] 	= 0;
							$INWARDDATA['product_id'] 			= $pro['product_id'];
							$INWARDDATA['production_report_id']	= 0;
							$INWARDDATA['ref_id']				= $DispatchId;
							$INWARDDATA['quantity']				= $pro['quantity'];
							$INWARDDATA['type']					= TYPE_DISPATCH;
							$INWARDDATA['product_type']			= PRODUCT_SALES;
							$INWARDDATA['batch_id']				= 0;
							$INWARDDATA['avg_price']			= $pro['rate'];
							$INWARDDATA['direct_dispatch']		= ($DispatchData->appointment_id > 0) ? 1 : 0;
							$INWARDDATA['mrf_id']				= $DispatchData->bill_from_mrf_id;
							$INWARDDATA['company_id']			= $DispatchData->company_id;
							$INWARDDATA['inward_date']			= date("Y-m-d");
							$INWARDDATA['created_by']			= Auth()->user()->adminuserid;
							$INWARDDATA['updated_by']			= Auth()->user()->adminuserid;
							$inward_record_id 					= ProductInwardLadger::AutoAddInward($INWARDDATA);
							$STOCK_AVG_PRICE 					= WmBatchProductDetail::GetSalesProductAvgPriceN1($DispatchData->bill_from_mrf_id,0,$pro['product_id'],$inward_record_id);
	                    	StockLadger::UpdateProductStockAvgPrice($pro['product_id'],PRODUCT_SALES,$DispatchData->bill_from_mrf_id,date("Y-m-d"),$STOCK_AVG_PRICE);	
						}
						/*INSERT DISPACTH INWARD RECORD IF DISPATCH GOT REJECTED */

						/** INSERT PRICE TREND IF RATE IS > MAX PRICE OR < MAX PRICE */
						if($DispatchPlanID == 0){
							if ($Status == REQUEST_APPROVED && (floatval($Max_Sales_Rate) > floatval($ins_prd['price']) || floatval($Max_Sales_Rate) < floatval($ins_prd['price'])))
							{
								$arrFields	= array("product_id"=>$pro['product_id'],
													"client_id"=>$DispatchData->client_master_id,
													"rate"=>_FormatNumberV2($pro['rate']),
													"rate_date"=>date("Y-m-d"),
													"city_id"=>0,
													"company_id"=>$DispatchData->company_id,
													"from_dispatch"=>$DispatchData->id,
													"rate_change_remark"=>$RATE_CHANGE_REMARK,
													"remark_id" =>$REMARK_ID,
													"created_by"=>Auth()->user()->adminuserid,
													"updated_by"=>Auth()->user()->adminuserid);
								$WMProductPrice->UpdateNewProductPriceTrend($arrFields);
							}
						}
						/** INSERT PRICE TREND IF RATE IS > MAX PRICE OR < MAX PRICE */
					}
				}
				/** UPDATE PRODUCT RATE */

				/** CALCULATE GST AMOUNT */
				$GST_PERCENT= 0;
				$RENT_SGST 	= 0;
				$RENT_CGST 	= 0;
				$RENT_IGST 	= 0;
				if(!empty($ARR_GST_PERCENT)) {
					$MAX_GST_PERCENT 	= _FormatNumberV2(max($ARR_GST_PERCENT));
					$GST_PERCENT 		= ($isFromSameState) ? $MAX_GST_PERCENT / 2 : $MAX_GST_PERCENT;
				}
				if($isFromSameState) {
					$RENT_SGST = $GST_PERCENT;
					$RENT_CGST = $GST_PERCENT;
				} else {
					$RENT_IGST = $GST_PERCENT;
				}
				$RENT_GST_AMT 	= 0;
				$DISPATCH_RENT 	= $DispatchData->rent_amt;
				if($DISPATCH_RENT > 0) {
					$DISPATCH_RENT 	= _FormatNumberV2($DispatchData->rent_amt);
					$RENT_GST_AMT 	= (($DISPATCH_RENT * $MAX_GST_PERCENT) / 100);
				}
				$TOTAL_RENT_AMT 	= $DISPATCH_RENT + $RENT_GST_AMT;
				$FINAL_AMT += $TOTAL_RENT_AMT;
				$FINAL_AMT -= (!empty($DispatchData->discount_amt)) ? _FormatNumberV2($DispatchData->discount_amt) : 0;
				/** CALCULATE GST AMOUNT */
				/*########## CALCULATE TCS AMOUNT###########*/

				$TCS_START_DATE		= strtotime(TCS_STATE_DATE_TIME);
				$DISPATCH_DATE_TIME = (isset($DispatchData->dispatch_date) && !empty($DispatchData->dispatch_date )) ? date("Y-m-d",strtotime($DispatchData->dispatch_date))." ".date("H:i:s")  : "";
				\Log::info("########## DISPATCH DATE TIME ##########".$DISPATCH_DATE_TIME);
				$TCS_TAX_PERCENT 	= 0;
				$TCS_TAX_RATE 		= 0;
				if(!empty($DISPATCH_DATE_TIME) && strtotime($DISPATCH_DATE_TIME) >= $TCS_START_DATE){
					$TCS_TAX_PERCENT 	= TCS_TEX_PERCENT;
					$TCS_TAX_RATE 		= (isset($DispatchData->ClientData->tcs_tax_allow) && $DispatchData->ClientData->tcs_tax_allow == 1) ? _FormatNumberV2(((TCS_TEX_PERCENT / 100) * $FINAL_AMT)) : 0;
				}

				/*########## CALCULATE TCS AMOUNT###########*/

				$update = 	self::where("id",$DispatchId)
								->update([
											"approval_status"	=>  $request->approval_status,
											"approved_by"		=>  $ApprovedBy,
											"eway_bill_no" 		=>  $eway_bill_no,
											"challan_no" 		=>  $challan_no,
											"type_of_transaction" 	=>  $TYPE_OF_TRANSACTION,
											"rent_cgst" 		=>  $RENT_CGST,
											"rent_sgst" 		=>  $RENT_SGST,
											"rent_igst" 		=>  $RENT_IGST,
											"rent_gst_amt" 		=>  $RENT_GST_AMT,
											"total_rent_amt" 	=>  $TOTAL_RENT_AMT,
											"tcs_rate"			=>  $TCS_TAX_PERCENT,
											"tcs_amount"		=>  $TCS_TAX_RATE,
											"challan_date"		=>  date("Y-m-d H:i:s")]);

				if($update)
				{
					if(isset($challan_no) && !empty($challan_no))
					{
						$data 	= WmDispatch::GetById($DispatchId);
						if($data)
						{
							$array 			= array("data"=> $data);
							$partialPath 	= PATH_CHALLAN;
							if(!is_dir(public_path(PATH_IMAGE.'/').$partialPath)) {
								mkdir(public_path(PATH_IMAGE.'/').$partialPath,0777,true);
							}
							$FILENAME   = "challan_".$DispatchId.".pdf";
							$PDF        = PDF::loadView('email-template.challan',$array);
							$PDF->setPaper("letter","A4");
							$PDF->save(public_path("/").PATH_IMAGE."/".PATH_CHALLAN."/".$FILENAME,true);
							$filePath   = public_path("/").PATH_IMAGE."/".PATH_CHALLAN."/".$FILENAME;
							$MediaId 	= MediaMaster::AddMedia($FILENAME,$FILENAME,$partialPath,$data->company_id);
							if($MediaId > 0) {
								self::where("id",$DispatchId)->update(["challan_media_id"=>$MediaId]);
							}
							/*DIRECT INVOICE GENERATE AFTER APPROVAL OF RATE - 05 MARCH 2020 - AXAY SHAH*/
							if($Status == DISPATCH_APPROVED) WmSalesMaster::GenerateInvoicev2($DispatchId);
							/*DIRECT INVOICE GENERATE AFTER APPROVAL OF RATE CODE END*/
						}
					}
				}
				########### GENERATE AUTO E INVOICE - 19 MAY 2022 ##############
				$res = array();
				if($DispatchId > 0 && $Status == DISPATCH_APPROVED){
					$GST_AMT = WmDispatchProduct::where(['dispatch_id'=>$DispatchId])->sum('gst_amount');
					if($GST_AMT > 0){
						\Log::info("###### AUTO GENERATE E INVOICE ".$GST_AMT." DISPATCH ID".$DispatchId);
						$res['res_from_auto_einvoice'] 	= 1;
						$res['res_result']   	= self::GenerateEInvoice($DispatchId);
						\Log::info("######################### DIRECT GENERATE ".print_r($res,true));
						return $res;
					}
				}
				
				$res['res_from_auto_einvoice'] 	= 0;
				$res['res_result'] 				= true;
				\Log::info("######################### NO DIRECT GENERATE ".print_r($res,true));

				return $res;
				########### GENERATE AUTO E INVOICE - 19 MAY 2022 ##############
				return true;
			}
			$res['res_from_auto_einvoice'] 	= 0;
			$res['res_result'] 				= false;
			return $res;
			return false;
		}
		return $DispatchId;
	}
	/*
	Use 	: Get Approval rights user data
	Author 	: Axay Shah
	Date 	: 26 June,2019
	*/
	public static function GetApprovalRights($rightsId){
		$UserBaseLocationMapping 	= new UserBaseLocationMapping();
		$UBLM 						= $UserBaseLocationMapping->getTable();
		$AdminUser 					= new Adminuser();
		$UserRights 				= new AdminUserRights();
		$Rights 					= $UserRights->getTable();
		$data = AdminUserRights::join("$UBLM","$Rights.adminuserid","=","$UBLM.adminuserid")
		->join($AdminUser->getTable()." as U1","$Rights.adminuserid","=","U1.adminuserid")
		->where("$UBLM.base_location_id",Auth()->user()->base_location)
		->where("$Rights.trnid",$rightsId)
		->where("U1.email","!=","")->pluck("U1.email");
		return $data;
	}

	/*
	Use  	:  Give client data with challon no and eway bill when mobile hit refresh button
	Author 	: Axay Shah
	Date 	: 26 June,2019
	*/
	public static function RefreshDispatch($DispatchId =0){
		$ClientMasterTbl 		= new WmClientMaster();
		$ClientMaster 			= $ClientMasterTbl->getTable();
		$Dispatch 				= (new static)->getTable();
		$OfficeNo 				= OFFICE_MOBILE_NO;
		$record = "";
		$data 	= self::select("$ClientMaster.client_name",
							"$ClientMaster.address",
							"$ClientMaster.city_id",
							"$ClientMaster.pincode",
							"$ClientMaster.latitude",
							"$ClientMaster.longitude",
							"$Dispatch.challan_no",
							"$Dispatch.eway_bill_no",
							"$Dispatch.challan_media_id",
							\DB::raw("'' as challan_url"),
							\DB::raw("CAST($OfficeNo AS char) AS mobile_no")
				)
		->join($ClientMaster,"$Dispatch.destination","=","$ClientMaster.id")
		->where("$Dispatch.id",$DispatchId)
		->first();
		if($data){
			if($data->challan_media_id > 0){
				$Media = MediaMaster::find($data->challan_media_id);
				if($Media){
					$data->challan_url = $Media->original_name;
					$record = $data;
				}
			}
		}
		return $record;

	}

	/*
	Use 	: Add Dispatch
	Author 	: Axay Shah
	Date 	: 29 May,2019
	*/
	public static function updateDispatchPlanStatus($dispatch_status,$batch_plan_id)
	{
		$sql = "UPDATE wm_dispatch_plan SET is_dispatch = '".$dispatch_status."' WHERE FIND_IN_SET(id,'".$batch_plan_id."')";
		$res = \DB::select($sql);
		return $res;
	}

	/*
	Use 	: segregationQtyUpdate
	Author 	: Axay Shah
	Date 	: 29 May,2019
	*/
	public static function segregationQtyUpdate($prdIdArr,$batch_type_status,$batchId,$dispatchId,$department,$date,$createdBy,$vehicleId = '')
	{
		$batchIdArr 	= array();
		if(!empty($prdIdArr)) {
			$prodPostQty 	= '';
			$productArr 	= array();
			foreach($prdIdArr as $value) {
				$prodPostQty 	=  $value['quantity'];
				$productArr	 	=  WmProductMaster::getTopParentProduct($value['product_id']);
				$productId 		=  isset($productArr->product_id)?$productArr->product_id:0;
				$sub_category	=  isset($productArr->sub_category)?$productArr->sub_category:0;
				$sub_parent_id 	=  isset($productArr->sub_parent_id)?$productArr->sub_parent_id:0;
				$parent_id 		=  isset($productArr->parent_id)?$productArr->parent_id:0;
				$top_parent_id 	=  isset($productArr->top_parent_product)?$productArr->top_parent_product:0;

				$segregationArr = WmProductMaster::GetProductwiseSegregation($productId, $sub_category, $sub_parent_id, $parent_id, $department);
				if(!empty($segregationArr)) {
					$counter 	= count($segregationArr);
					$ct 		= 0;
					for($i=0;$i<count($segregationArr);$i++) {
						$ct++;
						if($prodPostQty <= $segregationArr[$i]['tmp_stock']) {
							$remainQty 	= $segregationArr[$i]['tmp_stock'] - $prodPostQty;
							$updateQty 	= $prodPostQty;
						} else {
							$remainQty 	= $prodPostQty - $segregationArr[$i]['tmp_stock'];
							$updateQty 	= $segregationArr[$i]['tmp_stock'];
						}
							$final_qty 	= ($segregationArr[$i]['resegregate_qty']!= "0.00"?($segregationArr[$i]['resegregate_qty'] + $updateQty):$updateQty);
							$new_ref_id = ($segregationArr[$i]['reference_batch_id']!= ''?($segregationArr[$i]['reference_batch_id'].",".$batchId):$batchId);

							$up_data['resegregate_qty']		=	$final_qty;
							$up_data['reference_batch_id']	=	$new_ref_id;
							$updateDetails = WmBatchSegregationDetail::where("id",$segregationArr[$i]['id'])->update($up_data);


							/* INSERT Product Outward  */
							$add_outward['reference_batch_id']	= $segregationArr[$i]['batch_id'];
							$add_outward['batch_id']			= $batchId;
							$add_outward['batch_product_id']	= $segregationArr[$i]['product_id'];
							$add_outward['qty']					= $updateQty;
							$add_outward['para_type_id']		= $batch_type_status;
							$add_outward['dispatch_id']			= ($dispatchId != "") ? $dispatchId : 0;
							$add_outward['vehicle_id']			= ($vehicleId != "") ? $vehicleId : 0;
							$add_outward['created_date']		= date("Y-m-d H:i:s");
							$add_outward['created_by']			= Auth()->user()->adminuserid;
							$add_outward['master_dept_id']		= $department;
							WmBatchProductOutward::create($add_outward);
							$batchIdArr[] 	= $segregationArr[$i]['batch_id'];

						if($prodPostQty < $segregationArr[$i]['tmp_stock']) {
							break;
						}
						$prodPostQty = $remainQty;
						if($prodPostQty >0 && $ct == $counter) {
							$insertSeg['product_id']		=	$top_parent_id;
							$insertSeg['qty']				=	$prodPostQty;
							$insertSeg['resegregate_qty']	=	$prodPostQty;
							$insertSeg['created_date']		=	date("Y-m-d H:i:s");
							$insertSeg['created_by']		=	Auth()->user()->adminuserid;
							$insertSeg['dispatch_stock']	=	1;

							WmBatchSegregationDetail::create($insertSeg);


							$insOut['batch_product_id']		=	$top_parent_id;
							$insOut['batch_id']				= 	$batchId;
							$insOut['qty']					=	$prodPostQty;
							$insOut['para_type_id']			=	$batch_type_status;
							$insOut['dispatch_id']			=	$dispatchId;
							$insOut['vehicle_id']			=	($vehicleId != "") ? $vehicleId : 0;
							$insOut['created_date']			=	date("Y-m-d H:i:s");
							$insOut['created_by']			=	Auth()->user()->adminuserid;
							$insOut['master_dept_id']		=	$department;
							WmBatchProductOutward::create($insOut);
							break;
						}
					}
				} else {
					/* IF Main & Parent Product stock 0 Then Minus Stock From Top Parent Product */
					$insertSeg['product_id']		=	$top_parent_id;
					$insertSeg['qty']				=	$prodPostQty;
					$insertSeg['resegregate_qty']	=	$prodPostQty;
					$insertSeg['created_date']		=	date("Y-m-d H:i:s");
					$insertSeg['created_by']		=	Auth()->user()->adminuserid;
					$insertSeg['dispatch_stock']	=	1;
					WmBatchSegregationDetail::create($insertSeg);

					$insOut['batch_product_id']		=	$top_parent_id;
					$insOut['batch_id']				=	$batchId;
					$insOut['qty']					=	$prodPostQty;
					$insOut['para_type_id']			=	$batch_type_status;
					$insOut['dispatch_id']			=	$dispatchId;
					$insOut['vehicle_id']			=	($vehicleId != "") ? $vehicleId : 0;
					$insOut['created_date']			=	date("Y-m-d H:i:s");
					$insOut['created_by']			=	Auth()->user()->adminuserid;
					$insOut['master_dept_id']		=	$department;
					WmBatchProductOutward::create($insOut);
				}
			}
		}
		return $batchIdArr;
	}

	/*
	Use 	: Get Last Challan No
	Author 	: Axay Shah
	Date 	: 18 July,2019
	*/
	public static function GetLastChallanNo(){
		return 	self::where("company_id",Auth()->user()->company_id)
				->whereNotNull("challan_date")
				->whereNotNull("challan_no")
				->orderBy("challan_date","DESC")
				->value("challan_no");
	}

	/**
	* 	Use 	: retrieveAgreegatorCollection
	*	Author 	: Axay Shah
		Date 	: 12 June,2019
	*/
	public static function  retrieveAgreegatorCollection($id,$FromUpdate=false)
	{
		$data 				= array();
		$travel_distance 	= 0;
		$Dispatch = self::find($id);
		if($Dispatch){
			$CustomerMaster 	= CustomerMaster::find($Dispatch->origin);
			$DestinationMaster 	= WmClientMaster::find($Dispatch->client_master_id);
			$source_lat 		= (count($CustomerMaster) > 0) 		? $CustomerMaster->lattitude 	: 0;
			$source_long 		= (count($CustomerMaster) > 0) 		? $CustomerMaster->longitude 	: 0;
			$destination_lat	= (count($DestinationMaster) > 0) 	? $DestinationMaster->latitude  : 0;
			$destination_long	= (count($DestinationMaster) > 0) 	? $DestinationMaster->longitude : 0;


			$data['source_lat'] 		= $source_lat;
			$data['source_long'] 		= $source_long;
			$data['destination_lat'] 	= $destination_lat;
			$data['destination_long'] 	= $destination_long;

			if(!empty($source_lat) && !empty($source_long) && !empty($destination_lat) && !empty($destination_long)){
				$travel_distance = distance($source_lat,$source_long,$destination_lat,$destination_long,"K");
			}

			$data['travel_distance'] = $travel_distance;

		}
		return $data;
	}


	/*
	use 	: Upload Image for mobile side in dispatch
	Author 	: Axay Shah
	Date 	: 23 Jan,2020
	*/
	public static function DispatchImageUpload($request){
		$name 		= "";
		$DispatchId = (isset($request['dispatch_id']) && !empty($request['dispatch_id'])) ? $request['dispatch_id'] : 0;
		if(!empty($DispatchId) && isset($request['image'])){
			$companyId 			= Auth()->user()->company_id;
			$path 				= PATH_COMPANY."/".PATH_DISPATCH;
			$destinationPath 	= public_path('/').$path;

			if(!is_dir($destinationPath)) {
				mkdir($destinationPath,0777,true);
			}
			$image 		= $request["image"];
			$name 		= "scal_".time().'.'.$image->getClientOriginalExtension();
			$image->move($destinationPath, $name);
			$media = WmDispatchMediaMaster::AddDispatchMedia($DispatchId,$name,$name,$path,PARA_WAYBRIDGE);
			return $media;
		}
	}

	public static function InsertDispatchWeb($request){

		$dispatchPlanId 		= '';
		$OriginCity 			= 0;
		$DestinationCity 		= 0;
		$OriginStateCode 		= 0;
		$DestinationStateCode 	= 0;
		$DISPATCH_ID 			= 0;
		$CODE 					= 0;
		$challan_no				= 0;
		$challan_flag 			= false;
		$ProductConsumed 		= array();
		$BASE_LOCATION_ID 		= Auth()->user()->base_location;
		$FromMrf 				= (isset($request['from_mrf']) && !empty($request['from_mrf'])) ? $request['from_mrf'] : 'N';
		$Dated 					= (isset($request['dated']) && !empty($request['dated'])) ? $request['dated'] : '';
		$bill_of_lading 		= (isset($request['bill_of_lading']) && !empty($request['bill_of_lading'])) ? $request['bill_of_lading'] : '';
		$DeptId 				= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ? $request['master_dept_id'] : 0;
		$ShippingAddress 		= (isset($request['shipping_address']) && !empty($request['shipping_address'])) ? strtolower($request['shipping_address']) : "";
		$ShippingAddressId 		= (isset($request['shipping_address_id']) && !empty($request['shipping_address_id'])) ? $request['shipping_address_id'] : "";
		$ShippingState 			= (isset($request['shipping_state']) && !empty($request['shipping_state'])) ? $request['shipping_state'] : "";
		$ShippingPinCode		= (isset($request['shipping_pincode']) && !empty($request['shipping_pincode'])) ? $request['shipping_pincode'] : "";
		$ShippingCity 			= (isset($request['shipping_city']) && !empty($request['shipping_city'])) ? $request['shipping_city'] : "";
		$ShippingStateCode		= (isset($request['shipping_state_code']) && !empty($request['shipping_state_code'])) ? $request['shipping_state_code'] : "";
		$consignee_name			= (isset($request['consignee_name']) && !empty($request['consignee_name'])) ? $request['consignee_name'] : "";
		$ClientId				= (isset($request['client_id']) && !empty($request['client_id'])) ? $request['client_id'] : 0;
		$NESPL					= (isset($request['nespl']) && !empty($request['nespl'])) ? $request['nespl'] : 0;
		$FROM_JOBWORK			= (isset($request['from_jobwork']) && !empty($request['from_jobwork'])) ? $request['from_jobwork'] : 0;
		$JOBWORK_ID				= (isset($request['jobwork_id']) && !empty($request['jobwork_id'])) ? $request['jobwork_id'] : 0;
		$appointment_id			= (isset($request['appointment_id']) && !empty($request['appointment_id'])) ? $request['appointment_id'] : 0;
		$TYPE_OF_TRANSACTION 	= (isset($request['type_of_transaction']) && !empty($request['type_of_transaction'])) ? $request['type_of_transaction'] : "";
		$TARE_WEIGHT 		 	= (isset($request['tare_weight']) && !empty($request['tare_weight'])) ? $request['tare_weight'] : 0;
		$GROSS_WEIGHT 		 	= (isset($request['gross_weight']) && !empty($request['gross_weight'])) ? $request['gross_weight'] : 0;
		$BILL_FROM_MRF_ID 		= (isset($request['bill_from_mrf_id']) && !empty($request['bill_from_mrf_id'])) ? $request['bill_from_mrf_id'] : 0;
		$TRANSPORTER_NAME 		= (isset($request['transporter_name']) && !empty($request['transporter_name'])) ? $request['transporter_name'] : "";
		$VEHICLE_IN_OUT_ID 		= (isset($request['vehicle_in_out_id']) && !empty($request['vehicle_in_out_id'])) ? $request['vehicle_in_out_id'] : 0;
		$DISPATCH_TYPE_TRN 		= (isset($request['dispatch_type']) && !empty($request['dispatch_type'])) ? $request['dispatch_type'] : 0;
		$VIRTUAL_TARGET 		= (isset($request['virtual_target']) && !empty($request['virtual_target'])) ? $request['virtual_target'] : 0;
		$REMARKS 				= (isset($request['remark']) && !empty($request['remark'])) ? $request['remark'] : 0;
		$RELATION_SHIP_ID 		= (isset($request['relationship_manager_id']) && !empty($request['relationship_manager_id'])) ? $request['relationship_manager_id'] : 0;
		$TRANSFER_TRANS  		= TransactionMasterCodesMrfWise::GetTrnType($DISPATCH_TYPE_TRN);
		if(!empty($TRANSFER_TRANS)){
			$GET_CODE 			= TransactionMasterCodesMrfWise::GetLastTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS);
			if($GET_CODE){
				$CODE 			= 	$GET_CODE->code_value + 1;
				$challan_no 	=   $GET_CODE->group_prefix.LeadingZero($CODE);
				$challan_flag 	= 	true;
			}
		}
		/* IF DIRECT DISPATCH FROM MRF THEN ADD MRF ID IN ORIGIN AND CITY OF MRF IN ORIGIN CITY - 03 JULY 2019*/
		if(isset($request['origin']) && !empty($request['origin'])){
			if($FromMrf == "N"){
				$OriginCity 	= CustomerMaster::where("customer_id",$request['origin'])->value('city');
			}else{
				$OriginCity 	= WmDepartment::where("id",$request['origin'])->value('location_id');
				$DeptId 		= $request['origin'];
			}

		}
		if(isset($request['destination']) && !empty($request['destination'])){
			$DestinationCity 	= WmClientMaster::where("id",$request['destination'])->value('city_id');
		}
		$OriginGSTStateCodeData = StateMaster::GetGSTCodeByCustomerCity($OriginCity);
		if(isset($OriginGSTStateCodeData->state_code)){
			$OriginStateCode 	= $OriginGSTStateCodeData->state_code;
		}
		/* ####### MASTER DEPARTMENT GST STATE CODE CHANGES ####### */
		$MasterDeptStateID = 0;
		// if(isset($request['master_dept_id']) && !empty($request['master_dept_id'])){
		// 	$MasterDeptStateID = WmDepartment::where("id",$request['master_dept_id'])->value('gst_state_code_id');
		// }
		if(!empty($BILL_FROM_MRF_ID)){
			$MasterDeptStateID = WmDepartment::where("id",$BILL_FROM_MRF_ID)->value('gst_state_code_id');
		}
		/* ####### MASTER DEPARTMENT GST STATE CODE CHANGES ####### */

		$ClientMasterData	= WmClientMaster::find($request['destination']);
		if($ClientMasterData){
			$DestinationStateCode 	= $ClientMasterData->gst_state_code;
			if(empty($DestinationStateCode)){
				/* PRODUCT CONSUMED OF CLIENT - 29 JULY 2019*/
				if(isset($ClientMasterData->product_consumed) && !empty($ClientMasterData->product_consumed)){
					$ProductConsumed = explode(",",$ClientMasterData->product_consumed);
				}
				/*END PRODUCT CONSUMED OF CLIENT*/
				$getCode = StateMaster::GetGSTCodeByCustomerCity($ClientMasterData->city_id);
				if(isset($getCode->state_code)){
					$DestinationStateCode 	= $getCode->state_code;
				}
			}
		}
		$ShippingState 					= GSTStateCodes::where("state_code",$ShippingStateCode)->value('state_name');
		$Dispatch 						= new WmDispatch();
		$ddate 							= date('Y-m-d',strtotime($request['dispatch_date']));
		$total_qty 						= $request['total_qty'];
		$Dispatch->dispatchplan_id		= $dispatchPlanId;
		$Dispatch->shipping_address		= $ShippingAddress;
		$Dispatch->shipping_address_id	= $ShippingAddressId;
		$Dispatch->shipping_state		= $ShippingState;
		$Dispatch->shipping_state_code	= $ShippingStateCode;
		$Dispatch->shipping_city		= $ShippingCity;
		$Dispatch->shipping_pincode		= $ShippingPinCode;
		$Dispatch->client_master_id  	= $ClientId;
		$Dispatch->quantity				= $total_qty;
		$Dispatch->vehicle_id			= $request['vehicle_id'];
		$Dispatch->origin				= $request['origin'];
		$Dispatch->origin_state_code 	= $OriginStateCode;
		$Dispatch->destination_state_code = $DestinationStateCode;
		$Dispatch->origin_city			= $OriginCity;
		$Dispatch->from_mrf				= $FromMrf;
		$Dispatch->company_id			= Auth()->user()->company_id;
		$Dispatch->destination			= $request['destination'];
		$Dispatch->destination_city		= $DestinationCity;
		$Dispatch->driver_name			= (isset($request['dr_name'])?$request['dr_name']:'');
		$Dispatch->driver_mob_no		= (isset($request['dr_mobile'])?$request['dr_mobile']:'');
		$Dispatch->master_dept_id		= $DeptId;
		$Dispatch->dispatch_date		= date('Y-m-d',strtotime($request['dispatch_date']));
		$Dispatch->dispatch_type		= (isset($request['dispatch_type'])?$request['dispatch_type']:0);
		$Dispatch->recyclable_type		= (isset($request['recyclable_type'])?$request['recyclable_type']:0);
		$Dispatch->created_by			= Auth()->user()->adminuserid;
		$Dispatch->created_at			= date('Y-m-d H:i:s');
		$Dispatch->invoice_generated	= 0;
		$Dispatch->challan_no 			= $challan_no;
		$Dispatch->nespl 				= $NESPL;
		$Dispatch->bill_of_lading 		= $bill_of_lading;
		$Dispatch->dated 				= $Dated;
		$Dispatch->from_jobwork			= $FROM_JOBWORK;
		$Dispatch->jobwork_id			= $JOBWORK_ID;
		$Dispatch->appointment_id		= $appointment_id;
		$Dispatch->type_of_transaction	= $TYPE_OF_TRANSACTION;
		$Dispatch->tare_weight			= $TARE_WEIGHT;
		$Dispatch->gross_weight			= $GROSS_WEIGHT;
		$Dispatch->challan_date 		= (!empty($challan_no)) ? date("Y-m-d H:i:s") : "";
		$Dispatch->eway_bill_no			= (isset($request['eway_bill_no']) && !empty($request['eway_bill_no'])?$request['eway_bill_no']:'');
		$Dispatch->master_dept_state_code 	= $MasterDeptStateID;
		$Dispatch->remarks 				= $REMARKS;
		$Dispatch->bill_from_mrf_id 	= $BILL_FROM_MRF_ID;
		$Dispatch->transporter_name 	= $TRANSPORTER_NAME;
		$Dispatch->virtual_target 		= $VIRTUAL_TARGET;
		$Dispatch->relationship_manager_id = $RELATION_SHIP_ID;
		if($Dispatch->save()){
			$DISPATCH_ID 	= $Dispatch->id;
			$product 		= array();
			if($challan_flag){
				TransactionMasterCodesMrfWise::UpdateTrnCode($BILL_FROM_MRF_ID,$TRANSFER_TRANS,$CODE);
			}
			WaybridgeModuleVehicleInOut::UpdateVehicleInOutFlag($VEHICLE_IN_OUT_ID,WAYBRIDGE_MODULE_DISPATCH,$DISPATCH_ID,1);
			if(isset($request['sales_product']) && !empty($request['sales_product'])){
				$product 	= json_decode($request['sales_product'],true);
				foreach($product as $value)
				{
					$DESCRIPTION 	= (isset($value['description']) 		&& !empty($value['description'])) 		? $value['description'] 		: "";
					$IS_BAILING 	= (isset($value['is_bailing']) 			&& !empty($value['is_bailing'])) 		? $value['is_bailing'] 			: 0;
					$BALINIGTYPE 	= (isset($value['bailing_type']) 		&& !empty($value['bailing_type'])) 		? $value['bailing_type'] 		: 0;
					$BALINIG_QTY 	= (isset($value['bail_qty']) 			&& !empty($value['bail_qty'])) 			? $value['bail_qty'] 			: 0;
					$BAILING_ID 	= (isset($value['bailing_master_id']) 	&& !empty($value['bailing_master_id'])) ? $value['bailing_master_id'] 	: 0;
					$QTY = $value['quantity'];
					if(!in_array($value['sales_product_id'],$ProductConsumed)){
						array_push($ProductConsumed,$value['sales_product_id']);
					}
					############## NEW CHANGES FOR DIRECT DISPATCH REPORT ########
					$collectionDetailsId = AppointmentCollectionDetail::where("collection_id",$request['collection_id'])
					->where("product_id",$value['product_id'])
					->where("actual_coll_quantity",$QTY)
					->value("collection_detail_id");
					$collectionDetailsId = (!empty($collectionDetailsId)) ? $collectionDetailsId : 0;
					$ins_prd['collection_detail_id'] = $collectionDetailsId;
					############## NEW CHANGES FOR DIRECT DISPATCH REPORT ########
					$ins_prd['dispatch_plan_id']	= 0;
					$ins_prd['dispatch_id']			= $DISPATCH_ID;
					$ins_prd['product_id']			= $value['sales_product_id'];
					$ins_prd['description']			= $DESCRIPTION;
					$ins_prd['quantity']			= $QTY;
					$ins_prd['is_bailing']			= $IS_BAILING;
					$ins_prd['bailing_type']		= $BALINIGTYPE;
					$ins_prd['bail_qty']			= $BALINIG_QTY;
					$ins_prd['bailing_master_id'] 	= $BAILING_ID;
					$ins_prd['price']				= _FormatNumberV2($value['price']);
					WmDispatchProduct::insert($ins_prd);

					############ AVG PRICE FOR DIRECT DISPATCH AND NORMAL DISPATCH #################
					$collectionPrice 	= 	AppointmentCollectionDetail::where("collection_detail_id",$collectionDetailsId)->value("product_customer_price");
					$AVG_PRICE 			= 	StockLadger::where("product_type",PRODUCT_SALES)
											->where("mrf_id",$Dispatch->bill_from_mrf_id)
											->where("stock_date",date("Y-m-d"))
											->where("product_id",$value['sales_product_id'])
											->value("avg_price");
					$AVG_PRICE 			= (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) :0;
					// WmDispatchSalesProductAvgPrice::insert([
					// 	"dispatch_id" 			=> $DISPATCH_ID,
					// 	"collection_id" 		=> $request['collection_id'],
					// 	"direct_dispatch" 		=> 1,
					// 	"collection_detail_id" 	=> $collectionDetailsId,
					// 	"purchase_product_id" 	=> $value['product_id'],
					// 	"sales_product_id" 		=> $value['sales_product_id'],
					// 	"price" 				=> $collectionPrice,
					// 	"avg_price" 			=> $AVG_PRICE,
					// 	"created_at" 			=> date("Y-m-d"),
					// 	"updated_at" 			=> date("Y-m-d"),

					// ]);
					############ AVG PRICE FOR DIRECT DISPATCH AND NORMAL DISPATCH #################
					if($IS_BAILING == "1"){
						/*BAIL OUTWARD ENTRY productId,$bailQty,$bailType,$bailMasterId,$MRFID,$dispatchID,$OutwardDate */
						BailOutwardLedger::AddBailOutWard($value['sales_product_id'],$BALINIG_QTY,$BALINIGTYPE,$BAILING_ID,$DeptId,$DISPATCH_ID,$Dispatch->dispatch_date);
					}
					\Log::info("############ DISPATCH ID ###############".$DISPATCH_ID);
					/* MANAGE STOCK UPDATES IN PROCESS */
					$INWARDDATA 						= array();
					$INWARDDATA['product_id'] 			= $value['sales_product_id'];
					$INWARDDATA['production_report_id']= 0;
					$INWARDDATA['ref_id']				= $DISPATCH_ID;
					$INWARDDATA['direct_dispatch']		= ($Dispatch->appointment_id > 0) ? 1: 0;
					$INWARDDATA['quantity']				= $QTY;
					$INWARDDATA['avg_price']			= $collectionPrice;
					$INWARDDATA['type']					= TYPE_DISPATCH;
					$INWARDDATA['product_type']			= PRODUCT_SALES;
					$INWARDDATA['mrf_id']				= $Dispatch->bill_from_mrf_id;
					$INWARDDATA['company_id']			= $Dispatch->company_id;
					$INWARDDATA['outward_date']			= date("Y-m-d",strtotime($Dispatch->dispatch_date));
					$INWARDDATA['created_by']			= Auth()->user()->adminuserid;
					$INWARDDATA['updated_by']			= Auth()->user()->adminuserid;
					ProductInwardLadger::AutoAddInward($INWARDDATA);
					
					/* MANAGE STOCK UPDATES IN PROCESS */
					$OUTWORDDATA 						= array();
					$OUTWORDDATA['sales_product_id'] 	= $value['sales_product_id'];
					$OUTWORDDATA['product_id'] 			= 0;
					$OUTWORDDATA['production_report_id']= 0;
					$OUTWORDDATA['direct_dispatch']		= ($Dispatch->appointment_id > 0) ? 1: 0;
					$OUTWORDDATA['ref_id']				= $DISPATCH_ID;
					$OUTWORDDATA['quantity']			= $QTY;
					$OUTWORDDATA['type']				= TYPE_DISPATCH;
					$OUTWORDDATA['product_type']		= PRODUCT_SALES;
					$OUTWORDDATA['mrf_id']				= $Dispatch->bill_from_mrf_id;
					$OUTWORDDATA['company_id']			= $Dispatch->company_id;
					$OUTWORDDATA['outward_date']		= date("Y-m-d",strtotime($Dispatch->dispatch_date));
					$OUTWORDDATA['created_by']			= Auth()->user()->adminuserid;
					$OUTWORDDATA['updated_by']			= Auth()->user()->adminuserid;
					OutWardLadger::AutoAddOutward($OUTWORDDATA);
					\Log::info("STOCK DATA OUTWARD".$QTY);
					\Log::info("STOCK DATA OUTWARD".print_r($OUTWORDDATA,true));
					/* MANAGE STOCK UPDATES IN PROCESS */

				}
				/*Product Consume update in client master*/
				if(!empty($ProductConsumed)){
					$ProductConsumedString = implode(",",$ProductConsumed);
					WmClientMaster::where("id",$request['destination'])->update(["product_consumed"=>$ProductConsumedString]);
				}
				/*UPDATE STATUS FOR APPROVAL RATE */
				$company 		= CompanyMaster::find(Auth()->user()->company);
				$FromEmail 		= array(
					"Name"	=>	env("MAIL_FROM_NAME"),
					"Email"	=>	env("MAIL_FROM_ADDRESS"),
				);
				if($company){
					$FromEmail 	= array (
						"Name" 	=> $company->company_name,
						"Email" => $company->company_email
					);
				}
				$DispatchData 		= WmDispatchProduct::GetProductByDispatchId($Dispatch->id);
				$Subject 			= "Dispatch Product Rate Approval";
				$Body 				= "Hello,<br>";
				$Body 				.= "Here are Dispatch Product Rate Added By ".Auth()->user()->firstname." ".Auth()->user()->lastname.".<br /><br />";
				$Body 				.= " Challan No is : <b>".$challan_no."</b>";

				/**/
				/* SEND SMS TO DEFINE MOBILE NO*/
				/*IF MASTER DEPT. CITY IS INDOR THEN NO NEED TO SEND EMAIL AND SMS TO RK - 09-MARCH-2020*/
				$DeptLocId 			= WmDepartment::where("id",$Dispatch->master_dept_id)->first();
				if($DeptLocId){
					if(!empty($DeptLocId->mobile)){
						$MOBILE 	=  explode(",",$DeptLocId->mobile);
					}
					if(!empty($DeptLocId->rate_approval_email)){
						$GetApprovalEmail 	=  explode(",",$DeptLocId->rate_approval_email);
					}
				}
				// if(!empty($GetApprovalEmail)){
				// 	self::SendDispatchRateUpdateEmail($DispatchData,$FromEmail,$GetApprovalEmail,$Subject,$Body);
				// }
				/* SEND SMS TO DEFINE MOBILE NO*/
				// if(!empty($MOBILE)){
				// 	foreach($MOBILE AS $RAW){
				// 		$message = "New Dispatch added for approval by ".Auth()->user()->firstname." ".Auth()->user()->lastname." with Challan no".$challan_no;
				// 		\App\Classes\SendSMS::sendMessage($RAW,$message);
				// 	}
				// }
				/* SEND SMS TO DEFINE MOBILE NO*/
			}
		}
		if(isset($request['dispatch_plan_id']) && !empty($request['dispatch_plan_id']))
		{
			$dispatchplan_id_str = rtrim(implode(',',$request['dispatch_plan_id']),',');
			self::updateDispatchPlanStatus(1,$dispatchplan_id_str);
		}
		/*NOTE : FOR NOW IT IS NOT IN PROCESS SO WHEN IT APPLIED JUST UNCOMMENT THE CODE - 03 JUNE,2019*/
		/*
		$batchIdArr = self::segregationQtyUpdate($product,SALES_BATCH_TYPE,$batchId='', $Dispatch->id, $request['master_dept_id'], date("Y-m-d H:i:s"), Auth()->user()->adminuserid, $request['vehicle_id']);
		*/
		return $Dispatch;
	}


	public static function DispatchReport($request)
	{
		try{
			$base_location_Ids 	= "";
			$ACCOUNT  			= (isset($request->account) && !empty($request->account)) ?  $request->account : "";
			if(empty($ACCOUNT)){
				$cityId         	= (isset($request->base_location_id) && !empty($request->base_location_id)) ? GetBaseLocationCity($request->base_location_id) : GetBaseLocationCity(Auth()->user()->base_location);
			}else{

				$base_location_Ids 	= (isset($request->base_location_id) && !empty($request->base_location_id)) ? $request->base_location_id : UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id") ;
			}

			$res 				= array();
			$array 				= array();
			$table				= new WmDispatch();
			$parameter			= new Parameter();
			$VehicleTbl			= new VehicleMaster();
			$Today          	= date('Y-m-d');
			################ FOR REGISTER ITEM WISE REPORT ################
			$PAID  				= (isset($request->paid) && !empty($request->paid)) ?  $request->paid : "";
		
			$INCLUDE_CITY  		= (isset($request->city_id) && !empty($request->city_id)) ?  $request->city_id : "";
			$EXCLUDE_CITY  		= (isset($request->exclude_city_id) && !empty($request->exclude_city_id)) ?  $request->exclude_city_id : "";
			$INCLUDE_BILL_MRF  	= (isset($request->inc_bill_from_mrf) && !empty($request->inc_bill_from_mrf)) ?  $request->inc_bill_from_mrf : "";
			$EXCLUDE_BILL_MRF  	= (isset($request->exc_bill_from_mrf) && !empty($request->exc_bill_from_mrf)) ?  $request->exc_bill_from_mrf : "";
			$LoginUserID 		= (isset($request->login_user_id) && !empty($request->login_user_id)) ? $request->login_user_id : 0;
			if($LoginUserID > 0) {
				if(!empty($INCLUDE_CITY)) {
					$INCLUDE_CITY = explode(",",$INCLUDE_CITY);
				}
				if(!empty($EXCLUDE_CITY)) {
					$EXCLUDE_CITY = explode(",",$EXCLUDE_CITY);
				}
			} else {
				$LoginUserID = Auth()->user()->adminuserid;
			}
			################ FOR REGISTER ITEM WISE REPORT ################

			$createdAt 		= ($request->has('created_from') && $request->input('created_from')) ? date("Y-m-d",strtotime($request->input("created_from"))) : "";
			$createdTo 		= ($request->has('created_to') && $request->input('created_to')) ? date("Y-m-d",strtotime($request->input("created_to"))) : "";
			$data 			= WmDispatch::select(
								"wm_dispatch.bill_from_mrf_id",
								"wm_dispatch.e_invoice_no",
								"wm_dispatch.acknowledgement_no",
								"wm_dispatch.acknowledgement_date",
								"wm_dispatch.dispatch_type",
								DB::raw("DATE_FORMAT(wm_dispatch.dispatch_date,'%d-%m-%Y') as Dispatch_Date"),
								\DB::raw("(CASE
									WHEN wm_dispatch.invoice_cancel = 1 THEN 'Cancel'
									WHEN wm_dispatch.approval_status = 0 THEN 'Pending'
									WHEN wm_dispatch.approval_status = 1 THEN 'Approved'
									WHEN wm_dispatch.approval_status = 2 THEN 'Rejected'
								END ) AS Dispatch_Status"),
								"wm_dispatch.tcs_amount",
								"wm_dispatch.tcs_rate",
								"wm_dispatch.approval_status",
								"wm_dispatch.id as dispatch_id",
								"wm_dispatch.challan_no",
								"wm_dispatch.invoice_generated",
								"wm_dispatch.bill_of_lading",
								"wm_dispatch.invoice_generated",
								"wm_dispatch.eway_bill_no",
								DB::raw("IF(wm_dispatch.invoice_generated = 0,'No','Yes') as is_invoice_generated"),
								"wm_dispatch.invoice_cancel",
								"wm_dispatch.destination_state_code",
								"wm_dispatch.origin_state_code",
								"wm_dispatch.shipping_address_id",
								"wm_dispatch.master_dept_state_code",
								"V.vehicle_number",
								"V.vehicle_name",
								"V.owner_name",
								"wm_client_master.client_name",
								"wm_client_master.gst_state_code",
								"wm_client_master.gstin_no",
								"wm_product_master.title",
								"wm_product_master.title as productName",
								"wm_product_master.hsn_code",
								"wm_product_master.cgst",
								"wm_product_master.sgst",
								"wm_product_master.igst",
								"wm_sales_master.product_id",
								"wm_sales_master.cgst_rate",
								"wm_sales_master.sgst_rate",
								"wm_sales_master.igst_rate",
								"wm_sales_master.gross_amount",
								"wm_sales_master.net_amount",
								"wm_sales_master.gst_amount",
								"wm_sales_master.dispatch_product_id",
								"wm_sales_master.quantity as sales_quantity",
								"wm_sales_master.rate as sales_rate",
								"wm_department.department_name",
								"BILL_FROM.department_name as bill_from_mrf_name",
								"wm_department.location_id",
								"PARAM.para_value as sales_type_name"
							)
							->leftjoin("wm_sales_master","wm_dispatch.id","=","wm_sales_master.dispatch_id")
							->leftjoin($parameter->getTable()." as PARAM","wm_dispatch.type_of_transaction","=","PARAM.para_id")
							->join("wm_client_master","wm_client_master.id","=","wm_dispatch.client_master_id")
							->leftjoin($VehicleTbl->getTable()." as V","wm_dispatch.vehicle_id","=","V.vehicle_id")
							->leftjoin("wm_product_master","wm_sales_master.product_id","=","wm_product_master.id")
							->leftjoin("wm_department","wm_dispatch.master_dept_id","=","wm_department.id")
							->leftjoin("wm_department as BILL_FROM","wm_dispatch.bill_from_mrf_id","=","BILL_FROM.id");

			if(empty($INCLUDE_CITY) && empty($EXCLUDE_CITY) && empty($ACCOUNT)) {
				$cityId 			= UserCityMpg::userAssignCity($LoginUserID,true);
				$baseLocationIDs 	= UserBaseLocationMapping::where("adminuserid",$LoginUserID)->pluck("base_location_id");
				$data->whereIn("BILL_FROM.base_location_id",$baseLocationIDs);
			} elseif(!empty($INCLUDE_CITY)) {
				$data->whereIn("wm_department.location_id",$INCLUDE_CITY);
			}
			if(!empty($EXCLUDE_CITY)) {
				$data->whereNotIn("wm_department.location_id",$EXCLUDE_CITY);
			}
			if(!empty($INCLUDE_BILL_MRF)) {
				$data->whereIn("wm_dispatch.bill_from_mrf_id",$INCLUDE_BILL_MRF);
			}
			if(!empty($EXCLUDE_BILL_MRF)) {
				$data->whereNotIn("wm_dispatch.bill_from_mrf_id",$EXCLUDE_BILL_MRF);
			}

			if($request->has('master_dept_id') && !empty($request->input('master_dept_id'))) {
				$data->where("wm_dispatch.master_dept_id",$request->input('master_dept_id'));
			}
			if($request->has('bill_from_mrf_id') && !empty($request->input('bill_from_mrf_id'))) {
				$data->where("wm_dispatch.bill_from_mrf_id",$request->input('bill_from_mrf_id'));
			}
			if($request->has('vehicle_number') && !empty($request->input('vehicle_number'))) {
				$data->where("V.vehicle_number","like","%".$request->input('vehicle_number')."%");
			}
			if($request->has('client_id') && !empty($request->input('client_id'))) {
				$data->where("wm_dispatch.client_master_id",$request->input('client_id'));
			}
			if($request->has('product_id') && !empty($request->input('product_id'))) {
				$data->where("wm_sales_master.product_id",$request->input('product_id'));
			}
			if($request->has('challan_no') && !empty($request->input('challan_no'))) {
				$data->where("wm_dispatch.challan_no",$request->input('challan_no'));
			}
			if($request->has('acknowledgement_date') && !empty($request->acknowledgement_date)) {
				$data->where("wm_dispatch.acknowledgement_date",date("Y-m-d",strtotime($request->acknowledgement_date)));
			}
			if($request->has('acknowledgement_no') && !empty($request->input('acknowledgement_no'))) {
				$data->where("wm_dispatch.acknowledgement_no","like","%".$request->input('acknowledgement_no')."%");
			}
			if($request->has('invoice_generated')) {
				$invoice_generated =  $request->input('invoice_generated');
				if($invoice_generated == "0") {
					$data->where("wm_dispatch.invoice_generated",$invoice_generated);
				} elseif($invoice_generated == "1") {
					$data->where("wm_dispatch.invoice_generated",$invoice_generated);
				}
			}
			if($request->has('invoice_cancel')) {
				$invoice_cancel =  $request->input('invoice_cancel');
				if($invoice_cancel == "0") {
					$data->where("wm_dispatch.invoice_cancel",$invoice_cancel);
				} elseif($invoice_cancel == "1") {
					$data->where("wm_dispatch.invoice_cancel",$invoice_cancel);
				}
			}
			if($request->has('status')) {
				$status =  $request->input('status');
				if($status == "0") {
					$data->where("wm_dispatch.approval_status",$status);
				} elseif($status == "1" || $status == "2") {
					$data->where("wm_dispatch.approval_status",$status);
				}
			}
			if($PAID == "0" || $PAID == "1") {
				$data->where("wm_dispatch.aggregator_dispatch",$PAID);
			}
			if(!empty($createdAt) && !empty($createdTo)){
				$data->whereBetween("wm_dispatch.dispatch_date",[$createdAt." 00:00:00",$createdTo." 23:59:59"]);
			}elseif(!empty($createdAt)){
				$data->whereBetween("wm_dispatch.dispatch_date",[$createdAt." 00:00:00",$createdAt." 23:59:59"]);
			}elseif(!empty($createdTo)){
				$data->whereBetween("wm_dispatch.dispatch_date",[$createdTo." 00:00:00",$createdTo." 23:59:59"]);
			}
			if(!empty($base_location_Ids)){
				$data->whereIn("BILL_FROM.base_location_id",$base_location_Ids);
			}
			$result 	= $data->get()->toArray();
			$arrResult 	= array();
			$counter	= 0;
			if(!empty($result))
			{
				$totalQty 			= 0;
				$totalGst 			= 0;
				$totalGross			= 0;
				$totalNet 			= 0;
				$totalGrossCrAmt	= 0;
				$totalGrossDbAmt	= 0;
				$totalFinalAmt 		= 0;
				$totalCrAmt			= 0;
				$totalDbAmt			= 0;
				$tcsCheck 			= array();
				foreach($result as $key => $value)
				{
					$DISPATCH_QTY 		= 0;
					$CGST_AMT 			= 0;
					$SGST_AMT 			= 0;
					$IGST_AMT 			= 0;
					$CGST_RATE 			= 0;
					$SGST_RATE 			= 0;
					$IGST_RATE 			= 0;

					$DESCRIPTION 		= "";
					$result[$key]['dispatch_date'] = (isset($value['dispatch_date'])) ? date("Y-m-d",strtotime(['dispatch_date'])) : "";
					$IsFromSameState 	= ($value['destination_state_code'] == $value['master_dept_state_code']) ? true : false;
					$INVOICE_DATA 		= WmInvoices::where("dispatch_id",$value['dispatch_id'])->orderBy('id','desc')->first();
					$DESTINATION 		= ($INVOICE_DATA) ? $INVOICE_DATA->destination : "";
					$DISPATCH 			= WmDispatchProduct::find($value["dispatch_product_id"]);
					$Rate 				= (!empty($value['sales_rate'])) ? _FormatNumberV2($value['sales_rate']) : 0;
					$Quantity 			= (!empty($value['sales_quantity'])) ? _FormatNumberV2($value['sales_quantity']) : 0 ;
					// $CREDIT_AMT 		= (!empty($value['credit_gross_amt'])) ? _FormatNumberV2($value['credit_gross_amt']) : 0 ;
					// $DEBIT_AMT 			= (!empty($value['debit_gross_amt'])) ? _FormatNumberV2($value['debit_gross_amt']) : 0 ;

					$creditNoteTbl 		= new WmInvoicesCreditDebitNotes();
					$creditNoteDtl 		= new WmInvoicesCreditDebitNotesDetails();
					$detailsTbl 		= $creditNoteDtl->getTable();

					$CREDIT_AMT 		= 	WmInvoicesCreditDebitNotesDetails::join($creditNoteTbl->getTable()."  as CRN_TBL","$detailsTbl.cd_notes_id","=","CRN_TBL.id")
											->where("CRN_TBL.dispatch_id",$value["dispatch_id"])
											->where("CRN_TBL.notes_type",0)
											->where("CRN_TBL.status",3)
											->where("$detailsTbl.product_id",$value["product_id"])
											->where("$detailsTbl.dispatch_product_id",$value["dispatch_product_id"])
											->sum("$detailsTbl.revised_gross_amount");
					$DEBIT_AMT 			= 	WmInvoicesCreditDebitNotesDetails::join($creditNoteTbl->getTable()."  as CRN_TBL","$detailsTbl.cd_notes_id","=","CRN_TBL.id")
											->where("CRN_TBL.dispatch_id",$value["dispatch_id"])
											->where("CRN_TBL.notes_type",1)
											->where("CRN_TBL.status",3)
											->where("$detailsTbl.product_id",$value["product_id"])
											->where("$detailsTbl.dispatch_product_id",$value["dispatch_product_id"])
											->sum("$detailsTbl.revised_gross_amount");
					if($DISPATCH) {
						$CGST_RATE 		= $DISPATCH->cgst_rate;
						$SGST_RATE 		= $DISPATCH->sgst_rate;
						$IGST_RATE 		= $DISPATCH->igst_rate;
						$DISPATCH_QTY 	= $DISPATCH->quantity;
						$DESCRIPTION 	= $DISPATCH->description;
					}
					if($IsFromSameState) {
						if($Rate > 0) {
							$CGST_AMT 	= ($CGST_RATE > 0) ? (($Quantity * $Rate) / 100) * $CGST_RATE:0;
							$SGST_AMT 	= ($SGST_RATE > 0) ? (($Quantity * $Rate) / 100) *  $SGST_RATE:0;
						}
					} else {
						if($Rate > 0) {
							$IGST_AMT 	= ($IGST_RATE > 0) ? (($Quantity * $Rate) / 100) * $IGST_RATE:0;
						}
					}


					$GST_AMT 	= (!empty($value['gst_amount'])) ? $value['gst_amount']:0;
					$NET_AMT 	= (!empty($value['net_amount'])) ? $value['net_amount']:0;
					$GROSS_AMT 	= (!empty($value['gross_amount'])) ? $value['gross_amount']:0;

					$TCS_AMT 	= (!empty($value['tcs_amount']) && !in_array($value["dispatch_id"],$tcsCheck)) ? $value['tcs_amount']:0;
					$result[$key]['tcs_amount'] = $TCS_AMT;
					$NET_AMT 	= $TCS_AMT + $NET_AMT;
					array_push($tcsCheck,$value["dispatch_id"]);
					########## IF DISPATCH TYPE NON RECYCLEBLE THEN NOT COUNT GST ##########
					if($result[$key]['dispatch_type'] != RECYCLEBLE_TYPE){

						$NET_AMT 	= $NET_AMT - $GST_AMT;
						$GST_AMT 	= 0;
						$CGST_AMT 	= 0;
						$SGST_AMT 	= 0;
						$IGST_AMT 	= 0;
					}
					########## IF DISPATCH TYPE NON RECYCLEBLE THEN NOT COUNT GST ##########
					$result[$key]['sales_quantity'] 	= _FormatNumberV2($Quantity);
					$result[$key]['dispatch_qty'] 		= _FormatNumberV2($DISPATCH_QTY);
					$result[$key]['accepted_qty'] 		= _FormatNumberV2($Quantity);
					$result[$key]['cgst_amount'] 		= _FormatNumberV2($CGST_AMT);
					$result[$key]['sgst_amount'] 		= _FormatNumberV2($SGST_AMT);
					$result[$key]['igst_amount'] 		= _FormatNumberV2($IGST_AMT);
					$result[$key]['gst_amount'] 		= _FormatNumberV2($GST_AMT);
					$result[$key]['net_amount'] 		= _FormatNumberV2($NET_AMT);
					$result[$key]['gross_amount'] 		= _FormatNumberV2($GROSS_AMT);
					$result[$key]['product_description']= $DESCRIPTION;
					$result[$key]['invoice_no'] 		= ($INVOICE_DATA) ? $INVOICE_DATA->invoice_no : "";
					$result[$key]['consignee_state'] 	= ($INVOICE_DATA) ? $INVOICE_DATA->shipping_state : "";;
					$result[$key]['client_state_name']  = GSTStateCodes::where("id",$value['gst_state_code'])->value("state_name");
					$result[$key]['consignee_name']  	= ShippingAddressMaster::where("id",$value['shipping_address_id'])->value("consignee_name");
					$result[$key]['credit_gross_amt']  	= "<font style='color:red;font-weight:bold'>".$CREDIT_AMT."</font>";
					$result[$key]['debit_gross_amt']  	= "<font style='color:green;font-weight:bold'>".$DEBIT_AMT."</font>";

					$totalQty 				= $totalQty + $Quantity;
					$totalGst 				= $totalGst + $GST_AMT;
					$totalGross				= $totalGross + $GROSS_AMT;
					$totalNet 				= $totalNet + $NET_AMT;
					$arrResult[$counter] 	= $result[$key];
					$counter++;

					$totalCrAmt 			= $totalCrAmt + $CREDIT_AMT;
					$totalGrossCrAmt 		=  _FormatNumberV2(round($totalGross)) - $totalCrAmt;
					$totalDbAmt 			= $totalDbAmt + $DEBIT_AMT;
					$totalFinalAmt 			= $totalGrossCrAmt + $totalDbAmt;

					/** SHOW THE REJECTED ROW AS REQUIRED FOR TALLY */
					if ($value['approval_status'] == 2)
					{
						$tmpArray 					= $result[$key];
						$tmpArray['sales_quantity']	= "-"._FormatNumberV2($Quantity);
						$tmpArray['dispatch_qty']	= "-"._FormatNumberV2($DISPATCH_QTY);
						$tmpArray['accepted_qty']	= "-"._FormatNumberV2($Quantity);
						$tmpArray['cgst_amount']	= "-"._FormatNumberV2($CGST_AMT);
						$tmpArray['sgst_amount']	= "-"._FormatNumberV2($SGST_AMT);
						$tmpArray['igst_amount']	= "-"._FormatNumberV2($IGST_AMT);
						$tmpArray['gst_amount']		= "-"._FormatNumberV2($GST_AMT);
						$tmpArray['net_amount']		= "-"._FormatNumberV2($NET_AMT);
						$tmpArray['gross_amount']	= "-"._FormatNumberV2($GROSS_AMT);

						$totalQty 				= ($totalQty - $Quantity);
						$totalGst 				= ($totalGst - $GST_AMT);
						$totalGross				= ($totalGross - $GROSS_AMT);
						$totalNet 				= ($totalNet - $NET_AMT);
						$arrResult[$counter] 	= $tmpArray;
						$counter++;
						unset($tmpArray);
					}
					/** SHOW THE REJECTED ROW AS REQUIRED FOR TALLY */
					/** INVOICE CANCELLED **/
					if ($value['invoice_cancel'] == 1)
					{
						$tmpArray 					= $result[$key];
						$tmpArray['sales_quantity']	= "-"._FormatNumberV2($Quantity);
						$tmpArray['dispatch_qty']	= "-"._FormatNumberV2($DISPATCH_QTY);
						$tmpArray['accepted_qty']	= "-"._FormatNumberV2($Quantity);
						$tmpArray['cgst_amount']	= "-"._FormatNumberV2($CGST_AMT);
						$tmpArray['sgst_amount']	= "-"._FormatNumberV2($SGST_AMT);
						$tmpArray['igst_amount']	= "-"._FormatNumberV2($IGST_AMT);
						$tmpArray['gst_amount']		= "-"._FormatNumberV2($GST_AMT);
						$tmpArray['net_amount']		= "-"._FormatNumberV2($NET_AMT);
						$tmpArray['gross_amount']	= "-"._FormatNumberV2($GROSS_AMT);

						$totalQty 				= ($totalQty - $Quantity);
						$totalGst 				= ($totalGst - $GST_AMT);
						$totalGross				= ($totalGross - $GROSS_AMT);
						$totalNet 				= ($totalNet - $NET_AMT);
						$arrResult[$counter] 	= $tmpArray;
						$counter++;
						unset($tmpArray);
					}
				/** INVOICE CANCELLED **/
				}
				$array['TOTAL_GROSS_AMT'] 		= _FormatNumberV2(round($totalGross));
				$array['TOTAL_NET_AMT'] 		= _FormatNumberV2(round($totalNet));
				$array['TOTAL_GST_AMT'] 		= _FormatNumberV2($totalGst);
				$array['TOTAL_QUANTITY'] 		= _FormatNumberV2($totalQty);
				$array['TOTAL_CREDIT_AMT'] 		= "<font style='color:red;'><b>"._FormatNumberV2($totalCrAmt)."</b></font>";
				$array['TOTAL_DEBIT_AMT'] 		= "<font style='color:green;'><b>"._FormatNumberV2($totalDbAmt)."</b></font>";
				$array['TOTAL_GROSS_CREDIT_AMT']= _FormatNumberV2($totalGrossCrAmt);
				$array['TOTAL_FINAL_AMT']		= _FormatNumberV2($totalFinalAmt);

			}
			$res['total_data'] 	= $array;
			$res['res'] 		= $arrResult;
			return $res;
		}catch(\Exception $e){
			\Log::info("Error ".$e->getMessage()." LINE ".$e->getLine()." FILE ".$e->getFile());
		}
	}

	/** Dispatch Summary Report */
	public static function DispatchReportV3($request)
	{
		$base_location_Ids 	= (isset($request->base_location_id) && !empty($request->base_location_id)) ? $request->base_location_id : array() ;
		if(empty($base_location_Ids)){
			$cityId         = (isset($request->base_location_id) && !empty($request->base_location_id)) ? GetBaseLocationCity($request->base_location_id) : GetBaseLocationCity(Auth()->user()->base_location);
		}
		$res 			= array();
		$array 			= array();
		$table			= new WmDispatch();
		$parameter		= new Parameter();
		$VehicleTbl		= new VehicleMaster();
		$Today          = date('Y-m-d');
		
		################ FOR REGISTER ITEM WISE REPORT ################
		$DISPATCH_TYPE  	= (isset($request->dispatch_type)) ?  $request->dispatch_type : "";
		$VIRTUAL_TARGET  	= (isset($request->virtual_target)) ?  $request->virtual_target : "";
		$INCLUDE_CITY  		= (isset($request->city_id) && !empty($request->city_id)) ?  $request->city_id : "";
		$ACCOUNT  			= (isset($request->account) && !empty($request->account)) ?  $request->account : "";
		$EXCLUDE_CITY  		= (isset($request->exclude_city_id) && !empty($request->exclude_city_id)) ?  $request->exclude_city_id : "";
		$INCLUDE_BILL_MRF  	= (isset($request->inc_bill_from_mrf) && !empty($request->inc_bill_from_mrf)) ?  $request->inc_bill_from_mrf : "";
		$EXCLUDE_BILL_MRF  	= (isset($request->exc_bill_from_mrf) && !empty($request->exc_bill_from_mrf)) ?  $request->exc_bill_from_mrf : "";
		$LoginUserID 		= (isset($request->login_user_id) && !empty($request->login_user_id)) ? $request->login_user_id : 0;
		$CUSTOMER_ID  		= (isset($request->customer_id) && !empty($request->customer_id)) ?  $request->customer_id : "";
		$RELE_MANAGER  		= (isset($request->relationship_manager_id) && !empty($request->relationship_manager_id)) ?  $request->relationship_manager_id : "";
		$COLL_CYCLE_TERM  	= (isset($request->collection_cycle_term) && !empty($request->collection_cycle_term)) ?  $request->collection_cycle_term : "";
		$INCLUDE_BILL_MRF_ARR = array();
		$REQUEST_MAST_MRF 	  = 0;
		if($LoginUserID > 0) {
			if(!empty($INCLUDE_CITY)) {
				$INCLUDE_CITY = explode(",",$INCLUDE_CITY);
			}
			if(!empty($EXCLUDE_CITY)) {
				$EXCLUDE_CITY = explode(",",$EXCLUDE_CITY);
			}
		} else {
			$LoginUserID = Auth()->user()->adminuserid;
		}
		$userAssignBaseData = UserBaseLocationMapping::where("adminuserid",$LoginUserID)->pluck("base_location_id")->toArray();
		$BaseConcat 	= (!empty($userAssignBaseData)) ? implode(",",$userAssignBaseData) : 0;
		$CN_DN_MRF_CON 	= array();

		################ FOR REGISTER ITEM WISE REPORT ################
		$PAID  				= (isset($request->paid) && !empty($request->paid)) ?  $request->paid : "";
		$EWAY_BILL_NO  		= (isset($request->eway_bill_no) && !empty($request->eway_bill_no)) ?  $request->eway_bill_no : "";
		$createdAt 			= ($request->has('created_from') && $request->input('created_from')) ? date("Y-m-d",strtotime($request->input("created_from"))) : "";
		$createdTo 			= ($request->has('created_to') && $request->input('created_to')) ? date("Y-m-d",strtotime($request->input("created_to"))) : "";
		$isFromDispatch 	= ($request->has('is_from_dispatch')) ? $request->is_from_dispatch : "";
		$DISPATCH_WHERE 	= " WHERE wm_dispatch.company_id = ".Auth()->user()->company_id;
		$TRANSFER_WHERE 	= " WHERE wm_transfer_master.company_id = ".Auth()->user()->company_id;
		if($PAID > 0 || $PAID == 1) {
			$PAID = ($PAID < 0) ? 0 : $PAID; 
			$DISPATCH_WHERE .= " AND wm_dispatch.aggregator_dispatch = ".$PAID;
			$TRANSFER_WHERE .= " AND wm_transfer_master.id = 0";
		}
		if($RELE_MANAGER > 0) {
			$DISPATCH_WHERE .= " AND wm_dispatch.relationship_manager_id = ".$RELE_MANAGER;
			$TRANSFER_WHERE .= " AND wm_transfer_master.id = 0";
		}
		if($COLL_CYCLE_TERM > 0) {
			$DISPATCH_WHERE .= " AND wm_dispatch.collection_cycle_term = ".$COLL_CYCLE_TERM;
			$TRANSFER_WHERE .= " AND wm_transfer_master.id = 0";
		}
		if($DISPATCH_TYPE > 0) {
			$DISPATCH_WHERE .= " AND wm_dispatch.dispatch_type = ".$DISPATCH_TYPE;
			$TRANSFER_WHERE .= " AND wm_transfer_master.id = 0";
		}

		if(!empty($EWAY_BILL_NO)) {
			$DISPATCH_WHERE .= " AND wm_dispatch.eway_bill_no = ".$EWAY_BILL_NO;
			$TRANSFER_WHERE .= " AND wm_transfer_master.eway_bill_no = ".$EWAY_BILL_NO;
		}
		if(!empty($base_location_Ids)){
			$BaseConcat 	= implode(",",$base_location_Ids);
			$DISPATCH_WHERE .= " AND wm_department.base_location_id IN (".$BaseConcat.")";
			$TRANSFER_WHERE .= " AND O.base_location_id IN (".$BaseConcat.")";
		}elseif(empty($INCLUDE_CITY) && empty($EXCLUDE_CITY)) {
			$baseIDs = UserBaseLocationMapping::where("adminuserid",$LoginUserID)->pluck("base_location_id")->toArray();
			$BaseConcat 	= implode(",",$baseIDs);
			$DISPATCH_WHERE .= " AND wm_department.base_location_id IN (".$BaseConcat.")";
			$TRANSFER_WHERE .= " AND O.base_location_id IN (".$BaseConcat.")";
		} elseif(!empty($INCLUDE_CITY)) {
			$INCLUDE_CITY 	= implode(",",$INCLUDE_CITY);
			$DISPATCH_WHERE .= " AND wm_department.location_id IN (".$INCLUDE_CITY.")";
			$TRANSFER_WHERE .= " AND O.location_id IN (".$INCLUDE_CITY.")";
		}
		if(!empty($EXCLUDE_CITY)) {
			$EXCLUDE_CITY 	= implode(",",$EXCLUDE_CITY);
			$DISPATCH_WHERE .= " AND wm_department.location_id NOT IN (".$EXCLUDE_CITY.")";
			$TRANSFER_WHERE .= " AND O.location_id IN (".$EXCLUDE_CITY.")";
		}
		if(!empty($INCLUDE_BILL_MRF)) {
			$INCLUDE_BILL_MRF_ARR 	= $INCLUDE_BILL_MRF;
			$CN_DN_MRF_CON 			= array_merge($INCLUDE_BILL_MRF,$CN_DN_MRF_CON);
			$base_location_Ids 		= WmDepartment::whereIn("id",$INCLUDE_BILL_MRF)->pluck('base_location_id')->toArray();
			$BaseConcat 		= implode(",",$base_location_Ids);
			if(is_array($INCLUDE_BILL_MRF)) {
				$INCLUDE_BILL_MRF = implode(",",$INCLUDE_BILL_MRF);
			}
			$DISPATCH_WHERE .= " AND wm_dispatch.bill_from_mrf_id IN ($INCLUDE_BILL_MRF)";
			$TRANSFER_WHERE .= " AND wm_transfer_master.origin_mrf IN ($INCLUDE_BILL_MRF)";
		}

		if($VIRTUAL_TARGET == "0" || $VIRTUAL_TARGET == "1") {
			$DISPATCH_WHERE .= " AND wm_dispatch.virtual_target = $VIRTUAL_TARGET";
		}
		if(!empty($EXCLUDE_BILL_MRF)) {
			$base_location_Ids 	= WmDepartment::whereIn("id",$EXCLUDE_BILL_MRF)->pluck('base_location_id')->toArray();
			$baseIDs 			= UserBaseLocationMapping::where("adminuserid",$LoginUserID)->whereNotIn("base_location_id",$base_location_Ids)->pluck("base_location_id")->toArray();
			$BaseConcat 		= implode(",",$baseIDs);
			if(is_array($EXCLUDE_BILL_MRF)){
				$EXCLUDE_BILL_MRF = implode(",",$EXCLUDE_BILL_MRF);
			}
			$DISPATCH_WHERE .= " AND wm_dispatch.bill_from_mrf_id NOT IN ($EXCLUDE_BILL_MRF)";
			$TRANSFER_WHERE .= " AND wm_transfer_master.origin_mrf NOT IN ($EXCLUDE_BILL_MRF)";
		}

		if($request->has('master_dept_id') && !empty($request->input('master_dept_id'))) {
			$REQUEST_MAST_MRF = $request->master_dept_id;
			array_push($CN_DN_MRF_CON,$request->master_dept_id);
			$base_location_Ids = WmDepartment::where("id",$request->input('master_dept_id'))->pluck('base_location_id')->toArray();
			$DISPATCH_WHERE .= " AND wm_dispatch.master_dept_id =".$request->input('master_dept_id');
			$TRANSFER_WHERE .= " AND wm_transfer_master.origin_mrf=".$request->input('master_dept_id');
		}

		if($request->has('customer_id') && !empty($request->input('customer_id'))) {
			$DISPATCH_WHERE .= " AND wm_dispatch.origin =".$request->input('customer_id');
			$TRANSFER_WHERE .= " AND wm_transfer_master.id=0";
		}

		if($request->has('vehicle_number') && !empty($request->input('vehicle_number'))) {
			$DISPATCH_WHERE .= " AND V.vehicle_number LIKE '%'".$request->input('vehicle_number')."'%'";
			$TRANSFER_WHERE .= " AND V.vehicle_number LIKE '%'".$request->input('vehicle_number')."'%'";
		}
		if($request->has('client_id') && !empty($request->input('client_id'))) {
			$client_name = WmClientMaster::where("id",$request->input('client_id'))->value("client_name");
				
			$DISPATCH_WHERE .= " AND wm_client_master.client_name like '%".$client_name."%'";
			// $DISPATCH_WHERE .= " AND wm_dispatch.client_master_id =".$request->input('client_id');
			$TRANSFER_WHERE .= " AND D.id = ".$request->input('client_id');
		}
		if($request->has('product_id') && !empty($request->input('product_id'))) {
			$DISPATCH_WHERE .= " AND wm_sales_master.product_id =".$request->input('product_id');
			$TRANSFER_WHERE .= " AND wm_transfer_product.product_id =".$request->input('product_id')." and wm_transfer_master.product_type =2";
		}
		if($request->has('challan_no') && !empty($request->input('challan_no'))) {
			$DISPATCH_WHERE .= " AND wm_dispatch.challan_no ='".$request->input('challan_no')."'";
			$TRANSFER_WHERE .= " AND wm_transfer_master.challan_no ='".$request->input('challan_no')."'";
		}
		if($request->has('acknowledgement_date') && !empty($request->acknowledgement_date)) {
			$DISPATCH_WHERE .= " AND wm_dispatch.acknowledgement_date =".date("Y-m-d",strtotime($request->acknowledgement_date));
			$TRANSFER_WHERE .= " AND wm_transfer_master.ack_date =".date("Y-m-d",strtotime($request->acknowledgement_date));
		}
		if($request->has('acknowledgement_no') && !empty($request->input('acknowledgement_no'))) {
			$DISPATCH_WHERE .= " AND wm_dispatch.acknowledgement_no LIKE '%'".$request->input('acknowledgement_no')."'%'";
			$TRANSFER_WHERE .= " AND wm_transfer_master.ack_no LIKE '%'".$request->input('acknowledgement_no')."'%'";
		}
		if($request->has('invoice_generated')) {
			$invoice_generated =  $request->input('invoice_generated');
			if($invoice_generated == "0") {
				$DISPATCH_WHERE .= " AND wm_dispatch.invoice_generated =".$invoice_generated;
			} elseif($invoice_generated == "1") {
				$DISPATCH_WHERE .= " AND wm_dispatch.invoice_generated =".$invoice_generated;
			}
		}

		if($isFromDispatch == "0") {
			$DISPATCH_WHERE .= " AND wm_dispatch.id = 0";
		} elseif($isFromDispatch == "1") {
			$TRANSFER_WHERE .= " AND wm_transfer_master.id = 0";
		}

		if($request->has('invoice_cancel')) {
			$invoice_cancel =  $request->input('invoice_cancel');
			if($invoice_cancel == "0") {
				$DISPATCH_WHERE .= " AND wm_dispatch.invoice_cancel =".$invoice_cancel;
			} elseif($invoice_cancel == "1") {
				$DISPATCH_WHERE .= " AND wm_dispatch.invoice_cancel =".$invoice_cancel;
			}
		}
		if($request->has('status')) {
			$status =  $request->input('status');
			if($status == "0") {
				$DISPATCH_WHERE .= " AND wm_dispatch.approval_status =".$status;
				$TRANSFER_WHERE .= " AND wm_transfer_master.approval_status NOT IN (3,2,1)";
			} elseif($status == "1" || $status == "3") {
				$DISPATCH_WHERE .= " AND wm_dispatch.approval_status =".$status;
				$TRANSFER_WHERE .= " AND wm_transfer_master.approval_status IN(3,".$status.")";
			} elseif($status == "2") {
				$DISPATCH_WHERE .= " AND wm_dispatch.approval_status =".$status;
				$TRANSFER_WHERE .= " AND wm_transfer_master.approval_status IN(".$status.")";
			}
		} else {
			$TRANSFER_WHERE .= " AND wm_transfer_master.approval_status IN (1,3) ";
		}
		if(!empty($createdAt) && !empty($createdTo)){
			$DISPATCH_WHERE .= " AND wm_dispatch.dispatch_date between '".$createdAt." 00:00:00' AND '".$createdTo." 23:59:59'";
			$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$createdAt." 00:00:00' AND '".$createdTo." 23:59:59'";
		}elseif(!empty($createdAt)){
			$DISPATCH_WHERE .= " AND wm_dispatch.dispatch_date between '".$createdAt." 00:00:00' AND '".$createdAt." 23:59:59'";
			$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$createdAt." 00:00:00' AND '".$createdTo." 23:59:59'";
		}elseif(!empty($createdTo)){
			$DISPATCH_WHERE .= " AND wm_dispatch.dispatch_date between '".$createdTo." 00:00:00' AND '".$createdTo." 23:59:59'";
			$TRANSFER_WHERE .= " AND wm_transfer_master.transfer_date between '".$createdAt." 00:00:00' AND '".$createdTo." 23:59:59'";
		}
		/** CHANGED BY KALPAK @SINCE 25/11/2021 */
		$SALES_REPORT_SQL 		= "	SELECT
									wm_dispatch.bill_from_mrf_id,
									wm_dispatch.e_invoice_no,
									wm_dispatch.acknowledgement_no,
									wm_dispatch.acknowledgement_date,
									wm_dispatch.dispatch_type,
									DATE_FORMAT(wm_dispatch.dispatch_date,'%d-%m-%Y') AS Dispatch_Date,
									(
										CASE
											WHEN wm_dispatch.invoice_cancel = 1 THEN 'Cancel'
											WHEN wm_dispatch.approval_status = 0 THEN 'Pending'
											WHEN wm_dispatch.approval_status = 1 THEN 'Approved'
											WHEN wm_dispatch.approval_status = 2 THEN 'Rejected'
										END
									) AS Dispatch_Status,
									wm_dispatch.tcs_amount,
									wm_dispatch.tcs_rate,
									wm_dispatch.approval_status,
									wm_dispatch.id AS dispatch_id,
									wm_dispatch.challan_no,
									wm_dispatch.invoice_generated,
									wm_dispatch.bill_of_lading,
									wm_dispatch.eway_bill_no,
									IF(wm_dispatch.invoice_generated = 0,'No','Yes') AS is_invoice_generated,
									wm_dispatch.invoice_cancel,
									D_GST_STATE_CODES.display_state_code AS destination_state_code,
									wm_dispatch.origin_state_code,
									wm_dispatch.shipping_address_id,
									GST_STATE_CODES.display_state_code AS master_dept_state_code,
									V.vehicle_number,
									V.vehicle_name,
									V.owner_name,
									CONCAT(CM.first_name,' ',CM.last_name) AS dispatch_from,
									wm_client_master.client_name AS client_name,
									wm_client_master.gst_state_code,
									wm_client_master.gstin_no,
									wm_product_master.title,
									wm_product_master.title AS productName,
									'' AS description,
									wm_product_master.hsn_code,
									wm_product_master.net_suit_code,
									wm_product_master.cgst,
									wm_product_master.sgst,
									wm_product_master.igst,
									wm_sales_master.product_id,
									wm_sales_master.cgst_rate,
									wm_sales_master.sgst_rate,
									wm_sales_master.igst_rate,
									wm_sales_master.gross_amount,
									wm_sales_master.net_amount,
									wm_sales_master.gst_amount,
									wm_sales_master.dispatch_product_id,
									wm_sales_master.quantity AS sales_quantity,
									wm_sales_master.rate AS sales_rate,
									wm_department.department_name,
									BILL_FROM.department_name AS bill_from_mrf_name,
									wm_department.location_id,
									PARAM.para_value AS sales_type_name,
									1 AS is_from_dispatch,
									wm_dispatch.company_id,
									2 AS product_type,
									CASE WHEN 1=1 THEN 
									(
										SELECT IF(P_COGS.direct_dispatch = 1,P_COGS.price,P_COGS.avg_price)
										FROM wm_dispatch_sales_product_avg_price as P_COGS
										WHERE P_COGS.dispatch_id = wm_dispatch.id
										AND P_COGS.sales_product_id = wm_sales_master.product_id
										ORDER BY P_COGS.id LIMIT 1
									) END AS cogs,
									CASE WHEN 1=1 THEN 
									(
										SELECT (IF(P_COGS.direct_dispatch = 1,P_COGS.price,P_COGS.avg_price) * wm_sales_master.quantity)
										FROM wm_dispatch_sales_product_avg_price as P_COGS
										WHERE P_COGS.dispatch_id = wm_dispatch.id
										AND P_COGS.sales_product_id = wm_sales_master.product_id
										ORDER BY P_COGS.id LIMIT 1
									) END AS cogs_value,
									wm_dispatch.rent_amt,
									wm_dispatch.rent_gst_amt,
									wm_dispatch.rent_cgst,
									wm_dispatch.rent_sgst,
									wm_dispatch.rent_igst,
									wm_dispatch.total_rent_amt
									FROM `wm_sales_master`
									LEFT JOIN `wm_product_master` ON `wm_sales_master`.`product_id` = `wm_product_master`.`id`
									LEFT JOIN `wm_dispatch` ON `wm_dispatch`.`id` = `wm_sales_master`.`dispatch_id`
									LEFT JOIN `wm_client_master` ON `wm_client_master`.`id` = `wm_dispatch`.`client_master_id`
									LEFT JOIN `parameter` AS `PARAM` ON `wm_dispatch`.`type_of_transaction` = `PARAM`.`para_id`
									LEFT JOIN `wm_department` ON `wm_dispatch`.`master_dept_id` = `wm_department`.`id`
									LEFT JOIN `wm_department` AS `BILL_FROM` ON `wm_dispatch`.`bill_from_mrf_id` = `BILL_FROM`.`id`
									LEFT JOIN `GST_STATE_CODES` AS `GST_STATE_CODES` ON `BILL_FROM`.`gst_state_code_id` = `GST_STATE_CODES`.`id`
									LEFT JOIN `GST_STATE_CODES` AS `D_GST_STATE_CODES` ON wm_dispatch.`destination_state_code` = `D_GST_STATE_CODES`.`id`
									LEFT JOIN `vehicle_master` AS `V` ON `wm_dispatch`.`vehicle_id` = `V`.`vehicle_id`
									LEFT JOIN `appoinment` ON `wm_dispatch`.`appointment_id` = `appoinment`.`appointment_id`
									LEFT JOIN `customer_master` AS `CM` ON `appoinment`.`customer_id` = `CM`.`customer_id`
									$DISPATCH_WHERE";
		$TRANSFER_REPORT_SQL	= "	SELECT
									wm_transfer_master.origin_mrf as bill_from_mrf_id,
									wm_transfer_master.irn as e_invoice_no,
									wm_transfer_master.ack_no as acknowledgement_no,
									wm_transfer_master.ack_date as acknowledgement_date,
									'' as dispatch_type,
									DATE_FORMAT(wm_transfer_master.transfer_date,'%d-%m-%Y') AS Dispatch_Date,
									(
									CASE  WHEN wm_transfer_master.approval_status = 0 THEN 'Pending'
										  WHEN wm_transfer_master.approval_status = 1 THEN 'Approved'
										  WHEN wm_transfer_master.approval_status = 2 THEN 'Rejected'
										  WHEN wm_transfer_master.approval_status = 3 THEN 'Approved'
									END
									) AS Dispatch_Status,
									'0' as tcs_amount,
									'0' as tcs_rate,
									wm_transfer_master.approval_status,
									wm_transfer_master.id AS dispatch_id,
									wm_transfer_master.challan_no,
									1 as invoice_generated,
									NULL as bill_of_lading,
									wm_transfer_master.eway_bill_no,
									'No' AS is_invoice_generated,
									'0' as invoice_cancel,
									D_GST_STATE_CODES.display_state_code AS destination_state_code,
									O.gst_state_code_id AS origin_state_code,
									0 as shipping_address_id,
									GST_STATE_CODES.display_state_code AS master_dept_state_code,
									V.vehicle_number,
									V.vehicle_name,
									V.owner_name,
									'' AS dispatch_from,
									D.department_name as client_name,
									D.gst_state_code_id as gst_state_code,
									D.gst_in as gstin_no,
									NULL AS title,
									NULL AS productName,
									wm_transfer_product.description  AS description,
									NULL AS hsn_code,
									NULL AS net_suit_code,
									wm_transfer_product.cgst AS cgst,
									wm_transfer_product.sgst AS sgst,
									wm_transfer_product.igst AS igst,
									wm_transfer_product.product_id as product_id,
									wm_transfer_product.cgst AS cgst_rate,
									wm_transfer_product.sgst AS sgst_rate,
									wm_transfer_product.igst AS igst_rate,
									(wm_transfer_product.quantity * wm_transfer_product.price) as gross_amount,
									0 as net_amount,
									0 as gst_amount,
									wm_transfer_product.product_id as dispatch_product_id,
									wm_transfer_product.quantity AS sales_quantity,
									wm_transfer_product.price AS sales_rate,
									O.department_name,
									O.department_name AS bill_from_mrf_name,
									D.location_id,
									'Transfer' AS sales_type_name,
									'0' AS is_from_dispatch,
									wm_transfer_master.company_id,
									wm_transfer_master.product_type,
									wm_transfer_product.avg_price AS cogs,
									(wm_transfer_product.avg_price * wm_transfer_product.quantity) AS cogs_value,
									'0' as rent_amt,
									'0' as rent_gst_amt,
									'0' as rent_cgst,
									'0' as rent_sgst,
									'0' as rent_igst,
									'0' as total_rent_amt
									FROM wm_transfer_product
									INNER JOIN wm_transfer_master ON wm_transfer_master.id = wm_transfer_product.transfer_id
									INNER JOIN wm_product_master AS PRO ON wm_transfer_product.product_id = PRO.id
									INNER JOIN wm_department AS D ON wm_transfer_master.destination_mrf = D.id
									INNER JOIN wm_department AS O ON wm_transfer_master.origin_mrf = O.id
									LEFT JOIN location_master AS LD ON D.location_id = LD.location_id
									LEFT JOIN location_master AS LO ON O.location_id = LO.location_id
									LEFT JOIN vehicle_master  as V ON wm_transfer_master.vehicle_id = V.vehicle_id
									LEFT JOIN adminuser AS U1 ON wm_transfer_master.approved_by = U1.adminuserid
									LEFT JOIN adminuser AS U2 ON wm_transfer_master.final_approved_by = U2.adminuserid
									LEFT JOIN `GST_STATE_CODES` AS `GST_STATE_CODES` ON O.`gst_state_code_id` = `GST_STATE_CODES`.`id`
									LEFT JOIN `GST_STATE_CODES` AS `D_GST_STATE_CODES` ON D.`gst_state_code_id` = `D_GST_STATE_CODES`.`id`
									$TRANSFER_WHERE";
		if ($request->is_from_dispatch == 1 || $request->is_from_dispatch == "1") {
			$SQL = $SALES_REPORT_SQL;
		} else if ($request->is_from_dispatch == "0") {
			$SQL = $TRANSFER_REPORT_SQL;
		} else {
			$SQL = "(".$SALES_REPORT_SQL.") UNION ALL (".$TRANSFER_REPORT_SQL.")";
		}
		/** CHANGED BY KALPAK @SINCE 25/11/2021 */
		$result 	= \DB::select($SQL);
		$arrResult 	= array();
		$counter	= 0;
		if(!empty($result))
		{
			$totalQty 					= 0;
			$totalGst 					= 0;
			$totalGross					= 0;
			$totalNet 					= 0;
			$totalGrossCrAmt			= 0;
			$totalGrossDbAmt			= 0;
			$totalFinalAmt 				= 0;
			$totalCrAmt					= 0;
			$totalDbAmt					= 0;
			$totalFrightAmt				= 0;
			$totalFrightGstAmt 			= 0;
			$totalOtherChargesGstAmt	= 0;
			$totalOtherChargesAmt 		= 0;
			$totalDbAmt					= 0;
			$TOTAL_CN_GST_AMT 			= 0;
			$TOTAL_DN_GST_AMT 			= 0;
			$TOTAL_COGS_AMT				= 0;
			$tcsCheck 					= array();
			$rentNdOtherChargeChk 		= array();
			foreach($result as $key => $value)
			{
				$DISPATCH_QTY 		= 0;
				$CGST_AMT 			= 0;
				$SGST_AMT 			= 0;
				$IGST_AMT 			= 0;
				$CGST_RATE 			= 0;
				$SGST_RATE 			= 0;
				$IGST_RATE 			= 0;
				$DESCRIPTION 		= "";
				$CREDIT_AMT 		= 0;
				$DEBIT_AMT 			= 0;
				$CREDIT_GST_AMT 	= 0;
				$DEBIT_GST_AMT 		= 0;
				$IsFromSameState 	= ($value->destination_state_code == $value->master_dept_state_code) ? true : false;
				$Rate 				= (!empty($value->sales_rate)) ? _FormatNumberV2($value->sales_rate) : 0;
				$Quantity 			= (!empty($value->sales_quantity)) ? _FormatNumberV2($value->sales_quantity) : 0 ;
				$result[$key]->dispatch_date = (isset($value->dispatch_date)) ? date("Y-m-d",strtotime($value->dispatch_date)) : "";
				if($value->is_from_dispatch == 1) {
					$INVOICE_DATA 		= WmInvoices::where("dispatch_id",$value->dispatch_id)->orderBy('id','desc')->first();
					$creditNoteTbl 		= new WmInvoicesCreditDebitNotes();
					$creditNoteDtl 		= new WmInvoicesCreditDebitNotesDetails();
					$detailsTbl 		= $creditNoteDtl->getTable();
					$DESTINATION 		= ($INVOICE_DATA) ? $INVOICE_DATA->destination : "";
					$DISPATCH 			= WmDispatchProduct::find($value->dispatch_product_id);
					$CREDIT_AMT 		= WmInvoicesCreditDebitNotesDetails::join($creditNoteTbl->getTable()."  as CRN_TBL","$detailsTbl.cd_notes_id","=","CRN_TBL.id")
											->where("CRN_TBL.dispatch_id",$value->dispatch_id)
											->where("CRN_TBL.notes_type",0)
											->where("CRN_TBL.status",3)
											->where("$detailsTbl.product_id",$value->product_id)
											->where("$detailsTbl.dispatch_product_id",$value->dispatch_product_id)
											->sum("$detailsTbl.revised_gross_amount");
					$DEBIT_AMT 			= WmInvoicesCreditDebitNotesDetails::join($creditNoteTbl->getTable()."  as CRN_TBL","$detailsTbl.cd_notes_id","=","CRN_TBL.id")
											->where("CRN_TBL.dispatch_id",$value->dispatch_id)
											->where("CRN_TBL.notes_type",1)
											->where("CRN_TBL.status",3)
											->where("$detailsTbl.product_id",$value->product_id)
											->where("$detailsTbl.dispatch_product_id",$value->dispatch_product_id)
											->sum("$detailsTbl.revised_gross_amount");
					$CREDIT_GST_AMT 	= WmInvoicesCreditDebitNotesDetails::join($creditNoteTbl->getTable()."  as CRN_TBL","$detailsTbl.cd_notes_id","=","CRN_TBL.id")
											->where("CRN_TBL.dispatch_id",$value->dispatch_id)
											->where("CRN_TBL.notes_type",0)
											->where("CRN_TBL.status",3)
											->where("$detailsTbl.product_id",$value->product_id)
											->where("$detailsTbl.dispatch_product_id",$value->dispatch_product_id)
											->sum("$detailsTbl.revised_gst_amount");
					$DEBIT_GST_AMT 		= WmInvoicesCreditDebitNotesDetails::join($creditNoteTbl->getTable()."  as CRN_TBL","$detailsTbl.cd_notes_id","=","CRN_TBL.id")
											->where("CRN_TBL.dispatch_id",$value->dispatch_id)
											->where("CRN_TBL.notes_type",1)
											->where("CRN_TBL.status",3)
											->where("$detailsTbl.product_id",$value->product_id)
											->where("$detailsTbl.dispatch_product_id",$value->dispatch_product_id)
											->sum("$detailsTbl.revised_gst_amount");
					$TOTAL_CN_GST_AMT 	+= ($CREDIT_GST_AMT > 0) ? _FormatNumberV2($CREDIT_GST_AMT) : 0;
					$TOTAL_DN_GST_AMT 	+= ($DEBIT_GST_AMT > 0) ? _FormatNumberV2($DEBIT_GST_AMT) : 0;
					if($DISPATCH)
					{
						$CGST_RATE 		= $DISPATCH->cgst_rate;
						$SGST_RATE 		= $DISPATCH->sgst_rate;
						$IGST_RATE 		= $DISPATCH->igst_rate;
						$DISPATCH_QTY 	= $DISPATCH->quantity;
						$DESCRIPTION 	= $DISPATCH->description;
					}
					$result[$key]->net_suit_code = $value->net_suit_code;
				} else {
					$CGST_RATE 					= $value->cgst_rate;
					$SGST_RATE 					= $value->sgst_rate;
					$IGST_RATE 					= $value->igst_rate;
					if($value->product_type == 1)
					{
						$PRODUCT_DATA 				= CompanyProductMaster::where("id",$value->product_id)->first();
						$result[$key]->productName 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->name : "";
						$result[$key]->title 		= ($PRODUCT_DATA) ? $PRODUCT_DATA->name : "";
						$result[$key]->hsn_code 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->hsn_code : "";
						$result[$key]->net_suit_code= ($PRODUCT_DATA) ? $PRODUCT_DATA->net_suit_code : "";
						$DISPATCH_QTY 				= $value->sales_quantity;
						$DESCRIPTION 				= $value->description;
					} else {
						$PRODUCT_DATA 				= WmProductMaster::where("id",$value->product_id)->first();
						$result[$key]->productName 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->title : "";
						$result[$key]->title 		= ($PRODUCT_DATA) ? $PRODUCT_DATA->title : "";
						$result[$key]->hsn_code 	= ($PRODUCT_DATA) ? $PRODUCT_DATA->hsn_code : "";
						$result[$key]->net_suit_code= ($PRODUCT_DATA) ? $PRODUCT_DATA->net_suit_code : "";
						$DISPATCH_QTY 				= $value->sales_quantity;
						$DESCRIPTION 				= $value->description;
					}
				}
				if($IsFromSameState) {
					if($Rate > 0) {
						$CGST_AMT 	= ($CGST_RATE > 0) ? (($Quantity * $Rate) / 100) * $CGST_RATE:0;
						$SGST_AMT 	= ($SGST_RATE > 0) ? (($Quantity * $Rate) / 100) *  $SGST_RATE:0;
					}
				} else {
					if($Rate > 0) {
						$IGST_AMT 	= ($IGST_RATE > 0) ? (($Quantity * $Rate) / 100) * $IGST_RATE:0;
					}
				}
				$result[$key]->frieght_amt 			= 0;
				$result[$key]->frieght_gst_amt 		= 0;
				$result[$key]->frieght_net_amt 		= 0;
				if($value->is_from_dispatch == 0) {
					$GROSS_AMT 					= (!empty($value->gross_amount)) 	? $value->gross_amount:0;
					$GST_AMT 					= ($IsFromSameState) ? $CGST_AMT + $SGST_AMT : $IGST_AMT;
					$NET_AMT 					= $GROSS_AMT + $GST_AMT;
					$result[$key]->net_amount 	= _FormatNumberV2($NET_AMT);
					$result[$key]->gst_amount 	= _FormatNumberV2($GST_AMT);
				} else {
					$GST_AMT 					= (!empty($value->gst_amount)) 		? $value->gst_amount:0;
					$NET_AMT 					= (!empty($value->net_amount)) 		? $value->net_amount:0;
					$GROSS_AMT 					= (!empty($value->gross_amount)) 	? $value->gross_amount:0;
					$TCS_AMT 					= (!empty($value->tcs_amount) && !in_array($value->dispatch_id,$tcsCheck)) ? $value->tcs_amount:0;
					$result[$key]->tcs_amount 	= $TCS_AMT;
					$NET_AMT 					= $TCS_AMT + $NET_AMT;
					array_push($tcsCheck,$value->dispatch_id);
				}
				$result[$key]->credit_note_gst_amt 	= $CREDIT_GST_AMT;
				$result[$key]->debit_note_gst_amt 	= $DEBIT_GST_AMT;
				
				$totalFrightGstAmt 					+= ($value->rent_gst_amt > 0) ? _FormatNumberV2($value->rent_gst_amt) : 0;
				$totalFrightAmt 					+= ($value->total_rent_amt > 0) ? _FormatNumberV2($value->total_rent_amt) : 0;
				$Add_charge_gross_amt 				= 0;
				$Add_charge_gst_amt 				= 0;
				$Add_charge_net_amt 				= 0;
				if(!in_array($value->dispatch_id,$rentNdOtherChargeChk))
				{
					$addtionalChargesData 	= InvoiceAdditionalCharges::select(	\DB::raw("SUM(gross_amount) as charges_gross_amount"),
																				\DB::raw("SUM(gst_amount) as charges_gst_amount"),
																				\DB::raw("SUM(net_amount) as charges_net_amount"))
												->where("dispatch_id",$value->dispatch_id)->groupBy("dispatch_id")->get()->toArray();
					$Add_charge_gross_amt 	= (!empty($addtionalChargesData)) ? $addtionalChargesData[0]['charges_gross_amount'] : 0;
					$Add_charge_gst_amt 	= (!empty($addtionalChargesData)) ? $addtionalChargesData[0]['charges_gst_amount'] : 0;
					$Add_charge_net_amt 	= (!empty($addtionalChargesData)) ? $addtionalChargesData[0]['charges_net_amount'] : 0;
					array_push($rentNdOtherChargeChk,$value->dispatch_id);
					$result[$key]->frieght_amt 			= $value->rent_amt;
					$result[$key]->frieght_gst_amt 		= $value->rent_gst_amt;
					$result[$key]->frieght_net_amt 		= $value->total_rent_amt;
					$totalOtherChargesGstAmt	+= _FormatNumberV2($Add_charge_gst_amt);
					$totalOtherChargesAmt 	 	+= _FormatNumberV2($Add_charge_gross_amt);
					$NET_AMT 					+= $value->total_rent_amt;
					$NET_AMT 					+= $Add_charge_net_amt;
				}
				
				$result[$key]->other_charges_amt 		= _FormatNumberV2($Add_charge_gross_amt);
				$result[$key]->other_charges_gst_amt 	= _FormatNumberV2($Add_charge_gst_amt);
				#############COGS############
				$result[$key]->sales_rate 	= _FormatNumberV2($value->sales_rate);
				$result[$key]->cogs 		= _FormatNumberV2($value->cogs);
				$result[$key]->cogs_value 	= _FormatNumberV2($value->cogs_value);
				$TOTAL_COGS_AMT 			+= round($value->cogs_value,2);
				#############COGS############
				
				########## IF DISPATCH TYPE NON RECYCLEBLE THEN NOT COUNT GST ##########
				if($result[$key]->dispatch_type != RECYCLEBLE_TYPE && $value->is_from_dispatch == 1) {
					$NET_AMT 	= $NET_AMT - $GST_AMT;
					$GST_AMT 	= 0;
					$CGST_AMT 	= 0;
					$SGST_AMT 	= 0;
					$IGST_AMT 	= 0;
				}
				########## IF DISPATCH TYPE NON RECYCLEBLE THEN NOT COUNT GST ##########
				$result[$key]->is_from_dispatch 	=  $value->is_from_dispatch;
				$result[$key]->net_suit_code 		=  $value->net_suit_code;
				$result[$key]->sales_quantity 		= _FormatNumberV2($Quantity);
				$result[$key]->dispatch_qty 		= _FormatNumberV2($DISPATCH_QTY);
				$result[$key]->accepted_qty			= _FormatNumberV2($Quantity);
				$result[$key]->cgst_amount 			= _FormatNumberV2($CGST_AMT);
				$result[$key]->sgst_amount 			= _FormatNumberV2($SGST_AMT);
				$result[$key]->igst_amount 			= _FormatNumberV2($IGST_AMT);
				$result[$key]->gst_amount 			= _FormatNumberV2($GST_AMT);
				$result[$key]->net_amount 			= _FormatNumberV2($NET_AMT);
				$result[$key]->gross_amount 		= _FormatNumberV2($GROSS_AMT);
				$result[$key]->product_description 	= $DESCRIPTION;
				$result[$key]->invoice_no 			= (isset($value->challan_no)) ? $value->challan_no : "";
				$result[$key]->consignee_state 		= (isset($INVOICE_DATA) && !empty($INVOICE_DATA)) ? $INVOICE_DATA->shipping_state : "";;
				$result[$key]->client_state_name  	= GSTStateCodes::where("id",$value->gst_state_code)->value("state_name");
				$result[$key]->consignee_name  		= ShippingAddressMaster::where("id",$value->shipping_address_id)->value("consignee_name");
				$result[$key]->credit_gross_amt  	= "<font style='color:red;font-weight:bold'>".$CREDIT_AMT."</font>";
				$result[$key]->debit_gross_amt  	= "<font style='color:green;font-weight:bold'>".$DEBIT_AMT."</font>";
				$totalQty 							= $totalQty + $Quantity;
				$totalGst 							= $totalGst + $GST_AMT;
				$totalGross							= $totalGross + $GROSS_AMT;
				$totalNet 							= $totalNet + $NET_AMT;
				
				$totalCrAmt 						= $totalCrAmt + $CREDIT_AMT;
				$totalGrossCrAmt 					= _FormatNumberV2(round($totalGross)) - $totalCrAmt;
				$totalDbAmt 						= $totalDbAmt + $DEBIT_AMT;
				$totalFinalAmt 						= $totalGrossCrAmt + $totalDbAmt;
				if ($IsFromSameState) {
					$result[$key]->igst_rate 		= 0;
				} else {
					$result[$key]->sgst_rate 		= 0;
					$result[$key]->cgst_rate 		= 0;
				}
				$arrResult[$counter] 				= $result[$key];
				$counter++;
				/** SHOW THE REJECTED ROW AS REQUIRED FOR TALLY */
				if ($value->approval_status == 2)
				{
					$tmpArray 					= clone $result[$key];
					$tmpArray->sales_quantity	= "-"._FormatNumberV2($Quantity);
					$tmpArray->dispatch_qty		= "-"._FormatNumberV2($DISPATCH_QTY);
					$tmpArray->accepted_qty		= "-"._FormatNumberV2($Quantity);
					$tmpArray->cgst_amount		= "-"._FormatNumberV2($CGST_AMT);
					$tmpArray->sgst_amount		= "-"._FormatNumberV2($SGST_AMT);
					$tmpArray->igst_amount		= "-"._FormatNumberV2($IGST_AMT);
					$tmpArray->gst_amount		= "-"._FormatNumberV2($GST_AMT);
					$tmpArray->net_amount		= "-"._FormatNumberV2($NET_AMT);
					$tmpArray->gross_amount		= "-"._FormatNumberV2($GROSS_AMT);
					$totalQty 					= ($totalQty - $Quantity);
					$totalGst 					= ($totalGst - $GST_AMT);
					$totalGross					= ($totalGross - $GROSS_AMT);
					$totalNet 					= ($totalNet - $NET_AMT);
					$arrResult[$counter] 		= $tmpArray;
					$counter++;
					unset($tmpArray);
				}
				/** SHOW THE REJECTED ROW AS REQUIRED FOR TALLY */
				/** INVOICE CANCELLED **/
				if ($value->invoice_cancel == 1)
				{
					$tmpArray 					=clone $result[$key];
					$tmpArray->sales_quantity	= "-"._FormatNumberV2($Quantity);
					$tmpArray->dispatch_qty		= "-"._FormatNumberV2($DISPATCH_QTY);
					$tmpArray->accepted_qty		= "-"._FormatNumberV2($Quantity);
					$tmpArray->cgst_amount		= "-"._FormatNumberV2($CGST_AMT);
					$tmpArray->sgst_amount		= "-"._FormatNumberV2($SGST_AMT);
					$tmpArray->igst_amount		= "-"._FormatNumberV2($IGST_AMT);
					$tmpArray->gst_amount		= "-"._FormatNumberV2($GST_AMT);
					$tmpArray->net_amount		= "-"._FormatNumberV2($NET_AMT);
					$tmpArray->gross_amount		= "-"._FormatNumberV2($GROSS_AMT);
					$totalQty 					= ($totalQty - $Quantity);
					$totalGst 					= ($totalGst - $GST_AMT);
					$totalGross					= ($totalGross - $GROSS_AMT);
					$totalNet 					= ($totalNet - $NET_AMT);
					$arrResult[$counter] 		= $tmpArray;
					$counter++;
					unset($tmpArray);
				}
				/** INVOICE CANCELLED **/
			}
			$TOTAL_CN_AMT = 0;
			$TOTAL_DN_AMT = 0;
			if($request->is_from_dispatch != "0")
			{
				$createdAt 	= (!empty($createdAt)) ? date("Y-m-d",strtotime($createdAt))." ".GLOBAL_START_TIME : "";
				$createdTo 	= (!empty($createdTo)) ? date("Y-m-d",strtotime($createdTo))." ".GLOBAL_END_TIME : "";
				$CN_DN_SQL 	= "	SELECT 
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,0,1) AS TOTAL_MRF_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,0,1) AS TOTAL_MRF_DN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,1,1) AS TOTAL_PAID_CN_GROSS_AMT,
								getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,1,1) AS TOTAL_PAID_DN_GROSS_AMT";
				####### IF BILL FROM MRF AND MRF IS FILTER THEN ITS DISPLAY MRF WISE CN DN COUNT ######
				if(!empty($CN_DN_MRF_CON)) {
					$BaseConcat = implode(",",$CN_DN_MRF_CON);
					$CN_DN_SQL 	= "	SELECT 
									getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,0,0) AS TOTAL_MRF_CN_GROSS_AMT,
									getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,0,0) AS TOTAL_MRF_DN_GROSS_AMT,
									getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',0,1,0) AS TOTAL_PAID_CN_GROSS_AMT,
									getCreditDebitNoteAmount('".$createdAt."','".$createdTo."','".$BaseConcat."',1,1,0) AS TOTAL_PAID_DN_GROSS_AMT";
				}
				$CN_DN_AMT_RES 	= \DB::select($CN_DN_SQL);
				$TOTAL_DN_AMT 	= isset($CN_DN_AMT_RES[0]->TOTAL_MRF_DN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_MRF_DN_GROSS_AMT:0;
				$TOTAL_CN_AMT 	= isset($CN_DN_AMT_RES[0]->TOTAL_MRF_CN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_MRF_CN_GROSS_AMT:0;
				if (!in_array($REQUEST_MAST_MRF,$INCLUDE_BILL_MRF_ARR)) {
					if($PAID == 1) {
						$TOTAL_DN_AMT 	= isset($CN_DN_AMT_RES[0]->TOTAL_PAID_DN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_PAID_DN_GROSS_AMT:0;
						$TOTAL_CN_AMT 	= isset($CN_DN_AMT_RES[0]->TOTAL_PAID_CN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_PAID_CN_GROSS_AMT:0;
					} else {
						$TOTAL_DN_AMT 	+= isset($CN_DN_AMT_RES[0]->TOTAL_PAID_DN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_PAID_DN_GROSS_AMT:0;
						$TOTAL_CN_AMT 	+= isset($CN_DN_AMT_RES[0]->TOTAL_PAID_CN_GROSS_AMT)?$CN_DN_AMT_RES[0]->TOTAL_PAID_CN_GROSS_AMT:0;
					}
				}
			} 
			

			$totalFinalAmt 						= (($totalGross + $TOTAL_DN_AMT) - $TOTAL_CN_AMT);
			$array['TOTAL_GROSS_AMT'] 			= _FormatNumberV2(round($totalGross));
			$array['TOTAL_NET_AMT'] 			= _FormatNumberV2(round($totalNet));
			$array['TOTAL_GST_AMT'] 			= _FormatNumberV2($totalGst);
			$array['TOTAL_QUANTITY'] 			= _FormatNumberV2($totalQty);
			$array['TOTAL_FREIGHT_GST_AMT'] 	= _FormatNumberV2($totalFrightGstAmt);
			$array['TOTAL_FREIGHT_AMT'] 		= _FormatNumberV2($totalFrightAmt);
			$array['TOTAL_OTHER_GST_AMT'] 		= _FormatNumberV2($totalOtherChargesGstAmt);
			$array['TOTAL_OTHER_AMT'] 			= _FormatNumberV2($totalOtherChargesAmt);
			$array['TOTAL_CREDIT'] 				= "<font style='color:red;font-weight:bold'>"._FormatNumberV2($totalCrAmt)."</font>";
			$array['TOTAL_DEBIT'] 				= "<font style='color:green;font-weight:bold'>"._FormatNumberV2($totalDbAmt)."</font>";
			$array['TOTAL_CREDIT_AMT'] 			= "<font style='color:red;'><b>"._FormatNumberV2($TOTAL_CN_AMT)."</b></font>";
			$array['TOTAL_DEBIT_AMT'] 			= "<font style='color:green;'><b>"._FormatNumberV2($TOTAL_DN_AMT)."</b></font>";
			$array['TOTAL_CREDIT_NOTE_GST_AMT'] = _FormatNumberV2($TOTAL_CN_GST_AMT);
			$array['TOTAL_DEBIT_NOTE_GST_AMT']  = _FormatNumberV2($TOTAL_DN_GST_AMT);
			$array['TOTAL_GROSS_CREDIT_AMT']	= _FormatNumberV2($totalGrossCrAmt);
			$array['TOTAL_FINAL_AMT']			= _FormatNumberV2($totalFinalAmt);
			$array['TOTAL_COGS_AMT']			= _FormatNumberV2($TOTAL_COGS_AMT);
		}
		$res['total_data'] 	= $array;
		$res['res'] 		= $arrResult;
		// $res['SQL'] 		= $SQL;
		return $res;
	}

	/*
	Use 	: Sales Register Party wise Report
	Author 	: Axay Shah
	Date 	: 24 March,2020
	*/
	public static function SalesRegisterPartyWiseReport($request)
	{
		try{
			$cityId         = (isset($request->base_location_id) && !empty($request->base_location_id)) ? GetBaseLocationCity($request->base_location_id) : GetBaseLocationCity(Auth()->user()->base_location);
			$res 			= array();
			$array 			= array();
			$table			= new WmDispatch();
			$parameter		= new Parameter();
			$Today          = date('Y-m-d');
			################ FOR REGISTER ITEM WISE REPORT ################
			$INCLUDE_CITY  	= (isset($request->city_id) && !empty($request->city_id)) ?  $request->city_id : "";
			$EXCLUDE_CITY  	= (isset($request->exclude_city_id) && !empty($request->exclude_city_id)) ?  $request->exclude_city_id : "";
			$LoginUserID 	= (isset($request->login_user_id) && !empty($request->login_user_id)) ? $request->login_user_id : 0;


			if($LoginUserID > 0){
				if(!empty($INCLUDE_CITY)){
					$INCLUDE_CITY = explode(",",$INCLUDE_CITY);
				}
				if(!empty($EXCLUDE_CITY)){
					$EXCLUDE_CITY = explode(",",$EXCLUDE_CITY);
				}
			}else{
				$LoginUserID = Auth()->user()->adminuserid;
			}


			################ FOR REGISTER ITEM WISE REPORT ################

			$createdAt 		= ($request->has('created_from') && $request->input('created_from')) ? date("Y-m-d",strtotime($request->input("created_from"))) : "";
			$createdTo 		= ($request->has('created_to') && $request->input('created_to')) ? date("Y-m-d",strtotime($request->input("created_to"))) : "";
			$data 	= WmDispatch::select(
					DB::raw("DATE_FORMAT(wm_dispatch.dispatch_date,'%d-%m-%Y') as Dispatch_Date"),
					DB::raw("IF(wm_dispatch.approval_status = 1,'Approved',
								IF(wm_dispatch.approval_status = 2,'Rejected','Pending')) as Dispatch_Status"),
					"wm_dispatch.id as dispatch_id",
					"wm_dispatch.challan_no",
					"wm_dispatch.invoice_generated",
					"wm_dispatch.bill_of_lading",
					"wm_dispatch.invoice_generated",
					\DB::raw("IF(wm_dispatch.invoice_generated = 0,'No','Yes') as is_invoice_generated"),
					"wm_dispatch.invoice_cancel",
					"wm_dispatch.destination_state_code",
					"wm_dispatch.origin_state_code",
					"wm_dispatch.shipping_address_id",
					"wm_client_master.client_name",
					"wm_client_master.gst_state_code",
					"wm_client_master.gstin_no",
					"PARAM.para_value as sales_type_name",
					"wm_sales_master.cgst_rate",
					"wm_sales_master.sgst_rate",
					"wm_sales_master.igst_rate",
					\DB::Raw("sum(wm_sales_master.gross_amount) as total_gross_amount"),
					\DB::Raw("sum(wm_sales_master.net_amount) as total_net_amount"),
					\DB::Raw("sum(wm_sales_master.gst_amount) as total_gst_amount"),
					\DB::Raw("sum(wm_sales_master.quantity) as total_quantity"),
					"wm_sales_master.dispatch_product_id",
					"wm_department.department_name",
					"wm_department.location_id"
				)
				->leftjoin("wm_sales_master","wm_dispatch.id","=","wm_sales_master.dispatch_id")
				->leftjoin($parameter->getTable()." as PARAM","wm_dispatch.type_of_transaction","=","PARAM.para_id")
				->join("wm_client_master","wm_client_master.id","=","wm_dispatch.client_master_id")
				->leftjoin("wm_department","wm_dispatch.master_dept_id","=","wm_department.id");

			if(empty($INCLUDE_CITY) && empty($EXCLUDE_CITY)){
				$cityId = UserCityMpg::userAssignCity($LoginUserID,true);
				$data->whereIn("wm_department.location_id",$cityId);
			}elseif(!empty($INCLUDE_CITY)){
				$data->whereIn("wm_department.location_id",$INCLUDE_CITY);
			}
			if(!empty($EXCLUDE_CITY)){

				$data->whereNotIn("wm_department.location_id",$EXCLUDE_CITY);
			}

			if($request->has('master_dept_id') && !empty($request->input('master_dept_id')))
			{
				$data->where("wm_dispatch.master_dept_id",$request->input('master_dept_id'));
			}
			if($request->has('vehicle_number') && !empty($request->input('vehicle_number')))
			{
				$data->where("V.vehicle_number","like","%".$request->input('vehicle_number')."%");
			}
			if($request->has('client_id') && !empty($request->input('client_id')))
			{
				$data->where("wm_dispatch.client_master_id",$request->input('client_id'));
			}

			if($request->has('product_id') && !empty($request->input('product_id')))
			{
				$data->where("wm_sales_master.product_id",$request->input('product_id'));
			}

			if($request->has('challan_no') && !empty($request->input('challan_no')))
			{
				$data->where("wm_dispatch.challan_no",$request->input('challan_no'));
			}
			if($request->has('type_of_transaction') && !empty($request->input('type_of_transaction')))
			{
				$data->where("wm_dispatch.type_of_transaction",$request->input('type_of_transaction'));
			}

			if($request->has('challan_no') && !empty($request->input('challan_no')))
			{
				$data->where("wm_dispatch.challan_no",$request->input('challan_no'));
			}
			if($request->has('challan_no') && !empty($request->input('challan_no')))
			{
				$data->where("wm_dispatch.challan_no",$request->input('challan_no'));
			}

			if($request->has('invoice_generated'))
			{
				$invoice_generated =  $request->input('invoice_generated');
				if($invoice_generated == "0"){
					$data->where("wm_dispatch.invoice_generated",$invoice_generated);
				}elseif($invoice_generated == "1"){
					$data->where("wm_dispatch.invoice_generated",$invoice_generated);
				}
			}
			if($request->has('invoice_cancel'))
			{
				$invoice_cancel =  $request->input('invoice_cancel');
				if($invoice_cancel == "0"){
					$data->where("wm_dispatch.invoice_cancel",$invoice_cancel);
				}elseif($invoice_cancel == "1"){
					$data->where("wm_dispatch.invoice_cancel",$invoice_cancel);
				}
			}
			if($request->has('status'))
			{
				$status =  $request->input('status');
				if($status == "0"){
					$data->where("wm_dispatch.approval_status",$status);
				}elseif($status == "1" || $status == "2"){
					$data->where("wm_dispatch.approval_status",$status);
				}


			}

			if(!empty($createdAt) && !empty($createdTo)){
				$data->whereBetween("wm_dispatch.dispatch_date",[$createdAt." 00:00:00",$createdTo." 23:59:59"]);
			}elseif(!empty($createdAt)){
				$data->whereBetween("wm_dispatch.dispatch_date",[$createdAt." 00:00:00",$createdAt." 23:59:59"]);
			}elseif(!empty($createdTo)){
				$data->whereBetween("wm_dispatch.dispatch_date",[$createdTo." 00:00:00",$createdTo." 23:59:59"]);
			}
			// LiveServices::toSqlWithBinding($data);
			$data->groupBy("wm_dispatch.id");
			$result = $data->get()->toArray();

			if(!empty($result)){
				$totalQty 	= 0 ;
				$totalGst 	= 0 ;
				$totalGross	= 0 ;
				$totalNet 	= 0 ;
				$CGST_RATE 	= 0;
				$SGST_RATE 	= 0;
				$IGST_RATE 	= 0;
				foreach($result as $key => $value){
					$DISPATCH_QTY 		= 0;
					$CGST_AMT 			= 0;
					$SGST_AMT 			= 0;
					$IGST_AMT 			= 0;

					$IsFromSameState 	= ($value['destination_state_code'] == $value['origin_state_code']) ? true : false;

					if($IsFromSameState) {
						if($value['total_gst_amount'] > 0){
							$AMOUNT 	=  ($value['total_gst_amount'] / 2);
							$CGST_AMT 	=  $AMOUNT;
							$SGST_AMT 	=  $AMOUNT;
						}
					}else{
						$IGST_AMT 	= $value['total_gst_amount'];
					}


					$INVOICE_DATA 						= WmInvoices::where("dispatch_id",$value['dispatch_id'])->orderBy('id','desc')->first();
					$DESTINATION 						= ($INVOICE_DATA) ? $INVOICE_DATA->destination : "";
					$result[$key]['invoice_no'] 		= ($INVOICE_DATA) ? $INVOICE_DATA->invoice_no : "";
					$result[$key]['consignee_state'] 	= ($INVOICE_DATA) ? $INVOICE_DATA->shipping_state : "";;
					$result[$key]['client_state_name']  = GSTStateCodes::where("id",$value['gst_state_code'])->value("state_name");
					$CONSIGNEE_NAME = ShippingAddressMaster::where("id",$value['shipping_address_id'])->value("consignee_name");
					$result[$key]['consignee_name'] = (empty($CONSIGNEE_NAME)) ? $value['client_name'] : "";

					$GST_AMT 	= (!empty($value['total_gst_amount'])) ? $value['total_gst_amount'] 	: 0 ;
					$NET_AMT 	= (!empty($value['total_net_amount'])) ? $value['total_net_amount'] 	: 0 ;
					$GROSS_AMT 	= (!empty($value['total_gross_amount'])) ? $value['total_gross_amount'] : 0 ;
					$Quantity 	= (!empty($value['total_quantity'])) ? $value['total_quantity'] : 0 ;

					$result[$key]['cgst_amount'] =  $CGST_AMT;
					$result[$key]['sgst_amount'] =  $SGST_AMT;
					$result[$key]['igst_amount'] =  $IGST_AMT;
					$CGST_RATE 	+= $CGST_AMT;
					$SGST_RATE 	+= $SGST_AMT;
					$IGST_RATE 	+= $IGST_AMT;
					$totalQty 	= $totalQty + $Quantity ;
					$totalGst 	= $totalGst + $GST_AMT ;
					$totalGross	= $totalGross + $GROSS_AMT ;
					$totalNet 	= $totalNet + $NET_AMT  ;
				}
				$array['TOTAL_GROSS_AMT'] 	= _FormatNumberV2($totalGross);
				$array['TOTAL_NET_AMT'] 	= _FormatNumberV2($totalNet);
				$array['TOTAL_GST_AMT'] 	= _FormatNumberV2($totalGst);
				$array['TOTAL_QUANTITY'] 	= _FormatNumberV2($totalQty);
				$array['TOTAL_SGST'] 		= _FormatNumberV2($SGST_RATE);
				$array['TOTAL_CGST'] 		= _FormatNumberV2($CGST_RATE);
				$array['TOTAL_IGST'] 		= _FormatNumberV2($IGST_RATE);
			}
			$res['total_data'] 	= $array;
			$res['res'] 		= $result;
			return $res;
		}catch(\Exception $e){
			\Log::info("Error".$e->getMessage()." LINE".$e->getLine()." FILE".$e->getFile());
		}
	}

	/*
	Use     : Upload Document for EPR Track
	Author  : Axay Shah
	Date 	: 17 June 2020
	*/
	public static function UpdateDocumentForEPR($request,$dispatchID=0)
	{

		$status 		= 0;
		$VEHICLE_ID 	=  (isset($request->vehicle_id) && !empty($request->vehicle_id)) ?  $request->vehicle_id : 0;
		$RC_BOOK_NO 	=  (isset($request->rc_book_no) && !empty($request->rc_book_no)) ?  $request->rc_book_no : "";
		
		$DISPATCH_ID 	=  (isset($request->dispatch_id) && !empty($request->dispatch_id)) ?  $request->dispatch_id : $dispatchID;
		$BILLT 			=  (isset($request->epr_billt_no) && !empty($request->epr_billt_no)) ?  $request->epr_billt_no : "";
		$WAYBRIDGE 		=  (isset($request->epr_waybridge_no) && !empty($request->epr_waybridge_no)) ?  $request->epr_waybridge_no : "";
		$CHALLAN 		=  (isset($request->epr_challan_no) && !empty($request->epr_challan_no)) ?  $request->epr_challan_no : "";
		$EWAYBILL 		=  (isset($request->eway_bill_no) && !empty($request->eway_bill_no)) ?  $request->eway_bill_no : "";
		$TRANS_INV_NO 	=  (isset($request->transporter_invoice_no) && !empty($request->transporter_invoice_no)) ?  $request->transporter_invoice_no : "";
		$UNLOADING_SLIP =  (isset($request->unloading_slip_no) && !empty($request->unloading_slip_no)) ?  $request->unloading_slip_no : "";
		$UNLOADING_TARE =  (isset($request->unloading_tare_weight) && !empty($request->unloading_tare_weight)) ?  $request->unloading_tare_weight : "";
		$UNLOADING_GROSS =  (isset($request->unloading_gross_weight) && !empty($request->unloading_gross_weight)) ?  $request->unloading_gross_weight : "";
		$UNLOADING_DATE =  (isset($request->unloading_date) && !empty($request->unloading_date)) ?  date("Y-m-d",strtotime($request->unloading_date)) : "";
		$WEIGHMENT_NO 	=  (isset($request->weighment_no) && !empty($request->weighment_no)) ? $request->weighment_no  : "";
		$BRAND_IMG_CNT 	=  (isset($request->branding_image_count) && !empty($request->branding_image_count)) ? $request->branding_image_count  : 0;
		$DEMURRAGE_REMARK =  (isset($request->demurrage_remarks) && !empty($request->demurrage_remarks)) ? $request->demurrage_remarks  : "";
		$DEMURRAGE 		=  (isset($request->demurrage) && !empty($request->demurrage)) ? $request->demurrage  : 0;
		$waybridge_cnt 	=  (isset($request->epr_way_bridge_count) && !empty($request->epr_way_bridge_count)) ?  $request->epr_way_bridge_count : 0;
		$epr_billt_count=  (isset($request->epr_billt_count) && !empty($request->epr_billt_count)) ?  $request->epr_billt_count : 0;
		$epr_eway_count =  (isset($request->epr_eway_count) && !empty($request->epr_eway_count)) ?  $request->epr_eway_count : 0;
		$transporter_invoice_count 	=  (isset($request->transporter_invoice_count) && !empty($request->transporter_invoice_count)) ?  $request->transporter_invoice_count : 0;
		$unloading_slip_count 		=  (isset($request->unloading_slip_count) && !empty($request->unloading_slip_count)) ?  $request->unloading_slip_count : 0;
		
		$GET_IMAGES 	=  self::find($DISPATCH_ID);
		########### NEW DEVELOPMENT AS PER COUNT ###############
		if($waybridge_cnt > 0){
			WmDispatchMediaMaster::where("dispatch_id",$DISPATCH_ID)->where("media_type",PARA_WAYBRIDGE)->delete();
			for($i=0; $i < $waybridge_cnt;$i++){
				if( $request->hasFile("epr_way_bridge_".$i)) {
					$filedName 	= "epr_way_bridge_".$i;
					\Log::info("FILED NAME".$filedName);
					$ID 		= self::FnUploadDispatchDocument($request,$filedName,Auth()->user()->company_id,"",PARA_WAYBRIDGE);
					WmDispatch::where("id",$DISPATCH_ID)->update(["epr_waybridge_slip_id"=>$ID]);
					$status = 1;
				}
			}
		}
		\Log::info("###########  AFTER CONDITIONO ");
		if($epr_billt_count > 0){
			WmDispatchMediaMaster::where("dispatch_id",$DISPATCH_ID)->where("media_type",PARA_BILLT)->delete();
			for($i=0; $i< $epr_billt_count;$i++){
				if( $request->hasFile("epr_billt_".$i)) {
					$filedName 	= "epr_billt_".$i;
					$ID 		= self::FnUploadDispatchDocument($request,$filedName,Auth()->user()->company_id,"",PARA_BILLT);
					WmDispatch::where("id",$DISPATCH_ID)->update(["epr_billt_media_id"=>$ID]);
					$status = 1;
				}
			}
		}
		if($epr_eway_count > 0){
			WmDispatchMediaMaster::where("dispatch_id",$DISPATCH_ID)->where("media_type",PARA_EWAY_BILL)->delete();
			for($i=0; $i < $epr_eway_count;$i++){
				if( $request->hasFile("epr_eway_".$i)) {
					$filedName 	= "epr_eway_".$i;
					$ID 		= self::FnUploadDispatchDocument($request,$filedName,Auth()->user()->company_id,"",PARA_EWAY_BILL);
					WmDispatch::where("id",$DISPATCH_ID)->update(["epr_ewaybill_media_id"=>$ID]);
					$status = 1;
				}
			}
		}
		if($transporter_invoice_count > 0){
			WmDispatchMediaMaster::where("dispatch_id",$DISPATCH_ID)->where("media_type",PARA_TRANSPORTER_INV)->delete();
			for($i=0; $i < $transporter_invoice_count;$i++){
				if( $request->hasFile("transporter_invoice_".$i)) {
					$filedName 	= "transporter_invoice_".$i;
					$ID 		= self::FnUploadDispatchDocument($request,$filedName,Auth()->user()->company_id,"",PARA_TRANSPORTER_INV);
					WmDispatch::where("id",$DISPATCH_ID)->update(["transporter_invoice_media_id"=>$ID]);
					$status = 1;
				}
			}
		}
		if($unloading_slip_count > 0){
			WmDispatchMediaMaster::where("dispatch_id",$DISPATCH_ID)->where("media_type",PARA_UNLOADING_SLIP)->delete();
			for($i=0; $i < $unloading_slip_count;$i++){
				if( $request->hasFile("unloading_slip_".$i)) {
					$filedName 	= "unloading_slip_".$i;
					$ID 		= self::FnUploadDispatchDocument($request,$filedName,Auth()->user()->company_id,"",PARA_UNLOADING_SLIP);
					WmDispatch::where("id",$DISPATCH_ID)->update(["unloading_slip_media_id"=>$ID]);
					$status = 1;
				}
			}
		}
		########### NEW DEVELOPMENT AS PER COUNT ###############
		if( $request->hasFile("epr_challan")) {
			$MEDIAIDS 	= (($GET_IMAGES) && isset($GET_IMAGES->epr_challan_media_id) && !empty($GET_IMAGES->epr_challan_media_id)) ? $GET_IMAGES->epr_challan_media_id : 0;
			WmDispatchMediaMaster::where("id",$MEDIAIDS)->delete();
			$filedName = "epr_challan";
			$ID = self::FnUploadDispatchDocument($request,$filedName,Auth()->user()->company_id);
			WmDispatch::where("id",$DISPATCH_ID)->update(["epr_challan_media_id"=>$ID]);
			$status = 1;
		}
		if( $request->hasFile("epr_eway")) {
			$MEDIAIDS 	= (($GET_IMAGES) && isset($GET_IMAGES->epr_ewaybill_media_id) && !empty($GET_IMAGES->epr_ewaybill_media_id)) ? $GET_IMAGES->epr_ewaybill_media_id : 0;
			WmDispatchMediaMaster::where("id",$MEDIAIDS)->delete();
			$filedName = "epr_eway";
			$ID = self::FnUploadDispatchDocument($request,$filedName,Auth()->user()->company_id);
			WmDispatch::where("id",$DISPATCH_ID)->update(["epr_ewaybill_media_id"=>$ID]);
			$status = 1;
		}
		if( $request->hasFile("transporter_invoice")) {
			$MEDIAIDS 	= (($GET_IMAGES) && isset($GET_IMAGES->transporter_invoice_media_id) && !empty($GET_IMAGES->transporter_invoice_media_id)) ? $GET_IMAGES->transporter_invoice_media_id : 0;
			WmDispatchMediaMaster::where("id",$MEDIAIDS)->delete();
			$filedName = "transporter_invoice";
			$ID = self::FnUploadDispatchDocument($request,$filedName,Auth()->user()->company_id);
			WmDispatch::where("id",$DISPATCH_ID)->update(["transporter_invoice_media_id"=>$ID]);
			$status = 1;
		}
		if( $request->hasFile("unloading_slip")) {
			$MEDIAIDS 	= (($GET_IMAGES) && isset($GET_IMAGES->epr_ewaybill_media_id) && !empty($GET_IMAGES->epr_ewaybill_media_id)) ? $GET_IMAGES->epr_ewaybill_media_id : 0;
			WmDispatchMediaMaster::where("id",$MEDIAIDS)->delete();
			$filedName = "unloading_slip";
			$ID = self::FnUploadDispatchDocument($request,$filedName,Auth()->user()->company_id);
			WmDispatch::where("id",$DISPATCH_ID)->update(["unloading_slip_media_id"=>$ID]);
			$status = 1;
		}
		if($BRAND_IMG_CNT > 0){
			$MEDIAIDS = WmDispatchBrandingImgMapping::GetImageMediaIds("dispatch_id",$DISPATCH_ID);
			WmDispatchMediaMaster::whereIn("id",$MEDIAIDS)->delete();
			WmDispatchBrandingImgMapping::where("dispatch_id",$DISPATCH_ID)->delete();
			for($i = 0 ;$i < $BRAND_IMG_CNT;$i++){
				if( $request->hasFile("branding_image_".$i)) {
					$filedName = "branding_image_".$i;
					$ID = self::FnUploadDispatchDocument($request,$filedName,Auth()->user()->company_id);
					WmDispatchBrandingImgMapping::AddDispatchMedia($DISPATCH_ID,$ID);
					$status = 1;
				}
			}
		}
		if(!empty($DEMURRAGE) && !empty($DEMURRAGE_REMARK)){
			TransporterDetailsMaster::where("id",$GET_IMAGES->transporter_po_id)->update(["demurrage_remarks" => $DEMURRAGE_REMARK,"demurrage" => $DEMURRAGE]);
		}
		WmDispatch::where("id",$DISPATCH_ID)->update([	
			"epr_challan_no"			=>$CHALLAN,
			"epr_billt_no"				=>$BILLT,
			"epr_waybridge_no"			=>$WAYBRIDGE,
			"eway_bill_no"				=>$EWAYBILL,
			"unloading_slip_no"			=>$UNLOADING_SLIP,
			"transporter_invoice_no"	=>$TRANS_INV_NO,
			"unloading_tare_weight"		=>$UNLOADING_TARE,
			"unloading_gross_weight"	=>$UNLOADING_GROSS,
			"unloading_date"			=>$UNLOADING_DATE,
			"weighment_no"				=>$WEIGHMENT_NO
		]);
		if(!empty($EWAYBILL)){
			WmInvoices::where("dispatch_id",$DISPATCH_ID)->where("invoice_status","!=",1)->update(["eway_bill"=>$EWAYBILL]);
		}
		if($request->hasFile("rc_book")) {
			$cityId 					= VehicleMaster::where("vehicle_id",$VEHICLE_ID)->value("city_id");
			$request['vehicle_id']    	=  $VEHICLE_ID;
			$request['document_type'] 	=  RC_BOOK_ID;
			$request['document_name'] 	=  $RC_BOOK_NO;
			$request['document_note'] 	=  $RC_BOOK_NO;
			$request['city_id'] 	  	=  $cityId;
			VehicleDocument::where("vehicle_id",$VEHICLE_ID)->where("document_type",RC_BOOK_ID)->delete();
			VehicleDocument::UpdateDocFromDispatch($request);
		}else{
			$DOC_ID = VehicleDocument::where(["vehicle_id"=>$VEHICLE_ID,"document_type"=>RC_BOOK_ID])->orderBy("id")->value("id");
			VehicleDocument::where("id",$DOC_ID)->where("vehicle_id",$VEHICLE_ID)->update(array("document_name"=>$RC_BOOK_NO,"document_note"=>$RC_BOOK_NO));
		}
		return $DISPATCH_ID;
	}
	/*
	Use     : Upload Document for EPR Track comman function
	Author  : Axay Shah
	Date 	: 17 June 2020
	*/
	public static function FnUploadDispatchDocument($REQUEST,$FILED_NAME="image",$COMPANY_ID=0,$PATH=PATH_DISPATCH,$media_type=0)
	{
		$DISPATCH_ID 	= (isset($REQUEST->dispatch_id) && !empty($REQUEST->dispatch_id)) ? $REQUEST->dispatch_id : 0;
		$PATH_COMPANY 	= PATH_IMAGE."/".PATH_COMPANY.'/'.$COMPANY_ID."/";
		$FILE 			= $REQUEST->file($FILED_NAME);
		$EXTENSTION 	= $FILE->getClientOriginalExtension();
		$ID 			= 0;
		if(!is_dir(public_path($PATH_COMPANY).$PATH)) {
			mkdir(public_path($PATH_COMPANY).$PATH,0777,true);
		}

		if($EXTENSTION != CONVERT_EXT_PDF) {
			$ORIGIN 	= $FILED_NAME.time().'.'.$FILE->getClientOriginalExtension();
			$IMG_NAME 	= RESIZE_PRIFIX.$ORIGIN;
			$IMG     	= Image::make($FILE->getRealPath());
			$IMG->resize(RESIZE_HIGHT, RESIZE_WIDTH, function ($constraint) {
				$constraint->aspectRatio();
			})->save(public_path($PATH_COMPANY).$PATH.'/'.$IMG_NAME);
			$FILE->move(public_path($PATH_COMPANY).$PATH.'/', $ORIGIN);
			$ID = WmDispatchMediaMaster::AddDispatchMedia($DISPATCH_ID,$ORIGIN,$IMG_NAME,$PATH_COMPANY.$PATH,$media_type);
		} else {
			$ORIGIN 	= $FILED_NAME.time().'.'.$FILE->getClientOriginalExtension();
			$IMG_NAME 	= RESIZE_PRIFIX.$ORIGIN;
			$FILE->move(public_path($PATH_COMPANY).$PATH.'/', $ORIGIN);
			$ID 		= WmDispatchMediaMaster::AddDispatchMedia($DISPATCH_ID,$ORIGIN,$IMG_NAME,$PATH_COMPANY.$PATH,$media_type);





			// $NAME 		= $FILED_NAME.time();
			// $ORIGIN 	= $NAME.'.'.$EXTENSTION;
			// $FILE->move(public_path($PATH_COMPANY).$PATH.'/', $ORIGIN);
			// $FULL_PATH 	= public_path($PATH_COMPANY).$PATH.'/'.$ORIGIN;

			// $SOURCEFILE 		= $FULL_PATH;
			// $DESTINATIONFILE 	= public_path($PATH_COMPANY).$PATH.'/'.$NAME.".jpg";
			// ######### NEW LOGIC FOR QUALITY IMPORVMENT AS PER EPR REQUIREMENT - 09/02/2022 
			//  $imagick = new \Imagick();
			//  $imagick->setResolution(300, 300);
			// // Reads image from PDF
			// $imagick->readImage($SOURCEFILE);
			//     $imagick->setImageFormat('jpg');
			//     $imagick->setImageCompression(imagick::COMPRESSION_JPEG); 
			//     $imagick->setImageCompressionQuality(100);
			//  // Writes an image or image sequence Example- converted-0.jpg, converted-1.jpg
			//  $imagick->writeImages($DESTINATIONFILE, false);
			// // $CONVERTPHP 		= "/var/www/vhosts/nepra.co.in/v2-api.letsrecycle.co.in/pdftoimg.php";
			// // $COMMAND 			= "/opt/plesk/php/7.1/bin/php ".$CONVERTPHP." ".$SOURCEFILE." ".$DESTINATIONFILE;
			// // $last_line 			= system($COMMAND,$retval);
			// if (file_exists($DESTINATIONFILE)) {
			// 	$ID = WmDispatchMediaMaster::AddDispatchMedia($DISPATCH_ID,basename($DESTINATIONFILE),basename($DESTINATIONFILE),$PATH_COMPANY."/".$PATH);
			// 	if(File::exists($FULL_PATH)) File::delete($FULL_PATH);
			// } else {
			// 	$verifyimage 	= true;
			// 	$counter 		= 0;
			// 	while ($verifyimage) {
			// 		$DESTINATIONFILE = public_path($PATH_COMPANY).$PATH.'/'.$NAME."-".$counter.".jpg";
			// 		if (file_exists($DESTINATIONFILE)) {
			// 			$ID = WmDispatchMediaMaster::AddDispatchMedia($DISPATCH_ID,basename($DESTINATIONFILE),basename($DESTINATIONFILE),$PATH_COMPANY."/".$PATH);
			// 		} else {
			// 			$verifyimage = false;
			// 		}
			// 		$counter++;
			// 	}
			// 	if(File::exists($FULL_PATH)) File::delete($FULL_PATH);
			// }
		}
		return $ID;
	}
	################# EPR TRACK CODE ################

	/*
	Use 	: Dispatch Data send To EPR
	Author 	: Axay Shah
	Date 	: 15 April,2020
	*/
	public static function SendDataToEPR($request)
	{
		$WmDispatchProductTbl 	= new WmDispatchProduct();
		$ProductTbl 			= new WmProductMaster();
		$Vehicle				= new VehicleMaster();
		$Client 				= new WmClientMaster();
		$CustomerMasterTbl 		= new CustomerMaster();
		$WmProcessMasterTbl 	= new WmProcessMaster();
		$WmDispatchPlanTbl 		= new WmDispatchPlan();
		$Department 			= new WmDepartment();
		$SalesTbl				= new WmSalesMaster();
		$Parameter 				= new Parameter();
		$Location 				= new LocationMaster();
		$EPR 					= new LrEprMappingMaster();
		$WmDispatchProduct 		= $WmDispatchProductTbl->getTable();
		$CustomerMaster 		= $CustomerMasterTbl->getTable();
		$Sales 					= $SalesTbl->getTable();
		$Product				= $ProductTbl->getTable();
		$self 					= (new static)->getTable();
		$AdminUser 				= new AdminUser();
		$Admin 					= $AdminUser->getTable();
		$Today          		= date('Y-m-d');
		$OriginDetail 			= array();
		$DestinationDetail 		= array();
		$TOTAL_QTY 				= 0;
		$TOTAL_GROSS 			= 0;
		$TOTAL_NET 				= 0;
		$TOTAL_GST 				= 0;
		$RAW 					= array();
		$RESPONSE 				= array();
		$data 	= self::select(
				"$self.challan_no",
				"$self.id",
				"$self.company_id",
				"$self.dispatch_date",
				"$self.origin",
				"$self.origin_city",
				"$self.shipping_address",
				"$self.shipping_address_id",
				"$self.from_mrf",
				"$self.destination",
				"$self.destination_city",
				"$self.driver_name",
				"$self.driver_mob_no",
				"$self.epr_challan_no",
				"$self.epr_billt_no",
				"$self.epr_waybridge_no",
				"$self.eway_bill_no",
				"$self.epr_challan_media_id",
				"$self.epr_billt_media_id",
				"$self.epr_waybridge_slip_id",
				"$self.epr_ewaybill_media_id",
				"$self.unloading_slip_media_id",
				"$self.unloading_slip_no",
				"$self.unloading_tare_weight",
				"$self.unloading_gross_weight",
				"$self.total_rent_amt",
				"$self.tare_weight",
				"$self.gross_weight",
				"$self.transporter_po_id",
				"$self.weighment_no",
				"$self.unloading_date",
				"$self.dispatch_type",
				\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
				\DB::raw("IF($self.dispatch_type = ".RECYCLEBLE_TYPE.",'1','2') as epr_recycler_type"),
				\DB::raw("IF($self.dispatch_type = ".RECYCLEBLE_TYPE.",'1','2') as material_type"),
				"PARAM.para_value AS dispatch_material_type",
				"MRF.department_name",
				"MRF.address AS mrf_address",
				"MRF.gst_in AS mrf_gst_in",
				"MRF.net_suit_code AS mrf_net_suit_code",
				"MRF.code AS mrf_lr_code",
				"V.vehicle_id",
				"V.vehicle_number",
				"V.vehicle_empty_weight",
				"V.owner_name",
				"CLIENT.address as destination_address",
				"CLIENT.client_name as destination_name",
				"CLIENT.gstin_no as destination_gst_in",
				"CLIENT.pincode",
				"CLIENT.net_suit_code as destination_ns_code",
				"CLIENT.code as client_lr_code",
				"LOC.city as mrf_city_name",
				"LOC1.city as destination_city_name",
				"EPR.epr_track_id"
				)->with(["DispatchSalesData" => function($q) use($Product,$Sales){
					$q->join($Product." as PRO",$Sales.".product_id","=","PRO.id");
					$q->select("$Sales.*","PRO.title AS product_name","PRO.hsn_code");
				}])
		->leftjoin($Parameter->getTable()." AS PARAM","$self.dispatch_type","=","PARAM.para_id")
		->leftjoin($Vehicle->getTable()." AS V","$self.vehicle_id","=","V.vehicle_id")
		->leftjoin($Client->getTable()." AS CLIENT","$self.destination","=","CLIENT.id")
		->leftjoin($Department->getTable()." AS MRF","$self.master_dept_id","=","MRF.id")
		->leftjoin($Location->getTable()." AS LOC","MRF.location_id","=","LOC.location_id")
		->leftjoin($Location->getTable()." AS LOC1","$self.destination_city","=","LOC1.location_id")
		->leftjoin($Admin." AS U1","$self.created_by","=","U1.adminuserid")
		->leftjoin($EPR->getTable()." AS EPR","$self.id","=","EPR.dispatch_id");
		if(!empty($request->dispatch_id)){
			$data->where("$self.id",$request->dispatch_id);
		}
		$data->where("approval_status",1);
		$RES 	= $data->orderBy("id","ASC")->first();
		if(!$RES){
			return $RAW;
		}
		$RAW = $RES->toArray();

		if(!empty($RAW)){
			$DISPATCH_IMAGES_ARR 	= array();
			/** NOT REQUIRED TO SEND TO EPR */
			$FLEXI_CNT = WmDispatchProduct::whereIn("product_id",FLEXI_PRODUCT_ARRAY)->where("dispatch_id",$RAW['id'])->count();
			if(!empty($RAW['epr_challan_media_id'])) {
				$CHALLAN_MEDIAS = WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")
				->where("id",$RAW['epr_challan_media_id'])
				->get();
				if(!empty($CHALLAN_MEDIAS)) {
					foreach ($CHALLAN_MEDIAS as $CHALLAN_MEDIA) {
						$CHALLANMEDIA['type'] 		=  CHALLAN_TYPE;
						$CHALLANMEDIA['image_name'] =  $CHALLAN_MEDIA->image_name;
						$CHALLANMEDIA['image_url'] 	=  url('/')."/".$CHALLAN_MEDIA->image_path."/".$CHALLAN_MEDIA->image_name;
						array_push($DISPATCH_IMAGES_ARR,$CHALLANMEDIA);
					}
				}
			}
			
			/** NOT REQUIRED TO SEND TO EPR */
			if(!empty($RAW['epr_billt_media_id'])) {

				$BILLT_MEDIAS = WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")->where("dispatch_id",$RAW['id'])->where("media_type",PARA_BILLT)->get();
				if(!empty($BILLT_MEDIAS)) {
					foreach ($BILLT_MEDIAS as $BILLT_MEDIA) {
						$BILLTMEDIA['type'] 			=  BILL_TEE_TYPE;
						$BILLTMEDIA['image_name'] 		=  $BILLT_MEDIA->image_name;
						$BILLTMEDIA['image_url'] 		=  url('/')."/".$BILLT_MEDIA->image_path."/".$BILLT_MEDIA->image_name;
						array_push($DISPATCH_IMAGES_ARR,$BILLTMEDIA);
					}
				}
			}
			if(!empty($RAW['epr_waybridge_slip_id'])) {
				$WAYBRIDGE_MEDIAS = WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")->where("dispatch_id",$RAW['id'])->where("media_type",PARA_WAYBRIDGE)->get();
				if(!empty($WAYBRIDGE_MEDIAS)) {
					foreach ($WAYBRIDGE_MEDIAS as $WAYBRIDGE_MEDIA) {
						$WAYBRIDGEMEDIA['type'] 			=  WAY_BRIDGE_TYPE;
						$WAYBRIDGEMEDIA['image_name'] 		=  $WAYBRIDGE_MEDIA->image_name;
						$WAYBRIDGEMEDIA['image_url'] 		=  url('/')."/".$WAYBRIDGE_MEDIA->image_path."/".$WAYBRIDGE_MEDIA->image_name;
						array_push($DISPATCH_IMAGES_ARR,$WAYBRIDGEMEDIA);
					}
				}
			}
			if(!empty($RAW['epr_ewaybill_media_id'])) {

				$EWAYBILL_MEDIAS = WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")->where("dispatch_id",$RAW['id'])->where("media_type",PARA_EWAY_BILL)->get();
				if(!empty($EWAYBILL_MEDIAS)) {
					// prd($EWAYBILL_MEDIAS);
					foreach ($EWAYBILL_MEDIAS as $EWAYBILL_MEDIA) {
						$EWAYBILLMEDIA['type'] 			=  EWAY_BILL_TYPE;
						$EWAYBILLMEDIA['image_name'] 	=  $EWAYBILL_MEDIA->image_name;
						$EWAYBILLMEDIA['image_url'] 	=  url('/')."/".$EWAYBILL_MEDIA->image_path."/".$EWAYBILL_MEDIA->image_name;
						array_push($DISPATCH_IMAGES_ARR,$EWAYBILLMEDIA);
					}
				}
			}
			if(!empty($RAW['unloading_slip_media_id'])) {
				$UNLOADING_MEDIAS = WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","dispatch_id")->where("dispatch_id",$RAW['id'])->where("media_type",PARA_UNLOADING_SLIP)->get();
				if(!empty($UNLOADING_MEDIAS)) {
					foreach ($UNLOADING_MEDIAS as $UNLOADING_MEDIA) {
						$UNLOADING_MEDIA['type'] 		=  CHALLAN_TYPE;
						$UNLOADING_MEDIA['image_name'] 	=  $UNLOADING_MEDIA->image_name;
						$UNLOADING_MEDIA['image_url'] 	=  url('/')."/".$UNLOADING_MEDIA->image_path."/".$UNLOADING_MEDIA->image_name;
						array_push($DISPATCH_IMAGES_ARR,$UNLOADING_MEDIA);
					}
				}
			}
			if(!empty($RAW['transporter_invoice_media_id'])) {
				$TRANSPORTER_INV_MEDIAS = WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")->where("dispatch_id",$RAW['id'])->where("media_type",PARA_TRANSPORTER_INV)->get();
				if(!empty($TRANSPORTER_INV_MEDIAS)) {
					foreach ($TRANSPORTER_INV_MEDIAS as $TRANSPORTER_MEDIA) {
						$TRANSPORTER_MEDIA['type'] 			=  TRANSPORTER_INV_TYPE;
						$TRANSPORTER_MEDIA['image_name'] 	=  $TRANSPORTER_MEDIA->image_name;
						$TRANSPORTER_MEDIA['image_url'] 	=  url('/')."/".$TRANSPORTER_MEDIA->image_path."/".$TRANSPORTER_MEDIA->image_name;
						array_push($DISPATCH_IMAGES_ARR,$TRANSPORTER_MEDIA);
					}
				}
			}
			
			######## RC BOOK DOCUMENT DATA #############
			######## RC BOOK DOCUMENT DATA #############
			$RC_BOOK_NO 	= "";
			$RC_DATA_MEDIAS = VehicleDocument::where("vehicle_id",$RAW['vehicle_id'])
							->where("document_type",RC_BOOK_ID)
							->orderby('id','desc')
							->first();
			if(!empty($RC_DATA_MEDIAS)) {
				$RCBOOK_MEDIA 					= array();
				$url 							= $RC_DATA_MEDIAS['document_file'];
				$uriSegments 					= explode("/", parse_url($url, PHP_URL_PATH));
				$RC_BOOK_NO 					=  $RC_DATA_MEDIAS['document_name'];
				$RCBOOK_MEDIA['type'] 			=  6;
				$RCBOOK_MEDIA['image_name'] 	=  array_pop($uriSegments);;
				$RCBOOK_MEDIA['image_url'] 		=  $RC_DATA_MEDIAS['document_file'];
				
				array_push($DISPATCH_IMAGES_ARR,$RCBOOK_MEDIA);
			}
			/** GENERATE BILL OF SUPPLY */
			$InvoiceId 			= WmInvoices::where("dispatch_id",$request->dispatch_id)->value('id');
			$PATH_TO_COPY 		= public_path("/").PATH_IMAGE."/".PATH_COMPANY."/".$RAW['company_id']."/".PATH_DISPATCH;
			$PDF_NAME			= $PATH_TO_COPY."/invoice_".$InvoiceId.".pdf";
			$IMG_NAME			= $PATH_TO_COPY."/invoice_".$InvoiceId.".jpg";
			$data       		= WmInvoices::GetById($InvoiceId);
			$pdf        		= PDF::loadView('pdf.one',compact('data'));
			$pdf->setPaper("A4", "potrait");
			$pdf->stream("one");
			$pdf->save($PDF_NAME);
			$BILL_OF_SUPPLY['image_url'] 	=  url('/')."/".PATH_IMAGE."/".PATH_COMPANY."/".$RAW['company_id']."/".PATH_DISPATCH."/invoice_".$InvoiceId.".pdf";
			$BILL_OF_SUPPLY['type'] 		=  BILL_OF_SUPPLY;
			$BILL_OF_SUPPLY['image_name'] 	=  "invoice_".$InvoiceId.".pdf";
			array_push($DISPATCH_IMAGES_ARR,$BILL_OF_SUPPLY);
			$CONVERT_PDF = false;
			if($CONVERT_PDF){
				if (file_exists($PDF_NAME))
				{
					$CONVERTPHP 		= "/var/www/vhosts/nepra.co.in/v2-api.letsrecycle.co.in/pdftoimg.php";
					$COMMAND 			= "/opt/plesk/php/7.1/bin/php ".$CONVERTPHP." ".$PDF_NAME." ".$IMG_NAME;
					$last_line 			= system($COMMAND,$retval);
					if (file_exists($IMG_NAME))
					{
						$BILL_OF_SUPPLY['type'] 			=  BILL_OF_SUPPLY;
						$BILL_OF_SUPPLY['image_name'] 		=  basename($IMG_NAME);
						$BILL_OF_SUPPLY['image_url'] 		=  url('/')."/".PATH_IMAGE."/".PATH_COMPANY."/".$RAW['company_id']."/".PATH_DISPATCH."/".basename($IMG_NAME);
						array_push($DISPATCH_IMAGES_ARR,$BILL_OF_SUPPLY);
						@unlink($PDF_NAME);
					} else {
						$verifyimage 	= true;
						$counter 		= 0;
						while ($verifyimage) {
							$IMG_NAME = $PATH_TO_COPY."/invoice_".$InvoiceId."-".$counter.".jpg";
							if (file_exists($IMG_NAME)) {
								$BILL_OF_SUPPLY['type'] 			=  BILL_OF_SUPPLY;
								$BILL_OF_SUPPLY['image_name'] 		=  basename($IMG_NAME);
								$BILL_OF_SUPPLY['image_url'] 		=  url('/')."/".PATH_IMAGE."/".PATH_COMPANY."/".$RAW['company_id']."/".PATH_DISPATCH."/".basename($IMG_NAME);
								array_push($DISPATCH_IMAGES_ARR,$BILL_OF_SUPPLY);
							} else {
								$verifyimage = false;
							}
							$counter++;
						}
						@unlink($PDF_NAME);
					}
				}
			}
			/** GENERATE BILL OF SUPPLY */
			/** SEND DISPATCH BRANDING IMAGE TO EPR */
			$BRANDING_IMG_IDS 	= WmDispatchBrandingImgMapping::GetImageMediaIds($request->dispatch_id);
			$BRANDING_INV_DATA 	= array();
			if(!empty($BRANDING_IMG_IDS)){
				$BRANDING_INV_DATA 	= WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")->whereIn("id",$BRANDING_IMG_IDS)->get();
				if(!empty($BRANDING_INV_DATA)) {
					foreach ($BRANDING_INV_DATA AS $BRANDING_KEY => $BRANDING_INV_MEDIA) {
						$BRANDING_INV_DATA[$BRANDING_KEY]['image_name'] 	=  $BRANDING_INV_MEDIA->image_name;
						$BRANDING_INV_DATA[$BRANDING_KEY]['image_url'] 		=  url('/')."/".$BRANDING_INV_MEDIA->image_path."/".$BRANDING_INV_MEDIA->image_name;
					}
				}
			}
			$RAW['dispatch_images'] = $DISPATCH_IMAGES_ARR;
			if(!empty($RAW['dispatch_sales_data'])) {
				foreach($RAW['dispatch_sales_data'] as $raw) {
					$TOTAL_QTY 		+= (!empty($raw['quantity'])) ? _FormatNumberV2($raw['quantity']) : 0;
					$TOTAL_GROSS 	+= (!empty($raw['gross_amount'])) ? _FormatNumberV2($raw['gross_amount']) : 0;
					$TOTAL_NET 		+= (!empty($raw['net_amount'])) ? _FormatNumberV2($raw['net_amount']) : 0;
					$TOTAL_GST 		+= (!empty($raw['gst_amount'])) ? _FormatNumberV2($raw['gst_amount']) : 0;
				}
			}
			$NET_WEIGHT  			=  $TOTAL_QTY;
			$ORIGIN_NAME 			=  $RAW['department_name'];
			$ORIGIN_ADDRESS 		=  $RAW['mrf_address'];
			$ORIGIN_GST_NO 			=  $RAW['mrf_gst_in'];
			$ORIGIN_CITY 			=  $RAW['mrf_city_name'];
			$ORIGIN_NET_SUIT 		=  $RAW['mrf_net_suit_code'];
			$ORIGIN_LR_CODE 		=  $RAW['mrf_lr_code'];

			if($RAW['from_mrf'] == "N") {
				$CUSTOMER 				= CustomerMaster::find($RAW['origin']);
				if($CUSTOMER) {
					$CustomerCity 		= LocationMaster::where("location_id",$CUSTOMER->city)->first();
					$ORIGIN_NAME 		= $CUSTOMER->first_name." ".$CUSTOMER->last_name;
					$ORIGIN_ADDRESS 	= $CUSTOMER->address1." ".$CUSTOMER->address2;
					$ORIGIN_GST_NO 		= $CUSTOMER->gst_no;
					$ORIGIN_CITY 		= isset($CustomerCity->city)?$CustomerCity->city:"";
					$ORIGIN_NET_SUIT 	= isset($CUSTOMER->net_suit_code)?$CUSTOMER->net_suit_code:""; 
					$ORIGIN_LR_CODE 	= isset($CUSTOMER->code)?$CUSTOMER->code:""; 
				}
			}
			$ORIGIN_STATE 			= LocationMaster::find($RAW['origin_city'])->getstate;
			$DESTINATION_STATE 		= LocationMaster::find($RAW['destination_city'])->getstate;

			$OriginDetail['origin_state_name'] 				= ($ORIGIN_STATE) ? $ORIGIN_STATE->state_name : "";
			$OriginDetail['origin'] 						= $RAW['origin'];
			$OriginDetail['origin_name'] 					= $ORIGIN_NAME;
			$OriginDetail['origin_address'] 				= $ORIGIN_ADDRESS;
			$OriginDetail['origin_gst_in'] 					= $ORIGIN_GST_NO;
			$OriginDetail['origin_city_name'] 				= $ORIGIN_CITY;
			$OriginDetail['origin_ns_code'] 				= $ORIGIN_NET_SUIT;
			$OriginDetail['origin_lr_code'] 				= $ORIGIN_LR_CODE;

			$DestinationDetail['destination'] 				= $RAW['destination'];
			$DestinationDetail['destination_name'] 			= $RAW['destination_name'];
			$DestinationDetail['destination_address'] 		= $RAW['destination_address'];
			$DestinationDetail['destination_city'] 			= $RAW['destination_city_name'];
			$DestinationDetail['destination_state_name'] 	= ($DESTINATION_STATE) ? $DESTINATION_STATE->state_name : "";
			$DestinationDetail['destination_gst'] 			= $RAW['destination_gst_in'];
			$DestinationDetail['destination_ns_code'] 		= $RAW['destination_ns_code'];


			$CONSIGNEE_NAME 		= $RAW['destination_name'];
			$SHIPPING_ADDRESS_ID 	= "";
			$SHIPPING_ADDRESS 		= $RAW['destination_address'];
			$SHIPPING_CITY 			= $RAW['destination_city_name'];
			$SHIPPING_STATE 		= $DestinationDetail['destination_state_name'];
			$SHIPPING_PINCODE 		= $RAW['pincode'];
			$SHIPPING_NET_SUIT 		= "";
			$SHIPPING_GST 			= "";
			if(!empty($RAW['shipping_address_id'])){
				$SHIPPING = ShippingAddressMaster::where("id",$RAW['shipping_address_id'])->first();
				if($SHIPPING){
					$CONSIGNEE_NAME 		= ($RAW['dispatch_type'] == NON_RECYCLEBLE_TYPE) ? $CONSIGNEE_NAME : $SHIPPING['consignee_name'];
					$SHIPPING_ADDRESS_ID 	= $SHIPPING['id'];
					$SHIPPING_ADDRESS 		= $SHIPPING['shipping_address'];
					$SHIPPING_CITY 			= $SHIPPING['city'];
					$SHIPPING_STATE 		= $SHIPPING['state'];
					$SHIPPING_PINCODE 		= $SHIPPING['pincode'];
					$SHIPPING_NET_SUIT 		= $SHIPPING['address_ns_code'];
					$SHIPPING_GST 			= strtoupper(strtolower($SHIPPING['gst_no']));
				}
			}

			$ShippingDetail['consignee_name'] 				= $CONSIGNEE_NAME;
			$ShippingDetail['shipping_address_id'] 			= $SHIPPING_ADDRESS_ID;
			$ShippingDetail['shipping_address'] 			= $SHIPPING_ADDRESS;
			$ShippingDetail['shipping_city'] 				= $SHIPPING_CITY;
			$ShippingDetail['shipping_state'] 				= $SHIPPING_STATE;
			$ShippingDetail['shipping_pincode']				= $SHIPPING_PINCODE;
			$ShippingDetail['shipping_gst']					= $SHIPPING_GST;
			$ShippingDetail['shipping_ns_code']				= (!empty($SHIPPING_NET_SUIT)) ? $SHIPPING_NET_SUIT : "";
			$ShippingDetail['shipping_lr_code']				= $RAW['client_lr_code'];

			$TRANSPORTER_NAME 		= "";
			$TRANSPORTER_NS_CODE 	= "";
			$TRANSPORTER_LR_CODE 	= "";
			$TRANSPORTER_COST 		= 0;
			if(isset($RAW['transporter_po_id']) && !empty($RAW['transporter_po_id'])){
				$TRANSPORTER_COST 		= TransporterDetailsMaster::where("id",$RAW['transporter_po_id'])->value('rate');
				$TRANSPORTER_DATA 		= TransporterDetailsMaster::find($RAW['transporter_po_id'])->TransporterData;
				$TRANSPORTER_NAME 		= (!empty($TRANSPORTER_DATA)) 	? $TRANSPORTER_DATA->name : "";
				$TRANSPORTER_NS_CODE 	= (!empty($TRANSPORTER_DATA)) 	? $TRANSPORTER_DATA->net_suit_code : "";
				$TRANSPORTER_LR_CODE 	= (!empty($TRANSPORTER_DATA)) 	? $TRANSPORTER_DATA->code : "";
			}

			$TranspoterDetail['owner_name'] 				= $TRANSPORTER_NAME;
			$TranspoterDetail['transporter_lr_code'] 		= $TRANSPORTER_LR_CODE;
			$TranspoterDetail['address'] 					= "";
			$TranspoterDetail['transporter_ns_code'] 		= $TRANSPORTER_NS_CODE;
			

			$VehicleDetail['vehicle_number'] 				= $RAW['vehicle_number'];
			$VehicleDetail['vehicle_id'] 					= $RAW['vehicle_id'];
			$DriverDetail['driver_name'] 					= $RAW['driver_name'];
			$DriverDetail['driver_mob_no'] 					= $RAW['driver_mob_no'];

			$RESPONSE['id'] 								= $RAW['id'];
			$RESPONSE['rc_book_no'] 						= $RC_BOOK_NO;
			$RESPONSE['transporter_cost'] 					= ($TRANSPORTER_COST > 0) ? (float)$TRANSPORTER_COST : 0;
			$RESPONSE['created_by_name'] 					= $RAW['created_by_name'];
			$RESPONSE['epr_billt_no'] 						= $RAW['epr_billt_no'];
			$RESPONSE['epr_waybridge_no'] 					= $RAW['epr_waybridge_no'];
			$RESPONSE['weighment_no'] 						= $RAW['weighment_no'];
			$RESPONSE['eway_bill_no'] 						= $RAW['eway_bill_no'];
			$RESPONSE['total_rent_amt'] 					= $RAW['total_rent_amt'];
			$RESPONSE['dispatch_material_type'] 			= $RAW['dispatch_material_type'];
			$RESPONSE['epr_track_id'] 						= (!empty($RAW['epr_track_id'])) ? $RAW['epr_track_id'] : 0;
			$RESPONSE['dispatch_date'] 						= date("Y-m-d H:i:s",strtotime($RAW['dispatch_date']));
			$RESPONSE['epr_recycler_type'] 					= $RAW['epr_recycler_type'];
			$RESPONSE['material_type'] 						= ($FLEXI_CNT > 0) ? EPR_FLEXI_ID : $RAW['material_type'];
			$RESPONSE['unloading_slip_no'] 					= $RAW['unloading_slip_no'];
			$RESPONSE['unloading_gross_weight'] 			= (!empty($RAW['unloading_gross_weight'])) ? _FormatNumberV2($RAW['unloading_gross_weight']) : 0;
			$RESPONSE['unloading_tare_weight'] 				= (!empty($RAW['unloading_tare_weight'])) ? _FormatNumberV2($RAW['unloading_tare_weight']) : 0;
			$RESPONSE['unloading_net_weight'] 				= _FormatNumberV2($RESPONSE['unloading_gross_weight'] - $RESPONSE['unloading_tare_weight']);
			$RESPONSE['unloading_date'] 					= (!empty($RAW['unloading_date']) && $RAW['unloading_date'] != "0000-00-00") ? $RAW['unloading_date'] : null;
			$RESPONSE['invoice_no'] 						= $RAW['challan_no'];
			$RESPONSE['gross_weight'] 						= _FormatNumberV2($RAW['gross_weight']);
			$RESPONSE['tare_weight'] 						= _FormatNumberV2($RAW['tare_weight']);
			$RESPONSE['net_weight'] 						= _FormatNumberV2($NET_WEIGHT);
			$RESPONSE['dispatch_images'] 					= $RAW['dispatch_images'];
			$RESPONSE['origin_details'][] 					= $OriginDetail;
			$RESPONSE['destination_details'][] 				= $DestinationDetail;
			$RESPONSE['shipping_details'][] 				= $ShippingDetail;
			$RESPONSE['Transporter_details'][] 				= $TranspoterDetail;
			$RESPONSE['vehicle_details'][] 					= $VehicleDetail;
			$RESPONSE['driver_details'][] 					= $DriverDetail;
			$RESPONSE['dispatch_branding_images'] 			= $BRANDING_INV_DATA;
		}
		// prd($RESPONSE);
		return $RESPONSE;
	}
	/*
	Use 	: Call EPR URL
	Author 	: Axay Shah
	Date 	: 15 April,2020
	*/
	public static function CallEPRUrl()
	{
		// return false;
		$self 					= (new static)->getTable();
		$EPRTbl					= new LrEprMappingMaster();
		$EPR 					= $EPRTbl->getTable();
		$Client 				= new WmClientMaster();
		$Date 					= date('Y-m-d',strtotime("-1 days"));
		$startDate 				= EPR_START_DATE." ".GLOBAL_START_TIME;
		$endDate 				= $Date." ".GLOBAL_END_TIME;
		$startFromInvoice 		= "2022-02-02 00:00:00";
		$DispatchIDs 			= array();

		$GET_LAST_DISPATCH_ID 	= self::select(
									"$EPR.dispatch_id",
									"$self.id",
									"$self.challan_no",
									"$self.epr_challan_media_id",
									"$self.epr_challan_no")
									->leftjoin("$EPR","$self.id","=","$EPR.dispatch_id")
									->leftjoin($Client->getTable()." AS CLIENT","$self.destination","=","CLIENT.id")
									->where("$self.dispatch_type",NON_RECYCLEBLE_TYPE)
									->where("CLIENT.gstin_no","!=","")
									->whereNotNull("$self.epr_billt_media_id")
									->whereNotNull("CLIENT.gstin_no")
									->whereNotNull("$self.epr_waybridge_slip_id")
									->whereBetween("$self.dispatch_date",[$startDate,$endDate])
									->orderBy("id","ASC");
		$GET_LAST_DISPATCH_ID->where(function($query) use ($EPR) {
			$query->whereNull("$EPR.dispatch_id")->orWhere("$EPR.process","=",0);
		});
		if (!empty($DispatchIDs)) {
			$GET_LAST_DISPATCH_ID->whereIn("$self.id",$DispatchIDs);
		}
		$EPR_SQL = LiveServices::toSqlWithBinding($GET_LAST_DISPATCH_ID,true);
		echo "\r\n EPR_SQL :: ".$EPR_SQL."\r\n";
		$DISPATCH_ROWS = $GET_LAST_DISPATCH_ID->get();
		// prd($DISPATCH_ROWS);
		if(!empty($DISPATCH_ROWS))
		{
			foreach($DISPATCH_ROWS AS $RAW)
			{
				$INSERTED_ID 			= 0;
				$CHALLAN_MEDIA 			= $RAW->epr_challan_media_id;
				$CHALLAN_NO 			= $RAW->epr_challan_no;
				$request 				= new Request();
				$request->dispatch_id 	= $RAW->id;
				$data 					= WmDispatch::SendDataToEPR($request);
				if(!empty($data))
				{
					echo "\r\n Dispatch ID ::".$data['id']." -- \r\n";
					$array 				= $data;
					$IsExisting			= LrEprMappingMaster::where("dispatch_id",$array['id'])->first();
					if (isset($IsExisting->id) && !empty($IsExisting->id)) {
						$INSERTED_ID = $IsExisting->id;
					} else {
						$add 				= new LrEprMappingMaster();
						$add->dispatch_id 	= $array['id'];
						$add->request_data 	= json_encode($array);
						if($add->save()) {
							$INSERTED_ID 	= $add->id;
						}
					}
					$url 			= EPR_URL;
					$client 		= new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
					$response 		= $client->request('POST',$url,['form_params'=>[json_encode($array)]]);
					$response 		= $response->getBody()->getContents();
					$res 			= json_decode($response,true);
					if(!empty($res))
					{
						$IDS 		= (isset($res['Data']) && $res['Data'] > 0) ? $res['Data'] : 0;
						$Process 	= (!empty($CHALLAN_NO) && !empty($CHALLAN_MEDIA)) ? 2 : 1;
						if($Process == 2) {
							$array['epr_track_id'] = $IDS;
						}
						if($IDS == 0) {
							$Process = 0;
						}
						$REQUEST_DATA = json_encode($array);
						LrEprMappingMaster::where("id",$INSERTED_ID)->update(["request_data"=>$REQUEST_DATA,"response_data"=>$response,"epr_track_id"=>$IDS,"process"=>$Process]);
					}
				}
			}
		}
	}

	/*
	Use 	: Cron Update Challan Number to EPR
	Author 	: Axay Shah
	Date 	: 02 July,2020
	*/
	public static function UpdateChallanToEPR()
	{

		// return false;
		$self 					= (new static)->getTable();
		$EPRTbl					= new LrEprMappingMaster();
		$EPR 					= $EPRTbl->getTable();
		$GET_LAST_DISPATCH_ID 	= LrEprMappingMaster::select("$EPR.dispatch_id","$EPR.id","$EPR.process","$EPR.epr_track_id")
		->join($self,"$EPR.dispatch_id","=","$self.id")
		->where("$EPR.process",1)
		->where("$self.unloading_slip_media_id",">",0)
		// ->where("$EPR.dispatch_id",42448)
		->get();

		if(!empty($GET_LAST_DISPATCH_ID)){
			foreach($GET_LAST_DISPATCH_ID AS $RAW){
				$INSERTED_ID 			= 0;
				$request 				= new Request();
				$request->dispatch_id 	= $RAW->dispatch_id;
				$INSERTED_ID 			= $RAW->id;
				$data 					= WmDispatch::SendDataToEPR($request);
				// EXIT;
				// prd($data);
				if(!empty($data)){
					$array 				= $data;
					$request_data 		= json_encode($array);
					LrEprMappingMaster::where("id",$INSERTED_ID)->update(["request_data"=>$request_data]);
					$url 				= EPR_URL;
					$client 			= new \GuzzleHttp\Client([
						'headers' => [
							'Content-Type' => 'application/json'
						]
					]);
					$response = $client->request('POST', $url,[
							'form_params' => [
							$request_data,
						]
					]);
					$response 		= $response->getBody()->getContents();
					$res 			= json_decode($response,true);
					if(!empty($res)){
						$IDS 		= (isset($res['Data']) && $res['Data'] > 0) ? $res['Data'] : 0;
						$Process 	= (isset($res['Data']) && $res['Data'] > 0) ? 2 : 1;
						/*IF ALL MEDIA UPLOADED THEN ALLOW */
						LrEprMappingMaster::where("id",$INSERTED_ID)->update(["response_data"=>$response,
							"epr_track_id"=>$IDS,"process"=>$Process]);
					}
					\Log::info($response);
					//exit;
				}
			}
		}
	}

	################# EPR TRACK CODE ################
	/*
	Use     : Get Dispatch Document
	Author  : Axay Shah
	Date 	: 17 June 2020
	*/
	// public static function getEprDocument($DispatchId=0)
	// {
	// 	$EPR_CHALLAN 	= "";
	// 	$EPR_BILLT 		= "";
	// 	$EPR_WAYBRIDGE 	= "";
	// 	$EPR_EWAYBILL 	= "";
	// 	$EPR_UNLOAD 	= "";
	// 	$EPR_INVOICE 	= "";
	// 	$result 		= array();
	// 	$Dispatch 		= self::find($DispatchId);
	// 	if($Dispatch){
	// 		if(!empty($Dispatch->epr_challan_media_id)){
	// 			$EPR_CHALLAN = WmDispatchMediaMaster::GetImgById($Dispatch->epr_challan_media_id);
	// 		}
	// 		if(!empty($Dispatch->epr_billt_media_id)){
	// 			$EPR_BILLT = WmDispatchMediaMaster::GetImgById($Dispatch->epr_billt_media_id);
	// 		}
	// 		if(!empty($Dispatch->epr_waybridge_slip_id)){
	// 			$EPR_WAYBRIDGE = WmDispatchMediaMaster::GetImgById($Dispatch->epr_waybridge_slip_id);
	// 		}
	// 		if(!empty($Dispatch->epr_ewaybill_media_id)){
	// 			$EPR_EWAYBILL = WmDispatchMediaMaster::GetImgById($Dispatch->epr_ewaybill_media_id);
	// 		}
	// 		if(!empty($Dispatch->unloading_slip_media_id)){
	// 			$EPR_UNLOAD = WmDispatchMediaMaster::GetImgById($Dispatch->unloading_slip_media_id);
	// 		}
	// 		if(!empty($Dispatch->transporter_invoice_media_id)){
	// 			$EPR_INVOICE = WmDispatchMediaMaster::GetImgById($Dispatch->transporter_invoice_media_id);
	// 		}
	// 		$result['unloading_tare_weight'] 	= (!empty($Dispatch->unloading_tare_weight)) ? $Dispatch->unloading_tare_weight : 0;
	// 		$result['unloading_gross_weight'] 	= (!empty($Dispatch->unloading_gross_weight)) ? $Dispatch->unloading_gross_weight : 0;
	// 		$result['unloading_net_weight'] 	= ($result['unloading_gross_weight'] - $result['unloading_tare_weight']);
	// 		$result['unloading_date'] 			= (!empty($Dispatch->unloading_date)) ? $Dispatch->unloading_date : "";
	// 		$result['epr_challan_media_no'] 	= $Dispatch->epr_challan_no;
	// 		$result['epr_challan_url'] 			= $EPR_CHALLAN;
	// 		$result['epr_billt_media_no'] 		= $Dispatch->epr_billt_no;
	// 		$result['epr_billt_url'] 			= $EPR_BILLT;
	// 		$result['epr_waybridge_slip_no'] 	= $Dispatch->epr_waybridge_no;
	// 		$result['epr_waybridge_url'] 		= $EPR_WAYBRIDGE;
	// 		$result['eway_bill_no'] 			= $Dispatch->eway_bill_no;
	// 		$result['epr_eway_bill_url'] 		= $EPR_EWAYBILL;
	// 		$result['transporter_invoice_no'] 	= $Dispatch->transporter_invoice_no;
	// 		$result['transporter_invoice_url'] 	= $EPR_INVOICE;
	// 		$result['unloading_slip_no'] 		= $Dispatch->unloading_slip_no;
	// 		$result['unloading_slip_url'] 		= $EPR_UNLOAD;
	// 	}
	// 	return $result;
	// }
	public static function getEprDocument($DispatchId=0)
	{
		$RC_DATA 		= "";
		$EPR_CHALLAN 	= "";
		$EPR_BILLT 		= "";
		$EPR_WAYBRIDGE 	= "";
		$EPR_EWAYBILL 	= "";
		$EPR_UNLOAD 	= "";
		$EPR_INVOICE 	= "";
		$result 			= array();
		$EPR_WAYBRIDGE_ARR 	= array();
		$EPR_BILLT_ARR 		= array();
		$EPR_EWAYBILL_ARR 	= array();
		$EPR_UNLOAD_ARR 	= array();
		$EPR_INVOICE_ARR 	= array();
		$Dispatch 		= self::find($DispatchId);
		if($Dispatch){
			if(!empty($Dispatch->vehicle_id)){
				$RC_DATA = VehicleDocument::where(array("vehicle_id"=>$Dispatch->vehicle_id,"document_type"=>RC_BOOK_ID))->first();
				
			}
			if(!empty($Dispatch->epr_challan_media_id)){
				$EPR_CHALLAN = WmDispatchMediaMaster::GetImgById($Dispatch->epr_challan_media_id);
			}
			if(!empty($Dispatch->epr_billt_media_id)){
				$EPR_BILLT_DATA = WmDispatchMediaMaster::where(["media_type"=>PARA_BILLT,"dispatch_id"=>$DispatchId])->get()->toArray();
				if(!empty($EPR_BILLT_DATA)){
					foreach($EPR_BILLT_DATA AS $V){
						$EPR_BILLT_ARR[] = WmDispatchMediaMaster::GetImgById($V['id']);
					}
				}
			}
			if(!empty($Dispatch->epr_waybridge_slip_id)){
				$EPR_WAYBRIDGE_DATA = WmDispatchMediaMaster::where(["media_type"=>PARA_WAYBRIDGE,"dispatch_id"=>$DispatchId])->get()->toArray();
				if(!empty($EPR_WAYBRIDGE_DATA)){
					foreach($EPR_WAYBRIDGE_DATA AS $V){
						$EPR_WAYBRIDGE_ARR[] = WmDispatchMediaMaster::GetImgById($V['id']);
					}
				}
			}
			if(!empty($Dispatch->epr_ewaybill_media_id)){
				$EPR_EWAYBILL_DATA = WmDispatchMediaMaster::where(["media_type"=>PARA_EWAY_BILL,"dispatch_id"=>$DispatchId])->get()->toArray();
				if(!empty($EPR_EWAYBILL_DATA)){
					foreach($EPR_EWAYBILL_DATA AS $V){
						$EPR_EWAYBILL_ARR[] = WmDispatchMediaMaster::GetImgById($V['id']);
					}
				}
			}
			if(!empty($Dispatch->unloading_slip_media_id)){
				$EPR_UNLOAD_DATA = WmDispatchMediaMaster::where(["media_type"=>PARA_UNLOADING_SLIP,"dispatch_id"=>$DispatchId])->get()->toArray();
				if(!empty($EPR_UNLOAD_DATA)){
					foreach($EPR_UNLOAD_DATA AS $V){
						$EPR_UNLOAD_ARR[] = WmDispatchMediaMaster::GetImgById($V['id']);
					}
				}
			}
			if(!empty($Dispatch->transporter_invoice_media_id)){
				$EPR_INVOICE_DATA 	= WmDispatchMediaMaster::where(["media_type"=>PARA_TRANSPORTER_INV,"dispatch_id"=>$DispatchId])->get()->toArray();
				if(!empty($EPR_INVOICE_DATA)){
					foreach($EPR_INVOICE_DATA AS $V){
						$EPR_INVOICE_ARR[] = WmDispatchMediaMaster::GetImgById($V['id']);
					}
				}
			}

			$result['epr_waybridge_url_data'] 		= $EPR_WAYBRIDGE_ARR;
			$result['epr_eway_bill_url_data'] 		= $EPR_EWAYBILL_ARR;
			$result['unloading_slip_url_data'] 		= $EPR_UNLOAD_ARR;
			$result['epr_billt_url_data'] 			= $EPR_BILLT_ARR;
			$result['transporter_invoice_url_data'] = $EPR_INVOICE_ARR;



			$result['rc_book'] 					= ($RC_DATA) ? $RC_DATA->document_file : "";
			$result['rc_book_no'] 				= ($RC_DATA) ? $RC_DATA->document_name : "";
			
			$result['unloading_tare_weight'] 	= (!empty($Dispatch->unloading_tare_weight)) ? $Dispatch->unloading_tare_weight : 0;
			$result['unloading_gross_weight'] 	= (!empty($Dispatch->unloading_gross_weight)) ? $Dispatch->unloading_gross_weight : 0;
			$result['unloading_net_weight'] 	= ($result['unloading_gross_weight'] - $result['unloading_tare_weight']);
			$result['unloading_date'] 			= (!empty($Dispatch->unloading_date)) ? $Dispatch->unloading_date : "";
			$result['weighment_no'] 			= (!empty($Dispatch->weighment_no)) ? $Dispatch->weighment_no : "";
			$result['epr_challan_url'] 			= $EPR_CHALLAN;
			$result['epr_challan_media_no'] 	= $Dispatch->epr_challan_no;
			$result['epr_challan_url'] 			= $EPR_CHALLAN;
			$result['epr_billt_media_no'] 		= $Dispatch->epr_billt_no;
			$result['epr_billt_url'] 			= $EPR_BILLT;
			$result['epr_waybridge_slip_no'] 	= $Dispatch->epr_waybridge_no;
			$result['epr_waybridge_url'] 		= $EPR_WAYBRIDGE;
			$result['eway_bill_no'] 			= $Dispatch->eway_bill_no;
			$result['epr_eway_bill_url'] 		= $EPR_EWAYBILL;
			$result['transporter_invoice_no'] 	= $Dispatch->transporter_invoice_no;
			$result['transporter_invoice_url'] 	= $EPR_INVOICE;
			$result['unloading_slip_no'] 		= $Dispatch->unloading_slip_no;
			$result['unloading_slip_url'] 		= $EPR_UNLOAD;
			$TRANSPORTER_NAME 					= TransporterDetailsMaster::find($Dispatch->transporter_po_id);
			$result['demurrage_remarks'] 		= ($TRANSPORTER_NAME) ? $TRANSPORTER_NAME->demurrage_remarks : "";
			$result['demurrage'] 				= ($TRANSPORTER_NAME) ? $TRANSPORTER_NAME->demurrage : 0;
			$result['rc_book'] 					= ($RC_DATA) ? $RC_DATA->document_file : "";
			$result['rc_book_no'] 				= ($RC_DATA) ? $RC_DATA->document_name : "";
			/** BRANDING IMAGES CODE */
			$BRANDING_IMG_IDS = WmDispatchBrandingImgMapping::GetImageMediaIds($DispatchId);
			$BRANDING_INV_DATA 	= array();
			if(!empty($BRANDING_IMG_IDS)){
				$BRANDING_INV_DATA 	= WmDispatchMediaMaster::select("dispatch_id","image_name","image_path","id")->whereIn("id",$BRANDING_IMG_IDS)->get();
				if(!empty($BRANDING_INV_DATA)) {
					foreach ($BRANDING_INV_DATA AS $BRANDING_KEY => $BRANDING_INV_MEDIA) {
						$BRANDING_INV_DATA[$BRANDING_KEY]['image_name'] 	=  $BRANDING_INV_MEDIA->image_name;
						$BRANDING_INV_DATA[$BRANDING_KEY]['image_url'] 		=  url('/')."/".$BRANDING_INV_MEDIA->image_path."/".$BRANDING_INV_MEDIA->image_name;
					}
				}
			}
			$result['branding_image_count'] = 0;
			$result['branding_images'] 		= $BRANDING_INV_DATA;
		}
		return $result;
	}
	/*
	Use 	: Generate Eway Bill From Dispatch ID
	Author 	: Axay Shah
	Date 	: 10 December 2020
	*/
	public static function GetEwayBill($request)
	{
		\Log::info("########### CALLING EWAY BILL API ##############");
		\Log::info(print_r($request,true));
		$url 		= EWAY_BILL_PORTAL_URL."generate-eway-bill";
		$client 	= new \GuzzleHttp\Client([
			'headers' => ['Content-Type' => 'application/json']
		]);
		$client 	= new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
		$response 	= $client->request('POST',$url,['form_params'=>$request]);
		$response 	= $response->getBody()->getContents();
		\Log::info("########### CALLING EWAY BILL API RESPONSE##############");
		\Log::info(print_r($response,true));
		\Log::info("########### CALLING EWAY BILL API END##############");
		return $response;
	}
	/*
	Use 	: Generate Eway Bill From Dispatch ID
	Author 	: Axay Shah
	Date 	: 10 December 2020
	*/
	public static function GenerateEwayBillFromDispatch($id){
		$data 								= self::GetById($id);
		$REQUEST_DATA 						= array();
		if($data){
			$MERCHANT_KEY 					= CompanyMaster::where("company_id",$data->company_id)->value('merchant_key');
			$REQUEST_DATA['merchant_key'] 	= (!empty($MERCHANT_KEY)) ? $MERCHANT_KEY : "";
			$REQUEST_DATA['docType']    	= (isset($data['dispatch_type']) && $data['dispatch_type'] == NON_RECYCLEBLE_TYPE) ? 'BIL' : 'INV';
			$REQUEST_DATA['docNo']    		= (isset($data['challan_no']) && !empty($data['challan_no'])) ? $data['challan_no'] : '';
			$REQUEST_DATA['docDate']  		= (isset($data['dispatch_date']) && !empty($data['dispatch_date'])) ? date("d/m/Y",strtotime($data['dispatch_date'])) : '';
			$MRF_DEPT_ID 					= (isset($data['bill_from_mrf_id']) && !empty($data['bill_from_mrf_id'])) ? $data['bill_from_mrf_id'] : $data['master_dept_id'];
			$REQUEST_DATA['supplyType']    	= "O";
			$REQUEST_DATA['subSupplyType']  = "1";
			$REQUEST_DATA['subSupplyDesc']  = " ";
			########## BILL FROM MRF ##############
			$DepartmentData 				= WmDepartment::find($MRF_DEPT_ID);
			$REQUEST_DATA['username'] 		= ($DepartmentData && !empty($DepartmentData->gst_username)) ? $DepartmentData->gst_username : "";
			$REQUEST_DATA['password'] 		= ($DepartmentData && !empty($DepartmentData->gst_password)) ? $DepartmentData->gst_password : "";
			$REQUEST_DATA['user_gst_in'] 	= ($DepartmentData && !empty($DepartmentData->gst_in)) ? $DepartmentData->gst_in : "";

			############### DISPATCH FROM INFORMATION #####################
			$FROM_ADDRESS_2 		= "";
			if($data['from_mrf'] == "Y"){
				$MDI 						= (isset($data['master_dept_id']) && !empty($data['master_dept_id'])) ? $data['master_dept_id'] : 0;
				$MasterDepartmentData 	    = WmDepartment::find($MDI);

				if(!empty($MRF_DEPT_ID)){
					if($MasterDepartmentData){
						$FROM_PINCODE 	=  $MasterDepartmentData->pincode;
						$FROM_ADDRESS_1 =  $MasterDepartmentData->address;
					}
				}
				$FROM_GST_IN 		= $REQUEST_DATA['user_gst_in'];
				$FROM_TRD_NAME 		= $data['company_name'];
				$FROM_PLACE 		= $data['origin_city_name'];
				$FROM_STATE_CODE 	= $MasterDepartmentData->gst_state_code_id;
				$ACT_FROM_STATE_CODE = $FROM_STATE_CODE;
			}else{
				$FROM_GST_IN 		= $REQUEST_DATA['user_gst_in'];
				$FROM_TRD_NAME 		= $data['company_name'];
				$FROM_ADDRESS_1 	= $data['origin_address'];
				$FROM_PLACE 		= $data['origin_city_name'];
				$FROM_PINCODE   	= CustomerMaster::where("customer_id",$data['origin'])->value('zipcode');
				$FROM_STATE_CODE 	= (!empty($data['origin_state_code'])) ? GSTStateCodes::where("id",$data['origin_state_code'])->value('display_state_code') : $data['origin_state_code'];
				$ACT_FROM_STATE_CODE = $FROM_STATE_CODE;
			}
			$FROM_STATE_CODE 			= $DepartmentData->gst_state_code_id;;
			/* ORIGIN DETAILS */
			$REQUEST_DATA['fromGstin'] 			= $FROM_GST_IN;
			$REQUEST_DATA['fromTrdName'] 		= $FROM_TRD_NAME;
			$REQUEST_DATA['fromAddr1'] 			= $FROM_ADDRESS_1;
			$REQUEST_DATA['fromAddr2'] 			= $FROM_ADDRESS_2;
			$REQUEST_DATA['fromPlace'] 			= $FROM_PLACE;
			$REQUEST_DATA['fromPincode'] 		= $FROM_PINCODE;
			$REQUEST_DATA['actFromStateCode'] 	= $ACT_FROM_STATE_CODE;
			$REQUEST_DATA['fromStateCode'] 		= $FROM_STATE_CODE;
			$REQUEST_DATA['transactionType'] 	= (isset($data['transactionType']) && !empty($data['transactionType'])) ? $data['transactionType'] : 4;

			/* DESTINATION DETAILS */
			$REQUEST_DATA['toGstin'] 		= (isset($data['gstin_no']) && !empty($data['gstin_no'])) ? $data['gstin_no'] : '';
			$REQUEST_DATA['toTrdName'] 		= (isset($data['client_name']) && !empty($data['client_name'])) ? $data['client_name'] : '';
			$REQUEST_DATA['toStateCode'] 	= (isset($data['destination_state_code']) && !empty($data['destination_state_code'])) ? $data['destination_state_code'] : '';
			$REQUEST_DATA['toAddr1'] 		= (isset($data['shipping_address']) && !empty($data['shipping_address'])) ? $data['shipping_address'] : '';
			$REQUEST_DATA['toAddr2'] 		= (isset($data['toAddr2']) && !empty($data['toAddr2'])) ? $data['toAddr2'] : '';
			$REQUEST_DATA['toPlace'] 		= (isset($data['shipping_city']) && !empty($data['shipping_city'])) ? $data['shipping_city'] : '';
			$REQUEST_DATA['toPincode'] 		= (isset($data['shipping_pincode']) && !empty($data['shipping_pincode'])) ? $data['shipping_pincode'] : '';
			$REQUEST_DATA['actToStateCode'] = (isset($data['shipping_state_code']) && !empty($data['shipping_state_code'])) ? $data['shipping_state_code'] : '';
			// $REQUEST_DATA['actFromStateCode'] 	= (isset($data['destination_state_code']) && !empty($data['destination_state_code'])) ? $data['destination_state_code'] : '';
			$IsFromSameState 	= ($data['master_dept_state_code'] == $data['destination_state_code']) ? true : false;
			$TOTAL_AMOUNT       = 0;
			$TOTAL_TAX_AMOUNT   = 0;
			$TOTAL_CGST         = 0;
			$TOTAL_SGST         = 0;
			$TOTAL_IGST         = 0;
			$TAX_AMOUNT 		= 0;
			$CGST 				= 0;
			$SGST 				= 0;

			$ArrProduct = WmSalesMaster::select("wm_sales_master.*","wm_product_master.id as product_id","wm_product_master.title AS product_name","wm_product_master.hsn_code")
						->leftjoin("wm_product_master","wm_sales_master.product_id","=","wm_product_master.id")
						->where("wm_sales_master.dispatch_id",$id)
						->get()
						->toArray();
			$itemList 	= array();
			$Amount 	= 0;
			$TOTAL_TAXABLE_AMT = 0;
			if(!empty($ArrProduct)){
				$RENT_AMT 		=  (isset($data['rent_amt']) && !empty($data['rent_amt'])) ? $data['rent_amt'] : 0;
				$RENT_GST_AMT 	=  (isset($data['rent_gst_amt']) && !empty($data['rent_gst_amt'])) ? $data['rent_gst_amt'] : 0;
				foreach ($ArrProduct as $key => $value)
				{
					$Qty 		= _FormatNumberV2($value["quantity"]);
					$Rate 		= _FormatNumberV2($value['rate']);
					$Amount 	= $Qty * $Rate;

					$SUM_GST_PERCENT 		= 0;
					$CGST_AMT 				= 0;
					$SGST_AMT 				= 0;
					$IGST_AMT 				= 0;
					$RENT_CGST 				= 0;
					$RENT_SGST 				= 0;
					$RENT_IGST 				= 0;
					$TOTAL_GST_AMT 			= 0;

					$CGST_RATE 				= _FormatNumberV2($value['cgst_rate']);
					$SGST_RATE 				= _FormatNumberV2($value['sgst_rate']);
					$IGST_RATE 				= _FormatNumberV2($value['igst_rate']);
					if($IsFromSameState) {
						if($Rate > 0){
							$CGST_AMT 			= ($CGST_RATE > 0) ? (($Qty * $Rate) / 100) * $CGST_RATE:0;
							$SGST_AMT 			= ($SGST_RATE > 0) ? (($Qty * $Rate) / 100) *  $SGST_RATE:0;
							$TOTAL_GST_AMT 		= $CGST_AMT + $SGST_AMT;
							$SUM_GST_PERCENT 	= $CGST_RATE + $SGST_RATE;
							$TOTAL_CGST 		+= $CGST_AMT;
							$TOTAL_SGST 		+= $SGST_AMT;
							$RENT_CGST 			= (!empty($RENT_GST_AMT)) ? $RENT_GST_AMT / 2 : 0;
							$RENT_SGST 			= (!empty($RENT_GST_AMT)) ? $RENT_GST_AMT / 2 : 0;
						}
					}else{
						if($Rate > 0){
							$RENT_IGST 			= (!empty($RENT_GST_AMT)) ? $RENT_GST_AMT  : 0;
							$IGST_AMT 			= ($IGST_RATE > 0) ? (($Qty * $Rate) / 100) * $IGST_RATE:0;
							$TOTAL_GST_AMT 		= $IGST_AMT;
							$SUM_GST_PERCENT 	= $IGST_RATE;
							$TOTAL_IGST 		+= $IGST_AMT;

						}
					}
					$TOTAL_TAXABLE_AMT 	+= $Amount;
					$TOTAL_AMOUNT       += $Amount + $TOTAL_GST_AMT;
					$TOTAL_TAX_AMOUNT 	+= $TOTAL_GST_AMT;
					$itemList[$key]["productName"]      	= $value['product_name'];
					$itemList[$key]["productDesc"]      = WmDispatchProduct::where("dispatch_id",$id)->where("product_id",$value['product_id'])->value("description");
					$itemList[$key]["hsnCode"]          = $value['hsn_code'];
					$itemList[$key]["quantity"]         = _FormatNumberV2($Qty);
					$itemList[$key]["qtyUnit"]          = "KGS";
					$itemList[$key]["cgstRate"]     	= 0;
					$itemList[$key]["sgstRate"]     	= 0;
					$itemList[$key]["igstRate"]     	= 0;
					if($IsFromSameState){
						$itemList[$key]["cgstRate"]     = _FormatNumberV2($CGST_RATE);
						$itemList[$key]["sgstRate"]     = _FormatNumberV2($SGST_RATE);
					}else{
						$itemList[$key]["igstRate"]     = _FormatNumberV2($IGST_RATE);
					}
					$itemList[$key]["cessRate"]         = 0;
					$itemList[$key]["taxableAmount"]    = _FormatNumberV2($Amount);
				}
				$ADDTIONAL_CHARGES = 0;
				####### Invoice Additional Charges ############
				$AdditionalCharges = InvoiceAdditionalCharges::GetInvoiceAdditionalCharges($id);
				if (!empty($AdditionalCharges))
				{
					foreach ($AdditionalCharges as $AdditionalChargeValue) {
						$ADDTIONAL_CHARGES += $AdditionalChargeValue['net_amount'];
					}
				}
				####### Invoice Additional Charges ############
			}
			/*######## OTHER VALUE CALCULATION ##############*/
			$RENT_AMT 		=  (isset($data['rent_amt']) && !empty($data['rent_amt'])) ? $data['rent_amt'] : 0;
			$RENT_GST_AMT 	=  (isset($data['rent_gst_amt']) && !empty($data['rent_gst_amt'])) ? $data['rent_gst_amt'] : 0;

			$TOTAL_CGST 	+= $RENT_CGST;
			$TOTAL_SGST 	+= $RENT_SGST;
			$TOTAL_IGST 	+= $RENT_IGST;
			$TOTAL_AMOUNT   += $RENT_CGST + $RENT_SGST + $RENT_IGST;

			$DISCOUNT_AMT 		=  (isset($data['discount_amt']) && !empty($data['discount_amt'])) ? $data['discount_amt'] : 0;
			$TCS_AMOUNT 		=  (isset($data['tcs_amount']) && !empty($data['tcs_amount'])) ? $data['tcs_amount'] : 0;
			$OTHER_VALUE 		=  _FormatNumberV2(($RENT_AMT + $TCS_AMOUNT + $ADDTIONAL_CHARGES) - $DISCOUNT_AMT);
			$INVOICE_AMT 		=  $TOTAL_AMOUNT + $OTHER_VALUE;
			$DIFFRENCE_AMT  	=  (round($INVOICE_AMT)- $INVOICE_AMT);
			$TOTAL_OTHER_VAL 	=  _FormatNumberV2($OTHER_VALUE + $DIFFRENCE_AMT);
			$ROUND_INV_AMT  	=  round($INVOICE_AMT);
			/*######## OTHER VALUE CALCULATION ##############*/
			################ TRANSPOTER DETAILS ######################
			$TRANSPORTER_NAME 	= (isset($data['transporter_name']) && !empty($data['transporter_name'])) ? $data['transporter_name'] : "";
			$TRANSPOTER_PO_ID 	= (isset($data['transporter_po_id']) && !empty($data['transporter_po_id'])) ? $data['transporter_po_id'] :0;
			if(!empty($TRANSPORTER_NAME)){
				$PAID_BY_PARTY 	= TransporterDetailsMaster::where("id",$TRANSPOTER_PO_ID)->value("paid_by_party");
				$TRANSPORTER_NAME = strtolower(str_replace(" ","",$TRANSPORTER_NAME));
				if($TRANSPORTER_NAME == "thirdparty" || $PAID_BY_PARTY == 1 ){
					$TRANSPORTER_NAME = "";
				}
			}

			################ TRANSPOTER DETAILS ######################

			############### DISPATCH TO INFORMATION #####################
			$REQUEST_DATA['otherValue'] 		= _FormatNumberV2($TOTAL_OTHER_VAL);
			$REQUEST_DATA['totalValue'] 		= _FormatNumberV2($TOTAL_TAXABLE_AMT);
			$REQUEST_DATA['cgstValue'] 			= _FormatNumberV2($TOTAL_CGST);
			$REQUEST_DATA['sgstValue'] 			= _FormatNumberV2($TOTAL_SGST);
			$REQUEST_DATA['igstValue'] 			= _FormatNumberV2($TOTAL_IGST);
			$REQUEST_DATA['cessValue'] 			= (isset($data['cessValue']) && !empty($data['cessValue'])) ? _FormatNumberV2($data['cessValue']) : 0;
			$REQUEST_DATA['cessNonAdvolValue'] 	= (isset($data['cessNonAdvolValue']) && !empty($data['cessNonAdvolValue'])) ? $data['cessNonAdvolValue'] : 0;
			$REQUEST_DATA['totInvValue'] 		= _FormatNumberV2($ROUND_INV_AMT);
			$REQUEST_DATA['transporterId'] 		= (isset($data['transporterId']) && !empty($data['transporterId'])) ? $data['transporterId'] : '';
			$REQUEST_DATA['transporterName'] 	= $TRANSPORTER_NAME;
			$REQUEST_DATA['transDocNo'] 		= (isset($data['bill_of_lading']) && !empty($data['bill_of_lading'])) ? $data['bill_of_lading'] : "";
			$REQUEST_DATA['transMode'] 			= (isset($data['transMode']) && !empty($data['transMode'])) ? $data['transMode'] : 1;
			$REQUEST_DATA['transDistance'] 		= (isset($data['trans_distance']) && !empty($data['trans_distance'])) ? $data['trans_distance'] : 0;
			$REQUEST_DATA['transDocDate'] 		= (isset($REQUEST_DATA['docDate']) && !empty($REQUEST_DATA['docDate'])) ? $REQUEST_DATA['docDate'] : "";
			$REQUEST_DATA['vehicleNo'] 			= (isset($data['vehicle_number']) && !empty($data['vehicle_number'])) ?  str_replace(' ','',str_replace( array( '\'', '"', ',' ,"-", ';', '<', '>',' '), '', $data['vehicle_number']))  : '';
			$REQUEST_DATA['vehicleType'] 		= (isset($data['vehicleType']) && !empty($data['vehicleType'])) ? $data['vehicleType'] : 'R';
			$REQUEST_DATA['itemList'] 			= $itemList;
			############### DISPATCH TO INFORMATION #####################
			$responseData 	= array();
			$result 		= self::GetEwayBill($REQUEST_DATA);
			if(!empty($result)){
				$responseData = json_decode($result,true);
				if($responseData['code'] == SUCCESS){
					self::where("id",$id)->update(["eway_bill_no"=>$responseData['data']['ewayBillNo']]);
					WmInvoices::where("dispatch_id",$id)->update(["eway_bill"=>$responseData['data']['ewayBillNo']]);
				}
			}
			return $responseData;
		}
	}

	/*
	Use 	: Cancel Eway Bill by dispatch ID
	Author 	: Axay Shah
	Date 	: 11 December 2020
	*/
	public static function CancelEwayBill($request){
		$responseData 		= array();
		$EWAY_BILL_NO   	= (isset($request['eway_bill_no']) && !empty($request['eway_bill_no'])) ? $request['eway_bill_no'] : "";
		$CANCEL_REMARK  	= (isset($request['cancel_remark']) && !empty($request['cancel_remark'])) ? $request['cancel_remark'] : '';
		$CANCEL_RSN_CODE 	= (isset($request['cancel_rsn_code']) && !empty($request['cancel_rsn_code'])) ? $request['cancel_rsn_code'] : 4;
		$MERCHANT_KEY 		= CompanyMaster::where("company_id",Auth()->user()->company_id)->value('merchant_key');
		$request['merchant_key'] = $MERCHANT_KEY;
		if(!empty($MERCHANT_KEY) && !empty($EWAY_BILL_NO)){
			$LoginDetails = WmDispatch::join("wm_department","wm_dispatch.bill_from_mrf_id","=","wm_department.id")
			->where("wm_dispatch.eway_bill_no",$EWAY_BILL_NO)
			->first();
			$request['username'] 		= ($LoginDetails && !empty($LoginDetails->gst_username)) ? $LoginDetails->gst_username : "";
			$request['password'] 		= ($LoginDetails && !empty($LoginDetails->gst_password)) ? $LoginDetails->gst_password : "";
			$request['user_gst_in'] 	= ($LoginDetails && !empty($LoginDetails->gst_in)) ? $LoginDetails->gst_in : "";


			$url 		= EWAY_BILL_PORTAL_URL."cancel-ewaybill";
			$client 	= new \GuzzleHttp\Client([
				'headers' => ['Content-Type' => 'application/json']
			]);
			$response 	= $client->request('POST', $url,
			 array(
				'form_params' => $request
			));
			$response 		= $response->getBody()->getContents();
			if(!empty($response)){
				$responseData = json_decode($response);
				if(isset($responseData->data) && !empty($responseData->data->ewayBillNo)){
					self::where("eway_bill_no",$responseData->data->ewayBillNo)->update(["eway_bill_no"=>""]);
					WmInvoices::where("eway_bill",$responseData->data->ewayBillNo)->update(["eway_bill"=>""]);
				}
			}

			return $responseData;
		}
	}

	/*
	Use 	: Cancel Eway Bill by dispatch ID
	Author 	: Axay Shah
	Date 	: 11 December 2020
	*/
	public static function CancelEwayBillResons(){
		$array 		=	array();
		$response 	= 	array();
		$url 		= 	EWAY_BILL_PORTAL_URL."cancel-ewaybill-reason";
		$client 	= 	new \GuzzleHttp\Client([
			'headers' => ['Content-Type' => 'application/json']
		]);
		$response 	= $client->request('POST', $url,
		 array(
			'form_params' => $array
		));
		$response 	= $response->getBody()->getContents();
		if(!empty($response)){
			$responseData = json_decode($response,true);
			if(isset($responseData['data']) && !empty($responseData['data'])){
				$responseData = $responseData['data'];
			}
		}
		return $responseData;
	}

	/*
	Use 	: Update Transpoter Data
	Author 	: Axay Shah
	Date 	: 11 December 2020
	*/
	public static function UpdateTranspoterData($request){
		$responseData 	= array();
		$DISPATCH_ID  	= (isset($request['dispatch_id']) && !empty($request['dispatch_id'])) ? $request['dispatch_id'] : '';
		$MERCHANT_KEY 	= CompanyMaster::where("company_id",Auth()->user()->company_id)->value('merchant_key');


		$EWAY_BILL_NO 	= self::where("id",$DISPATCH_ID)->value("eway_bill_no");
		$request['eway_bill_no'] = $EWAY_BILL_NO;
		$request['merchant_key'] = $MERCHANT_KEY;
		$url 		= EWAY_BILL_PORTAL_URL."update-transpoter";
		$client 	= new \GuzzleHttp\Client([
			'headers' => ['Content-Type' => 'application/json']
		]);
		$response 	= $client->request('POST', $url,
		 array(
			'form_params' => $request
		));
		$response 		= $response->getBody()->getContents();
		if(!empty($response)){
			$responseData = json_decode($response);
		}
		return $responseData;
	}

	/*
	Use 	: Update Killometer in dispatch
	Author 	: Axay Shah
	Date 	: 10 Feb 2021
	*/
	public static function UpdateTransDistance($DispatchID=0,$Km=0){
		$responseData 	= array();
		if(!empty($DispatchID) && $Km > 0){
			$update = self::where("id",$DispatchID)->update(["trans_distance"=>$Km]);
			return true;
		}
		return false;
	}
	/*
	Use 	: Update E invoice number
	Author 	: Axay Shah
	Date 	: 15 March 2021
	*/
	public static function UpdateEinvoiceNo($DispatchID=0,$Einvoice="",$acknowledgement_no="",$acknowledgement_date=""){
		$responseData 	= array();
		if(!empty($DispatchID)){
			$update = self::where("id",$DispatchID)->update(["e_invoice_no"=>$Einvoice,"acknowledgement_no"=>$acknowledgement_no,"acknowledgement_date"=>$acknowledgement_date]);
			return true;
		}
		return false;
	}

	/*
	Use 	: Update Dispatch show vendor name flag
	Author 	: Axay Shah
	Date 	: 18 March 2021
	*/
	public static function UpdateVendorNameFlag($DispatchID=0,$flag=0){
		$responseData 	= array();
		if(!empty($DispatchID)){
			$update 	= self::where("id",$DispatchID)->update(["show_vendor_name_flag"=>$flag]);
			return true;
		}
		return false;
	}

	/*
	Use 	: generate E invoice Data
	Author 	: Axay Shah
	Date 	: 19 March 2021
	*/
	public static function GenerateEInvoice($id){
		$data 						= self::GetById($id);
		$REQUEST_DATA 				= array();
		if($data){
			$MERCHANT_KEY 			= CompanyMaster::where("company_id",$data->company_id)->value('merchant_key');

			$MRF_DEPT_ID 			= (isset($data['bill_from_mrf_id']) && !empty($data['bill_from_mrf_id'])) ? $data['bill_from_mrf_id'] : $data['master_dept_id'];
			########## BILL FROM MRF ##############
			$DepartmentData = WmDepartment::find($MRF_DEPT_ID);
			$array['merchant_key'] 	= (!empty($MERCHANT_KEY)) ? $MERCHANT_KEY : "";
			$GST_USER_NAME 	= ($DepartmentData && !empty($DepartmentData->gst_username)) ? $DepartmentData->gst_username : "";
			$GST_PASSWORD 	= ($DepartmentData && !empty($DepartmentData->gst_password)) ? $DepartmentData->gst_password : "";
			$GST_GST_IN 	= ($DepartmentData && !empty($DepartmentData->gst_in)) ? $DepartmentData->gst_in : "";
			############### DISPATCH FROM INFORMATION #####################
			$FROM_ADDRESS_2 = "";
			$FROM_ADDRESS_1 = "";
			if($data['from_mrf'] == "Y"){
				$MDI 				= (isset($data['master_dept_id']) && !empty($data['master_dept_id'])) ? $data['master_dept_id'] : 0;
				$MasterDepartmentData = WmDepartment::find($MDI);
				if(!empty($MRF_DEPT_ID)){
					if($MasterDepartmentData){
						$FROM_PINCODE 	=  $MasterDepartmentData->pincode;
						$FROM_ADDRESS_1 =  $MasterDepartmentData->address;
					}
				}
				$FROM_GST_IN 		= $GST_GST_IN;
				$FROM_TRD_NAME 		= $data['company_name'];
				$FROM_ADDRESS_2 	= "";
				$FROM_PLACE 		= $data['origin_city_name'];
				$FROM_STATE_CODE 	= $MasterDepartmentData->gst_state_code_id;
			}else{
				$FROM_GST_IN 		= $data['company_gst_no'];
				$FROM_TRD_NAME 		= $data['company_name'];
				$FROM_ADDRESS_1 	= $data['origin_address'];
				$FROM_ADDRESS_2 	= "";
				$FROM_PLACE 		= $data['origin_city_name'];
				$FROM_PINCODE   	= CustomerMaster::where("customer_id",$data['origin'])->value('zipcode');
				$FROM_STATE_CODE 	= $data['origin_state_code'];
			}
			######## NEW CHANGES ###########
			$MDI = (isset($data['bill_from_mrf_id']) && !empty($data['bill_from_mrf_id'])) ? $data['bill_from_mrf_id'] : 0;
			$MasterDepartmentData = WmDepartment::find($MDI);
			if(!empty($MRF_DEPT_ID)){
				if($MasterDepartmentData){
					$FROM_PINCODE 	=  $MasterDepartmentData->pincode;
					$FROM_ADDRESS_1 =  $MasterDepartmentData->address;
				}
			}
			if(strlen($FROM_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($FROM_ADDRESS_1);
				$FROM_ADDRESS_1 = (!empty($ARR_STRING)) ? $ARR_STRING[0] : $FROM_ADDRESS_1;
				$FROM_ADDRESS_2 = (!empty($ARR_STRING)) ? $ARR_STRING[1] : $FROM_ADDRESS_1;
			}
			$FROM_GST_IN 		= $GST_GST_IN;
			$FROM_TRD_NAME 		= $data['company_name'];
			$FROM_STATE_CODE 	= $MasterDepartmentData->gst_state_code_id;
			$FROM_PLACE 		= LocationMaster::where("location_id",$MasterDepartmentData->location_id)->value("city");
			#  NEW CHANGES #####
			$IsFromSameState 	= ($FROM_STATE_CODE == $data['destination_state_code']) ? true : false;
			$TOTAL_AMOUNT       = 0;
			$TOTAL_TAX_AMOUNT   = 0;
			$TOTAL_CGST         = 0;
			$TOTAL_SGST         = 0;
			$TOTAL_IGST         = 0;
			$TAX_AMOUNT 		= 0;
			$CGST 				= 0;
			$SGST 				= 0;

			$ArrProduct = WmSalesMaster::select("wm_sales_master.*","wm_product_master.id as product_id","wm_product_master.title AS product_name","wm_product_master.hsn_code")
						->leftjoin("wm_product_master","wm_sales_master.product_id","=","wm_product_master.id")
						->where("wm_sales_master.dispatch_id",$id)
						->get()
						->toArray();
			$itemList 	= array();
			$Amount 	= 0;
			$TOTAL_TAXABLE_AMT = 0;
			if(!empty($ArrProduct)){
				$RENT_AMT 		=  (isset($data['rent_amt']) && !empty($data['rent_amt'])) ? $data['rent_amt'] : 0;
				$RENT_GST_AMT 	=  (isset($data['rent_gst_amt']) && !empty($data['rent_gst_amt'])) ? $data['rent_gst_amt'] : 0;
				foreach ($ArrProduct as $key => $value)
				{
					$Qty 		= _FormatNumberV2($value["quantity"]);
					$Rate 		= _FormatNumberV2($value['rate']);
					$Amount 	= $Qty * $Rate;

					$SUM_GST_PERCENT 		= 0;
					$CGST_AMT 				= 0;
					$SGST_AMT 				= 0;
					$IGST_AMT 				= 0;
					$RENT_CGST 				= 0;
					$RENT_SGST 				= 0;
					$RENT_IGST 				= 0;
					$TOTAL_GST_AMT 			= 0;

					$CGST_RATE 				= _FormatNumberV2($value['cgst_rate']);
					$SGST_RATE 				= _FormatNumberV2($value['sgst_rate']);
					$IGST_RATE 				= _FormatNumberV2($value['igst_rate']);
					if($IsFromSameState) {
						if($Rate > 0){
							$CGST_AMT 			= ($CGST_RATE > 0) ? (($Qty * $Rate) / 100) * $CGST_RATE:0;
							$SGST_AMT 			= ($SGST_RATE > 0) ? (($Qty * $Rate) / 100) *  $SGST_RATE:0;
							$TOTAL_GST_AMT 		= $CGST_AMT + $SGST_AMT;
							$SUM_GST_PERCENT 	= $CGST_RATE + $SGST_RATE;
							$TOTAL_CGST 		+= $CGST_AMT;
							$TOTAL_SGST 		+= $SGST_AMT;
						}
					}else{
						if($Rate > 0){
							$IGST_AMT 			= ($IGST_RATE > 0) ? (($Qty * $Rate) / 100) * $IGST_RATE:0;
							$TOTAL_GST_AMT 		= $IGST_AMT;
							$SUM_GST_PERCENT 	= $IGST_RATE;
							$TOTAL_IGST 		+= $IGST_AMT;

						}
					}
				$TOTAL_ITEM_AMOUNT 	= $Amount + $TOTAL_GST_AMT;
				$TOTAL_TAXABLE_AMT 	+= $Amount;
				$TOTAL_AMOUNT       += $TOTAL_ITEM_AMOUNT;
				$TOTAL_TAX_AMOUNT 	+= $TOTAL_GST_AMT;
					$itemList[$key]["productName"]      = $value['product_name'];
					$itemList[$key]["productDesc"]      = WmDispatchProduct::where("dispatch_id",$id)->where("product_id",$value['product_id'])->value("description");
					$itemList[$key]["hsnCode"]          = $value['hsn_code'];
					$itemList[$key]["quantity"]         = _FormatNumberV2($Qty);
					$itemList[$key]["qtyUnit"]          = "KGS";
					$itemList[$key]["cgstRate"]     	= 0;
					$itemList[$key]["sgstRate"]     	= 0;
					$itemList[$key]["igstRate"]     	= 0;
					$itemList[$key]["price"]     		= $Rate;
					if($IsFromSameState){
						$itemList[$key]["cgstRate"]     = _FormatNumberV2($CGST_RATE);
						$itemList[$key]["sgstRate"]     = _FormatNumberV2($SGST_RATE);
					}else{
						$itemList[$key]["igstRate"]     = _FormatNumberV2($IGST_RATE);
					}
					$itemList[$key]["cgstAmt"]     		= $CGST_AMT;
					$itemList[$key]["sgstAmt"]     		= $SGST_AMT;
					$itemList[$key]["igstAmt"]     		= $IGST_AMT;
					$itemList[$key]["totalGstPercent"]  = $SUM_GST_PERCENT;
					$itemList[$key]["cessRate"]         = 0;
					$itemList[$key]["taxableAmount"]    = _FormatNumberV2($Amount);
					$itemList[$key]["totalItemAmount"]  = _FormatNumberV2($TOTAL_ITEM_AMOUNT);
					$itemList[$key]["is_service"]      	= "N";
					$itemList[$key]["gstAmount"]      	= $TOTAL_GST_AMT;
				}
			}
			############# CHARGES DETAILS PUSH IN E INVOICE IF APPLICABLE ##############
			$CLIENT_CHARGES_DATA = InvoiceAdditionalCharges::GetProductDataForEInvoice($id);
			if(!empty($CLIENT_CHARGES_DATA)){
				$count = sizeof($itemList);
				foreach ($CLIENT_CHARGES_DATA as $CHARGE_KEY => $CHARGE_VALUE) {
					$TOTAL_TAXABLE_AMT 	+= $CHARGE_VALUE['taxableAmount'];
					$TOTAL_AMOUNT       += $CHARGE_VALUE['totalItemAmount'];
					$TOTAL_TAX_AMOUNT 	+= $CHARGE_VALUE['gstAmount'];
					if($IsFromSameState){
						$TOTAL_CGST 		+= $CHARGE_VALUE['cgstAmt'];
						$TOTAL_SGST 		+= $CHARGE_VALUE['sgstAmt'];
					}else{
						$TOTAL_IGST 		+= $CHARGE_VALUE['igstAmt'];
					}
					$itemList[$count] 	= $CHARGE_VALUE;
					$count++;
				}
			}
			############# CHARGES DETAILS PUSH IN E INVOICE IF APPLICABLE ##############
			/*######## OTHER VALUE CALCULATION ##############*/
			$RENT_AMT 		=  (isset($data['rent_amt']) && !empty($data['rent_amt'])) ? $data['rent_amt'] : 0;
			$RENT_GST_AMT 	=  (isset($data['rent_gst_amt']) && !empty($data['rent_gst_amt'])) ? $data['rent_gst_amt'] : 0;
			$RENT_AMT 		=  _FormatNumberV2($RENT_AMT + $RENT_GST_AMT);
			$DISCOUNT_AMT 		=  (isset($data['discount_amt']) && !empty($data['discount_amt'])) ? $data['discount_amt'] : 0;
			$TCS_AMOUNT 		=  (isset($data['tcs_amount']) && !empty($data['tcs_amount'])) ? $data['tcs_amount'] : 0;
			$OTHER_VALUE 		=  _FormatNumberV2(($RENT_AMT + $TCS_AMOUNT) - $DISCOUNT_AMT);
			$INVOICE_AMT 		=  $TOTAL_AMOUNT + $OTHER_VALUE;
			$DIFFRENCE_AMT  	=  (round($INVOICE_AMT)- $INVOICE_AMT);
			$TOTAL_OTHER_VAL 	=  _FormatNumberV2($OTHER_VALUE + $DIFFRENCE_AMT);
			$ROUND_INV_AMT  	=  round($INVOICE_AMT);
			/*######## OTHER VALUE CALCULATION ##############*/
			############# E INVOICE REQUEST DATA ######################

			$array['merchant_key'] 	= (!empty($MERCHANT_KEY)) ? $MERCHANT_KEY : "";
			$array['username'] 		= $GST_USER_NAME;
			$array['password'] 		= $GST_PASSWORD;
			$array['user_gst_in'] 	= $GST_GST_IN;
			// prd($data);
			######## SALLER DETAILS ###########
			$array["SellerDtls"]["Gstin"]       = $FROM_GST_IN;
			$array["SellerDtls"]["LglNm"]       = $FROM_TRD_NAME;
			$array["SellerDtls"]["TrdNm"]       = $FROM_TRD_NAME;
			$array["SellerDtls"]["Addr1"]       = (string)$FROM_ADDRESS_1;
			$array["SellerDtls"]["Addr2"]       = (string)$FROM_ADDRESS_2;
			$array["SellerDtls"]["Loc"]         = $FROM_PLACE;
			$array["SellerDtls"]["Pin"]         = (int)$FROM_PINCODE;
			$array["SellerDtls"]["Stcd"]        = $FROM_STATE_CODE;
			$array["SellerDtls"]["Ph"]          = "";
			$array["SellerDtls"]["Em"]          = "";

			######## BUYER DETAILS ###########
			$ClientPinCode 		= WmClientMaster::where("id",$data["client_master_id"])->value("pincode");
			$TO_ADDRESS_1 		= (isset($data['client_address']) && !empty($data['client_address'])) ? $data['client_address'] : '';
			$TO_ADDRESS_2 		= null;

			if(strlen($TO_ADDRESS_1) > 100){

				$ARR_STRING 	= WrodWrapString($TO_ADDRESS_1);
				$TO_ADDRESS_1 	= (!empty($ARR_STRING)) ? $ARR_STRING[0] : $TO_ADDRESS_1;
				$TO_ADDRESS_2 	= (isset($ARR_STRING[1]) &&!empty($ARR_STRING[1])) ? $ARR_STRING[1] : "";
			}
			$array["BuyerDtls"]["Gstin"]        = (isset($data['gstin_no']) && !empty($data['gstin_no'])) ? $data['gstin_no'] : '';
			$array["BuyerDtls"]["LglNm"]        = (isset($data['client_name']) && !empty($data['client_name'])) ? $data['client_name'] : '';
			$array["BuyerDtls"]["TrdNm"]        = (isset($data['client_name']) && !empty($data['client_name'])) ? $data['client_name'] : '';
			$array["BuyerDtls"]["Addr1"]        = (string)$TO_ADDRESS_1;
			$array["BuyerDtls"]["Addr2"]        = (string)$TO_ADDRESS_2;
			$array["BuyerDtls"]["Loc"]          = (isset($data['destination_city_name']) && !empty($data['destination_city_name'])) ? $data['destination_city_name'] : '';
			// $array["BuyerDtls"]["Pin"]          = (isset($data['shipping_pincode']) && !empty($data['shipping_pincode'])) ? (int)$data['shipping_pincode'] : '';
			$array["BuyerDtls"]["Pin"]          = (int)$ClientPinCode;
			$array["BuyerDtls"]["Stcd"]         = (isset($data['destination_state_code']) && !empty($data['destination_state_code'])) ? $data['destination_state_code'] : null;
			$array["BuyerDtls"]["Pos"]          = (isset($data['destination_state_code']) && !empty($data['destination_state_code'])) ? $data['destination_state_code'] : null;
			$array["BuyerDtls"]["Ph"]           = null;
			$array["BuyerDtls"]["Em"]           = null;
			$array["DispDtls"]                 	= null;
			$array["ShipDtls"]                 	= null;
			$array["EwbDtls"]                  	= null;
			$array["version"]                   = "1.1";
			$array["TranDtls"]["TaxSch"]        = "GST";
			$array["TranDtls"]["SupTyp"]        = "B2B";
			$array["TranDtls"]["RegRev"]        = "N";
			$array["TranDtls"]["EcmGstin"]      = null;
			// $array["TranDtls"]["IgstOnIntra"]   = ($IsFromSameState) ? "N" : "Y";
			$array["TranDtls"]["IgstOnIntra"]   = "N";
			$array["DocDtls"]["Typ"]            = (isset($data['dispatch_type']) && $data['dispatch_type'] == NON_RECYCLEBLE_TYPE) ? 'BIL' : 'INV';
			$array["DocDtls"]["No"]             = (isset($data['challan_no']) && !empty($data['challan_no'])) ? $data['challan_no'] : '';
			$array["DocDtls"]["Dt"]             = (isset($data['dispatch_date']) && !empty($data['dispatch_date'])) ? date("d/m/Y",strtotime($data['dispatch_date'])) : '';
			######## SUMMERY OF INVOICE DETAILS ###########
			$array["ValDtls"]["AssVal"]        = (float)_FormatNumberV2($TOTAL_TAXABLE_AMT);
			$array["ValDtls"]["CgstVal"]       = (float)_FormatNumberV2($TOTAL_CGST);
			$array["ValDtls"]["SgstVal"]       = (float)_FormatNumberV2($TOTAL_SGST);
			$array["ValDtls"]["IgstVal"]       = (float)_FormatNumberV2($TOTAL_IGST);
			$array["ValDtls"]["CesVal"]        = (isset($data['cessValue']) && !empty($data['cessValue'])) ? (float)_FormatNumberV2($data['cessValue']) : 0;
			$array["ValDtls"]["StCesVal"]      = (isset($data['cessNonAdvolValue']) && !empty($data['cessNonAdvolValue'])) ? (float)$data['cessNonAdvolValue'] : 0;
			$array["ValDtls"]["Discount"]      = 0;
			$array["ValDtls"]["OthChrg"]       = ($TOTAL_OTHER_VAL > 0) ?_FormatNumberV2((float)$TOTAL_OTHER_VAL) : 0;
			$array["ValDtls"]["RndOffAmt"]     = ($DIFFRENCE_AMT > 0) ? _FormatNumberV2((float)$DIFFRENCE_AMT) : 0;
			$array["ValDtls"]["TotInvVal"]     = (float)_FormatNumberV2($ROUND_INV_AMT);

			####### ITEM DETAILS ###########
			$i      = 1;
			$item   = array();
			foreach($itemList as $key => $value){
					$item[] = array(
						"SlNo"                  => $i,
						"PrdDesc"               => $value["productName"],
						"IsServc"               => (isset($value['is_service'])) ? $value['is_service'] :"N",
						"HsnCd"                 => $value["hsnCode"],
						"Qty"                   => _FormatNumberV2((float)$value["quantity"]),
						"Unit"                  => $value["qtyUnit"],
						"UnitPrice"             => _FormatNumberV2((float)$value["price"]),
						"TotAmt"                => _FormatNumberV2((float)$value["taxableAmount"]),
						"Discount"              => _FormatNumberV2((float)0),
						"PreTaxVal"             => _FormatNumberV2((float)0),
						"AssAmt"                => _FormatNumberV2((float)$value["taxableAmount"]),
						"GstRt"                 => _FormatNumberV2((float)$value["totalGstPercent"]),
						"IgstAmt"               => _FormatNumberV2((float)$value["igstAmt"]),
						"CgstAmt"               => _FormatNumberV2((float)$value["cgstAmt"]),
						"SgstAmt"               => _FormatNumberV2((float)$value["sgstAmt"]),
						"CesRt"                 => 0,
						"CesAmt"                => 0,
						"CesNonAdvlAmt"         => 0,
						"StateCesRt"            => 0,
						"StateCesAmt"           => 0,
						"StateCesNonAdvlAmt"    => 0,
						"OthChrg"               => 0,
						"TotItemVal"            => _FormatNumberV2((float)$value["totalItemAmount"]),
				);
				$i++;
			}
			$array["ItemList"]  =  $item;
			############ ITEM DETAILS ############
			############### END E INVOICE REQUEST ##################
			if(!empty($array)){
				$url 		= EWAY_BILL_PORTAL_URL."generate-einvoice";
				$client 	= new \GuzzleHttp\Client([
					'headers' => ['Content-Type' => 'application/json']
				]);
				$response 	= $client->request('POST', $url,
				 array(
					'form_params' => $array
				));
				$response 		= $response->getBody()->getContents();

				if(!empty($response)){
					$res   	= json_decode($response,true);
					if($res["Status"] == 1){
						$details 	= $res["Data"];
						$AckNo  	= (isset($details['AckNo'])) ? $details['AckNo']  : "";
						$AckDt  	= (isset($details['AckDt'])) ? $details['AckDt']  : "";
						$Irn    	= (isset($details['Irn'])) ? $details['Irn']      : "";
						$signedQr   = (isset($details['SignedQRCode'])) ? $details['SignedQRCode']  : "";
						self::where("id",$id)->update([
							"e_invoice_no" 			=> $Irn,
							"acknowledgement_date" 	=> $AckDt,
							"acknowledgement_no" 	=> $AckNo,
							"signed_qr_code" 		=> $signedQr,
							"updated_at" 			=> date("Y-m-d H:i:s"),
							"updated_by" 			=> Auth()->user()->adminuserid
						]);
					}
				}
				return $res;
			}
		}
	}
	/*
	Use 	: Cancel E invoice Number
	Author 	: Axay Shah
	Date  	: 19 April 2021
	*/
	public static function CancelEInvoice($request){
		$res 				= array();
		$DISPATCH_ID   		= (isset($request['dispatch_id']) && !empty($request['dispatch_id'])) ? $request['dispatch_id'] : "";
		$IRN   				= (isset($request['irn']) && !empty($request['irn'])) ? $request['irn'] : "";
		$CANCEL_REMARK  	= (isset($request['CnlRem']) && !empty($request['CnlRem'])) ? $request['CnlRem'] : '';
		$CANCEL_RSN_CODE 	= (isset($request['CnlRsn']) && !empty($request['CnlRsn'])) ? $request['CnlRsn'] : '';
		$data 				= self::find($DISPATCH_ID);
		if($data){
			// prd($data);
			$MERCHANT_KEY 	= CompanyMaster::where("company_id",Auth()->user()->company_id)->value('merchant_key');
			$DepartmentData = WmDepartment::find($data->bill_from_mrf_id);
			$array['merchant_key'] 	= (!empty($MERCHANT_KEY)) ? $MERCHANT_KEY : "";
			$GST_USER_NAME 	= ($DepartmentData && !empty($DepartmentData->gst_username)) ? $DepartmentData->gst_username : "";
			$GST_PASSWORD 	= ($DepartmentData && !empty($DepartmentData->gst_password)) ? $DepartmentData->gst_password : "";
			$GST_GST_IN 	= ($DepartmentData && !empty($DepartmentData->gst_in)) ? $DepartmentData->gst_in : "";
			$request["merchant_key"] 	= $MERCHANT_KEY;
			$request['username'] 		= $GST_USER_NAME;
			$request['password'] 		= $GST_PASSWORD;
			$request['user_gst_in'] 	= $GST_GST_IN;
			if(!empty($MERCHANT_KEY) && !empty($IRN)){
				$url 		= EWAY_BILL_PORTAL_URL."cancel-einvoice";
				$client 	= new \GuzzleHttp\Client([
					'headers' => ['Content-Type' => 'application/json']
				]);
				$response 	= $client->request('POST', $url,
				 array(
					'form_params' => $request
				));
				$response 		= $response->getBody()->getContents();
				if(!empty($response)){
					$res   	= json_decode($response,true);
					if($res["Status"] == 1){
						self::where("id",$DISPATCH_ID)
						->where("e_invoice_no",$IRN)
						->update([
							"e_invoice_no" 			=> "",
							"acknowledgement_date" 	=> "",
							"acknowledgement_no" 	=> "",
							"signed_qr_code" 		=> "",
							"updated_at" 			=> date("Y-m-d H:i:s"),
							"updated_by" 			=> Auth()->user()->adminuserid
						]);
					}
				}
				return $res;
			}
		}
		return $res;
	}
	/*
	Use 	: Generate E invoice Number Data
	Author 	: Axay Shah
	Date  	: 09 March 2021
	*/
	public static function CancelEinvoiceReasons(){
		$responseData 	= array();
		$data 			= CANCEL_RSN_ARRAY;
		$i 				= 0;
		foreach($data as $key => $value){
			$responseData[$i]["id"] 		= $key;
			$responseData[$i]["reason"] 	= $value;
			$i++;
		}

		return $responseData;
	}

	/*
	Use 	: Update Aggregtor Dispatch Flag
	Author 	: Axay Shah
	Date  	: 06 May 2021
	*/
	public static function UpdateAggregetorDispatchFlag($request)
	{
		$DispatchID 	= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ?  $request->dispatch_id : 0;
		$aggregatorFlag = (isset($request->aggregator_dispatch) && !empty($request->aggregator_dispatch)) ?  $request->aggregator_dispatch : 0;
		$data 			= self::where("id",$DispatchID)->update(["aggregator_dispatch"=> $aggregatorFlag]);
		return $data;
	}

	/*
	Use 	: Aggretgator Sales Report
	Author 	: Axay Shah
	Date  	: 10 May 2021
	*/
	public static function AggregtorSalesReport($request)
	{
		$TotalAmount 	= 0;
		$TotalQuantity 	= 0;
		$LoginUserID 	= Auth()->user()->adminuserid;
		$cityId 		= UserCityMpg::userAssignCity($LoginUserID,true);
		$startDate 		= (isset($request->startDate) && !empty($request->startDate)) ?  date("Y-m-d",strtotime($request->startDate)) : "";
		$endDate 		= (isset($request->endDate) && !empty($request->endDate)) ?  date("Y-m-d",strtotime($request->endDate)) : "";
		$productID 		= (isset($request->product_id) && !empty($request->product_id)) ?  date("Y-m-d",strtotime($request->product_id)) : "";
		$inc_mrf 		= (isset($request->inc_mrf) && !empty($request->inc_mrf)) ? $request->inc_mrf : "";
		$ex_mrf 		= (isset($request->exc_mrf) && !empty($request->exc_mrf)) ? $request->exc_mrf : "";
		$inc_bill_mrf 	= (isset($request->inc_bill_from_mrf) && !empty($request->inc_bill_from_mrf)) ? $request->inc_bill_from_mrf : "";
		$ex_bill_mrf 	= (isset($request->exc_bill_from_mrf) && !empty($request->exc_bill_from_mrf)) ? $request->exc_bill_from_mrf : "";

		$self 		= (new static)->getTable();
		$salesTbl 	= new WmSalesMaster();
		$DEPT 		= new WmDepartment();
		$sales 		= $salesTbl->getTable();
		$product 	= new WmProductMaster();
		$data 		= WmSalesMaster::select(DB::raw("PRO.id as product_id"),
											DB::raw("PRO.title as product_name"),
											DB::raw("PRO.net_suit_code as net_suit_code"),
											DB::raw("SUM($sales.quantity) as quantity"),
											DB::raw("SUM($sales.gross_amount) as gross_amount"))
					->join($self,"$sales.dispatch_id","=","$self.id")
					->join($product->getTable()." as PRO","$sales.product_id","=","PRO.id")
					->leftjoin($DEPT->getTable()." as DEPT","$self.master_dept_id","=","DEPT.id")
					->where("$self.aggregator_dispatch",1);
		if(!empty($productID)) {
			$data->where("PRO.id",$productID);
		}
		if(!empty($startDate) && !empty($endDate)) {
			$data->whereBetween("$self.dispatch_date",array($startDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME));
		} elseif(!empty($startDate)) {
			$data->whereBetween("$Dispatch.dispatch_date",array($startDate." ".GLOBAL_START_TIME,$startDate." ".GLOBAL_END_TIME));
		} elseif(!empty($startDate)) {
			$data->whereBetween("$Dispatch.dispatch_date",array($endDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME));
		}
		if(!empty($inc_mrf)) {
			$data->whereIn("$self.master_dept_id",$inc_mrf);
		}
		if(!empty($ex_mrf)) {
			$data->whereNotIn("$self.master_dept_id",$ex_mrf);
		}
		if(!empty($inc_bill_mrf)) {
			$data->whereIn("$self.bill_from_mrf_id",$inc_bill_mrf);
		}
		if(!empty($ex_bill_mrf)) {
			$data->whereNotIn("$self.bill_from_mrf_id",$ex_bill_mrf);
		}
		$data->where(function($query) use($self,$cityId) {
			$query->whereIn("DEPT.location_id",$cityId);
		});
		$result = $data->groupBy("product_id")->get()->toArray();
		if(!empty($result)) {
			foreach($result as $key =>  $value) {
				$result[$key]['quantity'] 	 	= _FormatNumberV2($value['quantity']);
				$result[$key]['gross_amount'] 	= _FormatNumberV2($value['gross_amount']);
				$TotalAmount 					+= $value['gross_amount'];
				$TotalQuantity 					+= $value['quantity'];
			}
		}
		$res 					= array();
		$res['result'] 			= $result;
		$res['totalAmount'] 	= _FormatNumberV2($TotalAmount);
		$res['totalQuantity'] 	= _FormatNumberV2($TotalQuantity);
		return $res;
	}

	public static function AggregatorPLReport($request)
	{
		$result 				= array();
		$LoginUserID 			= Auth()->user()->adminuserid;
		$cityId 				= UserCityMpg::userAssignCity($LoginUserID,true);
		$startDate 				= (isset($request->startDate) && !empty($request->startDate)) ?  date("Y-m-d",strtotime($request->startDate)) : "";
		$endDate 				= (isset($request->endDate) && !empty($request->endDate)) ?  date("Y-m-d",strtotime($request->endDate)) : "";
		$purchase_product_id 	= (isset($request->purchase_product_id) && !empty($request->purchase_product_id)) ?  $request->purchase_product_id : "";
		$sales_product_id 		= (isset($request->sales_product_id) && !empty($request->sales_product_id)) ?  $request->sales_product_id : "";
		$mrf_id 				= (isset($request->mrf_id) && !empty($request->mrf_id)) ?  $request->mrf_id : "";
		$bill_from 				= (isset($request->bill_from_mrf_id) && !empty($request->bill_from_mrf_id)) ? $request->bill_from_mrf_id : "";
		$origin 				= (isset($request->origin) && !empty($request->origin)) ? $request->origin : "";
		$client_id 				= (isset($request->client_id) && !empty($request->client_id)) ? $request->client_id : "";
		$inc_mrf 				= (isset($request->inc_mrf) && !empty($request->inc_mrf)) ? $request->inc_mrf : "";
		$ex_mrf 				= (isset($request->exc_mrf) && !empty($request->exc_mrf)) ? $request->exc_mrf : "";
		$inc_bill_mrf 			= (isset($request->inc_bill_from_mrf) && !empty($request->inc_bill_from_mrf)) ? $request->inc_bill_from_mrf : "";
		$ex_bill_mrf 			= (isset($request->exc_bill_from_mrf) && !empty($request->exc_bill_from_mrf)) ? $request->exc_bill_from_mrf : "";


		$self 			= (new static)->getTable();
		$WDP 			= new WmDispatchProduct();
		$salesTbl 		= new WmSalesMaster();
		$DEPT 			= new WmDepartment();
		$product 		= new WmProductMaster();
		$appointment 	= new Appoinment();
		$app_col 		= new AppointmentCollection();
		$app_col_detail = new AppointmentCollectionDetail();
		$customer 		= new CustomerMaster();
		$Department 	= new WmDepartment();
		$Vehicle 		= new VehicleMaster();
		$Client 		= new WmClientMaster();
		$Sales 			= new WmSalesMaster();
		$Transpoter 	= new TransporterDetailsMaster();
		$PurchasePro 	= new CompanyProductMaster();
		$Quality 		= new CompanyProductQualityParameter();
		$Credit 		= new WmInvoicesCreditDebitNotes();
		$creditDetails 	= new WmInvoicesCreditDebitNotesDetails();
		$cre 			= $Credit->getTable();
		$APP 			= $appointment->getTable();
		$data 			= WmDispatch::select("$APP.app_date_time",
											"$APP.invoice_no as purchase_invoice_no",
											"$APP.appointment_id",
											"WDP.id as dispatch_product_id",
											\DB::raw("CONCAT(CUS.first_name,' ',CUS.middle_name,' ',CUS.last_name) as purchase_party"),
											\DB::raw("APP_DET.product_customer_price  as purchase_rate"),
											\DB::raw("APP_DET.actual_coll_quantity  as purchase_weight"),
											\DB::raw("APP_DET.product_id  as purchase_product_id"),
											\DB::raw("APP_DET.price  as purchase_amt"),
											\DB::raw("$self.dispatch_date as sales_date"),
											\DB::raw("$self.id as dispatch_id"),
											\DB::raw("MRF.department_name as mrf_name"),
											\DB::raw("BILL_MRF.department_name as bill_from_mrf"),
											\DB::raw("$self.challan_no"),
											\DB::raw("$self.dispatch_date"),
											\DB::raw("VEH.vehicle_number"),
											\DB::raw("CLI.client_name"),
											\DB::raw("SALES.rate as sales_rate"),
											\DB::raw("SALES.product_id as sales_product_id"),
											\DB::raw("SALES.quantity as sales_quantity"),
											\DB::raw("SALES.gross_amount"),
											\DB::raw("TRANS.rate as transporter_rate"),
											\DB::raw("TRANS.demurrage"),
											\DB::raw("CONCAT(PURCHASE.name,' ',Q.parameter_name) AS purchase_product"),
											\DB::raw("SALES_PRO.title as sales_product"),
											\DB::raw("WDP.epr_rate"))
							->leftjoin($APP,"$APP.appointment_id","=","$self.appointment_id")
							->leftjoin($WDP->getTable()." as WDP","$self.id","=","WDP.dispatch_id")
							->leftjoin($Sales->getTable()." as SALES","WDP.id","=","SALES.dispatch_product_id")
							->leftjoin($app_col_detail->getTable()." as APP_DET","WDP.collection_detail_id","=","APP_DET.collection_detail_id")
							->leftjoin($customer->getTable()." as CUS","$APP.customer_id","=","CUS.customer_id")
							->leftjoin($PurchasePro->getTable()." as PURCHASE","APP_DET.product_id","=","PURCHASE.id")
							->leftjoin($Quality->getTable()." as Q","PURCHASE.id","=","Q.product_id")
							->leftjoin($Department->getTable()." as MRF","$self.master_dept_id","=","MRF.id")
							->leftjoin($Department->getTable()." as BILL_MRF","$self.bill_from_mrf_id","=","BILL_MRF.id")
							->leftjoin($Vehicle->getTable()." as VEH","$self.vehicle_id","=","VEH.vehicle_id")
							->leftjoin($Client->getTable()." as CLI","$self.client_master_id","=","CLI.id")
							->leftjoin($product->getTable()." as SALES_PRO","SALES.product_id","=","SALES_PRO.id")
							->leftjoin($Transpoter->getTable()." as TRANS","$self.transporter_po_id","=","TRANS.id")
							->where("$self.approval_status",1);
		$data->where(function($query) use($request,$self) {
			$query->where("$self.aggregator_dispatch",1);
			$query->orWhere("$self.virtual_target",1);
		});
		if(!empty($startDate) & !empty($endDate)) {
			$data->whereBetween("$self.dispatch_date",array($startDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME));
		} elseif(!empty($startDate)) {
			$data->whereBetween("$self.dispatch_date",array($startDate." ".GLOBAL_START_TIME,$startDate." ".GLOBAL_END_TIME));
		} elseif(!empty($endDate)) {
			$data->whereBetween("$self.dispatch_date",array($endDate." ".GLOBAL_START_TIME,$endDate." ".GLOBAL_END_TIME));
		}
		if(!empty($origin)) {
			$data->where("$APP.customer_id",$origin);
		}
		if(!empty($client_id)) {
			$data->where("$self.client_master_id",$client_id);
		}
		if(!empty($sales_product_id)) {
			$data->where("SALES.product_id",$sales_product_id);
		}
		if(!empty($purchase_product_id)) {
			$data->where("APP_DET.product_id",$purchase_product_id);
		}
		if(!empty($inc_mrf)) {
			$data->whereIn("$self.master_dept_id",$inc_mrf);
		}
		if(!empty($ex_mrf)) {
			$data->whereNotIn("$self.master_dept_id",$ex_mrf);
		}
		if(!empty($inc_bill_mrf)) {
			$data->whereIn("$self.bill_from_mrf_id",$inc_bill_mrf);
		}
		if(!empty($ex_bill_mrf)) {
			$data->whereNotIn("$self.bill_from_mrf_id",$ex_bill_mrf);
		}
		$res  = $data->orderBy("$APP.appointment_id","DESC")->get()->toArray();
		$TOTAL_PURCHASE_WEIGHT 		 = 0;
		$TOTAL_PURCHASE_AMOUNT 		 = 0;
		$TOTAL_COST 		   		 = 0;
		$TOTAL_SALES_QTY 	   		 = 0;
		$TOTAL_SALES_AMOUNT    		 = 0;
		$TOTAL_GROSS_PROFIT    		 = 0;
		$TOTAL_DEBIT_NOTE_TO_PARTY   = 0;
		$TOTAL_DEBIT_AMOUNT    		 = 0;
		$TOTAL_DEBIT_FOR_NEPRA       = 0;	
		$TOTAL_NET_PROFIT       	 = 0;
		$array 					     = array();
		if(!empty($res))
		{
			foreach ($res as $key => $value) {
				$EPR_RATE 				= 0;
				$EPR_EARNING 			= 0;
				$EPR_GROSS_PROFIT 		= 0;
				$DEBIT_NOTE_FOR_NEPRA 	= 0;
				$EARNING_COST 			= 0;
				$DEBIT_NOTE_AMT 		= 0;
				$DEBIT_TO_PARTY 		= 0;
				$array[$key] 			= $value;
				$PURCHASE_WEIGHT 		= (!empty($value['purchase_weight'])) ? $value['purchase_weight'] : 0;
				$CreditNoteData 		= WmInvoicesCreditDebitNotes::join($creditDetails->getTable()." as CRN_DTL","$cre.id","=","CRN_DTL.cd_notes_id")
											->where("$cre.dispatch_id",$value['dispatch_id'])
											->where("$cre.status",3)
											->where("$cre.notes_type",0)
											->where("CRN_DTL.dispatch_product_id",$value['dispatch_product_id'])
											->where("CRN_DTL.product_id",$value['sales_product_id'])
											->orderBy("$cre.id","DESC")
											->get()
											->toArray();
				if(!empty($CreditNoteData)) {
					foreach($CreditNoteData as $CN_KEY => $val){
						$array[$CN_KEY]['credit_debit_id'] = $val["id"];
						$DEBIT_NOTE_AMT += _FormatNumberV2($val['revised_gross_amount']);
						if($val['change_in'] == 1) {
							$DEBIT_TO_PARTY +=_FormatNumberV2($PURCHASE_WEIGHT * $val['revised_rate']);
						} elseif($val['change_in'] == 2) {
							$DEBIT_TO_PARTY += _FormatNumberV2($value['purchase_rate'] * $val['revised_quantity']);
						} elseif($val['change_in'] == 3) {
							if($val['new_rate'] == 0 && $val["new_quantity"] == 0){
								$DEBIT_TO_PARTY += _FormatNumberV2($value['revised_rate'] * $value['revised_quantity']);
							}
						}
					}
				}
				$array[$key]['purchase_date'] 			= $value['app_date_time'];
				$array[$key]['purchase_invoice_no'] 	= $value['purchase_invoice_no'];
				$array[$key]['purchase_party_name'] 	= $value['purchase_party'];
				$array[$key]['sales_party_name'] 		= $value['client_name'];
				$array[$key]['sales_party_name'] 		= $value['client_name'];
				$array[$key]['purchase_weight'] 		= _FormatNumberV2($PURCHASE_WEIGHT);
				$array[$key]['purchase_rate'] 			= $value['purchase_rate'];
				$array[$key]['dispatch_date'] 			= date("Y-m-d",strtotime($value['sales_date']));
				$array[$key]['sales_date'] 				= date("Y-m-d",strtotime($value['sales_date']));
				$array[$key]['mrf_name'] 				= $value['mrf_name'];
				$array[$key]['bill_from_mrf'] 			= $value['bill_from_mrf'];
				$array[$key]['sales_invoice_no'] 		= $value['challan_no'];
				$array[$key]['client_name'] 			= $value['client_name'];
				$array[$key]['gross_amount'] 			= $value['gross_amount'];
				$array[$key]['purchase_product'] 		= $value['purchase_product'];
				$array[$key]['sales_product'] 			= $value['sales_product'];

				####### REMOVE EPR DATA FROM RESPONSE ##########
				$PURCHASE_AMT 			= (!empty($value['purchase_amt'])) ? $value['purchase_amt'] : 0;
				$VEHICLE_COST 			= _FormatNumberV2($value['transporter_rate'] + $value['demurrage']);
				$PER_KG_LOGISTIC 		= (!empty($PURCHASE_WEIGHT) && !empty($VEHICLE_COST)) ?  $PURCHASE_WEIGHT / $VEHICLE_COST : 0;
				$SALES_AMT 				= (!empty($value['gross_amount'])) ? $value['gross_amount']: 0;
				$TOTAL_COST 			= _FormatNumberV2($PURCHASE_AMT + $VEHICLE_COST);
				$EPR_EARNING 			= 0;
				$GROSS_PROFIT 			= _FormatNumberV2($SALES_AMT - $TOTAL_COST);
				$DEBIT_NOTE_FOR_NEPRA 	= _FormatNumberV2($DEBIT_NOTE_AMT - $DEBIT_TO_PARTY) ;
				$NET_PROFIT 			= _FormatNumberV2($GROSS_PROFIT - $DEBIT_NOTE_FOR_NEPRA);
				$EARNING_COST 			= ($PURCHASE_WEIGHT > 0 && $NET_PROFIT > 0) ? _FormatNumberV2($NET_PROFIT / $PURCHASE_WEIGHT) : 0;

				####### REMOVE EPR DATA FROM RESPONSE ##########
				$array[$key]['sales_amt'] 					= _FormatNumberV2($SALES_AMT);
				$array[$key]['purchase_amt'] 				= _FormatNumberV2($PURCHASE_AMT);
				$array[$key]['vehicle_cost'] 				= _FormatNumberV2($VEHICLE_COST);
				$array[$key]['per_kg_logistic'] 			= _FormatNumberV2($PER_KG_LOGISTIC);
				$array[$key]['total_cost'] 					= _FormatNumberV2($TOTAL_COST);
				$array[$key]['gross_profit'] 				= _FormatNumberV2($GROSS_PROFIT);
				$array[$key]['debit_note_amt'] 				= _FormatNumberV2($DEBIT_NOTE_AMT);
				$array[$key]['epr_rate'] 					= _FormatNumberV2(0);
				$array[$key]['epr_earning'] 				= _FormatNumberV2(0);
				$array[$key]['epr_earning_gross_profit']	= _FormatNumberV2(0);
				$array[$key]['debit_to_party']				= _FormatNumberV2($DEBIT_TO_PARTY);
				$array[$key]['debit_note_for_nepra']		= _FormatNumberV2($DEBIT_NOTE_FOR_NEPRA);
				$array[$key]['net_profit']					= _FormatNumberV2($NET_PROFIT);
				$array[$key]['earning_cost_in_kg']			= _FormatNumberV2($EARNING_COST);

				$TOTAL_PURCHASE_WEIGHT 		 +=  $PURCHASE_WEIGHT;
				$TOTAL_PURCHASE_AMOUNT 		 += _FormatNumberV2($PURCHASE_AMT);
				$TOTAL_COST 		   		 += _FormatNumberV2($TOTAL_COST);
				$TOTAL_SALES_QTY 	   		 += $value['sales_quantity'];
				$TOTAL_SALES_AMOUNT    		 += _FormatNumberV2($SALES_AMT);
				$TOTAL_GROSS_PROFIT    		 += _FormatNumberV2($GROSS_PROFIT);
				$TOTAL_DEBIT_NOTE_TO_PARTY   += _FormatNumberV2($DEBIT_TO_PARTY);
				$TOTAL_DEBIT_AMOUNT    		 += _FormatNumberV2($DEBIT_NOTE_AMT);
				$TOTAL_DEBIT_FOR_NEPRA       += _FormatNumberV2($DEBIT_NOTE_FOR_NEPRA);
				$TOTAL_NET_PROFIT       	 += _FormatNumberV2($NET_PROFIT);
			}
		}
		$result 								= array();
		$result['result'] 			 		 	= $array;
		$result['TOTAL_PURCHASE_WEIGHT'] 		= _FormatNumberV2($TOTAL_PURCHASE_WEIGHT);
		$result['TOTAL_PURCHASE_AMOUNT'] 	 	= _FormatNumberV2($TOTAL_PURCHASE_AMOUNT);
		$result['TOTAL_COST'] 		   		 	= _FormatNumberV2($TOTAL_COST);
		$result['TOTAL_SALES_QTY'] 	   		 	= _FormatNumberV2($TOTAL_SALES_QTY);
		$result['TOTAL_SALES_AMOUNT']    		= _FormatNumberV2($TOTAL_SALES_AMOUNT);
		$result['TOTAL_GROSS_PROFIT']    		= _FormatNumberV2($TOTAL_GROSS_PROFIT);
		$result['TOTAL_DEBIT_NOTE_TO_PARTY']   	= _FormatNumberV2($TOTAL_DEBIT_NOTE_TO_PARTY);
		$result['TOTAL_DEBIT_AMOUNT']    		= _FormatNumberV2($TOTAL_DEBIT_AMOUNT);
		$result['TOTAL_DEBIT_FOR_NEPRA']       	= _FormatNumberV2($TOTAL_DEBIT_FOR_NEPRA);	
		$result['TOTAL_NET_PROFIT']       	 	= _FormatNumberV2($TOTAL_NET_PROFIT);	
		return $result;
	}

	/*
	Use     	: Ready To Dispatch Report
	DevlopedBy  : Kalpak Prajapati 
	ConvertedBy : Axay Shah
	Date 		: 16 September,2021
	*/
	public static function readytodispachByMRF($request)
	{
		$arrResult 		= array();
		$WhereCond 		= "";
		$StockDate		= date("Y-m-d");
		$arrMRF 		= array();
		if (isset($request->product_id) && !empty($request->product_id)) {
			if (is_array($request->product_id)) {
				$WhereCond .= " AND wm_product_master.id IN (".implode(",",$request->product_id).")";
			} else if (!is_array($request->product_id)) {
				$ProductIDs = explode(",",$request->product_id);
				$WhereCond .= " AND wm_product_master.id IN (".implode(",",$ProductIDs).")";
			}
		}
		$SELECT_SQL 	= "	SELECT wm_department.department_name as MRF_NAME,
							wm_product_master.title AS PRODUCT_NAME,
							getSalesProductCurrentStock(stock_ladger.product_id,'".$StockDate."',stock_ladger.mrf_id,0) AS Current_Stock,
							stock_ladger.product_id AS PRODUCT_ID,
							stock_ladger.avg_price AS AVG_PRICE,
							stock_ladger.mrf_id AS MRF_ID,
							wm_product_master.product_category AS PRODUCT_CATEGORY
							FROM stock_ladger
							INNER JOIN wm_product_master ON wm_product_master.id = stock_ladger.product_id
							INNER JOIN wm_department ON wm_department.id = stock_ladger.mrf_id
							INNER JOIN wm_product_saleable_tagging ON wm_product_master.id = wm_product_saleable_tagging.product_id AND wm_product_saleable_tagging.mrf_id = wm_department.id
							WHERE stock_ladger.product_type = ".PRODUCT_SALES."
							AND stock_ladger.stock_date = '".$StockDate."'
							AND ( wm_product_master.product_category != 'Inert' OR wm_product_master.product_category  IS NULL ) 
							$WhereCond
							HAVING Current_Stock > 0
							ORDER BY MRF_NAME ASC, Current_Stock DESC, PRODUCT_NAME ASC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		$productArr 	= array();
		if (!empty($SELECT_RES)) {
			$counter 				= 0;
			$AVAILABLE_MRF_STOCK	= 0;
			$TOTAL_MRF_STOCK		= 0;
			$TOTAL_MRF_STOCK_VALUE	= 0;
			$TOTAL_MRF_PLAN_VALUE	= 0;
			foreach($SELECT_RES as $ROW)
			{
				$REMAINING_STOCK = 0;
				if (!in_array($ROW->MRF_ID,$arrMRF)) {
					if (isset($KEY)) {
						$arrResult[$KEY]['AVAILABLE_MRF_STOCK'] 	= _FormatNumberV2($AVAILABLE_MRF_STOCK);
						$arrResult[$KEY]['TOTAL_MRF_STOCK'] 		= _FormatNumberV2($TOTAL_MRF_STOCK);
						$arrResult[$KEY]['TOTAL_MRF_STOCK_VALUE'] 	= _FormatNumberV2($TOTAL_MRF_STOCK_VALUE);
						$arrResult[$KEY]['TOTAL_MRF_PLAN_VALUE'] 	= _FormatNumberV2($TOTAL_MRF_PLAN_VALUE);
						$AVAILABLE_MRF_STOCK						= 0;
						$TOTAL_MRF_STOCK							= 0;
						$TOTAL_MRF_STOCK_VALUE						= 0;
						$TOTAL_MRF_PLAN_VALUE						= 0;
					}
					array_push($arrMRF,$ROW->MRF_ID);
				}
				$KEY 									= array_search($ROW->MRF_ID,$arrMRF);
				$productArr['PRODUCT_NAME'] 			= $ROW->PRODUCT_NAME;
				$productArr['CURRENT_STOCK']			= $ROW->Current_Stock;
				$productArr['SALES_PLAN']				= array();
				$productArr['REMAINING_STOCK'] 			= 0;
				$productArr['STOCK_VALUE']				= 0;
				$arrAvgPrice 							= self::GetProductPlanAvgPrice($ROW->PRODUCT_ID,$ROW->MRF_ID,$ROW->Current_Stock,$StockDate);
				$PLAN_QTY 		= 0;
				$PLAN_AVG_PRICE = '-';
				$PLAN_VALUE 	= 0;
				if(!empty($arrAvgPrice)){
					$PLAN_QTY 		= $arrAvgPrice['TOTAL_QTY'];
					$PLAN_AVG_PRICE = (!empty($arrAvgPrice['AVG_PRICE'])) ?  $arrAvgPrice['AVG_PRICE'] : '-';
				}
				$PLAN_VALUE 					= (isset($arrAvgPrice['AVG_PRICE']) && !empty($arrAvgPrice['AVG_PRICE']))?_FormatNumberV2($ROW->Current_Stock * $PLAN_AVG_PRICE): 0;
				$productArr['PLAN_QTY']			= _FormatNumberV2($PLAN_QTY);
				$productArr['PLAN_AVG_PRICE']	= ($PLAN_AVG_PRICE > 0) ? _FormatNumberV2($PLAN_AVG_PRICE) : '-';
				$productArr['PLAN_VALUE']		= ($PLAN_VALUE > 0) ? _FormatNumberV2($PLAN_VALUE) : '-';
				$productArr['STOCK_AVG_PRICE']	= ($ROW->AVG_PRICE > 0) ? _FormatNumberV2($ROW->AVG_PRICE) : '-';
				if ($ROW->PRODUCT_CATEGORY != "RDF") {
					$AVAILABLE_MRF_STOCK 	+= ($ROW->Current_Stock);
					$TOTAL_MRF_STOCK 		+= $PLAN_VALUE;
					$TOTAL_MRF_STOCK_VALUE 	+= $productArr['STOCK_VALUE'];
					$TOTAL_MRF_PLAN_VALUE	+= $PLAN_VALUE;
				}
				$arrResult[$KEY]['MRF_NAME'] 			= str_replace(array("MRF-","V-","MRF - ","BASE STATION -","BASE STATION - "),"",$ROW->MRF_NAME);
				$arrResult[$KEY]['PRODUCT_DETAILS'][] 	= $productArr;
			}
			if (isset($KEY)) {
				$arrResult[$KEY]['AVAILABLE_MRF_STOCK'] 	= _FormatNumberV2($AVAILABLE_MRF_STOCK);
				$arrResult[$KEY]['TOTAL_MRF_STOCK'] 		= _FormatNumberV2($TOTAL_MRF_STOCK);
				$arrResult[$KEY]['TOTAL_MRF_STOCK_VALUE'] 	= _FormatNumberV2($TOTAL_MRF_STOCK_VALUE);
				$arrResult[$KEY]['TOTAL_MRF_PLAN_VALUE'] 	= _FormatNumberV2($TOTAL_MRF_PLAN_VALUE);
				$AVAILABLE_MRF_STOCK						= 0;
				$TOTAL_MRF_STOCK							= 0;
				$TOTAL_MRF_STOCK_VALUE						= 0;
			}
		}
		return ["Page_Title" => "Ready To Dispatch (By MRF)","arrResult" => $arrResult,"query"=>$SELECT_SQL];
	}

	/*
	Use     	: Ready To Dispatch Report
	DevlopedBy  : Kalpak Prajapati 
	ConvertedBy : Axay Shah
	Date 		: 16 September,2021
	*/
	public static function readytodispachByBaseLocation($request)
	{
		$arrResult 		= array();
		$WhereCond 		= "";
		$StockDate		= date("Y-m-d");
		$arrMRF 		= array();
		if (isset($request->product_id) && !empty($request->product_id)) {
			if (is_array($request->product_id)) {
				$WhereCond .= " AND wm_product_master.id IN (".implode(",",$request->product_id).")";
			} else if (!is_array($request->product_id)) {
				$ProductIDs = explode(",",$request->product_id);
				$WhereCond .= " AND wm_product_master.id IN (".implode(",",$ProductIDs).")";
			}
		}
		$SELECT_SQL 	= "	SELECT base_location_master.base_location_name as MRF_NAME,
							wm_product_master.title AS PRODUCT_NAME,
							getSalesProductCurrentStock(stock_ladger.product_id,'".$StockDate."',wm_department.base_location_id,1) AS Current_Stock,
							stock_ladger.product_id AS PRODUCT_ID,
							stock_ladger.avg_price AS AVG_PRICE,
							wm_department.base_location_id AS BASE_LOCATION_ID,
							wm_product_master.product_category AS PRODUCT_CATEGORY
							FROM stock_ladger
							INNER JOIN wm_product_master ON wm_product_master.id = stock_ladger.product_id
							INNER JOIN wm_department ON wm_department.id = stock_ladger.mrf_id
							INNER JOIN wm_product_saleable_tagging ON wm_product_master.id = wm_product_saleable_tagging.product_id AND wm_product_saleable_tagging.mrf_id = wm_department.id
							INNER JOIN base_location_master ON base_location_master.id = wm_department.base_location_id
							WHERE stock_ladger.product_type = ".PRODUCT_SALES."
							AND stock_ladger.stock_date = '".$StockDate."'
							AND ( wm_product_master.product_category != 'Inert' OR wm_product_master.product_category  IS NULL )
							$WhereCond
							GROUP BY wm_department.base_location_id,stock_ladger.product_id
							HAVING Current_Stock > 0
							ORDER BY MRF_NAME ASC, Current_Stock DESC, PRODUCT_NAME ASC";
		$SELECT_RES 	= DB::connection('master_database')->select($SELECT_SQL);
		$productArr 	= array();
		if (!empty($SELECT_RES)) {
			$counter 				= 0;
			$AVAILABLE_MRF_STOCK	= 0;
			$TOTAL_MRF_STOCK		= 0;
			$TOTAL_MRF_STOCK_VALUE	= 0;
			$TOTAL_MRF_PLAN_VALUE	= 0;
			foreach($SELECT_RES as $ROW)
			{
				$REMAINING_STOCK = 0;
				if (!in_array($ROW->BASE_LOCATION_ID,$arrMRF)) {
					if (isset($KEY)) {
						$arrResult[$KEY]['AVAILABLE_MRF_STOCK'] 	= _FormatNumberV2($AVAILABLE_MRF_STOCK);
						$arrResult[$KEY]['TOTAL_MRF_STOCK'] 		= _FormatNumberV2($TOTAL_MRF_STOCK);
						$arrResult[$KEY]['TOTAL_MRF_STOCK_VALUE'] 	= _FormatNumberV2($TOTAL_MRF_STOCK_VALUE);
						$arrResult[$KEY]['TOTAL_MRF_PLAN_VALUE'] 	= _FormatNumberV2($TOTAL_MRF_PLAN_VALUE);
						$AVAILABLE_MRF_STOCK						= 0;
						$TOTAL_MRF_STOCK							= 0;
						$TOTAL_MRF_STOCK_VALUE						= 0;
						$TOTAL_MRF_PLAN_VALUE						= 0;
					}
					array_push($arrMRF,$ROW->BASE_LOCATION_ID);
				}
				$KEY 									= array_search($ROW->BASE_LOCATION_ID,$arrMRF);
				$productArr['PRODUCT_NAME'] 			= $ROW->PRODUCT_NAME;
				$productArr['CURRENT_STOCK'] 			= $ROW->Current_Stock;
				$arrProductSalesPlan 					= self::getProductSalesPlan($ROW->PRODUCT_ID,$ROW->BASE_LOCATION_ID,$ROW->Current_Stock,$StockDate,1);
				$productArr['SALES_PLAN']				= $arrProductSalesPlan['SALES_PLAN'];
				$productArr['REMAINING_STOCK'] 			= _FormatNumberV2($arrProductSalesPlan['REMAINING_STOCK']);
				$productArr['STOCK_AVG_PRICE']			= $ROW->AVG_PRICE;
				$productArr['STOCK_VALUE']				= round($productArr['REMAINING_STOCK'] * $ROW->AVG_PRICE);
				$productArr['STOCK_VALUE']				= _FormatNumberV2(($productArr['REMAINING_STOCK'] > 0)?(floatval($productArr['REMAINING_STOCK']) * floatval($ROW->AVG_PRICE)):0);
				$arrAvgPrice 							= self::GetProductPlanAvgPrice($ROW->PRODUCT_ID,$ROW->BASE_LOCATION_ID,$ROW->Current_Stock,$StockDate,1);
				$PLAN_QTY 		= 0;
				$PLAN_AVG_PRICE = 0;
				$PLAN_VALUE 	= 0;
				if(!empty($arrAvgPrice)){
					$PLAN_QTY 		= $arrAvgPrice['TOTAL_QTY'];
					$PLAN_AVG_PRICE = (!empty($arrAvgPrice['AVG_PRICE']))?$arrAvgPrice['AVG_PRICE']:'-';
				}
				$PLAN_VALUE 					= (isset($arrAvgPrice['AVG_PRICE']) && !empty($arrAvgPrice['AVG_PRICE']))?_FormatNumberV2($ROW->Current_Stock * $PLAN_AVG_PRICE):_FormatNumberV2($ROW->Current_Stock * $ROW->AVG_PRICE);
				$productArr['PLAN_QTY']			= _FormatNumberV2($PLAN_QTY);
				$productArr['PLAN_AVG_PRICE']	= ($PLAN_AVG_PRICE > 0) ? _FormatNumberV2($PLAN_AVG_PRICE) : '-';
				$productArr['PLAN_VALUE']		= ($PLAN_VALUE > 0) ? _FormatNumberV2($PLAN_VALUE) : '-';
				$productArr['STOCK_AVG_PRICE']	= ($ROW->AVG_PRICE > 0) ? _FormatNumberV2($ROW->AVG_PRICE) : '-';
				if ($ROW->PRODUCT_CATEGORY != "RDF") {
					$AVAILABLE_MRF_STOCK 	+= ($ROW->Current_Stock);
					$TOTAL_MRF_STOCK 		+= ($productArr['REMAINING_STOCK']);
					$TOTAL_MRF_STOCK_VALUE 	+= $productArr['STOCK_VALUE'];
					$TOTAL_MRF_PLAN_VALUE 	+= $PLAN_VALUE;
				}
				$arrResult[$KEY]['MRF_NAME'] 			= str_replace(array("MRF-","V-","MRF - ","BASE STATION -","BASE STATION - "),"",$ROW->MRF_NAME);
				$arrResult[$KEY]['PRODUCT_DETAILS'][] 	= $productArr;
				$counter++;
			}
			if (isset($KEY)) {
				$arrResult[$KEY]['AVAILABLE_MRF_STOCK'] 	= _FormatNumberV2($AVAILABLE_MRF_STOCK);
				$arrResult[$KEY]['TOTAL_MRF_STOCK'] 		= _FormatNumberV2($TOTAL_MRF_STOCK);
				$arrResult[$KEY]['TOTAL_MRF_STOCK_VALUE'] 	= _FormatNumberV2($TOTAL_MRF_STOCK_VALUE);
				$arrResult[$KEY]['TOTAL_MRF_PLAN_VALUE'] 	= _FormatNumberV2($TOTAL_MRF_PLAN_VALUE);
				$AVAILABLE_MRF_STOCK						= 0;
				$TOTAL_MRF_STOCK							= 0;
				$TOTAL_MRF_STOCK_VALUE						= 0;
				$TOTAL_MRF_PLAN_VALUE						= 0;
			}
		}
		return ["Page_Title"=>"Ready To Dispatch (By Base Location)","arrResult"=>$arrResult,"arrMRF"=>$arrMRF];
	}

	/*
	Use     	: get Product Sales Plan For the Day
	DevlopedBy  : Kalpak Prajapati
	ConvertedBy : Axay Shah
	Date 		: 16 September,2021
	*/
	public static function getProductSalesPlan($product_id,$mrf_id,$current_stock,$sales_date,$BaseLocation=false)
	{
		$Sales_Qty 				= 0;
		$arrProductSalesPlan	= array('SALES_PLAN'=>array(),'REMAINING_STOCK'=>floatval($current_stock));
		if (!$BaseLocation) {
			$SELECT_SQL = "	SELECT wm_client_master.client_name AS CLIENT_NAME,
							wm_dispatch_plan_product.qty AS SALES_QTY,
							wm_dispatch_plan_product.rate AS SALES_RATE
							FROM wm_dispatch_plan_product
							INNER JOIN wm_dispatch_plan ON wm_dispatch_plan_product.dispatch_plan_id = wm_dispatch_plan.id
							INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch_plan.client_master_id
							WHERE '$sales_date' BETWEEN wm_dispatch_plan.dispatch_plan_date AND wm_dispatch_plan.valid_last_date
							AND wm_dispatch_plan.approval_status IN (0,1)
							AND wm_dispatch_plan.master_dept_id = $mrf_id
							AND wm_dispatch_plan_product.sales_product_id = $product_id";
		} else {
			$SELECT_SQL = "	SELECT wm_client_master.client_name AS CLIENT_NAME,
							wm_dispatch_plan_product.qty AS SALES_QTY,
							wm_dispatch_plan_product.rate AS SALES_RATE
							FROM wm_dispatch_plan_product
							INNER JOIN wm_dispatch_plan ON wm_dispatch_plan_product.dispatch_plan_id = wm_dispatch_plan.id
							INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch_plan.client_master_id
							INNER JOIN wm_department ON wm_dispatch_plan.master_dept_id = wm_department.id
							WHERE '$sales_date' BETWEEN wm_dispatch_plan.dispatch_plan_date AND wm_dispatch_plan.valid_last_date
							AND wm_dispatch_plan.approval_status IN (0,1)
							AND wm_department.base_location_id = $mrf_id
							AND wm_dispatch_plan_product.sales_product_id = $product_id";
		}
		$SELECT_RES = \DB::select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			foreach($SELECT_RES as $SELECT_ROW) {

				$arrProductSalesPlan['SALES_PLAN'][]= array("CLIENT_NAME"=>$SELECT_ROW->CLIENT_NAME,
															"SALES_QTY"=>($SELECT_ROW->SALES_QTY > 0) ? _FormatNumberV2($SELECT_ROW->SALES_QTY,2) : 0,
															"SALES_RATE"=>($SELECT_ROW->SALES_RATE > 0) ? _FormatNumberV2($SELECT_ROW->SALES_RATE,2) : 0);
				$Sales_Qty += $SELECT_ROW->SALES_QTY;
			}
			if (!empty($Sales_Qty)) {
				$arrProductSalesPlan['REMAINING_STOCK'] = round((floatval($current_stock) - floatval($Sales_Qty)),2);
			}
		}
		return $arrProductSalesPlan;
	}

	/*
	Use     	: Get Product Sales Plan Avg Price 
	DevelopedBy : Axay Shah
	Date 		: 25 May,2022
	*/
	public static function GetProductPlanAvgPrice($product_id,$mrf_id,$current_stock,$sales_date,$BaseLocation=false)
	{
		$month 					= date("m",strtotime($sales_date));
		$year 					= date("Y",strtotime($sales_date));
		$Sales_Qty 				= 0;
		$arrProductSalesPlan 	= array();
		if (!$BaseLocation) {
			$SELECT_SQL = "SELECT 
				SUM(wm_projection_plan_details.qty) as TOTAL_QTY,
				SUM(wm_projection_plan_details.qty * wm_projection_plan_details.rate) as TOTAL_VALUE,
				SUM(wm_projection_plan_details.qty * wm_projection_plan_details.rate) / sum(wm_projection_plan_details.qty) as AVG_RATE
				FROM wm_projection_plan_details 
				LEFT JOIN wm_projection_plan on wm_projection_plan.id = wm_projection_plan_details.wm_projection_plan_id
				INNER JOIN wm_department on wm_projection_plan.mrf_id = wm_department.id
				WHERE wm_projection_plan.month = $month and wm_projection_plan.year = $year
				AND wm_projection_plan.mrf_id = $mrf_id
				AND wm_projection_plan.product_id = $product_id
				GROUP BY wm_projection_plan.product_id";
			} else {
				$SELECT_SQL =  "SELECT 
				SUM(wm_projection_plan_details.qty) as TOTAL_QTY,
				SUM(wm_projection_plan_details.qty * wm_projection_plan_details.rate) as TOTAL_VALUE,
				SUM(wm_projection_plan_details.qty * wm_projection_plan_details.rate) / sum(wm_projection_plan_details.qty) as AVG_RATE
				FROM wm_projection_plan_details 
				LEFT JOIN wm_projection_plan on wm_projection_plan.id = wm_projection_plan_details.wm_projection_plan_id
				INNER JOIN wm_department on wm_projection_plan.mrf_id = wm_department.id
				WHERE wm_projection_plan.month = $month and wm_projection_plan.year = $year
				AND wm_department.base_location_id = $mrf_id
				AND wm_projection_plan.product_id = $product_id
				GROUP BY wm_projection_plan.product_id";
			}
		$SELECT_RES = \DB::select($SELECT_SQL);
		if (!empty($SELECT_RES)) {
			$arrProductSalesPlan = array(
				"AVG_PRICE" 	=> _FormatNumberV2($SELECT_RES[0]->AVG_RATE,2),
				"TOTAL_QTY" 	=> _FormatNumberV2($SELECT_RES[0]->TOTAL_QTY,2),
				"TOTAL_VALUE" 	=> _FormatNumberV2($SELECT_RES[0]->TOTAL_VALUE,2),
			);
		}
		return $arrProductSalesPlan;
	}
	/*
	Use     	: get Product Sales Plan For the Day
	Develop 	: Axay Shah
	Date 		: 29 December,2021
	*/
	public static function GetInvoiceMimeType($id) {
		$DATA = \DB::table("send_invoice_data_detail_master")->where("id",$id)->first();
		$mime_type = "";
		if($DATA){
			$mime_type = mime_content_type(public_path().$DATA->path."/".$DATA->file_name);
		}
		return $mime_type;
	}
	/*
	Use     	: HSN WISE DETAILS REPORT
	Develop 	: Axay Shah
	Date 		: 12 APRIL,2022
	*/
	public static function GetHSNWiseSalesDetailsReport($req)
	{
		$startDate 	= (isset($req['startDate']) && !empty($req['startDate'])) ? date("Y-m-d",strtotime($req['startDate']))." ".GLOBAL_START_TIME : "";
		$endDate 	= (isset($req['endDate']) && !empty($req['endDate'])) ? date("Y-m-d",strtotime($req['endDate']))." ".GLOBAL_END_TIME : "";
		$MRFID 		= (isset($req['mrf_id']) && !empty($req['mrf_id'])) ? $req['mrf_id'] : 0;
		$MRF_ID_ARR = $MRFID;
		$STATIC_HSN = 39151000;
		if(is_array($MRFID)) {
			$MRFID = implode($MRFID,","); 
		}
		$arrResult 			= array();
		$WhereCond 			= "";
		$GRAND_TOTAL_AMT 	= 0;
		$GRAND_TOTAL_CN 	= 0;
		$GRAND_TOTAL_DN 	= 0;
		$GRAND_TOTAL 		= 0;
		$arrMRF 			= array();
		$RESULT_ARR 		= array();
		$COUNTER 			= 0;
		if(!empty($MRFID))
		{
			$SELECT_RES = WmProductMaster::select("id","title","hsn_code","net_suit_code")->get()->toArray();
			if (!empty($SELECT_RES))
			{
				foreach($SELECT_RES as $KEY => $VALUE)
				{
					$GST_AMT  	= 0;
					$NET_AMT  	= 0;
					$GROSS_AMT  = 0;
					$QTY  		= 0;
					$SGST_AMT  	= 0;
					$CGST_AMT  	= 0;
					$IGST_AMT  	= 0;
					$DISPATCH 	= "	SELECT
									wm_department.id as mrf_id,
									wm_department.department_name,
									SUM(wm_sales_master.quantity) AS TOTAL_QTY,
									(SUM(wm_sales_master.gross_amount)) AS TOTAL_GROSS_AMT,
									(SUM(wm_sales_master.gst_amount)) AS TOTAL_GST_AMT,
									(SUM(wm_sales_master.net_amount)) AS TOTAL_NET_AMT
									FROM wm_dispatch
									JOIN wm_department on wm_dispatch.bill_from_mrf_id = wm_department.id
									JOIN wm_sales_master on wm_dispatch.id = wm_sales_master.dispatch_id
									WHERE wm_dispatch.approval_status 	= 1
									AND wm_sales_master.product_id 		= ".$VALUE['id']."
									AND wm_dispatch.bill_from_mrf_id IN ($MRFID)
									AND wm_dispatch.dispatch_date BETWEEN '".$startDate."' AND '".$endDate."'";
					$DISPATCH_DATA = DB::select($DISPATCH);
					if(!empty($DISPATCH_DATA)) {
						foreach($DISPATCH_DATA as $RAW => $R_VAL) {
							$SGST_AMT 	= (!empty($R_VAL->TOTAL_GST_AMT)) ? _FormatNumberV2($R_VAL->TOTAL_GST_AMT / 2) :0;
							$CGST_AMT 	= (!empty($R_VAL->TOTAL_GST_AMT)) ? _FormatNumberV2($R_VAL->TOTAL_GST_AMT / 2): 0;
							$IGST_AMT 	= (!empty($R_VAL->TOTAL_GST_AMT)) ? _FormatNumberV2($R_VAL->TOTAL_GST_AMT) : 0;
							$QTY 	  	= (!empty($R_VAL->TOTAL_QTY)) ? _FormatNumberV2($R_VAL->TOTAL_QTY) : 0;
							$GROSS_AMT 	= (!empty($R_VAL->TOTAL_GROSS_AMT)) ? _FormatNumberV2($R_VAL->TOTAL_GROSS_AMT) :0;
							$GST_AMT 	= (!empty($R_VAL->TOTAL_GST_AMT)) ? _FormatNumberV2($R_VAL->TOTAL_GST_AMT) : 0;
							$NET_AMT 	= (!empty($R_VAL->TOTAL_NET_AMT)) ? _FormatNumberV2($R_VAL->TOTAL_NET_AMT) : 0;
						}
					}

					### TRANSFER DATA CALCULATION FOR SALES ITEM ########
					$TRANSFER 	= "	SELECT
								    SUM(wm_transfer_product.quantity) AS TOTAL_QTY,
									SUM(wm_transfer_product.gross_amount) AS TOTAL_GROSS_AMT,
									SUM(wm_transfer_product.gst_amount) AS TOTAL_GST_AMT,
									SUM(wm_transfer_product.net_amount) AS TOTAL_NET_AMT
									FROM wm_transfer_master
									JOIN wm_department on wm_transfer_master.origin_mrf = wm_department.id
									JOIN wm_transfer_product on wm_transfer_master.id = wm_transfer_product.transfer_id
									WHERE wm_transfer_master.approval_status = 3
									AND wm_transfer_master.origin_state_code != wm_transfer_master.destination_state_code
									AND wm_transfer_product.product_id 		= ".$VALUE['id']."
									AND wm_transfer_master.product_type 	= ".PRODUCT_SALES."
									AND wm_transfer_master.origin_mrf IN ($MRFID)
									AND wm_transfer_master.transfer_date BETWEEN '".$startDate."' AND '".$endDate."'";
					$TRANSFER_DATA = DB::select($TRANSFER);
					if(!empty($TRANSFER_DATA)){
						$TRAN_TMP 	= $TRANSFER_DATA[0];
						$SGST_AMT 	+= (!empty($TRAN_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_GST_AMT / 2) :0;
						$CGST_AMT 	+= (!empty($TRAN_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_GST_AMT / 2) :0;
						$IGST_AMT 	+= (!empty($TRAN_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_GST_AMT) : 0;
						$QTY 	  	+= (!empty($TRAN_TMP->TOTAL_QTY)) ? _FormatNumberV2($TRAN_TMP->TOTAL_QTY) : 0;
						$GROSS_AMT 	+= (!empty($TRAN_TMP->TOTAL_GROSS_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_GROSS_AMT) :0;
						$GST_AMT 	+= (!empty($TRAN_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_GST_AMT) : 0;
						$NET_AMT 	+= (!empty($TRAN_TMP->TOTAL_NET_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_NET_AMT) : 0;
					}
					$RESULT_ARR[$COUNTER]['title'] 				= $VALUE['title'];
					$RESULT_ARR[$COUNTER]['id'] 				= $VALUE['id'];
					$RESULT_ARR[$COUNTER]['net_suit_code'] 		= $VALUE['net_suit_code'];
					$RESULT_ARR[$COUNTER]['hsn_code'] 			= str_replace(" ","",$VALUE['hsn_code']);
					$RESULT_ARR[$COUNTER]['sgst_amt'] 			= _FormatNumberV2($SGST_AMT); 
					$RESULT_ARR[$COUNTER]['cgst_amt'] 			= _FormatNumberV2($CGST_AMT);
					$RESULT_ARR[$COUNTER]['igst_amt'] 			= _FormatNumberV2($IGST_AMT);
					$RESULT_ARR[$COUNTER]['gross_amt'] 			= _FormatNumberV2($GROSS_AMT); 
					$RESULT_ARR[$COUNTER]['gst_amt'] 			= _FormatNumberV2($GST_AMT);
					$RESULT_ARR[$COUNTER]['net_amt'] 			= _FormatNumberV2($NET_AMT);
					$RESULT_ARR[$COUNTER]['quantity'] 			= _FormatNumberV2($QTY);
					$RESULT_ARR[$COUNTER]['cn_sgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_cgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_igst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_qty'] 	  		= 0;
					$RESULT_ARR[$COUNTER]['cn_gross_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_gst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_net_amt']     	= 0;
					$RESULT_ARR[$COUNTER]['dn_sgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_cgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_igst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_qty'] 	  		= 0;
					$RESULT_ARR[$COUNTER]['dn_gross_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_gst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_net_amt']     	= 0;
					
					$DISPATCH_CN_DATA 	= "	SELECT SUM(wm_invoices_credit_debit_notes_details.revised_gst_amount) AS CN_GST_AMT,
											SUM(wm_invoices_credit_debit_notes_details.revised_quantity) AS TOTAL_QTY,
											SUM(wm_invoices_credit_debit_notes_details.revised_gross_amount) AS CN_GROSS_AMT,
											SUM(wm_invoices_credit_debit_notes_details.revised_net_amount) AS CN_NET_AMT,
											wm_invoices_credit_debit_notes.notes_type
											FROM wm_invoices_credit_debit_notes_details
											INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
											INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
											WHERE wm_invoices_credit_debit_notes_details.product_id = ".$VALUE['id']."
											AND wm_invoices_credit_debit_notes.bill_from_mrf_id IN ($MRFID)
											AND wm_invoices_credit_debit_notes.status IN (3)
											AND wm_invoices_credit_debit_notes.approved_date between '".$startDate."' AND '".$endDate."'
											group by wm_invoices_credit_debit_notes.notes_type";
					$DISPATCH_CN_DATA = \DB::select($DISPATCH_CN_DATA);
					if(!empty($DISPATCH_CN_DATA)){
						foreach($DISPATCH_CN_DATA AS $DIS_CN_KEY => $DIS_CN_VAL){
							if($DIS_CN_VAL->notes_type == 0){
									$RESULT_ARR[$COUNTER]['cn_sgst_amt'] 	= (!empty($DIS_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['cn_cgst_amt'] 	= (!empty($DIS_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['cn_igst_amt'] 	= (!empty($DIS_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['cn_qty'] 	  	= (!empty($DIS_CN_VAL->TOTAL_QTY)) 	? _FormatNumberV2($DIS_CN_VAL->TOTAL_QTY) : 0;
									$RESULT_ARR[$COUNTER]['cn_gross_amt'] 	= (!empty($DIS_CN_VAL->CN_GROSS_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_GROSS_AMT) :0;
									$RESULT_ARR[$COUNTER]['cn_gst_amt'] 	= (!empty($DIS_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['cn_net_amt'] 	= (!empty($DIS_CN_VAL->CN_NET_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_NET_AMT) : 0;
								}
								if($DIS_CN_VAL->notes_type == 1){
									$RESULT_ARR[$COUNTER]['dn_sgst_amt'] 	= (!empty($DIS_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['dn_cgst_amt'] 	= (!empty($DIS_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['dn_igst_amt'] 	= (!empty($DIS_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['dn_qty'] 	  	= (!empty($DIS_CN_VAL->TOTAL_QTY)) 	? _FormatNumberV2($DIS_CN_VAL->TOTAL_QTY) : 0;
									$RESULT_ARR[$COUNTER]['dn_gross_amt'] 	= (!empty($DIS_CN_VAL->CN_GROSS_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_GROSS_AMT) :0;
									$RESULT_ARR[$COUNTER]['dn_gst_amt'] 	= (!empty($DIS_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['dn_net_amt'] 	= (!empty($DIS_CN_VAL->CN_NET_AMT)) ? _FormatNumberV2($DIS_CN_VAL->CN_NET_AMT) : 0;
								}
						}
					}
					######### TOTAL GST & GROSS & NET ############
					$TOTAL_GROSS_AMT 	= (($RESULT_ARR[$COUNTER]['gross_amt'] + $RESULT_ARR[$COUNTER]['dn_gross_amt']) - $RESULT_ARR[$COUNTER]['cn_gross_amt']);
					$TOTAL_GST_AMT 		= (($RESULT_ARR[$COUNTER]['gst_amt'] + $RESULT_ARR[$COUNTER]['dn_gst_amt']) - $RESULT_ARR[$COUNTER]['cn_gst_amt']);
					$TOTAL_NET_AMT 		= ($TOTAL_GROSS_AMT + $TOTAL_GST_AMT); 
					$RESULT_ARR[$COUNTER]['total_gross_amount'] = (!empty($TOTAL_GROSS_AMT)) ? _FormatNumberV2($TOTAL_GROSS_AMT) : 0;
					$RESULT_ARR[$COUNTER]['total_gst_amount'] 	= (!empty($TOTAL_GST_AMT)) ? _FormatNumberV2($TOTAL_GST_AMT) : 0;
					$RESULT_ARR[$COUNTER]['total_net_amount'] 	= (!empty($TOTAL_NET_AMT)) ? _FormatNumberV2($TOTAL_NET_AMT) : 0;
					$COUNTER++;
				}
			}
			######## PURCHASE PRODUCT TRANSFER DATA ##############
			
			$PRODUCT_SELECT_RES = CompanyProductMaster::select("id","name as title","hsn_code","net_suit_code")->where("para_status_id",6001)->get()->toArray();
			
			if(!empty($PRODUCT_SELECT_RES)){
				foreach($PRODUCT_SELECT_RES as $RES => $R_VAL){
					$PUR_TRANSFER 	= "	SELECT
									    SUM(wm_transfer_product.quantity) AS TOTAL_QTY,
										SUM(wm_transfer_product.gross_amount) AS TOTAL_GROSS_AMT,
										SUM(wm_transfer_product.gst_amount) AS TOTAL_GST_AMT,
										SUM(wm_transfer_product.net_amount) AS TOTAL_NET_AMT
										FROM wm_transfer_master
										JOIN wm_department on wm_transfer_master.origin_mrf = wm_department.id
										JOIN wm_transfer_product on wm_transfer_master.id = wm_transfer_product.transfer_id
										WHERE wm_transfer_master.approval_status = 3
										AND wm_transfer_master.origin_state_code != wm_transfer_master.destination_state_code
										AND wm_transfer_product.product_id 		= ".$R_VAL['id']."
										AND wm_transfer_master.product_type 	= ".PRODUCT_PURCHASE."
										AND wm_transfer_master.origin_mrf IN ($MRFID)
										AND wm_transfer_master.transfer_date BETWEEN '".$startDate."' AND '".$endDate."'";
					$PUR_TRANSFER_DATA = DB::select($PUR_TRANSFER);
					if(!empty($PUR_TRANSFER_DATA)){
						$TRAN_TMP 	= $PUR_TRANSFER_DATA[0];
						$SGST_AMT 	= (!empty($TRAN_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_GST_AMT / 2) :0;
						$CGST_AMT 	= (!empty($TRAN_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_GST_AMT / 2) :0;
						$IGST_AMT 	= (!empty($TRAN_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_GST_AMT) : 0;
						$QTY 	  	= (!empty($TRAN_TMP->TOTAL_QTY)) 	 ? _FormatNumberV2($TRAN_TMP->TOTAL_QTY) : 0;
						$GROSS_AMT 	= (!empty($TRAN_TMP->TOTAL_GROSS_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_GROSS_AMT) :0;
						$GST_AMT 	= (!empty($TRAN_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_GST_AMT) : 0;
						$NET_AMT 	= (!empty($TRAN_TMP->TOTAL_NET_AMT)) ? _FormatNumberV2($TRAN_TMP->TOTAL_NET_AMT) : 0;
					}
					$RESULT_ARR[$COUNTER]['title'] 				= $R_VAL['title'];
					$RESULT_ARR[$COUNTER]['id'] 				= $R_VAL['id'];
					$RESULT_ARR[$COUNTER]['net_suit_code'] 		= $R_VAL['net_suit_code'];
					$RESULT_ARR[$COUNTER]['hsn_code'] 			= str_replace(" ","",$R_VAL['hsn_code']);
					$RESULT_ARR[$COUNTER]['sgst_amt'] 			= _FormatNumberV2($SGST_AMT); 
					$RESULT_ARR[$COUNTER]['cgst_amt'] 			= _FormatNumberV2($CGST_AMT);
					$RESULT_ARR[$COUNTER]['igst_amt'] 			= _FormatNumberV2($IGST_AMT);
					$RESULT_ARR[$COUNTER]['gross_amt'] 			= _FormatNumberV2($GROSS_AMT); 
					$RESULT_ARR[$COUNTER]['gst_amt'] 			= _FormatNumberV2($GST_AMT);
					$RESULT_ARR[$COUNTER]['net_amt'] 			= _FormatNumberV2($NET_AMT);
					$RESULT_ARR[$COUNTER]['quantity'] 			= _FormatNumberV2($QTY);
					$RESULT_ARR[$COUNTER]['cn_sgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_cgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_igst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_qty'] 	  		= 0;
					$RESULT_ARR[$COUNTER]['cn_gross_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_gst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_net_amt']     	= 0;
					$RESULT_ARR[$COUNTER]['dn_sgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_cgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_igst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_qty'] 	  		= 0;
					$RESULT_ARR[$COUNTER]['dn_gross_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_gst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_net_amt']     	= 0;
					######### TOTAL GST & GROSS & NET ############
					$TOTAL_GROSS_AMT = (($RESULT_ARR[$COUNTER]['gross_amt'] + $RESULT_ARR[$COUNTER]['dn_gross_amt']) - $RESULT_ARR[$COUNTER]['cn_gross_amt']);
					$TOTAL_GST_AMT 	= (($RESULT_ARR[$COUNTER]['gst_amt'] + $RESULT_ARR[$COUNTER]['dn_gst_amt']) - $RESULT_ARR[$COUNTER]['cn_gst_amt']);
					$TOTAL_NET_AMT 	= ($TOTAL_GROSS_AMT + $TOTAL_GST_AMT); 
					$RESULT_ARR[$COUNTER]['total_gross_amount'] = ($TOTAL_GROSS_AMT > 0) ? _FormatNumberV2($TOTAL_GROSS_AMT) : 0;
					$RESULT_ARR[$COUNTER]['total_gst_amount'] 	= ($TOTAL_GST_AMT > 0) ? _FormatNumberV2($TOTAL_GST_AMT) : 0;
					$RESULT_ARR[$COUNTER]['total_net_amount'] 	= ($TOTAL_NET_AMT > 0) ? _FormatNumberV2($TOTAL_NET_AMT) : 0;
					$COUNTER++;
				}
			}
			
			$SERVICE_PRO_SELECT_RES = WmServiceProductMaster::select("id","product as title","hsn_code","service_net_suit_code as net_suit_code")->where("status",1)->get()->toArray();
			
			if(!empty($SERVICE_PRO_SELECT_RES)){
				foreach($SERVICE_PRO_SELECT_RES as $KEY_V => $VA){
					$SERVICE_PRO = "SELECT  
						SUM(PRO.gst_amt)  AS TOTAL_GST_AMT,
						SUM(PRO.gross_amt)  AS TOTAL_GROSS_AMT,
						SUM(PRO.net_amt)  AS TOTAL_NET_AMT
					FROM wm_service_master 
					INNER JOIN wm_service_product_mapping as PRO on wm_service_master.id = PRO.service_id 
					INNER JOIN wm_service_product_master as SPM on PRO.product_id = SPM.id 
					LEFT JOIN wm_department on wm_service_master.mrf_id = wm_department.id 
					WHERE wm_service_master.invoice_date BETWEEN '".date("Y-m-d",strtotime($startDate))."' AND '".date("Y-m-d",strtotime($endDate))."' AND wm_service_master.approval_status = 1
					AND wm_service_master.mrf_id IN ($MRFID) AND PRO.product_id=".$VA['id'];
					$SERVICE_RES = \DB::select($SERVICE_PRO);
					if(!empty($SERVICE_RES)){
						$SERV_TMP 	= $SERVICE_RES[0];
						$SGST_AMT 	= (!empty($SERV_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($SERV_TMP->TOTAL_GST_AMT / 2) :0;
						$CGST_AMT 	= (!empty($SERV_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($SERV_TMP->TOTAL_GST_AMT / 2) :0;
						$IGST_AMT 	= (!empty($SERV_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($SERV_TMP->TOTAL_GST_AMT) : 0;
						$QTY 	  	= (!empty($SERV_TMP->TOTAL_QTY)) 	? _FormatNumberV2($SERV_TMP->TOTAL_QTY) : 0;
						$GROSS_AMT 	= (!empty($SERV_TMP->TOTAL_GROSS_AMT)) ? _FormatNumberV2($SERV_TMP->TOTAL_GROSS_AMT) :0;
						$GST_AMT 	= (!empty($SERV_TMP->TOTAL_GST_AMT)) ? _FormatNumberV2($SERV_TMP->TOTAL_GST_AMT) : 0;
						$NET_AMT 	= (!empty($SERV_TMP->TOTAL_NET_AMT)) ? _FormatNumberV2($SERV_TMP->TOTAL_NET_AMT) : 0;
						$RESULT_ARR[$COUNTER]['title'] 				= $VA['title'];
						$RESULT_ARR[$COUNTER]['id'] 				= $VA['id'];
						$RESULT_ARR[$COUNTER]['net_suit_code'] 		= $VA['net_suit_code'];
						$RESULT_ARR[$COUNTER]['hsn_code'] 			= str_replace(" ","",$VA['hsn_code']);
						$RESULT_ARR[$COUNTER]['sgst_amt'] 			= _FormatNumberV2($SGST_AMT); 
						$RESULT_ARR[$COUNTER]['cgst_amt'] 			= _FormatNumberV2($CGST_AMT);
						$RESULT_ARR[$COUNTER]['igst_amt'] 			= _FormatNumberV2($IGST_AMT);
						$RESULT_ARR[$COUNTER]['gross_amt'] 			= _FormatNumberV2($GROSS_AMT); 
						$RESULT_ARR[$COUNTER]['gst_amt'] 			= _FormatNumberV2($GST_AMT);
						$RESULT_ARR[$COUNTER]['net_amt'] 			= _FormatNumberV2($NET_AMT);
						$RESULT_ARR[$COUNTER]['quantity'] 			= _FormatNumberV2($QTY);
						$RESULT_ARR[$COUNTER]['cn_sgst_amt'] 		= 0;
						$RESULT_ARR[$COUNTER]['cn_cgst_amt'] 		= 0;
						$RESULT_ARR[$COUNTER]['cn_igst_amt'] 		= 0;
						$RESULT_ARR[$COUNTER]['cn_qty'] 	  		= 0;
						$RESULT_ARR[$COUNTER]['cn_gross_amt'] 		= 0;
						$RESULT_ARR[$COUNTER]['cn_gst_amt'] 		= 0;
						$RESULT_ARR[$COUNTER]['cn_net_amt']     	= 0;
						$RESULT_ARR[$COUNTER]['dn_sgst_amt'] 		= 0;
						$RESULT_ARR[$COUNTER]['dn_cgst_amt'] 		= 0;
						$RESULT_ARR[$COUNTER]['dn_igst_amt'] 		= 0;
						$RESULT_ARR[$COUNTER]['dn_qty'] 	  		= 0;
						$RESULT_ARR[$COUNTER]['dn_gross_amt'] 		= 0;
						$RESULT_ARR[$COUNTER]['dn_gst_amt'] 		= 0;
						$RESULT_ARR[$COUNTER]['dn_net_amt']     	= 0;
						$SERVICE_CN_DN = "SELECT 
							SUM(revised_gross_amount) as CN_GROSS_AMT,
							SUM(revised_gst_amount) as CN_GST_AMT,
							SUM(revised_net_amount) as CN_NET_AMT,
							SUM(revised_quantity) as CN_QTY,
							WDM.notes_type
						FROM wm_service_invoices_credit_debit_notes  AS WDM
						INNER JOIN wm_service_invoices_credit_debit_notes_details   AS WCNDN ON WDM.id = WCNDN.cd_notes_id
						WHERE WDM.status = 1
						AND WCNDN.product_id = ".$VA['id']."
						AND WDM.mrf_id	IN ($MRFID) 
						AND WDM.change_date BETWEEN '".date("Y-m-d",strtotime($startDate))."' AND '".date("Y-m-d",strtotime($endDate))."'
						GROUP BY WDM.notes_type";
						$SER_CNDN_DATA = \DB::select($SERVICE_CN_DN);
						if(!empty($SER_CNDN_DATA)){
							foreach($SER_CNDN_DATA AS $CN_KEY => $DN_KEY){
								if($DN_KEY->notes_type == 0){
									$RESULT_ARR[$COUNTER]['cn_sgst_amt'] 	= (!empty($DN_KEY->CN_GST_AMT)) ? _FormatNumberV2($DN_KEY->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['cn_cgst_amt'] 	= (!empty($DN_KEY->CN_GST_AMT)) ? _FormatNumberV2($DN_KEY->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['cn_igst_amt'] 	= (!empty($DN_KEY->CN_GST_AMT)) ? _FormatNumberV2($DN_KEY->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['cn_qty'] 	  	= (!empty($DN_KEY->TOTAL_QTY)) 	? _FormatNumberV2($DN_KEY->TOTAL_QTY) : 0;
									$RESULT_ARR[$COUNTER]['cn_gross_amt'] 	= (!empty($DN_KEY->CN_GROSS_AMT)) ? _FormatNumberV2($DN_KEY->CN_GROSS_AMT) :0;
									$RESULT_ARR[$COUNTER]['cn_gst_amt'] 	= (!empty($DN_KEY->CN_GST_AMT)) ? _FormatNumberV2($DN_KEY->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['cn_net_amt'] 	= (!empty($DN_KEY->CN_NET_AMT)) ? _FormatNumberV2($DN_KEY->CN_NET_AMT) : 0;
								}
								if($DN_KEY->notes_type == 1){
									$RESULT_ARR[$COUNTER]['dn_sgst_amt'] 	= (!empty($DN_KEY->CN_GST_AMT)) ? _FormatNumberV2($DN_KEY->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['dn_cgst_amt'] 	= (!empty($DN_KEY->CN_GST_AMT)) ? _FormatNumberV2($DN_KEY->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['dn_igst_amt'] 	= (!empty($DN_KEY->CN_GST_AMT)) ? _FormatNumberV2($DN_KEY->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['dn_qty'] 	  	= (!empty($DN_KEY->TOTAL_QTY)) 	? _FormatNumberV2($DN_KEY->TOTAL_QTY) : 0;
									$RESULT_ARR[$COUNTER]['dn_gross_amt'] 	= (!empty($DN_KEY->CN_GROSS_AMT)) ? _FormatNumberV2($DN_KEY->CN_GROSS_AMT) :0;
									$RESULT_ARR[$COUNTER]['dn_gst_amt'] 	= (!empty($DN_KEY->CN_GST_AMT)) ? _FormatNumberV2($DN_KEY->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['dn_net_amt'] 	= (!empty($DN_KEY->CN_NET_AMT)) ? _FormatNumberV2($DN_KEY->CN_NET_AMT) : 0;
								}
							}
						}
						######### TOTAL GST & GROSS & NET ############
						$TOTAL_GROSS_AMT 	= (($RESULT_ARR[$COUNTER]['gross_amt'] + $RESULT_ARR[$COUNTER]['dn_gross_amt']) - $RESULT_ARR[$COUNTER]['cn_gross_amt']);
						$TOTAL_GST_AMT 		= (($RESULT_ARR[$COUNTER]['gst_amt'] + $RESULT_ARR[$COUNTER]['dn_gst_amt']) - $RESULT_ARR[$COUNTER]['cn_gst_amt']);
						$TOTAL_NET_AMT 		= ($TOTAL_GROSS_AMT + $TOTAL_GST_AMT); 
						$RESULT_ARR[$COUNTER]['total_gross_amount'] = (!empty($TOTAL_GROSS_AMT)) ? _FormatNumberV2($TOTAL_GROSS_AMT) : 0;
						$RESULT_ARR[$COUNTER]['total_gst_amount'] 	= (!empty($TOTAL_GST_AMT)) ? _FormatNumberV2($TOTAL_GST_AMT) : 0;
						$RESULT_ARR[$COUNTER]['total_net_amount'] 	= (!empty($TOTAL_NET_AMT)) ? _FormatNumberV2($TOTAL_NET_AMT) : 0;
						$COUNTER++;
					}
				}
			}
			######### SERVICE PRODUCT DATA ###############
			
			$ASSET_PRO_SELECT_RES 	= "	SELECT
									    wm_asset_product_mapping.id,
									    CONCAT(wm_asset_product_mapping.product,' ',wm_asset_product_mapping.description) as title,
										wm_asset_product_mapping.hsn_code,
										'' AS net_suit_code,
										SUM(wm_asset_product_mapping.quantity) AS TOTAL_QTY,
										SUM(wm_asset_product_mapping.gross_amt) AS TOTAL_GROSS_AMT,
										SUM(wm_asset_product_mapping.gst_amt) AS TOTAL_GST_AMT,
										SUM(wm_asset_product_mapping.net_amt) AS TOTAL_NET_AMT
										FROM wm_asset_product_mapping
										JOIN wm_asset_master on wm_asset_product_mapping.asset_id = wm_asset_master.id
										JOIN wm_department  AS FWD on wm_asset_master.from_mrf_id = FWD.id
										JOIN wm_department AS TWD on wm_asset_master.to_mrf_id = TWD.id
										WHERE wm_asset_master.approval_status = 1
										AND TWD.gst_state_code_id != FWD.gst_state_code_id
									    AND wm_asset_master.from_mrf_id	IN ($MRFID)
										AND wm_asset_master.invoice_date BETWEEN '".date("Y-m-d",strtotime($startDate))."' AND '".date("Y-m-d",strtotime($endDate))."'
										GROUP BY title";
			$ASSET_PRO_RES 			= \DB::select($ASSET_PRO_SELECT_RES);
			if(!empty($ASSET_PRO_RES)){
				foreach($ASSET_PRO_RES as $ASSET_KEY => $ASSET_VAL){
					$assetArray = array();
					$SGST_AMT 	= (!empty($ASSET_VAL->TOTAL_GST_AMT)) 	? _FormatNumberV2($ASSET_VAL->TOTAL_GST_AMT / 2) :0;
					$CGST_AMT 	= (!empty($ASSET_VAL->TOTAL_GST_AMT)) 	? _FormatNumberV2($ASSET_VAL->TOTAL_GST_AMT / 2): 0;
					$IGST_AMT 	= (!empty($ASSET_VAL->TOTAL_GST_AMT)) 	? _FormatNumberV2($ASSET_VAL->TOTAL_GST_AMT) : 0;
					$QTY 	  	= (!empty($ASSET_VAL->TOTAL_QTY)) 		? _FormatNumberV2($ASSET_VAL->TOTAL_QTY) : 0;
					$GROSS_AMT 	= (!empty($ASSET_VAL->TOTAL_GROSS_AMT)) ? _FormatNumberV2($ASSET_VAL->TOTAL_GROSS_AMT) :0;
					$GST_AMT 	= (!empty($ASSET_VAL->TOTAL_GST_AMT)) 	? _FormatNumberV2($ASSET_VAL->TOTAL_GST_AMT) : 0;
					$NET_AMT 	= (!empty($ASSET_VAL->TOTAL_NET_AMT)) 	? _FormatNumberV2($ASSET_VAL->TOTAL_NET_AMT) : 0;
					$RESULT_ARR[$COUNTER]['title'] 				= $ASSET_VAL->title;
					$RESULT_ARR[$COUNTER]['id'] 				= $ASSET_VAL->id;
					$RESULT_ARR[$COUNTER]['net_suit_code'] 		= $ASSET_VAL->net_suit_code;
					$RESULT_ARR[$COUNTER]['hsn_code'] 			= str_replace(" ","",$ASSET_VAL->hsn_code);
					$RESULT_ARR[$COUNTER]['sgst_amt'] 			= _FormatNumberV2($SGST_AMT); 
					$RESULT_ARR[$COUNTER]['cgst_amt'] 			= _FormatNumberV2($CGST_AMT);
					$RESULT_ARR[$COUNTER]['igst_amt'] 			= _FormatNumberV2($IGST_AMT);
					$RESULT_ARR[$COUNTER]['gross_amt'] 			= _FormatNumberV2($GROSS_AMT); 
					$RESULT_ARR[$COUNTER]['gst_amt'] 			= _FormatNumberV2($GST_AMT);
					$RESULT_ARR[$COUNTER]['net_amt'] 			= _FormatNumberV2($NET_AMT);
					$RESULT_ARR[$COUNTER]['quantity'] 			= _FormatNumberV2($QTY);
					$RESULT_ARR[$COUNTER]['cn_sgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_cgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_igst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_qty'] 	  		= 0;
					$RESULT_ARR[$COUNTER]['cn_gross_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_gst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_net_amt']     	= 0;
					$RESULT_ARR[$COUNTER]['dn_sgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_cgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_igst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_qty'] 	  		= 0;
					$RESULT_ARR[$COUNTER]['dn_gross_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_gst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_net_amt']     	= 0;
						

						######### TOTAL GST & GROSS & NET ############
					
						$TOTAL_GROSS_AMT 	= (($RESULT_ARR[$COUNTER]['gross_amt'] + $RESULT_ARR[$COUNTER]['dn_gross_amt']) - $RESULT_ARR[$COUNTER]['cn_gross_amt']);
						$TOTAL_GST_AMT 		= (($RESULT_ARR[$COUNTER]['gst_amt'] + $RESULT_ARR[$COUNTER]['dn_gst_amt']) - $RESULT_ARR[$COUNTER]['cn_gst_amt']);
						$TOTAL_NET_AMT 		= ($TOTAL_GROSS_AMT + $TOTAL_GST_AMT); 
						$RESULT_ARR[$COUNTER]['total_gross_amount'] = (!empty($TOTAL_GROSS_AMT)) ? _FormatNumberV2($TOTAL_GROSS_AMT) : 0;
						$RESULT_ARR[$COUNTER]['total_gst_amount'] 	= (!empty($TOTAL_GST_AMT)) ? _FormatNumberV2($TOTAL_GST_AMT) : 0;
						$RESULT_ARR[$COUNTER]['total_net_amount'] 	= (!empty($TOTAL_NET_AMT)) ? _FormatNumberV2($TOTAL_NET_AMT) : 0;
					$COUNTER++;
				}
			}
			###### CHARGES DETAILS ########
			
			$CHARGE_SQL 	= "SELECT id,charge_name as title,hsn_code,charge_ns_code as net_suit_code FROM client_charges_master WHERE status=1";
			$CHARGE_DATA 	= \DB::select($CHARGE_SQL);
			if(!empty($CHARGE_DATA)){
				foreach($CHARGE_DATA AS $CH_KEY => $CH_VALUE){
					
					$INVOICE_CHARGE = InvoiceAdditionalCharges::select(
						\DB::raw("SUM(sgst_amount) as TOTAL_SGST_AMT"),
						\DB::raw("SUM(cgst_amount) as TOTAL_CGST_AMT"),
						\DB::raw("SUM(igst_amount) as TOTAL_IGST_AMT"),
						\DB::raw("SUM(gst_amount) as TOTAL_GST_AMT"),
						\DB::raw("SUM(gross_amount) as TOTAL_GROSS_AMT"),
						\DB::raw("SUM(net_amount) as TOTAL_NET_AMT"))
					->join("wm_dispatch","wm_invoice_additional_charges.dispatch_id","=","wm_dispatch.id")
					->where("client_charges_id",$CH_VALUE->id)
					->whereIn("wm_dispatch.bill_from_mrf_id",$MRF_ID_ARR)
					->whereBetween("wm_dispatch.dispatch_date",array($startDate,$endDate))
					->where("wm_dispatch.approval_status",1)->first()->toArray();
					if($INVOICE_CHARGE){
						$SGST_AMT 	= (!empty($TRAN_TMP->TOTAL_SGST_AMT)) 	? _FormatNumberV2($TRAN_TMP->TOTAL_SGST_AMT) :0;
						$CGST_AMT 	= (!empty($TRAN_TMP->TOTAL_CGST_AMT)) 	? _FormatNumberV2($TRAN_TMP->TOTAL_CGST_AMT) :0;
						$IGST_AMT 	= (!empty($TRAN_TMP->TOTAL_IGST_AMT)) 	? _FormatNumberV2($TRAN_TMP->TOTAL_IGST_AMT) : 0;
						$QTY 	  	= (!empty($TRAN_TMP->TOTAL_QTY)) 		? _FormatNumberV2($TRAN_TMP->TOTAL_QTY) 		: 0;
						$GROSS_AMT 	= (!empty($TRAN_TMP->TOTAL_GROSS_AMT)) 	? _FormatNumberV2($TRAN_TMP->TOTAL_GROSS_AMT) :0;
						$GST_AMT 	= (!empty($TRAN_TMP->TOTAL_GST_AMT)) 	? _FormatNumberV2($TRAN_TMP->TOTAL_GST_AMT) : 0;
						$NET_AMT 	= (!empty($TRAN_TMP->TOTAL_NET_AMT)) 	? _FormatNumberV2($TRAN_TMP->TOTAL_NET_AMT) : 0;
					}
					$INVOICE_CHARGE = InvoiceAdditionalCharges::select(
						\DB::raw("SUM(sgst_amount) as TOTAL_SGST_AMT"),
						\DB::raw("SUM(cgst_amount) as TOTAL_CGST_AMT"),
						\DB::raw("SUM(igst_amount) as TOTAL_IGST_AMT"),
						\DB::raw("SUM(gst_amount) as TOTAL_GST_AMT"),
						\DB::raw("SUM(gross_amount) as TOTAL_GROSS_AMT"),
						\DB::raw("SUM(net_amount) as TOTAL_NET_AMT"))
					->join("wm_dispatch","wm_invoice_additional_charges.dispatch_id","=","wm_dispatch.id")
					->where("client_charges_id",$CH_VALUE->id)
					->whereIn("wm_dispatch.bill_from_mrf_id",$MRF_ID_ARR)
					->whereBetween("wm_dispatch.dispatch_date",array($startDate,$endDate))
					->where("wm_dispatch.approval_status",1)->first();
					
					if($INVOICE_CHARGE){
						$SGST_AMT 	= (!empty($INVOICE_CHARGE->TOTAL_SGST_AMT)) ? $INVOICE_CHARGE->TOTAL_SGST_AMT :0;
						$CGST_AMT 	= (!empty($INVOICE_CHARGE->TOTAL_CGST_AMT)) ? $INVOICE_CHARGE->TOTAL_CGST_AMT :0;
						$IGST_AMT 	= (!empty($INVOICE_CHARGE->TOTAL_IGST_AMT)) ? $INVOICE_CHARGE->TOTAL_IGST_AMT : 0;
						$QTY 	  	= (!empty($INVOICE_CHARGE->TOTAL_QTY)) 	? $INVOICE_CHARGE->TOTAL_QTY : 0;
						$GROSS_AMT = (!empty($INVOICE_CHARGE->TOTAL_GROSS_AMT)) ? $INVOICE_CHARGE->TOTAL_GROSS_AMT :0;
						$GST_AMT 	= (!empty($INVOICE_CHARGE->TOTAL_GST_AMT)) 	? $INVOICE_CHARGE->TOTAL_GST_AMT : 0;
						$NET_AMT 	= (!empty($INVOICE_CHARGE->TOTAL_NET_AMT)) 	? $INVOICE_CHARGE->TOTAL_NET_AMT : 0;
					}
					$RESULT_ARR[$COUNTER]['title'] 				= $CH_VALUE->title;
					$RESULT_ARR[$COUNTER]['id'] 				= $CH_VALUE->id;
					$RESULT_ARR[$COUNTER]['net_suit_code'] 		= $CH_VALUE->net_suit_code;
					$RESULT_ARR[$COUNTER]['hsn_code'] 			= str_replace(" ","",$CH_VALUE->hsn_code);
					$RESULT_ARR[$COUNTER]['sgst_amt'] 			= _FormatNumberV2($SGST_AMT); 
					$RESULT_ARR[$COUNTER]['cgst_amt'] 			= _FormatNumberV2($CGST_AMT);
					$RESULT_ARR[$COUNTER]['igst_amt'] 			= _FormatNumberV2($IGST_AMT);
					$RESULT_ARR[$COUNTER]['gross_amt'] 			= _FormatNumberV2($GROSS_AMT); 
					$RESULT_ARR[$COUNTER]['gst_amt'] 			= _FormatNumberV2($GST_AMT);
					$RESULT_ARR[$COUNTER]['net_amt'] 			= _FormatNumberV2($NET_AMT);
					$RESULT_ARR[$COUNTER]['quantity'] 			= _FormatNumberV2($QTY);
					$RESULT_ARR[$COUNTER]['cn_sgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_cgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_igst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_qty'] 	  		= 0;
					$RESULT_ARR[$COUNTER]['cn_gross_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_gst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['cn_net_amt']     	= 0;
					$RESULT_ARR[$COUNTER]['dn_sgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_cgst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_igst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_qty'] 	  		= 0;
					$RESULT_ARR[$COUNTER]['dn_gross_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_gst_amt'] 		= 0;
					$RESULT_ARR[$COUNTER]['dn_net_amt']     	= 0;
					$CHARGE_CNDN_DATA = WmInvoicesCreditDebitNotes::select(
						\DB::raw("SUM(revised_gst_amount) as CN_GST_AMT"),
						\DB::raw("SUM(revised_quantity) as TOTAL_QTY"),
						\DB::raw("SUM(revised_gross_amount) as CN_GROSS_AMT"),
						\DB::raw("SUM(revised_net_amount) as CN_NET_AMT"),"wm_invoices_credit_debit_notes.notes_type")
					->join("wm_invoices_credit_debit_notes_charges_details","wm_invoices_credit_debit_notes.id","=","wm_invoices_credit_debit_notes_charges_details.cd_notes_id")
					->whereIn("approved_date",array($startDate,$endDate))
					->where("status",3)
					->where("wm_invoices_credit_debit_notes_charges_details.charge_id",$CH_VALUE->id)
					->whereIn("wm_invoices_credit_debit_notes.bill_from_mrf_id",$MRF_ID_ARR)
					->groupby("wm_invoices_credit_debit_notes.notes_type")
					->get();

					if(!empty($CHARGE_CNDN_DATA)){
						foreach($CHARGE_CNDN_DATA AS $CH_CN_KEY => $CH_CN_VAL){
							if($DN_KEY->notes_type == 0){
									$RESULT_ARR[$COUNTER]['cn_sgst_amt'] 	= (!empty($CH_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['cn_cgst_amt'] 	= (!empty($CH_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['cn_igst_amt'] 	= (!empty($CH_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['cn_qty'] 	  	= (!empty($CH_CN_VAL->TOTAL_QTY)) 	? _FormatNumberV2($CH_CN_VAL->TOTAL_QTY) : 0;
									$RESULT_ARR[$COUNTER]['cn_gross_amt'] 	= (!empty($CH_CN_VAL->CN_GROSS_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_GROSS_AMT) :0;
									$RESULT_ARR[$COUNTER]['cn_gst_amt'] 	= (!empty($CH_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['cn_net_amt'] 	= (!empty($CH_CN_VAL->CN_NET_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_NET_AMT) : 0;
								}
								if($DN_KEY->notes_type == 1){
									$RESULT_ARR[$COUNTER]['dn_sgst_amt'] 	= (!empty($CH_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['dn_cgst_amt'] 	= (!empty($CH_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_GST_AMT / 2) :0;
									$RESULT_ARR[$COUNTER]['dn_igst_amt'] 	= (!empty($CH_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['dn_qty'] 	  	= (!empty($CH_CN_VAL->TOTAL_QTY)) 	? _FormatNumberV2($CH_CN_VAL->TOTAL_QTY) : 0;
									$RESULT_ARR[$COUNTER]['dn_gross_amt'] 	= (!empty($CH_CN_VAL->CN_GROSS_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_GROSS_AMT) :0;
									$RESULT_ARR[$COUNTER]['dn_gst_amt'] 	= (!empty($CH_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_GST_AMT) : 0;
									$RESULT_ARR[$COUNTER]['dn_net_amt'] 	= (!empty($CH_CN_VAL->CN_NET_AMT)) ? _FormatNumberV2($CH_CN_VAL->CN_NET_AMT) : 0;
								}
						}
					}

					######### TOTAL GST & GROSS & NET ############
					
					$TOTAL_GROSS_AMT 	= (($RESULT_ARR[$COUNTER]['gross_amt'] + $RESULT_ARR[$COUNTER]['dn_gross_amt']) - $RESULT_ARR[$COUNTER]['cn_gross_amt']);
					$TOTAL_GST_AMT 		= (($RESULT_ARR[$COUNTER]['gst_amt'] + $RESULT_ARR[$COUNTER]['dn_gst_amt']) - $RESULT_ARR[$COUNTER]['cn_gst_amt']);
					$TOTAL_NET_AMT 		= ($TOTAL_GROSS_AMT + $TOTAL_GST_AMT); 
					$RESULT_ARR[$COUNTER]['total_gross_amount'] = (!empty($TOTAL_GROSS_AMT)) ? _FormatNumberV2($TOTAL_GROSS_AMT) : 0;
					$RESULT_ARR[$COUNTER]['total_gst_amount'] 	= (!empty($TOTAL_GST_AMT)) ? _FormatNumberV2($TOTAL_GST_AMT) : 0;
					$RESULT_ARR[$COUNTER]['total_net_amount'] 	= (!empty($TOTAL_NET_AMT)) ? _FormatNumberV2($TOTAL_NET_AMT) : 0;
					$COUNTER++;
				}
			}
			########### FRIEGHT AMOUNT ############
			
			$FRIGHT_DATA ="SELECT 
				    wm_department.department_name,
				    '0'  AS TOTAL_QTY,
					(SUM(wm_dispatch.rent_amt)) AS TOTAL_GROSS_AMT,
				   	(SUM(wm_dispatch.rent_gst_amt)) AS TOTAL_GST_AMT,
					(SUM(wm_dispatch.total_rent_amt)) AS TOTAL_NET_AMT
				FROM wm_dispatch
				JOIN wm_department on wm_dispatch.bill_from_mrf_id = wm_department.id
				WHERE wm_dispatch.approval_status = 1
				AND wm_dispatch.bill_from_mrf_id IN ($MRFID)
				AND wm_dispatch.dispatch_date BETWEEN '".$startDate."' AND '".$endDate."'";
				$FRIGHT_DATA = DB::select($FRIGHT_DATA);
				if(!empty($FRIGHT_DATA)){
					foreach($FRIGHT_DATA as $FK => $FV){
						$SGST_AMT 	= (!empty($FV->TOTAL_GST_AMT)) ? _FormatNumberV2($FV->TOTAL_GST_AMT / 2) :0;
						$CGST_AMT 	= (!empty($FV->TOTAL_GST_AMT)) ? _FormatNumberV2($FV->TOTAL_GST_AMT / 2): 0;
						$IGST_AMT 	= (!empty($FV->TOTAL_GST_AMT)) ? _FormatNumberV2($FV->TOTAL_GST_AMT) : 0;
						$QTY 	  	= 0;
						$GROSS_AMT 	= (!empty($FV->TOTAL_GROSS_AMT)) ? _FormatNumberV2($FV->TOTAL_GROSS_AMT) :0;
						$GST_AMT 	= (!empty($FV->TOTAL_GST_AMT)) ? _FormatNumberV2($FV->TOTAL_GST_AMT) : 0;
						$NET_AMT 	= (!empty($FV->TOTAL_NET_AMT)) ? _FormatNumberV2($FV->TOTAL_NET_AMT) : 0;
					}
				}
				$RESULT_ARR[$COUNTER]['title'] 			= "Fright Charge";
				$RESULT_ARR[$COUNTER]['id'] 			= "";
				$RESULT_ARR[$COUNTER]['net_suit_code'] 	= "";
				$RESULT_ARR[$COUNTER]['hsn_code'] 		= "";
				$RESULT_ARR[$COUNTER]['sgst_amt'] 		= _FormatNumberV2($SGST_AMT); 
				$RESULT_ARR[$COUNTER]['cgst_amt'] 		= _FormatNumberV2($CGST_AMT);
				$RESULT_ARR[$COUNTER]['igst_amt'] 		= _FormatNumberV2($IGST_AMT);
				$RESULT_ARR[$COUNTER]['gross_amt'] 		= _FormatNumberV2($GROSS_AMT); 
				$RESULT_ARR[$COUNTER]['gst_amt'] 		= _FormatNumberV2($GST_AMT);
				$RESULT_ARR[$COUNTER]['net_amt'] 		= _FormatNumberV2($NET_AMT);
				$RESULT_ARR[$COUNTER]['quantity'] 		= _FormatNumberV2($QTY);
				$RESULT_ARR[$COUNTER]['cn_sgst_amt'] 	= 0;
				$RESULT_ARR[$COUNTER]['cn_cgst_amt'] 	= 0;
				$RESULT_ARR[$COUNTER]['cn_igst_amt'] 	= 0;
				$RESULT_ARR[$COUNTER]['cn_qty'] 	  	= 0;
				$RESULT_ARR[$COUNTER]['cn_gross_amt'] 	= 0;
				$RESULT_ARR[$COUNTER]['cn_gst_amt'] 	= 0;
				$RESULT_ARR[$COUNTER]['cn_net_amt']     = 0;
				$RESULT_ARR[$COUNTER]['dn_sgst_amt'] 	= 0;
				$RESULT_ARR[$COUNTER]['dn_cgst_amt'] 	= 0;
				$RESULT_ARR[$COUNTER]['dn_igst_amt'] 	= 0;
				$RESULT_ARR[$COUNTER]['dn_qty'] 	  	= 0;
				$RESULT_ARR[$COUNTER]['dn_gross_amt'] 	= 0;
				$RESULT_ARR[$COUNTER]['dn_gst_amt'] 	= 0;
				$RESULT_ARR[$COUNTER]['dn_net_amt']     = 0;
				$FRIGHT_CN_DATA = "SELECT 
					SUM(wm_invoices_credit_debit_notes_frieght_details.gross_amount) AS CN_GST_AMT,
					SUM(wm_invoices_credit_debit_notes_frieght_details.gst_amount) AS CN_GROSS_AMT,
					SUM(wm_invoices_credit_debit_notes_frieght_details.net_amount) AS CN_NET_AMT,
					wm_invoices_credit_debit_notes.notes_type
					FROM wm_invoices_credit_debit_notes_frieght_details
					INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_frieght_details.cd_notes_id
					WHERE  wm_invoices_credit_debit_notes.bill_from_mrf_id IN ($MRFID)
					AND wm_invoices_credit_debit_notes.status IN (3)
					AND wm_invoices_credit_debit_notes.approved_date between '".$startDate."' AND '".$endDate."'
					group by wm_invoices_credit_debit_notes.notes_type";
				$FRIGHT_CN_DATA = \DB::select($FRIGHT_CN_DATA);
				if(!empty($FRIGHT_CN_DATA)){
					foreach($FRIGHT_CN_DATA AS $FRI_CN_KEY => $FRI_CN_VAL){
						if($FRI_CN_VAL->notes_type == 0){
								$RESULT_ARR[$COUNTER]['cn_sgst_amt'] 	= (!empty($FRI_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($FRI_CN_VAL->CN_GST_AMT / 2) :0;
								$RESULT_ARR[$COUNTER]['cn_cgst_amt'] 	= (!empty($FRI_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($FRI_CN_VAL->CN_GST_AMT / 2) :0;
								$RESULT_ARR[$COUNTER]['cn_igst_amt'] 	= (!empty($FRI_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($FRI_CN_VAL->CN_GST_AMT) : 0;
								$RESULT_ARR[$COUNTER]['cn_gross_amt'] 	= (!empty($FRI_CN_VAL->CN_GROSS_AMT)) ? _FormatNumberV2($FRI_CN_VAL->CN_GROSS_AMT) :0;
								$RESULT_ARR[$COUNTER]['cn_gst_amt'] 	= (!empty($FRI_CN_VAL->CN_GST_AMT)) ? _FormatNumberV2($FRI_CN_VAL->CN_GST_AMT) : 0;
								$RESULT_ARR[$COUNTER]['cn_net_amt'] 	= (!empty($FRI_CN_VAL->CN_NET_AMT)) ? _FormatNumberV2($FRI_CN_VAL->CN_NET_AMT) : 0;
						}
						if($FRI_CN_VAL->notes_type == 1){
							$RESULT_ARR[$COUNTER]['dn_sgst_amt'] 	= (!empty($FRI_CN_VAL->CN_GST_AMT)) ? $FRI_CN_VAL->CN_GST_AMT / 2 :0;
							$RESULT_ARR[$COUNTER]['dn_cgst_amt'] 	= (!empty($FRI_CN_VAL->CN_GST_AMT)) ? $FRI_CN_VAL->CN_GST_AMT / 2 :0;
							$RESULT_ARR[$COUNTER]['dn_igst_amt'] 	= (!empty($FRI_CN_VAL->CN_GST_AMT)) ? $FRI_CN_VAL->CN_GST_AMT : 0;
							$RESULT_ARR[$COUNTER]['dn_gross_amt'] 	= (!empty($FRI_CN_VAL->CN_GROSS_AMT)) ? $FRI_CN_VAL->CN_GROSS_AMT :0;
							$RESULT_ARR[$COUNTER]['dn_gst_amt'] 	= (!empty($FRI_CN_VAL->CN_GST_AMT)) ? $FRI_CN_VAL->CN_GST_AMT : 0;
							$RESULT_ARR[$COUNTER]['dn_net_amt'] 	= (!empty($FRI_CN_VAL->CN_NET_AMT)) ? $FRI_CN_VAL->CN_NET_AMT : 0;
						}
					}
				}
				######### TOTAL GST & GROSS & NET ############
				$TOTAL_GROSS_AMT 	= (($RESULT_ARR[$COUNTER]['gross_amt'] + $RESULT_ARR[$COUNTER]['dn_gross_amt']) - $RESULT_ARR[$COUNTER]['cn_gross_amt']);
				$TOTAL_GST_AMT 		= (($RESULT_ARR[$COUNTER]['gst_amt'] + $RESULT_ARR[$COUNTER]['dn_gst_amt']) - $RESULT_ARR[$COUNTER]['cn_gst_amt']);
				$TOTAL_NET_AMT 		= ($TOTAL_GROSS_AMT + $TOTAL_GST_AMT); 
				$RESULT_ARR[$COUNTER]['total_gross_amount'] = (!empty($TOTAL_GROSS_AMT)) ? _FormatNumberV2($TOTAL_GROSS_AMT) : 0;
				$RESULT_ARR[$COUNTER]['total_gst_amount'] 	= (!empty($TOTAL_GST_AMT)) ? _FormatNumberV2($TOTAL_GST_AMT) : 0;
				$RESULT_ARR[$COUNTER]['total_net_amount'] 	= (!empty($TOTAL_NET_AMT)) ? _FormatNumberV2($TOTAL_NET_AMT) : 0;
				############ FRIGHT AMOUNT CHARGES #######
			return $RESULT_ARR;
	 	}
	 	return $RESULT_ARR;
	}

	/**
	* Function Name : CalculateInvoiceAmount
	* @param object $DispatchObj
	* @return integer $TotalInvoiceAmount
	* @author Kalpak Prajapati
	* @since 2022-07-21
	* @access public
	* @uses method used to Calculate Invoice Total Amount
	*/
	public static function CalculateInvoiceAmount($DispatchObj)
	{
		$FromMrf 			= (isset($DispatchObj->from_mrf) && !empty($DispatchObj->from_mrf)) ? $DispatchObj->from_mrf : 'N';
		$DeptId 			= (isset($DispatchObj->master_dept_id) && !empty($DispatchObj->master_dept_id)) ? $DispatchObj->master_dept_id : 0;
		$BILL_FROM_MRF_ID 	= (isset($DispatchObj->bill_from_mrf_id) && !empty($DispatchObj->bill_from_mrf_id)) ? $DispatchObj->bill_from_mrf_id : $DeptId;
		/* IF DIRECT DISPATCH FROM MRF THEN ADD MRF ID IN ORIGIN AND CITY OF MRF IN ORIGIN CITY - 03 JULY 2019*/
		if(isset($DispatchObj->origin) && !empty($DispatchObj->origin)) {
			if($FromMrf == "N") {
				$OriginCity 	= CustomerMaster::where("customer_id",$DispatchObj->origin)->value('city');
			} else {
				$OriginCity 	= WmDepartment::where("id",$DispatchObj->origin)->value('location_id');
				$DeptId 		= $DispatchObj->origin;
			}
		}
		if(isset($DispatchObj->destination) && !empty($DispatchObj->destination)) {
			$DestinationCity 	= WmClientMaster::where("id",$DispatchObj->destination)->value('city_id');
		}
		$OriginGSTStateCodeData = StateMaster::GetGSTCodeByCustomerCity($OriginCity);
		if(isset($OriginGSTStateCodeData->state_code)) {
			$OriginStateCode 	= $OriginGSTStateCodeData->state_code;
		}
		$ClientMasterData = WmClientMaster::find($DispatchObj->destination);
		if($ClientMasterData)
		{
			$DestinationStateCode 	= $ClientMasterData->gst_state_code;
			if(empty($DestinationStateCode)) {
				$getCode = StateMaster::GetGSTCodeByCustomerCity($ClientMasterData->city_id);
				if(isset($getCode->state_code)) {
					$DestinationStateCode = $getCode->state_code;
				}
			}
		}
		/* ####### MASTER DEPARTMENT GST STATE CODE CHANGES ####### */
		$MasterDeptStateID = 0;
		if(!empty($BILL_FROM_MRF_ID)) {
			$MasterDeptStateID 	= WmDepartment::where("id",$BILL_FROM_MRF_ID)->value('gst_state_code_id');
			$OriginStateCode 	= GSTStateCodes::where("id",$MasterDeptStateID)->value("display_state_code");
		}
		/* ####### MASTER DEPARTMENT GST STATE CODE CHANGES ####### */
		if(!empty($OriginStateCode) && !empty($DestinationStateCode)) {
			$isFromSameState = ($OriginStateCode == $DestinationStateCode)?true:false;
		} else {
			$isFromSameState = false;
		}
		/* IF DIRECT DISPATCH FROM MRF THEN ADD MRF ID IN ORIGIN AND CITY OF MRF IN ORIGIN CITY - 03 JULY 2019*/


		$TotalInvoiceAmount = 0;
		$TotalDispatchQty 	= 0;
		$ARR_GST_PERCENT 	= array();
		$FINAL_AMT 			= 0;

		/** CALCULATE PRODUCT AMOUNT TOTAL */
		if(isset($DispatchObj->sales_product)) {
			$salesProduct = json_decode($DispatchObj->sales_product);
			if(is_array($salesProduct) && !empty($salesProduct))
			{
				$totalQty = 0;
				foreach($salesProduct as $raw)
				{
					$PRODUCT_ID 		= ($raw->product_id > 0)?$raw->product_id:0;
					$QTY 				= ($raw->quantity > 0)?$raw->quantity:0;
					$RATE 				= ($raw->rate > 0)?$raw->rate:0;
					$GST_ARR 			= WmProductMaster::calculateProductGST($PRODUCT_ID,$QTY,$RATE,$isFromSameState);
					$TotalInvoiceAmount = $TotalInvoiceAmount + $GST_ARR['TOTAL_NET_AMT'];
					$TotalDispatchQty 	= $TotalDispatchQty + $QTY;
					array_push($ARR_GST_PERCENT,$GST_ARR['SUM_GST_PERCENT']);
				}
			}
		}
		/** CALCULATE PRODUCT AMOUNT TOTAL */

		/** CALCULATE FREIGHT GST AMOUNT */
		$GST_PERCENT= 0;
		$RENT_SGST 	= 0;
		$RENT_CGST 	= 0;
		$RENT_IGST 	= 0;
		if(!empty($ARR_GST_PERCENT)) {
			$MAX_GST_PERCENT 	= _FormatNumberV2(max($ARR_GST_PERCENT));
			$GST_PERCENT 		= ($isFromSameState) ? $MAX_GST_PERCENT / 2 : $MAX_GST_PERCENT;
		}
		if($isFromSameState) {
			$RENT_SGST = $GST_PERCENT;
			$RENT_CGST = $GST_PERCENT;
		} else {
			$RENT_IGST = $GST_PERCENT;
		}
		$RENT_GST_AMT 	= 0;
		$DISPATCH_RENT 	= $DispatchObj->rent_amount;
		if($DISPATCH_RENT > 0) {
			$DISPATCH_RENT 	= _FormatNumberV2($DispatchData->rent_amount);
			$RENT_GST_AMT 	= (($DISPATCH_RENT * $MAX_GST_PERCENT) / 100);
		}
		$TOTAL_RENT_AMT 	= $DISPATCH_RENT + $RENT_GST_AMT;
		$TotalInvoiceAmount += $TOTAL_RENT_AMT;
		$TotalInvoiceAmount -= (!empty($DispatchData->discount_amt)) ? _FormatNumberV2($DispatchData->discount_amt) : 0;
		/** CALCULATE FREIGHT GST AMOUNT */

		/*########## CALCULATE TCS AMOUNT###########*/
		$TCS_START_DATE		= strtotime(TCS_STATE_DATE_TIME);
		$DISPATCH_DATE_TIME = date("Y-m-d H:i:s");
		$TCS_TAX_PERCENT 	= 0;
		$TCS_TAX_RATE 		= 0;
		if(!empty($DISPATCH_DATE_TIME) && strtotime($DISPATCH_DATE_TIME) >= $TCS_START_DATE)
		{
			$TCS_TAX_PERCENT 	= TCS_TEX_PERCENT;
			$TCS_TAX_RATE 		= (isset($ClientMasterData->tcs_tax_allow) && $ClientMasterData->tcs_tax_allow == 1) ? _FormatNumberV2(((TCS_TEX_PERCENT / 100) * $FINAL_AMT)) : 0;
		}
		$TotalInvoiceAmount += $TCS_TAX_RATE;
		/*########## CALCULATE TCS AMOUNT###########*/

		$InvoiceAdditionalCharges = InvoiceAdditionalCharges::CalculateInvoiceAdditionalCharges($ClientMasterData->id,$TotalDispatchQty,$IsFromSameState);
		$TotalInvoiceAmount += $InvoiceAdditionalCharges;

		return $TotalInvoiceAmount;
	}
}