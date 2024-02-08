<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmInvoicesCreditDebitNotesDetails;
use App\Models\AdminUser;
use App\Models\AdminUserRights;
use App\Models\WmDispatch;
use App\Models\WmDepartment;
use App\Models\WmSalesMaster;
use App\Models\WmProductMaster;
use App\Models\WmClientMaster;
use App\Models\MasterCodes;
use App\Models\WmInvoices;
use App\Models\MediaMaster;
use App\Models\WmInvoicesCreditDebitNotesMasterCodes;
use App\Models\TransactionMasterCodesMrfWise;
use App\Models\UserBaseLocationMapping;
use App\Models\WmDispatchSalesProductAvgPrice;
use App\Models\ProductInwardLadger;
use App\Models\StockLadger;
use App\Models\NetSuitStockLedger;
use App\Models\WmBatchProductDetail;
use App\Models\WmInvoicesCreditDebitNotesChargesDetails;
use App\Models\WmInvoicesCreditDebitNotesFrieghtDetails;
use App\Models\GroupRightsTransaction;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Mail;
class WmInvoicesCreditDebitNotes extends Model implements Auditable
{
	protected 	$table 		=	'wm_invoices_credit_debit_notes';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	public function CreditDetails(){
		return $this->hasMany(WmInvoicesCreditDebitNotesDetails::class,"cd_notes_id","id");
	}

	public function department(){
		return $this->belongsTo(WmDepartment::class,"mrf_id","id");
	}

	public function crn_document(){
		return $this->belongsTo(MediaMaster::class,"crn_document_id");
	}
	/*
	use 	: Get Last invoice id
	Author 	: Axay Shah
	Date 	: 16 Octomber,2020
	*/
	public static function GenerateCreditDebitNotes($request)
	{
		$ID = 0;
		/* "CHANGE IN" PARAMETER IS FOR CREDIT DEBIT NOTE IN QUANTITY OR RATE */

		$CODE 		= "";
		$SERIAL_NO 	= "";
		$DispTbl 	= new WmDispatch();
		$invoiceTbl = new WmInvoices;
		$inv 		= $invoiceTbl->getTable();
		$TCS_AMT 	= 0;
		$TCS_RATE 	= 0;
		$createdBy  = Auth()->user()->adminuserid;
		$CompanyID  = Auth()->user()->company_id;
		$updatedBy  = Auth()->user()->adminuserid;
		$InvoiceID 	= (isset($request['invoice_id']) 	&& !empty($request['invoice_id'])) 	?  $request['invoice_id'] 	: 0;
		$DispatchID = (isset($request['dispatch_id']) 	&& !empty($request['dispatch_id'])) ? $request['dispatch_id']	: 0;
		$Save 				= new self();
		$Save->created_by  	= $createdBy;
		$Save->updated_by  	= $updatedBy;
		$notes_type 		= (isset($request['notes_type']) && !empty($request['notes_type']))  ?  $request['notes_type'] : 0;
		$NOTE_CODE 			= (isset($request['notes_type']) && !empty($request['notes_type']))  ?  DEBIT_NOTE : CREDIT_NOTE;
		$MRF_ID 			= (isset($request['mrf_id']) 	 && !empty($request['mrf_id'])) 	?  $request['mrf_id'] 	: 0;
		$REASON 			= (isset($request['reason']) 	&& !empty($request['reason'])) ? $request['reason']	: "";
		$BILL_MRF_ID 		= 0;
		######## NEW CHANGES ##########
		$INVOICE_DATA 		= WmInvoices::select("DES.bill_from_mrf_id","DES.master_dept_id")->where("$inv.id",$InvoiceID)->join($DispTbl->getTable()." as DES",$inv.".dispatch_id","=","DES.id")->first();
		if($INVOICE_DATA)
		{
			$BILL_MRF_ID 		= $INVOICE_DATA->bill_from_mrf_id;
			$MRF_ID 			= $INVOICE_DATA->master_dept_id;
			$TCS_AMT 			= $INVOICE_DATA->tcs_amount;
			$TCS_RATE 			= $INVOICE_DATA->tcs_rate;
			$GET_CODE 			= TransactionMasterCodesMrfWise::GetLastTrnCode($BILL_MRF_ID,$NOTE_CODE);
			if($GET_CODE)
			{
				$CODE 		= 	$GET_CODE->code_value + 1;
				$SERIAL_NO 	=   $GET_CODE->group_prefix.LeadingZero($CODE);
			}
			$Save->reason 		= $REASON;
			$Save->serial_no 	= $SERIAL_NO;
			$Save->dispatch_id 	= $DispatchID;
			$Save->transaction_type = (isset($request['transaction_type']) && !empty($request['transaction_type'])) ? $request['transaction_type']	: 0;
			$Save->invoice_no  	= (isset($request['invoice_no']) 	&& !empty($request['invoice_no'])) 	?  $request['invoice_no'] 	: "";
			$Save->invoice_id  	= $InvoiceID;
			$Save->change_date  = date("Y-m-d");
			$Save->notes_type  	= $notes_type;
			$Save->mrf_id  		= $MRF_ID;
			$Save->bill_from_mrf_id  = $BILL_MRF_ID;
			$Save->remarks  	= (isset($request['remarks']) 	 && !empty($request['remarks'])) 	?  $request['remarks'] 	: "";
			$Save->company_id  	= $CompanyID;
			$Save->first_level_approved_by  = (isset($request['first_level_approved_by']) 	&& !empty($request['first_level_approved_by'])) 	?  $request['first_level_approved_by'] 	: 0;
			######### IMAGE UPLOAD ##########
			if(isset($_FILES["crn_document"]["tmp_name"])) {
				$fileName 		= $_FILES["crn_document"]["name"];
				$partialPath 	= PATH_CREDIT_NOTE;
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
			if($Save->save())
			{
				######### UPDATE LAST DIGIT IN MASTER CODES #####################
				TransactionMasterCodesMrfWise::UpdateTrnCode($BILL_MRF_ID,$NOTE_CODE,$CODE);
				######### UPDATE LAST DIGIT IN MASTER CODES #####################
				$ID 			= $Save->id;
				$ProductList  	= (isset($request['product']) 		&& !empty($request['product'])) 	?  json_decode($request['product'],true) 	: "";
				$FROM_SAME_STATE = "";
				if(!empty($ProductList)){
					foreach($ProductList as $Raw){
						$CHANGE_IN 			= (isset($Raw['change_in']) && !empty($Raw['change_in'])) ?  $Raw['change_in'] 	: 0;
						if(!empty($CHANGE_IN)){
							$cgst_amt 			= 0;
							$sgst_amt 			= 0;
							$igst_amt 			= 0;
							$FROM_SAME_STATE 	= (isset($Raw['is_from_same_state']) && !empty($Raw['is_from_same_state'])) ?  $Raw['is_from_same_state'] : "";
							$PRODUCT_ID 		= (isset($Raw['product_id']) && !empty($Raw['product_id']))? $Raw['product_id'] : 0;
							$CGST_RATE 			= (isset($Raw['cgst_rate']) && !empty($Raw['cgst_rate'])) ?  $Raw['cgst_rate'] 	: 0;
							$SGST_RATE 			= (isset($Raw['sgst_rate']) && !empty($Raw['sgst_rate'])) ?  $Raw['sgst_rate'] 	: 0;
							$IGST_RATE 			= (isset($Raw['igst_rate']) && !empty($Raw['igst_rate'])) ?  $Raw['igst_rate'] 	: 0;
							$Qty 				= (isset($Raw['quantity']) 	&& !empty($Raw['quantity']))  ?  $Raw['quantity'] 	: 0;;
							$Rate 				= (isset($Raw['rate']) 	&& !empty($Raw['rate'])) ?  $Raw['rate'] : 0;
							$ReviseQty 			= (isset($Raw['revised_quantity']) && !empty($Raw['revised_quantity'])) ?  $Raw['revised_quantity'] : 0;
							$ReviseRate 		= (isset($Raw['revised_rate']) 	&& !empty($Raw['revised_rate'])) ?  $Raw['revised_rate'] : 0;
							$GstAmount 			= (isset($Raw['gst_amount']) && !empty($Raw['gst_amount'])) ?  $Raw['gst_amount'] : 0;
							$NetAmount 			= (isset($Raw['net_amount']) && !empty($Raw['net_amount'])) ?  $Raw['net_amount'] : 0;
							$GrossAmount 		= (isset($Raw['gross_amount']) && !empty($Raw['gross_amount'])) ?  $Raw['gross_amount'] : 0;
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
							if($FROM_SAME_STATE) {
								$cgst_amt 		= ($CGST_RATE > 0) ? (($RevisedGrossAmount / 100) * $CGST_RATE):0;
								$sgst_amt 		= ($SGST_RATE > 0) ? (($RevisedGrossAmount / 100) * $SGST_RATE):0;
								$RevisedGstAmt 	= $cgst_amt + $sgst_amt;
							}else{
								$igst_amt 		= ($IGST_RATE > 0) ? (( $RevisedGrossAmount / 100) * $IGST_RATE):0;
								$RevisedGstAmt 	= $igst_amt;
							}
							$RevisedNetAmount 	=	$RevisedGrossAmount + $RevisedGstAmt;
							$INSERT 						= new WmInvoicesCreditDebitNotesDetails();
							$INSERT->cd_notes_id 			= $ID;
							$INSERT->change_in 				= $CHANGE_IN;
							$INSERT->dispatch_product_id 	= (isset($Raw['dispatch_product_id']) && !empty($Raw['dispatch_product_id'])) ? $Raw['dispatch_product_id'] : 0;
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
				#################### FRIEGHT CALCULATION ################
				$TOTAL_CN_GROSS = WmInvoicesCreditDebitNotesDetails::where("cd_notes_id",$ID)->sum("revised_gross_amount");
				$DISPATCH_GROSS = WmDispatchProduct::where("dispatch_id",$DispatchID)->sum("gross_amount");
				if(round($TOTAL_CN_GROSS) == round($DISPATCH_GROSS)){
					WmInvoicesCreditDebitNotes::where("id",$ID)->update(array("tcs_rate"=>$TCS_RATE,"tcs_amount"=>$TCS_AMT));
				}
				$DISPATCH_DATA 	= WmDispatch::where("id",$DispatchID)->first();
				if(($TOTAL_CN_GROSS >= $DISPATCH_GROSS) && $DISPATCH_DATA && $DISPATCH_DATA->rent_amt > 0){
					\DB::table("wm_invoices_credit_debit_notes_frieght_details")->insert([
						"cd_notes_id" 	=>$ID,
						"cgst_rate" 	=>$DISPATCH_DATA->rent_cgst,
						"sgst_rate" 	=>$DISPATCH_DATA->rent_sgst,
						"igst_rate" 	=>$DISPATCH_DATA->rent_igst,
						"gross_amount" 	=>$DISPATCH_DATA->rent_amt,
						"gst_amount" 	=>$DISPATCH_DATA->rent_gst_amt,
						"net_amount" 	=>$DISPATCH_DATA->total_rent_amt,
						"created_by" 	=>$createdBy,
						"updated_by" 	=>$updatedBy,
						"created_at" 	=>date("Y-m-d H:i:s"),
						"updated_at" 	=>date("Y-m-d H:i:s")
						]);
					}
				#################### FRIEGHT CALCULATION ################
				############# ADDITIONAL CHARGES IN CREDIT DEBIT NOTE #######################
				$AdditionalCharges 	= (isset($request['additional_charges']) 	&& !empty($request['additional_charges'])) 	?  json_decode($request['additional_charges'],true)  : array();
				if(!empty($AdditionalCharges)){
					foreach($AdditionalCharges as $Raw){

						$CHANGE_IN 			= (isset($Raw['change_in']) && !empty($Raw['change_in'])) ?  $Raw['change_in'] 	: 0;
						if(!empty($CHANGE_IN)){
							$cgst_amt 			= 0;
							$sgst_amt 			= 0;
							$igst_amt 			= 0;
							$CHARGE_ID 			= (isset($Raw['charge_id']) && !empty($Raw['charge_id']))? $Raw['charge_id'] : 0;
							$CGST_RATE 			= 0;
							$SGST_RATE 			= 0;
							$IGST_RATE 			= 0;
							$Qty  		= (isset($Raw['quantity']) 	&& !empty($Raw['quantity']))  ?  $Raw['quantity'] 	: 0;;
							$Rate 		= (isset($Raw['rate']) && !empty($Raw['rate'])) ? $Raw['rate'] : 0;
							$ReviseQty 	= (isset($Raw['revised_quantity']) && !empty($Raw['revised_quantity'])) ?  $Raw['revised_quantity'] : 0;
							$ReviseRate = (isset($Raw['revised_rate']) 	&& !empty($Raw['revised_rate'])) ?  $Raw['revised_rate'] : 0;
							$chargeReviseQty = (isset($Raw['revised_quantity']) && !empty($Raw['revised_quantity'])) ?  $Raw['revised_quantity'] : 0;
							$NewQty  	= (isset($Raw['revised_quantity_value']) && !empty($Raw['revised_quantity_value'])) ?  $Raw['revised_quantity_value'] : 0;
							$NewRate 	= (isset($Raw['revised_rate_value']) && !empty($Raw['revised_rate_value'])) ?  $Raw['revised_rate_value'] : 0;
							$NewRate 	= ($notes_type == 1 && ($CHANGE_IN == 1 || $CHANGE_IN == 3)) ?  $Rate + $ReviseRate : $NewRate;
							$NewQty  	= ($notes_type == 1 && ($CHANGE_IN == 2 || $CHANGE_IN == 3)) ?  $Qty + $ReviseQty : $NewQty;
							$CHARGE_DATA = InvoiceAdditionalCharges::where("invoice_id",$InvoiceID)
							->where("client_charges_id",$CHARGE_ID)
							->first();
							$CGST_RATE 	= ($CHARGE_DATA) ? $CHARGE_DATA->cgst : 0;
							$SGST_RATE 	= ($CHARGE_DATA) ? $CHARGE_DATA->sgst : 0;
							$IGST_RATE 	= ($CHARGE_DATA) ? $CHARGE_DATA->igst : 0;
							$GstAmount 	= ($CHARGE_DATA) ? $CHARGE_DATA->igst : 0;
							$NetAmount 	= ($CHARGE_DATA) ? $CHARGE_DATA->igst : 0;
							$Qty 		= ($CHARGE_DATA) ? $CHARGE_DATA->totalqty : 0;
							$Rate 		= ($CHARGE_DATA) ? $CHARGE_DATA->rate : 0;
							$GrossAmount= ($CHARGE_DATA) ? $CHARGE_DATA->gross_amount : 0;
							$NetAmount 	= ($CHARGE_DATA) ? $CHARGE_DATA->net_amount : 0;
							$GstAmount 	= ($CHARGE_DATA) ? $CHARGE_DATA->gst_amount : 0;
							
							$NewRate = ($notes_type == 1 && ($CHANGE_IN == 1 || $CHANGE_IN == 3)) ?  $Rate + $ReviseRate : $NewRate;
							$NewQty = ($notes_type == 1 && ($CHANGE_IN == 2 || $CHANGE_IN == 3)) ?  $Qty + $ReviseQty : $NewQty;
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
							if($FROM_SAME_STATE) {
								$cgst_amt 		= ($CGST_RATE > 0) ? (($RevisedGrossAmount / 100) * $CGST_RATE):0;
								$sgst_amt 		= ($SGST_RATE > 0) ? (($RevisedGrossAmount / 100) * $SGST_RATE):0;
								$RevisedGstAmt 	= $cgst_amt + $sgst_amt;
							}else{
								$igst_amt 	= ($IGST_RATE > 0) ? (( $RevisedGrossAmount / 100) * $IGST_RATE):0;
								$RevisedGstAmt 	= $igst_amt;
							}
							$RevisedNetAmount 				= $RevisedGrossAmount + $RevisedGstAmt;
							$INSERT 						= new WmInvoicesCreditDebitNotesChargesDetails();
							$INSERT->cd_notes_id 			= $ID;
							$INSERT->change_in 				= $CHANGE_IN;
							$INSERT->charge_id 				= $CHARGE_ID;
							$INSERT->cgst_rate 				= $CGST_RATE;
							$INSERT->sgst_rate 				= $SGST_RATE;
							$INSERT->igst_rate 				= $IGST_RATE;
							$INSERT->quantity 				= $Qty;
							$INSERT->revised_quantity 		= $ReviseQty;
							$INSERT->revised_rate 			= $ReviseRate;
							$INSERT->new_quantity 			= $NewQty;
							$INSERT->new_rate 				= $NewRate;
							$INSERT->rate 					= $Rate;
							$INSERT->gst_amount 			= $GstAmount;
							$INSERT->net_amount 			= $NetAmount;
							$INSERT->gross_amount 			= $GrossAmount;
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
				############# ADDITIONAL CHARGES IN CREDIT DEBIT NOTE #######################
				
			}
		}
		return $ID;
	}

	/*
	use 	: Credit Debit Report
	Author 	: Axay Shah
	Date 	: 19 Octomber,2020
	*/
	public static function CreditDebitNoteReport($request)
	{
		$self 					= (new static)->getTable();
		$AdminUser  			= new AdminUser();
		$Dispatch  				= new WmDispatch();
		$Department  			= new WmDepartment();
		$SalesMaster 			= new WmSalesMaster();
		$Product 				= new WmProductMaster();
		$Invoice 				= new WmInvoices();
		$Client 				= new WmClientMaster();
		$Details 				= new WmInvoicesCreditDebitNotesDetails();
		$Today          		= date('Y-m-d');
		$sortBy         		= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$self.id";
		$sortOrder      		= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  		= !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     		= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';

		$cityId         		= GetBaseLocationCity();
		$data 					= self::select("$self.*",
										\DB::raw("DATE_FORMAT($self.change_date,'%Y-%m-%d') AS created_at"),
										\DB::raw("(CASE WHEN DETAILS.change_in = 0 THEN '-'
													WHEN DETAILS.change_in = 1 THEN 'Rate'
													WHEN DETAILS.change_in = 2 THEN 'Quantity'
													WHEN DETAILS.change_in = 3 THEN 'Rate & Quantity'
											END ) AS change_in_name"),
										\DB::raw("(CASE
														WHEN PRODUCT.is_afr = 1 THEN 'AFR'
														WHEN DISPATCH.aggregator_dispatch = 1 AND DISPATCH.virtual_target = 0 THEN 'CFM'
														WHEN DISPATCH.aggregator_dispatch = 0 AND DISPATCH.virtual_target = 1 THEN 'PAID'
													ELSE 'MRF'
										END ) AS dispatch_consider_in"),
										\DB::raw("(CASE WHEN $self.status = 0 THEN 'Pending'
													WHEN $self.status = 1 THEN 'First Level Approved'
													WHEN $self.status = 3 THEN 'Approved'
													WHEN $self.status = 2 THEN 'Rejected'
										END ) AS status_name"),
										\DB::raw("(CASE WHEN $self.notes_type = 0 THEN 'Credit'
													WHEN $self.notes_type = 1 THEN 'Debit'
											END ) AS note_type_name"),
										\DB::raw("INV.invoice_date"),
										\DB::raw("PRODUCT.net_suit_code"),
										\DB::raw("DETAILS.product_id"),
										\DB::raw("DETAILS.rate"),
										\DB::raw("DETAILS.revised_rate"),
										\DB::raw("DETAILS.quantity"),
										\DB::raw("DETAILS.revised_quantity"),
										\DB::raw("DETAILS.cgst_rate"),
										\DB::raw("DETAILS.sgst_rate"),
										\DB::raw("DETAILS.igst_rate"),
										\DB::raw("DETAILS.gst_amount"),
										\DB::raw("DETAILS.net_amount"),
										\DB::raw("DETAILS.revised_gross_amount"),
										\DB::raw("DETAILS.revised_gst_amount"),
										\DB::raw("DETAILS.revised_net_amount"),
										\DB::raw("DETAILS.is_from_same_state"),
										\DB::raw("CONCAT(DEPT.department_name) as department_name"),
										\DB::raw("BILL.department_name as bill_from_mrf_name"),
										\DB::raw("CLIENT.client_name"),
										\DB::raw("CLIENT.gstin_no"),
										\DB::raw("PRODUCT.title as product_name"),
										\DB::raw("PRODUCT.hsn_code"),
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
										\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) as approved_by_name"),
										\DB::raw("CONCAT(U4.firstname,' ',U4.lastname) as first_level_approved_by_name"))
									->join($Details->getTable()." as DETAILS","$self.id","=","DETAILS.cd_notes_id")
									->join($Dispatch->getTable()." as DISPATCH","$self.dispatch_id","=","DISPATCH.id")
									->leftjoin($Department->getTable()." as DEPT","$self.mrf_id","=","DEPT.id")
									->leftjoin($Department->getTable()." as BILL","$self.bill_from_mrf_id","=","BILL.id")
									->leftjoin($Invoice->getTable()." as INV","$self.invoice_id","=","INV.id")
									->leftjoin($Product->getTable()." as PRODUCT","DETAILS.product_id","=","PRODUCT.id")
									->leftjoin($Client->getTable()." as CLIENT","DISPATCH.destination","=","CLIENT.id")
									->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid")
									->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid")
									->leftjoin($AdminUser->getTable()." as U3","$self.approved_by","=","U3.adminuserid")
									->leftjoin($AdminUser->getTable()." as U4","$self.first_level_approved_by","=","U4.adminuserid");
		if($request->has('virtual_target')) {
			if($request->virtual_target == "0" || !empty($request->virtual_target)){
				$data->where("DISPATCH.virtual_target",$request->virtual_target);
			}
		}
		if($request->has('dispatch_id') && !empty($request->input('dispatch_id'))) {
			$id 	= $request->input('dispatch_id');
			if(!is_array($request->input('dispatch_id'))){
				$id = explode(",",$request->input("dispatch_id"));
			}
			$data->where("$self.dispatch_id",$id);
		}
		if($request->has('status')) {
			if($request->status == "0" || !empty($request->status)){
				$data->where("$self.status",$request->status);
			}
		}
		if($request->has('bill_from_mrf_id') && !empty($request->input('bill_from_mrf_id'))) {
			$data->where("$self.bill_from_mrf_id",$request->input('bill_from_mrf_id'));
		}
		if($request->has('challan_no') && !empty($request->input('challan_no'))) {
			$data->where("DISPATCH.challan_no","like","%".$request->input('challan_no')."%");
		}
		if($request->has('client_id') && !empty($request->input('client_id'))) {
			$data->where("CLIENT.id",$request->input('client_id'));
		}
		if($request->has('invoice_no') && !empty($request->input('invoice_no'))) {
			$data->where("$self.invoice_no","like","%".$request->input('invoice_no')."%");
		}
		if($request->has('net_suit_code') && !empty($request->input('net_suit_code'))) {
			$data->where("PRODUCT.net_suit_code","like","%".$request->input('net_suit_code')."%");
		}
		if($request->has('mrf_id') && !empty($request->input('mrf_id'))) {
			$mrf_id = (!is_array($request->mrf_id)) ? explode(",",$request->mrf_id) : $request->mrf_id;
			$data->whereIn("$self.mrf_id",$mrf_id);
		}
		if($request->has('product_id') && !empty($request->input('product_id'))) {
			$data->where("DETAILS.product_id",$request->input('product_id'));
		}
		if($request->has('notes_type')) {
			if($request->input('notes_type') == "0") {
				$data->where("$self.notes_type",$request->input('notes_type'));
			} else if($request->input('notes_type') == "1") {
				$data->where("$self.notes_type",$request->input('notes_type'));
			}
		}
		if($request->has('change_in')) {
			if($request->input('change_in') == "0") {
				$data->where("$self.change_in",$request->input('change_in'));
			} else if($request->input('change_in') == "1") {
				$data->where("$self.change_in",$request->input('change_in'));
			}
		}
		if(!empty($request->input('startDate')) && !empty($request->input('endDate'))) {
			$startDate 	= date("Y-m-d",strtotime($request->input('startDate')))." ".GLOBAL_START_TIME;
			$endDate 	= date("Y-m-d",strtotime($request->input('endDate')))." ".GLOBAL_END_TIME;
			$data->whereBetween("$self.approved_date",array(date("Y-m-d", strtotime($startDate)),date("Y-m-d",strtotime($endDate))));
		} else if(!empty($request->input('startDate'))) {
			$startDate 	= date("Y-m-d",strtotime($request->input('startDate')))." ".GLOBAL_START_TIME;
			$endDate 	= date("Y-m-d",strtotime($request->input('startDate')))." ".GLOBAL_END_TIME;
		   	$data->whereBetween("$self.approved_date",array(date("Y-m-d", strtotime($startDate)),date("Y-m-d",strtotime($endDate))));
		} else if(!empty($request->input('endDate'))) {
			$startDate 	= date("Y-m-d",strtotime($request->input('endDate')))." ".GLOBAL_START_TIME;
			$endDate 	= date("Y-m-d",strtotime($request->input('endDate')))." ".GLOBAL_END_TIME;
		   $data->whereBetween("$self.approved_date",array(date("Y-m-d", strtotime($startDate)),date("Y-m-d",strtotime($endDate))));
		}
		if($request->has('is_einvoice')) {
			 $is_einvoice = $request->input("is_einvoice");
			if($is_einvoice == "-1") {
				$data->whereNull("$self.ack_no");
			} else if($is_einvoice == "1") {
				$data->whereNotNull("$self.ack_no");
			}
		}
		if($request->has('ack_no') && !empty($request->input('ack_no'))) {
			$data->where("$self.ack_no",$request->input('ack_no'));
		}

		if($request->has('aggregator_value') && $request->input('aggregator_value') != "" && ($request->input('aggregator_value') == 1 OR $request->input('aggregator_value') == 0)) {
			if ($request->input('aggregator_value') == 1) {
				$data->havingRaw("dispatch_consider_in = 'PAID'");
			} else if ($request->input('aggregator_value') == 0) {
				$data->havingRaw("dispatch_consider_in != 'PAID'");
			}
		}
		$baseLocationMaster = UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id");
		$data->where(function($query) use($baseLocationMaster) {
			$query->whereIn("BILL.base_location_id",$baseLocationMaster);
		});
		$data->where("$self.company_id",Auth()->user()->company_id);
		$result 		= $data->orderBy($sortBy, $sortOrder)->get()->toArray();
		$TOTAL_ORG_QTY 	= 0;
		$TOTAL_CN_QTY 	= 0;
		$TOTAL_CN_GROSS = 0;
		$TOTAL_CN_GST 	= 0;
		$TOTAL_CN_NET 	= 0;
		$TOTAL_CGST_AMT	= 0;
		$TOTAL_SGST_AMT	= 0;
		$TOTAL_IGST_AMT	= 0;

		if(!empty($result)) {
			foreach($result as $key => $value) {
				$TOTAL_ORG_QTY 	+= $value['quantity'];
				$TOTAL_CN_QTY 	+= $value['revised_quantity'];
				$TOTAL_CN_GROSS += $value['revised_gross_amount'];
				$TOTAL_CN_GST 	+= $value['revised_gst_amount'];
				$TOTAL_CN_NET 	+= $value['revised_net_amount'];
				$CGST_AMT 		= 0;
				$SGST_AMT 		= 0;
				$IGST_AMT 		= 0;
				if (isset($value['is_from_same_state']) && $value['is_from_same_state'] == "Y") {
					$ARRAY[$i]['igst_rate'] = "0";
					$ARRAY[$i]['cgst_rate'] = $value['cgst_rate'];
					$ARRAY[$i]['sgst_rate'] = $value['sgst_rate'];
					$CGST_AMT 				= $value['revised_gst_amount'] / 2;
					$SGST_AMT 				= $value['revised_gst_amount'] / 2 ;
				} else {
					$ARRAY[$i]['igst_rate'] = $value['igst_rate'];
					$ARRAY[$i]['cgst_rate'] = "0";
					$ARRAY[$i]['sgst_rate'] = "0";
					$IGST_AMT 	= $value['revised_gst_amount'];
				}
				$ARRAY[$i]['cgst_amount'] = $CGST_AMT;
				$ARRAY[$i]['sgst_amount'] = $SGST_AMT;
				$ARRAY[$i]['igst_amount'] = $IGST_AMT;
				
				$TOTAL_CGST_AMT 		  += $CGST_AMT;
				$TOTAL_SGST_AMT 		  += $SGST_AMT;
				$TOTAL_IGST_AMT 		  += $IGST_AMT;
			}
		}
		$res["result"] 			= $result;
		$res["TOTAL_ORG_QTY"] 	= _FormatNumberV2($TOTAL_ORG_QTY);
		$res["TOTAL_CN_QTY"] 	= _FormatNumberV2($TOTAL_CN_QTY);
		$res["TOTAL_CN_GROSS"] 	= _FormatNumberV2($TOTAL_CN_GROSS);
		$res["TOTAL_CN_GST"] 	= _FormatNumberV2($TOTAL_CN_GST);
		$res["TOTAL_CN_NET"] 	= _FormatNumberV2($TOTAL_CN_NET);

		return $res;
	}

	/*
	use 	: Credit Debit Report
	Author 	: Axay Shah
	Date 	: 19 Octomber,2020
	*/
	public static function CreditDebitNoteReportV1($request)
	{
		$self 					= (new static)->getTable();
		$AdminUser  			= new AdminUser();
		$Dispatch  				= new WmDispatch();
		$Department  			= new WmDepartment();
		$SalesMaster 			= new WmSalesMaster();
		$Product 				= new WmProductMaster();
		$Invoice 				= new WmInvoices();
		$Client 				= new WmClientMaster();
		$Details 				= new WmInvoicesCreditDebitNotesDetails();
		$Today          		= date('Y-m-d');
		$sortBy         		= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$self.id";
		$sortOrder      		= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  		= !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     		= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';

		$cityId         		= GetBaseLocationCity();
		$data 					= self::select("$self.*",
										\DB::raw("DATE_FORMAT($self.change_date,'%Y-%m-%d') AS created_at"),
										\DB::raw("(CASE WHEN DETAILS.change_in = 0 THEN '-'
													WHEN DETAILS.change_in = 1 THEN 'Rate'
													WHEN DETAILS.change_in = 2 THEN 'Quantity'
													WHEN DETAILS.change_in = 3 THEN 'Rate & Quantity'
											END ) AS change_in_name"),
										\DB::raw("(CASE WHEN $self.status = 0 THEN 'Pending'
													WHEN $self.status = 1 THEN 'First Level Approved'
													WHEN $self.status = 3 THEN 'Approved'
													WHEN $self.status = 2 THEN 'Rejected'
										END ) AS status_name"),
										\DB::raw("(CASE 
														WHEN PRODUCT.is_afr = 1 THEN 'AFR'
														WHEN DISPATCH.aggregator_dispatch = 1 AND DISPATCH.virtual_target = 0 THEN 'PAID'
														WHEN DISPATCH.aggregator_dispatch = 0 AND DISPATCH.virtual_target = 1 THEN 'CFM'
													ELSE 'MRF'
										END ) AS dispatch_consider_in"),
										\DB::raw("(CASE WHEN $self.notes_type = 0 THEN 'Credit'
													WHEN $self.notes_type = 1 THEN 'Debit'
											END ) AS note_type_name"),
										\DB::raw("INV.invoice_date"),
										\DB::raw("PRODUCT.net_suit_code"),
										\DB::raw("DETAILS.product_id"),
										\DB::raw("DETAILS.rate"),
										\DB::raw("DETAILS.revised_rate"),
										\DB::raw("DETAILS.quantity"),
										\DB::raw("DETAILS.revised_quantity"),
										\DB::raw("DETAILS.cgst_rate"),
										\DB::raw("DETAILS.sgst_rate"),
										\DB::raw("DETAILS.igst_rate"),
										\DB::raw("DETAILS.gst_amount"),
										\DB::raw("DETAILS.net_amount"),
										\DB::raw("DETAILS.revised_gross_amount"),
										\DB::raw("DETAILS.revised_gst_amount"),
										\DB::raw("DETAILS.revised_net_amount"),
										\DB::raw("DETAILS.is_from_same_state"),
										\DB::raw("CONCAT(DEPT.department_name) as department_name"),
										\DB::raw("BILL.department_name as bill_from_mrf_name"),
										\DB::raw("CLIENT.client_name"),
										\DB::raw("CLIENT.gstin_no"),
										\DB::raw("PRODUCT.title as product_name"),
										\DB::raw("PRODUCT.hsn_code"),
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
										\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) as approved_by_name"),
										\DB::raw("CONCAT(U4.firstname,' ',U4.lastname) as first_level_approved_by_name"))
									->join($Details->getTable()." as DETAILS","$self.id","=","DETAILS.cd_notes_id")
									->join($Dispatch->getTable()." as DISPATCH","$self.dispatch_id","=","DISPATCH.id")
									->leftjoin($Department->getTable()." as DEPT","$self.mrf_id","=","DEPT.id")
									->leftjoin($Department->getTable()." as BILL","$self.bill_from_mrf_id","=","BILL.id")
									->leftjoin($Invoice->getTable()." as INV","$self.invoice_id","=","INV.id")
									->leftjoin($Product->getTable()." as PRODUCT","DETAILS.product_id","=","PRODUCT.id")
									->leftjoin($Client->getTable()." as CLIENT","DISPATCH.destination","=","CLIENT.id")
									->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid")
									->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid")
									->leftjoin($AdminUser->getTable()." as U3","$self.approved_by","=","U3.adminuserid")
									->leftjoin($AdminUser->getTable()." as U4","$self.first_level_approved_by","=","U4.adminuserid");
		if($request->has('virtual_target')) {
			if($request->virtual_target == "0" || !empty($request->virtual_target)){
				$data->where("DISPATCH.virtual_target",$request->virtual_target);
			}
		}
		if($request->has('dispatch_id') && !empty($request->input('dispatch_id'))) {
			$id 	= $request->input('dispatch_id');
			if(!is_array($request->input('dispatch_id'))){
				$id = explode(",",$request->input("dispatch_id"));
			}
			$data->where("$self.dispatch_id",$id);
		}
		if($request->has('status')) {
			if($request->status == "0" || !empty($request->status)){
				$data->where("$self.status",$request->status);
			}
		}
		if($request->has('bill_from_mrf_id') && !empty($request->input('bill_from_mrf_id'))) {
			$data->where("$self.bill_from_mrf_id",$request->input('bill_from_mrf_id'));
		}
		if($request->has('challan_no') && !empty($request->input('challan_no'))) {
			$data->where("DISPATCH.challan_no","like","%".$request->input('challan_no')."%");
		}
		if($request->has('serial_no') && !empty($request->input('serial_no'))) {
			$data->where("$self.serial_no","like","%".$request->input('serial_no')."%");
		}
		if($request->has('client_id') && !empty($request->input('client_id'))) {
			$data->where("CLIENT.id",$request->input('client_id'));
		}
		if($request->has('invoice_no') && !empty($request->input('invoice_no'))) {
			$data->where("$self.invoice_no","like","%".$request->input('invoice_no')."%");
		}
		if($request->has('net_suit_code') && !empty($request->input('net_suit_code'))) {
			$data->where("PRODUCT.net_suit_code","like","%".$request->input('net_suit_code')."%");
		}
		if($request->has('mrf_id') && !empty($request->input('mrf_id'))) {
			$mrf_id = (!is_array($request->mrf_id)) ? explode(",",$request->mrf_id) : $request->mrf_id;
			$data->whereIn("$self.mrf_id",$mrf_id);
		}
		if($request->has('product_id') && !empty($request->input('product_id'))) {
			$data->where("DETAILS.product_id",$request->input('product_id'));
		}
		if($request->has('notes_type')) {
			if($request->input('notes_type') == "0") {
				$data->where("$self.notes_type",$request->input('notes_type'));
			} else if($request->input('notes_type') == "1") {
				$data->where("$self.notes_type",$request->input('notes_type'));
			}
		}
		if($request->has('change_in')) {
			if($request->input('change_in') == "0") {
				$data->where("$self.change_in",$request->input('change_in'));
			} else if($request->input('change_in') == "1") {
				$data->where("$self.change_in",$request->input('change_in'));
			}
		}
		if(!empty($request->input('startDate')) && !empty($request->input('endDate'))) {
			$startDate 	= date("Y-m-d",strtotime($request->input('startDate')))." ".GLOBAL_START_TIME;
			$endDate 	= date("Y-m-d",strtotime($request->input('endDate')))." ".GLOBAL_END_TIME;
			$data->whereBetween("$self.approved_date",array($startDate,$endDate));
		} else if(!empty($request->input('startDate'))) {
			$startDate 	= date("Y-m-d",strtotime($request->input('startDate')))." ".GLOBAL_START_TIME;
			$endDate 	= date("Y-m-d",strtotime($request->input('startDate')))." ".GLOBAL_END_TIME;
		   	$data->whereBetween("$self.approved_date",array($startDate,$endDate));
		} else if(!empty($request->input('endDate'))) {
			$startDate 	= date("Y-m-d",strtotime($request->input('endDate')))." ".GLOBAL_START_TIME;
			$endDate 	= date("Y-m-d",strtotime($request->input('endDate')))." ".GLOBAL_END_TIME;
		   $data->whereBetween("$self.approved_date",array($startDate,$endDate));
		}
		if($request->has('is_einvoice')) {
			 $is_einvoice = $request->input("is_einvoice");
			if($is_einvoice == "-1") {
				$data->whereNull("$self.ack_no");
			} else if($is_einvoice == "1") {
				$data->whereNotNull("$self.ack_no");
			}
		}
		if($request->has('ack_no') && !empty($request->input('ack_no'))) {
			$data->where("$self.ack_no",$request->input('ack_no'));
		}

		if($request->has('aggregator_value') && $request->input('aggregator_value') != "" && ($request->input('aggregator_value') == 1 OR $request->input('aggregator_value') == 0)) {
			if ($request->input('aggregator_value') == 1) {
				$data->havingRaw("dispatch_consider_in = 'PAID'");
			} else if ($request->input('aggregator_value') == 0) {
				$data->havingRaw("dispatch_consider_in != 'PAID'");
			}
		}
		$baseLocationMaster = UserBaseLocationMapping::where("adminuserid",Auth()->user()->adminuserid)->pluck("base_location_id");
		$data->where(function($query) use($baseLocationMaster) {
			$query->whereIn("BILL.base_location_id",$baseLocationMaster);
		});
		$data->where("$self.company_id",Auth()->user()->company_id);
		$result 		= $data->orderBy($sortBy, $sortOrder)->get()->toArray();
		$TOTAL_ORG_QTY 	= 0;
		$TOTAL_CN_QTY 	= 0;
		$TOTAL_CN_GROSS = 0;
		$TOTAL_CN_GST 	= 0;
		$TOTAL_CN_NET 	= 0;
		$TOTAL_CGST_AMT	= 0;
		$TOTAL_SGST_AMT	= 0;
		$TOTAL_IGST_AMT	= 0;
		$i 				= 0;
		$ARRAY 			= array();
		$CN_DN_ARRAY 	= array();
		if(!empty($result)) {
			foreach($result as $key => $value) {
				$incrementFlag 	=  true;
				$ARRAY[$i] 		=  $value;
				$TOTAL_ORG_QTY 	+= $value['quantity'];
				$TOTAL_CN_QTY 	+= $value['revised_quantity'];
				$TOTAL_CN_GROSS += $value['revised_gross_amount'];
				$TOTAL_CN_GST 	+= $value['revised_gst_amount'];
				$TOTAL_CN_NET 	+= $value['revised_net_amount'];
				$CGST_AMT 		= 0;
				$SGST_AMT 		= 0;
				$IGST_AMT 		= 0;
				if (isset($value['is_from_same_state']) && $value['is_from_same_state'] == "Y") {
					$ARRAY[$i]['igst_rate'] = "0";
					$ARRAY[$i]['cgst_rate'] = $value['cgst_rate'];
					$ARRAY[$i]['sgst_rate'] = $value['sgst_rate'];
					$CGST_AMT 				= $value['revised_gst_amount'] / 2;
					$SGST_AMT 				= $value['revised_gst_amount'] / 2 ;
				} else {
					$ARRAY[$i]['igst_rate'] = $value['igst_rate'];
					$ARRAY[$i]['cgst_rate'] = "0";
					$ARRAY[$i]['sgst_rate'] = "0";
					$IGST_AMT 				= $value['revised_gst_amount'];
				}
				$ARRAY[$i]['cgst_amount'] = $CGST_AMT;
				$ARRAY[$i]['sgst_amount'] = $SGST_AMT;
				$ARRAY[$i]['igst_amount'] = $IGST_AMT;
				$TOTAL_CGST_AMT 		  += $CGST_AMT;
				$TOTAL_SGST_AMT 		  += $SGST_AMT;
				$TOTAL_IGST_AMT 		  += $IGST_AMT;
				####### CHARGES DETAILS #######
				if(!in_array($value['id'],$CN_DN_ARRAY)){
					$GET_CAHRGE = WmInvoicesCreditDebitNotesChargesDetails::GetCnDnChargeDetails($value['id']);
					if(!empty($GET_CAHRGE)){
						foreach($GET_CAHRGE AS $CHA_KEY => $CHA_VAL){
							$i++;
							$incrementFlag 						= false;
							$ARRAY[$i] 							= $value;
							$ARRAY[$i]['is_from_same_state'] 	= $CHA_VAL['is_from_same_state'];
							$ARRAY[$i]['product_name'] 			= $CHA_VAL['product_name'];
							$ARRAY[$i]['product_id'] 			= $CHA_VAL['product_id'];
							$ARRAY[$i]['change_in_name'] 		= $CHA_VAL['change_in_name'];
							$ARRAY[$i]['rate'] 					= $CHA_VAL['rate'];
							$ARRAY[$i]['revised_rate'] 			= $CHA_VAL['revised_rate'];
							$ARRAY[$i]['quantity'] 				= $CHA_VAL['quantity'];
							$ARRAY[$i]['revised_quantity'] 		= $CHA_VAL['revised_quantity'];
							$ARRAY[$i]['cgst_rate'] 			= $CHA_VAL['cgst_rate'];
							$ARRAY[$i]['sgst_rate'] 			= $CHA_VAL['sgst_rate'];
							$ARRAY[$i]['igst_rate'] 			= $CHA_VAL['igst_rate'];
							$ARRAY[$i]['gst_amount'] 			= $CHA_VAL['gst_amount'];
							$ARRAY[$i]['net_amount'] 			= $CHA_VAL['net_amount'];
							$ARRAY[$i]['revised_gst_amount'] 	= $CHA_VAL['revised_gst_amount'];
							$ARRAY[$i]['revised_gross_amount'] 	= $CHA_VAL['revised_gross_amount'];
							$ARRAY[$i]['revised_net_amount'] 	= $CHA_VAL['revised_net_amount'];
							$ARRAY[$i]['net_suit_code'] 		= $CHA_VAL['net_suit_code'];
							$ARRAY[$i]['hsn_code'] 				= $CHA_VAL['hsn_code'];
							$TOTAL_ORG_QTY 	+= $CHA_VAL['quantity'];
							$TOTAL_CN_QTY 	+= $CHA_VAL['revised_quantity'];
							$TOTAL_CN_GROSS += $CHA_VAL['revised_gross_amount'];
							$TOTAL_CN_GST 	+= $CHA_VAL['revised_gst_amount'];
							$TOTAL_CN_NET 	+= $CHA_VAL['revised_net_amount'];
							if ($CHA_VAL['is_from_same_state'] == "Y") {
								$ARRAY[$i]['igst_rate'] = "-";
								$ARRAY[$i]['cgst_rate'] = $CHA_VAL['cgst_rate'];
								$ARRAY[$i]['sgst_rate'] = $CHA_VAL['sgst_rate'];
							} else {
								$ARRAY[$i]['igst_rate'] = $CHA_VAL['igst_rate'];
								$ARRAY[$i]['cgst_rate'] = "-";
								$ARRAY[$i]['sgst_rate'] = "-";
							}
						}
					}
					####### FRIGHT DETAILS #######
					$GET_FRIGHT = WmInvoicesCreditDebitNotesFrieghtDetails::GetFrightDetails($value['id']);
					if(!empty($GET_FRIGHT)){
						foreach($GET_FRIGHT AS $FRE_KEY => $FRE_VAL){
							$i++;
							$incrementFlag 						= false;
							$ARRAY[$i] 							= $value;
							$ARRAY[$i]['product_name'] 			= $FRE_VAL['product_name'];
							$ARRAY[$i]['revised_rate'] 			= 0;
							$ARRAY[$i]['quantity'] 				= 0;
							$ARRAY[$i]['revised_quantity'] 		= 0;
							$ARRAY[$i]['rate'] 					= 0;
							$ARRAY[$i]['cgst_rate'] 			= $FRE_VAL['cgst_rate'];
							$ARRAY[$i]['sgst_rate'] 			= $FRE_VAL['sgst_rate'];
							$ARRAY[$i]['igst_rate'] 			= $FRE_VAL['igst_rate'];
							$ARRAY[$i]['gross_amount'] 			= $FRE_VAL['gross_amount'];
							$ARRAY[$i]['gst_amount'] 			= $FRE_VAL['gst_amount'];
							$ARRAY[$i]['net_amount'] 			= $FRE_VAL['net_amount'];
							$ARRAY[$i]['revised_gst_amount'] 	= $FRE_VAL['gst_amount'];
							$ARRAY[$i]['revised_gross_amount'] 	= $FRE_VAL['gross_amount'];
							$ARRAY[$i]['revised_net_amount'] 	= $FRE_VAL['net_amount'];
							$ARRAY[$i]['net_suit_code'] 		= $FRE_VAL['net_suit_code'];
							$ARRAY[$i]['hsn_code'] 				= $FRE_VAL['hsn_code'];
							$TOTAL_ORG_QTY 	+= 0;
							$TOTAL_CN_QTY 	+= 0;
							$TOTAL_CN_GROSS += $FRE_VAL['gross_amount'];
							$TOTAL_CN_GST 	+= $FRE_VAL['gst_amount'];
							$TOTAL_CN_NET 	+= $FRE_VAL['net_amount'];
							if ($value['is_from_same_state'] == "Y") {
								$ARRAY[$i]['igst_rate'] = "-";
								$ARRAY[$i]['cgst_rate'] = $FRE_VAL['cgst_rate'];
								$ARRAY[$i]['sgst_rate'] = $FRE_VAL['sgst_rate'];
							} else {
								$ARRAY[$i]['igst_rate'] = $FRE_VAL['igst_rate'];
								$ARRAY[$i]['cgst_rate'] = "-";
								$ARRAY[$i]['sgst_rate'] = "-";
							}
						}
					}

					################### TCS AMOUNT DETAILS ##########
					
					
					if(!empty($value['tcs_amount'] > 0)){
						$i++;
						$incrementFlag 						= false;
						$ARRAY[$i] 							= $value;
						$ARRAY[$i]['product_name'] 			= "TCS Charge";
						$ARRAY[$i]['revised_rate'] 			= 0;
						$ARRAY[$i]['quantity'] 				= 0;
						$ARRAY[$i]['revised_quantity'] 		= 0;
						$ARRAY[$i]['rate'] 					= 0;
						$ARRAY[$i]['cgst_rate'] 			= 0;
						$ARRAY[$i]['sgst_rate'] 			= 0;
						$ARRAY[$i]['igst_rate'] 			= 0;
						$ARRAY[$i]['gross_amount'] 			= 0;
						$ARRAY[$i]['gst_amount'] 			= 0;
						$ARRAY[$i]['net_amount'] 			= 0;
						$ARRAY[$i]['revised_gst_amount'] 	= 0;
						$ARRAY[$i]['revised_gross_amount'] 	= 0;
						$ARRAY[$i]['revised_net_amount'] 	= $value['tcs_amount'];
						$ARRAY[$i]['net_suit_code'] 		= "";
						$ARRAY[$i]['hsn_code'] 				= "";
						$TOTAL_ORG_QTY 	+= 0;
						$TOTAL_CN_QTY 	+= 0;
						$TOTAL_CN_GROSS += 0;
						$TOTAL_CN_GST 	+= 0;
						$TOTAL_CN_NET 	+= $value['tcs_amount'];;
						if ($value['is_from_same_state'] == "Y") {
							$ARRAY[$i]['igst_rate'] = "-";
							$ARRAY[$i]['cgst_rate'] = 0;
							$ARRAY[$i]['sgst_rate'] = 0;
						} else {
							$ARRAY[$i]['igst_rate'] = 0;
							$ARRAY[$i]['cgst_rate'] = "-";
							$ARRAY[$i]['sgst_rate'] = "-";
						}
					}
					################## TCS AMOUNT DETAILS ###########
				}
				array_push($CN_DN_ARRAY,$value['id']);
				$i++;
			}
		}
		$res["result"] 			= $ARRAY;
		$res["TOTAL_ORG_QTY"] 	= _FormatNumberV2($TOTAL_ORG_QTY);
		$res["TOTAL_CN_QTY"] 	= _FormatNumberV2($TOTAL_CN_QTY);
		$res["TOTAL_CN_GROSS"] 	= _FormatNumberV2($TOTAL_CN_GROSS);
		$res["TOTAL_CN_GST"] 	= _FormatNumberV2($TOTAL_CN_GST);
		$res["TOTAL_CN_NET"] 	= _FormatNumberV2($TOTAL_CN_NET);
		$res['TOTAL_CGST_AMT'] 	= _FormatNumberV2($TOTAL_CGST_AMT);
		$res['TOTAL_SGST_AMT'] 	= _FormatNumberV2($TOTAL_SGST_AMT);
		$res['TOTAL_IGST_AMT'] 	= _FormatNumberV2($TOTAL_IGST_AMT);
		return $res;
	}
	/*
	use 	: Credit Debit Report
	Author 	: Axay Shah
	Date 	: 19 Octomber,2020
	*/
	public static function ListCreditNotes($request)
	{
		$self 			= (new static)->getTable();
		$AdminUser 		= new AdminUser();
		$Dispatch 		= new WmDispatch();
		$Department 	= new WmDepartment();
		$SalesMaster 	= new WmSalesMaster();
		$Product 		= new WmProductMaster();
		$Client 		= new WmClientMaster();
		$Details 		= new WmInvoicesCreditDebitNotesDetails();
		$DetailsTbl 	= $Details->getTable();
		$Today 			= date('Y-m-d');
		$sortBy 		= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$self.id";
		$sortOrder 		= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage 	= !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber 	= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$MRF_ID 		= !empty($request->input('mrf_id'))?$request->input('mrf_id'):0;
		$FROM_WIDGET 	= !empty($request->input('from_widget'))?$request->input('from_widget'):0;
		$cityId 		= GetBaseLocationCity();
		$data 			= self::with(['crn_document'])->select("$self.*",
																\DB::raw("(CASE WHEN $self.notes_type = 0 THEN 'Credit'
																				WHEN $self.notes_type = 1 THEN 'Debit'
																		END ) AS note_type_name"),
																\DB::raw("(CASE WHEN $self.status = 0 THEN 'Pending'
																				WHEN $self.status = 1 THEN 'First Level Approved'
																				WHEN $self.status = 3 THEN 'Approved'
																				WHEN $self.status = 2 THEN 'Rejected'
																	END ) AS status_name"),
																	\DB::raw("DEPT.department_name"),
																	\DB::raw("DISPATCH.dispatch_type"),
																	\DB::raw("CLIENT.client_name"),
																	\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
																	\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
																	\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) as approved_by_name"),
																	\DB::raw("CONCAT(U4.firstname,' ',U4.lastname) as first_level_approved_by_name"))
		->leftjoin($Department->getTable()." as DEPT","$self.mrf_id","=","DEPT.id")
		->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid")
		->leftjoin($Dispatch->getTable()." as DISPATCH","$self.dispatch_id","=","DISPATCH.id")
		->leftjoin($Client->getTable()." as CLIENT","DISPATCH.destination","=","CLIENT.id")
		->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid")
		->leftjoin($AdminUser->getTable()." as U3","$self.approved_by","=","U3.adminuserid")
		->leftjoin($AdminUser->getTable()." as U4","$self.first_level_approved_by","=","U4.adminuserid");
		if($request->has('params.dispatch_id') && !empty($request->input('params.dispatch_id'))) {
			$id 	= $request->input('params.dispatch_id');
			if(!is_array($request->input('params.dispatch_id'))){
				$id = explode(",",$request->input("params.dispatch_id"));
			}
			$data->where("$self.dispatch_id",$id);
		}
		if($request->has('params.serial_no') && !empty($request->input('params.serial_no'))) {
			$data->where("$self.serial_no","like","%".$request->input('params.serial_no')."%");
		}
		if($request->has('params.client_id') && !empty($request->input('params.client_id'))) {
			$data->where("DISPATCH.destination",$request->input('params.client_id'));
		}
		if($request->has('params.invoice_no') && !empty($request->input('params.invoice_no'))) {
			$data->where("$self.invoice_no","like","%".$request->input('params.invoice_no')."%");
		}
		if($request->has('params.mrf_id') && !empty($request->input('params.mrf_id'))) {
			$data->where("$self.mrf_id",$request->input('params.mrf_id'));
		}
		if($request->has('params.product_id') && !empty($request->input('params.product_id'))) {
			$data->where("DETAILS.product_id",$request->input('params.product_id'));
		}
		if($request->has('params.from_widget') && $request->input('params.from_widget') == 1) {
			$data->whereIn("$self.status",array(0,1));
		}
		if($request->has('params.status')) {
			if($request->input('params.status') == "0") {
				$data->where("$self.status",$request->input('params.status'));
			} else if($request->input('params.status') == "1" || $request->input('params.status') == "2") {
				$data->where("$self.status",$request->input('params.status'));
			}
		}
		if($request->has('params.notes_type')) {
			if($request->input('params.notes_type') == "0") {
				$data->where("$self.notes_type",$request->input('params.notes_type'));
			} else if($request->input('params.notes_type') == "1") {
				$data->where("$self.notes_type",$request->input('params.notes_type'));
			}
		}
		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate'))) {
			$data->whereBetween("$self.change_date",array(date("Y-m-d", strtotime($request->input('params.startDate'))),date("Y-m-d",strtotime($request->input('params.endDate')))));
		} else if(!empty($request->input('params.startDate'))) {
		   $datefrom = date("Y-m-d", strtotime($request->input('params.startDate')));
		   $data->whereBetween("$self.change_date",array($datefrom,$datefrom));
		} else if(!empty($request->input('params.endDate'))) {
		   $data->whereBetween("$self.change_date",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}
		if (empty($MRF_ID) && empty($FROM_WIDGET)) {
			$data->where(function($query) use($cityId) {
				$query->whereIn("DEPT.location_id",$cityId);
			});
		} else {
			if (!empty($MRF_ID)) {
				$query->whereIn("DEPT.id",$MRF_ID);
			} else if (!empty($FROM_WIDGET) && !empty($cityId)) {
				$query->whereIn("DEPT.location_id",$cityId);
			} else {
				$query->whereIn("DEPT.id",0);
			}
		}
		$data->where("$self.company_id",Auth()->user()->company_id);
		if (empty($MRF_ID) && empty($FROM_WIDGET)) {
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);
		} else {
			$result =  $data->orderBy($sortBy, $sortOrder);
		}
		if(!empty($result)) {
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0) {
				foreach($toArray['result'] as $key => $value) {
					$NET_AMT_CNDN 									= WmInvoicesCreditDebitNotesDetails::where("cd_notes_id",$value['id'])->sum("net_amount");
					$toArray['result'][$key]['crn_doc_url'] 		= (!empty($value['crn_document'])) ? $value['crn_document']['original_name'] : "";
					$toArray['result'][$key]['invoice_url'] 		= ($value['status'] == 3) ? url("/credit-note-invoice")."/".passencrypt($value['id'])."/".passencrypt($value['invoice_id']) : "";
					$toArray['result'][$key]['show_approval_menu'] 	= ($value['status'] == 0) ? true : false;
					$DetailsData 									= WmInvoicesCreditDebitNotesDetails::select(
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
																	\DB::raw("PRODUCT.title as product_name"),
																	\DB::raw("PRODUCT.hsn_code"))
																	->leftjoin($Product->getTable()." as PRODUCT","$DetailsTbl.product_id","=","PRODUCT.id")
																	->where("$DetailsTbl.cd_notes_id",$value['id'])
																	->get()
																	->toArray();
					$toArray['result'][$key]['credit_note_details'] =  $DetailsData;
					$TotalGSTAmount = 0;
					if(!empty($DetailsData)) {
						####### Invoice Additional Charges ############
						$AdditionalCharges = WmInvoicesCreditDebitNotesChargesDetails::GetCnDnChargeDetails($value['id']);
						if (!empty($AdditionalCharges)) {
							$counter = sizeof($DetailsData);
							$counter++;
							foreach ($AdditionalCharges as $AdditionalCharge) {
								$DetailsData[] = $AdditionalCharge;
								$counter++;
							}
						}

						foreach ($DetailsData as $Details) {
							$TotalGSTAmount +=	_FormatNumberV2($Details['gst_amount']);
						}

						####### Invoice Additional Charges ############
					}
					$toArray['result'][$key]['cancel_einvoice'] 	=  (!empty($value['irn'])) ?  1 : 0;
					$toArray['result'][$key]['generate_einvoice'] 	=  (empty($value['irn']) && $TotalGSTAmount > 0) ?  1 : 0;
					
					$DispatchApproved 		= WmDispatch::where("id",$value["dispatch_id"])->where("approval_status",1)->value("approved_by");
					$firstLevelApprovalFlag = 0;
					$finalLevelApprovalFlag = 0;
					$finalLevelApprovalFlag = self::GetRightsRoleWise(Auth()->user()->user_type,SALES_CN_DN_FINAL_LEVEL_APPROVAL);
					$firstLevelApprovalFlag = 0;
					if($value['status'] == 0 && $value["first_level_approved_by"] ==  Auth()->user()->adminuserid){
						$firstLevelApprovalFlag = self::GetRightsRoleWise(Auth()->user()->user_type,SALES_CN_DN_FIRST_LEVEL_APPROVAL);
					}elseif($value["status"] == 1) {
						$firstLevelApprovalFlag = 1;
					}
					$finalLevelApproval = ($firstLevelApprovalFlag == 1 && $finalLevelApprovalFlag > 0 && $value["status"] == 1 ) ? 1 : 0;
					$toArray['result'][$key]['invoice_download_flag'] 		=  (empty($value['irn']) && $NET_AMT_CNDN > 0 &&  $value['dispatch_type'] != NON_RECYCLEBLE_TYPE) ?  0 : 1;
					$toArray['result'][$key]['invoice_download_flag'] 		= ($value['change_date'] < "2022-02-01") ? 1 : $toArray['result'][$key]['invoice_download_flag'];
					$toArray['result'][$key]['first_level_approval_flag'] 	=  $firstLevelApprovalFlag;
					$toArray['result'][$key]['final_level_approval_flag'] 	=  $finalLevelApproval;
					$COLOR_RED 		= "red";
					$COLOR_GREEN 	= "green";
					// $toArray['result'][$key]['badge_ewaybill'] = "E";
					$toArray['result'][$key]['badge_einvoice'] = "EI";
					$toArray['result'][$key]['badge_color_einvoice'] = (empty($value['ack_no'])) ? $COLOR_RED : $COLOR_GREEN;
					// $toArray['result'][$key]['badge_color_ewaybill'] = (empty($value['eway_bill_no'])) 	? $COLOR_RED : $COLOR_GREEN;
				}
				$result = $toArray;
			}
		}
		return $result;
	}


	/*
	use 	: Update Approval status of Credit Note
	Author 	: Axay Shah
	Date 	: 03 Feb,2021
	*/
	public static function ApproveCreditNote($request,$ApprovedBy=0,$CompanyID=0)
	{
		$status 	= (isset($request['status']) && !empty($request['status'])) ? $request['status'] : 0;
		$Id 		= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : 0;
		$GetData 	= self::find($Id);
	
		if($GetData)
		{
			$GetData->status = $status;
			if($status == 1 || $status == 2) {
				$ApprovedBy = (!empty($ApprovedBy)?$ApprovedBy:Auth()->user()->adminuserid);
				$GetData->first_level_approved_by 		= (!empty($ApprovedBy)?$ApprovedBy:Auth()->user()->adminuserid);
				$GetData->first_level_approved_date 	= date("Y-m-d H:i:s");
				if ($status == 1) {
					$user_type 			= AdminUser::where("adminuserid",$ApprovedBy)->value("user_type");
					$IsSecondLevelUser 	= self::GetRightsRoleWise($user_type,SALES_CN_DN_FINAL_LEVEL_APPROVAL);
					if($IsSecondLevelUser > 0) {
						$status 				= 3;
						$GetData->status 		= $status;
						$GetData->approved_by 	= (!empty($ApprovedBy)?$ApprovedBy:Auth()->user()->adminuserid);
						$GetData->approved_date = date("Y-m-d H:i:s");
						$GetData->change_date 	= date("Y-m-d");
					}
				}
			} elseif($status ==  3 || $status == 2) {
				$GetData->approved_by 	= (!empty($ApprovedBy)?$ApprovedBy:Auth()->user()->adminuserid);
				$GetData->approved_date = date("Y-m-d H:i:s");
				$GetData->change_date 	= date("Y-m-d");
			}
			$GetData->save();
			if($status == 3) {
				$MRF_ID =   self::where("id",$Id)->value('bill_from_mrf_id');
				$Inward = 	WmInvoicesCreditDebitNotesDetails::
							where("cd_notes_id",$Id)
							->whereIn("change_in",array(CHANGE_IN_QTY,CHANGE_IN_BOTH))
							->where("inward_stock",1)
							->get()
							->toArray();
				if(!empty($Inward))
				{
					foreach($Inward as $value)
					{
						$QTY 		= ($value['revised_quantity'] > 0) ? $value['revised_quantity'] : 0;
						$PRODUCT_ID = $value['product_id'];
						############### NEW AVG PRICE CALCULATION LOGIC LIVE 20 JAN 2022 ####################
						$INWARD_AVG_PRICE = StockLadger::where("mrf_id",$MRF_ID)
											->where("product_id",$PRODUCT_ID)
											->where("stock_date",date("Y-m-d"))
											->where("product_type",PRODUCT_SALES)
											->value("avg_price");
					// $INWARD_AVG_PRICE 						= _FormatNumberV2($value['rate']);
						$InwardLedger 							= array();
						$InwardLedger['product_id'] 			= $PRODUCT_ID;
						$InwardLedger['production_report_id'] 	= 0;
						$InwardLedger['ref_id']					= $Id;
						$InwardLedger['avg_price']				= $INWARD_AVG_PRICE;
						$InwardLedger['quantity']				= $QTY;
						$InwardLedger['type']					= "I";
						$InwardLedger['remarks']				= "Credit Note No : ".$Id;
						$InwardLedger['product_type']			= PRODUCT_SALES;
						$InwardLedger['mrf_id']					= ($MRF_ID > 0) ? $MRF_ID : 0;
						$InwardLedger['company_id']				= (!empty($CompanyID)?$CompanyID:Auth()->user()->company_id);
						$InwardLedger['outward_date']			= date("Y-m-d");
						$InwardLedger['created_by']				= (!empty($ApprovedBy)?$ApprovedBy:Auth()->user()->adminuserid);
						$InwardLedger['updated_by']				= (!empty($ApprovedBy)?$ApprovedBy:Auth()->user()->adminuserid);
						$EXITS = ProductInwardLadger::where("ref_id",$Id)->where("mrf_id",$MRF_ID)->WHERE("product_id",$PRODUCT_ID)->WHERE("product_type",PRODUCT_SALES)->first();
							############### NEW AVG PRICE CALCULATION LOGIC LIVE 20 JAN 2022 ####################
							$inward_record_id 						= ProductInwardLadger::AutoAddInward($InwardLedger);
							$STOCK_AVG_PRICE 						= WmBatchProductDetail::GetSalesProductAvgPriceN1($MRF_ID,0,$PRODUCT_ID,$inward_record_id);
	                    	StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,PRODUCT_SALES,$MRF_ID,date("Y-m-d"),$STOCK_AVG_PRICE);
							############### NEW AVG PRICE CALCULATION LOGIC LIVE 20 JAN 2022 ####################
					}
				}
			}
			if($status == 3 || $status == 2) {
				$GetData->change_date 	= date("Y-m-d");
			}
			return true;
		}
		return false;
	}

	/*
	use 	: Update Approval status of Credit Note
	Author 	: Axay Shah
	Date 	: 03 Feb,2021
	*/
	public static function NoActionTakenSendEmail()
	{
		$Client 	= new WmClientMaster();
		$Department = new WmDepartment();
		$Invoice 	= new WmInvoices();
		$self 		= (new static)->getTable();
		$date 		= date("Y-m-d");
		$prev_date 	= date('Y-m-d', strtotime($date .' -2 day'));
		$GetData 	= 	self::select("DEPT.department_name","CLIENT.client_name","$self.invoice_no","INV.invoice_date","$self.serial_no","$self.change_date","$self.remarks","$self.created_at")
						->leftjoin($Invoice->getTable()." as INV","$self.invoice_id","=","INV.id")
						->leftjoin($Client->getTable()." as CLIENT","INV.client_master_id","=","CLIENT.id")
						->leftjoin($Department->getTable()." as DEPT","$self.mrf_id","=","DEPT.id")
						->where("$self.change_date","<=",$prev_date)
						->where("$self.status",0)
						->get()
						->toArray();
		if(!empty($GetData)){
			try{
				$ToEmail 		= CREDIT_NOTE_PENDING_APPROVAL_EMAIL;
				$Subject 		= "Pending Credit Note Approval";
				$sendEmail      = Mail::send("email-template.credit_note_approval_pending",array("data"=>$GetData,"HeaderTitle"=>$Subject),function ($message) use ($ToEmail,$Subject)
				{
					$message->to($ToEmail);
					$message->subject($Subject);
				});
			}catch(\Exception $e){
				\Log::error("ERROR : ".$e->getMessage()." FILE: ".$e->getFile()." Line: ".$e->getLine());
			}
		}
	}

	/*
	Use 	: Cancle Invoice
	Author 	: Axay Shah
	Date 	: 16 July,2019
	*/
	public static function GenerateCreditInvoice($creditNoteId=0,$InvoiceId=0)
	{
		$data 				= array();
		$InvoiceData 		= WmInvoices::GetById($InvoiceId);
		if($InvoiceData)
		{
			######### COMPANY DETAILS ###############
			$companyDetails 			= $InvoiceData['company_details'];
			$MRFDepartment 				= $InvoiceData['MRFDepartment'];

			$data['mrf_name'] 			= isset($MRFDepartment['department_name']) ? ucwords(strtolower($MRFDepartment['department_name'])) : "";
			$data['mrf_address'] 		= isset($MRFDepartment['address']) ? ucwords(strtolower($MRFDepartment['address'])) : "";
			$data['mrf_city'] 			= isset($MRFDepartment['mrf_city_name']) ? ucwords(strtolower($MRFDepartment['mrf_city_name'])) : "";
			$data['mrf_gst_in'] 		= isset($MRFDepartment['gst_in']) ? strtoupper(strtolower($MRFDepartment['gst_in'])) : "";
			$data['mrf_state_name'] 	= isset($MRFDepartment['mrf_state_name']) ? ucwords($MRFDepartment['mrf_state_name']) : "";
			$data['mrf_state_code'] 	= isset($MRFDepartment['mrf_state_code']) ? strtoupper($MRFDepartment['mrf_state_code']) : "";
			$data['mrf_pincode'] 		= isset($MRFDepartment['pincode']) ? strtoupper($MRFDepartment['pincode']) : "";

			$data['company_title'] 		= ucwords(strtolower($companyDetails['company_name']));
			$data['company_address'] 	= ucwords(strtolower($companyDetails['address1']." ".$companyDetails['address2']));
			$data['company_city'] 		= ucwords(strtolower($companyDetails['city_name']));
			$data['company_gst_in'] 	= strtoupper($companyDetails['gst_no']);
			$data['company_cin_no'] 	= strtoupper($companyDetails['cin_no']);
			$data['company_state_name'] = ucwords(strtolower($companyDetails['state_name']));
			$data['company_state_code'] = $companyDetails['state_code'];
			$data['company_zipcode'] 	= $companyDetails['zipcode'];
			$data['company_cin'] 		= $companyDetails['zipcode'];
			######### CLIENT DETAILS ###############
			$data['client_name'] 		= ucwords(strtolower($InvoiceData['client_name']));
			$data['client_name'] 		= ucwords(strtolower($InvoiceData['client_name']));
			$data['client_address'] 	= ucwords(strtolower($InvoiceData['address']));
			$data['client_gst_in'] 		= strtoupper($InvoiceData['gstin_no']);
			$data['client_state_name'] 	= ucwords(strtolower($InvoiceData['client_state_name']));
			$data['client_state_code'] 	= $InvoiceData['gst_state_code'];
			$data['client_city_name'] 	= $InvoiceData['client_city_name'];
			$data['client_pincode'] 	= $InvoiceData['pincode'];
			######### OTHER DETAILS #############
			$data['term_of_payment'] 	= $InvoiceData['days'];
			$data['invoice_no'] 		= $InvoiceData['invoice_no'];
			$data['invoice_date'] 		= $InvoiceData['invoice_date'];
			$data['dispatch_doc_no'] 	= $InvoiceData['dispatch_doc_no'];
			$data['dispatched_through'] = ucwords(strtolower($InvoiceData['dispatched_through']));
			$data['destination'] 		= ucwords(strtolower($InvoiceData['destination']));
			$data['terms_of_delivery'] 	= ucwords(strtolower($InvoiceData['terms_of_delivery']));
			$productList 	= array();
			$sameState 		= "";
			$creditNote 	= WmInvoicesCreditDebitNotes::find($creditNoteId);
			if($creditNote){
				$data['bill_from_mrf_id'] 	= $creditNote->bill_from_mrf_id;
				$data['mrf_id'] 			= $creditNote->mrf_id;
				$data['serial_no'] 			= $creditNote->serial_no;
				$data['invoice_id'] 		= $creditNote->invoice_id;
				$data['tcs_rate'] 			= $creditNote->tcs_rate;
				$data['tcs_amount'] 		= $creditNote->tcs_amount;
				$data['dated'] 			= (!empty($creditNote->change_date)) ? date("Y-m-d",strtotime($creditNote->change_date)) : "";
				$data['note_title'] 	= ($creditNote->notes_type == 1) ? "Debit" : "Credit";
				$data['notes_type'] 	= $creditNote->notes_type;
				######### QR CODE GENERATION OF E INVOICE NO #############
				$qr_code 				= "";
				$e_invoice_no 			= (!empty($creditNote->irn)) 		? $creditNote->irn : "";
				$acknowledgement_no 	= (!empty($creditNote->ack_no)) 	? $creditNote->ack_no : "";
				$acknowledgement_date 	= (!empty($creditNote->ack_date)) 	? $creditNote->ack_date : "";
				$signed_qr_code 		= (!empty($creditNote->signed_qr_code)) 	? $creditNote->signed_qr_code : "";
				$qr_code_string 		= "E-Invoice No. :".$e_invoice_no." Acknowledgement No. : ".$acknowledgement_no." Acknowledgement Date : ".$acknowledgement_date;
				$qr_code_string 		= (empty($e_invoice_no) && empty($acknowledgement_no) && empty($acknowledgement_date)) ? " " : $qr_code_string ;
				if(!empty($e_invoice_no) || !empty($acknowledgement_no) || !empty($acknowledgement_date)){
					$name 					= "credit_debit_".$creditNoteId;
					$qr_code 				= url("/")."/".GetQRCode($signed_qr_code,$creditNoteId);
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
				$FRIEGHT_HSN 			= "";
				$PRIVIOUS_GST 			= "";
				$GST_PER_TOTAL 			= 0;
				######### QR CODE GENERATION OF E INVOICE NO #############
				if(!empty($creditNote->CreditDetails)){
					foreach($creditNote->CreditDetails as $key => $value){
						$sameState 			= ($value['is_from_same_state'] == "Y") ? true : false;
						$rate 				= ($value['change_in'] == 1 || $value['change_in'] == 3) ? _FormatNumberV2($value['revised_rate']) : _FormatNumberV2($value['rate']);
						$qty 				= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $value['revised_quantity'] : $value['quantity'];
						$productList[$key]  = $value;
						$product 			= WmProductMaster::where("id",$value['product_id'])->first();
						$productList[$key]['original_qty'] 		= $value['quantity'];
						$productList[$key]['original_rate'] 	= _FormatNumberV2($value['rate']);
						$productList[$key]['invoice_rate'] 		= _FormatNumberV2($rate);
						$productList[$key]['invoice_qty'] 		= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $qty : 0;
						$productList[$key]['product_name'] 		= ($product) ? $product->title 		: "";
						$productList[$key]['hsn_code'] 			= ($product) ? $product->hsn_code 	: "";
						$productList[$key]['is_charge'] 		= 0;
						############# ###################
						$return_type = "";
						if($value['change_in'] == 1){
							$return_type =  " (Rate Difference)";
						}elseif($value['change_in'] == 2 && $value['inward_stock'] == 2){
							$return_type =  " (Weight Difference)";
						}
						$productList[$key]['return_type'] 	= ucwords($return_type);
						$GST_AMOUNT  = ($value['is_from_same_state'] == "Y" && $value['revised_gst_amount'] > 0)  ? _FormatNumberV2($value['revised_gst_amount'] / 2) : _FormatNumberV2($value['revised_gst_amount']);
						$productList[$key]['cgst_amount'] = ($value['is_from_same_state'] == "Y" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
						$productList[$key]['sgst_amount'] = ($value['is_from_same_state'] == "Y" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
						$productList[$key]['igst_amount'] = ($value['is_from_same_state'] == "N" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;

						$GST_PER_TOTAL =($sameState) ? $value['cgst_rate'] + $value['sgst_rate'] : $value['igst_rate'];
						if($PRIVIOUS_GST <= $GST_PER_TOTAL){
							$PRIVIOUS_GST 	= $GST_PER_TOTAL;
							$FRIEGHT_HSN 	= ($product) ? $product->hsn_code 	: "";
						}
					}
				}
				$FRIEGHT_DATA = \DB::table("wm_invoices_credit_debit_notes_frieght_details")->where("cd_notes_id",$creditNoteId)->first();
				if(!empty($FRIEGHT_DATA)){
					$FRIEGHT_DATA->hsn_code 	= $FRIEGHT_HSN;
					$FRIEGHT_DATA->product_name = "Frieght";
					$FRIEGHT_DATA->cgst_amount 	= (!empty($FRIEGHT_DATA->cgst_rate) ? _FormatNumberV2(($FRIEGHT_DATA->gross_amount * $FRIEGHT_DATA->cgst_rate) / 100) : 0);
					$FRIEGHT_DATA->sgst_amount 	= (!empty($FRIEGHT_DATA->sgst_amount) ? _FormatNumberV2(($FRIEGHT_DATA->gross_amount * $FRIEGHT_DATA->sgst_rate) / 100) : 0);
					$FRIEGHT_DATA->igst_amount 	= (!empty($FRIEGHT_DATA->igst_amount) ? _FormatNumberV2(($FRIEGHT_DATA->gross_amount * $FRIEGHT_DATA->igst_rate) / 100) : 0);
				}
				####### Invoice Additional Charges ############
				$AdditionalCharges = WmInvoicesCreditDebitNotesChargesDetails::GetCnDnChargeDetails($creditNoteId);
				if (!empty($AdditionalCharges))
				{
					$counter = sizeof($productList);
					$counter++;
					foreach ($AdditionalCharges as $AddCharge) {
						$NewRow 	= $AddCharge;
						$sameState 	= ($AddCharge['is_from_same_state'] == "Y") ? true : false;
						$rate 		= ($AddCharge['change_in'] == 1 || $AddCharge['change_in'] == 3) ? _FormatNumberV2($AddCharge['revised_rate']) : _FormatNumberV2($AddCharge['rate']);
						$qty 		= ($AddCharge['change_in'] == 2 || $AddCharge['change_in'] == 3) ? $AddCharge['revised_quantity'] : $AddCharge['quantity'];
						$NewRow['original_qty'] 	= $AddCharge['quantity'];
						$NewRow['original_rate'] 	= _FormatNumberV2($AddCharge['rate']);
						$NewRow['invoice_rate'] 	= _FormatNumberV2($rate);
						$NewRow['invoice_qty'] 		= ($AddCharge['change_in'] == 2 || $AddCharge['change_in'] == 3) ? $qty : 0;
						$NewRow['product_name'] 	= $AddCharge['product_name'];
						$NewRow['hsn_code'] 		= $AddCharge['hsn_code'];
						$NewRow['is_charge'] 		= 1;
						############# ###################
						$return_type = "";
						if($AddCharge['change_in'] == 1){
							$return_type =  " (Rate Difference)";
						}elseif($AddCharge['change_in'] == 2){
							$return_type =  " (Weight Difference)";
						}
						$NewRow['return_type'] 	= ucwords($return_type);
						$GST_AMOUNT  = ($AddCharge['is_from_same_state'] == "Y" && $AddCharge['revised_gst_amount'] > 0)  ? _FormatNumberV2($AddCharge['revised_gst_amount'] / 2) : _FormatNumberV2($AddCharge['revised_gst_amount']);
						$NewRow['cgst_amount'] = ($AddCharge['is_from_same_state'] == "Y" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
						$NewRow['sgst_amount'] = ($AddCharge['is_from_same_state'] == "Y" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
						$NewRow['igst_amount'] = ($AddCharge['is_from_same_state'] == "N" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
						$CreditDebitObj 	= new WmInvoicesCreditDebitNotesDetails($NewRow);
						$productList[] 		= $CreditDebitObj;
						$counter++;
					}
				}
				####### Invoice Additional Charges ############
			}
			$data['frieght_data'] 		= (isset($FRIEGHT_DATA) && !empty($FRIEGHT_DATA)) ? $FRIEGHT_DATA : array();
			$data['credit_note_no'] 	= ($creditNote) ? $creditNote->serial_no : "";
			$data['remarks'] 			= ($creditNote) ? ucwords(strtolower($creditNote->remarks)) : "";
			$data['products'] 			= $productList;
			$data['from_same_state'] 	= $sameState;
		}
		
		return $data;
	}
	/*
	Use 	: Update E invoice number
	Author 	: Axay Shah
	Date 	: 06 May 2021
	*/
	public static function UpdateEinvoiceNo($id=0,$irn="",$ack_no="",$ack_date=""){
		if(!empty($id)){
			$update = self::where("id",$id)->update(["irn"=>$irn,"ack_no"=>$ack_no,"ack_date"=>$ack_date]);
			return true;
		}
		return false;
	}

	/*
	Use 	: Generate E invoice for Asset
	Author 	: Axay Shah
	Date 	: 10 May 2021
	*/
	public static function GenerateCreditDebitEinvoice($ID,$INVOICEID)
	{
		$data   = self::GenerateCreditInvoice($ID,$INVOICEID);
		$array  = array();
		$res 	= array();
		if(!empty($data))
		{
			$SellerDtls   		= array();
			$BuyerDtls 			= array();
			$LOGIN_DETAILS 		= WmDepartment::find($data['bill_from_mrf_id']);
			$MERCHANT_KEY 		= CompanyMaster::where("company_id",Auth()->user()->company_id)->value('merchant_key');
			$USERNAME 			= (isset($LOGIN_DETAILS->gst_username) && !empty($LOGIN_DETAILS->gst_username)) ? $LOGIN_DETAILS->gst_username : "";
			$PASSWORD 			= (isset($LOGIN_DETAILS->gst_password) && !empty($LOGIN_DETAILS->gst_password)) ? $LOGIN_DETAILS->gst_password : "";
			$GST_IN 			= (isset($LOGIN_DETAILS->gst_in) && !empty($LOGIN_DETAILS->gst_in)) ? $LOGIN_DETAILS->gst_in : "";
			############## SALLER DETAILS #############
			$TCS_AMOUNT 		= (isset($data['tcs_amount']) && !empty($data['tcs_amount'])) ? $data['tcs_amount'] : 0;
			$COMPANY_NAME 		= (isset($data['company_title']) && !empty($data['company_title'])) ? $data['company_title'] : null;
			$FROM_ADDRESS_1 	= (!empty($data['mrf_address'])) ? $data['mrf_address'] : null;
			$FROM_ADDRESS_2 	= null;
			if(strlen($FROM_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($FROM_ADDRESS_1);
				$FROM_ADDRESS_1 = (!empty($ARR_STRING)) ? $ARR_STRING[0] : $FROM_ADDRESS_1;
				$FROM_ADDRESS_2 = (!empty($ARR_STRING)) ? $ARR_STRING[1] : $FROM_ADDRESS_1;
			}
			$FROM_TREAD 		= $COMPANY_NAME;
			$FROM_GST 			= (!empty($data['mrf_gst_in'])) ? $data['mrf_gst_in'] : null;
			$FROM_STATE_CODE 	= (!empty($data['mrf_state_code'])) ? $data['mrf_state_code'] : null;
			$FROM_STATE 		= (!empty($data['mrf_state'])) ? $data['mrf_state'] : null;
			$FROM_LOC 			= (!empty($data['mrf_city'])) ? $data['mrf_city'] : null;
			$FROM_PIN 			= (!empty($data['mrf_pincode'])) ? $data['mrf_pincode'] : null;

			############## BUYER DETAILS #############
			$TO_ADDRESS_1 		= (!empty($data['client_address'])) ? $data['client_address'] : null;
			$TO_ADDRESS_2 		= null;
			if(strlen($TO_ADDRESS_1) > 100){
				$ARR_STRING 	= WrodWrapString($TO_ADDRESS_1);
				// prd($ARR_STRING);
				$TO_ADDRESS_1 	= (isset($ARR_STRING[0]) && !empty($ARR_STRING[0])) ? $ARR_STRING[0] : $TO_ADDRESS_1;
				$TO_ADDRESS_2 	= (isset($ARR_STRING[1]) && !empty($ARR_STRING[1])) ? $ARR_STRING[1] : $TO_ADDRESS_1;
			}
			$TO_TREAD 			= (!empty($data['client_name'])) ? $data['client_name'] : null;
			// $TO_ADDRESS 		= (!empty($data->client_address)) ? $data->client_address : null;
			$TO_GST 			= (!empty($data['client_gst_in'])) ? $data['client_gst_in'] : null;
			$TO_STATE_CODE 		= (!empty($data['client_state_code'])) ? $data['client_state_code'] : null;
			$TO_STATE 			= (!empty($data['client_state_name'])) ? $data['client_state_name'] : null;
			$TO_LOC 			= (!empty($data['client_city_name'])) ? $data['client_city_name'] : null;
			$TO_PIN 			= (!empty($data['client_pincode'])) ? $data['client_pincode'] : null;
			$DOC_NO 			= (isset($data['serial_no']) && !empty($data['serial_no'])) ? $data['serial_no'] : null;
			$DOC_DATE 			= (isset($data['invoice_date']) && !empty($data['invoice_date'])) ? date("d/m/Y",strtotime($data['invoice_date'])) : null;

			$array["merchant_key"] 	= $MERCHANT_KEY;
			$array["username"] 		= $USERNAME;
			$array["password"] 		= $PASSWORD;
			$array["user_gst_in"] 	= $GST_IN;
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

			$IGST_ON_INTRA 	= ($SAME_STATE) ? "N" : "Y";

			$array['merchant_key']				= $MERCHANT_KEY;
			$array["SellerDtls"] 				= $SellerDtls;
			$array["BuyerDtls"] 				= $BuyerDtls;
			$array["DispDtls"]   				= null;
			$array["ShipDtls"]    				= null;
			$array["EwbDtls"]     				= null;
			$array["version"]     				= E_INVOICE_VERSION;
			$array["TranDtls"]["TaxSch"]        = TAX_SCH ;
			$array["TranDtls"]["SupTyp"]        = "B2B";
			$array["TranDtls"]["RegRev"]        = "N";
			$array["TranDtls"]["EcmGstin"]      = null;
			$array["TranDtls"]["IgstOnIntra"]   = "N";
			$array["DocDtls"]["Typ"]            = ($data["notes_type"] == 1) ? "DBN" : "CRN";
			$array["DocDtls"]["No"]             = $DOC_NO;
			$array["DocDtls"]["Dt"]             = date("d/m/Y");
			$itemList                          	= isset($data['products']) ? $data['products']:array();
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
					$quantity 	= 0;
					$rate 		= 0;
					if($value['change_in'] == 1){
						$rate 	= $value['revised_rate'];
						$qty 	= $value['quantity'];
					}elseif($value['change_in'] == 2){
						$rate 	= $value['rate'];
						$qty 	= $value['revised_quantity'];
					}elseif($value['change_in'] == 3){
						$rate 	= $value['revised_rate'];
						$qty 	= $value['revised_quantity'];
					}
					$TOTAL_GST_PERCENT 			= ($SAME_STATE) ? _FormatNumberV2($value['sgst_rate'] + $value['cgst_rate']) :  _FormatNumberV2($value['igst_rate']);
					$QTY 						= (float)$qty;
					$RATE 						= (float)$rate;
					$IGST 						= (float)$value['igst_rate'];
					$SGST 						= (float)$value['sgst_rate'];
					$CGST 						= (float)$value['cgst_rate'];
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
														"PrdDesc"               => $value['product_name'],
														"IsServc"               => (isset($value["IsServc"]) && !empty($value["IsServc"]) ? $value['IsServc'] : "N"),
														"HsnCd"                 => $value['hsn_code'],
														"Qty"                   => _FormatNumberV2((float)$QTY),
														"Unit"                  => "KGS",
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
			############# CHARGES DETAILS PUSH IN E INVOICE IF APPLICABLE ##############
			// $CLIENT_CHARGES_DATA = WmInvoicesCreditDebitNotesChargesDetails::GetCnDnChargesProductDataForEInvoice($ID);
			// if(!empty($CLIENT_CHARGES_DATA)){
			// 	$count = sizeof($item);
			// 	foreach ($CLIENT_CHARGES_DATA as $CHARGE_KEY => $CHARGE_VALUE) {
					
			// 		$TOTAL_NET_AMOUNT 	+= $CHARGE_VALUE['taxableAmount'];
			// 		$TOTAL_GROSS_AMOUNT += $CHARGE_VALUE['totalItemAmount'];
			// 		if($CHARGE_VALUE['isFromSameState']){
			// 			$TOTAL_CGST 		+= $CHARGE_VALUE['cgstAmt'];
			// 			$TOTAL_SGST 		+= $CHARGE_VALUE['sgstAmt'];
			// 		}else{
			// 			$TOTAL_IGST 		+= $CHARGE_VALUE['igstAmt'];
			// 		}
			// 		$itemList[$count] 		= $CHARGE_VALUE;
			// 		$count++;
			// 	}
			// }
			$FRIEGHT_SUM 	= \DB::table("wm_invoices_credit_debit_notes_frieght_details")->where("cd_notes_id",$ID)->sum("net_amount");
			############# CHARGES DETAILS PUSH IN E INVOICE IF APPLICABLE ##############
			####### ITEM DETAILS ###########
			$array["ItemList"]  =  $item;
			if($FRIEGHT_SUM > 0){
				$TOTAL_NET_AMOUNT 	+= $FRIEGHT_SUM; 
			}
			$TOTAL_NET_AMOUNT = ($TCS_AMOUNT > 0) ? ($TCS_AMOUNT + $TOTAL_NET_AMOUNT) : $TOTAL_NET_AMOUNT;
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
			$array["ValDtls"]["OthChrg"]    = ($FRIEGHT_SUM + $TCS_AMOUNT);
			$array["ValDtls"]["RndOffAmt"]  = _FormatNumberV2($DIFFERENCE_AMT);
			$array["ValDtls"]["TotInvVal"]  = round($TOTAL_NET_AMOUNT);
			 // prd($array);
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
						$Irn    	= (isset($details['Irn'])) ? $details['Irn']      : "";
						$SignedQRCode   = (isset($details['SignedQRCode'])) ? $details['SignedQRCode']      : "";
						self::where("id",$ID)->update([
							"irn" 			=> $Irn,
							"ack_date" 		=> $AckDt,
							"ack_no" 		=> $AckNo,
							"signed_qr_code" => $SignedQRCode,
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
	Use 	: Cancel E invoice Number Data
	Author 	: Axay Shah
	Date  	: 11 May 2021
	*/
	public static function CancelEInvoice($request)
	{
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
							"irn" 			=> "",
							"ack_date" 		=> "",
							"ack_no" 		=> "",
							"signed_qr_code" => "",
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
	Use 	: First level approval user drop down display for purchase & sales Credit Note
	Author 	: Axay Shah
	Date 	: 27 Sep,2021
	*/
	public static function GetFirstLevelApprovalUserList($mrf_id=0,$from_purchase=0)
	{
		$res = array();
		if($mrf_id > 0) {
			$trnid 	= ($from_purchase == 1) ? PURCHASE_CN_DN_FIRST_LEVEL_APPROVAL  :  SALES_CN_DN_FIRST_LEVEL_APPROVAL;
			$Admin 	= new AdminUser();
			$user 	= $Admin->getTable();
			$SQL 	= "	SELECT U.adminuserid, CONCAT(U.firstname,' ',U.lastname) as name 
						FROM adminuserrights 
						INNER JOIN adminuser as U ON adminuserrights.adminuserid = U.adminuserid 
						where U.status = 'A' and adminuserrights.trnid = $trnid 
						and find_in_set($mrf_id,assign_mrf_id)";
			$res =  \DB::select($SQL);
			return $res;
		}
		return $res;
	}

	/*
	Use 	: SendCreditDebitNoteApprovalEmail
	Author 	: Kalpak Prajapati
	Date 	: 16 July,2019
	*/
	public static function SendCreditDebitNoteApprovalEmail($Level="first",$creditNoteId=0,$InvoiceId=0,$AdminUserRight=0)
	{
		$data 				= array();
		$InvoiceData 		= self::GenerateCreditInvoice($creditNoteId,$InvoiceId);
		if($InvoiceData)
		{
			######### COMPANY DETAILS ###############
			$data['mrf_name'] 			= isset($InvoiceData['mrf_name'])?ucwords(strtolower($InvoiceData['mrf_name'])):"";
			######### CLIENT DETAILS ###############
			$data['client_name'] 		= ucwords(strtolower($InvoiceData['client_name']));
			######### OTHER DETAILS #############
			$data['invoice_no'] 		= $InvoiceData['invoice_no'];
			$data['invoice_date'] 		= $InvoiceData['invoice_date'];
			$productList 				= array();
			$sameState 					= "";
			$creditNote 				= WmInvoicesCreditDebitNotes::find($creditNoteId);
			if($creditNote)
			{
				if (strtolower($Level) != "first") {
					$WmDepartment 	= WmDepartment::select("base_location_id")->where("id",$creditNote->bill_from_mrf_id)->first();
					$AdminUser 		= AdminUser::select("adminuser.adminuserid","adminuser.email")
										->leftjoin("user_base_location_mapping as BLM","BLM.adminuserid","=","adminuser.adminuserid")
										->leftjoin("adminuserrights as AUR","AUR.adminuserid","=","adminuser.adminuserid")
										->where("BLM.base_location_id",$WmDepartment->base_location_id)
										->where("AUR.trnid",$AdminUserRight)
										->where("adminuser.status","A")
										->where("adminuser.email","!=","")
										->whereNotNull("adminuser.email")
										->whereRaw("FIND_IN_SET(".$creditNote->bill_from_mrf_id.",adminuser.assign_mrf_id)")
										->get()
										->toArray();
				} else {
					$AdminUser 		= AdminUser::select("adminuser.adminuserid","adminuser.email")
										->where("adminuser.adminuserid",$creditNote->first_level_approved_by)
										->where("adminuser.status","A")
										->where("adminuser.email","!=","")
										->whereNotNull("adminuser.email")
										->get()
										->toArray();
				}
				if (!empty($AdminUser))
				{
					$data['bill_from_mrf_id'] 	= $creditNote->bill_from_mrf_id;
					$data['mrf_id'] 			= $creditNote->mrf_id;
					$data['serial_no'] 			= $creditNote->serial_no;
					$data['invoice_id'] 		= $creditNote->invoice_id;
					$data['dated'] 				= (!empty($creditNote->change_date)) ? date("Y-m-d",strtotime($creditNote->change_date)) : "";
					$data['note_title'] 		= ($creditNote->notes_type == 1)?"Debit" : "Credit";
					$data['notes_type'] 		= $creditNote->notes_type;
					$data['dispatch_id'] 		= $creditNote->dispatch_id;
					$data['e_invoice_no'] 		= (!empty($creditNote->ack_no))?$creditNote->ack_no:"";
					if(!empty($creditNote->CreditDetails))
					{
						foreach($creditNote->CreditDetails as $key => $value)
						{
							$sameState 								= ($value['is_from_same_state'] == "Y") ? true : false;
							$rate 									= ($value['change_in'] == 1 || $value['change_in'] == 3)?_FormatNumberV2($value['revised_rate']):_FormatNumberV2($value['rate']);
							$qty 									= ($value['change_in'] == 2 || $value['change_in'] == 3)?$value['revised_quantity']:$value['quantity'];
							$productList[$key]  					= $value;
							$product 								= WmProductMaster::where("id",$value['product_id'])->first();
							$productList[$key]['original_qty'] 		= $value['quantity'];
							$productList[$key]['original_rate'] 	= _FormatNumberV2($value['rate']);
							$productList[$key]['invoice_rate'] 		= _FormatNumberV2($rate);
							$productList[$key]['invoice_qty'] 		= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $qty : 0;
							$productList[$key]['product_name'] 		= ($product) ? $product->title 		: "";
							$productList[$key]['hsn_code'] 			= ($product) ? $product->hsn_code 	: "";
							$return_type 							= "";
							if($value['change_in'] == 1) {
								$return_type =  " (Rate Difference)";
							} elseif($value['change_in'] == 2 && $value['inward_stock'] == 2) {
								$return_type =  " (Weight Difference)";
							}
							$productList[$key]['return_type'] 	= ucwords($return_type);
							$GST_AMOUNT  						= ($value['is_from_same_state'] == "Y" && $value['revised_gst_amount'] > 0)  ? _FormatNumberV2($value['revised_gst_amount'] / 2) : _FormatNumberV2($value['revised_gst_amount']);
							$productList[$key]['cgst_amount'] 	= ($value['is_from_same_state'] == "Y" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
							$productList[$key]['sgst_amount'] 	= ($value['is_from_same_state'] == "Y" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
							$productList[$key]['igst_amount'] 	= ($value['is_from_same_state'] == "N" && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
							$productList[$key]['gross_amount'] 	= $value['gross_amount'];
							$productList[$key]['gst_amount'] 	= $value['gst_amount'];
							$productList[$key]['net_amount'] 	= $value['net_amount'];
							$productList[$key]['rev_gross_amt'] = $value['revised_gross_amount'];
							$productList[$key]['rev_gst_amt'] 	= $value['revised_gst_amount'];
							$productList[$key]['rev_net_amt'] 	= $value['revised_net_amount'];
						}
					}
					$data['credit_note_no'] 	= ($creditNote) ? $creditNote->serial_no : "";
					$data['remarks'] 			= ($creditNote) ? nl2br(ucwords(strtolower($creditNote->remarks))):"";
					$data['products'] 			= $productList;
					$data['from_same_state'] 	= $sameState;
					foreach($AdminUser as $AdminUser)
					{
						$ToEmail 		= $AdminUser['email'];
						$Subject 		= "New ".$data['note_title']." Note ".ucwords($Level)." Level Approval Request - ".$data['credit_note_no'];
						if ($creditNote->notes_type == 1) {
							$APPROVE_LINK 	= env("APP_URL")."/request-".$Level."-approval/debit-note/approve/".encode($creditNoteId)."/".encode($InvoiceId)."/".encode($AdminUser['adminuserid']);
							$REJECT_LINK 	= env("APP_URL")."/request-".$Level."-approval/debit-note/reject/".encode($creditNoteId)."/".encode($InvoiceId)."/".encode($AdminUser['adminuserid']);
						} else {
							$APPROVE_LINK 	= env("APP_URL")."/request-".$Level."-approval/credit-note/approve/".encode($creditNoteId)."/".encode($InvoiceId)."/".encode($AdminUser['adminuserid']);
							$REJECT_LINK 	= env("APP_URL")."/request-".$Level."-approval/credit-note/reject/".encode($creditNoteId)."/".encode($InvoiceId)."/".encode($AdminUser['adminuserid']);
						}
						$arrEmailData	= array("NoteDetails"=>$data,
												"HeaderTitle"=>$data['note_title']." Note ".$data['credit_note_no'],
												"APPROVE_LINK"=>$APPROVE_LINK,
												"REJECT_LINK"=>$REJECT_LINK);
						$sendEmail 		= Mail::send("email-template.credit_note_approve_email",$arrEmailData,function ($message) use ($ToEmail,$Subject) {
							$message->to($ToEmail);
							$message->subject($Subject);
						});
					}
				}
			}
		}
	}
	/*
	Use 	: Role has Rights check 
	Author 	: Axay 
	Date 	: 14 Auguest,2023
	*/
	public static function GetRightsRoleWise($userType,$rightsID){
		return GroupRightsTransaction::where("group_id",$userType)->where("trn_id",$rightsID)->count();
	}
}