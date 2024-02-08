<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceAdditionalCharges;
use App\Models\ClientChargesMaster;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class WmInvoicesCreditDebitNotesChargesDetails extends Model implements Auditable
{
    protected 	$table 		=	'wm_invoices_credit_debit_notes_charges_details';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	
	public static function GetCnDnChargeDetails($cn_dn_id){
		$CCMTBL 	= new ClientChargesMaster;
		$CCM 		= $CCMTBL->getTable();
		$self 		= (new static)->getTable();

		$data = WmInvoicesCreditDebitNotesChargesDetails::select(
			\DB::raw("(CASE WHEN $self.change_in = 0 THEN '-'
					WHEN $self.change_in = 1 THEN 'Rate'
					WHEN $self.change_in = 2 THEN 'Quantity'
					WHEN $self.change_in = 3 THEN 'Rate & Quantity'
			END ) AS change_in_name"),
			\DB::raw("$self.charge_id as product_id"),
			\DB::raw("'Y' as IsServc"),
			\DB::raw("$self.change_in"),
			\DB::raw("$self.rate"),
			\DB::raw("$self.revised_rate"),
			\DB::raw("$self.quantity"),
			\DB::raw("$self.revised_quantity"),
			\DB::raw("$self.cgst_rate"),
			\DB::raw("$self.sgst_rate"),
			\DB::raw("$self.igst_rate"),
			\DB::raw("$self.gst_amount"),
			\DB::raw("$self.net_amount"),
			\DB::raw("$self.revised_gst_amount"),
			\DB::raw("$self.revised_net_amount"),
			\DB::raw("$self.revised_gross_amount"),
			\DB::raw("$self.is_from_same_state"),
			\DB::raw("CCM.charge_name as product_name"),
			\DB::raw("CCM.hsn_code"),
			\DB::raw("CCM.charge_ns_code as net_suit_code")
		)->join($CCM." AS CCM","$self.charge_id","=","CCM.id")
		->where("cd_notes_id",$cn_dn_id)
		->get()
		->toArray();
		return $data;
		
	}
	
	/*
	Use     	: Get Invoice Additional Charges for e invoice
	Developer 	: Kalpak Prajapati
	Date 		: 24 Feb,2022
	*/
	public static function GetCnDnChargesProductDataForEInvoice($cn_dn_id=0)
	{
		$itemList 	= array();
		$data 		= self::select(
					"client_charges_master.id as charge_id",
					"client_charges_master.charge_name",
					"client_charges_master.hsn_code",
					"wm_invoices_credit_debit_notes_charges_details.*",
					\DB::raw("'Y' as IsServc")
					)
					->join("client_charges_master","client_charges_master.id","=","wm_invoices_credit_debit_notes_charges_details.charge_id");
		if(!empty($cn_dn_id)){
			$data->where("wm_invoices_credit_debit_notes_charges_details.cd_notes_id",$cn_dn_id);
		}
		
		$result 	= $data->get()->toArray();
		
		if (!empty($result)) {
			foreach($result as $key => $value){
				$itemList[$key]["productName"]      = $value['charge_name'];
				$itemList[$key]["productDesc"]      = "";
				$itemList[$key]["is_service"]      	= "Y";
				$itemList[$key]["IsServc"]      	= "Y";
				$itemList[$key]["hsnCode"]          = $value['hsn_code'];
				$itemList[$key]["quantity"]         = _FormatNumberV2($value['hsn_code']);
				$itemList[$key]["qtyUnit"]          = "KGS";
				$itemList[$key]["cgstRate"]     	= _FormatNumberV2($value['cgst_rate']);
				$itemList[$key]["sgstRate"]     	= _FormatNumberV2($value['sgst_rate']);
				$itemList[$key]["igstRate"]     	= _FormatNumberV2($value['igst_rate']);
				$itemList[$key]["price"]     		= ($value['cgst_rate'] > 0) ? $value['cgst_rate'] : $value['rate'];
				$SUM_GST_PERCENT 					= ($value['igst_rate'] > 0) ? $value['igst_rate'] : ($value['cgst_rate'] + $value['sgst_rate']);
				$CGST_AMT = 0;
				$SGST_AMT = 0;
				$IGST_AMT = 0;
				if($value['is_from_same_state'] == "Y"){
					$CGST_AMT  = ($value['cgst_rate'] > 0) ? ($value['revised_gross_amount'] * $value['cgst_rate']) / 100 :0;
					$SGST_AMT  = ($value['sgst_rate'] > 0) ? ($value['revised_gross_amount'] * $value['sgst_rate']) / 100 :0;
					
				}else{
					$IGST_AMT  = ($value['igst_rate'] > 0) ? ($value['revised_gross_amount'] * $value['igst_rate']) / 100 : 0;
				}
				$itemList[$key]["cgstAmt"]     		= $CGST_AMT;
				$itemList[$key]["sgstAmt"]     		= $SGST_AMT;
				$itemList[$key]["igstAmt"]     		= $IGST_AMT;
				$itemList[$key]["totalGstPercent"]  = $SUM_GST_PERCENT;
				$itemList[$key]["cessRate"]         = 0;
				$itemList[$key]["taxableAmount"]    = _FormatNumberV2($value['revised_gross_amount']);
				$itemList[$key]["totalItemAmount"]  = _FormatNumberV2($value['revised_net_amount']);
				$itemList[$key]["gstAmount"]        = _FormatNumberV2($value['revised_gst_amount']);
				$itemList[$key]["isFromSameState"]  = $value["is_from_same_state"];
			}
		}
		return $itemList;
	}
}
