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
class WmDispatchAddtionalData extends Model implements Auditable
{
    protected 	$table 		=	'wm_dispatch_addtional_edit_data';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	

	/*
	Use 	: Add Sales Data
	Author 	: Axay Shah
	Date 	: 04 July,2019
	*/
	public static function EditDispatchAdditionalData($request,$DispatchId=0){
		$DispatchId 			= (isset($request['dispatch_id']) 	&& !empty($request['dispatch_id'])) ? $request['dispatch_id'] : $DispatchId;
		$delivery_note			= (isset($request['delivery_note']) && !empty($request['delivery_note'])) ? $request['delivery_note'] : "";
		$eway_bill				= (isset($request['eway_bill']) && !empty($request['eway_bill'])) ? $request['eway_bill'] : "";
		$other_reference		= (isset($request['other_reference']) && !empty($request['other_reference'])) ? $request['other_reference'] : "";
		$buyer_order_no			= (isset($request['buyer_order_no']) && !empty($request['buyer_order_no'])) ? $request['buyer_order_no'] : "";
		$dated					= (isset($request['dated']) && !empty($request['dated'])) ? $request['dated'] : "";
		$dispatch_doc_no		= (isset($request['dispatch_doc_no']) && !empty($request['dispatch_doc_no'])) ? $request['dispatch_doc_no'] : "";
		$dispatch_address		= (isset($request['dispatch_address']) && !empty($request['dispatch_address'])) ? $request['dispatch_address'] : "";
		$invoice_date			= (isset($request['dispatch_date']) && !empty($request['dispatch_date'])) ? date("Y-m-d",strtotime($request['dispatch_date'])) : "";
		$vehicle_number			= (isset($request['vehicle_number']) && !empty($request['vehicle_number'])) ? $request['vehicle_number'] : "";
		$dispatched_through		= (isset($request['dispatched_through']) && !empty($request['dispatched_through'])) ? $request['dispatched_through'] : "";
		$destination			= (isset($request['destination']) && !empty($request['destination'])) ? $request['destination'] : "";
		$destination			= (isset($request['shipping_city']) && !empty($request['shipping_city'])) ? $request['shipping_city'] : "";
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
		$data 							= array();
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
		$data['created_by']				= Auth()->user()->adminuserid;
		$data['updated_by']				= Auth()->user()->adminuserid;
		$data['created_at']				= $invoice_date;
		$data['updated_at'] 			= date("Y-m-d H:i:s");
		$matchThese 					= ['dispatch_id'=>$DispatchId];
		self::updateOrCreate($matchThese,$data);
	}

	
}

