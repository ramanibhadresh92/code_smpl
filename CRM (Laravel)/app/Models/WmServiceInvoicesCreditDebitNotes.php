<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmServiceInvoicesCreditDebitNotesDetails;
use App\Models\AdminUser;
use App\Models\WmDispatch;
use App\Models\WmDepartment;
use App\Models\WmSalesMaster;
use App\Models\WmProductMaster;
use App\Models\WmClientMaster;
use App\Models\MasterCodes;
use App\Models\WmServiceMaster;
use App\Models\WmServiceProductMaster;
use App\Models\WmServiceProductMapping;
use App\Models\MediaMaster;
use App\Models\WmInvoicesCreditDebitNotesMasterCodes;
use App\Models\TransactionMasterCodesMrfWise;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Mail;
class WmServiceInvoicesCreditDebitNotes extends Model implements Auditable
{
    protected 	$table 		=	'wm_service_invoices_credit_debit_notes';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	public function CreditDetails(){
		return $this->hasMany(WmServiceInvoicesCreditDebitNotesDetails::class,"cd_notes_id","id");
	}

	public function department(){
		return $this->belongsTo(WmDepartment::class,"mrf_id","id");
	}

	public function crn_document(){
		return $this->belongsTo(MediaMaster::class,"crn_document_id");
	}

	/*
	use 	: Generate credit debit note
	Author 	: Hasmukhi Patel
	Date 	: 09 June,2021
	*/
	public static function GenerateCreditDebitNotes($request)
	{
		$ID = 0;
		/* "CHANGE IN" PARAMETER IS FOR CREDIT DEBIT NOTE IN QUANTITY OR RATE */
		$CODE 				= "";
		$SERIAL_NO 			= "";
		$DispTbl 			= new WmDispatch();
		$serviceInvoiceTbl 	= new WmServiceMaster();
		$inv 				= $serviceInvoiceTbl->getTable();
		$createdBy  		= Auth()->user()->adminuserid;
		$CompanyID  		= Auth()->user()->company_id;
		$updatedBy  		= Auth()->user()->adminuserid;
		$ServiceID 			= (isset($request['service_id']) 	&& !empty($request['service_id'])) 	?  $request['service_id'] 	: 0;
		$Save 				= new self();
		$Save->created_by  	= $createdBy;
		$Save->updated_by  	= $updatedBy;
		$notes_type 		= (isset($request['notes_type']) && !empty($request['notes_type']))  ?  $request['notes_type'] : 0;
		$NOTE_CODE 			= (isset($request['notes_type']) && !empty($request['notes_type']))  ?  DEBIT_NOTE : CREDIT_NOTE;
		$MRF_ID 			= (isset($request['mrf_id']) 	 && !empty($request['mrf_id'])) 	?  $request['mrf_id'] 	: 0;
		$CLEINT_ID 			= (isset($request['client_id']) 	 && !empty($request['client_id'])) 	?  $request['client_id'] 	: 0;
		$REASON 			= (isset($request['reason']) 	&& !empty($request['reason'])) ? $request['reason']	: "";
		$BILL_MRF_ID 		= 0;
		$Save->serial_no 	= NULL;
		$Save->transaction_type = (isset($request['transaction_type']) && !empty($request['transaction_type'])) ? $request['transaction_type']	: 0;
		$Save->invoice_no  	= (isset($request['invoice_no']) 	&& !empty($request['invoice_no'])) 	?  $request['invoice_no'] 	: "";
		$Save->service_id  	= $ServiceID;
		$Save->change_date  = date("Y-m-d");
		$Save->notes_type  	= $notes_type;
		$Save->mrf_id  		= $MRF_ID;
		$Save->client_id	= $CLEINT_ID;
		$Save->remarks  	= (isset($request['remarks']) 	 && !empty($request['remarks'])) 	?  $request['remarks'] 	: "";
		$Save->company_id  	= $CompanyID;
		$Save->reason 		= $REASON;
		######### IMAGE UPLOAD ##########
		if(isset($_FILES["crn_document"]["tmp_name"])) {
            $fileName 		= $_FILES["crn_document"]["name"];
            $partialPath 	= PATH_SERVICE.'/'.PATH_CREDIT_NOTE;
          	$imageFileType 	= strtolower(pathinfo($_FILES["crn_document"]["name"],PATHINFO_EXTENSION));
			if(!is_dir(public_path(PATH_IMAGE.'/').$partialPath)) {
                mkdir(public_path(PATH_IMAGE.'/').$partialPath,0777,true);
            }
           	$orignalImg     = "CRN_".time().'.'.$imageFileType;
           	$fullPath 		= public_path(PATH_IMAGE.'/').$partialPath."/".$orignalImg;
           	move_uploaded_file($_FILES["crn_document"]["tmp_name"],$fullPath);
            $mediaMaster = new MediaMaster();
            $mediaMaster->company_id 	= $CompanyID;
            $mediaMaster->city_id 	    = Auth()->user()->city;
            $mediaMaster->original_name = $orignalImg;
            $mediaMaster->server_name   = $orignalImg;
            $mediaMaster->image_path    = $partialPath;
            if($mediaMaster->save()){
            	$Save->crn_document_id  = $mediaMaster->id;
            }
		}
		######### IMAGE UPLOAD ##########
		if($Save->save()){
			######### UPDATE LAST DIGIT IN MASTER CODES #####################
			// WmInvoicesCreditDebitNotesMasterCodes::UpdateLastCode($MASTER_CODE_NO,$notes_type,$GST_STATE_CODE);
			// TransactionMasterCodesMrfWise::UpdateTrnCode($BILL_MRF_ID,$NOTE_CODE,$CODE);
			######### UPDATE LAST DIGIT IN MASTER CODES #####################
			$FROM_SAME_STATE 		= true;
			$SERVICE_DATA 			= WmServiceMaster::find($ServiceID);
			if($SERVICE_DATA){
				$MRF_GST 			= WmDepartment::where("id",$SERVICE_DATA->mrf_id)->value("gst_state_code_id");
				$MRF_GST_CODE 		= GSTStateCodes::where("id",$MRF_GST)->value("display_state_code");
				$CLIENT_GST 		= WmClientMaster::where("id",$SERVICE_DATA->client_id)->value("gst_state_code");
				$CLIENT_GST_CODE 	= GSTStateCodes::where("id",$CLIENT_GST)->value("display_state_code");
				$FROM_SAME_STATE 	= ($CLIENT_GST_CODE == $MRF_GST_CODE) ?  true : false;
			}
			$ID 			= $Save->id;
			$ProductList  	= (isset($request['product']) 		&& !empty($request['product'])) 	?  json_decode($request['product'],true) 	: "";
			if(!empty($ProductList)){
				foreach($ProductList as $Raw){
					$service_product_mapping_id = (isset($Raw['service_product_mapping_id']) && !empty($Raw['service_product_mapping_id'])) ?  $Raw['service_product_mapping_id'] 	: 0;
					$CHANGE_IN 			= (isset($Raw['change_in']) && !empty($Raw['change_in'])) ?  $Raw['change_in'] 	: 0;
					if(!empty($CHANGE_IN)){
						$cgst_amt 			= 0;
						$sgst_amt 			= 0;
						$igst_amt 			= 0;
						$PRODUCT_ID 		= (isset($Raw['product_id']) && !empty($Raw['product_id']))? $Raw['product_id'] : 0;
						$productData 		= (!empty($PRODUCT_ID) ? WmServiceProductMapping::where('product_id',$PRODUCT_ID)
							->where("service_id",$ServiceID)->where("id",$service_product_mapping_id)->first() : "");
						$CGST_RATE 			= (isset($productData->cgst) && !empty($productData->cgst) ? $productData->cgst : 0);
						$SGST_RATE 			= (isset($productData->sgst) && !empty($productData->sgst) ? $productData->sgst : 0);
						$IGST_RATE 			= (isset($productData->igst) && !empty($productData->igst) ? $productData->igst : 0);
						$Qty 				= (isset($Raw['quantity']) 	&& !empty($Raw['quantity']))  ?  $Raw['quantity'] 	: 0;
						$Rate 				= (isset($Raw['rate']) 	&& !empty($Raw['rate'])) ?  $Raw['rate'] : 0;
						$ReviseQty 			= (isset($Raw['revised_quantity']) && !empty($Raw['revised_quantity'])) ?  $Raw['revised_quantity'] : 0;
						$ReviseRate 		= (isset($Raw['revised_rate']) 	&& !empty($Raw['revised_rate'])) ?  $Raw['revised_rate'] : 0;
						$GstAmount 			= (isset($productData->gst_amt) && !empty($productData->gst_amt) ? $productData->gst_amt : 0);
						$NetAmount 			= (isset($productData->net_amt) && !empty($productData->net_amt) ? $productData->net_amt : 0);
						$GrossAmount 		= (isset($productData->gross_amt) && !empty($productData->gross_amt) ? $productData->gross_amt : 0);
						$NewQty 			= (isset($Raw['revised_quantity_value']) && !empty($Raw['revised_quantity_value'])) ?  $Raw['revised_quantity_value'] : 0;
						$NewRate 			= (isset($Raw['revised_rate_value']) && !empty($Raw['revised_rate_value'])) ?  $Raw['revised_rate_value'] : 0;
						$InwardStock 		= (isset($Raw['inward_stock']) && !empty($Raw['inward_stock'])) ?  $Raw['inward_stock'] : 0;
						$NewRate 			= ($notes_type == 1 && ($CHANGE_IN == 1 || $CHANGE_IN == 3)) ?  $Rate + $ReviseRate : $NewRate;
						$NewQty 			= ($notes_type == 1 && ($CHANGE_IN == 2 || $CHANGE_IN == 3)) ?  $Qty + $ReviseQty : $NewQty;

						####### IF ONLY RATE CHANGE THEN CALCULATE WITH ORIGINAL QTY ##########
						$RevisedGrossAmount = 0;
						$SUM_GST_PERCENT 	= 0;
						$RevisedGstAmt 		= 0;
						if($CHANGE_IN ==  1){
							$RevisedGrossAmount 	=  _FormatNumberV2($ReviseRate * $Qty);
						}elseif($CHANGE_IN ==  2){
							$RevisedGrossAmount 	=  _FormatNumberV2($ReviseQty * $Rate);
						}elseif($CHANGE_IN ==  3){
							$RevisedGrossAmount 	=  _FormatNumberV2($ReviseRate * $ReviseQty);
						}
						############ REVISED INVOICE CALCULATION #################
						if($CGST_RATE > 0 && $SGST_RATE > 0) {
							$cgst_amt 			= ($CGST_RATE > 0) ? (($RevisedGrossAmount / 100) * $CGST_RATE):0;
							$sgst_amt 			= ($SGST_RATE > 0) ? (($RevisedGrossAmount / 100) * $SGST_RATE):0;
							$RevisedGstAmt 		= $cgst_amt + $sgst_amt;
							$FROM_SAME_STATE 	= 1;
						}else{
							$igst_amt 			= ($IGST_RATE > 0) ? (( $RevisedGrossAmount / 100) * $IGST_RATE):0;
							$RevisedGstAmt 		= $igst_amt;
							$FROM_SAME_STATE 	= 0;
						}
						$RevisedNetAmount 				= $RevisedGrossAmount + $RevisedGstAmt;
						$INSERT 						= new WmServiceInvoicesCreditDebitNotesDetails();
						$INSERT->cd_notes_id 			= $ID;
						$INSERT->change_in 				= $CHANGE_IN;
						$INSERT->service_product_mapping_id = (isset($Raw['service_product_mapping_id']) && !empty($Raw['service_product_mapping_id'])) ? $Raw['service_product_mapping_id'] : 0;
						$INSERT->cgst_rate 				= $CGST_RATE;
						$INSERT->sgst_rate 				= $SGST_RATE;
						$INSERT->igst_rate 				= $IGST_RATE;
						$INSERT->product_id 			= $PRODUCT_ID;
						$INSERT->quantity 				= $Qty;
						$INSERT->revised_quantity 		= $ReviseQty;
						$INSERT->revised_rate 			= $ReviseRate;
						$INSERT->new_quantity 			= $NewQty;
						$INSERT->new_rate 				= $NewRate;
						$INSERT->rate 					= $Rate;
						$INSERT->gst_amount 			= $GstAmount;
						$INSERT->net_amount 			= $NetAmount;
						$INSERT->gross_amount 			= $GrossAmount;
						$INSERT->inward_stock 			= $InwardStock;
						$INSERT->is_from_same_state 	= $FROM_SAME_STATE;
						$INSERT->revised_gst_amount 	= _FormatNumberV2($RevisedGstAmt);
						$INSERT->revised_gross_amount	= _FormatNumberV2($RevisedGrossAmount);
						$INSERT->revised_net_amount		= _FormatNumberV2($RevisedNetAmount);
						$INSERT->created_by 			= $createdBy;
						$INSERT->updated_by 			= $updatedBy;
						$INSERT->save();
					}
				}
			}
			LR_Modules_Log_CompanyUserActionLog($request,$ID);
		}
		return $ID;
	}

	/*
	use 	: List credit notes
	Author 	: Hasmukhi Patel
	Date 	: 09 June,2021
	*/
	public static function ListCreditNotes($request){
		$self 					= (new static)->getTable();
		$AdminUser  			= new AdminUser();
		$Dispatch  				= new WmDispatch();
		$Department  			= new WmDepartment();
		$SalesMaster 			= new WmSalesMaster();
		$Product 				= new WmServiceProductMaster();
		$Client 				= new WmClientMaster();
		$Details 				= new WmServiceInvoicesCreditDebitNotesDetails();
		$DetailsTbl 			= $Details->getTable();
		$CMT 					= $Client->getTable();
		$Today          		= date('Y-m-d');
		$sortBy         		= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$self.id";
		$sortOrder      		= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  		= !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     		= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId         		= UserBaseLocationMapping::GetBaseLocationCityListByUser(Auth()->user()->adminuserid);
		$data = self::with(['crn_document'])->select("$self.*",
				\DB::raw("(CASE WHEN $self.notes_type = 0 THEN 'Credit'
								WHEN $self.notes_type = 1 THEN 'Debit'
						END ) AS note_type_name"),
				\DB::raw("(CASE WHEN $self.status = 0 THEN 'Pending'
								WHEN $self.status = 1 THEN 'Approved'
								WHEN $self.status = 2 THEN 'Rejected'
					END ) AS status_name"),
					\DB::raw("DEPT.department_name"),
					\DB::raw("$CMT.gstin_no as client_gst_in"),
					\DB::raw("$CMT.client_name as client_name"),
					\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
					\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
					\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) as approved_by_name")
				)
		->leftjoin($Department->getTable()." as DEPT","$self.mrf_id","=","DEPT.id")
		->leftjoin($CMT,"$CMT.id","=","$self.client_id")
		->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid")
		->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid")
		->leftjoin($AdminUser->getTable()." as U3","$self.approved_by","=","U3.adminuserid");

		if($request->has('params.serial_no') && !empty($request->input('params.serial_no')))
		{
			$data->where("$self.serial_no","like","%".$request->input('params.serial_no')."%");
		}
		if($request->has('params.client_id') && !empty($request->input('params.client_id')))
		{
			$data->where("$self.client_id",$request->input('params.client_id'));
		}
		if($request->has('params.invoice_no') && !empty($request->input('params.invoice_no')))
		{
			$data->where("$self.invoice_no","like","%".$request->input('params.invoice_no')."%");
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id')))
		{
			$data->where("$self.mrf_id",$request->input('params.mrf_id'));
		}
		if($request->has('params.product_id') && !empty($request->input('params.product_id')))
		{
			$data->where("DETAILS.product_id",$request->input('params.product_id'));
		}

		if($request->has('params.status'))
		{
			if($request->input('params.status') == "0"){
				$data->where("$self.status",$request->input('params.status'));
			}elseif($request->input('params.status') == "1" || $request->input('params.status') == "2"){
				$data->where("$self.status",$request->input('params.status'));
			}
		}
		if($request->has('params.notes_type'))
		{
			if($request->input('params.notes_type') == "0"){
				$data->where("$self.notes_type",$request->input('params.notes_type'));
			}elseif($request->input('params.notes_type') == "1"){
				$data->where("$self.notes_type",$request->input('params.notes_type'));
			}
		}
		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
		{
			$data->whereBetween("$self.change_date",array(date("Y-m-d", strtotime($request->input('params.startDate'))),date("Y-m-d",strtotime($request->input('params.endDate')))));
		}else if(!empty($request->input('params.startDate'))){
		   $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		   $data->whereBetween("$self.change_date",array($datefrom,$datefrom));
		}else if(!empty($request->input('params.endDate'))){
		   $data->whereBetween("$self.change_date",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}

		$data->where(function($query) use($cityId){
		    $query->whereIn("DEPT.location_id",$cityId);
		});
		$data->where("$self.company_id",Auth()->user()->company_id);
		// LiveServices::toSqlWithBinding($data);
		$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);
		if(!empty($result)){
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0){
				foreach($toArray['result'] as $key => $value){
					$toArray['result'][$key]['crn_doc_url'] =  (!empty($value['crn_document'])) ? $value['crn_document']['original_name'] : "";
					$toArray['result'][$key]['invoice_url'] =  ($value['status'] == 1) ? url("/credit-note-service-invoice")."/".passencrypt($value['id'])."/".passencrypt($value['service_id']) : "";
					$toArray['result'][$key]['show_approval_menu'] 	=  ($value['status'] == 0) ? true : false;
					$DetailsData = WmServiceInvoicesCreditDebitNotesDetails::select(
						\DB::raw("(CASE WHEN $DetailsTbl.change_in = 0 THEN '-'
									WHEN $DetailsTbl.change_in = 1 THEN 'Rate'
									WHEN $DetailsTbl.change_in = 2 THEN 'Quantity'
									WHEN $DetailsTbl.change_in = 3 THEN 'Rate & Quantity'
							END ) AS change_in_name"),
						\DB::raw("$DetailsTbl.product_id"),
						\DB::raw("$DetailsTbl.rate"),
						\DB::raw("$DetailsTbl.revised_rate"),
						\DB::raw("$DetailsTbl.quantity"),
						\DB::raw("$DetailsTbl.revised_quantity"),
						\DB::raw("$DetailsTbl.cgst_rate"),
						\DB::raw("$DetailsTbl.sgst_rate"),
						\DB::raw("$DetailsTbl.igst_rate"),
						\DB::raw("$DetailsTbl.gst_amount"),
						\DB::raw("$DetailsTbl.net_amount"),
						\DB::raw("$DetailsTbl.revised_gst_amount"),
						\DB::raw("$DetailsTbl.revised_net_amount"),
						\DB::raw("$DetailsTbl.revised_gross_amount"),
						\DB::raw("PRODUCT.product as product_name"),
						\DB::raw("PRODUCT.hsn_code")
					)
					->leftjoin($Product->getTable()." as PRODUCT","$DetailsTbl.product_id","=","PRODUCT.id")
					->where("$DetailsTbl.cd_notes_id",$value['id'])
					->get()->toArray();
					$toArray['result'][$key]['credit_note_details'] =  $DetailsData;

					$toArray['result'][$key]['credit_note_details'] =  $DetailsData;
					$toArray['result'][$key]['cancel_einvoice'] 	=  (!empty($value['irn'])) ?  1 : 0;
					$toArray['result'][$key]['generate_einvoice'] 	=  (empty($value['irn'])) ?  1 : 0;
					$COLOR_RED 		= "red";
					$COLOR_GREEN 	= "green";
					$toArray['result'][$key]['badge_einvoice'] = "EI";
					$toArray['result'][$key]['badge_color_einvoice'] = (empty($value['ack_no'])) ? $COLOR_RED : $COLOR_GREEN;
				}
				$result = $toArray;
			}
		}
		return $result;
	}

	/*
	use 	: Generate Credit Invoice
	Author 	: Hasmukhi Patel
	Date 	: 10 June,2021
	*/
	public static function GenerateCreditInvoice($creditNoteId=0,$ServiceId=0){
		$data 				= array();
		$ServiceData 		= WmServiceMaster::GetById($ServiceId);

		if($ServiceData){

			######### COMPANY DETAILS ###############
			$gst_state_code_id 			= StateMaster::where("state_id",$ServiceData->Company->state)->value("gst_state_code_id");
			$companyGstData 			= GSTStateCodes::find($gst_state_code_id);
			$MrfStateData 				= $ServiceData['MrfStateData'];
			$StateMaster 				= $ServiceData['StateMaster'];
			$ClientStateData 			= $ServiceData['ClientStateData'];
			$data['mrf_address'] 		= isset($ServiceData->Department->address) ? ucwords(strtolower($ServiceData->Department->address)) : "";
			$data['mrf_city'] 			= isset($ServiceData->Department->location_id) ? ucwords(strtolower(LocationMaster::where("location_id",$ServiceData->Department->location_id)->value("city"))) : "";
			$data['mrf_gst_in'] 		= isset($ServiceData->Department->gst_in) ? strtoupper(strtolower($ServiceData->Department->gst_in)) : "";
			// $data['mrf_state_name'] 	= isset($MrfStateData->state_name) ? ucwords($MrfStateData->state_name) : "";
			// $data['mrf_state_code'] 	= isset($MrfStateData->display_state_code) ? strtoupper($MrfStateData->display_state_code) : "";
			$data['mrf_state_name'] 	= isset($ServiceData["mrf_state"]) ? ucwords($ServiceData["mrf_state"]) : "";
			$data['mrf_state_code'] 	= isset($ServiceData["mrf_state_code"]) ? strtoupper($ServiceData["mrf_state_code"]) : "";
			$data['mrf_pincode'] 		= isset($ServiceData->Department->pincode) ? strtoupper($ServiceData->Department->pincode) : "";

			$data['company_title'] 		= ucwords(strtolower($ServiceData->Company->company_name));
			$data['company_address'] 	= ucwords(strtolower($ServiceData->Company->address1." ".$ServiceData->Company->address2));
			$data['company_city'] 		= ucwords(strtolower(LocationMaster::where("location_id",$ServiceData->Company->city)->value("city")));
			$data['company_gst_in'] 	= strtoupper($ServiceData->Company->gst_no);
			$data['company_cin_no'] 	= strtoupper($ServiceData->Company->cin_no);
			$data['company_state_name'] = (($companyGstData) ? ucwords(strtolower($companyGstData->state_name)) : "");
			$data['company_state_code'] = (($companyGstData) ? $companyGstData->display_state_code : "");
			$data['company_zipcode'] 	= $ServiceData->Company->zipcode;
			$data['company_merchant_key'] 	= $ServiceData->Company->merchant_key;
			// $data['company_cin'] 		= $ServiceData->Company->cin_no;
			######### CLIENT DETAILS ###############
			// $data['client_state_name'] 	= ucwords(strtolower($ServiceData["client_state"]));
			// $data['client_state_code'] 	= $ServiceData["client_state_code"];
			// $data['client_city_name'] 	= LocationMaster::where("location_id",$ServiceData->Client->city_id)->value("city");
			// $data['client_pincode'] 	= $ServiceData->Client->pincode;
			$data['client_name'] 			= ucwords(strtolower($ServiceData->Client->client_name));
			$data['client_pan_no'] 			= (isset($ServiceData->client_pan_no)?strtoupper($ServiceData->client_pan_no):"");

			$Billing_address_Data 			= ShippingAddressMaster::where("id",$ServiceData->billing_address_id)->first();
			$CLIENT_STATE_CODE 				= ($Billing_address_Data) ? GSTStateCodes::where("id",$Billing_address_Data->state_code)->value("display_state_code") : $ServiceData->Client->client_state_code;
			$data['client_address'] 		= ($Billing_address_Data) ? ucwords(strtolower($Billing_address_Data->shipping_address)) : ucwords(strtolower($ServiceData->Client->address));
			$data['client_gst_in'] 			= ($Billing_address_Data) ? strtoupper($Billing_address_Data->gst_no) : strtoupper($ServiceData->Client->gstin_no);
			$data['client_state_code'] 		= $CLIENT_STATE_CODE;
			$data['client_state_name'] 		= ($Billing_address_Data) ? strtoupper($Billing_address_Data->state) : ucwords(strtolower($ServiceData["client_state"]));
			$data['client_pincode'] 		= ($Billing_address_Data) ? $Billing_address_Data->pincode : $ServiceData->Client->pincode;
			$data['client_city_name'] 		= ($Billing_address_Data) ? $Billing_address_Data->city : LocationMaster::where("location_id",$ServiceData->Client->city_id)->value("city");
			
			######### OTHER DETAILS #############
			$data['term_of_payment'] 	= $ServiceData['days'];
			$data['invoice_no'] 		= $ServiceData['serial_no'];
			$data['invoice_date'] 		= $ServiceData['created_at'];
			$data['dispatch_doc_no'] 	= $ServiceData['dispatch_doc_no'];
			$data['dispatched_through'] = ucwords(strtolower($ServiceData['dispatched_through']));
			$data['destination'] 		= ucwords(strtolower($ServiceData['destination']));
			$data['terms_payment'] 		= ucwords(strtolower($ServiceData['terms_payment']));

			$data["from_same_state"] = ($data['client_state_code'] == $data['mrf_state_code']) ? "Y" : "N";

			$productList 	= array();
			$sameState 		= "";
			$creditNote 	= WmServiceInvoicesCreditDebitNotes::find($creditNoteId);
			if($creditNote){
				$data['mrf_id'] 		= $creditNote->mrf_id;
				$data['serial_no'] 		= $creditNote->serial_no;
				$data['service_id'] 	= $creditNote->service_id;

				$data['dated'] 			= (!empty($creditNote->change_date)) ? date("Y-m-d",strtotime($creditNote->change_date)) : "";
				$data['note_title'] 	= ($creditNote->notes_type == 1) ? "Debit" : "Credit";
				$data['notes_type'] 	= $creditNote->notes_type;


				######### QR CODE GENERATION OF E INVOICE NO #############
				$qr_code 				= "";
				$e_invoice_no 			= (!empty($creditNote->irn)) 		? $creditNote->irn : "";
				$acknowledgement_no 	= (!empty($creditNote->ack_no)) 	? $creditNote->ack_no : "";
				$acknowledgement_date 	= (!empty($creditNote->ack_date)) 	? $creditNote->ack_date : "";
				$qr_code_string 		= (isset($creditNote->signed_qr_code) && !empty($creditNote->signed_qr_code)) ? $creditNote->signed_qr_code : "" ;
				if(!empty($qr_code_string)){
					$name 					= "service_credit_debit_".$creditNoteId;
					$qr_code 				= url("/")."/".GetQRCode($qr_code_string,$creditNoteId);
					$path 					= public_path("/")."phpqrcode/".$name.".png";
					$type 					= pathinfo($path, PATHINFO_EXTENSION);
					if(file_exists($path)){
						$imgData				= file_get_contents($path);
						$qr_code 				= 'data:image/' . $type . ';base64,' . base64_encode($imgData);
						unlink(public_path("/")."/phpqrcode/".$name.".png");
					}
				}
				$data['irn'] 			= $e_invoice_no;
				$data['ack_date'] 		= $acknowledgement_date;
				$data['ack_no'] 		= $acknowledgement_no;
				$data['qr_code'] 		= $qr_code;

				######### QR CODE GENERATION OF E INVOICE NO #############
				if(!empty($creditNote->CreditDetails)){
					foreach($creditNote->CreditDetails as $key => $value){
						$sameState 			= ($value['from_same_state'] == "Y") ? true : false;
						$rate 				= ($value['change_in'] == 1 || $value['change_in'] == 3) ? _FormatNumberV2($value['revised_rate']) : _FormatNumberV2($value['rate']);
						$qty 				= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $value['revised_quantity'] : $value['quantity'];
						$productList[$key]  = $value;
						$product 			= WmServiceProductMaster::where("id",$value['product_id'])->first();
						$productList[$key]['original_qty'] 		= $value['quantity'];
						$productList[$key]['original_rate'] 	= _FormatNumberV2($value['rate']);
						$productList[$key]['invoice_rate'] 		= _FormatNumberV2($rate);
						$productList[$key]['invoice_qty'] 		= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $qty : 0;
						$productList[$key]['product_name'] 		= ($product) ? $product->product : "";
						$productList[$key]['hsn_code'] 			= ($product) ? $product->hsn_code 	: "";
						############# ###################
						$return_type = "";
						if($value['change_in'] == 1){
							$return_type =  " (Rate Difference)";
						}elseif($value['change_in'] == 2 && $value['inward_stock'] == 2){
							$return_type =  " (Weight Difference)";
						}
						$productList[$key]['return_type'] 	= ucwords($return_type);
						$GST_AMOUNT  = ($data['from_same_state'] == "Y" && $value['revised_gst_amount'] > 0)  ? _FormatNumberV2($value['revised_gst_amount'] / 2) : _FormatNumberV2($value['revised_gst_amount']);
						$productList[$key]['cgst_amount'] = ($data['from_same_state'] == "Y" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
						$productList[$key]['sgst_amount'] = ($data['from_same_state'] == "Y" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
						$productList[$key]['igst_amount'] = ($data['from_same_state'] == "N" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
					}
				}
			}
			$data['credit_note_no'] 	= ($creditNote) ? $creditNote->serial_no : "";
			$data['remarks'] 			= ($creditNote) ? ucwords(strtolower($creditNote->remarks)) : "";
			$data['products'] 			= $productList;

			// echo "<pre>";
			// prd($data);
			// exit;
		}

		return $data;
	}

	/*
	use 	: Approve Credit Note
	Author 	: Hasmukhi Patel
	Date 	: 10 June,2021
	*/
	public static function ApproveCreditNote($request){
		$status 	= (isset($request['status']) && !empty($request['status'])) ? $request['status'] : 0;
		$Id 		= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : 0;
		$GetData 	= self::find($Id);
		if($GetData){
			$CODE 				= "";
			$BILL_MRF_ID 		= $GetData->mrf_id;
			$NOTE_CODE 			= ($GetData->notes_type == 1) ? DEBIT_NOTE: CREDIT_NOTE;
			$GET_CODE 			= TransactionMasterCodesMrfWise::GetLastTrnCode($BILL_MRF_ID,$NOTE_CODE);
			if($GET_CODE){
				$CODE 		= 	$GET_CODE->code_value + 1;
				$SERIAL_NO 	=   $GET_CODE->group_prefix.LeadingZero($CODE);
			}
			$GetData->serial_no 		= $SERIAL_NO;
			$GetData->status 			= $status;
			$GetData->approved_by 		= Auth()->user()->adminuserid;
			$GetData->approved_date 	= date("Y-m-d H:i:s");
			$GetData->save();

			TransactionMasterCodesMrfWise::UpdateTrnCode($BILL_MRF_ID,$NOTE_CODE,$CODE);
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$Id);
			return true;
		}
		return false;
	}

	/*
	use 	: Generate Credit Debit Note E invoice
	Author 	: Axay Shah
	Date 	: 07 July,2021
	*/
	public static function GenerateServiceCreditDebitEinvoice($ID){
		$creditData = self::find($ID);
        $array  	= array();
        $res 		= array();
        if(!empty($creditData)){
        	$data 				= self::GenerateCreditInvoice($ID,$creditData->service_id);
        	
        	$service_id = (isset($data['service_id']) && $data['service_id'] > 0) ? $data['service_id'] : 0;
        	$is_service_invoice = WmServiceMaster::where("id",$service_id)->value("is_service_invoice");

        	$IsServ 			= ($is_service_invoice == 0) ? "N" : "Y";
        	$invoice_date 	= $creditData->change_date;
        	$SellerDtls   	= array();
        	$BuyerDtls 		= array();
			$MERCHANT_KEY 	= (isset($data["company_merchant_key"])) ? $data["company_merchant_key"] : "";
			$COMPANY_NAME 	= (isset($data["company_title"]) && !empty($data["company_title"])) ? $data["company_title"] : null;
			$USERNAME 		= (isset($creditData->Department->gst_username) && !empty($creditData->Department->gst_username)) ? $creditData->Department->gst_username : "";
			$PASSWORD 		= (isset($creditData->Department->gst_password) && !empty($creditData->Department->gst_password)) ? $creditData->Department->gst_password : "";
			$GST_IN 		= (isset($creditData->Department->gst_in) && !empty($creditData->Department->gst_in)) ? $creditData->Department->gst_in : "";
			############## SALLER DETAILS #############
			$FROM_ADDRESS_1 = ucwords(strtolower($data['mrf_address']));
			$FROM_ADDRESS_2 = null;
			if(strlen($FROM_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($FROM_ADDRESS_1);
				$FROM_ADDRESS_1 = (!empty($ARR_STRING)) ? $ARR_STRING[0] : $FROM_ADDRESS_1;
				$FROM_ADDRESS_2 = (!empty($ARR_STRING)) ? $ARR_STRING[1] : $FROM_ADDRESS_1;
			}
			$FROM_TREAD 		= $COMPANY_NAME;
			$FROM_GST 			= (isset($data["mrf_gst_in"]) && !empty($data["mrf_gst_in"])) ? $data["mrf_gst_in"] : null;
			$FROM_STATE_CODE 	= (isset($data["mrf_state_code"]) && !empty($data["mrf_state_code"])) ? $data["mrf_state_code"] : null;
			$FROM_STATE 		= (isset($data["mrf_state_name"]) && !empty($data["mrf_state_name"])) ? $data["mrf_state_name"] : null;
			$FROM_LOC 			= (isset($data["mrf_city"]) && !empty($data["mrf_city"])) ? $data["mrf_city"] : null;
			$FROM_PIN 			= (isset($data["mrf_pincode"]) && !empty($data["mrf_pincode"])) ? $data["mrf_pincode"] : null;

			############## BUYER DETAILS #############
			$TO_ADDRESS_1 		= (!empty($data["client_address"])) ? ucwords(strtolower($data["client_address"])): null;
			$TO_ADDRESS_2 		= "";
			if(strlen($TO_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($TO_ADDRESS_1);
				$TO_ADDRESS_1 = (!empty($ARR_STRING)) ? $ARR_STRING[0] : $TO_ADDRESS_1;
				$TO_ADDRESS_2 = (!empty($ARR_STRING)) ? $ARR_STRING[1] : $TO_ADDRESS_2;
			}
			$TO_TREAD 			= (!empty($data["client_name"])) ? ucwords(strtolower($data["client_name"])): null;
			$TO_GST 			= (!empty($data["client_gst_in"])) ? strtoupper(strtolower($data["client_gst_in"])): null;
			$TO_STATE_CODE 		= (!empty($data["client_state_code"])) ? ucwords(strtolower($data["client_state_code"])): null;
			$TO_STATE 			= (!empty($data["client_state_name"])) ? ucwords(strtolower($data["client_state_name"])): null;
			$TO_LOC 			= (!empty($data["client_city_name"])) ? ucwords(strtolower($data["client_city_name"])): null;
			$TO_PIN 			= (!empty($data["client_pincode"])) ? ucwords(strtolower($data["client_pincode"])): null;
			$DOC_NO 			= (isset($data['serial_no']) && !empty($data['serial_no'])) ? $data['serial_no'] : null;
			$TO_STATE_CODE 		= GSTStateCodes::where("id",$TO_STATE_CODE)->value('display_state_code');
			$DOC_DATE 			= (isset($invoice_date) && !empty($invoice_date)) ? date("d/m/Y",strtotime($invoice_date)) : null;

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
			$SAME_STATE 		= ($FROM_STATE_CODE == $TO_STATE_CODE) ? true : false;
			$IGST_ON_INTRA 		= "N";

	        $array['merchant_key']				= $MERCHANT_KEY;
			$array["SellerDtls"] 				= $SellerDtls;
			$array["BuyerDtls"] 				= $BuyerDtls;
			$array["BuyerDtls"] 				= $BuyerDtls;
			$array["DispDtls"]   				= null;
	        $array["ShipDtls"]    				= null;
	        $array["EwbDtls"]     				= null;
			$array["version"]     				= E_INVOICE_VERSION;
	        $array["TranDtls"]["TaxSch"]        = TAX_SCH ;
	        $array["TranDtls"]["SupTyp"]        = "B2B";
	        $array["TranDtls"]["RegRev"]        = "N";
	        $array["TranDtls"]["EcmGstin"]      = null;
	        $array["TranDtls"]["IgstOnIntra"]   = $IGST_ON_INTRA;
	        $array["DocDtls"]["Typ"]            = ($data["notes_type"] == 1) ? "DBN" : "CRN";
	        $array["DocDtls"]["No"]             = $creditData->serial_no;
	        $array["DocDtls"]["Dt"]             = $DOC_DATE;
	        $itemList                          	= isset($data["products"]) ? $data["products"]:array();
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
					$PRODUCT_DATA 	= WmServiceProductMaster::where("id",$value->product_id)->first();
					$UOM 			= $PRODUCT_DATA->uom;
					if($UOM == PARA_PRODUCT_UNIT_IN_KG){
						$UOM = "KGS";
					}elseif($UOM == PARA_PRODUCT_UNIT_IN_NOS){
						$UOM = "NOS";
					}else{
						$UOM = "OTH";
					}
					$rate 	= ($value->change_in == 1 || $value->change_in == 3) ? _FormatNumberV2($value->revised_rate) : _FormatNumberV2($value->rate);
					$qty 	= ($value->change_in == 2 || $value->change_in == 3) ? $value->revised_quantity : $value->quantity;
        			$TOTAL_GST_PERCENT 			= ($SAME_STATE) ? _FormatNumberV2($value->cgst_rate + $value->sgst_rate) :  _FormatNumberV2($value->igst_rate);
        			$QTY 						= (float)$qty;
        			$RATE 						= (float)$rate;
        			$IGST 						= (float)$value->igst_rate;
        			$SGST 						= (float)$value->sgst_rate;
        			$CGST 						= (float)$value->cgst_rate;
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

			        $item[] = array(
	                    "SlNo"              	=> $i,
                        "PrdDesc"               => $PRODUCT_DATA->product,
                        "IsServc"               => $IsServ,
                        "HsnCd"                 => $PRODUCT_DATA->hsn_code,
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
                        "TotItemVal"            => _FormatNumberV2((float)$TOTAL_NET_AMT),
	                );
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
			    	if(isset($res["Status"]) && $res["Status"] == 1){
			    		$details 	= $res["Data"];
			    		$AckNo  	= (isset($details['AckNo'])) ? $details['AckNo']  : "";
			    		$AckDt  	= (isset($details['AckDt'])) ? $details['AckDt']  : "";
		                $signedQr   = (isset($details['SignedQRCode'])) ? $details['SignedQRCode']  : "";
		                $Irn    	= (isset($details['Irn'])) ? $details['Irn']      : "";
		                self::where("id",$ID)->update([
		                	"irn" 			=> $Irn,
		                	"ack_date" 		=> $AckDt,
		                	"ack_no" 		=> $AckNo,
		                	"signed_qr_code" => $signedQr,
		                	"updated_at" 	=> date("Y-m-d H:i:s"),
		                	"updated_by" 	=> Auth()->user()->adminuserid
		                ]);
			    	}
			    }
			    return $res;
			}
	    }
	}
	/*
	Use 	: Cancel Service
	Author 	: Axay Shah
	Date  	: 09 March 2021
	*/
	public static function CancelServiceCreditDebitEInvoice($request){
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
			    		self::where("id",$ID)
			    		->where("irn",$IRN)
			    		->update([
		                	"irn" 				=> "",
		                	"ack_date" 			=> "",
		                	"ack_no" 			=> "",
		                	"signed_qr_code" 	=> "",
		                	"updated_at" 		=> date("Y-m-d H:i:s"),
		                	"updated_by" 		=> Auth()->user()->adminuserid
		                ]);
			    	}
			    	$requestObj = json_encode($request,JSON_FORCE_OBJECT);
					LR_Modules_Log_CompanyUserActionLog($requestObj,$ID);
			    }
			    return $res;
		    }
		}
		return $res;
	}
	/*
	use 	: List Credit Debit Note report for service
	Author 	: Axay Shah
	Date 	: 13 September,2021
	*/
	public static function ListServiceCreditDebitNoteReport($request){
		$baseLocationIds = GetUserAssignedBaseLocation(Auth()->user()->adminuserid);
        $self 			= (new static)->getTable();
		$ServiceMst  	= new WmServiceMaster();
		$Parameter  	= new Parameter();
		$AdminUser  	= new AdminUser();
		$Dispatch  		= new WmDispatch();
		$Department  	= new WmDepartment();
		$SalesMaster 	= new WmSalesMaster();
		$Product 		= new WmServiceProductMaster();
		$Client 		= new WmClientMaster();
		$Details 		= new WmServiceInvoicesCreditDebitNotesDetails();
		$DetailsTbl 	= $Details->getTable();
		$CMT 			= $Client->getTable();
		$Today          = date('Y-m-d');
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$self.id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size')) ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId         = GetBaseLocationCity();
		$data 			= self::select(

					"SERVICE.invoice_date",
					"$self.service_id",
					"SERVICE.serial_no as service_id",
					"PARA.para_value AS service_type_name",
					"$self.id",
					"$self.serial_no",
					"$self.invoice_no",
					"$self.change_date",
					"$self.irn",
					"$self.ack_date",
					"$self.ack_no",
					"$self.approved_date",
					"$self.remarks",
					\DB::raw("
						(CASE WHEN $self.notes_type = 0 THEN 'C'
									WHEN $self.notes_type = 1 THEN 'D'
						END ) AS note_type_name"),
					\DB::raw("
					(CASE WHEN $self.status = 0 THEN 'P'
								WHEN $self.status = 1 THEN 'A'
								WHEN $self.status = 2 THEN 'R'
					END ) AS status_name"),
					\DB::raw("DEPT.department_name"),
					\DB::raw("SHAP.gst_no as client_gst_in"),
					\DB::raw("$CMT.client_name as client_name"),
					\DB::raw("DETAILS.product_id"),
					\DB::raw("DETAILS.revised_gross_amount"),
					\DB::raw("DETAILS.revised_gst_amount"),
					\DB::raw("DETAILS.revised_net_amount"),
					\DB::raw("DETAILS.cgst_rate"),
					\DB::raw("DETAILS.sgst_rate"),
					\DB::raw("DETAILS.igst_rate"),
					\DB::raw("PRODUCT.product"),
					\DB::raw("PRODUCT.service_net_suit_code as product_net_suit_code"),
					\DB::raw("PRODUCT.hsn_code"),
					\DB::raw("PRODUCT.description"),
					\DB::raw("DETAILS.rate"),
					\DB::raw("DETAILS.quantity"),
					\DB::raw("DETAILS.is_from_same_state"),
					\DB::raw("IF(DETAILS.change_in = 1 OR DETAILS.change_in = 3,DETAILS.revised_rate,rate ) as revised_rate"),
					\DB::raw("IF(DETAILS.change_in = 2 OR DETAILS.change_in = 3,DETAILS.revised_quantity,quantity ) as revised_quantity"),
					\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
					\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
					\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) as approved_by_name")
				)
		->join($DetailsTbl." as DETAILS","$self.id","=","DETAILS.cd_notes_id")
		->join($ServiceMst->getTable()." as SERVICE","$self.service_id","=","SERVICE.id")
		->leftjoin($Parameter->getTable()." as PARA","SERVICE.service_type","=","PARA.para_id")
		->join($Product->getTable()." as PRODUCT","DETAILS.product_id","=","PRODUCT.id")
		->leftjoin($Department->getTable()." as DEPT","$self.mrf_id","=","DEPT.id")
		->leftjoin($CMT,"$CMT.id","=","$self.client_id")
		->leftjoin("shipping_address_master as SHAP","SERVICE.billing_address_id","=","SHAP.id")
		->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid")
		->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid")
		->leftjoin($AdminUser->getTable()." as U3","$self.approved_by","=","U3.adminuserid");

		if($request->has('serial_no') && !empty($request->input('serial_no')))
		{
			$data->where("$self.serial_no","like","%".$request->input('serial_no')."%");
		}
		if($request->has('service_type') && !empty($request->input('service_type')))
		{
			$data->where("SERVICE.service_type",$request->input('service_type'));
		}
		if($request->has('client_id') && !empty($request->input('client_id')))
		{
			$data->where("$self.client_id",$request->input('client_id'));
		}
		if($request->has('invoice_no') && !empty($request->input('invoice_no')))
		{
			$data->where("$self.invoice_no","like","%".$request->input('invoice_no')."%");
		}
		if($request->has('mrf_id') && !empty($request->input('mrf_id')))
		{
			$data->where("$self.mrf_id",$request->input('mrf_id'));
		}
		if($request->has('product_id') && !empty($request->input('product_id')))
		{
			$data->where("DETAILS.product_id",$request->input('product_id'));
		}
		if($request->has('net_suit_code') && !empty($request->input('net_suit_code')))
		{
			$data->where("PRODUCT.service_net_suit_code",$request->input('net_suit_code'));
		}
		if($request->has('status'))
		{
			if($request->input('status') == "0"){
				$data->where("$self.status",$request->input('status'));
			}elseif($request->input('status') == "1" || $request->input('status') == "2"){
				$data->where("$self.status",$request->input('status'));
			}
		}
		if($request->has('notes_type'))
		{
			if($request->input('notes_type') == "0"){
				$data->where("$self.notes_type",$request->input('notes_type'));
			}elseif($request->input('notes_type') == "1"){
				$data->where("$self.notes_type",$request->input('notes_type'));
			}
		}
		if(!empty($request->input('startDate')) && !empty($request->input('endDate')))
		{
			$data->whereBetween("$self.change_date",array(date("Y-m-d", strtotime($request->input('startDate'))),date("Y-m-d",strtotime($request->input('endDate')))));
		}else if(!empty($request->input('startDate'))){
		   $datefrom = date("Y-m-d", strtotime($request->input('startDate')));
		   $data->whereBetween("$self.change_date",array($datefrom,$datefrom));
		}else if(!empty($request->input('endDate'))){
		   $data->whereBetween("$self.change_date",array(date("Y-m-d", strtotime($request->input('endDate'))),$Today));
		}
		if($request->has('is_einvoice')) {
			 $is_einvoice = $request->input("is_einvoice");
			if($is_einvoice == "-1") {
				$data->Where(function($query) use($self) {
                $query->whereNull("$self.ack_no")->orWhere("$self.ack_no",'=',' ')->orWhere("$self.ack_no",'=','0');
            	});

			} else if($is_einvoice == "1") {
				$data->Where(function($query) use($self) {
                $query->whereNotNull("$self.ack_no")->Where("$self.ack_no",'<>',' ')->Where("$self.ack_no",'<>','0');
            	});
			}
		}
		$data->where(function($query) use($baseLocationIds){
		    $query->whereIn("DEPT.base_location_id",$baseLocationIds);
		});
		// LiveServices::toSqlWithBinding($data);
		$result 			= $data->where("$self.company_id",Auth()->user()->company_id)->get()->toArray();
		$TOTAL_NET_AMT 		= 0;
		$TOTAL_GROSS_AMT 	= 0;
		$TOTAL_GST_AMT 		= 0;
		$TOTAL_QTY 			= 0;
		$TOTAL_ORG_QTY 		= 0;
		$TOTAL_CGST_AMT		= 0;
		$TOTAL_SGST_AMT		= 0;
		$TOTAL_IGST_AMT		= 0;
		if(!empty($result)){
			foreach($result as $key => $value){
				$TOTAL_QTY 			+= (!empty($value["revised_quantity"])) ? _FormatNumberV2($value["revised_quantity"]) : 0;
				$TOTAL_ORG_QTY 		+= (!empty($value["quantity"])) ? _FormatNumberV2($value["quantity"]) : 0;
				$TOTAL_NET_AMT 		+= (!empty($value["revised_net_amount"])) ? _FormatNumberV2($value["revised_net_amount"]) : 0.00;
				$TOTAL_GST_AMT 		+= (!empty($value["revised_gst_amount"])) ? _FormatNumberV2($value["revised_gst_amount"]) : 0.00;
				$TOTAL_GROSS_AMT 	+= (!empty($value["revised_gross_amount"])) ? _FormatNumberV2($value["revised_gross_amount"]) : 0.00;
				$sameState 			= ($value['is_from_same_state'] == "1") ? true : false;
				$CGST_AMT 		 	= 0;
				$SGST_AMT 		 	= 0;
				$IGST_AMT 		 	= 0;
				if($sameState == true){
					$CGST_AMT 	= $value["revised_gst_amount"] / 2;
					$SGST_AMT 	= $value["revised_gst_amount"] / 2;
					$IGST_AMT 	= 0;	
				}else{
					$CGST_AMT 	= 0 ;
					$SGST_AMT 	= 0 ;
					$IGST_AMT 	= $value["revised_gst_amount"];
				}
				$result[$key]['cgst_amount'] 	= _FormatNumberV2($CGST_AMT);
				$result[$key]['sgst_amount'] 	= _FormatNumberV2($SGST_AMT);
				$result[$key]['igst_amount'] 	= _FormatNumberV2($IGST_AMT);
				$TOTAL_CGST_AMT 				+= $CGST_AMT;
				$TOTAL_SGST_AMT 				+= $SGST_AMT;
				$TOTAL_IGST_AMT 				+= $IGST_AMT;
			}
		}
		$array 						= array();
		$array["result"] 			= $result;
		$array["TOTAL_QTY"] 		= $TOTAL_QTY;
		$array["TOTAL_ORG_QTY"] 	= $TOTAL_ORG_QTY;
		$array["TOTAL_NET_AMT"] 	= _FormatNumberV2($TOTAL_NET_AMT);
		$array["TOTAL_GST_AMT"] 	= _FormatNumberV2($TOTAL_GST_AMT);
		$array["TOTAL_GROSS_AMT"] 	= _FormatNumberV2($TOTAL_GROSS_AMT);
		$array['TOTAL_CGST_AMT'] 	= _FormatNumberV2($TOTAL_CGST_AMT);
		$array['TOTAL_SGST_AMT'] 	= _FormatNumberV2($TOTAL_SGST_AMT);
		$array['TOTAL_IGST_AMT'] 	= _FormatNumberV2($TOTAL_IGST_AMT);
		return $array;
	}
	
	/*
	use 	: Get Credit Note Amount
	Author 	: Axay Shah
	Date 	: 13 September,2021
	*/
	public static function GetServiceCreditAmt($baseLocationIds=array(),$startDate="",$endDate="",$service_id=0,$product_id=0,$ServiceType=0,$request="")
	{
		if(!is_array($baseLocationIds)) {
			$baseLocationIds =  explode(",",$baseLocationIds);
		}
		$MRF_IDS 		= WmDepartment::whereIn("base_location_id",$baseLocationIds)->where("status",1)->pluck("id")->toArray();
		$CN_DE_TBL 		= new WmServiceInvoicesCreditDebitNotesDetails();
		$CN_TBL 		= new WmServiceInvoicesCreditDebitNotes();
		$CND_MASTER 	= $CN_DE_TBL->getTable();
		$CN_MASTER 		= $CN_TBL->getTable();
		$AMOUNT 		= 0;
		$CREDIT_AMT 	= WmServiceInvoicesCreditDebitNotesDetails::join($CN_MASTER." as CNM","$CND_MASTER.cd_notes_id","=","CNM.id")
							->leftjoin("wm_service_master","CNM.service_id","=","wm_service_master.id")
							->where("CNM.notes_type",0)
							->where("CNM.status",1)
							->whereIn("CNM.mrf_id",$MRF_IDS);
		if(!empty($startDate) && !empty($endDate)) {
			$startDate 	= date("Y-m-d",strtotime($startDate));
			$endDate 	= date("Y-m-d",strtotime($endDate));
			$CREDIT_AMT->whereBetween("CNM.change_date",array($startDate,$endDate));
		}
		if(!empty($service_id)) {
			$CREDIT_AMT->where("CNM.service_id",$service_id);
		}
		if(!empty($ServiceType)) {
			$CREDIT_AMT->where("wm_service_master.service_type",$ServiceType);
		}
		if(!empty($product_id)) {
			$CREDIT_AMT->where("$CND_MASTER.product_id",$product_id);
		}
		if(isset($request->client_id) && !empty($request->client_id)) {
			$CREDIT_AMT->where("CNM.client_id",$request->client_id);
		}
		$AMOUNT = $CREDIT_AMT->sum("$CND_MASTER.revised_gross_amount");
		return $AMOUNT;
	}
		/*
	use 	: Get Debit note amount
	Author 	: Axay Shah
	Date 	: 13 September,2021
	*/
	public static function GetServiceDebitAmt($baseLocationIds=array(),$startDate="",$endDate="",$service_id=0,$product_id=0,$ServiceType=0,$request="")
	{
		if(!is_array($baseLocationIds)) {
			$baseLocationIds =  explode(",",$baseLocationIds);
		}
		$MRF_IDS 		= WmDepartment::whereIn("base_location_id",$baseLocationIds)->where("status",1)->pluck("id")->toArray();
		$CN_DE_TBL 		= new WmServiceInvoicesCreditDebitNotesDetails();
		$CN_TBL 		= new WmServiceInvoicesCreditDebitNotes();
		$CND_MASTER 	= $CN_DE_TBL->getTable();
		$CN_MASTER 		= $CN_TBL->getTable();
		$AMOUNT 		= 0;
		$DEBIT_AMT 		= WmServiceInvoicesCreditDebitNotesDetails::join($CN_MASTER." as CNM","$CND_MASTER.cd_notes_id","=","CNM.id")
							->leftjoin("wm_service_master","CNM.service_id","=","wm_service_master.id")
							->where("CNM.notes_type",1)
							->where("CNM.status",1)
							->whereIn("CNM.mrf_id",$MRF_IDS);
		if(!empty($startDate) && !empty($endDate)) {
			$startDate 	= date("Y-m-d",strtotime($startDate));
			$endDate 	= date("Y-m-d",strtotime($endDate));
			$DEBIT_AMT->whereBetween("CNM.change_date",array($startDate,$endDate));
		}
		if(!empty($service_id)) {
			$DEBIT_AMT->where("CNM.service_id",$service_id);
		}
		if(!empty($ServiceType)) {
			$DEBIT_AMT->where("wm_service_master.service_type",$ServiceType);
		}
		if(!empty($product_id)) {
			$DEBIT_AMT->where("$CND_MASTER.product_id",$product_id);
		}
		if(isset($request->client_id) && !empty($request->client_id)) {
			$DEBIT_AMT->where("CNM.client_id",$request->client_id);
		}
		$AMOUNT = $DEBIT_AMT->sum("$CND_MASTER.revised_gross_amount");
		return $AMOUNT;
	}
}