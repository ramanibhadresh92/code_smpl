<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmServiceProductMapping;
use App\Models\WmDepartment;
use App\Models\WmClientMaster;
use App\Models\AdminUser;
use App\Models\CompanyMaster;
use App\Models\StateMaster;
use App\Models\LocationMaster;
use App\Models\GSTStateCodes;
use App\Models\Parameter;
use App\Models\WmServiceProductMaster;
use App\Models\WmServiceInvoicesCreditDebitNotesDetails;
use App\Models\WmServiceInvoicesCreditDebitNotes;
use App\Models\WmServiceDocuments;
use App\Models\UserBaseLocationMapping;
use App\Models\WmServicePOAmount;
use App\Models\ServiceAppointmentMappingMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use PDF;
use Mail;
use App\Models\ShippingAddressMaster;
use App\Models\MasterCodes;
use App\Models\MediaMaster;
use Illuminate\Support\Facades\Storage;
use DB;
class WmServiceMaster extends Model implements Auditable
{
	protected 	$table 		= 'wm_service_master';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;
	use AuditableTrait;

	public function ProductList() {
		return $this->hasMany(WmServiceProductMapping::class,"service_id","id");
	}

	public function Department() {
		return $this->belongsTo(WmDepartment::class,"mrf_id");
	}

	public function Client() {
		return $this->belongsTo(WmClientMaster::class,"client_id");
	}

	public function Company() {
		return $this->belongsTo(CompanyMaster::class,"company_id");
	}
	public function ShippingAddressData() {
		return $this->belongsTo(ShippingAddressData::class,"shipping_address_id");
	}
	/*
	Use 	: Save Service Details
	Author 	: Upasana
	Date 	: 07 April 2021
	*/
	public static function SaveService($request)
	{
		if(!is_object($request)){
			$request = json_decode($request);
		}

		$is_slab_invoice 	= (isset($request->is_slab_invoice) && !empty($request->is_slab_invoice)) ? $request->is_slab_invoice : 0;
		$id 				= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$mrf_id 			= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$invoice_date 		= date("Y-m-d");
		$client_id 			= (isset($request->client_id) && !empty($request->client_id)) ? $request->client_id : 0;
		$delivery_note 		= (isset($request->delivery_note) && !empty($request->delivery_note)) ? $request->delivery_note : "";
		$remarks 			= (isset($request->remarks) && !empty($request->remarks)) ? $request->remarks : "";
		$terms_payment 		= (isset($request->terms_payment) && !empty($request->terms_payment)) ? $request->terms_payment : "";
		$supplier_ref 		= (isset($request->supplier_ref) && !empty($request->supplier_ref)) ? $request->supplier_ref : "";
		$buyer_no 			= (isset($request->buyer_no) && !empty($request->buyer_no)) ? $request->buyer_no : "";
		$dated 				= (isset($request->dated) && !empty($request->dated)) ? date("Y-m-d",strtotime($request->dated)) : "";
		$dispatch_doc_no	= (isset($request->dispatch_doc_no) && !empty($request->dispatch_doc_no)) ? $request->dispatch_doc_no : 0;
		$delivery_note_date = (isset($request->delivery_note_date) && !empty($request->delivery_note_date)) ? date("Y-m-d",strtotime($request->delivery_note_date)) : "";
		$dispatch_through 	= (isset($request->dispatch_through) && !empty($request->dispatch_through)) ? $request->dispatch_through : "";
		$destination 		= (isset($request->destination) && !empty($request->destination)) ? $request->destination : "";
		$service_type 		= (isset($request->service_type) && !empty($request->service_type)) ? $request->service_type : "";
		$billing_address_id = (isset($request->billing_address_id) && !empty($request->billing_address_id)) ? $request->billing_address_id : 0;
		$shipping_address_id = (isset($request->shipping_address_id) && !empty($request->shipping_address_id)) ? $request->shipping_address_id : 0;
		$is_service_invoice = (isset($request->is_service_invoice) && !empty($request->is_service_invoice)) ? $request->is_service_invoice : 0;
		$email_for_notification = (isset($request->email_for_notification) && !empty($request->email_for_notification)) ? $request->email_for_notification : "";
		$is_tradex 			= (isset($request->is_tradex) && !empty($request->is_tradex)) ? $request->is_tradex : 0;
		$company_id 		= (isset($request->company_id) && !empty($request->company_id)) ? $request->company_id : 1;
		$tradex_record_id 	= (isset($request->tradex_record_id) && !empty($request->tradex_record_id)) ? $request->tradex_record_id : 0;
		$is_buyer_tradex 	= (isset($request->is_buyer_tradex) && !empty($request->is_buyer_tradex) && $request->is_buyer_tradex == true) ? true : false;
		
		$service_data 		= self::find($id);
		if(!$service_data){
			$service_data 				= new self();
			$createdAt 					= date("Y-m-d H:i:s");
			$service_data->created_at	= $createdAt;
			$service_data->created_by	= (\Auth::check()) ? Auth()->user()->adminuserid :  0; 
		}else{
			$updatedAt 					= date("Y-m-d H:i:s");
			$service_data->updated_at	= $updatedAt;
			$service_data->created_by	=(\Auth::check()) ? Auth()->user()->adminuserid :  0; 
			$is_tradex 					= $service_data->is_tradex;
			$tradex_record_id 			= $service_data->tradex_record_id ;
			$is_buyer_tradex 			= $service_data->is_buyer_tradex ;
		}
		$DepartmentData 		= WmDepartment::find($mrf_id)->GetDepartmentGSTCode;
		$MRF_GST_STATE_CODE 	= (isset($DepartmentData->display_state_code) && !empty($DepartmentData->display_state_code)) ? $DepartmentData->display_state_code : 0;
		
		if($billing_address_id > 0){
			$CLIENT_STATE_CODE_DATA = ShippingAddressMaster::find($billing_address_id)->GetShippingStateCode;
		}else{
			$CLIENT_STATE_CODE_DATA = WmClientMaster::find($client_id)->ClientGSTStateCode;
		}

		$CLIENT_STATE_CODE 	= (isset($CLIENT_STATE_CODE_DATA->display_state_code) && !empty($CLIENT_STATE_CODE_DATA->display_state_code)) ? $CLIENT_STATE_CODE_DATA->display_state_code : 0; 
		$IGST_FLAG 		= true;
		if($MRF_GST_STATE_CODE == $CLIENT_STATE_CODE){
			$IGST_FLAG 	= false;
		}
		$service_data->mrf_id 				= $mrf_id;
		$service_data->invoice_date			= $invoice_date;
		$service_data->client_id 			= $client_id;
		$service_data->delivery_note		= $delivery_note;
		$service_data->remarks 				= $remarks;
		$service_data->terms_payment 		= $terms_payment;
		$service_data->supplier_ref 		= $supplier_ref;
		$service_data->buyer_no 			= $buyer_no;
		$service_data->dated 				= $dated;
		$service_data->dispatch_doc_no 		= $dispatch_doc_no;
		$service_data->delivery_note_date 	= $delivery_note_date;
		$service_data->dispatch_through		= $dispatch_through;
		$service_data->destination			= $destination;
		$service_data->company_id			= (\Auth::check()) ? Auth()->user()->company_id :  $company_id; 
		$service_data->service_type			= $service_type;
		$service_data->billing_address_id	= $billing_address_id;
		$service_data->is_service_invoice	= $is_service_invoice;
		$service_data->shipping_address_id	= $shipping_address_id;
		$service_data->email_for_notification = $email_for_notification;
		$service_data->is_tradex			= $is_tradex;
		$service_data->tradex_record_id		= $tradex_record_id;
		$service_data->is_buyer_tradex		= $is_buyer_tradex;
		if($service_data->save()){
			$id = $service_data->id;
			WmServiceProductMapping::SaveServiceProduct($request,$id,$IGST_FLAG);
			ServiceAppointmentMappingMaster::saveData($request,$id);	
			// LR_Modules_Log_CompanyUserActionLog($request,$id);
		}
		return $id;
	}

		/*
	Use 	: View And Save Specific Service Details
	Author 	: Hardyesh Gupta
	Date 	: 15 Sep 2023
	*/
	public static function ViewSaveServiceDetails($request)
	{
		if(!is_object($request)){
			$request = json_decode($request);
		}		
		$id 			= (isset($request->id) && !empty($request->id)) ? $request->id : 0;		
		$dated 			= (isset($request->dated) && !empty($request->dated)) ? date("Y-m-d",strtotime($request->dated)) : "";		
		$buyer_no 		= (isset($request->buyer_no) && !empty($request->buyer_no)) ? $request->buyer_no : "";
		$remarks 		= (isset($request->remarks) && !empty($request->remarks)) ? $request->remarks : "";	
		$supplier_ref 	= (isset($request->supplier_ref) && !empty($request->supplier_ref)) ? $request->supplier_ref : "";	
		$service_data 					= self::find($id);
		$updatedAt 						= date("Y-m-d H:i:s");
		$service_data->buyer_no 		= $buyer_no;
		$service_data->dated 			= $dated;
		$service_data->remarks 			= $remarks;
		$service_data->updated_at		= $updatedAt;
		$service_data->supplier_ref		= $supplier_ref;
		$service_data->created_by		= Auth()->user()->adminuserid;
		if($service_data->save()){
			$id = $service_data->id;
			WmServiceProductMapping::ViewSaveServiceProduct($request,$id);	
		}
		return $id;
	}

	public static function GetServiceDetailsList($request)
	{
		$WMSM 			= (new static)->getTable();
		$WMSPM 			= new WmServiceProductMapping();
		$WMSP 			= $WMSPM->getTable();
		$WMD 			= new WmDepartment();
		$WMDM 			= $WMD->getTable();
		$CM 			= new WmClientMaster();
		$CMT 			= $CM->getTable();
		$Admin 			= new AdminUser();
		$sortBy 		= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder 		= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage 	= !empty($request->input('size')) ? $request->input('size') : DEFAULT_SIZE;
		$pageNumber    	= !empty($request->input('pageNumber')) ? $request->input('pageNumber') : '';
		$cityId        	= GetBaseLocationCity();
		$is_service_invoice = "";
		$data 			= self::with(["ProductList" => function($query){
										$query->select(
											"wm_service_product_mapping.*",
											"parameter.para_value as uom_value",
											"SPM.product as product"
										);
									$query->leftjoin("wm_service_product_master as SPM","wm_service_product_mapping.product_id","=","SPM.id");
									$query->leftjoin("parameter","wm_service_product_mapping.uom","=","parameter.para_id");
							}])->select("$WMSM.*",
										\DB::raw("(
											CASE WHEN $WMSM.approval_status = 0 THEN 'Pending'
												WHEN $WMSM.approval_status = 1 THEN 'Approved'
												WHEN $WMSM.approval_status = 2 THEN 'Rejected'
											END) AS approval_status_name"),
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as approved_by_name"),
										\DB::raw("$WMDM.department_name"),
										\DB::raw("$WMSM.irn"),
										\DB::raw("$WMDM.gst_in as mrf_gst_in"),
										\DB::raw("PARAM.para_value as service_type_name"),
										\DB::raw("$CMT.gstin_no as client_gst_in"),
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw("SAM.shipping_address as billing_full_address"),
										\DB::raw("SAM.gst_no as billing_gst_no"),
										\DB::raw("SAM2.shipping_address as shipping_full_address"),
										\DB::raw("SAM2.gst_no  as shipping_gst_in"),
										\DB::raw("SAM.gst_no as billing_gst_no"),
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw("$CMT.client_name as client_name"))
						->leftjoin($WMDM,"$WMDM.id","=","$WMSM.mrf_id")
						->leftjoin($Admin->getTable()." as U1","$WMSM.created_by","=","U1.adminuserid")
						->leftjoin($Admin->getTable()." as U2","$WMSM.approved_by","=","U2.adminuserid")
						->leftjoin("parameter as PARAM","$WMSM.service_type","=","PARAM.para_id")
						->leftjoin("shipping_address_master as SAM","$WMSM.billing_address_id","=","SAM.id")
						->leftjoin("shipping_address_master as SAM2","$WMSM.shipping_address_id","=","SAM2.id")
						->leftjoin($CMT,"$CMT.id","=","$WMSM.client_id");
		if($request->has('params.approval_status'))
		{
			if($request->input('params.approval_status') == "0"){
				$data->where("$WMSM.approval_status",$request->input('params.approval_status'));
			}elseif($request->input('params.approval_status') == "1" || $request->input('params.approval_status') == "2"){
				$data->where("$WMSM.approval_status",$request->input('params.approval_status'));
			}
		}
		if($request->has('params.serial_no') && !empty($request->input('params.serial_no')))
		{
			$data->where("$WMSM.serial_no","like","%".$request->input('params.serial_no')."%");
		}
		if($request->has('params.client_id') && !empty($request->input('params.client_id')))
		{
			$data->where("$WMSM.client_id",$request->input('params.client_id'));
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$data->where("$WMSM.mrf_id",$request->input('params.mrf_id'));
		}
		if($request->has('params.is_service_invoice'))
		{
			$is_service_invoice = $request->input('params.is_service_invoice');
			if($is_service_invoice == "-1"){
				$is_service_invoice = 0;
				$data->where("$WMSM.is_service_invoice",$is_service_invoice);
			}elseif($is_service_invoice == 1){
				$is_service_invoice = 1;
				$data->where("$WMSM.is_service_invoice",$is_service_invoice);
			}
		}
		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$data->whereBetween("$WMSM.created_at",array(date("Y-m-d H:i:s", strtotime($request->input('params.startDate')." ".GLOBAL_START_TIME)),date("Y-m-d H:i:s", strtotime($request->input('params.endDate')." ".GLOBAL_END_TIME))));
		}else if(!empty($request->input('params.startDate'))){
			$datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
			$data->whereBetween("$WMSM.created_at",array($datefrom." ".GLOBAL_START_TIME,$datefrom." ".GLOBAL_END_TIME));
		}else if(!empty($request->input('params.endDate'))){
		   $data->whereBetween("$WMSM.created_at",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}
		$data->where(function($query) use($request,$WMDM,$cityId){
				$query->whereIn("$WMDM.location_id",$cityId);
		});
		$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		if(!empty($result)){
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
				foreach($toArray['result'] as $key => $value){

					######### E INVOICE #############
					$GST_AMT 	= WmServiceProductMapping::where("service_id",$value["id"])->sum("gst_amt");
					$GST_CHECK  = (!empty($value['mrf_gst_in'] && !empty($value["client_gst_in"])) ? true : false);

					$toArray['result'][$key]['generate_einvoice'] 	= ($value["approval_status"] == 1 && $GST_CHECK && !empty($GST_AMT) && empty($value['irn'])) ? 1 : 0;
					$toArray['result'][$key]['cancel_einvoice'] 	= ($value["approval_status"] == 1 && !empty($value['irn'])) ? 1 : 0;
					######### E INVOICE #############
					$toArray['result'][$key]['regenerate_invoice_flag'] = ($value["approval_status"] == 1 && Auth()->user()->adminuserid == 1) ? 0 : 0;
					$invoice_path= (isset($value['invoice_path']) && (!empty($value['invoice_path'])) ? $value['invoice_path'] : "");
					$invoice_name= (isset($value['invoice_name']) && (!empty($value['invoice_name'])) ? $value['invoice_name'] : "");
					$toArray['result'][$key]['invoice_url'] 			= url('/service/invoice')."/".passencrypt($value['id'])."/".passencrypt(1);;
					$toArray['result'][$key]['invoice_without_ds'] 		= url('/download-service-invoice-without-digital-signature')."/".$value['id'];
					$toArray['result'][$key]['signature_invoice_url'] 	= (!empty($invoice_path)) ? url('/'.$value['invoice_path'])."/".$value['invoice_name'] : "";
					$toArray['result'][$key]['view_signature_invoice']	= (!empty($invoice_path)) ? 1 : 0;

					$COLOR_RED 		= "red";
					$COLOR_GREEN 	= "green";
					$toArray['result'][$key]['badge_einvoice'] 			= "EI";
					$toArray['result'][$key]['badge_color_einvoice'] 	= (empty($value['ack_no'])) 	? $COLOR_RED : $COLOR_GREEN;
					$toArray['result'][$key]['agreement_copy_url'] 		= WmServiceDocuments::GetAgreementCopyURL($value['id']);
					############### MISSING DOCUMENT FLAG SHOWING ###################

				}
			}
			$result = $toArray;
		}
		return $result;
	}

	/*
	Use 	: Approval of
	Author 	: Axay Shah
	Date 	: 08 April 2021
	*/
	public static function ApproveServiceRequest($request)
	{
		$res 				= array();
		$GST_AMOUNT 		= 0;
		$data 				= false;
		$id 				= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$approval_status 	= (isset($request->approval_status) && !empty($request->approval_status)) ? $request->approval_status : 0;
		$data 				= self::find($id);
		if($data && $approval_status > 0){
			$MRF_ID 			= $data->mrf_id;
			$GET_CODE 			= TransactionMasterCodesMrfWise::GetLastTrnCode($MRF_ID,SALES_OF_SERVICE);
			$CODE 				= 0;
			$CHALLAN_NO 		= 0;
			if($GET_CODE){
				$CODE 			= 	$GET_CODE->code_value + 1;
				$CHALLAN_NO 	=   $GET_CODE->group_prefix.LeadingZero($CODE);
			}
			$array = [
				"invoice_date" 		=> date("Y-m-d"),
				"approval_status" 	=> $approval_status,
				"approval_date" 	=> date("Y-m-d H:i:s"),
				"approved_by" 		=> Auth()->user()->adminuserid,
				"serial_no" 		=> $CHALLAN_NO
			];
			$data = self::where("id",$id)->update($array);
			TransactionMasterCodesMrfWise::UpdateTrnCode($MRF_ID,SALES_OF_SERVICE,$CODE);
			########## GENERATE E INVOICE AUTOMATICLY 06 JUNE 2022 ############
			$GST_AMOUNT = WmServiceProductMapping::where("service_id",$id)->sum("gst_amt");
			if($approval_status == 1 && $GST_AMOUNT > 0){
				$res['res_from_auto_einvoice'] 	= 1;
				$res['res_result']   			= self::GenerateServiceEinvoice($id);
				return $res;
			}
			########## GENERATE E INVOICE AUTOMATICLY 06 JUNE 2022 ############
		}
		$res['res_from_auto_einvoice'] 	= 0;
		$res['res_result'] 				= $data;
		return $res;
	}
	/*
	Use 	: Get by ID
	Author 	: Axay Shah
	Date 	: 08 April 2021
	*/
	public static function GetById($id=0)
	{
		$GSTStateCodes 	= new GSTStateCodes();
		$data 			= 	self::select(
			"wm_service_master.*",
			\DB::raw("SAM.shipping_address as billing_full_address"),
			\DB::raw("SAM.gst_no as billing_gst_no"))
		->leftjoin("shipping_address_master as SAM","billing_address_id","=","SAM.id")
		->where("wm_service_master.id",$id)
		->first();
		if($data){
			$COMPANY_NAME 	= (isset($data->Company->company_name)) ? strtoupper(strtolower($data->Company->company_name)) : "";
			$COMPANY_PAN_NO = (isset($data->Company->pan)) ? strtoupper(strtolower($data->Company->pan)) : "";
			$COM_ADDRESS_1 	= (isset($data->Company->address1)) ? ucwords(strtolower($data->Company->address1)) : "";
			$COM_ADDRESS_2 	= (isset($data->Company->address2)) ? ucwords(strtolower($data->Company->address2)) : "";
			$COM_GST 		= (isset($data->Company->gstno)) ? strtoupper(strtolower($data->Company->gstno)) : "";
			$COM_CINNO 		= (isset($data->Company->cin_no)) ? strtoupper(strtolower($data->Company->cin_no)) : "";
			$COM_ZIPCODE 	= (isset($data->Company->zipcode)) ? strtoupper(strtolower($data->Company->zipcode)) : "";
			$COM_STATE 		= (isset($data->Company->state_id)) ? strtoupper(strtolower($data->Company->state_id)) : "";
			$COM_CITY 		= (isset($data->Company->city)) ? LocationMaster::where("location_id",$data->Company->city)->value("city") : "";
			$StateMaster 	= StateMaster::where("state_id",$COM_STATE)->join($GSTStateCodes->getTable()." as GST_STATE_CODES","state_master.gst_state_code_id","=","GST_STATE_CODES.id")->first();


			$MrfStateData 				= GSTStateCodes::find($data->Department->gst_state_code_id);
			$ClientStateData 			= GSTStateCodes::find($data->Client->gst_state_code);
			$data->mrf_name 			= strtoupper(strtolower($data->Department->department_name));
			$data->mrf_address 			= ucwords(strtolower($data->Department->address));
			$data->mrf_gst_in 			= strtoupper(strtolower($data->Department->gst_in));
			$data->mrf_state_code 		= ($MrfStateData) ? $MrfStateData->display_state_code : "";
			$data->mrf_state 			= ($MrfStateData) ? ucwords(strtolower($MrfStateData->state_name)) : "";
			$data->from_name 			= strtoupper(strtolower(NRMPL_TITLE));
			$data->client_name 			= ucwords(strtolower($data->Client->client_name));
			if($data->billing_address_id > 0){
				$CLIENT_STATE_CODE_DATA 	= ShippingAddressMaster::find($data->billing_address_id);
				$data->client_address 		= ucwords(strtolower($CLIENT_STATE_CODE_DATA->shipping_address));
				$data->client_gst_in 		= strtoupper(strtolower($CLIENT_STATE_CODE_DATA->gst_no));
				$data->client_state_code 	= (isset($CLIENT_STATE_CODE_DATA->GetShippingStateCode->display_state_code)) ? $CLIENT_STATE_CODE_DATA->GetShippingStateCode->display_state_code : "";
				$data->client_state 		= (isset($CLIENT_STATE_CODE_DATA->GetShippingStateCode->state_name)) ? ucwords(strtolower($CLIENT_STATE_CODE_DATA->GetShippingStateCode->state_name)) : "";
				$data->client_pincode 		= $CLIENT_STATE_CODE_DATA->pincode;
				$TO_CITY_DETAILS 			= LocationMaster::find($CLIENT_STATE_CODE_DATA->city_id);
				$data->client_city 			= ($TO_CITY_DETAILS) ? ucwords(strtolower($TO_CITY_DETAILS->city)) : "";
			}else{
				$data->client_address 		= ucwords(strtolower($data->Client->address));
				$data->client_gst_in 		= strtoupper(strtolower($data->Client->gstin_no));
				$data->client_state_code 	= ($ClientStateData) ? $ClientStateData->display_state_code : "";
				$data->client_state 		= ($ClientStateData) ? ucwords(strtolower($ClientStateData->state_name)) : "";
				$data->client_pincode 		= $data->Client->pincode;
				$TO_CITY_DETAILS 			= LocationMaster::find($data->Client->city_id);
				$data->client_city 			= ($TO_CITY_DETAILS) ? ucwords(strtolower($TO_CITY_DETAILS->city)) : "";
			}
			$data->client_pan_no 	= (isset($data->Client->pan_no)?strtoupper(strtolower($data->Client->pan_no)):"");
			$data->is_sez 			= (isset($data->Client->is_sez)? $data->Client->is_sez :0); //Added By Kalpak @since 02/06/2022
			$CLIENT_SHIPPING_DATA 		= ShippingAddressMaster::find($data->shipping_address_id);
			$data->consignee_name 		= ($CLIENT_SHIPPING_DATA) ? ucwords(strtolower($CLIENT_SHIPPING_DATA->consignee_name)) : "" ;
			$data->shipping_address 	= ($CLIENT_SHIPPING_DATA) ? ucwords(strtolower($CLIENT_SHIPPING_DATA->shipping_address)) : "" ;
			$data->shipping_gst_in 		= ($CLIENT_SHIPPING_DATA) ? strtoupper(strtolower($CLIENT_SHIPPING_DATA->gst_no)) : "";
			$data->shipping_state_code 	= (isset($CLIENT_SHIPPING_DATA->GetShippingStateCode->display_state_code)) ? $CLIENT_SHIPPING_DATA->GetShippingStateCode->display_state_code : "";
			$data->shipping_state 		= (isset($CLIENT_SHIPPING_DATA->GetShippingStateCode->state_name)) ? ucwords(strtolower($CLIENT_SHIPPING_DATA->GetShippingStateCode->state_name)) : "";
			if(isset($data->ProductList) && !empty($data->ProductList)){
				foreach($data->ProductList as $key => $value){
					$data->ProductList[$key]["uom_value"] = Parameter::where("para_id",$value->uom)->value("para_value");
					$data->ProductList[$key]["product"] 	= WmServiceProductMaster::where("id",$value->product_id)->value("product");
				}
			}
			$data->product 				= $data->ProductList;
			$data->company_title 		= $COMPANY_NAME;
			$data->company_address	 	= $COM_ADDRESS_1." ".$COM_ADDRESS_2;
			$data->company_city 		= $COM_CITY;
			$data->company_gst_in 		= strtoupper(strtolower($COM_GST));
			$data->company_cin_no 		= strtoupper(strtolower($COM_CINNO));
			$data->company_pan_no 		= strtoupper(strtolower($COMPANY_PAN_NO));
			$data->company_state_name 	= (($StateMaster) ? strtoupper(strtolower($StateMaster->state_name)) : "");
			$data->company_state_code 	= (($StateMaster) ? $StateMaster->display_state_code : "");
			$data->company_zipcode 		= $COM_ZIPCODE;

			$FROM_CITY_DETAILS 			= LocationMaster::find($data->Department->location_id);
			$data->mrf_city 			= ($FROM_CITY_DETAILS) ? ucwords(strtolower($FROM_CITY_DETAILS->city)) : "";
			$data->mrf_pincode 			= (isset($data->Department->pincode)) ? $data->Department->pincode : "";

			######### QR CODE GENERATION OF E INVOICE NO #############
			$qr_code 				= "";
			$e_invoice_no 			= (!empty($data->irn)) 		? $data->irn : "";
			$acknowledgement_no 	= (!empty($data->ack_no)) 	? $data->ack_no : "";
			$acknowledgement_date 	= (!empty($data->ack_date)) 	? $data->ack_date : "";
			$signed_qr_code 	= (!empty($data->signed_qr_code)) 	? $data->signed_qr_code : "";
			$qr_code_string 		= "E-Invoice No. :".$e_invoice_no." Acknowledgement No. : ".$acknowledgement_no." Acknowledgement Date : ".$acknowledgement_date;
			$qr_code_string 		= (empty($e_invoice_no) && empty($acknowledgement_no) && empty($acknowledgement_date)) ? " " : $qr_code_string ;
			if(!empty($e_invoice_no) || !empty($acknowledgement_no) || !empty($acknowledgement_date)){
				$name 					= "service_".$id;
				$qr_code 				= url("/")."/".GetQRCode($signed_qr_code,$id);
				$path 					= public_path("/")."phpqrcode/".$name.".png";
				$type 					= pathinfo($path, PATHINFO_EXTENSION);
				if(file_exists($path)){
					$imgData				= file_get_contents($path);
					$qr_code 				= 'data:image/' . $type . ';base64,' . base64_encode($imgData);
					unlink(public_path("/")."/phpqrcode/".$name.".png");
				}
			}
			$data->qr_code 		= $qr_code;
			$data->irn 			= $e_invoice_no ;
			$data->ack_no 		= $acknowledgement_no;
			$data->ack_date 	= $acknowledgement_date;
			$data->signature 	= (isset($data->Department->signature)) ? $data->Department->signature : "";
			######### QR CODE GENERATION OF E INVOICE NO #############

			/** Stop Editable if request is from EPR Portal. */
			$data->is_editable	= (isset($data->epr_wma_id) && $data->epr_wma_id > 0)?0:1;
			/** Stop Editable if request is from EPR Portal. */

			/** Get Agreement Copy URL */
			$data->agreement_copy_url = WmServiceDocuments::GetAgreementCopyURL($id);
			/** Get Agreement Copy URL */

		}
		return $data;
	}

	public static function ServiceReport($request)
	{
		$PRODUCT 		= new WmServiceProductMapping();
		$self 			= (new static)->getTable();
		$WMD 			= new WmDepartment();
		$WMDM 			= $WMD->getTable();
		$CM 			= new WmClientMaster();
		$CMT 			= $CM->getTable();
		$res 			= array();
		$array 			= array();
		$Admin 			= new AdminUser();
		$SPM 			= new WmServiceProductMaster();
		$Parameter 		= new Parameter();
		$CN_DE_TBL 		= new WmServiceInvoicesCreditDebitNotesDetails();
		$CN_TBL 		= new WmServiceInvoicesCreditDebitNotes();
		$CND_MASTER 	= $CN_DE_TBL->getTable();
		$CN_MASTER 		= $CN_TBL->getTable();
		$cityId        	= UserBaseLocationMapping::GetBaseLocationCityListByUser(Auth()->user()->adminuserid);
		$BASELOCATION 	= UserBaseLocationMapping::GetUserAssignBaseLocationId(Auth()->user()->adminuserid);
		$data 			= self::select("$self.*",
										"PRO.*",
										"SPM.product",
										"SPM.service_net_suit_code as net_suit_code",
										"PARA.para_value as uom_value",
										"ST.para_value as service_type_name",
										\DB::raw("(
											CASE WHEN $self.approval_status = 0 THEN 'Pending'
												WHEN $self.approval_status = 1 THEN 'Approved'
												WHEN $self.approval_status = 2 THEN 'Rejected'
											END) AS approval_status_name"),
										\DB::raw("$WMDM.department_name"),
										\DB::raw("$CMT.client_name as client_name"))
						->join($PRODUCT->getTable()." as PRO","$self.id","=","PRO.service_id")
						->join($SPM->getTable()." as SPM","PRO.product_id","=","SPM.id")
						->join($Parameter->getTable()." as PARA","PRO.uom","=","PARA.para_id")
						->leftjoin($Parameter->getTable()." as ST","$self.service_type","=","ST.para_id")
						->leftjoin($WMDM,"$self.mrf_id","=","$WMDM.id")
						->join($CMT,"$self.client_id","=","$CMT.id")
						->leftjoin($Admin->getTable()." as U1","$self.created_by","=","U1.adminuserid")
						->leftjoin($Admin->getTable()." as U2","$self.approved_by","=","U2.adminuserid");
		if($request->has('client_id') && !empty($request->input('client_id')))
		{
			$data->where("$self.client_id",$request->input('client_id'));
		}
		if($request->has('serial_no') && !empty($request->input('serial_no')))
		{
			$data->where("$self.serial_no",$request->input('serial_no'));
		}
		if($request->has('net_suit_code') && !empty($request->input('net_suit_code')))
		{
			$data->where("SPM.service_net_suit_code","like","%".$request->input('net_suit_code')."%");
		}
		if($request->has('service_type') && !empty($request->input('service_type')))
		{
			$data->where("$self.service_type",$request->input('service_type'));
		}
		if($request->has('mrf_id') && !empty($request->input('mrf_id')))
		{
			$data->where("$self.mrf_id",$request->input('mrf_id'));
		}
		if($request->has('product_id') && !empty($request->input('product_id')))
		{
			$data->where("SPM.product_id",$request->input('product_id'));
		}
		if($request->has('approval_status')) {
			if($request->input('approval_status') == "0") {
				$data->where("$self.approval_status",$request->input('approval_status'));
			} else if($request->input('approval_status') == "1" || $request->input('approval_status') == "2") {
				$data->where("$self.approval_status",$request->input('approval_status'));
			}
		}
		if($request->has('serial_no') && !empty($request->input('client_id'))) {
			$data->where("$self.serial_no","like","%".$request->input('approval_status')."%");
		}
		if($request->has('is_einvoice')) {
			 $is_einvoice = $request->input("is_einvoice");

			if($is_einvoice == "-1") {
					$data->where(function($q) use($self) {
						$q->whereNull("$self.ack_no")
						->orWhere("$self.ack_no","="," ");
					});

			} else if($is_einvoice == "1") {
				$data->whereNotNull("$self.ack_no");
			}
		}
		$startDate 	= "";
		$endDate 	= "";
		if(!empty($request->input('startDate')) && !empty($request->input('endDate'))) {
			$startDate 	= date("Y-m-d", strtotime($request->input('startDate')));
			$endDate    = date("Y-m-d", strtotime($request->input('endDate')));
			$data->whereBetween("$self.invoice_date",array($startDate,$endDate));
		} else if(!empty($request->input('startDate'))) {
			$startDate 	= date("Y-m-d", strtotime($request->input('startDate')));
			$endDate 	= $startDate;
			$data->whereBetween("$self.invoice_date",array($startDate,$endDate));
		} else if(!empty($request->input('endDate'))) {
			$endDate    = date("Y-m-d", strtotime($request->input('startDate')));
			$startDate 	= $endDate;
			$endDate 	= $Today;
			$data->whereBetween("$self.invoice_date",array($startDate,$endDate));
		}
		$data->where(function($query) use($request,$WMDM,$BASELOCATION) {
			$query->whereIn("$WMDM.base_location_id",$BASELOCATION);
		});
		$ServiceSql 		= LiveServices::toSqlWithBinding($data,true);

		$res['ServiceSql']	= $ServiceSql;
		$result 			= $data->get()->toArray();
		if(!empty($result)){
			$totalQty 	= 0;
			$totalGst 	= 0;
			$totalGross = 0;
			$totalNet 	= 0;
			$totalCN 	= 0;
			$totalDN 	= 0;
			$counter 	= 0;
			$CN_TOT 	= 0;
			$DN_TOT 	= 0;
			$TOTAL_CGST_AMT		= 0;
			$TOTAL_SGST_AMT		= 0;
			$TOTAL_IGST_AMT		= 0;
			foreach($result as $key => $value)
			{
				$CGST_AMT 	= 0;
				$SGST_AMT 	= 0;
				$IGST_AMT 	= 0;
				$CREDIT_AMT = WmServiceInvoicesCreditDebitNotesDetails::join($CN_MASTER." as CNM","$CND_MASTER.cd_notes_id","=","CNM.id")
								->where("CNM.service_id",$value["service_id"])
								->where("CNM.notes_type",0)
								->where("CNM.status",1)
								->where("$CND_MASTER.product_id",$value['product_id'])
								->sum("$CND_MASTER.revised_gross_amount");

				$DEBIT_AMT 	= WmServiceInvoicesCreditDebitNotesDetails::join($CN_MASTER." as CNM","$CND_MASTER.cd_notes_id","=","CNM.id")
								->where("CNM.service_id",$value["service_id"])
								->where("CNM.notes_type",1)
								->where("CNM.status",1)
								->where("$CND_MASTER.product_id",$value['product_id'])
								->sum("$CND_MASTER.revised_gross_amount");

				$Quantity 	= (!empty($value['quantity'])) ? _FormatNumberV2($value['quantity']) : 0 ;
				$GST_AMT 	= (!empty($value['gst_amt'])) ? _FormatNumberV2($value['gst_amt']):0;
				$NET_AMT 	= (!empty($value['net_amt'])) ? _FormatNumberV2($value['net_amt']):0;
				$GROSS_AMT 	= (!empty($value['gross_amt'])) ? _FormatNumberV2($value['gross_amt']):0;
				$totalQty 	+= $Quantity;
				$totalGst 	+= $GST_AMT;
				$totalGross += $GROSS_AMT;
				$totalNet 	+= $NET_AMT;
				$GST_NO 	= ShippingAddressMaster::where("id",$value["billing_address_id"])->value("gst_no");
				$result[$key]['billing_gst_no'] = (!empty($GST_NO)) ? $GST_NO : "";
				$result[$key]['credit_amt'] = "<font style='color:red;font-weight:bold'>"._FormatNumberV2($CREDIT_AMT)."</font>";
				$result[$key]['debit_amt'] 	= "<font style='color:green;font-weight:bold'>"._FormatNumberV2($DEBIT_AMT)."</font>";
				$CGST_AMT 	= ($value['cgst'] > 0 && $value['igst'] == 0) ? $GST_AMT / 2 : 0 ;
				$SGST_AMT 	= ($value['sgst'] > 0 && $value['igst'] == 0) ? $GST_AMT / 2 : 0 ;
				$IGST_AMT 	= ($value['igst'] > 0 && ($value['cgst'] == 0 && $value['sgst'] == 0)) ? $GST_AMT  : 0 ;
				$result[$key]['cgst_amount'] 	= _FormatNumberV2($CGST_AMT);
				$result[$key]['sgst_amount'] 	= _FormatNumberV2($SGST_AMT);
				$result[$key]['igst_amount'] 	= _FormatNumberV2($IGST_AMT);
				$TOTAL_CGST_AMT 	+= $CGST_AMT;
				$TOTAL_SGST_AMT 	+= $SGST_AMT;
				$TOTAL_IGST_AMT 	+= $IGST_AMT;
				$CN_TOT 			+= $CREDIT_AMT;
				$DN_TOT 			+= $DEBIT_AMT;
				################ minus logic ##############
				$arrResult[$counter] = $result[$key];
				$counter++;
				$tempArr = array();
				if ($value['approval_status'] == 2)
				{
					$tmpArray 					= $result[$key];
					$tmpArray['quantity']		= "-"._FormatNumberV2($Quantity);
					$tmpArray['gst_amt']		= "-"._FormatNumberV2($GST_AMT);
					$tmpArray['net_amt']		= "-"._FormatNumberV2($NET_AMT);
					$tmpArray['gross_amt']		= "-"._FormatNumberV2($GROSS_AMT);
					$totalQty 					= ($totalQty - $Quantity);
					$totalGst 					= ($totalGst - $GST_AMT);
					$totalGross					= ($totalGross - $GROSS_AMT);
					$totalNet 					= ($totalNet - $NET_AMT);
					$tmpArray['credit_amt'] 	= "<font style='color:red;font-weight:bold'>-"._FormatNumberV2($CREDIT_AMT)."</font>";
					$tmpArray['debit_amt'] 		= "<font style='color:green;font-weight:bold'>-"._FormatNumberV2($DEBIT_AMT)."</font>";
					$CN_TOT -= $CREDIT_AMT;
					$DN_TOT -= $DEBIT_AMT;
					$arrResult[$counter] 		= $tmpArray;
					$counter++;
					unset($tmpArray);
				}
				################ minus ##############
			}
			$ServiceType 	= 0;
			if($request->has('service_type') && !empty($request->input('service_type'))) {
				$ServiceType = $request->input('service_type');
			}
			$totalCN  		= WmServiceInvoicesCreditDebitNotes::GetServiceCreditAmt($BASELOCATION,$startDate,$endDate,0,0,$ServiceType,$request);
			$totalDN  		= WmServiceInvoicesCreditDebitNotes::GetServiceDebitAmt($BASELOCATION,$startDate,$endDate,0,0,$ServiceType,$request);
			$res['CN_AMT'] 				= _FormatNumberV2($CN_TOT);
			$res['DN_AMT'] 				= _FormatNumberV2($DN_TOT);
			$res['TOTAL_GROSS_AMT'] 	= _FormatNumberV2($totalGross);
			$res['TOTAL_NET_AMT'] 		= _FormatNumberV2($totalNet);
			$res['TOTAL_GST_AMT'] 		= _FormatNumberV2($totalGst);
			$res['TOTAL_QUANTITY'] 		= _FormatNumberV2($totalQty);
			$res['TOTAL_CN'] 			= "<font style='color:red;font-weight:bold'>"._FormatNumberV2($totalCN)."</font>";
			$res['TOTAL_DN'] 			= "<font style='color:green;font-weight:bold'>"._FormatNumberV2($totalDN)."</font>";
			$res['TOTAL_FINAL_AMT'] 	= _FormatNumberV2(($totalGross + $totalDN) - $totalCN);
			$res['TOTAL_CGST_AMT'] 		= _FormatNumberV2($TOTAL_CGST_AMT);
			$res['TOTAL_SGST_AMT'] 		= _FormatNumberV2($TOTAL_SGST_AMT);
			$res['TOTAL_IGST_AMT'] 		= _FormatNumberV2($TOTAL_IGST_AMT);
			$res['res']					= $arrResult;
			//$res['resultData']			= $arrResult;
		}
		return $res;
	}

	/*
	Use 	: Generate E invoice for Asset
	Author 	: Axay Shah
	Date 	: 20 April 2021
	*/
	public static function GenerateServiceEinvoice($ID)
	{
		$data   = self::GetById($ID);
		$array  = array();
		$res 	= array();
		if(!empty($data)){
			$IS_SEZ 			= (isset($data->is_sez)) ? $data->is_sez  : 0;
			$IS_SERVICE_INVOICE =(isset($data->is_service_invoice) && $data->is_service_invoice == 1) ? "Y" : "N";
			$SellerDtls   		= array();
			$BuyerDtls 			= array();
			$MERCHANT_KEY 		= (isset($data->Company->merchant_key)) ? $data->Company->merchant_key : "";
			$COMPANY_NAME 		= (isset($data->Company->company_name) && !empty($data->Company->company_name)) ? $data->Company->company_name : null;
			$USERNAME 			= (isset($data->Department->gst_username) && !empty($data->Department->gst_username)) ? $data->Department->gst_username : "";
			$PASSWORD 			= (isset($data->Department->gst_password) && !empty($data->Department->gst_password)) ? $data->Department->gst_password : "";
			$GST_IN 			= (isset($data->Department->gst_in) && !empty($data->Department->gst_in)) ? $data->Department->gst_in : "";
			############## SALLER DETAILS #############
			$FROM_ADDRESS_1 	= (!empty($data->mrf_address)) ? $data->mrf_address : null;
			$FROM_ADDRESS_2 	= null;
			if(strlen($FROM_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($FROM_ADDRESS_1);
				$FROM_ADDRESS_1 = (!empty($ARR_STRING)) ? $ARR_STRING[0] : $FROM_ADDRESS_1;
				$FROM_ADDRESS_2 = (!empty($ARR_STRING)) ? $ARR_STRING[1] : $FROM_ADDRESS_1;
			}
			$FROM_TREAD 		= $COMPANY_NAME;
			// $FROM_ADDRESS 		= (!empty($data->mrf_address)) ? $data->mrf_address : null;
			$FROM_GST 			= (!empty($data->mrf_gst_in)) ? $data->mrf_gst_in : null;
			$FROM_STATE_CODE 	= (!empty($data->mrf_state_code)) ? $data->mrf_state_code : null;
			$FROM_STATE 		= (!empty($data->mrf_state)) ? $data->mrf_state : null;
			$FROM_LOC 			= (!empty($data->mrf_city)) ? $data->mrf_city : null;
			$FROM_PIN 			= (!empty($data->mrf_pincode)) ? $data->mrf_pincode : null;

			############## BUYER DETAILS #############
			$TO_ADDRESS_1 		= (!empty($data->client_address)) ? $data->client_address : null;
			$TO_ADDRESS_2 		= null;
			if(strlen($TO_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($TO_ADDRESS_1);
				// prd($ARR_STRING);
				$TO_ADDRESS_1 	= (!empty($ARR_STRING)) ? $ARR_STRING[0] : $TO_ADDRESS_1;
				$TO_ADDRESS_2 	= (!empty($ARR_STRING)) ? $ARR_STRING[1] : $TO_ADDRESS_1;
			}
			$TO_TREAD 			= (!empty($data->client_name)) ? $data->client_name : null;
			// $TO_ADDRESS 		= (!empty($data->client_address)) ? $data->client_address : null;
			$TO_GST 			= (!empty($data->client_gst_in)) ? $data->client_gst_in : null;
			$TO_STATE_CODE 		= (!empty($data->client_state_code)) ? $data->client_state_code : null;
			$TO_STATE 			= (!empty($data->client_state)) ? $data->client_state : null;
			$TO_LOC 			= (!empty($data->client_city)) ? $data->client_city : null;
			$TO_PIN 			= (!empty($data->client_pincode)) ? $data->client_pincode : null;
			$DOC_NO 			= (isset($data->serial_no) && !empty($data->serial_no)) ? $data->serial_no : null;
			$DOC_DATE 			= (isset($data->invoice_date) && !empty($data->invoice_date)) ? date("d/m/Y",strtotime($data->invoice_date)) : null;

			$array["merchant_key"] 	= $MERCHANT_KEY;
			$array["username"] 		= $USERNAME;
			$array["password"] 		= $PASSWORD;
			$array["user_gst_in"] 	= $GST_IN;


			$SellerDtls["Gstin"] = (string)$FROM_GST;
			$SellerDtls["LglNm"] = (string)$FROM_TREAD;
			$SellerDtls["TrdNm"] = (string)$FROM_TREAD;
			$SellerDtls["Addr1"] = (string)$FROM_ADDRESS_1;
			$SellerDtls["Addr2"] = (string)$FROM_ADDRESS_2;
			$SellerDtls["Loc"]   = (string)$FROM_LOC;
			$SellerDtls["Pin"]   = $FROM_PIN;
			$SellerDtls["Stcd"]  = (string)$FROM_STATE_CODE;
			$SellerDtls["Ph"]    = null;
			$SellerDtls["Em"]    = null;

			$BuyerDtls["Gstin"] = (string)$TO_GST;
			$BuyerDtls["LglNm"] = (string)$TO_TREAD;
			$BuyerDtls["TrdNm"] = (string)$TO_TREAD;
			$BuyerDtls["Addr1"] = (string)$TO_ADDRESS_1;
			$BuyerDtls["Addr2"] = (string)$TO_ADDRESS_2;
			$BuyerDtls["Loc"]   = (string)$TO_LOC;
			$BuyerDtls["Pin"]   = $TO_PIN;
			$BuyerDtls["Stcd"]  = (string)$TO_STATE_CODE;
			$BuyerDtls["Ph"]    = null;
			$BuyerDtls["Em"]    = null;
			$BuyerDtls["Pos"]   = (string)$TO_STATE_CODE;

			$SAME_STATE 	= ($FROM_STATE_CODE == $TO_STATE_CODE) ? true : false;

			$IGST_ON_INTRA 	= "N";

			$array['merchant_key']				= $MERCHANT_KEY;
			$array["SellerDtls"] 				= $SellerDtls;
			$array["BuyerDtls"] 				= $BuyerDtls;
			$array["BuyerDtls"] 				= $BuyerDtls;
			$array["DispDtls"]   				= null;
			$array["ShipDtls"]    				= null;
			$array["EwbDtls"]     				= null;
			$array["version"]     				= E_INVOICE_VERSION;
			$array["TranDtls"]["TaxSch"]        = TAX_SCH ;
			$array["TranDtls"]["SupTyp"]        = ($IS_SEZ == 1) ? "SEZWOP" : "B2B";
			$array["TranDtls"]["RegRev"]        = "N";
			$array["TranDtls"]["EcmGstin"]      = null;
			$array["TranDtls"]["IgstOnIntra"]   = $IGST_ON_INTRA;
			$array["DocDtls"]["Typ"]            = "INV";
			$array["DocDtls"]["No"]             = $DOC_NO;
			$array["DocDtls"]["Dt"]             = $DOC_DATE;
			$itemList                          	= isset($data->product) ? $data->product:array();
			$item   							= array();
			$TOTAL_CGST 		= 0;
			$TOTAL_SGST 		= 0;
			$TOTAL_IGST 		= 0;
			$TOTAL_NET_AMOUNT 	= 0;
			$TOTAL_GST_AMOUNT 	= 0;
			$TOTAL_GROSS_AMOUNT = 0;
			$DIFFERENCE_AMT 	= 0;
			if(!empty($itemList)){
				$i = 1;
				foreach($itemList as $key => $value){
					$UOM 						= $value->uom;
					if($UOM == PARA_PRODUCT_UNIT_IN_KG){
						$UOM = "KGS";
					}elseif($UOM == PARA_PRODUCT_UNIT_IN_NOS){
						$UOM = "NOS";
					}else{
						$UOM = "OTH";
					}
					$TOTAL_GST_PERCENT 			= ($SAME_STATE) ? _FormatNumberV2($value->sgst + $value->cgst) :  _FormatNumberV2($value->igst);
					$QTY 						= (float)$value->quantity;
					$RATE 						= (float)$value->rate;
					$IGST 						= (float)$value->igst;
					$SGST 						= (float)$value->sgst;
					$CGST 						= (float)$value->cgst;
					$GST_ARR				 	= GetGSTCalculation($QTY,$RATE,$SGST,$CGST,$IGST,$SAME_STATE);
					$CGST_RATE      			= $GST_ARR['CGST_RATE'];
					$SGST_RATE      			= $GST_ARR['SGST_RATE'];
					$IGST_RATE      			= $GST_ARR['IGST_RATE'];
					$TOTAL_GR_AMT   			= $GST_ARR['TOTAL_GR_AMT'];
					$TOTAL_NET_AMT  			= $GST_ARR['TOTAL_NET_AMT'];
					$CGST_AMT       			= $GST_ARR['CGST_AMT'];
					$SGST_AMT       			= $GST_ARR['SGST_AMT'];
					$IGST_AMT       			= $GST_ARR['IGST_AMT'];
					$TOTAL_GST_AMT  			= $GST_ARR['TOTAL_GST_AMT'];
					$SUM_GST_PERCENT 			= $GST_ARR['SUM_GST_PERCENT'];
					$TOTAL_CGST 				+= $CGST_AMT;
					$TOTAL_SGST 				+= $SGST_AMT;
					$TOTAL_IGST 				+= $IGST_AMT;
					$TOTAL_NET_AMOUNT 			+= $TOTAL_NET_AMT;
					$TOTAL_GST_AMOUNT 			+= $TOTAL_GST_AMT;
					$TOTAL_GROSS_AMOUNT 		+= $TOTAL_GR_AMT;
					$item[] 					= array(
														"SlNo"              	=> $i,
														"PrdDesc"               => $value->product,
														"IsServc"               => $IS_SERVICE_INVOICE,
														"HsnCd"                 => $value->hsn_code,
														"Qty"                   => _FormatNumberV2((float)$QTY),
														"Unit"                  => $UOM,
														"UnitPrice"             => _FormatNumberV2((float)$RATE),
														"TotAmt"                => _FormatNumberV2((float)$TOTAL_GR_AMT),
														"Discount"              => _FormatNumberV2((float)0),
														"PreTaxVal"             => _FormatNumberV2((float)0),
														"AssAmt"                => _FormatNumberV2((float)$TOTAL_GR_AMT),
														"GstRt"                 => _FormatNumberV2((float)$SUM_GST_PERCENT),
														"IgstAmt"               => _FormatNumberV2((float)$IGST_AMT),
														"CgstAmt"               => _FormatNumberV2((float)$CGST_AMT),
														"SgstAmt"               => _FormatNumberV2((float)$SGST_AMT),
														"CesRt"                 => 0,
														"CesAmt"                => 0,
														"CesNonAdvlAmt"         => 0,
														"StateCesRt"            => 0,
														"StateCesAmt"           => 0,
														"StateCesNonAdvlAmt"    => 0,
														"OthChrg"               => 0,
														"TotItemVal"            => _FormatNumberV2((float)$TOTAL_NET_AMT));
					$i++;
				}
			}
			####### ITEM DETAILS ###########
			$array["ItemList"]  =  $item;
			####### ITEM DETAILS ###########
			$DIFFERENCE_AMT 	= _FormatNumberV2(round($TOTAL_NET_AMOUNT) - $TOTAL_NET_AMOUNT);
			######## SUMMERY OF INVOICE DETAILS ###########
			$array["ValDtls"]["AssVal"]     = _FormatNumberV2($TOTAL_GROSS_AMOUNT);
			$array["ValDtls"]["CgstVal"]    = _FormatNumberV2($TOTAL_CGST);
			$array["ValDtls"]["SgstVal"]    = _FormatNumberV2($TOTAL_SGST);
			$array["ValDtls"]["IgstVal"]    = _FormatNumberV2($TOTAL_IGST);
			$array["ValDtls"]["CesVal"]     = 0;
			$array["ValDtls"]["StCesVal"]   = 0;
			$array["ValDtls"]["Discount"]   = 0;
			$array["ValDtls"]["OthChrg"]    = 0;
			$array["ValDtls"]["RndOffAmt"]  = _FormatNumberV2($DIFFERENCE_AMT);
			$array["ValDtls"]["TotInvVal"]  = round($TOTAL_NET_AMOUNT);
			if(!empty($array))
			{
				$url 		= EWAY_BILL_PORTAL_URL."generate-einvoice";
				$client 	= new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
				$response 	= $client->request('POST', $url,array('form_params' => $array));
				$response = $response->getBody()->getContents();
				if(!empty($response)){
					$res   	= json_decode($response,true);
					if(isset($res["Status"]) && $res["Status"] == 1){
						$details 	= $res["Data"];
						$AckNo  	= (isset($details['AckNo'])) ? $details['AckNo']  : "";
						$AckDt  	= (isset($details['AckDt'])) ? $details['AckDt']  : "";
						$Irn    	= (isset($details['Irn'])) ? $details['Irn']      : "";
						$SignedQRCode   = (isset($details['SignedQRCode'])) ? $details['SignedQRCode']      : "";
						self::where("id",$ID)->update([	"irn" 			=> $Irn,
														"ack_date" 		=> $AckDt,
														"ack_no" 		=> $AckNo,
														"signed_qr_code" 		=> $SignedQRCode,
														"updated_at" 	=> date("Y-m-d H:i:s"),
														"updated_by" 	=> Auth()->user()->adminuserid]);
						########## STORE LOG OF DISPATCH TO SEND EMAIL TO CLIENT IN BACKGROUD ###########
  						self::StoreServiceInvoiceEmailSentLog($ID);
  						########## STORE LOG OF DISPATCH TO SEND EMAIL TO CLIENT IN BACKGROUD ###########
					}
				}
				return $res;
			}
		}
	}
	/*
	Use 	: Generate E invoice Number Data
	Author 	: Axay Shah
	Date  	: 09 March 2021
	*/
	public static function CancelServiceEInvoice($request){
		$res 				= array();
		$ID   				= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : "";
		$IRN   				= (isset($request['irn']) && !empty($request['irn'])) ? $request['irn'] : "";
		$CANCEL_REMARK  	= (isset($request['CnlRem']) && !empty($request['CnlRem'])) ? $request['CnlRem'] : '';
		$CANCEL_RSN_CODE 	= (isset($request['CnlRsn']) && !empty($request['CnlRsn'])) ? $request['CnlRsn'] : '';
		$data 				= self::find($ID);
		if($data){
			// prd($data);
			$MERCHANT_KEY 	= CompanyMaster::where("company_id",Auth()->user()->company_id)->value('merchant_key');
			$DepartmentData = WmDepartment::find($data->mrf_id);
			$array['merchant_key'] 	= (!empty($MERCHANT_KEY)) ? $MERCHANT_KEY : "";
			$GST_USER_NAME 	= ($DepartmentData && !empty($DepartmentData->gst_username)) ? $DepartmentData->gst_username : "";
			$GST_PASSWORD 	= ($DepartmentData && !empty($DepartmentData->gst_password)) ? $DepartmentData->gst_password : "";
			$GST_GST_IN 	= ($DepartmentData && !empty($DepartmentData->gst_in)) ? $DepartmentData->gst_in : "";
			$IRN 			= $data->irn;
			$request["merchant_key"] 	= $MERCHANT_KEY;
			$request['username'] 		= $GST_USER_NAME;
			$request['password'] 		= $GST_PASSWORD;
			$request['user_gst_in'] 	= $GST_GST_IN;
			$request['irn'] 			= $IRN;
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
						self::where("id",$ID)
						->where("irn",$IRN)
						->update([
							"irn" 			=> "",
							"ack_date" 		=> "",
							"ack_no" 		=> "",
							"signed_qr_code" 		=> "",
							"updated_at" 	=> date("Y-m-d H:i:s"),
							"updated_by" 	=> Auth()->user()->adminuserid
						]);
					}
				}
				return $res;
			}
		}
		return $res;
	}

	/*
	Use 	: GET IRN data
	Author 	: Axay Shah
	Date  	: 09 March 2021
	*/
	public static function GetIrnDetails($request)
	{
		$res 				= array();
		$ID   				= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : "";
		$IRN   				= (isset($request['irn']) && !empty($request['irn'])) ? $request['irn'] : "";
		$data 				= self::find($ID);
		if($data) {
			$MERCHANT_KEY 	= CompanyMaster::where("company_id",1)->value('merchant_key');
			$DepartmentData = WmDepartment::find($data->mrf_id);
			$array['merchant_key'] 	= (!empty($MERCHANT_KEY)) ? $MERCHANT_KEY : "";
			$GST_USER_NAME 	= ($DepartmentData && !empty($DepartmentData->gst_username)) ? $DepartmentData->gst_username : "";
			$GST_PASSWORD 	= ($DepartmentData && !empty($DepartmentData->gst_password)) ? $DepartmentData->gst_password : "";
			$GST_GST_IN 	= ($DepartmentData && !empty($DepartmentData->gst_in)) ? $DepartmentData->gst_in : "";
			$IRN 					= $data->irn;
			$req["irn"] 			= $IRN;
			$req["merchant_key"] 	= $MERCHANT_KEY;
			$req['username'] 		= $GST_USER_NAME;
			$req['password'] 		= $GST_PASSWORD;
			$req['user_gst_in'] 	= $GST_GST_IN;
			if(!empty($MERCHANT_KEY) && !empty($IRN)) {
				$url 		= EWAY_BILL_PORTAL_URL."get-irn-details";
				$client 	= new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
				$response 	= $client->request('POST', $url,array('form_params' => $req));
				$response 		= $response->getBody()->getContents();
				return $res;
			}
		}
		return $res;
	}

	/*
	Use 	: Update E invoice number
	Author 	: Axay Shah
	Date 	: 26 April 2021
	*/
	public static function UpdateEinvoiceNo($id=0,$Einvoice="",$acknowledgement_no="",$acknowledgement_date=""){
		$responseData 	= array();
		if(!empty($id) && !empty($Einvoice)){
			$update = self::where("id",$id)->update(["irn"=>$Einvoice,"ack_no"=>$acknowledgement_no,"ack_date"=>$acknowledgement_date,"updated_at"=>date("Y-m-d H:i:s")]);
			return true;
		}
		return false;
	}

	/*
	Use 	: Save Service Bill Request By EPR Team
	Author 	: Kalpak Prajapati
	Date 	: 24 November 2021
	*/
	public function AddServiceInoviceByEPR($request)
	{
		if (isset($request->existing_invoice_no) && !empty($request->existing_invoice_no)) {
			return self::SendExistingInvoiceToEPR($request->existing_invoice_no,$request);
		} else {
			$id 							= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
			$ServiceMRF 					= WmDepartment::select("id","company_id")->where("is_service_mrf",PARA_STATUS_ACTIVE)->first();
			$ClientMaster 					= WmClientMaster::select("id")->where("code",$request->brand_lr_code)->first();
			$mrf_id 						= (isset($ServiceMRF->id) && (!empty($ServiceMRF->id))?$ServiceMRF->id:0);
			$invoice_date 					= (isset($request->invoice_date) && !empty($request->invoice_date)) ? date("Y-m-d",strtotime($request->invoice_date)) : "";
			$client_id 						= $ClientMaster->id;
			$delivery_note 					= (isset($request->delivery_note) && !empty($request->delivery_note)) ? $request->delivery_note : "";
			$remarks 						= (isset($request->remarks) && !empty($request->remarks)) ? $request->remarks : "";
			$terms_payment 					= (isset($request->terms_payment) && !empty($request->terms_payment)) ? $request->terms_payment : "";
			$supplier_ref 					= (isset($request->supplier_ref) && !empty($request->supplier_ref)) ? $request->supplier_ref : "";
			$buyer_no 						= (isset($request->buyer_no) && !empty($request->buyer_no)) ? $request->buyer_no : "";
			$dated 							= (isset($request->dated) && !empty($request->dated)) ? date("Y-m-d",strtotime($request->dated)) : "";
			$dispatch_doc_no				= (isset($request->dispatch_doc_no) && !empty($request->dispatch_doc_no)) ? $request->dispatch_doc_no : 0;
			$delivery_note_date 			= (isset($request->delivery_note_date) && !empty($request->delivery_note_date)) ? date("Y-m-d",strtotime($request->delivery_note_date)) : "";
			$dispatch_through 				= (isset($request->dispatch_through) && !empty($request->dispatch_through)) ? $request->dispatch_through : "";
			$destination 					= (isset($request->destination) && !empty($request->destination)) ? $request->destination : "";
			$epr_wma_id 					= (isset($request->wma_id) && !empty($request->wma_id)) ? $request->wma_id : 0;
			$epr_batch_id 					= (isset($request->batch_id) && !empty($request->batch_id)) ? $request->batch_id : "";
			$epr_batch_invoice_process_id 	= (isset($request->batch_invoice_process_id) && !empty($request->batch_invoice_process_id)) ? $request->batch_invoice_process_id : "";
			$po_documents 					= (isset($request->po_documents) && !empty($request->po_documents)) ? $request->po_documents : "";
			$po_amount 						= (isset($request->po_amount) && !empty($request->po_amount)) ? $request->po_amount : "";
			$credit_transfer 				= (isset($request->credit_transfer) && !empty($request->credit_transfer)) ? true : false;
			$service_type 					= ($credit_transfer)?PARA_CREDIT_TRANSFER_SERVICE_TYPE:PARA_EPR_SERVICE;
			$service_data 					= self::find($id);
			if(!$service_data) {
				$service_data 				= new self();
				$createdAt 					= date("Y-m-d H:i:s");
				$service_data->created_at	= $createdAt;
				$service_data->created_by	= 0;
				$service_data->updated_at	= $createdAt;
				$service_data->updated_by	= 0;
			} else {
				$updatedAt 					= date("Y-m-d H:i:s");
				$service_data->updated_at	= $updatedAt;
				$service_data->updated_by	= 0;
			}
			$service_data->mrf_id 						= $mrf_id;
			$service_data->invoice_date					= $invoice_date;
			$service_data->client_id 					= $client_id;
			$service_data->delivery_note				= $delivery_note;
			$service_data->remarks 						= $remarks;
			$service_data->terms_payment 				= $terms_payment;
			$service_data->supplier_ref 				= $supplier_ref;
			$service_data->buyer_no 					= $buyer_no;
			$service_data->dated 						= $dated;
			$service_data->dispatch_doc_no 				= $dispatch_doc_no;
			$service_data->delivery_note_date 			= $delivery_note_date;
			$service_data->dispatch_through				= $dispatch_through;
			$service_data->destination					= $destination;
			$service_data->company_id					= $ServiceMRF->company_id;
			$service_data->service_type					= $service_type;
			$service_data->epr_wma_id					= $epr_wma_id;
			$service_data->epr_batch_id					= $epr_batch_id;
			$service_data->epr_batch_invoice_process_id	= $epr_batch_invoice_process_id;
			if($service_data->save()) {
				$id = $service_data->id;
				WmServiceProductMapping::SaveServiceProductFromEPR($request,$id,$client_id,$mrf_id);
				if (!empty($po_documents)) {
					WmServiceDocuments::SaveAgreementCopy($po_documents,$id);
				}
				WmServicePOAmount::SavePoDetails($id,$buyer_no,$po_amount); //Added By Kalpak to store PO amount
			}
			return $id;
		}
	}

	/*
	Use 	: Send Service Invoice To EPR Team
	Author 	: Kalpak Prajapati
	Date 	: 02 Dec 2021
	*/
	public static function SendInvoiceToEPR()
	{
		$ServiceRows = self::select("id","company_id","epr_batch_id","epr_batch_invoice_process_id","serial_no","epr_wma_id","invoice_date","invoice_path","invoice_name")
							->where("epr_invoice_sent",0)
							->where("epr_batch_id",">",0)
							->where("epr_batch_invoice_process_id",">",0)
							->whereNotNull("invoice_path")
							->whereNotNull("invoice_name")
							->get();
		if (!empty($ServiceRows)) {
			foreach ($ServiceRows as $ServiceRow) {
				$data 		= self::GetById($ServiceRow->id);
				$Products 	= WmServiceProductMapping::GetServiceProduct($ServiceRow->id);
				$filepath	= public_path("/".$ServiceRow->invoice_path."/".$ServiceRow->invoice_name);
				if (file_exists($filepath) && !empty($ServiceRow->invoice_path) && !empty($ServiceRow->invoice_name)) {
					self::SendInvoicePDFToEPR($data,$filepath,$Products);
				}
			}
		}
	}

	/*
	Use 	: Call EPR Invoice API URL
	Author 	: KALPAK PRAJAPATI
	Date 	: 02 DEC,2021
	*/
	public static function SendInvoicePDFToEPR($Row,$FilePath,$Products,$wma_id=0,$batch_id=0,$batch_invoice_process_id=0)
	{
		$FILE_URL 		= url("/".$Row->invoice_path."/")."/".$Row->invoice_name;
		$_fields		= array("invoice_date"=>$Row->invoice_date,
								"invoice_no"=>$Row->serial_no,
								"batch_id"=>!empty($batch_id)?$batch_id:$Row->epr_batch_id,
								"wma_id"=>!empty($wma_id)?$wma_id:$Row->epr_wma_id,
								"batch_invoice_process_id"=>!empty($batch_invoice_process_id)?$batch_invoice_process_id:$Row->epr_batch_invoice_process_id,
								"product_list"=>json_encode($Products),
								"pdf_file_url"=>$FILE_URL);
		$client 		= new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
		$response 		= $client->request('POST',EPR_INVOICE_URL,['form_params'=>$_fields]);
		$response 		= $response->getBody()->getContents();
		$ApiResponse 	= json_decode($response,true);
		$SuccessOrFail 	= 0;
		if(!empty($ApiResponse))
		{
			$SuccessOrFail 		= (isset($ApiResponse['Data']) && $ApiResponse['Data'] > 0)?$ApiResponse['Data']:0;
			$EPR_INVOICE_SENT	= ($SuccessOrFail)?1:0;
			$R_Fields 			= [	"epr_invoice_sent" 			=> $EPR_INVOICE_SENT,
									"epr_invoice_sent_request" 	=> json_encode($_fields),
									"epr_invoice_sent_response" => json_encode($ApiResponse),
									"epr_invoice_sent_timestamp"=> date("Y-m-d H:i:s")];
			$data = self::where("id",$Row->id)->update($R_Fields);
		}
		return $SuccessOrFail;
	}

	/*
	Use 	: Send Existing Service Invoice To EPR Team
	Author 	: Kalpak Prajapati
	Date 	: 04 Jan 2022
	*/
	public static function SendExistingInvoiceToEPR($serial_no="",$Request="")
	{
		$ServiceRows 	= self::select("id","company_id","epr_batch_id","epr_batch_invoice_process_id","serial_no","epr_wma_id","invoice_date","invoice_path","invoice_name","invoice_path","invoice_name")
							->where("serial_no",$serial_no)
							->first();
		$_fields 		= "";
		if (!empty($ServiceRows)) {
			$data 		= self::GetById($ServiceRows->id);
			$Products 	= WmServiceProductMapping::GetServiceProduct($ServiceRows->id,$Request->wma_id,$Request->batch_id,$Request->batch_invoice_process_id);
			$filepath	= public_path("/".$ServiceRows->invoice_path."/".$ServiceRows->invoice_name);
			if (file_exists($filepath) && !empty($ServiceRows->invoice_path) && !empty($ServiceRows->invoice_name)) {
				self::SendInvoicePDFToEPR($ServiceRows,$filepath,$Products,$Request->wma_id,$Request->batch_id,$Request->batch_invoice_process_id);
				$_fields = $ServiceRows->id;
			}
		}
		return $_fields;
	}

	/*
	Use 	: upload signature invoice
	Author 	: hasmukhi patel
	Date 	: 24 Jan 2022
	*/
	public static function uploadInvoice($request,$service_id)
	{
		$service_update_id 	= 0;
		if ($request->hasFile("invoice_name")) {
			$invoice_name 	= $request->file('invoice_name');
			$path 			= PATH_SERVICE . '/' . PATH_SERVICE_INVOICE;
			$imageName  	= "media-".sha1($invoice_name->getClientoriginalName().time()). '.' . $invoice_name->getClientOriginalExtension();
			$uploadpath = (isset($data['path']) &&!empty($data['path'])) ? $data['path'] : "uploads";
			if(!is_dir(public_path()."/".$path)) {
					mkdir(public_path()."/".$path,0777,true);
			}
			$fullPath       = public_path()."/".$path;
			$invoice_name->move($path, $imageName);
			$ServiceData 	= self::where('id',$service_id)->first();
			if($ServiceData){
				$ServiceData->invoice_path = $path;
				$ServiceData->invoice_name = $imageName;
				$ServiceData->save();
				$service_update_id = $ServiceData->id;
			}
		}
		return $service_update_id;
	}

	/**
	* Function Name : SendPendingServiceInvoiceEmailToAccount
	* @param date $START_DATE
	* @param date $END_DATE
	* @author Kalpak Prajapati
	* @since 2022-02-22
	* @access public
	* @uses SendPendingServiceInvoiceEmailToAccount
	*/
	public static function SendPendingServiceInvoiceEmailToAccount($START_DATE,$END_DATE)
	{
		$START_DATE = $START_DATE." ".GLOBAL_START_TIME;
		$END_DATE 	= $END_DATE." ".GLOBAL_END_TIME;
		$ReportRows = self::select(\DB::raw("wm_client_master.client_name As Client_Name"),
									"parameter.para_value as Service_Type_Name",
									"wm_service_master.created_at")
						->LEFTJOIN("wm_client_master","wm_client_master.id",'=','wm_service_master.client_id')
						->LEFTJOIN("parameter","parameter.para_id",'=','wm_service_master.service_type')
						->whereIn("wm_service_master.approval_status",[REQUEST_PENDING])
						->whereBetWeen("wm_service_master.created_at",[$START_DATE,$END_DATE])
						->orderBy("wm_service_master.id")
						->get();
		if (!empty($ReportRows))
		{
			$ReportData = array();
			foreach($ReportRows as $ReportRow)
			{
				$ReportData[] 	= array("CLIENT_NAME"=>$ReportRow->Client_Name,
										"SERVICE_TYPE_NAME"=>$ReportRow->Service_Type_Name,
										"CREATED_AT"=>$ReportRow->created_at);
			}
			if (!empty($ReportData)) {
				$Attachments    = array();
				$FromEmail 		= array("Email"=>"reports@letsrecycle.co.in","Name"=>"Nepra Resource Management Private Limited");
				$ToEmail 		= array("meenal.modi@nepra.co.in","sakshi.rajput@nepra.co.in");
				$CCEmail 		= array("samir.jani@nepra.co.in");
				$Subject 		= "Service Pending Invoice Request From ".date("Y-m-d",strtotime($START_DATE))." TO ".date("Y-m-d",strtotime($END_DATE));
				$sendEmail      = Mail::send("email-template.pending_service_invoice_request",array("ReportData"=>$ReportData,"HeaderTitle"=>$Subject), function ($message) use ($ToEmail,$CCEmail,$FromEmail,$Subject,$Attachments) {
								$message->from($FromEmail['Email'], $FromEmail['Name']);
								$message->to($ToEmail);
								$message->to($CCEmail);
								$message->subject($Subject);
								if (!empty($Attachments)) {
									foreach($Attachments as $Attachment) {
										$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
									}
								}
							});
			}
		}
	}

	/**
	* Function Name : Tradex Service Invoice API
	*
	* @author Hardyesh Gupta
	* @since 06-10-2023
	* @access public
	* @uses Tradex Service Invoice Data Save In LR Service Table
	*/
	public static function TradexServiceInvoiceAPI($request)
	{
		$ExistingRow 					= 0;
		$responseArray 					= array();
		$client_master 					= array();
		$TradexRecordID 				= (isset($request->TradexRecordID) && !empty($request->TradexRecordID)) ? $request->TradexRecordID : 0;
		$LrServiceInvoiceId 			= (isset($request->LrServiceInvoiceId) && !empty($request->LrServiceInvoiceId)) ? $request->LrServiceInvoiceId : 0;
		$RequestNo 						= (isset($request->RequestNo) && !empty($request->RequestNo)) ? $request->RequestNo : "";
		$IsBuyer 						= (isset($request->IsBuyer) && (!empty($request->IsBuyer) && $request->IsBuyer == true)) ? 1 : 0;
		$vendorDetails 					= (isset($request->vendorDetails) && !empty($request->vendorDetails)) ? $request->vendorDetails : array();
		$city_name						= (isset($vendorDetails['VendorCityName']) && !empty($vendorDetails['VendorCityName'])) ? $vendorDetails['VendorCityName'] : "";
		$vendor_id						= (isset($vendorDetails['VendorID']) && !empty($vendorDetails['VendorID'])) ? $vendorDetails['VendorID'] : 0;
		$invoiceItemArray 				= (isset($request->invoiceItemArray) && !empty($request->invoiceItemArray)) ? $request->invoiceItemArray : array();
		$client_master['tradex_id'] 	= (isset($vendorDetails['VendorID']) && !empty($vendorDetails['VendorID'])) ? $vendorDetails['VendorID'] : "";
		$client_master['id'] 			= (isset($vendorDetails['LrClientId']) && !empty($vendorDetails['LrClientId'])) ? $vendorDetails['LrClientId'] : 0;
		$client_master['code'] 			= (isset($vendorDetails['LrClientCode']) && !empty($vendorDetails['LrClientCode'])) ? $vendorDetails['LrClientCode'] : " ";
		$client_master['net_suit_code'] = (isset($vendorDetails['VendorNetSuiteCode']) && !empty($vendorDetails['VendorNetSuiteCode'])) ? $vendorDetails['VendorNetSuiteCode'] : "";
		$client_master['client_name'] 	= (isset($vendorDetails['VendorCompanyName']) && !empty($vendorDetails['VendorCompanyName'])) ? $vendorDetails['VendorCompanyName'] : "";
		$client_master['email'] 		= (isset($vendorDetails['VendorEmail']) && !empty($vendorDetails['VendorEmail'])) ? $vendorDetails['VendorEmail'] : "";
		$client_master['address'] 		= (isset($vendorDetails['VendorAddress'] ) && !empty($vendorDetails['VendorAddress'] )) ? $vendorDetails['VendorAddress']  : "";
		$client_master['gstin_no'] 		= (isset($vendorDetails['VendorGSTNo']) && !empty($vendorDetails['VendorGSTNo'])) ? $vendorDetails['VendorGSTNo'] : "";
		$client_master['pan_no'] 		= (isset($vendorDetails['VendorPanCardNo']) && !empty($vendorDetails['VendorPanCardNo'] )) ? $vendorDetails['VendorPanCardNo']  : "";
		$client_master['pincode'] 		= (isset($vendorDetails['VendorPinCode']) && !empty($vendorDetails['VendorPinCode'])) ? $vendorDetails['VendorPinCode'] : "";
		$client_master['contact_person'] = (isset($vendorDetails['VendorName']) && !empty($vendorDetails['VendorName'])) ? $vendorDetails['VendorName'] : "";
		$client_master['mobile_no'] 	= (isset($vendorDetails['VendorMobileNo']) && !empty($vendorDetails['VendorMobileNo'])) ? $vendorDetails['VendorMobileNo'] : "";
		$client_master['gst_state_code']= (isset($vendorDetails['VendorGSTStateCode']) && !empty($vendorDetails['VendorGSTStateCode'])) ? $vendorDetails['VendorGSTStateCode'] : 0;
		$client_master['vendorDocList'] = (isset($vendorDetails['vendorDocumentList']) && !empty($vendorDetails['vendorDocumentList'])) ? $vendorDetails['vendorDocumentList'] : array();
		$client_master['company_id'] 	= 1;
		$client_master['introduced_by'] = "Self";
		$gst_state_code 				= $client_master['gst_state_code'];
		$client_master['city_id'] 		= 0;
		$companyId 						= $client_master['company_id'];
		$state_code 					= 0;
		$state 							= "";
		if(isset($gst_state_code) && !empty($gst_state_code)){
			$GstStateData 		= GSTStateCodes::where("display_state_code",$gst_state_code)->first();
			$StateMaster 		= StateMaster::where("gst_state_code_id",$GstStateData->id)->where("status",'A')->first();
			$state_code 		= $StateMaster->state_id;
			$state 	 			= $StateMaster->state_name;
		}
		if(!empty($city_name && !empty($state_code))){
			$CityData = LocationMaster::where("state_id",$state_code)->whereRaw("LOWER(REPLACE(city, ' ', '')) like ?",[strtolower(str_replace(' ', '', $city_name))])->first();
			if(!empty($CityData)){
				$client_master['city_id'] =  $CityData->location_id ;
			}else{
				$ref_city_id 	= DB::table('city_master')->insertGetId(['state_id'=>$state_code,'country_id'=> 1,'city_name' =>ucwords($city_name),'status' => 'A']);
				$InsertID 		= LocationMaster::insertGetId(['city' =>ucwords($city_name),'state'=> $state,'state_id'=>$state_code,'color_code' => '','ref_city_id'=>$ref_city_id,'status' => 'A']);
				$client_master['city_id'] = $InsertID;
			}
		}
		if(!empty($client_master['code']) && !empty($client_master['id'])){
			$ExistingRow = WmClientMaster::where("id",$client_master['id'])->where("code",$client_master['code'])->count();
		}else if(!empty($client_master['net_suit_code'])){
			$ExistingRow = WmClientMaster::where("net_suit_code",$client_master['net_suit_code'])->count();
		}
		if(($ExistingRow == 0)){
			$client_id 		= WmClientMaster::AddClient($client_master,$request);
			if($client_id > 0){
				$cityId 		= $client_master['city_id'];
				$vendorDocList 	= $client_master['vendorDocList'];
				foreach($vendorDocList as $docKey => $docVal){
					$DocumentType 	=  $docVal['DocumentType'];
					$DocumentURL 	=  $docVal['DocumentURL'];
					if(!empty($DocumentURL)){
						$filename 		=  pathinfo($DocumentURL,PATHINFO_BASENAME);
						$publicpath 	= public_path(PATH_IMAGE.'/');
						$partialPath    = PATH_COMPANY."/".$companyId.'/'.$client_master['city_id']."/".PATH_COMPANY_CLIENT."/".PATH_CLIENT_DOC."/".$client_id;
						if($DocumentType == "PanCard"){
							$MEDIARECORD = self::TradexfileSave($DocumentURL,$filename,$companyId,$cityId,$partialPath);
							$MEDIA_ID 	 = isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
							if (!empty($MEDIA_ID)) {
								WmClientMaster::where("id",$client_id)->update(["pan_doc_id"=>$MEDIA_ID]);
							}
						}else if($DocumentType == "GSTNo"){
							$MEDIARECORD = self::TradexfileSave($DocumentURL,$filename,$companyId,$cityId,$partialPath);
							$MEDIA_ID 	 = isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
							if (!empty($MEDIA_ID)) {
								WmClientMaster::where("id",$client_id)->update(["gst_doc_id"=>$MEDIA_ID]);
							}
						}else if($DocumentType == "CancelledCheque"){
							$MEDIARECORD = self::TradexfileSave($DocumentURL,$filename,$companyId,$cityId,$partialPath);
							$MEDIA_ID 	 = isset($MEDIARECORD->id)?$MEDIARECORD->id:0;
							if (!empty($MEDIA_ID)) {
								WmClientMaster::where("id",$client_id)->update(["cheque_doc_id"=>$MEDIA_ID]);
							}
						}
					}
				}
			}
		}
		$client_id 			= !empty($client_master['id']) ? $client_master['id'] : $client_id;
		$ClientMasterData 	= WmClientMaster::find($client_id);
		if(isset($ClientMasterData)){
			if(empty($ClientMasterData->net_suit_code)){
				$lastNetsuitCode 			= MasterCodes::getMasterCode(MASTER_CODE_CLIENT_NET_SUIT_CODE);

				if($lastNetsuitCode){
					$newNetsuitCreatedCode  = $lastNetsuitCode->code_value + 1;
					$prefix1 				= LeadingZero($newNetsuitCreatedCode);
					$client_net_suit_code 	= $lastNetsuitCode->prefix.$prefix1;
					MasterCodes::updateMasterCode(MASTER_CODE_CLIENT_NET_SUIT_CODE,$newNetsuitCreatedCode);
					$LrNetSuitCode 			= $client_net_suit_code;
					WmClientMaster::where('id',$client_id)->update(['company_id'=>$companyId,'net_suit_code'=>$LrNetSuitCode]);
				}
			}else{
				$LrNetSuitCode = $ClientMasterData->net_suit_code;
			}
			$shipping_address 	= $ClientMasterData->address;
			$city_id			= $ClientMasterData->city_id;
			$city 				= ucwords($city_name);
			$pincode 			= $ClientMasterData->pincode;
			$gst_no 			= $ClientMasterData->gstin_no;
			$consignee_name 	= $ClientMasterData->client_name;
			$string 			= str_replace(' ', '-', $shipping_address); 	// Replaces all spaces with hyphens.
			$NewString			= preg_replace("/[^a-zA-Z0-9]/", "", $string);	// Removes special chars.
			$base64 			= base64_encode($NewString);
			$ShopAddDataQry 	= ShippingAddressMaster::where('client_id',$client_id)->where('encoded_address',$base64);
			$ShopAddDataCount 	= $ShopAddDataQry->count();
			if($ShopAddDataCount == 0) {
				$ship_id = ShippingAddressMaster::insertGetId(['client_id' =>$client_id,'billing_address'=> 1,'consignee_name' => $consignee_name,'shipping_address'=> ucwords(strtolower($shipping_address)),'encoded_address'=> $base64,'city_id'=> $city_id,'city'=>ucwords(strtolower($city)),'state' => ucwords(strtolower($state)),'state_code'=>$gst_state_code,'pincode' => $pincode,'gst_no' => strtoupper(strtolower($gst_no)),'company_id' => $companyId,'created_at' => date('Y-m-d h:i:s')]);
			} else {
				$ShopAddData 		= $ShopAddDataQry->first();
				$billing_address_id = $ShopAddData->id;
				$ship_id 			= $ShopAddData->id;
			}
		}
		########## Insert Invoice Detail in Service Master ###########
		$service_type 			= PARA_OTHER_SERVICE;
		$mrf_id 				= TRADEX_MRF_ID;
		$invoice_date 			= date('Y-m-d');
		$client_id 				= $client_id;
		$remarks 				= "";
		$delivery_note 			= "";
		$terms_payment 			= "";
		$supplier_ref 			= "";
		$buyer_no 				= $RequestNo;
		$dated 					= date('Y-m-d');
		$dispatch_doc_no 		= 0;
		$delivery_note_date 	= "";
		$dispatch_through 		= "";
		$destination 			= "";
		$billing_address_id 	= $ship_id;
		$shipping_address_id  	= $ship_id ;
		$SqlQuery 				= WmServiceMaster::where('tradex_record_id',$TradexRecordID)->where('is_tradex',1)->where('is_buyer_tradex',$IsBuyer)->where('id',$LrServiceInvoiceId);
		$ServiceRowCount 		= $SqlQuery->count();
		$ServiceMasterData 		= $SqlQuery->first();
		if($ServiceRowCount == 0)
		{
			$ServiceInvoice['is_slab_invoice']		= 0;
			$ServiceInvoice['id'] 					= 0;
			$ServiceInvoice['service_type'] 		= $service_type;
			$ServiceInvoice['mrf_id'] 				= $mrf_id;
			$ServiceInvoice['invoice_date'] 		= $invoice_date;
			$ServiceInvoice['client_id'] 			= $client_id;
			$ServiceInvoice['delivery_note'] 		= $delivery_note;
			$ServiceInvoice['remarks'] 				= $remarks;
			$ServiceInvoice['terms_payment'] 		= $terms_payment;
			$ServiceInvoice['supplier_ref'] 		= $supplier_ref;
			$ServiceInvoice['buyer_no'] 			= $buyer_no;
			$ServiceInvoice['dated'] 				= $dated;
			$ServiceInvoice['dispatch_doc_no'] 		= $dispatch_doc_no;
			$ServiceInvoice['destination'] 			= $destination;
			$ServiceInvoice['company_id'] 			= $companyId;
			$ServiceInvoice['is_service_invoice'] 	= 1;
			$ServiceInvoice['billing_address_id'] 	= $billing_address_id;
			$ServiceInvoice['shipping_address_id'] 	= $shipping_address_id;
			$ServiceInvoice['is_tradex'] 			= 1;
			$ServiceInvoice['is_buyer_tradex'] 		= $IsBuyer;
			$ServiceInvoice['tradex_record_id'] 	= $TradexRecordID;
			$ServiceInvoice['product_list'] 		= array();
			$ProductArray 							= array();
			$TradexInvoiceItemArray  				= $invoiceItemArray;
			if(!empty($TradexInvoiceItemArray))
			{
				foreach ($TradexInvoiceItemArray as $key => $value)
				{
					$ServiceTradexId 				=  $value['TradexProductID']  ;
					if(!empty($value['TradexProductID'])) {
						$WmServiceProductData 		= WmServiceProductMaster::where('tradex_product_id',$ServiceTradexId)->first();
					} else {
						$WmServiceProductData 		= WmServiceProductMaster::where('id',TRADEX_SERVICE_CHARGE_PRODUCT_ID)->first();
					}
					$Product_array['product'] 		= (!empty($WmServiceProductData)) ? $WmServiceProductData->product : $value['DescriptionOfGoods'];
					$Product_array['product_id'] 	= (!empty($WmServiceProductData)) ? $WmServiceProductData->id : 0;
					$Product_array['description'] 	= $value['DescriptionOfGoods'];
					$Product_array['hsn_code'] 		= $value['HSNSACCode'];
					$Product_array['quantity'] 		= $value['Quantity'];
					$Product_array['rate'] 			= $value['Rate'];
					$Product_array['uom'] 			= (!empty($WmServiceProductData)) ? $WmServiceProductData->uom : 8003;
					$Product_array['cgst'] 			= $value['CGSTPercentage'];
					$Product_array['sgst'] 			= $value['SGSTPercentage'];
					$Product_array['igst'] 			= $value['IGSTPercentage'];
					$Product_array['gst_amt'] 		= 0;
					$Product_array['net_amt'] 		= 0;
					$Product_array['gross_amt'] 	= 0;
					array_push($ProductArray,$Product_array);
				}
			}
			$ProductArrays 					= array_values($ProductArray);
			$ServiceInvoice['product_list'] = $ProductArrays;
			$ServiceInvoiceRequet 			= json_encode($ServiceInvoice,JSON_FORCE_OBJECT);
			$LrServiceInvoiceId 			= self::SaveService($ServiceInvoiceRequet);
			if($LrServiceInvoiceId > 0)	{
				self::where('id',$LrServiceInvoiceId)->update(['company_id'=>$companyId]);
			}
		} else {
			$LrServiceInvoiceId = $ServiceMasterData->id;
		}
		if(!empty($ClientMasterData) && !empty($LrServiceInvoiceId)){
			$LrClientId 	= $ClientMasterData->id;
			$LrClientCode 	= $ClientMasterData->code;
			$responseArray 	= array("VendorID" 			=> $vendor_id,
									"TradexRecordID" 	=> $TradexRecordID,
									"LrClientId" 		=> $LrClientId,
									"LrClientCode" 		=> $LrClientCode,
									"LrNetSuitCode"     => $LrNetSuitCode,
									"LrServiceInvoiceId"=> $LrServiceInvoiceId);
			$responseAPI 	= json_encode($responseArray);
			$request 		= json_encode($request->all());
			$InsertLogID 	= \DB::table('tradex_service_invoice_request_response_api')->insertGetId(['tradex_record_id'=> $TradexRecordID,'service_id'=> $LrServiceInvoiceId,'client_id'=> $LrClientId,'client_net_suit_code'=> $LrNetSuitCode,'is_buyer'=> $IsBuyer,'input_parameter' => $request,'api_flag'=> 0,'response_parameter'=>$responseAPI,'created_by' => 0,'created_at'=>date('Y-m-d H:i:s')]);
			return $responseArray;
		}
		return $responseArray;
	}

	/* Function Name : Client Document Saved Send By Tradex
	* @author Hardyesh Gupta
	* @since 11-10-2023
	* @access public
	* @uses File Upload
	*/
	public static function TradexfileSave($DocumentURL="",$filename="",$companyId,$cityId,$partialPath,$moduleName="")
	{
		$resultData = "";
		$publicpath 	= public_path(PATH_IMAGE.'/');
		$DocSavePath 	= $publicpath.$partialPath;
		$SaveFileName 	= $DocSavePath."/".$filename;
		$GetFilename 	= file_get_contents($DocumentURL);
		if(!is_dir($DocSavePath)) {
			mkdir($DocSavePath,0777,true);
		}
		file_put_contents($SaveFileName,$GetFilename);
		$mediaMaster = new MediaMaster();
		$mediaMaster->company_id 	= $companyId;
		$mediaMaster->city_id 	    = $cityId;
		$mediaMaster->original_name = $filename;
		$mediaMaster->server_name   = $filename;
		$mediaMaster->image_path    = $partialPath;
		if($mediaMaster->save()) {
			return $mediaMaster;
		}
		return $resultData;
	}

	/* Function Name : Tradex Service Invoice API
	*
	* @author Hardyesh Gupta
	* @since 06-10-2023
	* @access public
	* @uses Tradex Service Invoice Data Save In LR Service Table
	*/
	public static function TradexServiceEInvoiceGenerateAPI($request="")
	{
		$res 				= array();
		$SqlQuery 			= WmServiceMaster::where('is_tradex',1)->where('ack_no','!=','')->where('approval_status',1)->whereIn('tradex_process_status',['0','3']);
		$ServiceRowCount 	= $SqlQuery->count();
		$ServiceMasterData 	= $SqlQuery->get()->toArray();
		if($ServiceRowCount > 0)
		{
			foreach($ServiceMasterData as $tradexRow)
			{
				$id 			= $tradexRow['id'];
				$name 			= "service_invoice_".$id;
				$partialPath 	= PATH_SERVICE."/".$id;
				$filename 		= $name.".pdf";
				$fullPath 		= public_path(PATH_IMAGE.'/').$partialPath;
				$url 			= url('/')."/".PATH_IMAGE.'/'.$partialPath."/".$filename;
				$invoice_path 	= (file_exists(public_path("/")."/".PATH_IMAGE.'/'.$partialPath."/".$filename)) ? $url : "";
				if($invoice_path !="")
				{
					$LrServiceInvoiceId = $tradexRow['id'];
					$TradexRecordID 	= $tradexRow['tradex_record_id'];
					$IsBuyer 			= ($tradexRow['is_buyer_tradex'] == 1) ? 1 :0 ;
					$LrClientId 		= $tradexRow['client_id'];
					$InvoiceDate  		= $tradexRow['invoice_date'];
					$InvoiceNo  		= $tradexRow['serial_no'];
					$ClientMasterData 	= WmClientMaster::find($LrClientId);
					$LrNetSuitCode      = $ClientMasterData->net_suit_code;
					WmServiceMaster::where('id',$LrServiceInvoiceId)->where('is_tradex',1)->update(['tradex_process_status'=>1]);
					$dataArray 			= ['TradexRecordID' => $TradexRecordID,'LrServiceInvoiceId'=>$LrServiceInvoiceId,"LrClientId" => $LrClientId,"IsBuyer" => $IsBuyer,"TradexInvoiceUrl" => $invoice_path,"TradexInvoiceFileName" => $filename,"InvoiceDate" => $InvoiceDate,"InvoiceNo" => $InvoiceNo ];
					$InsertLogID 	= \DB::table('tradex_service_invoice_request_response_api')->insertGetId(['tradex_record_id'=> $TradexRecordID,'service_id'=> $LrServiceInvoiceId,'client_id'=> $LrClientId,'client_net_suit_code'=> $LrNetSuitCode,'is_buyer'=> $IsBuyer,'input_parameter' => json_encode($dataArray),'api_flag'=> 1,'created_at'=>date('Y-m-d H:i:s')]);
					$dataArrayJson  	= json_encode($dataArray);
					$ch 				= curl_init();
					$apiURL 			= PROJECT_TRADEX_SERVICE_INVOICE_GENERATE_URL;
					$curl 				= curl_init($apiURL);
					curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataArray));
					$responseAPI 		= curl_exec($curl);
					$response 			= json_decode($responseAPI);
					$responseData   	= $response;
					$InsertLogID 		= \DB::table('tradex_service_invoice_request_response_api')->where("id",$InsertLogID)->update(['response_parameter'=>$responseAPI]);
					if(isset($response))
					{
						if($response->StatusCode == 200) {
							WmServiceMaster::where('id',$LrServiceInvoiceId)->where('is_tradex',1)->update(['tradex_process_status'=>2]);
						} else {
							WmServiceMaster::where('id',$LrServiceInvoiceId)->where('is_tradex',1)->update(['tradex_process_status'=>3]);
						}
						$createdby_userid  		= (\Auth::check()) ? Auth()->user()->adminuserid :  0;
						$res['code'] 			= $response->StatusCode;
						$res['status'] 			= $response->Status;
						$res['data'] 			= $response->Data;
						$res['message'] 		= $response->Message;
					}
				}
			}
		}
		return $res;
	}

	/*
	Use 	: Service invoice sent to the client
	Author 	: Hardyesh Gupta
	Date 	: 22 Nov 2023
	*/
	public static function StoreServiceInvoiceEmailSentLog($id)
	{
		$EXITS = \DB::table("wm_service_invoice_email_sent_log")->where("service_id",$id)->count();
		if($EXITS == 0) {
			\DB::table('wm_service_invoice_email_sent_log')->insert(array(	"service_id" => $id,
																			"created_at" => date("Y-m-d H:i:s"),
																			"updated_at" => date("Y-m-d H:i:s")));
		}
	}
	/*
	Use 	: Service invoice sent to the client
	Author 	: Hardyesh Gupta
	Date 	: 22 Nov 2023
	*/
	public static function SendServiceInvoiceByEmail()
	{
		$SqlQuery 	= DB::table("wm_service_invoice_email_sent_log")->where('sent_status',0)->get()->toArray();
		if(!empty($SqlQuery))
		{
			foreach($SqlQuery as $key => $value)
			{
				$InvoiceDetail 	= WmServiceMaster::where("id",$value->service_id)->where("approval_status",1)->whereIn("service_type",array(1043002,1043003,1043005))->first();
				if($InvoiceDetail)
				{
					$invoice_month					= date("F", strtotime($InvoiceDetail->invoice_date));
					$invoice_year 					= date("Y", strtotime($InvoiceDetail->invoice_date));
					$ClientData						= WmClientMaster::where('id',$InvoiceDetail->client_id)->first();
					$name 							= "service_invoice_".$InvoiceDetail->id;
					$partialPath 					= PATH_SERVICE."/".$InvoiceDetail->id;
					$fullPath 						= public_path(PATH_IMAGE.'/').$partialPath;
					$url 							= url('/')."/".PATH_IMAGE.'/'.$partialPath."/".$name.".pdf";
					$Attachments 					= (file_exists($fullPath."/".$name.".pdf"))?array($fullPath."/".$name.".pdf"):array();
					if (empty($Attachments)) continue; //Do not sent email as attachment is not yet generated
					$EmailContent					= array();
					$EmailContent['ROWS']			= $InvoiceDetail;
					$EmailContent['Title']			= "Invoice Detail";
					$EmailContent['InvoiceYear']	= $invoice_year;
					$EmailContent['InvoiceMonth']	= $invoice_month;
					$ToEmail        				= (isset($ClientData->email) && !empty($ClientData->email))?explode(",",$ClientData->email):"";
					if(!empty($ToEmail))
					{
						$FromEmail	= array ("Name" => EMAIL_FROM_NAME,"Email" => EMAIL_FROM_ID);
						$Subject  	= EMAIL_FROM_NAME." - Invoice No - ".$InvoiceDetail->serial_no;
						$sendEmail	= Mail::send("email-template.ServiceInvoiceEmailSend",$EmailContent,function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
							$message->from($FromEmail['Email'], $FromEmail['Name']);
							$message->to($ToEmail);
							$message->bcc(["kalpak@nepra.co.in","sejal.banker@nepra.co.in","sakshi.rajput@nepra.co.in"]);
							$message->subject($Subject);
							if (!empty($Attachments)) {
								foreach($Attachments as $Attachment) {
									$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
								}
							}
						});
						DB::table("wm_service_invoice_email_sent_log")->where('service_id',$InvoiceDetail->id)->update(['sent_status'=>1,"email"=>json_encode($ToEmail)]);
					}
				} else {
					DB::table("wm_service_invoice_email_sent_log")->where('service_id',$value->service_id)->update(['sent_status'=>1,"email"=>""]);
				}
			}
		}
	}
}