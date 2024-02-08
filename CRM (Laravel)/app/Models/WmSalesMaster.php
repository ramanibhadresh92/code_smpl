<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDispatch;
use App\Models\WmInvoices;
use App\Models\WmDispatchProduct;
use App\Models\VehicleMaster;
use App\Models\OutWardLadger;
use App\Models\InvoiceAdditionalCharges;
use App\Models\WmDispatchAddtionalData;
class WmSalesMaster extends Model implements Auditable
{
    protected 	$table 		=	'wm_sales_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	/*
	Use 	: Add Sales Data
	Author 	: Axay Shah
	Date 	: 04 July,2019
	*/
	public static function AddSales($request)
	{
		$salesId 					= 0;
		$Sales 						= new self();
		$Sales->dispatch_id 		= (isset($request['dispatch_id']) 	&& !empty($request['dispatch_id'])) ?  $request['dispatch_id']	: 0;
		$Sales->dispatch_product_id = (isset($request['dispatch_product_id']) 	&& !empty($request['dispatch_product_id'])) ?  $request['dispatch_product_id']	: 0;
		$Sales->product_id 			= (isset($request['product_id']) 	&& !empty($request['product_id'])) 	?  $request['product_id'] 	: 0;
		$Sales->invoice_no 			= (isset($request['invoice_no']) 	&& !empty($request['invoice_no'])) 	?  $request['invoice_no'] 	: "";
		$Sales->rate 				= (isset($request['rate']) 			&& !empty($request['rate'])) 		?  $request['rate']			: 0;
		$Sales->quantity 			= (isset($request['quantity']) 		&& !empty($request['quantity'])) 	?  $request['quantity']		: 0;
		$Sales->gross_amount 		= (isset($request['gross_amount']) 	&& !empty($request['gross_amount']))?  $request['gross_amount']	: 0;
		$Sales->cgst_rate 			= (isset($request['cgst_rate']) 	&& !empty($request['cgst_rate'])) 	?  $request['cgst_rate']	: 0;
		$Sales->sgst_rate 			= (isset($request['sgst_rate']) 	&& !empty($request['sgst_rate'])) 	?  $request['sgst_rate']	: 0;
		$Sales->igst_rate 			= (isset($request['igst_rate']) 	&& !empty($request['igst_rate'])) 	?  $request['igst_rate']	: 0;
		$Sales->gst_amount 			= (isset($request['gst_amount']) 	&& !empty($request['gst_amount'])) 	?  $request['gst_amount']	: 0;
		$Sales->vat_type 			= (isset($request['vat_type']) 		&& !empty($request['vat_type'])) 	?  $request['vat_type']		: "";
		$Sales->net_amount 			= (isset($request['net_amount']) 	&& !empty($request['net_amount'])) 	?  $request['net_amount']	: 0;
		$Sales->payment_done 		= (isset($request['payment_done']) 	&& !empty($request['payment_done']))?  $request['payment_done']	: '0';
		$Sales->final_sale 			= (isset($request['final_sale']) 	&& !empty($request['final_sale'])) 	?  $request['final_sale']	: 0;
		$Sales->sales_date 			= (isset($request['sales_date']) 	&& !empty($request['sales_date'])) 	?  $request['sales_date']	: date("Y-m-d H:i:s");
		$Sales->master_dept_id 		= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ?  $request['master_dept_id']: 0;
		$Sales->created_by 			= Auth()->user()->adminuserid;
		if($Sales->save()) {
			$salesId = $Sales->id;
		}
		return $salesId;
	}

	/*
	Use 	: Add Sales Data
	Author 	: Axay Shah
	Date 	: 04 July,2019
	*/
	public static function GenerateInvoice($request)
	{
		$id 					= 0;
		$data 					= array();
		$DispatchTbl 			= new WmDispatch();
		$Sales 					= (new static)->getTable();
		$DispatchId 			= (isset($request['dispatch_id']) && !empty($request['dispatch_id'])) ? $request['dispatch_id'] : 0;
		$DepartmentId 			= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ? $request['master_dept_id'] : 0;
		$ProductList			= (isset($request['product']) && !empty($request['product'])) ? $request['product'] : "";
		$ExcludeGST				= (isset($request['exclude_gst']) && !empty($request['exclude_gst'])) ? $request['exclude_gst'] : "N";
		$clientId 				= (isset($request['client_master_id']) && !empty($request['client_master_id']))?$request['client_master_id']:0;
		$invoice_date			= (isset($request['sales_date'])?date('Y-m-d g:i:s',strtotime($request['sales_date'])):date('Y-m-d g:i:s'));
		$delivery_note			= (isset($request['delivery_note']) && !empty($request['delivery_note'])) ? $request['delivery_note'] : "";
		$eway_bill				= (isset($request['eway_bill']) && !empty($request['eway_bill'])) ? $request['eway_bill'] : "";
		$other_reference		= (isset($request['other_reference']) && !empty($request['other_reference'])) ? $request['other_reference'] : "";
		$buyer_order_no			= (isset($request['buyer_order_no']) && !empty($request['buyer_order_no'])) ? $request['buyer_order_no'] : "";
		$dated					= (isset($request['dated']) && !empty($request['dated'])) ? $request['dated'] : "";
		$dispatch_doc_no		= (isset($request['dispatch_doc_no']) && !empty($request['dispatch_doc_no'])) ? $request['dispatch_doc_no'] : "";
		$dispatch_address		= (isset($request['dispatch_address']) && !empty($request['dispatch_address'])) ? $request['dispatch_address'] : "";
		$vehicle_number			= (isset($request['vehicle_number']) && !empty($request['vehicle_number'])) ? $request['vehicle_number'] : "";
		$dispatched_through		= (isset($request['dispatched_through']) && !empty($request['dispatched_through'])) ? $request['dispatched_through'] : "";
		$destination			= (isset($request['destination']) && !empty($request['destination'])) ? $request['destination'] : "";
		$bill_of_lading			= (isset($request['bill_of_lading']) && !empty($request['bill_of_lading'])) ? $request['bill_of_lading'] : "";
		$terms_of_delivery		= (isset($request['terms_of_delivery']) && !empty($request['terms_of_delivery'])) ? $request['terms_of_delivery'] : "";
		$bill_of_lading			= (isset($request['bill_of_lading']) && !empty($request['bill_of_lading'])) ? $request['bill_of_lading'] : "";
		$shipping_address		= (isset($request['shipping_address']) && !empty($request['shipping_address'])) ? $request['shipping_address'] : "";
		$shipping_city			= (isset($request['shipping_city']) && !empty($request['shipping_city'])) ? $request['shipping_city'] : "";
		$shipping_state			= (isset($request['shipping_state']) && !empty($request['shipping_state'])) ? $request['shipping_state'] : "";
		$shipping_pincode		= (isset($request['shipping_pincode']) && !empty($request['shipping_pincode'])) ? $request['shipping_pincode'] : "";
		$shipping_state_code	= (isset($request['shipping_state_code']) && !empty($request['shipping_state_code'])) ? $request['shipping_state_code'] : "";
		$terms_of_delivery		= (isset($request['terms_of_delivery']) && !empty($request['terms_of_delivery'])) ? $request['terms_of_delivery'] : "";
		$invoice_dispatch_type	= (isset($request['invoice_dispatch_type']) && !empty($request['invoice_dispatch_type'])) ? $request['invoice_dispatch_type'] : "";
		$invoice_recyclable_type= (isset($request['invoice_recyclable_type']) && !empty($request['invoice_recyclable_type'])) ? $request['invoice_recyclable_type'] : "";
		$invoice_no 	= (isset($request['invoice_no']) && !empty($request['invoice_no'])) ? $request['invoice_no'] : 0;
		$salesIds 		= array();
		if(!empty($ProductList)){
			if(!is_array($ProductList)){
				$ProductList = json_decode($ProductList,true);
			}
			foreach($ProductList as $product){
				$product['master_dept_id'] 		= $DepartmentId;
				$product['dispatch_id'] 		= $DispatchId;
				$dispatch_product_id 			= WmDispatchProduct::GetDispatchProductId($product);
				$product['dispatch_product_id']	= $dispatch_product_id;
				$insertProduct 					= self::AddSales($product);
				array_push($salesIds, $insertProduct);
			}
			$sales = "";
			if(!empty($salesIds)){
				$sales =  implode(",",$salesIds);
			}
			$invoiceId = WmInvoices::LastInvoiceId();
			if(!empty($invoiceId)){
				$sales_invoice 	= $invoiceId;
				$in 			= intval($invoiceId)+1;
				if($in>0 && $in<10){$str='00000';}
				elseif($in>=10 && $in<100){$str='0000';}
				elseif($in>=100 && $in<1000){$str='000';}
				elseif($in>=1000 && $in<10000){$str='00';}
				elseif($in>=10000 && $in<100000){$str='0';}
				$inv=$str.$in;
				$data = WmDispatch::find($DispatchId);
				$data['invoice_no'] 			= $invoice_no;
				$data['client_master_id']		= $clientId;
				$data['sales_id']				= $sales;
				$data['invoice_status']			= 0;
				$data['collect_payment_status']	= 0;
				$data['master_dept_id']			= $DepartmentId;
				$data['created_by']				= Auth()->user()->adminuserid;
				$data['created_dt']				= $invoice_date;
				$data['invoice_date']			= date("Y-m-d",strtotime($invoice_date));
				$data['invoice_id'] 			= 0;
				$data['exclude_gst'] 			= $ExcludeGST;
				$data['exclude_gst'] 			= $ExcludeGST;
				$data['delivery_note'] 			= $delivery_note;
				$data['eway_bill'] 				= $eway_bill;
				$data['other_reference'] 		= $other_reference;
				$data['buyer_order_no'] 		= $buyer_order_no;
				$data['dated'] 					= $dated;
				$data['dispatch_doc_no'] 		= $dispatch_doc_no;
				$data['dispatch_address'] 		= $dispatch_address;
				$data['vehicle_number'] 		= $vehicle_number;
				$data['dispatched_through'] 	= $dispatched_through;
				$data['destination'] 			= $destination;
				$data['terms_of_delivery'] 		= $terms_of_delivery;
				$data['bill_of_lading'] 		= $bill_of_lading;
				$data['shipping_state_code'] 	= $shipping_state_code;
				$data['shipping_address'] 		= $shipping_address;
				$data['shipping_state'] 		= $shipping_state;
				$data['shipping_city'] 			= $shipping_city;
				$data['shipping_pincode'] 		= $shipping_pincode;
				$data['terms_of_delivery'] 		= $terms_of_delivery;
				$data['invoice_dispatch_type'] 	= $invoice_dispatch_type;
				$data['invoice_recyclable_type'] = $invoice_recyclable_type;
				$id  = WmInvoices::InsertInvoiceDetail($data);
				if($id > 0){
					$data['id'] = $id;
					$data['encypt_id'] 		=  passencrypt($id);
					$data['invoice_url'] 	=  url("/")."/".INVOICE_URL."/".passencrypt($id);
					WmDispatch::where("id",$DispatchId)->update(["invoice_generated"=>1]);
					self::whereIn("id",$salesIds)->update(["final_sale"=>1]);
					$requestObj = json_encode($request,JSON_FORCE_OBJECT);
					LR_Modules_Log_CompanyUserActionLog($requestObj,$DispatchId);
				}
			}
			/*OUTWARD OF PRODUCT FROM MRF - 28 AUG,2019*/
			$Dispatch = WmSalesMaster::select("$Sales.*","D.from_mrf")
						->join($DispatchTbl->getTable()." as D","$Sales.dispatch_id","=","D.id")
						->where("$Sales.dispatch_id",$DispatchId)
						->get();
			if(!empty($Dispatch)){
				foreach($Dispatch as $D){
					$FROM_MRF =  $D->from_mrf;
					$date 	= date("Y-m-d",strtotime($D->created_at));
					OutWardLadger::CreateOutWard($D->product_id,$D->quantity,TYPE_SALES,$D->master_dept_id,$date);
				}
			}
			/*END CODE FOR OUTWARD*/
			return $data;
		}
		return false;
	}

	/*
	Use 	: Add Sales Data
	Author 	: Axay Shah
	Date 	: 05 March,2020
	*/
	public static function GenerateInvoicev2($DispatchId)
	{
		$DispatchTbl 	= new WmDispatch();
		$Sales 			= (new static)->getTable();
		$data 			= WmDispatch::find($DispatchId);
		if($data)
		{
			$vehicle_number = VehicleMaster::where("vehicle_id",$data->vehicle_id)->value("vehicle_number");
			$salesIds 		= array();
			$DepartmentId 	= $data->master_dept_id;
			$invoice_date 	= $data->dispatch_date;
			$ProductList	= WmDispatchProduct::where("dispatch_id",$DispatchId)->get()->toArray();
			$TotalQty 		= 0;
			if(!empty($ProductList))
			{
				foreach($ProductList as $product) {
					$product['master_dept_id'] 		= $DepartmentId;
					$product['rate'] 				= $product['price'];
					$product['dispatch_id'] 		= $DispatchId;
					$product['dispatch_product_id'] = $product['id'];
					$TotalQty 						+= $product['quantity'];
					$insertProduct 					= self::AddSales($product);
					array_push($salesIds, $insertProduct);
				}
				$sales = "";
				if(!empty($salesIds)) {
					$sales =  implode(",",$salesIds);
				}
				$data['invoice_status']			= 0;
				$data['collect_payment_status']	= 0;
				$data['master_dept_id']			= $DepartmentId;
				$data['created_by']				= Auth()->user()->adminuserid;
				$data['created_dt']				= $invoice_date;
				$data['invoice_date']			= date("Y-m-d",strtotime($invoice_date));
				$data['invoice_id'] 			= 0;
				$data['eway_bill'] 				= $data->eway_bill_no;
				$data['vehicle_number'] 		= $vehicle_number;
				$data['destination'] 			= $data->shipping_city;
				$data['bill_of_lading'] 		= $data->bill_of_lading;
				$data['shipping_state_code'] 	= $data->shipping_state_code;
				$data['shipping_address'] 		= $data->shipping_address;
				$data['shipping_state'] 		= $data->shipping_state;
				$data['shipping_city'] 			= $data->shipping_city;
				$data['shipping_pincode'] 		= $data->shipping_pincode;
				$data['invoice_dispatch_type'] 	= $data->dispatch_type;
				$data['invoice_recyclable_type']= $data->recyclable_type;
				$data['invoice_no'] 			= $data->challan_no;
				$data['sales_id'] 				= $sales;
				$id  							= WmInvoices::InsertInvoiceDetail($data);
				if($id > 0) {
					$data['id'] = $id;
					$data['encypt_id'] 		= passencrypt($id);
					$data['invoice_url'] 	= url("/")."/".INVOICE_URL."/".passencrypt($id);
					WmDispatch::where("id",$DispatchId)->update(["invoice_generated"=>1]);
					self::whereIn("id",$salesIds)->update(["final_sale"=>1,"invoice_no"=>$id]);
					InvoiceAdditionalCharges::SaveInvoiceAdditionalCharges($DispatchId,$id,$data->client_master_id,$TotalQty);
					$AddtionalData = WmDispatchAddtionalData::where("dispatch_id",$DispatchId)->first();
					if($AddtionalData){
						$addtionalDataArray = array();
						
						$addtionalDataArray['buyer_order_no'] 		= $AddtionalData->buyer_order_no;
						$addtionalDataArray['dispatch_doc_no'] 		= $AddtionalData->dispatch_doc_no;
						$addtionalDataArray['dispatched_through'] 	= $AddtionalData->dispatched_through;
						$addtionalDataArray['bill_of_lading'] 		= $AddtionalData->bill_of_lading;
						$addtionalDataArray['terms_of_delivery'] 	= $AddtionalData->terms_of_delivery;
						$addtionalDataArray['destination'] 			= $AddtionalData->destination;
						$addtionalDataArray['delivery_note'] 		= $AddtionalData->delivery_note;
						$addtionalDataArray['delivery_note_date'] 	= $AddtionalData->delivery_note_date;
						$addtionalDataArray['dated'] 				= $AddtionalData->dated;
						$addtionalDataArray['other_reference'] 		= $AddtionalData->other_reference;
					
						$AddtionalData =  WmInvoices::where("dispatch_id",$DispatchId)->where("id",$id)->update($addtionalDataArray);
					}
				}
			}
		}
		return $data;
	}

	/*
	Use 	: Add Sales Data
	Author 	: Axay Shah
	Date 	: 04 July,2019
	*/
	public static function EditInvoice($request){
		$request 		= json_decode(json_encode($request),true);
		$id 			= 0;
		$data 			= array();
		$DispatchTbl 	= new WmDispatch();
		$Sales 			= (new static)->getTable();
		$InvoiceId 		= (isset($request['invoice_id']) 	&& !empty($request['invoice_id'])) ? $request['invoice_id'] : 0;
		$DispatchId 	= (isset($request['dispatch_id']) 	&& !empty($request['dispatch_id'])) ? $request['dispatch_id'] : 0;
		$DepartmentId 	= (isset($request['master_dept_id']) && !empty($request['master_dept_id'])) ? $request['master_dept_id'] : 0;
		$ProductList	= (isset($request['product']) && !empty($request['product'])) ? $request['product'] : "";
		$ExcludeGST		= (isset($request['exclude_gst']) && !empty($request['exclude_gst'])) ? $request['exclude_gst'] : "N";
		$clientId 		= (isset($request['client_master_id']) && !empty($request['client_master_id']))?$request['client_master_id']:0;
		$invoice_date	= (isset($request['sales_date'])?date('Y-m-d g:i:s',strtotime($request['sales_date'])):date('Y-m-d g:i:s'));
		$delivery_note			= (isset($request['delivery_note']) && !empty($request['delivery_note'])) ? $request['delivery_note'] : "";
		$eway_bill				= (isset($request['eway_bill']) && !empty($request['eway_bill'])) ? $request['eway_bill'] : "";
		$other_reference		= (isset($request['other_reference']) && !empty($request['other_reference'])) ? $request['other_reference'] : "";
		$buyer_order_no			= (isset($request['buyer_order_no']) && !empty($request['buyer_order_no'])) ? $request['buyer_order_no'] : "";
		$dated					= (isset($request['dated']) && !empty($request['dated'])) ? $request['dated'] : "";
		$dispatch_doc_no		= (isset($request['dispatch_doc_no']) && !empty($request['dispatch_doc_no'])) ? $request['dispatch_doc_no'] : "";
		$dispatch_address		= (isset($request['dispatch_address']) && !empty($request['dispatch_address'])) ? $request['dispatch_address'] : "";
		$vehicle_number			= (isset($request['vehicle_number']) && !empty($request['vehicle_number'])) ? $request['vehicle_number'] : "";
		$dispatched_through		= (isset($request['dispatched_through']) && !empty($request['dispatched_through'])) ? $request['dispatched_through'] : "";
		$destination			= (isset($request['destination']) && !empty($request['destination'])) ? $request['destination'] : "";
		$bill_of_lading			= (isset($request['bill_of_lading']) && !empty($request['bill_of_lading'])) ? $request['bill_of_lading'] : "";

		$terms_of_delivery		= (isset($request['terms_of_delivery']) && !empty($request['terms_of_delivery'])) ? $request['terms_of_delivery'] : "";
		$bill_of_lading			= (isset($request['bill_of_lading']) && !empty($request['bill_of_lading'])) ? $request['bill_of_lading'] : "";
		$shipping_address		= (isset($request['shipping_address']) && !empty($request['shipping_address'])) ? $request['shipping_address'] : "";
		$shipping_city			= (isset($request['shipping_city']) && !empty($request['shipping_city'])) ? $request['shipping_city'] : "";
		$shipping_state			= (isset($request['shipping_state']) && !empty($request['shipping_state'])) ? $request['shipping_state'] : "";
		$shipping_pincode		= (isset($request['shipping_pincode']) && !empty($request['shipping_pincode'])) ? $request['shipping_pincode'] : "";
		$shipping_state_code		= (isset($request['shipping_state_code']) && !empty($request['shipping_state_code'])) ? $request['shipping_state_code'] : "";
		$terms_of_delivery			= (isset($request['terms_of_delivery']) && !empty($request['terms_of_delivery'])) ? $request['terms_of_delivery'] : "";
		$delivery_note_date			= (isset($request['delivery_note_date']) && !empty($request['delivery_note_date'])) ? date("Y-m-d",strtotime($request['delivery_note_date'])) : "0000-00-00";

		$invoice_dispatch_type			= (isset($request['invoice_dispatch_type']) && !empty($request['invoice_dispatch_type'])) ? $request['invoice_dispatch_type'] : "";
		$invoice_recyclable_type			= (isset($request['invoice_recyclable_type']) && !empty($request['invoice_recyclable_type'])) ? $request['invoice_recyclable_type'] : "";

		$invoice_no 	= (isset($request['invoice_no']) && !empty($request['invoice_no'])) ? $request['invoice_no'] : 0;
		$transporter_name = (isset($request['transporter_name']) && !empty($request['transporter_name'])) ? ucwords(strtolower($request['transporter_name'])) : "";
		($DispatchId > 0 && !empty($transporter_name)) ? WmDispatch::where("id",$DispatchId)->update(["transporter_name"=>$transporter_name,"bill_of_lading"=>$bill_of_lading]) : "";
		$salesIds 		= array();
		$invoice = WmInvoices::find($InvoiceId);
		if($invoice){
			$data['buyer_order_no'] 		= $buyer_order_no;
			$data['dispatch_doc_no'] 		= $dispatch_doc_no;
			$data['dispatched_through'] 	= $dispatched_through;
			$data['bill_of_lading'] 		= $bill_of_lading;
			$data['terms_of_delivery'] 		= $terms_of_delivery;
			$data['destination'] 			= $destination;
			$data['delivery_note'] 			= $delivery_note;
			$data['delivery_note_date'] 	= $delivery_note_date;
			$data['dated'] 					= $dated;
			$data['other_reference'] 		= $other_reference;
			$data['invoice_no'] 			= $invoice_no;
			$data['updated_by']				= Auth()->user()->adminuserid;
			$data['created_at']				= $invoice_date;
			$data['invoice_date']			= date("Y-m-d",strtotime($invoice_date));
			$data['updated_at'] 			= date("Y-m-d H:i:s");
			$data['invoice_edit'] 			= 1;
			$invoice['invoice_url'] 		= url('/invoice')."/".passencrypt($InvoiceId);
			$update = WmInvoices::where("id",$InvoiceId)->update($data);
			$requestObj = json_encode($request,JSON_FORCE_OBJECT);
			LR_Modules_Log_CompanyUserActionLog($requestObj,$InvoiceId);
		}
		return $invoice;

		return false;
	}

	/*
	Use 	: CalculateTotalInvoiceAmount
	Author 	: Axay Shah
	Date 	: 04 July,2019
	*/
	public static function CalculateTotalInvoiceAmount($DispatchID=0){
		$FinalAmount 	= 0;
		$Amount 		= self::where("dispatch_id",$DispatchID)->sum("net_amount");
		$Dispatch 		= WmDispatch::find($DispatchID);
		$TotalNetamount 		= ($Amount > 0) ? $Amount : 0;
		$TotalRentamount 		= ($Dispatch && isset($Dispatch->total_rent_amt) && $Dispatch->total_rent_amt > 0) ? _FormatNumberV2($Dispatch->total_rent_amt) : 0;
		$TotalDiscountamount 	= ($Dispatch && isset($Dispatch->discount_amt) && $Dispatch->discount_amt > 0) ? _FormatNumberV2($Dispatch->discount_amt) : 0;
		$FinalAmount 			= _FormatNumberV2(($Amount + $TotalRentamount) - $TotalDiscountamount);
		return $FinalAmount;
	}
}
