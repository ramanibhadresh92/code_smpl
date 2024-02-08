<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use App\Models\ClientChargesMapping;
use App\Models\WmDispatch;
class InvoiceAdditionalCharges extends Model
{
	protected 	$table 		= 'wm_invoice_additional_charges';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;
	protected 	$casts 		= [];

	/*
	Use     	: Save Invoice Additional Charges
	Developer 	: Kalpak Prajapati
	Date 		: 17 Feb,2022
	*/
	public static function SaveInvoiceAdditionalCharges($DispatchId=0,$InvoiceId=0,$client_master_id=0,$TotalQty=0)
	{
		$ClientChargesMapping = ClientChargesMapping::select("client_charges_mapping.charge_id","client_charges_mapping.rate","client_charges_master.cgst",
															"client_charges_master.sgst","client_charges_master.igst")
								->leftjoin("client_charges_master","client_charges_master.id","=","client_charges_mapping.charge_id")
								->where("client_charges_mapping.client_id",$client_master_id)
								->where("client_charges_mapping.charge_id",">",0)
								->get();
		if (!empty($ClientChargesMapping))
		{
			$WmDispatch 	= WmDispatch::select("wm_department.gst_state_code_id as bill_from_gst_state_id","wm_dispatch.destination_state_code")
								->leftjoin("wm_department","wm_department.id","=","wm_dispatch.bill_from_mrf_id")
								->where("wm_dispatch.id",$DispatchId)
								->first();
			if (!empty($WmDispatch))
			{
				$IsFromSameState = true;
				if (isset($WmDispatch->bill_from_gst_state_id) && $WmDispatch->bill_from_gst_state_id != $WmDispatch->destination_state_code) {
					$IsFromSameState = false;
				}
			}
			foreach($ClientChargesMapping as $ClientCharges)
			{
				$InvoiceAdditionalCharges 						= new self;
				$InvoiceAdditionalCharges->dispatch_id 			= $DispatchId;
				$InvoiceAdditionalCharges->invoice_id 			= $InvoiceId;
				$InvoiceAdditionalCharges->client_charges_id 	= $ClientCharges->charge_id;
				$InvoiceAdditionalCharges->sgst 				= $ClientCharges->sgst;
				$InvoiceAdditionalCharges->cgst 				= $ClientCharges->cgst;
				$InvoiceAdditionalCharges->igst 				= $ClientCharges->igst;
				$InvoiceAdditionalCharges->rate 				= $ClientCharges->rate;
				$InvoiceAdditionalCharges->totalqty 			= $TotalQty;
				$GROSS_AMOUNT 									= ($ClientCharges->rate * $TotalQty);
				$GST_AMOUNT 									= 0;
				$InvoiceAdditionalCharges->sgst_amount 			= 0;
				$InvoiceAdditionalCharges->cgst_amount 			= 0;
				$InvoiceAdditionalCharges->igst_amount 			= 0;
				if ($IsFromSameState && $GROSS_AMOUNT > 0) {
					if ($ClientCharges->sgst > 0) {
						$SGST_AMOUNT 							= _FormatNumberV2(($GROSS_AMOUNT * $ClientCharges->sgst) / 100);
						$InvoiceAdditionalCharges->sgst_amount 	= _FormatNumberV2($SGST_AMOUNT);
						$GST_AMOUNT += $SGST_AMOUNT;
					}
					if ($ClientCharges->cgst > 0) {
						$CGST_AMOUNT 							= _FormatNumberV2(($GROSS_AMOUNT * $ClientCharges->cgst) / 100);
						$InvoiceAdditionalCharges->cgst_amount 	= _FormatNumberV2($CGST_AMOUNT);
						$GST_AMOUNT += $CGST_AMOUNT;
					}
				} else if (!$IsFromSameState && $GROSS_AMOUNT > 0) {
					if ($ClientCharges->igst > 0) {
						$IGST_AMOUNT 							= _FormatNumberV2(($GROSS_AMOUNT * $ClientCharges->igst) / 100);
						$InvoiceAdditionalCharges->igst_amount 	= _FormatNumberV2($IGST_AMOUNT);
						$GST_AMOUNT += $IGST_AMOUNT;
					}
				}
				$InvoiceAdditionalCharges->gross_amount = _FormatNumberV2($GROSS_AMOUNT);
				$InvoiceAdditionalCharges->gst_amount 	= _FormatNumberV2($GST_AMOUNT);
				$InvoiceAdditionalCharges->net_amount 	= _FormatNumberV2($GROSS_AMOUNT+$GST_AMOUNT);
				$InvoiceAdditionalCharges->created_at 	= date("Y-m-d H:i:s");
				$InvoiceAdditionalCharges->updated_at 	= date("Y-m-d H:i:s");
				$InvoiceAdditionalCharges->save();
			}
		}
	}

	/*
	Use     	: Get Invoice Additional Charges
	Developer 	: Kalpak Prajapati
	Date 		: 17 Feb,2022
	*/
	public static function GetInvoiceAdditionalCharges($dispatch_id=0,$invoice_id=0)
	{
		$ClientChargesMapping = array();
		if (!empty($dispatch_id)) {
			$ClientChargesMapping = self::select("client_charges_master.id as charge_id","client_charges_master.charge_name","client_charges_master.hsn_code","wm_invoice_additional_charges.rate",
											"wm_invoice_additional_charges.totalqty",
											"wm_invoice_additional_charges.cgst","wm_invoice_additional_charges.sgst","wm_invoice_additional_charges.igst",
											"wm_invoice_additional_charges.cgst_amount","wm_invoice_additional_charges.sgst_amount","wm_invoice_additional_charges.igst_amount",
											"wm_invoice_additional_charges.net_amount","wm_invoice_additional_charges.gst_amount","wm_invoice_additional_charges.gross_amount")
									->leftjoin("client_charges_master","client_charges_master.id","=","wm_invoice_additional_charges.client_charges_id")
									->where("wm_invoice_additional_charges.dispatch_id",$dispatch_id)
									->where("wm_invoice_additional_charges.client_charges_id",">",0)
									->get()
									->toArray();
		} else if (!empty($invoice_id)) {
			$ClientChargesMapping = self::select("client_charges_master.id as charge_id","client_charges_master.charge_name","client_charges_master.hsn_code",
												"wm_invoice_additional_charges.rate","wm_invoice_additional_charges.totalqty",
												"wm_invoice_additional_charges.cgst","wm_invoice_additional_charges.sgst","wm_invoice_additional_charges.igst",
												"wm_invoice_additional_charges.cgst_amount","wm_invoice_additional_charges.sgst_amount","wm_invoice_additional_charges.igst_amount",
												"wm_invoice_additional_charges.net_amount","wm_invoice_additional_charges.gst_amount","wm_invoice_additional_charges.gross_amount")
									->leftjoin("client_charges_master","client_charges_master.id","=","wm_invoice_additional_charges.client_charges_id")
									->where("wm_invoice_additional_charges.invoice_id",$invoice_id)
									->where("wm_invoice_additional_charges.client_charges_id",">",0)
									->get()
									->toArray();
		}
		return $ClientChargesMapping;
	}
	/*
	Use     	: Get Invoice Additional Charges for e invoice
	Developer 	: Kalpak Prajapati
	Date 		: 24 Feb,2022
	*/
	public static function GetProductDataForEInvoice($dispatch_id=0,$invoice_id=0)
	{
		$itemList 	= array();
		$data 		= self::select(
					"client_charges_master.id as charge_id",
					"client_charges_master.charge_name",
					"client_charges_master.hsn_code",
					"wm_invoice_additional_charges.*"
					)
					->join("client_charges_master","client_charges_master.id","=","wm_invoice_additional_charges.client_charges_id");
		if(!empty($dispatch_id)){
			$data->where("wm_invoice_additional_charges.dispatch_id",$dispatch_id);
		}else{
			$data->where("wm_invoice_additional_charges.invoice_id",$invoice_id);
		}
		
		$result 	= $data->get()->toArray();
		
		if (!empty($result)) {
			foreach($result as $key => $value){
				$itemList[$key]["productName"]      = $value['charge_name'];
				$itemList[$key]["productDesc"]      = "";
				$itemList[$key]["is_service"]      	= "Y";
				$itemList[$key]["hsnCode"]          = $value['hsn_code'];
				$itemList[$key]["quantity"]         = _FormatNumberV2($value['totalqty']);
				$itemList[$key]["qtyUnit"]          = "KGS";
				$itemList[$key]["cgstRate"]     	= _FormatNumberV2($value['cgst']);
				$itemList[$key]["sgstRate"]     	= _FormatNumberV2($value['sgst']);
				$itemList[$key]["igstRate"]     	= _FormatNumberV2($value['igst']);
				$itemList[$key]["price"]     		= $value['rate'];
				$SUM_GST_PERCENT 					= ($value['igst'] > 0) ? $value['igst'] : ($value['sgst'] + $value['cgst']);
				$itemList[$key]["cgstAmt"]     		= $value['cgst_amount'];
				$itemList[$key]["sgstAmt"]     		= $value['sgst_amount'];
				$itemList[$key]["igstAmt"]     		= $value['igst_amount'];
				$itemList[$key]["totalGstPercent"]  = $SUM_GST_PERCENT;
				$itemList[$key]["cessRate"]         = 0;
				$itemList[$key]["taxableAmount"]    = _FormatNumberV2($value['gross_amount']);
				$itemList[$key]["totalItemAmount"]  = _FormatNumberV2($value['net_amount']);
				$itemList[$key]["gstAmount"]        = _FormatNumberV2($value['gst_amount']);
			}
		}
		return $itemList;
	}
	/*
	Use     	: Get Invoice Additional Charges for e invoice
	Developer 	: Kalpak Prajapati
	Date 		: 24 Feb,2022
	*/
	public static function GetAddtionalChargesData($dispatch_id=0,$invoice_id=0)
	{
		$itemList 	= array();
		$data 		= self::select(
					"client_charges_master.id as charge_id",
					"client_charges_master.charge_name",
					"client_charges_master.hsn_code",
					"client_charges_master.charge_ns_code",
					"wm_invoice_additional_charges.*"
					)
					->join("client_charges_master","client_charges_master.id","=","wm_invoice_additional_charges.client_charges_id");
		if(!empty($dispatch_id)){
			$data->where("wm_invoice_additional_charges.dispatch_id",$dispatch_id);
		}else{
			$data->where("wm_invoice_additional_charges.invoice_id",$invoice_id);
		}
		$result 	= $data->get()->toArray();
		if (!empty($result)) {
			foreach($result as $key => $value){
				$itemList[$key]["product_name"]      = $value['charge_name'];
				$itemList[$key]["product_description"]  = "";
				$itemList[$key]["product_code"]     = $value['charge_ns_code'];
				$itemList[$key]["is_service"]      	= "Y";
				$itemList[$key]["hsn_code"]         = $value['hsn_code'];
				$itemList[$key]["sales_qty"]        = _FormatNumberV2($value['totalqty']);
				$itemList[$key]["uom"]          	= "KGS";
				$itemList[$key]["cgst_rate"]     	= _FormatNumberV2($value['cgst_amount']);
				$itemList[$key]["sgst_rate"]     	= _FormatNumberV2($value['sgst_amount']);
				$itemList[$key]["igst_rate"]     	= _FormatNumberV2($value['igst_amount']);
				$itemList[$key]["product_rate"]     = $value['rate'];
				$SUM_GST_PERCENT 					= ($value['igst'] > 0) ? $value['igst'] : ($value['sgst'] + $value['cgst']);
				$itemList[$key]["cgst"]     		= $value['cgst'];
				$itemList[$key]["sgst"]     		= $value['sgst'];
				$itemList[$key]["igst"]     		= $value['igst'];
				$itemList[$key]["totalGstPercent"]  = $SUM_GST_PERCENT;
				$itemList[$key]["cessRate"]         = 0;
				$itemList[$key]["gross_amount"]    	= _FormatNumberV2($value['gross_amount']);
				$itemList[$key]["net_amount"]  		= _FormatNumberV2($value['net_amount']);
				$itemList[$key]["tax_amount"]       = _FormatNumberV2($value['gst_amount']);
			}
		}
		return $itemList;
	}

	/*
	Use     	: Calculate Invoice Additional Charges
	Developer 	: Kalpak Prajapati
	Date 		: 13 March 2023
	*/
	public static function CalculateInvoiceAdditionalCharges($client_master_id=0,$TotalQty=0,$IsFromSameState=false)
	{
		$InvoiceAdditionalCharges = 0;
		$ClientChargesMapping = ClientChargesMapping::select("client_charges_mapping.charge_id","client_charges_mapping.rate","client_charges_master.cgst",
															"client_charges_master.sgst","client_charges_master.igst")
								->leftjoin("client_charges_master","client_charges_master.id","=","client_charges_mapping.charge_id")
								->where("client_charges_mapping.client_id",$client_master_id)
								->where("client_charges_mapping.charge_id",">",0)
								->get();
		if (!empty($ClientChargesMapping))
		{
			foreach($ClientChargesMapping as $ClientCharges)
			{
				$GROSS_AMOUNT 	= ($ClientCharges->rate * $TotalQty);
				$GST_AMOUNT 	= 0;
				if ($IsFromSameState && $GROSS_AMOUNT > 0) {
					if ($ClientCharges->sgst > 0) {
						$SGST_AMOUNT 							= _FormatNumberV2(($GROSS_AMOUNT * $ClientCharges->sgst) / 100);
						// $InvoiceAdditionalCharges->sgst_amount 	= _FormatNumberV2($SGST_AMOUNT);
						$GST_AMOUNT += $SGST_AMOUNT;
					}
					if ($ClientCharges->cgst > 0) {
						$CGST_AMOUNT 							= _FormatNumberV2(($GROSS_AMOUNT * $ClientCharges->cgst) / 100);
						// $InvoiceAdditionalCharges->cgst_amount 	= _FormatNumberV2($CGST_AMOUNT);
						$GST_AMOUNT += $CGST_AMOUNT;
					}
				} else if (!$IsFromSameState && $GROSS_AMOUNT > 0) {
					if ($ClientCharges->igst > 0) {
						$IGST_AMOUNT 							= _FormatNumberV2(($GROSS_AMOUNT * $ClientCharges->igst) / 100);
						// $InvoiceAdditionalCharges->igst_amount 	= _FormatNumberV2($IGST_AMOUNT);
						$GST_AMOUNT += $IGST_AMOUNT;
					}
				}
				$InvoiceAdditionalCharges += _FormatNumberV2($GROSS_AMOUNT+$GST_AMOUNT);
			}
		}
		return $InvoiceAdditionalCharges;
	}

	/*
	Use     	: Get Invoice Fright Charges for e invoice
	Developer 	: Axay Shah
	Date 		: 14 April,2023
	*/
	public static function GetFreightDataForEInvoice($dispatch_id=0)
	{
		$itemList 	= array();
		$value 		= WmDispatch::where("id",$dispatch_id)
					->first()->toArray();
		if(!empty($value)) {
				$itemList["productName"]  = "Freight";
				$itemList["productDesc"]  = "";
				$itemList["is_service"]   = "Y";
				$itemList["hsnCode"]      = FREIGHT_SAC_CODE;
				$itemList["quantity"]     = 1;
				$itemList["qtyUnit"]      = "NOS";
				$itemList["cgstRate"]     = _FormatNumberV2($value['rent_cgst']);
				$itemList["sgstRate"]     = _FormatNumberV2($value['rent_sgst']);
				$itemList["igstRate"]     = _FormatNumberV2($value['rent_igst']);
				$itemList["price"]     	  = $value['rent_amt'];
				$SUM_GST_PERCENT 			= ($value['rent_igst'] > 0) ? $value['rent_igst'] : ($value['rent_sgst'] + $value['rent_cgst']);
				$itemList["cgstAmt"]     	= ($value['rent_cgst'] > 0) ? _FormatNumberV2($value['rent_gst_amt']) / 2 : 0;
				$itemList["sgstAmt"]     	= ($value['rent_sgst'] > 0) ? _FormatNumberV2($value['rent_gst_amt']) / 2 : 0;
				$itemList["igstAmt"]     	= ($value['rent_igst'] > 0) ? $value['rent_gst_amt'] : 0;
				$itemList["totalGstPercent"] = $SUM_GST_PERCENT;
				$itemList["cessRate"]        = 0;
				$itemList["taxableAmount"]    = _FormatNumberV2($value['rent_amt']);
				$itemList["totalItemAmount"]  	 = _FormatNumberV2($value['rent_amt'] + $value['rent_gst_amt']);
				$itemList["gstAmount"]      = _FormatNumberV2($value['rent_gst_amt']);
		}
		return $itemList;
	}

}