<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseCreditDebitNoteDetailsMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\Appoinment;
use App\Models\CustomerMaster;
use App\Models\WmBatchCollectionMap;
use App\Models\WmDepartment;
use App\Models\GSTStateCodes;
use App\Models\ViewCityStateContryList;
use App\Models\WmBatchProductDetail;
use App\Models\StockLadger;
use Mail;
use DB;
class PurchaseCreditDebitNoteMaster extends Model implements Auditable
{
    protected 	$table 		=	'purchase_credit_debit_note_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	public function CreditDetails(){
		return $this->hasMany(PurchaseCreditDebitNoteDetailsMaster::class,"note_id","id");
	}

	public function department(){
		return $this->belongsTo(WmDepartment::class,"mrf_id","id");
	}

	public function crn_document(){
		return $this->belongsTo(MediaMaster::class,"document_id");
	}

	/*
	Use 	: Geerate Purchase credit debit notes
	Author 	: Axay Shah
	Date 	: 04 August,2021
	*/
	public static function GeneratePurchaseCreditDebitNote($request)
	{
		//prd($request->all());
		$ID 							= 0;
		$CODE 							= "";
		$SERIAL_NO 						= "";
		$GstStateCodes 					= new GSTStateCodes();
		$GstTbl 						= $GstStateCodes->getTable();
		$Dept 							= new WmDepartment();
		$DeptTbl 						= $Dept->getTable();
		$createdBy  					= Auth()->user()->adminuserid;
		$CompanyID  					= Auth()->user()->company_id;
		$updatedBy  					= Auth()->user()->adminuserid;
		$appointment_id 				= (isset($request['appointment_id']) 	&& !empty($request['appointment_id'])) 	?  $request['appointment_id'] 	: 0;
		$transaction_type 				= (isset($request['transaction_type']) 	&& !empty($request['transaction_type'])) 	?  $request['transaction_type'] 	: 0;
		$collection_id 					= (isset($request['collection_id']) 	&& !empty($request['collection_id'])) 	?  $request['collection_id'] 	: 0;
		$customer_id 					= (isset($request['customer_id']) 	&& !empty($request['customer_id'])) 	?  $request['customer_id'] 	: 0;
		$mrf_id 						= (isset($request['mrf_id']) 	&& !empty($request['mrf_id'])) 	?  $request['mrf_id'] 	: 0;
		$batch_id 						= (isset($request['batch_id']) 	&& !empty($request['batch_id'])) 	?  $request['batch_id'] 	: 0;
		$collection_details_id 			= (isset($request['collection_detail_id']) 	&& !empty($request['collection_detail_id'])) ?  $request['collection_detail_id'] 	: 0;
		$status 						= (isset($request['status']) 	&& !empty($request['status'])) 	?  $request['status'] : 0;
		$remarks 						= (isset($request['remarks']) && !empty($request['remarks'])) 	?  $request['remarks'] 	: "";
		$note_type 						= (isset($request['notes_type']) && !empty($request['notes_type']))  ?  1 : 0;
		$invoice_date 					= (isset($request['invoice_date']) && !empty($request['invoice_date']))  ?  date("Y-m-d",strtotime($request["invoice_date"])) : "";
		$invoice_no 					= (isset($request['invoice_no']) && !empty($request['invoice_no']))  ?  $request['invoice_no'] : "";
		$approval_request 				= (isset($request['approval_request']) && !empty($request['approval_request']))  ?  $request['approval_request'] : "";
		$REASON 						= (isset($request['reason']) 	&& !empty($request['reason'])) ? $request['reason']	: "";
		$Save 							= new self();
		$Save->created_by  				= $createdBy;
		$Save->updated_by  				= $updatedBy;
		$Save->appointment_id 			= $appointment_id;
		$Save->collection_id 			= $collection_id;
		$Save->collection_details_id 	= $collection_details_id;
		$Save->customer_id 				= $customer_id;
		$Save->mrf_id 					= $mrf_id;
		$Save->batch_id 				= $batch_id;
		$Save->transaction_type 		= $transaction_type;
		// $Save->change_date  			= date("Y-m-d");
		$Save->notes_type  				= $note_type;
		$Save->remarks  				= $remarks;
		$Save->company_id  				= $CompanyID;
		$Save->invoice_date  			= $invoice_date;
		$Save->invoice_no  				= $invoice_no;
		$Save->first_level_approved_by  = $approval_request;
		$Save->reason 					= $REASON;
		######### IMAGE UPLOAD ##########
		if(isset($_FILES["crn_document"]["tmp_name"])) {
            $fileName 		= $_FILES["crn_document"]["name"];
            $partialPath 	= PATH_CREDIT_NOTE;
          	$imageFileType 	= strtolower(pathinfo($_FILES["crn_document"]["name"],PATHINFO_EXTENSION));
			if(!is_dir(public_path(PATH_IMAGE.'/').$partialPath)) {
                mkdir(public_path(PATH_IMAGE.'/').$partialPath,0777,true);
            }
           	$orignalImg     = "PUR_CRN_DRN_".time().'.'.$imageFileType;
           	$fullPath 		= public_path(PATH_IMAGE.'/').$partialPath."/".$orignalImg;
           	move_uploaded_file($_FILES["crn_document"]["tmp_name"],$fullPath);
            $mediaMaster = new MediaMaster();
            $mediaMaster->company_id 	= $CompanyID;
            $mediaMaster->city_id 	    = Auth()->user()->city;
            $mediaMaster->original_name = $orignalImg;
            $mediaMaster->server_name   = $orignalImg;
            $mediaMaster->image_path    = $partialPath;
            if($mediaMaster->save()){
            	$Save->document_id  	= $mediaMaster->id;
            }
		}
		######### IMAGE UPLOAD ##########
		######### IMAGE UPLOAD ##########
		if($Save->save()){
			$ID 					= 	$Save->id;
			$ProductList  			= 	(isset($request['product']) && !empty($request['product'])) ?  json_decode($request['product'],true) 	: "";
			// $customer_id 			= 	Appoinment::where("appointment_id",$appointment_id)->value('customer_id');
			// $customer_location_id	= 	CustomerMaster::where("customer_id",$customer_id)->value('city');
			// $CUS_GST_STATE 			= 	ViewCityStateContryList::where("cityId",$customer_location_id)->value("display_state_code");
			$AppointmentData 		= 	Appoinment::where("appointment_id",$appointment_id)->first();
			$customer_id 			=   $AppointmentData->customer_id;
			$billing_address_id 	=   $AppointmentData->billing_address_id;
			$CusAddressData			= 	CustomerAddress::where("id",$billing_address_id)->first();
			$billing_address_city	= 	$CusAddressData->city;
			$CUS_GST_STATE 			= 	ViewCityStateContryList::where("cityId",$billing_address_city)->value("display_state_code");
			$department_id			= 	WmBatchCollectionMap::leftjoin('wm_batch_master as WBM',"WBM.batch_id",'=','wm_batch_collection_map.batch_id')
										->where("WBM.collection_id",$collection_id)
										->value('master_dept_id');
			$department_location_id	= 	WmDepartment::where("id",$department_id)->value('gst_state_code_id');
			$DEPT_GST_STATE 		= 	ViewCityStateContryList::where("gst_state_id",$department_location_id)->value("display_state_code");
			$FROM_SAME_STATE 		= ($DEPT_GST_STATE == $CUS_GST_STATE) ?  1 : 0;
			if(!empty($ProductList)){
				foreach($ProductList as $Raw){
					$CHANGE_IN 				= (isset($Raw['change_in']) && !empty($Raw['change_in'])) ?  $Raw['change_in'] 	: 0;
					if(!empty($CHANGE_IN)){
						$cgst_amt 			= 0;
						$sgst_amt 			= 0;
						$igst_amt 			= 0;
						$PHYSICAL_STOCK 	= (isset($Raw['physical_stock']) && !empty($Raw['physical_stock']))? $Raw['physical_stock'] : 0;
						$PRODUCT_ID 		= (isset($Raw['product_id']) && !empty($Raw['product_id']))? $Raw['product_id'] : 0;
						$collection_details_id= (isset($Raw['collection_detail_id']) && !empty($Raw['collection_detail_id']))? $Raw['collection_detail_id'] : 0;
						$CGST_RATE 			= (isset($Raw['cgst_rate']) && !empty($Raw['cgst_rate'])) ?  $Raw['cgst_rate'] 	: 0;
						$SGST_RATE 			= (isset($Raw['sgst_rate']) && !empty($Raw['sgst_rate'])) ?  $Raw['sgst_rate'] 	: 0;
						$IGST_RATE 			= (isset($Raw['igst_rate']) && !empty($Raw['igst_rate'])) ?  $Raw['igst_rate'] 	: 0;
						$notes_type 		= (isset($Raw['notes_type']) && !empty($Raw['notes_type'])) ?  $Raw['notes_type'] 	: 0;
						$Qty 				= (isset($Raw['quantity']) 	&& !empty($Raw['quantity']))  ?  $Raw['quantity'] 	: 0;;

						$Rate 				= (isset($Raw['rate']) 	&& !empty($Raw['rate'])) ?  $Raw['rate'] : 0;
						$ReviseQty 			= (isset($Raw['revised_quantity']) && !empty($Raw['revised_quantity'])) ?  $Raw['revised_quantity'] : 0;
						$ReviseRate 		= (isset($Raw['revised_rate']) 	&& !empty($Raw['revised_rate'])) ?  $Raw['revised_rate'] : 0;
						$GstAmount 			= (isset($Raw['gst_amount']) && !empty($Raw['gst_amount'])) ?  $Raw['gst_amount'] : 0;
						$NetAmount 			= (isset($Raw['net_amount']) && !empty($Raw['net_amount'])) ?  $Raw['net_amount'] : 0;
						$GrossAmount 		= (isset($Raw['gross_amount']) && !empty($Raw['gross_amount'])) ?  $Raw['gross_amount'] : 0;
						$NewQty 			= (isset($Raw['revised_quantity_value']) && !empty($Raw['revised_quantity_value'])) ?  $Raw['revised_quantity_value'] : 0;
						$NewRate 			= (isset($Raw['revised_rate_value']) && !empty($Raw['revised_rate_value'])) ?  $Raw['revised_rate_value'] : 0;
						$OutwardStock 		= (isset($Raw['outward_stock']) && !empty($Raw['outward_stock'])) ?  $Raw['outward_stock'] : 0;
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
						$INSERT 						= new PurchaseCreditDebitNoteDetailsMaster();
						$INSERT->note_id 				= $ID;
						$INSERT->collection_details_id	= $collection_details_id;
						$INSERT->change_in 				= $CHANGE_IN;
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
						$INSERT->outward_stock 			= $OutwardStock;
						$INSERT->is_from_same_state 	= $FROM_SAME_STATE;
						$INSERT->revised_gst_amount 	= _FormatNumberV2($RevisedGstAmt);
						$INSERT->revised_gross_amount	= _FormatNumberV2($RevisedGrossAmount);
						$INSERT->revised_net_amount		= _FormatNumberV2($RevisedNetAmount);
						$INSERT->created_by 			= $createdBy;
						$INSERT->updated_by 			= $updatedBy;
						$INSERT->physical_stock 		= $PHYSICAL_STOCK;
						if($INSERT->save()){
							$request['product_id']		= $PRODUCT_ID;
							$request['ref_id']			= $collection_details_id;
							if($CHANGE_IN ==  2){
								$request['quantity']	= $ReviseQty;
							}elseif($CHANGE_IN ==  3){
								$request['quantity']	= $ReviseQty;
							}
							$request['type']			= 'PC';
							$request['mrf_id']			= $department_id;
							$request['company_id']		= $CompanyID;
							$request['created_by']		= $createdBy;
							$request['updated_by']		= $updatedBy;
							// if($OutwardStock == 1){
							// 	OutWardLadger::AutoAddOutward($request);
							// }
							
						}
					}
				}
			}

			$Lumsumlist  	= (isset($request['lumpsum']) && !empty($request['lumpsum'])) ?  json_decode($request['lumpsum'],true) 	: "";
			if(!empty($Lumsumlist)){
				foreach($Lumsumlist as $Raw){
					$REVISED_NET 	= 0;
					$REVISED_GST 	= 0;
					$CGST_RATE 		= (isset($Raw['cgst_rate']) && !empty($Raw['cgst_rate'])) ?  $Raw['cgst_rate'] 	: 0;
					$SGST_RATE 		= (isset($Raw['sgst_rate']) && !empty($Raw['sgst_rate'])) ?  $Raw['sgst_rate'] 	: 0;
					$IGST_RATE 		= (isset($Raw['igst_rate']) && !empty($Raw['igst_rate'])) ?  $Raw['igst_rate'] 	: 0;
					$description	= (isset($Raw['description']) 	&& !empty($Raw['description']))  ?  $Raw['description'] : 0;;
					$remark			= (isset($Raw['remark']) 	&& !empty($Raw['remark']))  ?  $Raw['remark'] : 0;;
					$gross_amount 	= (isset($Raw['gross_amount']) && !empty($Raw['gross_amount'])) ?  $Raw['gross_amount'] : 0;
					$CHANGE_IN 	= CHANGE_IN_LUMSUM;
					if((!empty($remark) || !empty($description)) && (!empty($gross_amount))) {
						if(!empty($CHANGE_IN)){

							$cgst_amt 				= 0;
							$sgst_amt 				= 0;
							$igst_amt 				= 0;


							if($FROM_SAME_STATE) {
								$cgst_amt 		= ($CGST_RATE > 0) ? (($gross_amount / 100) * $CGST_RATE):0;
								$sgst_amt 		= ($SGST_RATE > 0) ? (($gross_amount / 100) * $SGST_RATE):0;
								$REVISED_GST 	= $cgst_amt + $sgst_amt;
							}else{
								$igst_amt 		= ($IGST_RATE > 0) ? (( $gross_amount / 100) * $IGST_RATE):0;
								$REVISED_GST 	= $igst_amt;
							}

							############ REVISED INVOICE CALCULATION #################
							$INSERT 						= new PurchaseCreditDebitNoteDetailsMaster();
							$INSERT->note_id 				= $ID;
							$INSERT->change_in 				= $CHANGE_IN;
							$INSERT->cgst_rate 				= $CGST_RATE;
							$INSERT->sgst_rate 				= $SGST_RATE;
							$INSERT->igst_rate 				= $IGST_RATE;
							$INSERT->description 			= $description;
							$INSERT->remark 				= $remark;
							$INSERT->is_from_same_state 	= $FROM_SAME_STATE;
							$INSERT->revised_gross_amount	= _FormatNumberV2($gross_amount);
							$INSERT->revised_gst_amount		= _FormatNumberV2($REVISED_GST);
							$INSERT->revised_net_amount		= _FormatNumberV2($gross_amount + $REVISED_GST);
							$INSERT->created_by 			= $createdBy;
							$INSERT->updated_by 			= $updatedBy;
							$INSERT->save();
						}
					}
				}
			}
		}
		return $ID;
	}

	/*
	Use 	: List Purchase credit debit notes
	Author 	: Hasmukhi
	Date 	: 04 August,2021
	*/
	public static function PurchaseCreditDebitNoteList($request)
	{
		$self 					= (new static)->getTable();
		$AdminUser  			= new AdminUser();
		$Appoinment  			= new Appoinment();
		$AppointmentCollection  = new AppointmentCollection();
		$CustomerMaster			= new CustomerMaster();
		$wm_batch_collection_map= new WmBatchCollectionMap();
		$wm_batch_master 		= new WmBatchMaster();
		$wm_department 			= new WmDepartment();
		$Today          		= date('Y-m-d');
		$sortBy         		= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$self.id";
		$sortOrder      		= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  		= !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     		= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId         		= GetBaseLocationCity();
		$data 					= self::with(['crn_document'])->select("$self.*",
																		\DB::raw("CONCAT(CM.first_name,' ',CM.last_name) AS customer_name"),
																		\DB::raw("WD.department_name AS department_name"),
																		\DB::raw("(CASE WHEN $self.notes_type = 0 THEN 'Credit'
																						WHEN $self.notes_type = 1 THEN 'Debit'
																				END ) AS note_type_name"),
																		\DB::raw("(CASE WHEN $self.status = 0 THEN 'Pending'
																						WHEN $self.status = 1 THEN 'First Level Approved'
																						WHEN $self.status = 3 THEN 'Approved'
																						WHEN $self.status = 2 THEN 'Rejected'
																			END ) AS status_name"),
																		\DB::raw("(CASE WHEN $self.transaction_type = 1 THEN 'Partial Transation'
																						WHEN $self.transaction_type = 2 THEN 'Full Transaction'
																			END ) AS transaction_type_name"),
																		\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
																		\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
																		\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) as approved_by_name"),
																		\DB::raw("if($self.first_level_approved_by = 0,'-',$self.first_level_approved_by) as first_level_approved_by"),
																		\DB::raw("CONCAT(U4.firstname,' ',U4.lastname) as first_level_approved_by_name"))
								->leftjoin($Appoinment->getTable()." as Appoinment","$self.appointment_id","=","Appoinment.appointment_id")
								->leftjoin($CustomerMaster->getTable()." as CM","CM.customer_id","=","Appoinment.customer_id")
								->leftjoin($AppointmentCollection->getTable()." as AppoinmentColl","$self.collection_id","=","AppoinmentColl.collection_id")
								->leftjoin($wm_batch_collection_map->getTable()." AS WBCM","WBCM.collection_id","=","AppoinmentColl.collection_id")
								->leftjoin($wm_batch_master->getTable()." AS WBM","WBM.batch_id","=","WBCM.batch_id")
								->leftjoin($wm_department->getTable()." AS WD","WBM.master_dept_id","=","WD.id")
								->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid")
								->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid")
								->leftjoin($AdminUser->getTable()." as U3","$self.approved_by","=","U3.adminuserid")
								->leftjoin($AdminUser->getTable()." as U4","$self.first_level_approved_by","=","U4.adminuserid")
								->where("WD.base_location_id",Auth()->user()->base_location);
		if($request->has('params.status'))
		{
			if($request->input('params.status') == "0") {
				$data->where("$self.status",$request->input('params.status'));
			} else if($request->input('params.status') == "1" || $request->input('params.status') == "2" || $request->input('params.status') == "3") {
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
		if($request->has('params.serial_no') && !empty($request->input('params.serial_no'))) {
			$data->where("$self.serial_no","like","%".$request->input('params.serial_no')."%");
		}
		if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate'))) {
			$data->whereBetween("$self.created_at",array(date("Y-m-d", strtotime($request->input('params.startDate')))." ".GLOBAL_START_TIME,date("Y-m-d", strtotime($request->input('params.endDate')))." ".GLOBAL_END_TIME));
		} else if(!empty($request->input('params.startDate'))) {
		   $datefrom 	= date("Y-m-d", strtotime($request->input('params.startDate')))." ".GLOBAL_START_TIME;
		   $dateto 		= date("Y-m-d", strtotime($request->input('params.startDate')))." ".GLOBAL_END_TIME;
		   $data->whereBetween("$self.created_at",array($datefrom,$datefrom));
		} else if(!empty($request->input('params.endDate'))) {
			$datefrom 	= date("Y-m-d", strtotime($request->input('params.startDate')))." ".GLOBAL_START_TIME;
			$dateto 	= date("Y-m-d", strtotime($request->input('params.startDate')))." ".GLOBAL_END_TIME;
			$data->whereBetween("$self.created_at",array($datefrom,$dateto));
		}
		$data->where("$self.company_id",Auth()->user()->company_id);
		$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage);
		if(!empty($result))
		{
			$toArray = $result->toArray();
			if(isset($toArray['totalElements']) && $toArray['totalElements']>0)
			{
				foreach($toArray['result'] as $key => $value)
				{
					$toArray['result'][$key]['crn_doc_url'] =  (!empty($value['document_id'])) ? $value['crn_document']['original_name'] : "";
					$toArray['result'][$key]['invoice_url'] =  ($value['status'] == 3 || $value['status'] == 1) ? url("/purchase-credit-note-invoice")."/".passencrypt($value['id'])."/".passencrypt($value['batch_id']) : "";
					########### APPROVAL SYSTEM ##################
					$firstLevelApprovalFlag = 0;
					$finalLevelApprovalFlag = 0;
					if($value['status'] == 0 && $value['first_level_approved_by'] == Auth()->user()->adminuserid) {
						$firstLevelApprovalFlag = AdminUserRights::checkUserAuthorizeForTrn(PURCHASE_CN_DN_FIRST_LEVEL_APPROVAL,Auth()->user()->adminuserid);
						$firstLevelApprovalFlag = ($firstLevelApprovalFlag > 0)?1:0;
					}
					if($value['status'] == 1) {
						$finalLevelApprovalFlag = AdminUserRights::checkUserAuthorizeForTrn(PURCHASE_CN_DN_FINAL_LEVEL_APPROVAL,Auth()->user()->adminuserid);
						$finalLevelApprovalFlag = ($finalLevelApprovalFlag > 0)?1:0;
					}
					$toArray['result'][$key]['first_level_approval_flag'] 	= $firstLevelApprovalFlag;
					$toArray['result'][$key]['final_level_approval_flag'] 	= $finalLevelApprovalFlag;
					########### APPROVAL SYSTEM ##################
					$toArray['result'][$key]['print_invoice'] 				= ($value["status"] == 3) ? 1 : 0;
					########### APPROVAL SYSTEM ##################
				}
			}
			$result = $toArray;
		}
		return $result;
	}

	/*
	Use 	: Get By Id Purchase credit debit notes
	Author 	: Hasmukhi
	Date 	: 04 August,2021
	*/
	public static function GetById($creditNoteId=0){
		$data 			= array();
		$lumpsumList 	= array();
		$productList	= array();
	 	$creditNote 	= self::find($creditNoteId);
		if($creditNote){
			$data 						= $creditNote;
			$data['id'] 				= $creditNote->id;
			$data['batch_id'] 			= $creditNote->batch_id;
			$data['company_id'] 		= $creditNote->company_id;
			$data['customer_id'] 		= $creditNote->customer_id;
			$data['mrf_id'] 			= $creditNote->mrf_id;
			$data['customer_name']		= ((!empty($creditNote->customer_id)) ? CustomerMaster::where('customer_id',$creditNote->customer_id)->value(DB::raw("CONCAT(first_name,' ',last_name)")) : "");
			$data['mrf_name']			= ((!empty($creditNote->mrf_id)) ? WmDepartment::where('id',$creditNote->mrf_id)->value('department_name') : "");
			$data['remarks'] 			= $creditNote->remarks;
			$data['serial_no'] 			= $creditNote->serial_no;
			$data['appointment_id'] 	= $creditNote->appointment_id;
			$data['collection_id'] 		= $creditNote->collection_id;
			$data['transaction_type']	= $creditNote->transaction_type;
			$data['change_date']		= $creditNote->change_date;
			$data['status']				= $creditNote->status;
			$data['invoice_no']			= $creditNote->invoice_no;
			$data['invoice_date']		= $creditNote->invoice_date;
			$data['status_name'] 		= "Pending";
			if($creditNote->status == 1){
				$data['status_name']	= "Approved";
			} else if($creditNote->status == 2) {
				$data['status_name'] 	= "Rejected";
			}else if($creditNote->status == 3) {
				$data['status_name'] 	= "First level Approved";
			}
			$data['approved_by']		= $creditNote->approved_by;
			$data['note_title'] 		= ($creditNote->notes_type == 1) ? "Debit" : "Credit";
			$data['notes_type'] 		= $creditNote->notes_type;
			$data['crn_doc_url'] 		= (!empty($creditNote->document_id)) ? $creditNote['crn_document']['original_name'] : "";
			$sameState 					= 0;
			if(!empty($creditNote->CreditDetails)){
				$i=0;
				foreach($creditNote->CreditDetails as $key => $value){

					$sameState 			= $value['is_from_same_state'];
					$rate 				= ($value['change_in'] == 1 || $value['change_in'] == 3) ? _FormatNumberV2($value['revised_rate']) : _FormatNumberV2($value['rate']);
					$qty 				= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $value['revised_quantity'] : $value['quantity'];
					if($value['change_in'] != CHANGE_IN_LUMSUM){
						$productList[$key]  = $value;
						$product 			= CompanyProductMaster::where("id",$value['product_id'])->first();
						$CREDIT_DEBIT_NOTE_NAME= APPOINTMENT_COLLECTION_CREDIT_DEBIT;
						foreach ($CREDIT_DEBIT_NOTE_NAME as $cr_db_key => $cr_db_value) {
							if($key != CHANGE_IN_LUMSUM){
								if($cr_db_key == $value['change_in']){
									$productList[$key]['change_in_name'] = $cr_db_value;
								}
							}
						}
						$productList[$key]['revised_rate'] 		= $value['revised_rate'];
						$productList[$key]['revised_quantity'] 	= _FormatNumberV2($value['revised_quantity']);
						$productList[$key]['new_rate'] 			= _FormatNumberV2($rate);
						$productList[$key]['new_quantity'] 		= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $qty : 0;
						$productList[$key]['product_name'] 		= ($product) ? $product->name 		: "";
						$productList[$key]['hsn_code'] 			= ($product) ? $product->hsn_code 	: "";
						############# ###################
						$return_type = "";
						if($value['change_in'] == 1){
							$return_type =  " (Rate Difference)";
						}elseif($value['change_in'] == 2 && $value['outawrd_stock'] == 2){
							$return_type =  " (Weight Difference)";
						}
						$productList[$key]['return_type'] 	= ucwords($return_type);
						$GST_AMOUNT  = ($value['is_from_same_state'] == "Y" && $value['revised_gst_amount'] > 0)  ? _FormatNumberV2($value['revised_gst_amount'] / 2) : _FormatNumberV2($value['revised_gst_amount']);
						$productList[$key]['cgst_amount'] = ($value['is_from_same_state'] == 1 && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
						$productList[$key]['sgst_amount'] = ($value['is_from_same_state'] == 1 && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
						$productList[$key]['igst_amount'] = ($value['is_from_same_state'] == 1 && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
					}

					if($value['change_in'] == CHANGE_IN_LUMSUM){
						$lumpsumList[$i] 							= $value;
						$lumpsumList[$i]['id'] 						= $value['id'];
						$lumpsumList[$i]['revised_gross_amount'] 	= _FormatNumberV2($value['revised_gross_amount']);
						$lumpsumList[$i]['revised_net_amount'] 		= _FormatNumberV2($value['revised_net_amount']);
						$lumpsumList[$i]['revised_gst_amount'] 		= _FormatNumberV2($value['revised_gst_amount']);
						$lumpsumList[$i]['cgst_rate'] 				= $value['cgst_rate'];
						$lumpsumList[$i]['sgst_rate'] 				= $value['sgst_rate'];
						$lumpsumList[$i]['igst_rate'] 				= $value['igst_rate'];
						$lumpsumList[$i]['description'] 			= $value['description'];
						$lumpsumList[$i]['remark']					= $value['remark'];
						$i++;
					}
				}
				$data['products'] 			= $productList;
				$data['lumpsum'] 			= $lumpsumList;
				$data['from_same_state']    = $sameState;
			}
		}
		// prd($data);
		return $data;
	}

	/*
	use 	: Approve Purchase Credit Note
	Author 	: Hasmukhi Patel
	Date 	: 05 August,2021
	*/
	public static function ApproveCreditDebitNote($request,$ApprovedBy=0,$CompanyID=0){
		$status 	= (isset($request['status']) && !empty($request['status'])) ? $request['status'] : 0;
		$Id 		= (isset($request['id']) && !empty($request['id'])) ? $request['id'] : 0;
		$GetData 	= self::find($Id);
		$flag 		= false;
		if($GetData){
			$AdminUserID 		= (!empty($ApprovedBy)?$ApprovedBy:Auth()->user()->adminuserid);
			$COMPANY_ID 		= $GetData->company_id;
			$MRF_ID 			= $GetData->mrf_id;
			$NOTES_TYPE 		= ($GetData->notes_type == 1) ? PURCHASE_DEBIT_NOTE : PURCHASE_CREDIT_NOTE;
			$GET_CODE 			= TransactionMasterCodesMrfWise::GetLastTrnCode($MRF_ID,$NOTES_TYPE);
			$CODE 				= 0;
			$CHALLAN_NO 		= 0;
			if($GET_CODE){
				$CODE 			= $GET_CODE->code_value + 1;
				$CHALLAN_NO 	= $GET_CODE->group_prefix.LeadingZero($CODE);
				$flag 			= true;
			}
			$GetData->status 		= $status;
			$GetData->change_date 	= date("Y-m-d");
			if($status == 1) {
				$GetData->first_level_approved_by 		= $AdminUserID;
				$GetData->first_level_approved_date 	= date("Y-m-d H:i:s");
			} else if($status ==  3) {
				$GetData->approved_by 		= $AdminUserID;
				$GetData->approved_date 	= date("Y-m-d H:i:s");
			}
			IF($status == 3 || $status == 2){
				if(empty($GetData->serial_no)) {
					$GetData->serial_no			= $CHALLAN_NO;
					TransactionMasterCodesMrfWise::UpdateTrnCode($MRF_ID,$NOTES_TYPE,$CODE);
				}
			}
			
			IF($GetData->save()){
				IF($status == 3){
					$PRODUCT_DATA = PurchaseCreditDebitNoteDetailsMaster::where("note_id",$Id)
					->whereIn("change_in",array(2,3))
					->where("outward_stock",1)
					->get()
					->toArray();
					if(!empty($PRODUCT_DATA)){
						foreach($PRODUCT_DATA AS $KEY => $VALUE){
							$PRODUCT_ID = $VALUE['product_id'];
							if($VALUE["physical_stock"] == 1){
								$INWARDDATA 						= array();
								$INWARDDATA['product_id'] 			= $PRODUCT_ID;
								$INWARDDATA['production_report_id']	= 0;
								$INWARDDATA['ref_id']				= $Id;
								$INWARDDATA['quantity']				= $VALUE["revised_quantity"];
								$INWARDDATA['type']					= "PC";
								$INWARDDATA['product_type']			= PRODUCT_PURCHASE;
								$INWARDDATA['batch_id']				= 0;
								$INWARDDATA['avg_price']			= _FormatNumberV2($VALUE['rate']);
								$INWARDDATA['mrf_id']				= $MRF_ID;
								$INWARDDATA['company_id']			= $COMPANY_ID;
								$INWARDDATA['inward_date']			= date("Y-m-d");
								$INWARDDATA['created_by']			= $AdminUserID;
								$INWARDDATA['updated_by']			= $AdminUserID;
								$inward_record_id 					= ProductInwardLadger::AutoAddInward($INWARDDATA);
								$STOCK_AVG_PRICE 					= WmBatchProductDetail::GetPurchaseProductAvgPriceN1($MRF_ID,$PRODUCT_ID,$inward_record_id);
								StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,date("Y-m-d"),$STOCK_AVG_PRICE);	
								$OUTWORDDATA 						= array();
								$OUTWORDDATA['sales_product_id'] 	= 0;
								$OUTWORDDATA['product_id'] 			= $PRODUCT_ID;
								$OUTWORDDATA['production_report_id']= 0;
								$OUTWORDDATA['ref_id']				= $Id;
								$OUTWORDDATA['quantity']			= $VALUE["revised_quantity"];
								$OUTWORDDATA['type']				= "PC";
								$OUTWORDDATA['product_type']		= PRODUCT_PURCHASE;
								$OUTWORDDATA['mrf_id']				= $MRF_ID;
								$OUTWORDDATA['company_id']			= $COMPANY_ID;
								$OUTWORDDATA['outward_date']		= date("Y-m-d");
								$OUTWORDDATA['created_by']			= $AdminUserID;
								$OUTWORDDATA['updated_by']			= $AdminUserID;
								OutWardLadger::AutoAddOutward($OUTWORDDATA);
							}else{
								$OUTWORDDATA 						= array();
								$OUTWORDDATA['sales_product_id'] 	= 0;
								$OUTWORDDATA['product_id'] 			= PRODUCT_INERT;
								$OUTWORDDATA['production_report_id']= 0;
								$OUTWORDDATA['ref_id']				= $Id;
								$OUTWORDDATA['quantity']			= $VALUE["revised_quantity"];
								$OUTWORDDATA['type']				= "PC";
								$OUTWORDDATA['product_type']		= PRODUCT_PURCHASE;
								$OUTWORDDATA['mrf_id']				= $MRF_ID;
								$OUTWORDDATA['company_id']			= $COMPANY_ID;
								$OUTWORDDATA['outward_date']		= date("Y-m-d");
								$OUTWORDDATA['created_by']			= $AdminUserID;
								$OUTWORDDATA['updated_by']			= $AdminUserID;
								OutWardLadger::AutoAddOutward($OUTWORDDATA);
							}
						}
					}
				}
			}
			if($status == 3 || $status == 2){
				$GetData->change_date 	= date("Y-m-d");
			}
			return true;
		}
		return false;
	}

	/*
	Use 	: Generate invoice of Purchase credit debit notes
	Author 	: Hasmukhi
	Date 	: 05 August,2021
	*/
	public static function GenerateCreditDebitInvoice($creditNoteId=0,$batch_id=0){
		$data 				= array();
		$PurchaseCrDbData 	= self::GetById($creditNoteId);
		$data 				= $PurchaseCrDbData;
		if($PurchaseCrDbData){
			$sameState 					= $PurchaseCrDbData['from_same_state'];
			$mrf_id 					= (isset($PurchaseCrDbData["mrf_id"]) && (!empty($PurchaseCrDbData["mrf_id"])) ? $PurchaseCrDbData["mrf_id"] : 0);
			$company_id 				= (isset($PurchaseCrDbData["company_id"]) && (!empty($PurchaseCrDbData["company_id"])) ? $PurchaseCrDbData["company_id"] : 0);
			$customer_id 				= (isset($PurchaseCrDbData["customer_id"]) && (!empty($PurchaseCrDbData["customer_id"])) ? $PurchaseCrDbData["customer_id"] : 0);
			
			$MRFDepartment 				= WmDepartment::select("wm_department.*",
											\DB::raw("LOWER(wm_department.address) as address"),
											\DB::raw("LOWER(location_master.city) as mrf_city_name"),
											\DB::raw("LOWER(GST.state_name) as mrf_state_name"),
											\DB::raw("GST.display_state_code as mrf_state_code"))
											->join("location_master","wm_department.location_id","=","location_master.location_id")
											->leftjoin("GST_STATE_CODES as GST","wm_department.gst_state_code_id","=","GST.id")
											->where("wm_department.id",$mrf_id)
											->first();
			######### COMPANY DETAILS ###############
			$companyDetails 			= CompanyMaster::select("company_master.*",
											\DB::raw("LOWER(location_master.city) as city_name"),
											\DB::raw("LOWER(SM.state_name) as state_name"),
											\DB::raw("GST.display_state_code as state_code"))
											->join("location_master","company_master.city","=","location_master.location_id")
											->leftjoin("state_master as SM","company_master.state","=","SM.state_id")
											->leftjoin("GST_STATE_CODES as GST","SM.gst_state_code_id","=","GST.id")
											->where('company_id',$company_id)->first();

			$appointment_id				=	$PurchaseCrDbData->appointment_id;
			$customer_id				=	$PurchaseCrDbData->customer_id;
			$AppointmentData 			= 	Appoinment::where("appointment_id",$appointment_id)->first();
			$billing_address_id 		=   $AppointmentData->billing_address_id;
			$shipping_address_id 		=   $AppointmentData->address_id;
			$CusBillAddressData			= 	CustomerAddress::where("id",$billing_address_id)->first();
			$billing_address_city		= 	$CusBillAddressData->city;
			$CUS_GST_STATE 				= 	ViewCityStateContryList::where("cityId",$billing_address_city)->value("display_state_code");
			$customerDetails 			=    CustomerAddress::select("customer_address.*",
											\DB::raw("CM.first_name AS first_name"),
											\DB::raw("CM.last_name AS last_name"),
											\DB::raw("LOWER(location_master.city) as city_name"),
											\DB::raw("LOWER(SM.state_name) as state_name"),
											\DB::raw("GST.display_state_code as customer_state_code"))
											->join("location_master","customer_address.city","=","location_master.location_id")
											->leftjoin("state_master as SM","customer_address.state","=","SM.state_id")
											->leftjoin("GST_STATE_CODES as GST","SM.gst_state_code_id","=","GST.id")
											->leftjoin("customer_master as CM","customer_address.customer_id","=","CM.customer_id")
											->where('customer_address.customer_id',$customer_id)
											->where('customer_address.id',$billing_address_id)
											->first();

			$ShippingDetails 			=    CustomerAddress::select("customer_address.*",
											\DB::raw("CM.first_name AS first_name"),
											\DB::raw("CM.last_name AS last_name"),
											\DB::raw("LOWER(location_master.city) as city_name"),
											\DB::raw("LOWER(SM.state_name) as state_name"),
											\DB::raw("GST.display_state_code as customer_state_code"))
											->join("location_master","customer_address.city","=","location_master.location_id")
											->leftjoin("state_master as SM","customer_address.state","=","SM.state_id")
											->leftjoin("GST_STATE_CODES as GST","SM.gst_state_code_id","=","GST.id")
											->leftjoin("customer_master as CM","customer_address.customer_id","=","CM.customer_id")
											->where('customer_address.customer_id',$customer_id)
											->where('customer_address.id',$shipping_address_id)
											->first();

			// $customerDetails 			= CustomerMaster::select("customer_master.*",
			// 								\DB::raw("LOWER(location_master.city) as city_name"),
			// 								\DB::raw("LOWER(SM.state_name) as state_name"),
			// 								\DB::raw("GST.display_state_code as customer_state_code"))
			// 								->join("location_master","customer_master.city","=","location_master.location_id")
			// 								->leftjoin("state_master as SM","customer_master.state","=","SM.state_id")
			// 								->leftjoin("GST_STATE_CODES as GST","SM.gst_state_code_id","=","GST.id")
			// 								->where('customer_id',$customer_id)->first();
			$data['mrf_address'] 		= isset($MRFDepartment->address) ? ucwords(strtolower($MRFDepartment->address)) : "";
			$data['mrf_city'] 			= isset($MRFDepartment->mrf_city_name) ? ucwords(strtolower($MRFDepartment->mrf_city_name)) : "";
			$data['mrf_gst_in'] 		= isset($MRFDepartment->gst_in) ? strtoupper(strtolower($MRFDepartment->gst_in)) : "";
			$data['mrf_state_name'] 	= isset($MRFDepartment->mrf_state_name) ? ucwords($MRFDepartment->mrf_state_name) : "";
			$data['mrf_state_code'] 	= isset($MRFDepartment->mrf_state_code) ? strtoupper($MRFDepartment->mrf_state_code) : "";
			$data['mrf_pincode'] 		= isset($MRFDepartment->pincode) ? strtoupper($MRFDepartment->pincode) : "";

			$data['company_title'] 		= ucwords(strtolower($companyDetails['company_name']));
			$data['company_address'] 	= ucwords(strtolower($companyDetails['address1']." ".$companyDetails['address2']));
			$data['company_city'] 		= ucwords(strtolower($companyDetails['city_name']));
			$data['company_gst_in'] 	= strtoupper($companyDetails["gst_no"]);
			$data['company_cin_no'] 	= strtoupper($companyDetails["cin_no"]);
			$data['company_state_name'] = ucwords(strtolower($companyDetails["state_name"]));
			$data['company_state_code'] = $companyDetails["state_code"];
			$data['company_zipcode'] 	= $companyDetails["zipcode"];
			$data['company_cin'] 		= "";
			######### CUSTOMER BILLING DETAILS ###############
			
			$data['customer_name'] 		= ucwords(strtolower($customerDetails['first_name']." ".$customerDetails['last_name']));
			$data['customer_address'] 	= ucwords(strtolower($customerDetails['address1']." ".$customerDetails["address2"]));
			$data['customer_gst_in'] 	= strtoupper(strtolower($customerDetails['gst_no']));
			$data['customer_state_name']= ucwords(strtolower($customerDetails['state_name']));
			$data['customer_city_name'] = $customerDetails['city_name'];
			$data['customer_pincode'] 	= $customerDetails['zipcode'];
			$data['customer_state_code']= $customerDetails["customer_state_code"];
			######### CUSTOMER SHIPPING DETAILS ###############

			$data['shipping_customer_name'] 	= ucwords(strtolower($ShippingDetails['first_name']." ".$ShippingDetails['last_name']));
			$data['shipping_address'] 			= ucwords(strtolower($ShippingDetails['address1']." ".$ShippingDetails["address2"]));
			$data['shipping_address_gst_in'] 	= strtoupper(strtolower($ShippingDetails['gst_no']));
			$data['shipping_address_state_name']= ucwords(strtolower($ShippingDetails['state_name']));
			$data['shipping_address_city_name'] = $ShippingDetails['city_name'];
			$data['shipping_address_pincode'] 	= $ShippingDetails['zipcode'];
			$data['shipping_address_state_code']= $ShippingDetails["customer_state_code"];
			######### OTHER DETAILS #############

			$productList 	= array();
			$creditNote 	= self::find($creditNoteId);
			if($creditNote){
				$data['mrf_id'] 		= $creditNote->mrf_id;
				$data['batch_id'] 		= $creditNote->batch_id;
				$data['serial_no'] 		= $creditNote->serial_no;
				$data['dated'] 			= (!empty($creditNote->change_date)) ? date("Y-m-d",strtotime($creditNote->change_date)) : "";
				$data['note_title'] 	= ($creditNote->notes_type == 1) ? "Debit" : "Credit";
				$data['notes_type'] 	= $creditNote->notes_type;

				######### QR CODE GENERATION OF E INVOICE NO #############
				if(!empty($creditNote->CreditDetails)){
					foreach($creditNote->CreditDetails as $key => $value){

						$rate 				= ($value['change_in'] == 1 || $value['change_in'] == 3) ? _FormatNumberV2($value['revised_rate']) : _FormatNumberV2($value['rate']);
						$qty 				= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $value['revised_quantity'] : $value['quantity'];
						$productList[$key]  = $value;
						$product 			= CompanyProductMaster::where("id",$value['product_id'])->first();
						$productList[$key]['original_qty'] 		= $value['quantity'];
						$productList[$key]['original_rate'] 	= _FormatNumberV2($value['rate']);
						$productList[$key]['invoice_rate'] 		= _FormatNumberV2($rate);
						$productList[$key]['invoice_qty'] 		= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $qty : 0;
						$productList[$key]['product_name'] 		= ($product) ? $product->name 		: "";
						$productList[$key]['hsn_code'] 			= ($product) ? $product->hsn_code 	: "";
						$productList[$key]['change_in'] 		= $value['change_in'];
						############# ###################
						$return_type = "";
						if($value['change_in'] == 1){
							$return_type =  " (Rate Difference)";
						}elseif($value['change_in'] == 2 && $value['outward_stock'] == 2){
							$return_type =  " (Weight Difference)";
						}
						$productList[$key]['return_type'] 	= ucwords($return_type);
						$GST_AMOUNT  = ($value['is_from_same_state'] == 1 && $value['revised_gst_amount'] > 0)  ? _FormatNumberV2($value['revised_gst_amount'] / 2) : _FormatNumberV2($value['revised_gst_amount']);
						$productList[$key]['cgst_amount'] = ($value['is_from_same_state'] == 1 && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
						$productList[$key]['sgst_amount'] = ($value['is_from_same_state'] == 1 && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
						$productList[$key]['igst_amount'] = ($value['is_from_same_state'] == 0 && $GST_AMOUNT > 0) ? $GST_AMOUNT : 0;
					}
				}
			}
			$data['credit_note_no'] 	= ($creditNote) ? $creditNote->serial_no : "";
			$data['remarks'] 			= ($creditNote) ? ucwords(strtolower($creditNote->remarks)) : "";
			$data['products'] 			= $productList;
			$data['from_same_state'] 	= $sameState;
		}
		return $data;
	}

	/*
	use 	: Credit Debit Report
	Author 	: Hasmukhi Patel
	Date 	: 05 Octomber,2020
	*/
	public static function CreditDebitNoteReport($request)
	{
		$self 			= (new static)->getTable();
		$AdminUser  	= new AdminUser();
		$Dispatch  		= new WmDispatch();
		$Department  	= new WmDepartment();
		$SalesMaster 	= new WmSalesMaster();
		$Product 		= new CompanyProductMaster();
		$Invoice 		= new WmInvoices();
		$Customer 		= new CustomerMaster();
		$ProductQuality	= new CompanyProductQualityParameter();
		$Details 		= new PurchaseCreditDebitNoteDetailsMaster();
		$Appoinment 	= new Appoinment();
		$Today 			= date('Y-m-d');
		$sortBy 		= ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "$self.id";
		$sortOrder 		= ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage 	= !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber 	= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId 		= GetBaseLocationCity();
		$data 			= self::select("$self.*","DETAILS.*",
										\DB::raw("(CASE WHEN DETAILS.change_in = 0 THEN '-'
													WHEN DETAILS.change_in = 1 THEN 'Rate'
													WHEN DETAILS.change_in = 2 THEN 'Quantity'
													WHEN DETAILS.change_in = 3 THEN 'Rate & Quantity'
											END ) AS change_in_name"),
										\DB::raw("(CASE WHEN $self.status = 0 THEN 'Pending'
													WHEN $self.status = 1 THEN 'Final Approved'
													WHEN $self.status = 2 THEN 'Rejected'
													WHEN $self.status = 3 THEN 'Approved'
										END ) AS status_name"),
										\DB::raw("(CASE WHEN $self.notes_type = 0 THEN 'Credit'
													WHEN $self.notes_type = 1 THEN 'Debit'
											END ) AS note_type_name"),
										\DB::raw("CUST.gst_no as gstin_no"),
										\DB::raw("CUST.net_suit_code as customer_net_suit_code"),
										\DB::raw("CONCAT(CUST.first_name,' ',CUST.last_name) as customer_name"),
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
										\DB::raw("DEPT.department_name"),
										\DB::raw("CONCAT(PRODUCT.name,' ',PRODUCT_QUALITY.parameter_name) as product_name"),
										\DB::raw("PRODUCT.net_suit_code as product_net_suit_code"),					
										\DB::raw("PRODUCT.hsn_code"),
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"),
										\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) as approved_by_name"),
										\DB::raw("CONCAT(U4.firstname,' ',U4.lastname) as first_level_approved_by_name"))
		->join($Details->getTable()." as DETAILS","$self.id","=","DETAILS.note_id")
		->leftjoin($Appoinment->getTable()." as APP","$self.appointment_id","=","APP.appointment_id")
		->leftjoin($Department->getTable()." as DEPT","$self.mrf_id","=","DEPT.id")
		->leftjoin($Customer->getTable()." as CUST","$self.customer_id","=","CUST.customer_id")
		->leftjoin($Product->getTable()." as PRODUCT","DETAILS.product_id","=","PRODUCT.id")
		->leftjoin($ProductQuality->getTable()." as PRODUCT_QUALITY","PRODUCT_QUALITY.product_id","=","PRODUCT.id")
		->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid")
		->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid")
		->leftjoin($AdminUser->getTable()." as U3","$self.approved_by","=","U3.adminuserid")
		->leftjoin($AdminUser->getTable()." as U4","$self.first_level_approved_by","=","U4.adminuserid");
		if($request->has('mrf_id') && !empty($request->input('mrf_id'))) {
			$data->whereIn("$self.mrf_id",$request->input('mrf_id'));
		}
		if($request->has('serial_no') && !empty($request->input('serial_no'))) {
			$data->where("$self.serial_no","like","%".$request->input('serial_no')."%");
		}
		if($request->has('invoice_no') && !empty($request->input('invoice_no'))) {
			$data->where("$self.invoice_no","like","%".$request->input('invoice_no')."%");
		}
		if($request->has('product_id') && !empty($request->input('product_id'))) {
			$data->where("DETAILS.product_id",$request->input('product_id'));
		}
		if($request->has('cust_id') && !empty($request->input('cust_id'))) {
			$data->where("$self.customer_id",$request->input('cust_id'));
		}
		if($request->has('is_paid') && !empty($request->input('is_paid'))) {
			if($request->input('is_paid') == strtolower(AUDIT_STATUS_YES)) {
				$is_paid = 1;
			} elseif($request->input('is_paid') == strtolower(AUDIT_STATUS_NO)) {
				$is_paid = 0;
			}
			$data->where("APP.is_paid",$is_paid);
		}
		if(!empty($request->input('startDate')) && !empty($request->input('endDate'))) {
			$data->whereBetween("$self.change_date",array(date("Y-m-d", strtotime($request->input('startDate'))),date("Y-m-d",strtotime($request->input('endDate')))));
		} else if(!empty($request->input('startDate'))) {
		   $datefrom = date("Y-m-d", strtotime($request->input('startDate')));
		   $data->whereBetween("$self.change_date",array($datefrom,$datefrom));
		} else if(!empty($request->input('endDate'))) {
		   $data->whereBetween("$self.change_date",array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
		}
		if($request->has('net_suit_code') && !empty($request->input('net_suit_code'))) {
			$data->where("PRODUCT.net_suit_code",$request->input('net_suit_code'));
		}
		if($request->has('change_in')) {
			if($request->input('change_in') == "0") {
				$data->where("DETAILS.change_in",$request->input('change_in'));
			} else if($request->input('change_in') == "1") {
				$data->where("DETAILS.change_in",$request->input('change_in'));
			}
		}
		if($request->has('status')) {
			if($request->input('status') == "0") {
				$data->where("$self.status",$request->input('status'));
			} else if($request->input('status') == "1" || $request->input('status') == "2" || $request->input('status') == "3") {
				$data->where("$self.status",$request->input('status'));
			}
		}
		if($request->has('notes_type')) {
			if($request->input('notes_type') == "0") {
				$data->where("$self.notes_type",$request->input('notes_type'));
			} else if($request->input('notes_type') == "1") {
				$data->where("$self.notes_type",$request->input('notes_type'));
			}
		}
		$result 			= $data->orderBy($sortBy, $sortOrder)->get()->toArray();
		$TOTAL_NET_AMT 		= 0;
		$TOTAL_GROSS_AMT 	= 0;
		$TOTAL_GST_AMT 		= 0;
		$TOTAL_QTY 			= 0;
		$TOTAL_ORG_QTY 		= 0;
		$TOTAL_CGST_AMT		= 0;
		$TOTAL_SGST_AMT		= 0;
		$TOTAL_IGST_AMT		= 0;

		if(!empty($result)) {
			foreach($result as $key => $value) {
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
				$TOTAL_CGST_AMT 	+= $CGST_AMT;
				$TOTAL_SGST_AMT 	+= $SGST_AMT;
				$TOTAL_IGST_AMT 	+= $IGST_AMT;
			}
		}
		$array 						= array();
		$array["result"] 			= $result;
		$array["TOTAL_QTY"] 		= _FormatNumberV2($TOTAL_QTY);
		$array["TOTAL_ORG_QTY"] 	= _FormatNumberV2($TOTAL_ORG_QTY);
		$array["TOTAL_NET_AMT"] 	= _FormatNumberV2($TOTAL_NET_AMT);
		$array["TOTAL_GST_AMT"] 	= _FormatNumberV2($TOTAL_GST_AMT);
		$array["TOTAL_GROSS_AMT"] 	= _FormatNumberV2($TOTAL_GROSS_AMT);
		$array['TOTAL_CGST_AMT'] 	= _FormatNumberV2($TOTAL_CGST_AMT);
		$array['TOTAL_SGST_AMT'] 	= _FormatNumberV2($TOTAL_SGST_AMT);
		$array['TOTAL_IGST_AMT'] 	= _FormatNumberV2($TOTAL_IGST_AMT);
		return $array;
	}
	/*
	Use 	: SendCreditDebitNoteApprovalEmail
	Author 	: Kalpak Prajapati
	Date 	: 16 July,2019
	*/
	public static function SendCreditDebitNoteApprovalEmail($Level="first",$creditNoteId=0,$InvoiceId=0,$AdminUserRight=0)
	{
		$data 				= array();
		$InvoiceData 		= self::GenerateCreditDebitInvoice($creditNoteId);
		if($InvoiceData)
		{
			######### COMPANY DETAILS ###############
			$data['mrf_name'] 			= isset($InvoiceData['mrf_name'])?ucwords(strtolower($InvoiceData['mrf_name'])):"";
			######### CLIENT DETAILS ###############
			$data['customer_name'] 		= ucwords(strtolower($InvoiceData['customer_name']));
			######### OTHER DETAILS #############
			$data['invoice_no'] 		= $InvoiceData['invoice_no'];
			$data['invoice_date'] 		= $InvoiceData['invoice_date'];
			$productList 				= array();
			$sameState 					= "";
			$creditNote 				= $InvoiceData;
			if($creditNote)
			{
				if (strtolower($Level) != "first") {
					$WmDepartment 	= WmDepartment::select("base_location_id")->where("id",$creditNote->mrf_id)->first();
					$AdminUserData 		= AdminUser::select("adminuser.adminuserid","adminuser.email")
										->leftjoin("user_base_location_mapping as BLM","BLM.adminuserid","=","adminuser.adminuserid")
										->leftjoin("adminuserrights as AUR","AUR.adminuserid","=","adminuser.adminuserid")
										->where("BLM.base_location_id",$WmDepartment->base_location_id)
										->where("AUR.trnid",$AdminUserRight)
										->where("adminuser.status","A")
										->where("adminuser.email","!=","")
										->whereNotNull("adminuser.email")
										->whereRaw("FIND_IN_SET(".$creditNote->mrf_id.",adminuser.assign_mrf_id)")
										->get()
										->toArray();
				} else {
					$AdminUserData 		= AdminUser::select("adminuser.adminuserid","adminuser.email")
										->where("adminuser.adminuserid",$creditNote->first_level_approved_by)
										->where("adminuser.status","A")
										->where("adminuser.email","!=","")
										->whereNotNull("adminuser.email")
										->get()
										->toArray();
				}
				if (!empty($AdminUserData))
				{
					$data['mrf_id'] 			= $creditNote->mrf_id;
					$data['serial_no'] 			= $creditNote->serial_no;
					// $data['invoice_id'] 		= $creditNote->invoice_id;
					$data['dated'] 				= (!empty($creditNote->change_date)) ? date("Y-m-d",strtotime($creditNote->change_date)) : "";
					$data['note_title'] 		= ($creditNote->notes_type == 1)? "Debit" : "Credit";
					$data['notes_type'] 		= $creditNote->notes_type;
					$data['appointment_id'] 		= $creditNote->appointment_id;
					if(!empty($creditNote->CreditDetails))
					{
						foreach($creditNote->CreditDetails as $key => $value)
						{
							$sameState 								= ($value['is_from_same_state'] == "Y") ? true : false;
							$rate 									= ($value['change_in'] == 1 || $value['change_in'] == 3)?_FormatNumberV2($value['revised_rate']):_FormatNumberV2($value['rate']);
							$qty 									= ($value['change_in'] == 2 || $value['change_in'] == 3)?$value['revised_quantity']:$value['quantity'];
							$productList[$key]  					= $value;
							$product 								= $value['product_name'];
							$productList[$key]['original_qty'] 		= $value['quantity'];
							$productList[$key]['original_rate'] 	= _FormatNumberV2($value['rate']);
							$productList[$key]['invoice_rate'] 		= _FormatNumberV2($rate);
							$productList[$key]['invoice_qty'] 		= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $qty : 0;
							$productList[$key]['product_name'] 		= $value['product_name'];
							$productList[$key]['hsn_code'] 			= $value['hsn_code'];
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
					foreach($AdminUserData as $AdminUser)
					{
						$ToEmail 		= $AdminUser["email"];
						$Subject 		= "New Purchase ".$data['note_title']." Note ".ucwords($Level)." Level Approval Request - ".$data['credit_note_no'];
						if ($creditNote->notes_type == 1) {
							$APPROVE_LINK 	= env("APP_URL")."/purchase-request-".$Level."-approval/debit-note/approve/".encode($creditNoteId)."/".encode($AdminUser['adminuserid']);
							$REJECT_LINK 	= env("APP_URL")."/purchase-request-".$Level."-approval/debit-note/reject/".encode($creditNoteId)."/".encode($AdminUser['adminuserid']);
						} else {
							$APPROVE_LINK 	= env("APP_URL")."/purchase-request-".$Level."-approval/credit-note/approve/".encode($creditNoteId)."/".encode($AdminUser['adminuserid']);
							$REJECT_LINK 	= env("APP_URL")."/purchase-request-".$Level."-approval/credit-note/reject/".encode($creditNoteId)."/".encode($AdminUser['adminuserid']);
						}
						$arrEmailData	= array(
										"NoteDetails"=>$data,
										"HeaderTitle"=>"Purchase ".$data['note_title']." Note ".$data['credit_note_no'],
										"APPROVE_LINK"=>$APPROVE_LINK,
										"REJECT_LINK"=>$REJECT_LINK );
						
						$sendEmail 		= Mail::send("email-template.purchase_credit_note_approve_email",$arrEmailData,function ($message) use ($ToEmail,$Subject) {
							$message->to($ToEmail);
							$message->subject($Subject);
						});
					}
				}
			}
		}
	}

	/*
	use 	: Bulk Approve Purchase Credit Note
	Author 	: Kalpak Prajapati
	Date 	: 22 March,2023
	*/
	public static function BulkApproveCreditDebitNote($request)
	{
		$record_ids = (isset($request['record_id']) && !empty($request['record_id'])) ? $request['record_id'] : array();
		$flag 		= false;
		if (!empty($record_ids)) {
			$record_ids = array_unique($record_ids);
			foreach ($record_ids as $Id) {
				$GetData 	= self::find($Id);
				if($GetData)
				{
					$AdminUserID 			= Auth()->user()->adminuserid;
					$COMPANY_ID 			= $GetData->company_id;
					$MRF_ID 				= $GetData->mrf_id;
					$NOTES_TYPE 			= ($GetData->notes_type == 1) ? PURCHASE_DEBIT_NOTE : PURCHASE_CREDIT_NOTE;
					$GET_CODE 				= TransactionMasterCodesMrfWise::GetLastTrnCode($MRF_ID,$NOTES_TYPE);
					$CODE 					= 0;
					$CHALLAN_NO 			= 0;
					$CurrentStatus 			= $GetData->status;
					$firstLevelApprovalFlag = 0;
					$finalLevelApprovalFlag = 0;
					if($CurrentStatus == 0) {
						$firstLevelApprovalFlag = AdminUserRights::checkUserAuthorizeForTrn(PURCHASE_CN_DN_FIRST_LEVEL_APPROVAL,Auth()->user()->adminuserid);
					} else if($CurrentStatus == 1) {
						$finalLevelApprovalFlag = AdminUserRights::checkUserAuthorizeForTrn(PURCHASE_CN_DN_FINAL_LEVEL_APPROVAL,Auth()->user()->adminuserid);
					}
					if ($firstLevelApprovalFlag) {
						$NewStatus = 1;
					} else if ($finalLevelApprovalFlag) {
						$NewStatus = 3;
					} else {
						$NewStatus = 0;
					}
					if (empty($NewStatus)) continue;
					if($GET_CODE) {
						$CODE 			= $GET_CODE->code_value + 1;
						$CHALLAN_NO 	= $GET_CODE->group_prefix.LeadingZero($CODE);
					}
					$GetData->status 		= $NewStatus;
					$GetData->change_date 	= date("Y-m-d");
					if($NewStatus == 1) {
						$GetData->first_level_approved_by 		= $AdminUserID;
						$GetData->first_level_approved_date 	= date("Y-m-d H:i:s");
					} else if($NewStatus ==  3) {
						$GetData->approved_by 		= $AdminUserID;
						$GetData->approved_date 	= date("Y-m-d H:i:s");
					}
					if($NewStatus == 3 || $NewStatus == 2) {
						if(empty($GetData->serial_no)) {
							$GetData->serial_no	= $CHALLAN_NO;
							TransactionMasterCodesMrfWise::UpdateTrnCode($MRF_ID,$NOTES_TYPE,$CODE);
						}
					}
					if($GetData->save())
					{
						$flag = true;
						if($NewStatus == 3)
						{
							$PRODUCT_DATA = PurchaseCreditDebitNoteDetailsMaster::where("note_id",$Id)
													->whereIn("change_in",array(2,3))
													->where("outward_stock",1)
													->get()
													->toArray();
							if(!empty($PRODUCT_DATA))
							{
								foreach($PRODUCT_DATA AS $KEY => $VALUE)
								{
									$PRODUCT_ID = $VALUE['product_id'];
									if($VALUE["physical_stock"] == 1)
									{
										$INWARDDATA 						= array();
										$INWARDDATA['product_id'] 			= $PRODUCT_ID;
										$INWARDDATA['production_report_id']	= 0;
										$INWARDDATA['ref_id']				= $Id;
										$INWARDDATA['quantity']				= $VALUE["revised_quantity"];
										$INWARDDATA['type']					= "PC";
										$INWARDDATA['product_type']			= PRODUCT_PURCHASE;
										$INWARDDATA['batch_id']				= 0;
										$INWARDDATA['avg_price']			= _FormatNumberV2($VALUE['rate']);
										$INWARDDATA['mrf_id']				= $MRF_ID;
										$INWARDDATA['company_id']			= $COMPANY_ID;
										$INWARDDATA['inward_date']			= date("Y-m-d");
										$INWARDDATA['created_by']			= $AdminUserID;
										$INWARDDATA['updated_by']			= $AdminUserID;
										$inward_record_id 					= ProductInwardLadger::AutoAddInward($INWARDDATA);
										$STOCK_AVG_PRICE 					= WmBatchProductDetail::GetPurchaseProductAvgPriceN1($MRF_ID,$PRODUCT_ID,$inward_record_id);

										StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,date("Y-m-d"),$STOCK_AVG_PRICE);

										$OUTWORDDATA 						= array();
										$OUTWORDDATA['sales_product_id'] 	= 0;
										$OUTWORDDATA['product_id'] 			= $PRODUCT_ID;
										$OUTWORDDATA['production_report_id']= 0;
										$OUTWORDDATA['ref_id']				= $Id;
										$OUTWORDDATA['quantity']			= $VALUE["revised_quantity"];
										$OUTWORDDATA['type']				= "PC";
										$OUTWORDDATA['product_type']		= PRODUCT_PURCHASE;
										$OUTWORDDATA['mrf_id']				= $MRF_ID;
										$OUTWORDDATA['company_id']			= $COMPANY_ID;
										$OUTWORDDATA['outward_date']		= date("Y-m-d");
										$OUTWORDDATA['created_by']			= $AdminUserID;
										$OUTWORDDATA['updated_by']			= $AdminUserID;
										OutWardLadger::AutoAddOutward($OUTWORDDATA);
									} else {
										$OUTWORDDATA 						= array();
										$OUTWORDDATA['sales_product_id'] 	= 0;
										$OUTWORDDATA['product_id'] 			= PRODUCT_INERT;
										$OUTWORDDATA['production_report_id']= 0;
										$OUTWORDDATA['ref_id']				= $Id;
										$OUTWORDDATA['quantity']			= $VALUE["revised_quantity"];
										$OUTWORDDATA['type']				= "PC";
										$OUTWORDDATA['product_type']		= PRODUCT_PURCHASE;
										$OUTWORDDATA['mrf_id']				= $MRF_ID;
										$OUTWORDDATA['company_id']			= $COMPANY_ID;
										$OUTWORDDATA['outward_date']		= date("Y-m-d");
										$OUTWORDDATA['created_by']			= $AdminUserID;
										$OUTWORDDATA['updated_by']			= $AdminUserID;
										OutWardLadger::AutoAddOutward($OUTWORDDATA);
									}
								}
							}
						}
					}
				}
			}
		}
		return $flag;
	}
}