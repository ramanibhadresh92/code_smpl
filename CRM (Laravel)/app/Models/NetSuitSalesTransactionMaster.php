<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseLocationMaster;
use App\Models\LocationMaster;
use App\Models\VehicleMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmDispatch;
use App\Models\WmDispatchProduct;
use App\Models\Parameter;
use App\Models\WmClientMaster;
use App\Models\WmProductMaster;
use App\Models\WmDepartment;
use App\Models\NetSuitApiLogMaster;
use App\Models\InvoiceAdditionalCharges;
use DB;
class NetSuitSalesTransactionMaster extends Model implements Auditable
{
	//
	protected 	$table 		=	'net_suit_sales_transaction_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [

    ];
   /*
   Use 		:  Store service and dispatch data for net suit
   Author 	: Axay Shah
   Date 	: 2022-01-01
   */
   	public static function StoreDispatchDataForNetSuit(){
   		$invoiceDate = "2022-01-01 00:00:00";
   		$FROM 		= date("Y-m-d")." 00:00:00";
   		// $TO 		= date('Y-m-d H:i:s', strtotime(INTERVAL_TIME));
   		$FROM 		= "2022-01-01 00:00:00";
   		$TO 		= date("Y-m-d")." 23:59:00";
   		$SQL 		= "REPLACE INTO net_suit_sales_transaction_master(
					dispatch_id,
					sales_id,
					dispatch_product_master_id,
					invoice_no,
					invoice_date,
					company_id,
					vehicle_id,
					vehicle_no,
					mrf_id,
					mrf_name,
					mrf_ns_id,
					material_type,
					other_reference,
					client_po_id,
					client_po_no,
					client_id,
					client_code,
					client_name,
					product_id,
					product_code,
					product_class,
					product_department,
					product_name,
					product_description,
					hsn_code,
					sales_qty,
					uom,
					product_rate,
					cgst_rate,
					sgst_rate,
					igst_rate,
					cgst,
					sgst,
					igst,
					tcs,
					freight_amount,
					discount_amount,
					gross_amount,
					total_tax_amt,
					net_amount,
					paid,
					eway_bill_no,
					transporter_name,
					dispatch_from,
					dispatch_to,
					dispatch_doc_no,
					delivery_term,
					e_invoice_no,
					ack_no,
					ack_date,
					created_at,
					updated_at
				)
					SELECT
					    wm_dispatch.id as dispatch_id,
						wm_sales_master.id as sales_id,
						wm_dispatch_product.id as dispatch_product_master_id,
						wm_dispatch.challan_no as invoice_no,
						wm_dispatch.dispatch_date as invoice_date,
						wm_dispatch.company_id,
						V.vehicle_id,
						V.vehicle_number,
						wm_dispatch.bill_from_mrf_id,
						BILL.department_name,
						BILL.net_suit_code,
						(CASE
							WHEN wm_dispatch.dispatch_type = 1032001 THEN 'RECYCLABLE'
							WHEN wm_dispatch.dispatch_type = 1032002 THEN 'NON RECYCLABLE'
							ELSE ''
						END ) AS material_type,
						'' AS other_reference,
						'' as client_po_id,
						'' AS client_po_no,
						wm_dispatch.client_master_id as client_id,
						wm_client_master.net_suit_code as client_code,
						wm_client_master.client_name,
						wm_product_master.id as product_id,
						wm_product_master.net_suit_code as product_code,
						P1.para_value as product_class,
						P2.para_value as product_department,
						wm_product_master.title as product_name,
						wm_dispatch_product.description as product_description,
						wm_product_master.hsn_code,
						wm_sales_master.quantity as sales_qty,
						'KGS' as uom,
						wm_sales_master.rate as product_rate,
						IF(ORI_GST.display_state_code = DES_GST.display_state_code,wm_product_master.cgst,0) as cgst_rate,
						IF(ORI_GST.display_state_code = DES_GST.display_state_code,wm_product_master.sgst,0) as sgst_rate,
						IF(ORI_GST.display_state_code <> DES_GST.display_state_code,wm_product_master.igst,0) as igst_rate,
						0 as cgst,
						0 as sgst,
						0 as igst,
						wm_dispatch.tcs_amount as tcs,
						wm_dispatch.total_rent_amt as freight_amount,
						wm_dispatch.discount_amt as discount_amount,
						wm_sales_master.gross_amount,
						wm_sales_master.gst_amount as total_tax_amt,
						wm_sales_master.net_amount as net_amount,
						(CASE
							WHEN wm_dispatch.dispatch_type = 1032001 THEN '1'
							WHEN wm_dispatch.dispatch_type = 1032002 THEN '0'
							ELSE ''
						END ) AS paid,
						wm_dispatch.eway_bill_no as eway_bill_no,
						wm_dispatch.transporter_name as transporter_name,
						'' as dispatch_from,
						'' as dispatch_to,
						'' as dispatch_doc_no,
						'' as delivery_term,
						wm_dispatch.e_invoice_no as e_invoice_no,
						wm_dispatch.acknowledgement_no as ack_no,
						wm_dispatch.acknowledgement_date as ack_date,
						now() as created_at,
						now() as updated_at
						FROM wm_dispatch
						left join wm_dispatch_product on wm_dispatch.id = wm_dispatch_product.dispatch_id
						left join wm_sales_master on wm_dispatch_product.id = wm_sales_master.dispatch_product_id and wm_dispatch.id = wm_sales_master.dispatch_id
						left join parameter as PARAM on wm_dispatch.type_of_transaction = PARAM.para_id

						inner join wm_client_master on wm_client_master.id = wm_dispatch.client_master_id
						left join vehicle_master as V on wm_dispatch.vehicle_id = V.vehicle_id
						left join wm_product_master on wm_dispatch_product.product_id = wm_product_master.id
						LEFT JOIN parameter P1 ON wm_product_master.net_suit_class = P1.para_id
						LEFT JOIN parameter P2 ON wm_product_master.net_suit_department = P2.para_id
						left join wm_department on wm_dispatch.master_dept_id = wm_department.id
						left join wm_department AS BILL on wm_dispatch.bill_from_mrf_id = BILL.id
						left join GST_STATE_CODES as ORI_GST on wm_dispatch.origin_state_code = ORI_GST.state_code
						left join GST_STATE_CODES as DES_GST on wm_dispatch.destination_state_code = DES_GST.state_code
						where wm_dispatch.approval_status IN ('1','2') AND wm_dispatch.updated_at BETWEEN '".$FROM."' and '".$TO."'
						AND  wm_dispatch.dispatch_date >= '".$invoiceDate."'";
						$SQL_DATA = DB::statement($SQL);
	}

   /*
	Use 	: Send Dispatch Data to Net Suit
	Author 	: Axay Shah
	Date 	: 08 April 2021
    */
 	public static function SendSalesTransactionDataToNetSuit($date=""){
 		return false;
		$date = (!empty($date)) ? date("Y-m-d H:i:s",strtotime($date)) : "";
		if(!empty($date)){
			$data = self::where("updated_at",">=",$date)->get()->toArray();
		}
		return $data;
   	}
   	/*
	Use 	: Send Dispatch Data to Net Suit
	Author 	: Axay Shah
	Date 	: 08 April 2021
    */
 	public static function SendDispatchDataToNetSuit($request){
 		// return false;
		$ID 		= NetSuitApiLogMaster::AddRequest($request->all(),"SALES_TRANSACTION",1);
		$date 		= (isset($request->date) && !empty($request->date)) ? date("Y-m-d H:i:s",strtotime($request->date)) : "2022-01-01 00:00:00";
		$record_id 	= (isset($request->record_id) && !empty($request->record_id)) ?  $request->record_id : "";
		$flag 		= false;
		$FROM 		= "2022-01-01";
		$FROM 		= date('Y-m-d',strtotime("-5 days"));
		$TO 		= date("Y-m-d");
		if(!empty($date)){
				$data = "( SELECT  
							'DISPATCH' AS transaction_for,
						    dispatch_id,
						    invoice_no,
						    invoice_date,
						    vehicle_no,
						    mrf_name,
						    mrf_ns_id,
						    material_type,
						    other_reference,
						    client_po_no,
						    client_code,
						    client_name,
						    product_code,
						    product_class,
						    product_department,
						    product_name,
						    product_description,
						    hsn_code,
						    sales_qty,
						    uom,
						    product_rate,
						    cgst_rate,
						    sgst_rate,
						    igst_rate,
						    cgst,
						    sgst,
						    igst,
						    tcs,
						    freight_amount,
						    discount_amount,
						    gross_amount,
						    total_tax_amt,
						    net_amount,
						    paid,
						    eway_bill_no,
						    transporter_name,
						    dispatch_from,
						    dispatch_to,
						    dispatch_doc_no,
						    delivery_term,
						    e_invoice_no,
						    ack_no,
						    ack_date,
						    created_at,
						    updated_at
							FROM net_suit_sales_transaction_master
							where invoice_date >= '".$FROM."' and invoice_date <= '".$TO."'
							OR invoice_no in (5101983)

							) 
						UNION ALL  
						( 
							SELECT
							   'SERVICE' AS transaction_for,
								WSM.service_id as  dispatch_id,
							    WSM.serial_no as invoice_no,
							    WSM.invoice_date as invoice_date,
							    WSM.dispatch_through as vehicle_no,
							    WSM.mrf as mrf_name,
							    WSM.mrf_ns_id,
							    'SERVICE' AS material_type,
							    WSM.remarks AS other_reference, 
							    WSM.buyer_no AS client_po_no,
							    WSM.client_ns_code AS client_code,
							    WSM.client_name,
							    WSPM.product_ns_code AS product_code,
							    WSPM.product_class,
						    	WSPM.product_department,
							    WSPM.product as product_name,
							    WSPM.description as product_description,
							    WSPM.hsn_code as hsn_code,
							    WSPM.quantity as sales_qty,
							    WSPM.uom,
							    WSPM.rate as product_rate,
							    WSPM.sgst as sgst_rate,
							    WSPM.cgst as cgst_rate,
							    WSPM.igst as igst_rate,
								WSPM.sgst,
							    WSPM.cgst,
							    WSPM.igst,
							    0 as tcs,
							    0 as freight_amount,
							    0 as discount_amount,
							    WSPM.gross_amt as gross_amount,
							    WSPM.gst_amt as total_tax_amt,
							    WSPM.net_amt as net_amount,
							    0 as paid,
							    '' as eway_bill_no,
							    '' as transporter_name,
							    '' as dispatch_from,
							    '' as dispatch_to,
							    WSM.dispatch_doc_no,
							    WSM.terms_payment as delivery_term,
							    WSM.irn as e_invoice_no,
							    WSM.ack_no,
							    WSM.ack_date as ack_date,
							    WSM.created_at,
							    WSM.updated_at
							
							FROM net_suit_wm_service_master AS WSM
							INNER JOIN net_suit_wm_service_product_mapping  AS WSPM ON WSM.service_id = WSPM.service_id
							WHERE WSM.invoice_date >= '".$FROM."' and WSM.invoice_date <= '".$TO."')

							UNION ALL 
							( 
								SELECT  
								'TRANSFER' AS transaction_for,
							    dispatch_id,
							    invoice_no,
							    invoice_date,
							    vehicle_no,
							    mrf_name,
							    mrf_ns_id,
							    material_type,
							    other_reference,
							    client_po_no,
							    client_code,
							    client_name,
							    product_code,
							    product_class,
							    product_department,
							    product_name,
							    product_description,
							    hsn_code,
							    sales_qty,
							    uom,
							    product_rate,
							    cgst_rate,
							    sgst_rate,
							    igst_rate,
							    cgst,
							    sgst,
							    igst,
							    tcs,
							    freight_amount,
							    discount_amount,
							    gross_amount,
							    total_tax_amt,
							    net_amount,
							    paid,
							    eway_bill_no,
							    transporter_name,
							    dispatch_from,
							    dispatch_to,
							    dispatch_doc_no,
							    delivery_term,
							    e_invoice_no,
							    ack_no,
							    ack_date,
							    created_at,
							    updated_at
								FROM net_suit_transfer_sales_transaction_master 
								where  invoice_date >= '".$FROM."' and invoice_date <= '".$TO."'
							 OR invoice_no in (3600281))";
						
				$res 	= \DB::select($data);

				$result = array();
				
				$i = 0;
				if(!empty($res)){
					$priviousDispatchID = 0;
					foreach($res as $key => $value){
						$value = (array) $value;
						if($priviousDispatchID == $value['dispatch_id']){
							$priviousGrossAmount 	+= $value['gross_amount'];
							$priviousGstAmount 		+= $value['total_tax_amt'];
							$priviousNetAmount 		+= $value['net_amount'];
						}else{
							$priviousGrossAmount 	= $value['gross_amount'];
							$priviousGstAmount 		= $value['total_tax_amt'];
							$priviousNetAmount 		= $value['net_amount'];
							$priviousDispatchID 	= $value['dispatch_id'];
						}
						$product_array 										= array();
						$TRANSACTION_FOR 									= $value['transaction_for'];
						$result[$value["dispatch_id"]]['invoice_no'] 		= $value["invoice_no"];
						$result[$value["dispatch_id"]]['transaction_for'] 	= $value["transaction_for"];
						$result[$value["dispatch_id"]]['record_id'] 		= $value["dispatch_id"];
						$result[$value["dispatch_id"]]['invoice_date'] 		= $value["invoice_date"];
						$result[$value["dispatch_id"]]['mrf_name'] 			= $value["mrf_name"];
						$result[$value["dispatch_id"]]['mrf_ns_id'] 		= $value["mrf_ns_id"];
						$result[$value["dispatch_id"]]['material_type']	 	= $value["material_type"];
						$result[$value["dispatch_id"]]['other_reference'] 	= $value["other_reference"];
						$result[$value["dispatch_id"]]['client_code'] 		= $value["client_code"];
						$result[$value["dispatch_id"]]['client_name'] 		= $value["client_name"];
						$result[$value["dispatch_id"]]['client_po_no'] 		= $value["client_po_no"];
						$result[$value["dispatch_id"]]['discount_amount'] 	= _FormatNumberV2($value["discount_amount"]);
						$result[$value["dispatch_id"]]['freight_amount'] 	= _FormatNumberV2($value["freight_amount"]);
						$result[$value["dispatch_id"]]['gross_amount'] 		= _FormatNumberV2($priviousGrossAmount);
						$result[$value["dispatch_id"]]['total_tax_amt'] 	= _FormatNumberV2($priviousGstAmount);
						$result[$value["dispatch_id"]]['net_amount'] 		= _FormatNumberV2($priviousNetAmount);
						$result[$value["dispatch_id"]]['paid'] 				= $value["paid"];
						$result[$value["dispatch_id"]]['tcs'] 				= $value["tcs"];
						$result[$value["dispatch_id"]]['eway_bill_no'] 		= $value["eway_bill_no"];
						$result[$value["dispatch_id"]]['transporter_name'] 	= $value["transporter_name"];
						$result[$value["dispatch_id"]]['dispatch_from'] 	= $value["dispatch_from"];
						$result[$value["dispatch_id"]]['dispatch_to'] 		= $value["dispatch_to"];
						$result[$value["dispatch_id"]]['dispatch_doc_no'] 	= $value["dispatch_doc_no"];
						$result[$value["dispatch_id"]]['delivery_term'] 	= $value["delivery_term"];
						$result[$value["dispatch_id"]]['e_invoice_no'] 		= $value["e_invoice_no"];
						$result[$value["dispatch_id"]]['ack_date'] 			= $value["ack_date"];
						$result[$value["dispatch_id"]]['ack_no'] 			= $value["ack_no"];
						$result[$value["dispatch_id"]]['created_at'] 		= $value["created_at"];
						$result[$value["dispatch_id"]]['updated_at'] 		= $value["updated_at"];
						$result[$value["dispatch_id"]]['class'] 			= $value['product_class'];
						$result[$value["dispatch_id"]]['dept'] 				= $value['product_department'];
						$product_array['product_name'] 						= $value['product_name'];
						$product_array['product_code'] 						= $value['product_code'];
						$product_array['product_description'] 				= $value['product_description'];
						$product_array['hsn_code'] 							= $value['hsn_code'];
						$product_array['uom'] 								= $value['uom'];
						$product_array["product_rate"] 						= $value["product_rate"];
						$product_array["sales_qty"] 						= $value["sales_qty"];
						$product_array["cgst_rate"] 						= $value["cgst_rate"];
						$product_array["sgst_rate"] 						= $value["sgst_rate"];
						$product_array["igst_rate"] 						= $value["igst_rate"];
						$product_array["cgst"] 								= $value["cgst"];
						$product_array["sgst"] 								= $value["sgst"];
						$product_array["igst"] 								= $value["igst"];
						$product_array['gross_amount'] 						= $value["gross_amount"];
						$product_array['tax_amount'] 						= $value["total_tax_amt"];
						$product_array['net_amount'] 						= $value["net_amount"];
						$result[$value["dispatch_id"]]["item_list"][] 		= $product_array;
					}
				}
				$response = array();
				if(!empty($result)){
					$i = 0;
					foreach($result as $key => $value){
						$ADDTIONAL_CHARGES = InvoiceAdditionalCharges::GetAddtionalChargesData($value['record_id']);
						if(!empty($ADDTIONAL_CHARGES)){
							foreach($ADDTIONAL_CHARGES as $RAW => $RAW_VAL){
								$product_array['product_name'] 			= $RAW_VAL['product_name'];
								$product_array['product_code'] 			= $RAW_VAL['product_code'];
								$product_array['product_description'] 	= $RAW_VAL['product_description'];
								$product_array['hsn_code'] 				= $RAW_VAL['hsn_code'];
								$product_array['uom'] 					= $RAW_VAL['uom'];
								$product_array["product_rate"] 			= $RAW_VAL["product_rate"];
								$product_array["sales_qty"] 			= $RAW_VAL["sales_qty"];
								$product_array["cgst_rate"] 			= $RAW_VAL["cgst_rate"];
								$product_array["sgst_rate"] 			= $RAW_VAL["sgst_rate"];
								$product_array["igst_rate"] 			= $RAW_VAL["igst_rate"];
								$product_array["cgst"] 					= $RAW_VAL["cgst"];
								$product_array["sgst"] 					= $RAW_VAL["sgst"];
								$product_array["igst"] 					= $RAW_VAL["igst"];
								$product_array['gross_amount'] 			= $RAW_VAL["gross_amount"];
								$product_array['tax_amount'] 			= $RAW_VAL["tax_amount"];
								$product_array['net_amount'] 			= $RAW_VAL["net_amount"];
								$result[$key]['item_list'][] 			= $product_array;
							}
						}
						$result[$key]["net_amount"] =  _FormatNumberV2(round($result[$key]["net_amount"]));
						$response[$i] = $result[$key];
						$i++;
					}
				}
		}
		NetSuitApiLogMaster::UpdateRequest($ID,json_encode($response));
		
		return $response;
   	}
   	
   	/*
	Use 	: Export Net suit Sales Product Master
	Author  : Hasmukhi Patel
	Date 	: 30 Sept 2021
   	*/
   	public static function ExportSalesProductMaster($request){
   		$start_date = (isset($request->start_date) && (!empty($request->start_date)) ? date("Y-m-d",strtotime($request->start_date)) : date('Y-m-d'));
   		$end_date 	= (isset($request->end_date) && (!empty($request->end_date)) ?  date("Y-m-d",strtotime($request->end_date)) : date('Y-m-d'));
   		$data 		= self::whereBetween('invoice_date', [$start_date, $end_date])
   							->get()->toArray();
   						//LiveServices::toSqlWithBinding($data);die;
   		return $data;
   	}
}
