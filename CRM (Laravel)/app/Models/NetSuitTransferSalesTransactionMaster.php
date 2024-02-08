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
use App\Models\CompanyProductMaster;

use DB;
class NetSuitTransferSalesTransactionMaster extends Model implements Auditable
{
	//
	protected 	$table 		=	'net_suit_transfer_sales_transaction_master';
	protected 	$primaryKey =	'id'; // or null
	protected $guarded 		= ['id'];
	public 		$timestamps = 	false;
	use AuditableTrait;
	protected $casts = [

    ];

    /*
	Use 	: STORE TRANSFER SALES DATA
	Author 	: Axay Shah
	Date 	: 16 FEB 2022
    */
	public static function StoreTransferData(){
   		$invoiceDate = "2021-04-01 00:00:00";
   		$TO 		= date("Y-m-d")." 00:00:00";
   		$FROM 		= date('Y-m-d H:i:s', strtotime(INTERVAL_TIME));
   		$FROM 		= "2022-01-01 00:00:00";
   		$TO 		= date("Y-m-d H:i:s");
   		$SQL 		= "SELECT 
   		WTM.company_id as company_id,
		WTM.id as dispatch_id,
		WTP.id as sales_id,
		WTP.id as dispatch_product_master_id,
		WTM.challan_no as invoice_no,
		DATE_FORMAT(WTM.transfer_date, '%Y-%m-%d') as invoice_date,
		WTM.vehicle_id,
		V.vehicle_number as vehicle_no,
		DEPT.net_suit_client_name as vendor_name,	
		DEPT.net_suit_client_id as vendor_code,
		WTM.origin_mrf as mrf_id,	
		DEPT.department_name as mrf_name,	
		DEPT.net_suit_code as mrf_ns_id,
		WTP.product_type,
		'RECYCLABLE' as material_type,
		'' as other_reference,
		0 as client_po_id,
		'' as client_po_no,
		WTM.destination_mrf as client_id,
		DEPT1.net_suit_code as client_mrf_ns_id,
		DEPT1.department_name as client_mrf_name,
		DEPT1.net_suit_client_id as client_code,
		DEPT1.net_suit_client_name as client_name,
		WTP.product_id as product_id,
		'' as product_code,
		NULL AS product_class,
		NULL AS product_department,
		'' AS product_name,
		WTP.description AS product_description,
		'' AS hsn_code,
		WTP.quantity as sales_qty,
		'KGS' AS uom,
		WTP.price as product_rate,
		WTP.cgst as cgst_rate,
		WTP.sgst as sgst_rate,
		WTP.igst as igst_rate,
		WTP.cgst,
		WTP.sgst,
		WTP.igst,
		0 as tcs,
		0 as freight_amount,
		0 as discount_amount,
		0 as gross_amount,
		0 as total_tax_amt,
		0 as net_amount,
		1 as paid,
		WTM.eway_bill_no,
		WTM.transporter_name,
		'' as dispatch_from,
		'' as dispatch_to,
		'' as dispatch_doc_no,
		'' as delivery_term,
		WTM.irn AS e_invoice_no,
		WTM.ack_no,
		WTM.ack_date,
		now() as created_at,
		now() as updated_at
		FROM wm_transfer_product AS WTP
		INNER JOIN wm_transfer_master  AS WTM ON WTM.id = WTP.transfer_id
		INNER JOIN GST_STATE_CODES  AS GSC_ORG ON WTM.origin_state_code = GSC_ORG.id
		INNER JOIN GST_STATE_CODES  AS GSC_DESTI ON WTM.destination_state_code = GSC_DESTI.id
		INNER JOIN wm_department AS DEPT ON WTM.origin_mrf = DEPT.id
		INNER JOIN wm_department AS DEPT1 ON WTM.destination_mrf = DEPT1.id
		LEFT JOIN vehicle_master  AS V ON WTM.vehicle_id = V.vehicle_id
		WHERE GSC_ORG.display_state_code != GSC_DESTI.display_state_code 
		AND WTM.transfer_date BETWEEN '".$FROM."' AND '".$TO."'";	
		$SQLDATA = \DB::select($SQL);
		if(!empty($SQLDATA)){
			// prd($SQLDATA);
			foreach($SQLDATA AS $KEY => $VALUE){
				if($VALUE->product_type == PRODUCT_PURCHASE){
					$product =  CompanyProductMaster::select(
					 	\DB::raw("CONCAT(company_product_master.name,' ',cq.parameter_name) as product_name"),
					 	'company_product_master.hsn_code',
					 	'P_CLASS.para_value as product_class',
					 	'PD.para_value as product_department',
					 	'company_product_master.net_suit_code as product_code')
			        ->join('company_product_quality_parameter as cq','company_product_master.id','=','cq.product_id')
			        ->leftjoin('parameter as P_CLASS','company_product_master.net_suit_class','=','P_CLASS.para_id')
			        ->leftjoin('parameter as PD','company_product_master.net_suit_department','=','PD.para_id')
			        ->where('company_product_master.id',$VALUE->product_id)
			        ->first();
				}else{
					$product =  WmProductMaster::select(
					 	\DB::raw("wm_product_master.title as product_name"),
					 	'wm_product_master.hsn_code',
					 	'P_CLASS.para_value as product_class',
					 	'PD.para_value as product_department',
					 	'wm_product_master.net_suit_code as product_code')
			        ->leftjoin('parameter as P_CLASS','wm_product_master.net_suit_class','=','P_CLASS.para_id')
			        ->leftjoin('parameter as PD','wm_product_master.net_suit_department','=','PD.para_id')
			        ->where('wm_product_master.id',$VALUE->product_id)
			        ->first();
				}
				$product_name 	= (isset($product) && !empty($product)) ? $product->product_name : "";
				$hsn_code  		= (isset($product) && !empty($product)) ? $product->hsn_code : "";
				$product_class 	= (isset($product) && !empty($product)) ? $product->product_class : "";
				$product_department = (isset($product) && !empty($product)) ? $product->product_department : "";
				$product_code 	= (isset($product) && !empty($product)) ? $product->product_code : "";
				$GROSS_AMT 	= 0;
				$GST_AMT 	= 0;
				$NET_AMT 	= 0;
				$SGST_AMT 	= 0;
				$CGST_AMT 	= 0;
				$GROSS_AMT  = _FormatNumberV2($VALUE->sales_qty * $VALUE->product_rate);
				if(!empty($VALUE->igst_rate)){
					$GST_AMT 	= _FormatNumberV2(($GROSS_AMT * $VALUE->igst_rate) / 100);
				}else{
					$SGST_AMT 	= _FormatNumberV2(($GROSS_AMT * $VALUE->sgst_rate) / 100);
					$CGST_AMT 	= _FormatNumberV2(($GROSS_AMT * $VALUE->cgst_rate) / 100);
					$GST_AMT 	= _FormatNumberV2($SGST_AMT + $CGST_AMT);
				}
				$NET_AMT = _FormatNumberV2($GROSS_AMT + $GST_AMT);
				############ CREATE OR UPDATE ##########
				
				self::updateOrCreate([
					"dispatch_id"	=> $VALUE->dispatch_id,
					"sales_id"		=> $VALUE->sales_id,
				],
				[
					'invoice_no' 			=> $VALUE->invoice_no,
					'dispatch_id' 			=> $VALUE->dispatch_id,
					'sales_id' 				=> $VALUE->sales_id,
					'dispatch_product_master_id' => $VALUE->dispatch_product_master_id,
					'company_id' 			=> $VALUE->company_id,
					'vehicle_id' 			=> $VALUE->vehicle_id,
					'vehicle_no' 			=> $VALUE->vehicle_no,
					'vendor_code' 			=> $VALUE->vendor_code,
					'vendor_name' 			=> $VALUE->vendor_name,
					'invoice_date' 			=> $VALUE->invoice_date,
					'mrf_id' 				=> $VALUE->mrf_id,
					'mrf_name' 				=> $VALUE->mrf_name,
					'mrf_ns_id' 			=> $VALUE->mrf_ns_id,
					'material_type'			=> $VALUE->material_type,
					'other_reference' 		=> $VALUE->other_reference,
					'client_id' 			=> $VALUE->client_id,
					'client_mrf_ns_id' 		=> $VALUE->client_mrf_ns_id,
					'client_mrf_name' 		=> $VALUE->client_mrf_name,
					'client_code' 			=> $VALUE->client_code,
					'client_name' 			=> $VALUE->client_name,
					'client_po_no' 			=> $VALUE->client_po_no,
					'discount_amount' 		=> _FormatNumberV2($VALUE->discount_amount),
					'freight_amount' 		=> _FormatNumberV2($VALUE->freight_amount),
					'paid' 					=> $VALUE->paid,
					'tcs' 					=> $VALUE->tcs,
					'eway_bill_no' 			=> $VALUE->eway_bill_no,
					'transporter_name' 		=> $VALUE->transporter_name,
					'dispatch_from' 		=> $VALUE->dispatch_from,
					'dispatch_to' 			=> $VALUE->dispatch_to,
					'dispatch_doc_no' 		=> $VALUE->dispatch_doc_no,
					'delivery_term' 		=> $VALUE->delivery_term,
					'e_invoice_no' 			=> $VALUE->e_invoice_no,
					'ack_date' 				=> $VALUE->ack_date,
					'ack_no' 				=> $VALUE->ack_no,
					'product_id' 			=> $VALUE->product_id,
					'product_class' 		=> $product_class,
					'product_department' 	=> $product_department,
					'product_name' 			=> $product_name,
					'product_code' 			=> $product_code,
					'product_description' 	=> $VALUE->product_description,
					'hsn_code' 				=> $hsn_code,
					'uom' 					=> $VALUE->uom,
					'product_rate' 			=> $VALUE->product_rate,
					'sales_qty' 			=> $VALUE->sales_qty,
					'cgst_rate' 			=> $VALUE->cgst_rate,
					'sgst_rate' 			=> $VALUE->sgst_rate,
					'igst_rate' 			=> $VALUE->igst_rate,
					'cgst' 					=> $VALUE->cgst,
					'sgst' 					=> $VALUE->sgst,
					'igst' 					=> $VALUE->igst,
					'gross_amount' 			=> _FormatNumberV2($GROSS_AMT),
					'total_tax_amt' 		=> _FormatNumberV2($GST_AMT),
					'net_amount' 			=> _FormatNumberV2($NET_AMT),
					'created_at' 			=> $VALUE->created_at,
					'updated_at' 			=> $VALUE->updated_at,
				]);
			}
		}
	}
}
