<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmClientMaster;
use App\Models\WmDepartment;
use App\Models\LocationMaster;
use App\Models\CompanyMaster;
use App\Models\WmSalesMaster;
use App\Models\WmDispatch;
use App\Models\WmProductMaster;
use App\Models\GSTStateCodes;
use App\Models\ShippingAddressMaster;
use App\Models\WmDispatchProduct;
use App\Models\ProductInwardLadger;
use App\Models\InvoiceAdditionalCharges;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;
class WmInvoices extends Model implements Auditable
{
	protected 	$table 		=	'wm_invoices';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	private 	$exclude_cat=	array("RDF");
	use AuditableTrait;
	/*
	use 	: Get Last invoice id
	Author 	: Axay Shah
	Date 	: 04 July,2019
	*/
	public static function LastInvoiceId(){
		return self::max('id');
	}
	public function setDeliveryNoteAttribute($value)
    {
        $this->attributes['delivery_note'] = (!empty($value) && $value != "null") ? $value : "";
    }
    public function setOtherReferenceAttribute($value)
    {
        $this->attributes['other_reference'] = (!empty($value) && $value != "null") ? $value : "";
    }
    public function setBuyerOrderNoAttribute($value)
    {
        $this->attributes['buyer_order_no'] = (!empty($value) && $value != "null") ? $value : "";
    }
    public function setDispatchDocNoAttribute($value)
    {
        $this->attributes['dispatch_doc_no'] = (!empty($value) && $value != "null") ? $value : "";
    }	 
    public function setDispatchThroughAttribute($value)
    {
        $this->attributes['dispatched_through'] = (!empty($value) && $value != "null") ? $value : "";
    }
    public function setTermsOfDeliveryAttribute($value)
    {
        $this->attributes['terms_of_delivery'] = (!empty($value) && $value != "null") ? $value : "";
    }	
    public function setBillOfLadingAttribute($value)
    {
        $this->attributes['bill_of_lading'] = (!empty($value) && $value != "null") ? $value : "";
    }
	/*
	Use 	: Add Invoice Data
	Author 	: Axay Shah
	Date 	: 04 July 2019
	*/
	public static function InsertInvoiceDetail($request){
		$InvoiceId = 0;
		$Invoice 							=  new self();
		$Invoice->client_master_id 			= (isset($request['client_master_id']) && !empty($request['client_master_id'])) ? $request['client_master_id'] :0;
		$Invoice->invoice_no 				= (isset($request['invoice_no']) && !empty($request['invoice_no'])) ? $request['invoice_no'] :0;
		$Invoice->dispatch_id 				= (isset($request['id']) && !empty($request['id'])) ? $request['id'] :0;
		$Invoice->nespl 					= (isset($request['nespl']) && !empty($request['nespl'])) ? $request['nespl'] :0;
		$Invoice->invoice_date 				= (isset($request['invoice_date']) && !empty($request['invoice_date'])) ? $request['invoice_date'] :"";
		$Invoice->sales_id 					= (isset($request['sales_id']) 	&& !empty($request['sales_id'])) ? $request['sales_id'] :0;
		$Invoice->collect_payment_status 	= (isset($request['collect_payment_status']) && !empty($request['collect_payment_status'])) ? $request['collect_payment_status'] :0;
		$Invoice->master_dept_id 			= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ? $request['master_dept_id'] :0;
		$Invoice->created_by 				= Auth()->user()->adminuserid;
		$Invoice->exclude_gst				= (isset($request['exclude_gst']) && !empty($request['exclude_gst'])) ? $request['exclude_gst'] : "N";
		$Invoice->challan_no				= (isset($request['challan_no']) && !empty($request['challan_no'])) ? $request['challan_no'] : "";
		$Invoice->delivery_note				= (isset($request['delivery_note']) && !empty($request['delivery_note'])) ? $request['delivery_note'] : "";
		$Invoice->eway_bill					= (isset($request['eway_bill']) && !empty($request['eway_bill'])) ? $request['eway_bill'] : "";
		$Invoice->other_reference			= (isset($request['other_reference']) && !empty($request['other_reference'])) ? $request['other_reference'] : "";
		$Invoice->buyer_order_no			= (isset($request['buyer_order_no']) && !empty($request['buyer_order_no'])) ? $request['buyer_order_no'] : "";
		$Invoice->dated						= (isset($request['dated']) && !empty($request['dated'])) ? $request['dated'] : "";
		$Invoice->dispatch_doc_no			= (isset($request['dispatch_doc_no']) && !empty($request['dispatch_doc_no'])) ? $request['dispatch_doc_no'] : "";
		$Invoice->dispatch_address			= (isset($request['dispatch_address']) && !empty($request['dispatch_address'])) ? $request['dispatch_address'] : "";
		$Invoice->vehicle_number			= (isset($request['vehicle_number']) && !empty($request['vehicle_number'])) ? $request['vehicle_number'] : "";
		$Invoice->dispatched_through		= (isset($request['dispatched_through']) && !empty($request['dispatched_through'])) ? $request['dispatched_through'] : "";
		$Invoice->destination				= (isset($request['destination']) && !empty($request['destination'])) ? $request['destination'] : "";
		$Invoice->bill_of_lading			= (isset($request['bill_of_lading']) && !empty($request['bill_of_lading'])) ? $request['bill_of_lading'] : "";

		$Invoice->terms_of_delivery			= (isset($request['terms_of_delivery']) && !empty($request['terms_of_delivery'])) ? $request['terms_of_delivery'] : "";
		$Invoice->bill_of_lading			= (isset($request['bill_of_lading']) && !empty($request['bill_of_lading'])) ? $request['bill_of_lading'] : "";
		$Invoice->shipping_address			= (isset($request['shipping_address']) && !empty($request['shipping_address'])) ? $request['shipping_address'] : "";
		$Invoice->shipping_city				= (isset($request['shipping_city']) && !empty($request['shipping_city'])) ? $request['shipping_city'] : "";
		$Invoice->shipping_state			= (isset($request['shipping_state']) && !empty($request['shipping_state'])) ? $request['shipping_state'] : "";
		$Invoice->shipping_state_code		= (isset($request['shipping_state_code']) && !empty($request['shipping_state_code'])) ? $request['shipping_state_code'] : "";
		$Invoice->shipping_pincode			= (isset($request['shipping_pincode']) && !empty($request['shipping_pincode'])) ? $request['shipping_pincode'] : "";

		$Invoice->invoice_dispatch_type			= (isset($request['invoice_dispatch_type']) && !empty($request['invoice_dispatch_type'])) ? $request['invoice_dispatch_type'] : "";
		$Invoice->invoice_recyclable_type			= (isset($request['invoice_recyclable_type']) && !empty($request['invoice_recyclable_type'])) ? $request['invoice_recyclable_type'] : "";

		if($Invoice->save()){
			$InvoiceId = $Invoice->id;
		}
		return $InvoiceId;
	}

	/*
	Use 	: List Invoice
	Author 	: Axay Shah
	Date 	: 08 July,2019
	*/
	public static function SearchInvoice($request,$paginate = true,$client_id = 0){
		$data1 			= array();
		$table 			= (new static)->getTable();
		$ClientMaster 	= new WmClientMaster();
		$LocationMaster	= new LocationMaster();
		$DepartmentTbl	= new WmDepartment();
		$DispatchTbl 	= new WmDispatch();
		

		$Client 		= $ClientMaster->getTable();
		$Location		= $LocationMaster->getTable();
		$Department		= $DepartmentTbl->getTable();
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
		$sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
		$recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
		$pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$cityId         = GetBaseLocationCity();
		$InvoiceNo 		= ($request->has('params.invoice_no') && !empty($request->input('params.invoice_no'))) ? $request->input('params.invoice_no') : "";
		$collectStatus 		= ($request->has('params.collect_payment_status') && !empty($request->input('params.collect_payment_status'))) ? $request->input('params.collect_payment_status')  : "";
		$InvoiceStatus	= ($request->has('params.invoice_status') && !empty($request->input('params.invoice_status'))) ? $request->input('params.invoice_status') : 0;
		$ClientName 	= ($request->has('params.client_name') && !empty($request->input('params.client_name'))) ? $request->input('params.client_name') : "";
		$FromDate 		= ($request->has('params.startDate') && !empty($request->input('params.startDate'))) ? date("Y-m-d",strtotime($request->input('params.startDate'))) :"" ;
		$EndDate 		= ($request->has('params.endDate') && !empty($request->input('params.endDate'))) ? date("Y-m-d",strtotime($request->input('params.endDate'))) : "";
		$data 	= self::select("$table.*",
							"$table.dispatch_id",
							\DB::raw("CASE WHEN 1=1 THEN(
								SELECT COUNT(wm_payment_receive.id)
								FROM wm_payment_receive
								WHERE
								wm_payment_receive.invoice_id = wm_invoices.id
							) END AS paid_status"),

							"C.client_name",
							\DB::raw("'' as payment_status"),
							\DB::raw("L.city as city_name"),
							\DB::raw("DEP.location_id"),
							\DB::raw("CASE WHEN 1=1 THEN (SELECT wm_sales_master.net_amount FROM wm_sales_master WHERE wm_sales_master.dispatch_id = DISPATCH.id GROUP BY wm_sales_master.dispatch_id) END AS TOTAL_INVOICE_AMT"),
							\DB::raw("CASE WHEN 1=1 THEN (SELECT sum(wm_payment_receive.received_amount) FROM wm_payment_receive WHERE wm_payment_receive.invoice_id = $table.id GROUP BY wm_payment_receive.invoice_id) END AS TOTAL_RECEIVED_AMT"),
							\DB::raw("CASE WHEN 1=1 THEN
								(
									SELECT SUM(wm_invoices_credit_debit_notes_details.revised_net_amount)
									FROM wm_invoices_credit_debit_notes_details
									INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
									INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
									WHERE wm_dispatch.approval_status = 1
									AND wm_invoices_credit_debit_notes.notes_type = 0
									AND wm_invoices_credit_debit_notes.status = 3
									AND wm_invoices_credit_debit_notes.invoice_id = $table.id
								) END AS CN_Amount"),
							\DB::raw("CASE WHEN 1=1 THEN
								(
									SELECT SUM(wm_invoices_credit_debit_notes_details.revised_net_amount)
									FROM wm_invoices_credit_debit_notes_details
									INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
									INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
									WHERE wm_dispatch.approval_status = 1
									AND wm_invoices_credit_debit_notes.notes_type = 1
									AND wm_invoices_credit_debit_notes.status = 3
									AND wm_invoices_credit_debit_notes.invoice_id = $table.id
								) END AS DN_Amount")
						)

		->join($Client." as C","$table.client_master_id","=","C.id")
		->join($DispatchTbl->getTable()." AS DISPATCH","DISPATCH.id","=","$table.dispatch_id")
		->leftjoin("$Location as L","C.city_id","=","L.location_id")
		->leftjoin("$Department as DEP","$table.master_dept_id","=","DEP.id");
		if(!empty($client_id)){
			$data->where("C.id",$client_id);	
		}
		$data->where("C.company_id",Auth()->user()->company_id);
		if($request->has('params.nespl'))
		{
			$nespl = $request->input('params.nespl');
			if($nespl == "0"){
				$data->where("$table.nespl",$nespl );
			}elseif($nespl == "1"){
				$data->where("$table.nespl", $nespl);
			}
		}
		if($request->has('params.city_id') && !empty($request->input('params.city_id')))
		{
			$data->whereIn('DEP.location_id', explode(",",$request->input('params.city_id')));
		}else if(empty($client_id)){
			$data->whereIn("DEP.location_id",$cityId);
		}
		if(!empty($InvoiceNo))
		{
			$data->where("$table.invoice_no","like","%$InvoiceNo%");
		}
		if(!empty($ClientName))
		{
			$data->where("C.client_name","like","%$ClientName%");
		}

		if(!empty($FromDate) && !empty($EndDate)){
			$data->whereBetween("$table.created_at",array($FromDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		}elseif(!empty($FromDate)){
			$data->whereBetween("$table.created_at",array($FromDate." ".GLOBAL_START_TIME,$FromDate." ".GLOBAL_END_TIME));
		}elseif(!empty($EndDate)){
			$data->whereBetween("$table.created_at",array($EndDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		}

		if($paginate){
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			if($result->total()> 0){
				$data1 		= $result->toArray();
				$dataResult = $data1['result'];
				foreach($dataResult as $key => $field){
					$total_invoice_amount 	= $field['TOTAL_INVOICE_AMT'];
					$total_cn_amount 	 	= $field['CN_Amount'];
					$total_paid_amount 	 	= $field['TOTAL_RECEIVED_AMT'];
					$total_dn_amount 	 	= $field['DN_Amount'];

					$remaining_amount = ((($total_invoice_amount - $total_cn_amount) - $total_paid_amount) + $total_dn_amount);

					$data1['result'][$key]["total_invoice_amount"] 	= !empty($total_invoice_amount)?_FormatNumberV2($total_invoice_amount):number_format(_FormatNumberV2(0),2);
					$data1['result'][$key]["total_paid_amount"] 	= !empty($total_paid_amount)?_FormatNumberV2($total_paid_amount):number_format(_FormatNumberV2(0),2);
					$data1['result'][$key]["total_cn_amount"] 		= !empty($total_cn_amount)?_FormatNumberV2($total_cn_amount):number_format(_FormatNumberV2(0),2);
					$data1['result'][$key]["total_dn_amount"] 		= !empty($total_dn_amount)?_FormatNumberV2($total_dn_amount):number_format(_FormatNumberV2(0),2);
					$data1['result'][$key]["remaing_amount"] 		= !empty($remaining_amount) && ($remaining_amount > 0)?_FormatNumberV2($remaining_amount):number_format(_FormatNumberV2(0),2);

					$data1['result'][$key]['encypt_id'] 	= passencrypt($field['id']);
					$data1['result'][$key]['invoice_url'] 	= url("/")."/".INVOICE_URL."/".passencrypt($field['id']);
					$InvoiceAmount 							=  WmSalesMaster::where("invoice_no",$field['id'])->where("dispatch_id",$field['dispatch_id'])->sum('net_amount');
					$AdditionalChargesAmount 				= 0;
					$AdditionalCharges 						= InvoiceAdditionalCharges::GetInvoiceAdditionalCharges($field['dispatch_id'],$field['id']);
					if (!empty($AdditionalCharges))
					{
						foreach($AdditionalCharges as $chargekey => $chargevalue){
						$AdditionalChargesAmount += $chargevalue['net_amount'];
						}
					}
					$DispatchData 								= WmDispatch::where("id",$field['dispatch_id'])->first();
					$RentAmount 								= (!empty($DispatchData) && $DispatchData->total_rent_amt) ? $DispatchData->total_rent_amt : 0;
					$TCS_AMT									= (!empty($DispatchData) && $DispatchData->tcs_amount) ? $DispatchData->tcs_amount : 0;
					$Discount_Amount							= (!empty($DispatchData) && $DispatchData->discount_amt) ? $DispatchData->discount_amt : 0;
					$data1['result'][$key]['invoice_amount'] 	= (!empty($InvoiceAmount)) ? ($InvoiceAmount + $AdditionalChargesAmount + $RentAmount + $TCS_AMT - $Discount_Amount): 0;
					$data1['result'][$key]['rent_amount'] 		= $RentAmount;
					$data1['result'][$key]['tcs_amount']		= $TCS_AMT;
					$data1['result'][$key]['additional_charge_amount'] = $AdditionalChargesAmount;
					
					$payment_status = '';
					if($field['collect_payment_status'] == 1 && $field['invoice_status']== 0){
						$payment_status = 'Completed';
					}elseif($field['collect_payment_status'] == 0 && $field['invoice_status'] == 0){
						$seconds_diff 	= strtotime(date("Y-m-d")) - strtotime(substr($field['created_at'],0,10));
						$day 			= floor($seconds_diff/3600/24);
						if($day>7) {
							$payment_status = 'Late';
						}
						if($field['invoice_status'] == 0) {
							$payment_status 	= 'Partial';
						}
					}
					$data1['result'][$key]['payment_status'] = $payment_status;
				}
			}
		}
		return $data1;
	}

	/*
	Use 	: List Invoice
	Author 	: Axay Shah
	Date 	: 08 July,2019
	*/
	public static function GetById($InvoiceId,$fromCnDn=0)
	{
		$ProductMaster 	=  	new WmProductMaster();
		$SalesMaster 	=  	new WmSalesMaster();
		$ClientMaster 	=  	new WmClientMaster();
		$DispatchMaster	=  	new WmDispatch();
		$DispatchProduct=  	new WmDispatchProduct();
		$CompanyMaster	=   new CompanyMaster();
		$LocationMaster	=   new LocationMaster();
		$VehicleMaster	=   new VehicleMaster();
		$GSTTABLE		=   new GSTStateCodes();
		$Self			=  	(new static)->getTable();
		$Client			=  	$ClientMaster->getTable();
		$Sales 			=  	$SalesMaster->getTable();
		$Product 		=  	$ProductMaster->getTable();
		$Dispatch		=  	$DispatchMaster->getTable();
		$DispatchPro    =  	$DispatchProduct->getTable();
		$Company 		=   $CompanyMaster->getTable();
		$Location 		=   $LocationMaster->getTable();
		$Vehicle 		=   $VehicleMaster->getTable();
		$GST 			=   $GSTTABLE->getTable();
		$result 		=  	array();
		$data 			= 	self::select("$Self.*","$Self.invoice_date as created_at",
							\DB::raw("IF($Self.invoice_dispatch_type = ".RECYCLEBLE_TYPE.",'Tax Invoice','Bill of Supply') as invoice_title"),
							\DB::raw("IF($Self.invoice_dispatch_type = ".RECYCLEBLE_TYPE.",1,1) as gst_on_flag"),
							"C.client_name",
							"C.contact_person",
							"C.address",
							"C.shipping_address as client_shipping_address",
							"C.city_id",
							"C.company_id",
							"C.pincode",
							"C.latitude",
							"C.longitude",
							"C.contact_no",
							"C.mobile_no",
							"C.VAT",
							"C.CST",
							"C.email",
							"C.pan_no",
							"C.gstin_no",
							"C.service_no",
							"C.gst_state_code",
							"C.taxes",
							"C.tcs_tax_allow",
							"C.payment_mode",
							"C.days",
							"C.freight_born",
							"C.rates",
							"C.remarks",
							"C.rejection_term",
							"C.introduced_by",
							"C.product_consumed",
							"C.material_consumed",
							"C.material_alias",
							"$Self.master_dept_id",
							"C.status",
							"C.created_by",
							"C.updated_by",
							"C.updated_at",
							"GST.state_name as client_state_name",
							"GST.display_state_code as gst_state_code",
							"LOC_CL.city as client_city_name",
							\DB::raw("now() as POS"))->where("$Self.id",$InvoiceId)
							->join("$Client as C","$Self.client_master_id","=","C.id")
							->leftjoin("$Location as LOC_CL","C.city_id","=","LOC_CL.location_id")
							->leftjoin("$GST as GST","C.gst_state_code","=","GST.state_code")
							->first();
		if($data) {
			$data['encypt_id'] 		=  passencrypt($data->id);
			$data['invoice_url'] 	=  url("/")."/".INVOICE_URL."/".passencrypt($data->id);
			$InvoiceTitleFlag 		=  false;
			$data['invoice_title'] 	= $data->invoice_title;
			if(!empty($data->sales_id)) {
				$salesID 	= 	explode(",",$data->sales_id);
				$SalesData 	= 	WmSalesMaster::select("$Sales.*","P.*","$Sales.cgst_rate as cgst","$Sales.sgst_rate as sgst","$Sales.igst_rate as igst",
								\DB::raw("(CASE
									WHEN D.master_dept_state_code > 0 AND D.master_dept_state_code = D.destination_state_code THEN 'Y'
									WHEN D.master_dept_state_code = 0 AND D.origin_state_code = D.destination_state_code THEN 'Y'
									ELSE 'N'
								END) AS is_from_same_state"),
								\DB::raw("'Kg' as UOM"))
								->join("$Product as P","$Sales.product_id","=","P.id")
								->leftjoin("$Dispatch as D","$Sales.dispatch_id","=","D.id")
								->whereIn("$Sales.id",$salesID)
								->get();
				$dispatchId 	= 0;
				$company_id 	= 0;
				####### BILL OF SUPPLY ONLY FOR AFR ############
				
				####### BILL OF SUPPLY ONLY FOR AFR ############
				if(!empty($SalesData)) {
					foreach($SalesData as $key => $value){
						$SalesData[$key]['description'] = WmDispatchProduct::where("dispatch_id",$value->dispatch_id)->where('id',$value->dispatch_product_id)->value('description');
						$SalesData[$key]['is_charges'] = 0;
						$dispatchId 			= $SalesData[0]->dispatch_id;
						$data['invoice_title'] 	= ($data->invoice_dispatch_type == RECYCLEBLE_TYPE && $value->product_id == SALES_AFR_PRODUCT) ?
						"Bill of Supply" : $data->invoice_title;
						$InvoiceTitleFlag = ($SalesData[$key]['gst_amount'] > 0) ? true : false; 
					}
				}
				$data['invoice_title'] 	= ($InvoiceTitleFlag) ? "Tax Invoice" : $data['invoice_title'];
				####### Invoice Additional Charges ############
				
					$AdditionalCharges = InvoiceAdditionalCharges::GetInvoiceAdditionalCharges(0,$InvoiceId);
				if($fromCnDn == 0){
					if (!empty($AdditionalCharges))
					{
						$counter = sizeof($SalesData);
						$counter++;
						foreach ($AdditionalCharges as $AdditionalCharge) {
							$NewRow['title'] 		= $AdditionalCharge['charge_name'];
							$NewRow['description'] 	= "";
							$NewRow['hsn_code'] 	= $AdditionalCharge['hsn_code'];
							$NewRow['quantity'] 	= intval($AdditionalCharge['totalqty']);
							$NewRow['rate'] 		= $AdditionalCharge['rate'];
							$NewRow['gross_amount']	= $AdditionalCharge['gross_amount'];
							$NewRow['gst_amount']	= $AdditionalCharge['gst_amount'];
							$NewRow['net_amount']	= $AdditionalCharge['net_amount'];
							$NewRow['cgst']			= $AdditionalCharge['cgst'];
							$NewRow['cgst_amount']	= $AdditionalCharge['cgst_amount'];
							$NewRow['sgst']			= $AdditionalCharge['sgst'];
							$NewRow['sgst_amount']	= $AdditionalCharge['sgst_amount'];
							$NewRow['igst']			= $AdditionalCharge['igst'];
							$NewRow['igst_amount']	= $AdditionalCharge['igst_amount'];
							$NewRow['is_charges'] 	= 1;
							$SalesData->push(new WmSalesMaster($NewRow));
							$counter++;
						}
					}
				}
				$data->additional_charges 	= (!empty($AdditionalCharges)) ? $AdditionalCharges : array();
				####### Invoice Additional Charges ############

				$consignee_name 		= ShippingAddressMaster::select("shipping_address_master.*")
				->join("wm_dispatch","shipping_address_master.id","=","wm_dispatch.shipping_address_id")
				->where("wm_dispatch.id",$dispatchId)
				->first();
				
				$data->consignee_name 	= (!empty($consignee_name)) ? $consignee_name->consignee_name : $data->client_name;
				$data->consignee_gst 	= (!empty($consignee_name)) ? $consignee_name->gst_no : "";
				$data->shipping_state_code = (!empty($consignee_name)) ? GSTStateCodes::where("state_code",$consignee_name->state_code)->value('display_state_code') : "";
				$data->shipping_address = (!empty($consignee_name)) ? $consignee_name->shipping_address : "";
				$data->shipping_state 	= (!empty($consignee_name)) ? $consignee_name->state : "";
				$data->shipping_city 	= (!empty($consignee_name)) ? $consignee_name->city : "";
				$data->sales_list 		= $SalesData;

				$companyDetails 		= WmDispatch::select(
											"COM.company_id",
											"COM.company_name",
											"COM.address1",
											"COM.address2",
											"COM.city",
											"COM.zipcode",
											"COM.phone_office",
											"COM.gst_no",
											"COM.pan",
											"COM.cin_no",
											"$Dispatch.from_mrf",
											"$Dispatch.origin",
											"$Dispatch.origin_state_code",
											"$Dispatch.origin_city",
											"COM.cin_no",
											"$Dispatch.rent_amt",
											"$Dispatch.total_rent_amt",
											"$Dispatch.discount_amt",
											"$Dispatch.rent_cgst",
											"$Dispatch.rent_sgst",
											"$Dispatch.rent_igst",
											"$Dispatch.rent_gst_amt",
											"$Dispatch.type_of_transaction",
											"$Dispatch.bill_from_mrf_id",
											"$Dispatch.master_dept_id",
											"$Dispatch.tcs_rate",
											"$Dispatch.tcs_amount",
											"$Dispatch.e_invoice_no",
											"$Dispatch.acknowledgement_no",
											"$Dispatch.acknowledgement_date",
											"$Dispatch.show_vendor_name_flag",
											"$Dispatch.signed_qr_code",
											"$Dispatch.client_master_id",
											"V.vehicle_number",
											"$Dispatch.remarks",
											"PARA1.para_value as days",
											"PARA.para_value as transport_cost_name",
											\DB::raw("LOC_S.gst_state_code_id as state_code"),
											\DB::raw("LOC_S.state_name as state_name"),
											\DB::raw("LOC.city as city_name"))
				->join("company_master as COM","$Dispatch.company_id","=","COM.company_id")
				->leftjoin("$Location as LOC","COM.city","=","LOC.location_id")
				->leftjoin("state_master as LOC_S","COM.state","=","LOC_S.state_id")
				->leftjoin("$Vehicle as V","$Dispatch.vehicle_id","=","V.vehicle_id")
				->leftjoin("parameter as PARA","$Dispatch.transport_cost_id","=","PARA.para_id")
				->leftjoin("parameter as PARA1","$Dispatch.collection_cycle_term","=","PARA1.para_id")
				->where("$Dispatch.id",$dispatchId)
				->first();

				$BILL_FROM_MRF_ID 	= 	($companyDetails) ? $companyDetails->bill_from_mrf_id : 0;
				$MASTER_DEPT_ID 	= 	($companyDetails) ? $companyDetails->master_dept_id : 0;

				$MasterMRFDepartment 	= 	WmDepartment::select("wm_department.*",
											\DB::raw("LOWER(wm_department.address) as address"),
											\DB::raw("LOWER(location_master.city) as mrf_city_name"),
											\DB::raw("LOWER(GST.state_name) as mrf_state_name"),
											\DB::raw("GST.display_state_code as mrf_state_code"))
											->join("location_master","wm_department.location_id","=","location_master.location_id")
											->leftjoin("GST_STATE_CODES as GST","wm_department.gst_state_code_id","=","GST.id")
											->where("wm_department.id",$MASTER_DEPT_ID)
											->first();
				$data->MasterMRFDepartment 	= 	$MasterMRFDepartment;


				$MRFDepartment 		= 	WmDepartment::select("wm_department.*",
										\DB::raw("LOWER(wm_department.address) as address"),
										\DB::raw("LOWER(location_master.city) as mrf_city_name"),
										\DB::raw("LOWER(GST.state_name) as mrf_state_name"),
										\DB::raw("GST.display_state_code as mrf_state_code"))
										->join("location_master","wm_department.location_id","=","location_master.location_id")
										->leftjoin("GST_STATE_CODES as GST","wm_department.gst_state_code_id","=","GST.id")
										->where("wm_department.id",$BILL_FROM_MRF_ID)
										->first();
				$data->MRFDepartment 	= $MRFDepartment;
				$data->company_details 	= ($companyDetails) ? $companyDetails : "";
				$data->vehicle_number 	= ($companyDetails) ? $companyDetails->vehicle_number 	: "";
				$data->rent_amt 		= ($companyDetails) ? $companyDetails->rent_amt 	: 0;
				$data->total_rent_amt 	= ($companyDetails) ? $companyDetails->total_rent_amt : 0;
				$data->discount_amt 	= ($companyDetails) ? $companyDetails->discount_amt : 0;
				$data->rent_sgst 		= ($companyDetails) ? $companyDetails->rent_sgst 	: 0;
				$data->rent_cgst 		= ($companyDetails) ? $companyDetails->rent_cgst 	: 0;
				$data->rent_igst 		= ($companyDetails) ? $companyDetails->rent_igst 	: 0;
				$data->rent_gst_amt 	= ($companyDetails) ? $companyDetails->rent_gst_amt : 0;
				$data->tcs_amount 		= ($companyDetails) ? $companyDetails->tcs_amount : 0;
				$data->tcs_rate 		= ($companyDetails) ? $companyDetails->tcs_rate : 0;
				$data->e_invoice_no 	= ($companyDetails) ? $companyDetails->e_invoice_no : "";
				$data->type_of_transaction 	= ($companyDetails) ? $companyDetails->type_of_transaction : 0;
				$data->remarks 			= ($companyDetails) ? $companyDetails->remarks : "";
				$data->transport_cost_name 	= ($companyDetails) ? $companyDetails->transport_cost_name : "";
				$data->tcs_tax_percent 	= TCS_TEX_PERCENT;
				$vendor_address 		= "";
				$vendor_city 			= "";
				$vendor_state_code 		= "";
				$vendor_state_name 		= "";
				$vendor_name 			= "";
				$show_vendor_name_flag  = ($companyDetails) ? $companyDetails->show_vendor_name_flag : 0;
				if(isset($companyDetails) && $companyDetails->from_mrf == "N"){
					$customer 			= CustomerMaster::where("customer_id",$companyDetails->origin)->first();
					$vendor_name 		= ($customer) ? $customer->first_name." ".$customer->middle_name." ".$customer->last_name : "";
					$vendor_address 	= ($customer) ? $customer->address1." ".$customer->address2 : "";
					$vendor_city 		= ($customer) ? LocationMaster::where("location_id",$companyDetails->origin_city)->value("city") : "";
					$vendor_state_code 	= ($companyDetails) ? $companyDetails->origin_state_code : "";
					$vendor_state_name 	= ($companyDetails) ? GSTStateCodes::where("id",$companyDetails->origin_state_code)->value("state_name") : "";
				}else{
					$vendor_name 		= (isset($data->MasterMRFDepartment->department_name)) ? $data->MasterMRFDepartment->department_name : "";
					$vendor_address 	= (isset($data->MasterMRFDepartment->address)) ? $data->MasterMRFDepartment->address : "";
					$vendor_city 		= (isset($data->MasterMRFDepartment->mrf_city_name)) ? $data->MasterMRFDepartment->mrf_city_name : "";
					$vendor_state_code 	= (isset($data->MasterMRFDepartment->mrf_state_code)) ? $data->MasterMRFDepartment->mrf_state_code : "";
					$vendor_state_name 	= (isset($data->MasterMRFDepartment->mrf_state_name)) ? $data->MasterMRFDepartment->mrf_state_name : "";
				}
				######### QR CODE GENERATION OF E INVOICE NO #############
				$qr_code 				= "";
				$e_invoice_no 			= (!empty($companyDetails->e_invoice_no)) 		? $companyDetails->e_invoice_no : "";
				$acknowledgement_no 	= (!empty($companyDetails->acknowledgement_no)) 	? $companyDetails->acknowledgement_no : "";
				$acknowledgement_date 	= (!empty($companyDetails->acknowledgement_date)) ? $companyDetails->acknowledgement_date : "";
				$signed_qr_code 		= (!empty($companyDetails->signed_qr_code)) ? $companyDetails->signed_qr_code : "";
				$qr_code_string 		= "E-Invoice No. :".$e_invoice_no." Acknowledgement No. : ".$acknowledgement_no." Acknowledgement Date : ".$acknowledgement_date;
				$qr_code_string 		= (empty($e_invoice_no) && empty($acknowledgement_no) && empty($acknowledgement_date)) ? " " : $qr_code_string ;
				if(!empty($e_invoice_no) || !empty($acknowledgement_no) || !empty($acknowledgement_date)){
					$QRCODE 				= url("/")."/".GetQRCode($signed_qr_code,$dispatchId);
					$path 					= public_path("/")."phpqrcode/".$dispatchId.".png";
					$type 					= pathinfo($path, PATHINFO_EXTENSION);
					if(file_exists($path)){
						$imgData				= file_get_contents($path);
						$qr_code 				= 'data:image/' . $type . ';base64,' . base64_encode($imgData);
						unlink(public_path("/")."/phpqrcode/".$dispatchId.".png");
					}
				}
				// $data->signature_flag 		= ($companyDetails && $companyDetails->client_master_id == 567) ? false : true;
				$data->signature_flag 		= true;
				$data->qr_code 				= $qr_code;
				$data->e_invoice_no 		= $e_invoice_no ;
				$data->acknowledgement_no 	= $acknowledgement_no;
				$data->acknowledgement_date = $acknowledgement_date;
				######### QR CODE GENERATION OF E INVOICE NO #############
				$data->vendor_name 			= ($show_vendor_name_flag > 0) ? $vendor_name : "";
				$data->vendor_address 		= ucwords(strtolower($vendor_address));
				$data->vendor_city 			= ucwords(strtolower($vendor_city));
				$data->vendor_state_code 	= $vendor_state_code;
				$data->vendor_state_name 	= ucwords(strtolower($vendor_state_name));
				$data->days 				= ($companyDetails) ? $companyDetails->days : "";
			}
		}
		return $data;
	}

	///////
	public static function GetByIdOnly($InvoiceId)
	{
		return self::select('*')->where("invoice_no",$InvoiceId)->first()->toArray();
	}

	/*
	Use 	: Add Payment
	Author 	: Axay Shah
	Date 	: 16 July,2019
	*/
	public static function OneInvoice($Id = 0){
		$WmClient 	= new WmClientMaster();
		$Client 	= $WmClient->getTable();
		$Invoice 	= (new static)->getTable();
		$data 		= self::select("$Invoice.*","C.client_name")
		->join("$Client as C","$Invoice.client_master_id","=","C.id")
		->where("$Invoice.id",$Id)
		->first();
		return $data;
	}

	/*
	Use 	: Cancle Invoice
	Author 	: Axay Shah
	Date 	: 16 July,2019
	*/
	public static function CancelInvoice($request){
		$InvoiceId 		= (isset($request->invoice_id) && !empty($request->invoice_id)) ? $request->invoice_id : 0 ;
		$cancel_reason			= (isset($request->cancel_reason) && !empty($request->cancel_reason)) ? $request->cancel_reason : '' ;
		$InvoiceData 	= self::OneInvoice($InvoiceId);
		if($InvoiceData){
			$SalesIds 			= explode(",",$InvoiceData->sales_id);
			$DispatchIds 		= WmSalesMaster::whereIn("id",$SalesIds)->pluck("dispatch_id");
			$UpdateInvoiceTbl 	= self::where("id",$InvoiceId)->update(["invoice_status"=>1,"cancel_reason"=>$cancel_reason]);
			$UpdateDispatchTbl 	= WmDispatch::whereIn("id",$DispatchIds)->update(["invoice_generated"=>'0','invoice_cancel'=>1]);
			$UpdateSalesTbl 	= WmSalesMaster::whereIn("id",$SalesIds)->update(["payment_done"=>'0',"invoice_status"=>"1"]);
			$GetProduct 		= WmDispatchProduct::whereIn("dispatch_id",$DispatchIds)->get()->toArray();

			if(!empty($GetProduct)){

				foreach($GetProduct as $pro){
					$DispatchDetail 					= WmDispatch::select("master_dept_id","company_id")
														->where("id",$pro['dispatch_id'])->first();
					$INWARDDATA['purchase_product_id'] 	= 0;
					$INWARDDATA['product_id'] 			= $pro['product_id'];
					$INWARDDATA['production_report_id']	= 0;
					$INWARDDATA['ref_id']				= $pro['dispatch_id'];
					$INWARDDATA['quantity']				= $pro['quantity'];
					$INWARDDATA['type']					= TYPE_INWARD;
					$INWARDDATA['product_type']			= PRODUCT_SALES;
					$INWARDDATA['batch_id']				= 0;
					$INWARDDATA['mrf_id']				= ($DispatchDetail) ? $DispatchDetail->master_dept_id : 0;
					$INWARDDATA['company_id']			= ($DispatchDetail) ? $DispatchDetail->company_id : 0;
					$INWARDDATA['inward_date']			= date("Y-m-d");
					$INWARDDATA['created_by']			= Auth()->user()->adminuserid;
					$INWARDDATA['updated_by']			= Auth()->user()->adminuserid;
					ProductInwardLadger::AutoAddInward($INWARDDATA);
				}
			}
			LR_Modules_Log_CompanyUserActionLog($request,$InvoiceId);
			return true;
		}
		return false;
	}

	/*
	Use 	: Cancle Invoice
	Author 	: Axay Shah
	Date 	: 16 July,2019
	*/
	public static function GenerateCreditInvoice($creditNoteId=0,$InvoiceId=0){
		$data 				= array();
		$InvoiceData 		= self::GetById($InvoiceId);
		if($InvoiceData){
			######### COMPANY DETAILS ###############
			$companyDetails 			= $InvoiceData['company_details'];
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
				$data['dated'] 			= (!empty($creditNote->change_date)) ? date("Y-m-d",strtotime($creditNote->change_date)) : "";
				$data['note_title'] 	= ($creditNote->notes_type == 1) ? "Debit" : "Credit";
				if(!empty($creditNote->CreditDetails)){
					foreach($creditNote->CreditDetails as $key => $value){
						$sameState 			= ($value['is_from_same_state'] == "Y") ? true : false;
						$rate 				= ($value['change_in'] == 1 || $value['change_in'] == 3) ? _FormatNumberV2($value['revised_rate']) : _FormatNumberV2($value['rate']);
						$qty 				= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $value['revised_quantity'] : $value['quantity'];
						$productList[$key]  		= $value;
						$product 			= WmProductMaster::where("id",$value['product_id'])->first();
						$productList[$key]['original_qty'] 		= $value['quantity'];
						$productList[$key]['original_rate'] 	= _FormatNumberV2($value['rate']);
						$productList[$key]['invoice_rate'] 		= _FormatNumberV2($rate);
						$productList[$key]['invoice_qty'] 		= ($value['change_in'] == 2 || $value['change_in'] == 3) ? $qty : 0;
						$productList[$key]['product_name'] 		= ($product) ? $product->title 		: "";
						$productList[$key]['hsn_code'] 			= ($product) ? $product->hsn_code 	: "";
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
	Use 	: List Outstanding Ledger Report
	Author 	: Axay Shah
	Date 	: 03 September,2021
	*/
	public static function OutStandingLedgerReport($request)
	{
		$Today 			= date("Y-m-d");
		$result 		= array();
		$self 			= (new static)->getTable();
		$DispatchTbl 	= new WmDispatch();
		$clientTbl 		= new WmClientMaster();
		$DeptTbl 		= new WmDepartment();
		$Parameter 		= new Parameter();
		$assignBaseId 	= GetUserAssignedBaseLocation();
		$mrf_id 		= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : array();
		$FromDate 		= isset($request->startDate) && !empty($request->startDate)?date("Y-m-d",strtotime($request->startDate)):date("Y")."-04-01";
		$EndDate 		= isset($request->endDate) && !empty($request->endDate)?date("Y-m-d",strtotime($request->endDate)):date("Y-m-d");
		if (isset($request->account) && $request->account) {
			$SELECT_SQL = 	self::select("$self.id as invoice_id",
										"$self.challan_no",
										"$self.dispatch_id",
										"$self.invoice_status as invoice_status_id",
										"$self.client_master_id",
										"DISPATCH.collection_cycle_term",
										"PARA.para_value as cycle_term",
										"CLIENT.client_name",
										"$self.invoice_date",
										"$self.invoice_status",
										"DEPT.department_name",
										\DB::raw("CASE WHEN 1=1 THEN (SELECT wm_sales_master.net_amount FROM wm_sales_master WHERE wm_sales_master.dispatch_id = DISPATCH.id GROUP BY wm_sales_master.dispatch_id) END AS TOTAL_INVOICE_AMT"),
										\DB::raw("CASE WHEN 1=1 THEN (SELECT sum(wm_payment_receive.received_amount) FROM wm_payment_receive WHERE wm_payment_receive.invoice_id = $self.id GROUP BY wm_payment_receive.invoice_id) END AS TOTAL_RECEIVED_AMT"),
										\DB::raw("IF (CLIENT.days > 0,DATE_ADD($self.invoice_date,INTERVAL CAST(PARA.para_value AS INTEGER) DAY),'') AS DUE_DATE"),
										\DB::raw("IF (CLIENT.days > 0,DATEDIFF(DATE_ADD($self.invoice_date,INTERVAL CAST(PARA.para_value AS INTEGER) DAY),$self.invoice_date),0) AS DUE_DAYS"),
										\DB::raw("CASE WHEN 1=1 THEN
												(
													SELECT SUM(wm_invoices_credit_debit_notes_details.revised_net_amount)
													FROM wm_invoices_credit_debit_notes_details
													INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
													INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
													WHERE wm_dispatch.approval_status = 1
													AND wm_invoices_credit_debit_notes.notes_type = 0
													AND wm_invoices_credit_debit_notes.status = 3
													AND wm_invoices_credit_debit_notes.invoice_id = $self.id
												) END AS CN_Amount"),
										\DB::raw("CASE WHEN 1=1 THEN
												(
													SELECT SUM(wm_invoices_credit_debit_notes_details.revised_net_amount)
													FROM wm_invoices_credit_debit_notes_details
													INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
													INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
													WHERE wm_dispatch.approval_status = 1
													AND wm_invoices_credit_debit_notes.notes_type = 1
													AND wm_invoices_credit_debit_notes.status = 3
													AND wm_invoices_credit_debit_notes.invoice_id = $self.id
												) END AS DN_Amount"),
										\DB::raw("(	CASE 	WHEN $self.invoice_status = 0 THEN 'UP'
															WHEN $self.invoice_status = 1 THEN 'C'
															WHEN $self.invoice_status = 2 THEN 'P'
															WHEN $self.invoice_status = 3 THEN 'P'
													END ) AS invoice_status_name"),
										\DB::raw("(	CASE 	WHEN $self.invoice_status = 0 THEN 'UnPaid'
															WHEN $self.invoice_status = 1 THEN 'Cancelled'
															WHEN $self.invoice_status = 2 THEN 'Paid'
															WHEN $self.invoice_status = 3 THEN 'Partial'
													END ) AS status_full_name"),
										\DB::raw("(	CASE 	WHEN $self.invoice_status = 0 THEN 'blue'
															WHEN $self.invoice_status = 1 THEN 'red'
															WHEN $self.invoice_status = 2 THEN 'green'
															WHEN $self.invoice_status = 3 THEN 'orange'
													END ) AS status_color_code"));
			$SELECT_SQL->join($DispatchTbl->getTable()." AS DISPATCH","DISPATCH.id","=","$self.dispatch_id");
			$SELECT_SQL->join($DeptTbl->getTable()." AS DEPT","DISPATCH.bill_from_mrf_id","=","DEPT.id");
			$SELECT_SQL->leftjoin($clientTbl->getTable()." AS CLIENT","$self.client_master_id","=","CLIENT.id");
			$SELECT_SQL->leftjoin($Parameter->getTable()." AS PARA","CLIENT.days","=","PARA.para_id");
		} else {
			$SELECT_SQL = 	self::select("$self.id as invoice_id",
										"$self.challan_no",
										"$self.dispatch_id",
										"$self.invoice_status as invoice_status_id",
										"$self.client_master_id",
										"DISPATCH.collection_cycle_term",
										"PARA.para_value as cycle_term",
										"CLIENT.client_name",
										"$self.invoice_date",
										"$self.invoice_status",
										"DEPT.department_name",
										\DB::raw("CASE WHEN 1=1 THEN (SELECT wm_sales_master.net_amount FROM wm_sales_master WHERE wm_sales_master.dispatch_id = DISPATCH.id GROUP BY wm_sales_master.dispatch_id) END AS TOTAL_INVOICE_AMT"),
										\DB::raw("CASE WHEN 1=1 THEN (SELECT sum(wm_payment_receive.received_amount) FROM wm_payment_receive WHERE wm_payment_receive.invoice_id = $self.id GROUP BY wm_payment_receive.invoice_id) END AS TOTAL_RECEIVED_AMT"),
										\DB::raw("IF (DISPATCH.collection_cycle_term > 0,DATE_ADD($self.invoice_date,INTERVAL CAST(PARA.para_value AS INTEGER) DAY),'') AS DUE_DATE"),
										\DB::raw("IF (DISPATCH.collection_cycle_term > 0,DATEDIFF(DATE_ADD($self.invoice_date,INTERVAL CAST(PARA.para_value AS INTEGER) DAY),$self.invoice_date),0) AS DUE_DAYS"),
										\DB::raw("CASE WHEN 1=1 THEN
												(
													SELECT SUM(wm_invoices_credit_debit_notes_details.revised_net_amount)
													FROM wm_invoices_credit_debit_notes_details
													INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
													INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
													WHERE wm_dispatch.approval_status = 1
													AND wm_invoices_credit_debit_notes.notes_type = 0
													AND wm_invoices_credit_debit_notes.status = 3
													AND wm_invoices_credit_debit_notes.invoice_id = $self.id
												) END AS CN_Amount"),
										\DB::raw("CASE WHEN 1=1 THEN
												(
													SELECT SUM(wm_invoices_credit_debit_notes_details.revised_net_amount)
													FROM wm_invoices_credit_debit_notes_details
													INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
													INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
													WHERE wm_dispatch.approval_status = 1
													AND wm_invoices_credit_debit_notes.notes_type = 1
													AND wm_invoices_credit_debit_notes.status = 3
													AND wm_invoices_credit_debit_notes.invoice_id = $self.id
												) END AS DN_Amount"),
										\DB::raw("(	CASE 	WHEN $self.invoice_status = 0 THEN 'UP'
															WHEN $self.invoice_status = 1 THEN 'C'
															WHEN $self.invoice_status = 2 THEN 'P'
															WHEN $self.invoice_status = 3 THEN 'P'
										END ) AS invoice_status_name"),
										\DB::raw("(	CASE 	WHEN $self.invoice_status = 0 THEN 'UnPaid'
															WHEN $self.invoice_status = 1 THEN 'Cancelled'
															WHEN $self.invoice_status = 2 THEN 'Paid'
															WHEN $self.invoice_status = 3 THEN 'Partial'
													END ) AS status_full_name"),
										\DB::raw("(	CASE 	WHEN $self.invoice_status = 0 THEN 'blue'
															WHEN $self.invoice_status = 1 THEN 'red'
															WHEN $self.invoice_status = 2 THEN 'green'
															WHEN $self.invoice_status = 3 THEN 'orange'
													END ) AS status_color_code"));
			$SELECT_SQL->join($DispatchTbl->getTable()." AS DISPATCH","DISPATCH.id","=","$self.dispatch_id");
			$SELECT_SQL->join($DeptTbl->getTable()." AS DEPT","DISPATCH.bill_from_mrf_id","=","DEPT.id");
			$SELECT_SQL->leftjoin($clientTbl->getTable()." AS CLIENT","$self.client_master_id","=","CLIENT.id");
			$SELECT_SQL->leftjoin($Parameter->getTable()." AS PARA","DISPATCH.collection_cycle_term","=","PARA.para_id");
		}
		$SELECT_SQL->whereIn("DEPT.base_location_id",$assignBaseId);
		if (isset($request->challan_no) && !empty($request->challan_no)) {
			$SELECT_SQL->where("$self.challan_no",$request->challan_no);
		}
		if(!empty($mrf_id)){
			$SELECT_SQL->whereIn("DISPATCH.bill_from_mrf_id",$mrf_id);
		}
		if (isset($request->paid)) {
			if ($request->paid == 1) { //paid
				$SELECT_SQL->where("$self.invoice_status",2);
			} else if ($request->paid == 0) { //unpaid
				$SELECT_SQL->where("$self.invoice_status",0);
			} else if ($request->paid == 3) { //partial
				$SELECT_SQL->where("$self.invoice_status",3);
			}
		} else {
			$SELECT_SQL->where("$self.invoice_status","!=","1");
		}
		if (isset($request->client_id) && !empty($request->client_id)) {
			$SELECT_SQL->where("$self.client_master_id",$request->client_id);
		}
		if (isset($request->collection_cycle_term) && !empty($request->collection_cycle_term)) {
			if (isset($request->account) && $request->account) {
				$SELECT_SQL->where("CLIENT.days",$request->collection_cycle_term);
			} else {
				$SELECT_SQL->where("DISPATCH.collection_cycle_term",$request->collection_cycle_term);
			}
		}
		if(!empty($FromDate) && !empty($EndDate)) {
			$SELECT_SQL->whereBetween("$self.invoice_date",array($FromDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		} elseif(!empty($FromDate)) {
			$SELECT_SQL->whereBetween("$self.invoice_date",array($FromDate." ".GLOBAL_START_TIME,$FromDate." ".GLOBAL_END_TIME));
		} elseif(!empty($EndDate)) {
			$SELECT_SQL->whereBetween("$self.invoice_date",array($EndDate." ".GLOBAL_START_TIME,$EndDate." ".GLOBAL_END_TIME));
		}
		if (isset($request->due_in_days) && !empty($request->due_in_days)) {
			$SELECT_SQL->having("DUE_DAYS",intval($request->due_in_days));
		}
		$SELECT_ROWS = $SELECT_SQL->orderBy("$self.id","DESC")->get()->toArray();
		$GRAND_TOTAL_INVOICE_AMOUNT 	= 0;
		$GRAND_TOTAL_RECEIVED_AMOUNT 	= 0;
		$GRAND_TOTAL_PENDING_AMOUNT 	= 0;
		$GRAND_TOTAL_CN_AMOUNT 			= 0;
		$GRAND_TOTAL_DN_AMOUNT 			= 0;
		if(!empty($SELECT_ROWS))
		{
			foreach($SELECT_ROWS as $key => $SELECT_ROW)
			{
				$total_invoice_amount 	= $SELECT_ROW['TOTAL_INVOICE_AMT'];
				$total_paid_amount 	 	= $SELECT_ROW['TOTAL_RECEIVED_AMT'];
				$total_cn_amount 	 	= $SELECT_ROW['CN_Amount'];
				$total_dn_amount 	 	= $SELECT_ROW['DN_Amount'];
				if ($SELECT_ROW["invoice_status"] == 2 && empty($total_paid_amount)) {
					$total_paid_amount = $total_invoice_amount;
				}
				$remaining_amount 							= ((($total_invoice_amount - $total_cn_amount) - $total_paid_amount) + $total_dn_amount);
				$SELECT_ROWS[$key]["total_invoice_amount"] 	= !empty($total_invoice_amount)?_FormatNumberV2($total_invoice_amount):number_format(_FormatNumberV2(0),2);
				$SELECT_ROWS[$key]["total_paid_amount"] 	= !empty($total_paid_amount)?_FormatNumberV2($total_paid_amount):number_format(_FormatNumberV2(0),2);
				$SELECT_ROWS[$key]["total_cn_amount"] 		= !empty($total_cn_amount)?_FormatNumberV2($total_cn_amount):number_format(_FormatNumberV2(0),2);
				$SELECT_ROWS[$key]["total_dn_amount"] 		= !empty($total_dn_amount)?_FormatNumberV2($total_dn_amount):number_format(_FormatNumberV2(0),2);
				$SELECT_ROWS[$key]["remaing_amount"] 		= !empty($remaining_amount) && ($remaining_amount > 0)?_FormatNumberV2($remaining_amount):number_format(_FormatNumberV2(0),2);
				$SELECT_ROWS[$key]["due_date_val"]			= $SELECT_ROW['DUE_DATE'];
				if ($SELECT_ROW['DUE_DAYS'] > 0 && $SELECT_ROW["invoice_status"] != "2" && !empty($SELECT_ROW['DUE_DATE'])) {
					$SELECT_ROWS[$key]["due_date"] = "<font style='color:green;font-weight:bold'>".date("Y-m-d",strtotime($SELECT_ROW['DUE_DATE']))."</font>";
				} else if ($SELECT_ROW['DUE_DAYS'] <= 0 && $SELECT_ROW["invoice_status"] != "2" && !empty($SELECT_ROW['DUE_DATE'])) {
					$SELECT_ROWS[$key]["due_date"] = "<font style='color:red;font-weight:bold'>".date("Y-m-d",strtotime($SELECT_ROW['DUE_DATE']))."</font>";
				} else {
					$SELECT_ROWS[$key]["due_date"] = "-";
				}
				$GRAND_TOTAL_INVOICE_AMOUNT 	+= (!empty($total_invoice_amount)?$total_invoice_amount:0);
				$GRAND_TOTAL_RECEIVED_AMOUNT 	+= (!empty($total_paid_amount)?$total_paid_amount:0);
				$GRAND_TOTAL_PENDING_AMOUNT 	+= (!empty($remaining_amount) && ($remaining_amount > 0)?$remaining_amount:0);
				$GRAND_TOTAL_CN_AMOUNT 			+= (!empty($total_cn_amount)?$total_cn_amount:0);
				$GRAND_TOTAL_DN_AMOUNT 			+= (!empty($total_dn_amount)?$total_dn_amount:0);
			}
		}
		$arrResult['ResultRow'] 					= $SELECT_ROWS;
		$arrResult['GRAND_TOTAL_INVOICE_AMOUNT'] 	= !empty($GRAND_TOTAL_INVOICE_AMOUNT)?_FormatNumberV2($GRAND_TOTAL_INVOICE_AMOUNT):number_format(_FormatNumberV2(0),2);
		$arrResult['GRAND_TOTAL_RECEIVED_AMOUNT']	= !empty($GRAND_TOTAL_RECEIVED_AMOUNT)?_FormatNumberV2($GRAND_TOTAL_RECEIVED_AMOUNT):number_format(_FormatNumberV2(0),2);
		$arrResult['GRAND_TOTAL_PENDING_AMOUNT']	= !empty($GRAND_TOTAL_PENDING_AMOUNT)?_FormatNumberV2($GRAND_TOTAL_PENDING_AMOUNT):number_format(_FormatNumberV2(0),2);
		$arrResult['GRAND_TOTAL_CN_AMOUNT']			= !empty($GRAND_TOTAL_CN_AMOUNT)?_FormatNumberV2($GRAND_TOTAL_CN_AMOUNT):number_format(_FormatNumberV2(0),2);
		$arrResult['GRAND_TOTAL_DN_AMOUNT']			= !empty($GRAND_TOTAL_DN_AMOUNT)?_FormatNumberV2($GRAND_TOTAL_DN_AMOUNT):number_format(_FormatNumberV2(0),2);
		return $arrResult;
	}

	/*
	Use 	: analysisreportByMRF
	Author 	: Axay Shah
	Date 	: 06 September,2021
	*/
	public static  function analysisreportByMRF($request)
	{
		$exclude_cat	= array("RDF");
		$type_of_sales 	= (isset($request->type_of_sales) && !empty($request->type_of_sales)) ? $request->type_of_sales : 0;
		$start_date 	= (isset($request->start_date) && !empty($request->start_date)) ? $request->start_date : "";
		$end_date 		= (isset($request->end_date) && !empty($request->end_date)) ? $request->end_date : "";
		$userID 		= Auth()->user()->adminuserid;
		$AssignBaseLoc 	= UserBaseLocationMapping::where("adminuserid",$userID)->pluck("base_location_id")->toArray();
		$arrResult 		= array();
		$arrProductCat	= array();
		$arrMRF			= array();
		$arrTimePeriod	= array();
		if (!empty($end_date)) {
			$PriviousYear 	= date("Y",strtotime($end_date)) - 1;
			$YEAR 			= date("Y",strtotime($end_date));
		} else {
			$PriviousYear 	= date("Y") - 1;
			$YEAR 			= date("Y");
		}
		$F_YEAR_START 	= $PriviousYear."-04-01 00:00:00";
		$F_YEAR_END 	= $YEAR."-03-01 23:59:59";
		$WhereCond 		= "";
		if (!empty($type_of_sales)) {
			switch ($type_of_sales) {
				case 2: {
					$WhereCond = " AND wm_dispatch.bill_from_mrf_id = wm_dispatch.master_dept_id";
					break;
				}
				case 3: {
					$WhereCond = " AND wm_dispatch.bill_from_mrf_id != wm_dispatch.master_dept_id";
					break;
				}
			}
		}
		$MRF_IDS = array();
		if(!empty($AssignBaseLoc)) {
			$MRF_IDS = WmDepartment::whereIn("base_location_id",$AssignBaseLoc)->where("is_virtual",0)->where("status",1)->pluck("id")->toArray();
		}
		$MRF_ID 	= (!empty($MRF_IDS))?implode(",",$MRF_IDS):"";
		$WhereCond 	.= " AND (wm_dispatch.master_dept_id IN ($MRF_ID) OR wm_dispatch.bill_from_mrf_id IN ($MRF_ID))";
		if (!empty($exclude_cat)) {
			$WhereCond .= " AND wm_product_master.product_category NOT IN ("."'".implode("','", $exclude_cat)."'".")";
		}
		$SELECT_SQL 	= "	SELECT CONCAT(MONTHNAME(wm_dispatch.dispatch_date),'-',YEAR(wm_dispatch.dispatch_date)) AS DispatchMonth,
							YEAR(wm_dispatch.dispatch_date) AS Dispatch_Year,
							MONTH(wm_dispatch.dispatch_date) AS Dispatch_Month,
							IF (Bill_From_MRF.department_name IS NULL, From_MRF.department_name, Bill_From_MRF.department_name) AS MRF,
							wm_product_master.product_category AS P_C, SUM(wm_dispatch_product.quantity) AS QTY, 
							ROUND((SUM(wm_dispatch_product.gross_amount) / SUM(wm_dispatch_product.quantity)),2) AS AVG_PRICE
							FROM wm_dispatch_product
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
							LEFT JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
							LEFT JOIN wm_department as Bill_From_MRF ON Bill_From_MRF.id = wm_dispatch.bill_from_mrf_id
							LEFT JOIN wm_department as From_MRF ON From_MRF.id = wm_dispatch.master_dept_id
							WHERE wm_dispatch.dispatch_date BETWEEN '$F_YEAR_START' AND '$F_YEAR_END'
							AND wm_dispatch.approval_status = 1
							AND (wm_product_master.product_category != '' OR wm_product_master.product_category IS NOT NULL)
							$WhereCond
							GROUP BY MRF,Dispatch_Year,Dispatch_Month,P_C
							ORDER BY Dispatch_Year ASC, Dispatch_Month ASC ,MRF ASC,P_C ASC";
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		foreach ($SELECT_RES as $SELECT_ROW) {
			if (!isset($arrResult[$SELECT_ROW->MRF])) {
				$arrResult[$SELECT_ROW->MRF] = array();
			}
			if (!isset($arrResult[$SELECT_ROW->MRF][$SELECT_ROW->P_C])) {
				$arrResult[$SELECT_ROW->MRF][$SELECT_ROW->P_C] = array();
			}
			$arrResult[$SELECT_ROW->MRF][$SELECT_ROW->P_C][$SELECT_ROW->DispatchMonth] 	= array("Month"=>$SELECT_ROW->DispatchMonth,
																								"QTY"=>intval($SELECT_ROW->QTY),
																								"Avg_Price"=>$SELECT_ROW->AVG_PRICE);
			if (!in_array($SELECT_ROW->P_C,$arrProductCat)) array_push($arrProductCat,$SELECT_ROW->P_C);
			if (!in_array($SELECT_ROW->MRF,$arrMRF)) array_push($arrMRF,$SELECT_ROW->MRF);
			if (!in_array($SELECT_ROW->DispatchMonth,$arrTimePeriod)) array_push($arrTimePeriod,$SELECT_ROW->DispatchMonth);
		}
		$SELECT_SQL 	= "	SELECT 
							CONCAT(MONTHNAME(wm_dispatch.dispatch_date),'-',YEAR(wm_dispatch.dispatch_date)) AS DispatchMonth,
							wm_product_master.product_category AS P_C, SUM(wm_dispatch_product.quantity) AS QTY, 
							ROUND((SUM(wm_dispatch_product.gross_amount) / SUM(wm_dispatch_product.quantity)),2) AS AVG_PRICE
							FROM wm_dispatch_product
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
							LEFT JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
							WHERE wm_dispatch.dispatch_date BETWEEN '$F_YEAR_START' AND '$F_YEAR_END'
							AND wm_dispatch.approval_status = 1
							AND (wm_product_master.product_category != '' OR wm_product_master.product_category IS NOT NULL)
							$WhereCond
							GROUP BY DispatchMonth,P_C
							ORDER BY DispatchMonth,P_C";
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		foreach ($SELECT_RES as $SELECT_ROW) {
			if (!isset($arrResult['ALL'][$SELECT_ROW->P_C])) {
				$arrResult['ALL'][$SELECT_ROW->P_C] = array();
			}
			$arrResult['ALL'][$SELECT_ROW->P_C][$SELECT_ROW->DispatchMonth] 	= array("Month"=>$SELECT_ROW->DispatchMonth,
																						"QTY"=>intval($SELECT_ROW->QTY),
																						"Avg_Price"=>$SELECT_ROW->AVG_PRICE);
		}
		array_push($arrMRF,'ALL');
		$arrResult2 = array();
		$counter 	= 0;
		if (!empty($arrResult)) {
			foreach($arrResult as $Mrf_Name => $ResultRow) {
				$arrResult2[$counter]['lable'][] = $Mrf_Name;
				foreach($arrTimePeriod as $Month_Name) {
					array_push($arrResult2[$counter]['lable'],$Month_Name);
				}
				$arrResult2[$counter]['product'] 	= array();
				$GrandTotal							= array();
				foreach($ResultRow as $Product_Name=>$Product_Sales_Data) {
					$SalesData 	= array();
					foreach($arrTimePeriod as $Month_Name) {
						$QTY 		= 0;
						$Avg_Price	= 0;
						if (isset($Product_Sales_Data[$Month_Name])) {
							array_push($SalesData,$Product_Sales_Data[$Month_Name]);
							$QTY 		= $Product_Sales_Data[$Month_Name]['QTY'];
							$Avg_Price	= round(($Product_Sales_Data[$Month_Name]['QTY'] * $Product_Sales_Data[$Month_Name]['Avg_Price']),2);
						} else {
							$TempArray 	= array("Month"=>$Month_Name,"QTY"=>"","Avg_Price"=>"");
							array_push($SalesData,$TempArray);
						}
						if (isset($GrandTotal[$Month_Name])) {
							$GrandTotal[$Month_Name]['QTY'] 		+= $QTY;
							$GrandTotal[$Month_Name]['Avg_Price'] 	+= $Avg_Price;
						} else {
							$GrandTotal[$Month_Name]['QTY'] 		= $QTY;
							$GrandTotal[$Month_Name]['Avg_Price'] 	= $Avg_Price;
						}
					}
					$arrResult2[$counter]['product'][] = array('name'=> $Product_Name,'sales_data'=>$SalesData);
				}
				$SalesData 	= array();
				foreach($arrTimePeriod as $Month_Name) {
					if ($GrandTotal[$Month_Name]['Avg_Price'] > 0 && $GrandTotal[$Month_Name]['QTY'] > 0) {
						$Avg_Price 	= round(($GrandTotal[$Month_Name]['Avg_Price']/$GrandTotal[$Month_Name]['QTY']),2);
						$QTY 		= $GrandTotal[$Month_Name]['QTY'];
					} else {
						$Avg_Price 	= "";
						$QTY 		= "";
					}
					$TempArray 	= array("Month"=>$Month_Name,"QTY"=>$QTY,"Avg_Price"=>$Avg_Price);
					array_push($SalesData,$TempArray);
				}
				$arrResult2[$counter]['product'][] = array('name'=> 'Grand Total','sales_data'=>$SalesData);
				$counter++;
			}
			$data = [	"Page_Title"=>"Avg Composition vs Sales Price (By Base Location)",
						"arrResult"=>$arrResult2,
						"arrMRF"=>$arrMRF,
						"colspan"=>((count($arrTimePeriod)+1) * 2),
						"arrProductCat"=>$arrProductCat,
						"arrTimePeriod"=>$arrTimePeriod,
						"WhereCond"=>$WhereCond,
						"type_of_sales"=>$type_of_sales];
		} else {
			$data = [	"Page_Title"=>"Avg Composition vs Sales Price (By Base Location)",
						"arrResult"=>$arrResult2,
						"arrMRF"=>$arrMRF,
						"colspan"=>((count($arrTimePeriod)+1) * 2),
						"arrProductCat"=>$arrProductCat,
						"arrTimePeriod"=>$arrTimePeriod,
						"WhereCond"=>$WhereCond,
						"type_of_sales"=>$type_of_sales];
		} 
		return $data;
	}
	
	/*
	Use 	: analysisreportByBaseLocation
	Author 	: Axay Shah
	Date 	: 06 September,2021
	*/
	public static function analysisreportByBaseLocation($request)
	{
		$type_of_sales 	= (isset($request->type_of_sales) && !empty($request->type_of_sales)) ? $request->type_of_sales : 0;
		$exclude_cat	= array("RDF");
		$start_date 	= (isset($request->start_date) && !empty($request->start_date)) ? $request->start_date : "";
		$end_date 		= (isset($request->end_date) && !empty($request->end_date)) ? $request->end_date : "";
		$userID 		= Auth()->user()->adminuserid;
		$AssignBaseLoc 	= UserBaseLocationMapping::where("adminuserid",$userID)->pluck("base_location_id")->toArray();
		$arrResult 		= array();
		$arrProductCat	= array();
		$arrMRF			= array();
		$arrTimePeriod	= array();
		if (!empty($end_date)) {
			$PriviousYear 	= date("Y",strtotime($end_date)) - 1;
			$YEAR 			= date("Y",strtotime($end_date));
		} else {
			$PriviousYear 	= date("Y") - 1;
			$YEAR 			= date("Y");
		}
		$F_YEAR_START 	= $PriviousYear."-04-01 00:00:00";
		$F_YEAR_END 	= $YEAR."-03-01 23:59:59";
		$WhereCond 		= "";
		if (!empty($type_of_sales)) {
			switch ($type_of_sales) {
				case 2: {
					$WhereCond = " AND wm_dispatch.bill_from_mrf_id = wm_dispatch.master_dept_id";
					break;
				}
				case 3: {
					$WhereCond = " AND wm_dispatch.bill_from_mrf_id != wm_dispatch.master_dept_id";
					break;
				}
			}
		}
		$MRF_IDS = array();
		if(!empty($AssignBaseLoc)){
			$MRF_IDS = WmDepartment::whereIn("base_location_id",$AssignBaseLoc)->where("is_virtual",0)->where("status",1)->pluck("id")->toArray();
		}
		$MRF_ID 		= (!empty($MRF_IDS)) ?  implode(",",$MRF_IDS) :   "";
		$WhereCond 		.= " AND (wm_dispatch.master_dept_id IN ($MRF_ID) OR wm_dispatch.bill_from_mrf_id IN ($MRF_ID))";
		if (!empty($exclude_cat)) {
			$WhereCond .= " AND wm_product_master.product_category NOT IN ("."'".implode("','", $exclude_cat)."'".")";
		}
		$SELECT_SQL 	= "	SELECT CONCAT(MONTHNAME(wm_dispatch.dispatch_date),'-',YEAR(wm_dispatch.dispatch_date)) AS DispatchMonth,
							YEAR(wm_dispatch.dispatch_date) AS Dispatch_Year,
							MONTH(wm_dispatch.dispatch_date) AS Dispatch_Month,
							IF (Bill_From_BL.base_location_name IS NULL, From_MRF_BL.base_location_name, Bill_From_BL.base_location_name) AS MRF,
							wm_product_master.product_category AS P_C, SUM(wm_dispatch_product.quantity) AS QTY, 
							ROUND((SUM(wm_dispatch_product.gross_amount) / SUM(wm_dispatch_product.quantity)),2) AS AVG_PRICE
							FROM wm_dispatch_product
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
							LEFT JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
							LEFT JOIN wm_department as Bill_From_MRF ON Bill_From_MRF.id = wm_dispatch.bill_from_mrf_id
							LEFT JOIN wm_department as From_MRF ON From_MRF.id = wm_dispatch.master_dept_id
							LEFT JOIN base_location_master as Bill_From_BL ON Bill_From_MRF.base_location_id = Bill_From_BL.id
							LEFT JOIN base_location_master as From_MRF_BL ON From_MRF.base_location_id = From_MRF_BL.id
							WHERE wm_dispatch.dispatch_date BETWEEN '$F_YEAR_START' AND '$F_YEAR_END'
							AND wm_dispatch.approval_status = 1
							AND (wm_product_master.product_category != '' OR wm_product_master.product_category IS NOT NULL)
							$WhereCond
							GROUP BY MRF,Dispatch_Year,Dispatch_Month,P_C
							ORDER BY Dispatch_Year ASC, Dispatch_Month ASC ,MRF ASC,P_C ASC";
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		foreach ($SELECT_RES as $SELECT_ROW) {
			if (!isset($arrResult[$SELECT_ROW->MRF])) {
				$arrResult[$SELECT_ROW->MRF] = array();
			}
			if (!isset($arrResult[$SELECT_ROW->MRF][$SELECT_ROW->P_C])) {
				$arrResult[$SELECT_ROW->MRF][$SELECT_ROW->P_C] = array();
			}
			$arrResult[$SELECT_ROW->MRF][$SELECT_ROW->P_C][$SELECT_ROW->DispatchMonth] 	= array("Month"=>$SELECT_ROW->DispatchMonth,
																								"QTY"=>intval($SELECT_ROW->QTY),
																								"Avg_Price"=>$SELECT_ROW->AVG_PRICE);
			if (!in_array($SELECT_ROW->P_C,$arrProductCat)) array_push($arrProductCat,$SELECT_ROW->P_C);
			if (!in_array($SELECT_ROW->MRF,$arrMRF)) array_push($arrMRF,$SELECT_ROW->MRF);
			if (!in_array($SELECT_ROW->DispatchMonth,$arrTimePeriod)) array_push($arrTimePeriod,$SELECT_ROW->DispatchMonth);
		}
		$SELECT_SQL 	= "	SELECT 
							CONCAT(MONTHNAME(wm_dispatch.dispatch_date),'-',YEAR(wm_dispatch.dispatch_date)) AS DispatchMonth,
							wm_product_master.product_category AS P_C, SUM(wm_dispatch_product.quantity) AS QTY, 
							ROUND((SUM(wm_dispatch_product.gross_amount) / SUM(wm_dispatch_product.quantity)),2) AS AVG_PRICE
							FROM wm_dispatch_product
							LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
							LEFT JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
							WHERE wm_dispatch.dispatch_date BETWEEN '$F_YEAR_START' AND '$F_YEAR_END'
							AND wm_dispatch.approval_status = 1
							AND (wm_product_master.product_category != '' OR wm_product_master.product_category IS NOT NULL)
							$WhereCond
							GROUP BY DispatchMonth,P_C
							ORDER BY DispatchMonth,P_C";
		$SELECT_RES = DB::connection('master_database')->select($SELECT_SQL);
		foreach ($SELECT_RES as $SELECT_ROW) {
			if (!isset($arrResult['ALL'][$SELECT_ROW->P_C])) {
				$arrResult['ALL'][$SELECT_ROW->P_C] = array();
			}
			$arrResult['ALL'][$SELECT_ROW->P_C][$SELECT_ROW->DispatchMonth] 	= array("Month"=>$SELECT_ROW->DispatchMonth,
																						"QTY"=>intval($SELECT_ROW->QTY),
																						"Avg_Price"=>$SELECT_ROW->AVG_PRICE);
		}
		array_push($arrMRF,'ALL');
		$arrResult2 = array();
		$counter 	= 0;
		if (!empty($arrResult)) {
			foreach($arrResult as $Mrf_Name => $ResultRow) {
				$arrResult2[$counter]['lable'][] = $Mrf_Name;
				foreach($arrTimePeriod as $Month_Name) {
					array_push($arrResult2[$counter]['lable'],$Month_Name);
				}
				$arrResult2[$counter]['product'] 	= array();
				$GrandTotal							= array();
				foreach($ResultRow as $Product_Name=>$Product_Sales_Data) {
					$SalesData 	= array();
					foreach($arrTimePeriod as $Month_Name) {
						$QTY 		= 0;
						$Avg_Price	= 0;
						if (isset($Product_Sales_Data[$Month_Name])) {
							array_push($SalesData,$Product_Sales_Data[$Month_Name]);
							$QTY 		= $Product_Sales_Data[$Month_Name]['QTY'];
							$Avg_Price	= round(($Product_Sales_Data[$Month_Name]['QTY'] * $Product_Sales_Data[$Month_Name]['Avg_Price']),2);
						} else {
							$TempArray 	= array("Month"=>$Month_Name,"QTY"=>"","Avg_Price"=>"");
							array_push($SalesData,$TempArray);
						}
						if (isset($GrandTotal[$Month_Name])) {
							$GrandTotal[$Month_Name]['QTY'] 		+= $QTY;
							$GrandTotal[$Month_Name]['Avg_Price'] 	+= $Avg_Price;
						} else {
							$GrandTotal[$Month_Name]['QTY'] 		= $QTY;
							$GrandTotal[$Month_Name]['Avg_Price'] 	= $Avg_Price;
						}
					}
					$arrResult2[$counter]['product'][] = array('name'=> $Product_Name,'sales_data'=>$SalesData);
				}
				$SalesData 	= array();
				foreach($arrTimePeriod as $Month_Name) {
					if ($GrandTotal[$Month_Name]['Avg_Price'] > 0 && $GrandTotal[$Month_Name]['QTY'] > 0) {
						$Avg_Price 	= round(($GrandTotal[$Month_Name]['Avg_Price']/$GrandTotal[$Month_Name]['QTY']),2);
						$QTY 		= $GrandTotal[$Month_Name]['QTY'];
					} else {
						$Avg_Price 	= "";
						$QTY 		= "";
					}
					$TempArray 	= array("Month"=>$Month_Name,"QTY"=>$QTY,"Avg_Price"=>$Avg_Price);
					array_push($SalesData,$TempArray);
				}
				$arrResult2[$counter]['product'][] = array('name'=> 'Grand Total','sales_data'=>$SalesData);
				$counter++;
			}
			$data = [	"Page_Title"=>"Avg Composition vs Sales Price (By Base Location)",
						"arrResult"=>$arrResult2,
						"arrMRF"=>$arrMRF,
						"colspan"=>((count($arrTimePeriod)+1) * 2),
						"arrProductCat"=>$arrProductCat,
						"arrTimePeriod"=>$arrTimePeriod,
						"type_of_sales"=>$type_of_sales];
		} else {
			$data = [	"Page_Title"=>"Avg Composition vs Sales Price (By Base Location)",
						"arrResult"=>$arrResult2,
						"arrMRF"=>$arrMRF,
						"colspan"=>((count($arrTimePeriod)+1) * 2),
						"arrProductCat"=>$arrProductCat,
						"arrTimePeriod"=>$arrTimePeriod,
						"type_of_sales"=>$type_of_sales];
		}
		return $data;
	}


	/*
	Use 	: Get INvoice Details as per darshak requirement
	Author 	: Axay Shah
	Date 	: 08 July,2019
	*/
	public static function GetByIdToReplaceProduct($InvoiceId,$fromCnDn=0)
	{
		$ProductMaster 	=  	new WmProductMaster();
		$SalesMaster 	=  	new WmSalesMaster();
		$ClientMaster 	=  	new WmClientMaster();
		$DispatchMaster	=  	new WmDispatch();
		$DispatchProduct=  	new WmDispatchProduct();
		$CompanyMaster	=   new CompanyMaster();
		$LocationMaster	=   new LocationMaster();
		$VehicleMaster	=   new VehicleMaster();
		$GSTTABLE		=   new GSTStateCodes();
		$Self			=  	(new static)->getTable();
		$Client			=  	$ClientMaster->getTable();
		$Sales 			=  	$SalesMaster->getTable();
		$Product 		=  	$ProductMaster->getTable();
		$Dispatch		=  	$DispatchMaster->getTable();
		$DispatchPro    =  	$DispatchProduct->getTable();
		$Company 		=   $CompanyMaster->getTable();
		$Location 		=   $LocationMaster->getTable();
		$Vehicle 		=   $VehicleMaster->getTable();
		$GST 			=   $GSTTABLE->getTable();
		$result 		=  	array();
		$data 			= 	self::select("$Self.*","$Self.invoice_date as created_at",
							\DB::raw("IF($Self.invoice_dispatch_type = ".RECYCLEBLE_TYPE.",'Tax Invoice','Bill of Supply') as invoice_title"),
							\DB::raw("IF($Self.invoice_dispatch_type = ".RECYCLEBLE_TYPE.",1,1) as gst_on_flag"),
							"C.client_name",
							"C.contact_person",
							"C.address",
							"C.shipping_address as client_shipping_address",
							"C.city_id",
							"C.company_id",
							"C.pincode",
							"C.latitude",
							"C.longitude",
							"C.contact_no",
							"C.mobile_no",
							"C.VAT",
							"C.CST",
							"C.email",
							"C.pan_no",
							"C.gstin_no",
							"C.service_no",
							"C.gst_state_code",
							"C.taxes",
							"C.tcs_tax_allow",
							"C.payment_mode",
							"C.days",
							"C.freight_born",
							"C.rates",
							"C.remarks",
							"C.rejection_term",
							"C.introduced_by",
							"C.product_consumed",
							"C.material_consumed",
							"C.material_alias",
							"$Self.master_dept_id",
							"C.status",
							"C.created_by",
							"C.updated_by",
							"C.updated_at",
							"GST.state_name as client_state_name",
							"GST.display_state_code as gst_state_code",
							"LOC_CL.city as client_city_name",
							\DB::raw("now() as POS"))->where("$Self.id",$InvoiceId)
							->join("$Client as C","$Self.client_master_id","=","C.id")
							->leftjoin("$Location as LOC_CL","C.city_id","=","LOC_CL.location_id")
							->leftjoin("$GST as GST","C.gst_state_code","=","GST.state_code")
							->first();
		if($data) {
			$data['encypt_id'] 		=  passencrypt($data->id);
			$data['invoice_url'] 	=  url("/")."/".INVOICE_URL."/".passencrypt($data->id);
			$InvoiceTitleFlag 		=  false;
			$data['invoice_title'] 	= $data->invoice_title;
			if(!empty($data->sales_id)) {
				$salesID 	= 	explode(",",$data->sales_id);
				$SalesData 	= 	WmSalesMaster::select("$Sales.*","P.*",
								\DB::raw("(
									CASE 
										WHEN P.id = 112 THEN 'HDPE Grinding' 
										WHEN P.id = 314 THEN 'Waste Plastic - Grinding' 
										WHEN P.id = 130 THEN 'PP Grinding' 
										WHEN P.id = 381 THEN 'Waste Plastic - Atta Pouch / Oil Pouch (Lumps)' 
										WHEN P.id = 7 THEN 'Waste Plastic - LD / HM Film (Colour & Printed)- Lumps' 
										WHEN P.id = 379 THEN 'Waste Plastic - LD/HM ( Transparent) (Lumps)' 
										WHEN P.id = 370 THEN 'Waste Plastic - Raffia Scrap (General)(Lumps)' 
									END) AS title
								"),
								"$Sales.cgst_rate as cgst","$Sales.sgst_rate as sgst","$Sales.igst_rate as igst",
								\DB::raw("(CASE
									WHEN D.master_dept_state_code > 0 AND D.master_dept_state_code = D.destination_state_code THEN 'Y'
									WHEN D.master_dept_state_code = 0 AND D.origin_state_code = D.destination_state_code THEN 'Y'
									ELSE 'N'
								END) AS is_from_same_state"),
								\DB::raw("'Kg' as UOM"))
								->join("$Product as P","$Sales.product_id","=","P.id")
								->leftjoin("$Dispatch as D","$Sales.dispatch_id","=","D.id")
								->whereIn("$Sales.id",$salesID)
								->get();
				$dispatchId 	= 0;
				$company_id 	= 0;
				####### BILL OF SUPPLY ONLY FOR AFR ############
				
				####### BILL OF SUPPLY ONLY FOR AFR ############
				if(!empty($SalesData)) {
					foreach($SalesData as $key => $value){
						$SalesData[$key]['description'] = WmDispatchProduct::where("dispatch_id",$value->dispatch_id)->where('id',$value->dispatch_product_id)->value('description');
						$SalesData[$key]['is_charges'] = 0;
						$dispatchId 			= $SalesData[0]->dispatch_id;
						$data['invoice_title'] 	= ($data->invoice_dispatch_type == RECYCLEBLE_TYPE && $value->product_id == SALES_AFR_PRODUCT) ?
						"Bill of Supply" : $data->invoice_title;
						$InvoiceTitleFlag = ($SalesData[$key]['gst_amount'] > 0) ? true : false; 
					}
				}
				$data['invoice_title'] 	= ($InvoiceTitleFlag) ? "Tax Invoice" : $data['invoice_title'];
				####### Invoice Additional Charges ############
				
					$AdditionalCharges = InvoiceAdditionalCharges::GetInvoiceAdditionalCharges(0,$InvoiceId);
				if($fromCnDn == 0){
					if (!empty($AdditionalCharges))
					{
						$counter = sizeof($SalesData);
						$counter++;
						foreach ($AdditionalCharges as $AdditionalCharge) {
							$NewRow['title'] 		= $AdditionalCharge['charge_name'];
							$NewRow['description'] 	= "";
							$NewRow['hsn_code'] 	= $AdditionalCharge['hsn_code'];
							$NewRow['quantity'] 	= intval($AdditionalCharge['totalqty']);
							$NewRow['rate'] 		= $AdditionalCharge['rate'];
							$NewRow['gross_amount']	= $AdditionalCharge['gross_amount'];
							$NewRow['gst_amount']	= $AdditionalCharge['gst_amount'];
							$NewRow['net_amount']	= $AdditionalCharge['net_amount'];
							$NewRow['cgst']			= $AdditionalCharge['cgst'];
							$NewRow['cgst_amount']	= $AdditionalCharge['cgst_amount'];
							$NewRow['sgst']			= $AdditionalCharge['sgst'];
							$NewRow['sgst_amount']	= $AdditionalCharge['sgst_amount'];
							$NewRow['igst']			= $AdditionalCharge['igst'];
							$NewRow['igst_amount']	= $AdditionalCharge['igst_amount'];
							$NewRow['is_charges'] 	= 1;
							$SalesData->push(new WmSalesMaster($NewRow));
							$counter++;
						}
					}
				}
				$data->additional_charges 	= (!empty($AdditionalCharges)) ? $AdditionalCharges : array();
				####### Invoice Additional Charges ############

				$consignee_name 		= ShippingAddressMaster::select("shipping_address_master.*")
				->join("wm_dispatch","shipping_address_master.id","=","wm_dispatch.shipping_address_id")
				->where("wm_dispatch.id",$dispatchId)
				->first();
				
				$data->consignee_name 	= (!empty($consignee_name)) ? $consignee_name->consignee_name : $data->client_name;
				$data->consignee_gst 	= (!empty($consignee_name)) ? $consignee_name->gst_no : "";
				$data->shipping_state_code = (!empty($consignee_name)) ? GSTStateCodes::where("state_code",$consignee_name->state_code)->value('display_state_code') : "";
				$data->shipping_address = (!empty($consignee_name)) ? $consignee_name->shipping_address : "";
				$data->shipping_state 	= (!empty($consignee_name)) ? $consignee_name->state : "";
				$data->shipping_city 	= (!empty($consignee_name)) ? $consignee_name->city : "";
				$data->sales_list 		= $SalesData;

				$companyDetails 		= WmDispatch::select(
											"COM.company_id",
											"COM.company_name",
											"COM.address1",
											"COM.address2",
											"COM.city",
											"COM.zipcode",
											"COM.phone_office",
											"COM.gst_no",
											"COM.pan",
											"COM.cin_no",
											"$Dispatch.from_mrf",
											"$Dispatch.origin",
											"$Dispatch.origin_state_code",
											"$Dispatch.origin_city",
											"COM.cin_no",
											"$Dispatch.rent_amt",
											"$Dispatch.total_rent_amt",
											"$Dispatch.discount_amt",
											"$Dispatch.rent_cgst",
											"$Dispatch.rent_sgst",
											"$Dispatch.rent_igst",
											"$Dispatch.rent_gst_amt",
											"$Dispatch.type_of_transaction",
											"$Dispatch.bill_from_mrf_id",
											"$Dispatch.master_dept_id",
											"$Dispatch.tcs_rate",
											"$Dispatch.tcs_amount",
											"$Dispatch.e_invoice_no",
											"$Dispatch.acknowledgement_no",
											"$Dispatch.acknowledgement_date",
											"$Dispatch.show_vendor_name_flag",
											"$Dispatch.signed_qr_code",
											"$Dispatch.client_master_id",
											"V.vehicle_number",
											"$Dispatch.remarks",
											"PARA1.para_value as days",
											"PARA.para_value as transport_cost_name",
											\DB::raw("LOC_S.gst_state_code_id as state_code"),
											\DB::raw("LOC_S.state_name as state_name"),
											\DB::raw("LOC.city as city_name"))
				->join("company_master as COM","$Dispatch.company_id","=","COM.company_id")
				->leftjoin("$Location as LOC","COM.city","=","LOC.location_id")
				->leftjoin("state_master as LOC_S","COM.state","=","LOC_S.state_id")
				->leftjoin("$Vehicle as V","$Dispatch.vehicle_id","=","V.vehicle_id")
				->leftjoin("parameter as PARA","$Dispatch.transport_cost_id","=","PARA.para_id")
				->leftjoin("parameter as PARA1","$Dispatch.collection_cycle_term","=","PARA1.para_id")
				->where("$Dispatch.id",$dispatchId)
				->first();

				$BILL_FROM_MRF_ID 	= 	($companyDetails) ? $companyDetails->bill_from_mrf_id : 0;
				$MASTER_DEPT_ID 	= 	($companyDetails) ? $companyDetails->master_dept_id : 0;

				$MasterMRFDepartment 	= 	WmDepartment::select("wm_department.*",
											\DB::raw("LOWER(wm_department.address) as address"),
											\DB::raw("LOWER(location_master.city) as mrf_city_name"),
											\DB::raw("LOWER(GST.state_name) as mrf_state_name"),
											\DB::raw("GST.display_state_code as mrf_state_code"))
											->join("location_master","wm_department.location_id","=","location_master.location_id")
											->leftjoin("GST_STATE_CODES as GST","wm_department.gst_state_code_id","=","GST.id")
											->where("wm_department.id",$MASTER_DEPT_ID)
											->first();
				$data->MasterMRFDepartment 	= 	$MasterMRFDepartment;


				$MRFDepartment 		= 	WmDepartment::select("wm_department.*",
										\DB::raw("LOWER(wm_department.address) as address"),
										\DB::raw("LOWER(location_master.city) as mrf_city_name"),
										\DB::raw("LOWER(GST.state_name) as mrf_state_name"),
										\DB::raw("GST.display_state_code as mrf_state_code"))
										->join("location_master","wm_department.location_id","=","location_master.location_id")
										->leftjoin("GST_STATE_CODES as GST","wm_department.gst_state_code_id","=","GST.id")
										->where("wm_department.id",$BILL_FROM_MRF_ID)
										->first();
				$data->MRFDepartment 	= $MRFDepartment;
				$data->company_details 	= ($companyDetails) ? $companyDetails : "";
				$data->vehicle_number 	= ($companyDetails) ? $companyDetails->vehicle_number 	: "";
				$data->rent_amt 		= ($companyDetails) ? $companyDetails->rent_amt 	: 0;
				$data->total_rent_amt 	= ($companyDetails) ? $companyDetails->total_rent_amt : 0;
				$data->discount_amt 	= ($companyDetails) ? $companyDetails->discount_amt : 0;
				$data->rent_sgst 		= ($companyDetails) ? $companyDetails->rent_sgst 	: 0;
				$data->rent_cgst 		= ($companyDetails) ? $companyDetails->rent_cgst 	: 0;
				$data->rent_igst 		= ($companyDetails) ? $companyDetails->rent_igst 	: 0;
				$data->rent_gst_amt 	= ($companyDetails) ? $companyDetails->rent_gst_amt : 0;
				$data->tcs_amount 		= ($companyDetails) ? $companyDetails->tcs_amount : 0;
				$data->tcs_rate 		= ($companyDetails) ? $companyDetails->tcs_rate : 0;
				$data->e_invoice_no 	= ($companyDetails) ? $companyDetails->e_invoice_no : "";
				$data->type_of_transaction 	= ($companyDetails) ? $companyDetails->type_of_transaction : 0;
				$data->remarks 			= ($companyDetails) ? $companyDetails->remarks : "";
				$data->transport_cost_name 	= ($companyDetails) ? $companyDetails->transport_cost_name : "";
				$data->tcs_tax_percent 	= TCS_TEX_PERCENT;
				$vendor_address 		= "";
				$vendor_city 			= "";
				$vendor_state_code 		= "";
				$vendor_state_name 		= "";
				$vendor_name 			= "";
				$show_vendor_name_flag  = ($companyDetails) ? $companyDetails->show_vendor_name_flag : 0;
				if(isset($companyDetails) && $companyDetails->from_mrf == "N"){
					$customer 			= CustomerMaster::where("customer_id",$companyDetails->origin)->first();
					$vendor_name 		= ($customer) ? $customer->first_name." ".$customer->middle_name." ".$customer->last_name : "";
					$vendor_address 	= ($customer) ? $customer->address1." ".$customer->address2 : "";
					$vendor_city 		= ($customer) ? LocationMaster::where("location_id",$companyDetails->origin_city)->value("city") : "";
					$vendor_state_code 	= ($companyDetails) ? $companyDetails->origin_state_code : "";
					$vendor_state_name 	= ($companyDetails) ? GSTStateCodes::where("id",$companyDetails->origin_state_code)->value("state_name") : "";
				}else{
					$vendor_name 		= (isset($data->MasterMRFDepartment->department_name)) ? $data->MasterMRFDepartment->department_name : "";
					$vendor_address 	= (isset($data->MasterMRFDepartment->address)) ? $data->MasterMRFDepartment->address : "";
					$vendor_city 		= (isset($data->MasterMRFDepartment->mrf_city_name)) ? $data->MasterMRFDepartment->mrf_city_name : "";
					$vendor_state_code 	= (isset($data->MasterMRFDepartment->mrf_state_code)) ? $data->MasterMRFDepartment->mrf_state_code : "";
					$vendor_state_name 	= (isset($data->MasterMRFDepartment->mrf_state_name)) ? $data->MasterMRFDepartment->mrf_state_name : "";
				}
				######### QR CODE GENERATION OF E INVOICE NO #############
				$qr_code 				= "";
				$e_invoice_no 			= (!empty($companyDetails->e_invoice_no)) 		? $companyDetails->e_invoice_no : "";
				$acknowledgement_no 	= (!empty($companyDetails->acknowledgement_no)) 	? $companyDetails->acknowledgement_no : "";
				$acknowledgement_date 	= (!empty($companyDetails->acknowledgement_date)) ? $companyDetails->acknowledgement_date : "";
				$signed_qr_code 		= (!empty($companyDetails->signed_qr_code)) ? $companyDetails->signed_qr_code : "";
				$qr_code_string 		= "E-Invoice No. :".$e_invoice_no." Acknowledgement No. : ".$acknowledgement_no." Acknowledgement Date : ".$acknowledgement_date;
				$qr_code_string 		= (empty($e_invoice_no) && empty($acknowledgement_no) && empty($acknowledgement_date)) ? " " : $qr_code_string ;
				if(!empty($e_invoice_no) || !empty($acknowledgement_no) || !empty($acknowledgement_date)){
					$QRCODE 				= url("/")."/".GetQRCode($signed_qr_code,$dispatchId);
					$path 					= public_path("/")."phpqrcode/".$dispatchId.".png";
					$type 					= pathinfo($path, PATHINFO_EXTENSION);
					if(file_exists($path)){
						$imgData				= file_get_contents($path);
						$qr_code 				= 'data:image/' . $type . ';base64,' . base64_encode($imgData);
						unlink(public_path("/")."/phpqrcode/".$dispatchId.".png");
					}
				}
				// $data->signature_flag 		= ($companyDetails && $companyDetails->client_master_id == 567) ? false : true;
				$data->signature_flag 		= true;
				$data->qr_code 				= $qr_code;
				$data->e_invoice_no 		= $e_invoice_no ;
				$data->acknowledgement_no 	= $acknowledgement_no;
				$data->acknowledgement_date = $acknowledgement_date;
				######### QR CODE GENERATION OF E INVOICE NO #############
				$data->vendor_name 			= ($show_vendor_name_flag > 0) ? $vendor_name : "";
				$data->vendor_address 		= ucwords(strtolower($vendor_address));
				$data->vendor_city 			= ucwords(strtolower($vendor_city));
				$data->vendor_state_code 	= $vendor_state_code;
				$data->vendor_state_name 	= ucwords(strtolower($vendor_state_name));
				$data->days 				= ($companyDetails) ? $companyDetails->days : "";
			}
		}
		return $data;
	}

	public static function isValidInvoiceId($id)
    {
    	$data = self::select(['id','invoice_no'])->find($id)->toArray();
    	if(!empty($data)) {
    		return $data;
    	}

    	return array();
    }

    /*
	Use 	: List Invoice for Client Mobile App
	Author 	: Hardyesh Gupta
	Date 	: 31 Oct,2023
	*/
	public static function SearchInvoiceMobile($request,$paginate = true,$client_id = 0){
		$data1 			= array();
		$table 			= (new static)->getTable();
		$ClientMaster 	= new WmClientMaster();
		$LocationMaster	= new LocationMaster();
		$DepartmentTbl	= new WmDepartment();
		$Client 		= $ClientMaster->getTable();
		$Location		= $LocationMaster->getTable();
		$Department		= $DepartmentTbl->getTable();
		$sortBy         = (isset($request->sortBy) && !empty($request->sortBy)) ? $request->sortBy : "id"; 
		$sortOrder      = (isset($request->sortOrder) && !empty($request->sortOrder)) ? $request->sortOrder : "ASC"; 
		$recordPerPage  = (isset($request->size) && !empty($request->size)) ? $request->size : DEFAULT_SIZE; 
		$pageNumber     = (isset($request->pageNumber) && !empty($request->pageNumber)) ? $request->pageNumber : ''; 
		$cityId         = GetBaseLocationCity();
		$InvoiceNo 		= (isset($request->invoice_no) && !empty($request->invoice_no)) ? $request->invoice_no : ""; 
		$collectStatus 	= (isset($request->collect_payment_status) && !empty($request->collect_payment_status)) ? $request->collect_payment_status : ""; 
		$InvoiceStatus 	= (isset($request->invoice_status) && !empty($request->invoice_status)) ? $request->invoice_status : 0; 
		$ClientName 	= (isset($request->client_name) && !empty($request->client_name)) ? $request->client_name : 0; 
		$FromDate 		= (isset($request->startDate) && !empty($request->startDate)) ? date("Y-m-d",strtotime($request->startDate)) : date("Y-m-d");
		$EndDate   		= (isset($request->endDate) && !empty($request->endDate)) ? date("Y-m-d",strtotime($request->endDate)) : date("Y-m-d");
		$data 			= self::select("$table.*",
							\DB::raw("CASE WHEN 1=1 THEN(
								SELECT COUNT(wm_payment_receive.id)
								FROM wm_payment_receive
								WHERE
								wm_payment_receive.invoice_id = wm_invoices.id
							) END AS paid_status"),
							"C.client_name",
							\DB::raw("'' as payment_status"),
							\DB::raw("L.city as city_name"),
							\DB::raw("DEP.location_id")
						)
		->join($Client." as C","$table.client_master_id","=","C.id")
		->leftjoin("$Location as L","C.city_id","=","L.location_id")
		->leftjoin("$Department as DEP","$table.master_dept_id","=","DEP.id");
		$data->where("C.id",Auth()->user()->id)->where("C.company_id",Auth()->user()->company_id);
		if(!empty($client_id)){
			$data->where("C.id",$client_id);	
		}
		if(isset($request->nespl) && !empty($request->nespl)) 
		{
			$nespl = $request->nespl;
			if($nespl == "0"){
				$data->where("$table.nespl",$nespl );
			}elseif($nespl == "1"){
				$data->where("$table.nespl", $nespl);
			}
		}
		if(isset($request->city_id) && !empty($request->city_id)) 
		{
			$data->whereIn('DEP.location_id', explode(",",$request->city_id));
		}
		else{
			if(!empty($cityId)){
				$data->whereIn("DEP.location_id",$cityId);	
			}
		 }
		if(!empty($InvoiceNo))
		{
			$data->where("$table.invoice_no","like","%$InvoiceNo%");
		}
		if(!empty($ClientName))
		{
			$data->where("C.client_name","like","%$ClientName%");
		}

		if($paginate){
			$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
			if($result->total()> 0){
				$data1 		= $result->toArray();
				$dataResult = $data1['result'];
				foreach($dataResult as $key => $field){
					$data1['result'][$key]['encypt_id'] 	= passencrypt($field['id']);
					$data1['result'][$key]['invoice_url'] 	= url("/")."/".INVOICE_URL."/".passencrypt($field['id']);
					$InvoiceAmount 							=  WmSalesMaster::where("invoice_no",$field['id'])->where("dispatch_id",$field['dispatch_id'])->sum('net_amount');
					$AdditionalChargesAmount 				= 0;
					$AdditionalCharges 						= InvoiceAdditionalCharges::GetInvoiceAdditionalCharges($field['dispatch_id'],$field['id']);
					if (!empty($AdditionalCharges))
					{
						$AdditionalChargesAmount += $AdditionalCharge['net_amount'];
					}
					$data1['result'][$key]['invoice_amount'] 	= (!empty($InvoiceAmount)) ? ($InvoiceAmount + $AdditionalChargesAmount): 0;
					$data1['result'][$key]['additional_charge'] = $AdditionalChargesAmount;
					$payment_status 	= '';
					if($field['collect_payment_status'] == 1 && $field['invoice_status']== 0){
						$payment_status = 'Completed';
					}elseif($field['collect_payment_status'] = 0 && $field['invoice_status'] == 0){
						$seconds_diff 	= strtotime(date("Y-m-d")) - strtotime(substr($field['created_at'],0,10));
						$day 			= floor($seconds_diff/3600/24);
						if($day>7) {
							$payment_status = 'Late';
						}
						if($field['invoice_status'] == 0) {
							$payment_status = 'Partial';
						}

					}
					$data1['result'][$key]['payment_status'] = $payment_status;
				}
			}
		}
		return $data1;
	}
}
