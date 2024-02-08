<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB; 
use App\Models\WmInvoicesCreditDebitNotesChargesDetails;
use App\Facades\LiveServices;
class AccountReport extends Model
{
	
	/*
	Use 	: B2B Report for Account
	Author 	: Axay Shah
	Date 	: 25 May 2022
	*/
	public static function B2BAccountReport($req){
		IF(Auth()->user()->adminuserid == 1){
			return self::B2BAccountReportV2($req);
		}
		$START_DATE = (isset($req['startDate']) && !empty($req['startDate'])) ? date("Y-m-d",strtotime($req['startDate']))." ".GLOBAL_START_TIME : "01-".date("m-d")." ".GLOBAL_START_TIME;
		$END_DATE 	= (isset($req['endDate']) && !empty($req['endDate'])) ? date("Y-m-d",strtotime($req['endDate']))." ".GLOBAL_END_TIME : date("Y-m-t")." ".GLOBAL_END_TIME;
		$MRF_ID 	= (isset($req['mrf_id']) && !empty($req['mrf_id'])) ? $req['mrf_id'] : '';
		$WITH_TAX 	= (isset($req['with_tax']) && !empty($req['with_tax'])) ? $req['with_tax'] : 0;
		if(is_array($MRF_ID)){
			$MRF_ID = implode(",",$MRF_ID);
		}
		$REPORT 			= DB::select("CALL PR_B2B_REPORT('".$START_DATE."','".$END_DATE."','".$MRF_ID."')");
		$ARRAY_CHALLAN 		= array();
		$TOTAL_GROSS 		= 0;
		$TOTAL_CGST 		= 0;
		$TOTAL_SGST 		= 0;
		$TOTAL_IGST 		= 0;
		$TOTAL_GST 			= 0;
		$TOTAL_NET 			= 0;
		$TOTAL_TCS 			= 0;
		$TOTAL_NET_EXC_TCS 	= 0;
		$TOTAL_CHARGES_AMT 	= 0;
		if(!empty($REPORT)){
			foreach($REPORT as $KEY => $VALUE){
				$GROSS_AMT 			= 0;
				$GST_AMT 			= 0;
				$NET_AMT 			= 0;
				$TCS_AMT 			= 0;
				$NET_EXC_TCS 		= 0;
				$CHARGES_GROSS 		= 0;
				$CHARGES_GST 		= 0;
				$CHARGES_NET 		= 0;


				$REPORT[$KEY]->cgst_amount = ($VALUE->cgst_amount > 0) ? _FormatNumberV2($VALUE->cgst_amount) : 0;
				$REPORT[$KEY]->sgst_amount = ($VALUE->sgst_amount > 0) ?_FormatNumberV2($VALUE->sgst_amount) : 0;
				$REPORT[$KEY]->igst_amount = ($VALUE->igst_amount > 0) ? _FormatNumberV2($VALUE->igst_amount) : 0;
				$TOTAL_CGST 		+= ($VALUE->cgst_amount > 0) ? _FormatNumberV2($VALUE->cgst_amount) : 0;
				$TOTAL_SGST 		+= ($VALUE->sgst_amount > 0) ?_FormatNumberV2($VALUE->sgst_amount) : 0;
				$TOTAL_IGST 		+= ($VALUE->igst_amount > 0) ? _FormatNumberV2($VALUE->igst_amount) : 0;
				if (!in_array($VALUE->challan_no, $ARRAY_CHALLAN))
				{
				  	array_push($ARRAY_CHALLAN,$VALUE->challan_no);
				  	if($VALUE->TRN_TYPE == 'DISPATCH'){
				  		$CHARGE_DATA = \DB::table("wm_invoice_additional_charges")
				  		->select(
							\DB::raw("SUM(gross_amount) as charge_gross_amount"),
							\DB::raw("SUM(gst_amount) as charge_gst_amount"),
							\DB::raw("SUM(net_amount) as charge_net_amount")
						)->where("dispatch_id",$VALUE->dispatch_id)->groupBy("dispatch_id")->first();
						if(!empty($CHARGE_DATA)){
							$CHARGES_GROSS 			= _FormatNumberV2($CHARGE_DATA->charge_gross_amount);
						  	$CHARGES_GST 			= _FormatNumberV2($CHARGE_DATA->charge_gst_amount);
						  	$CHARGES_NET 			= _FormatNumberV2($CHARGE_DATA->charge_net_amount);
						}
				  	}
				  	
				  	$TCS_AMT 						= _FormatNumberV2($VALUE->tcs_amount);
				  	$GROSS_AMT 						= _FormatNumberV2($VALUE->gross_amount + $VALUE->rent_amt + $CHARGES_GROSS);
				  	$GST_AMT 						= _FormatNumberV2($VALUE->gst_amount + $VALUE->rent_gst_amt + $CHARGES_GST);
				  	$NET_AMT 						= _FormatNumberV2($VALUE->net_amount + $VALUE->total_rent_amt + $TCS_AMT + $CHARGES_NET);

				  	if($VALUE->TRN_TYPE == "TRANSFER"){
						$GROSS_AMT 	= _FormatNumberV2($VALUE->gross_amount);
						$GST_AMT 	= ($VALUE->gst_amount > 0) ? _FormatNumberV2($VALUE->gst_amount) : _FormatNumberV2(($GROSS_AMT * $VALUE->gst_percent) / 100);
						$NET_AMT 	= ($VALUE->net_amount > 0) ? _FormatNumberV2($VALUE->net_amount) : _FormatNumberV2($GROSS_AMT + $GST_AMT);
					}

				}else{
					$TCS_AMT						= 0;
					$GROSS_AMT 						= _FormatNumberV2($VALUE->gross_amount);
					$GST_AMT 						= _FormatNumberV2($VALUE->gst_amount);
					$NET_AMT 						= _FormatNumberV2($VALUE->net_amount);
					if($VALUE->TRN_TYPE == "TRANSFER"){
						$GROSS_AMT 	= _FormatNumberV2($VALUE->gross_amount);
						$GST_AMT 	= ($VALUE->gst_amount > 0) ? _FormatNumberV2($VALUE->gst_amount) : _FormatNumberV2(($GROSS_AMT * $VALUE->gst_percent) / 100);
						$NET_AMT 	= ($VALUE->net_amount > 0) ? _FormatNumberV2($VALUE->net_amount) : _FormatNumberV2($GROSS_AMT + $GST_AMT);
					}
				}
				$NET_EXC_TCS 						= _FormatNumberV2($NET_AMT - $TCS_AMT);
				$REPORT[$KEY]->gross_amount 		= _FormatNumberV2($GROSS_AMT);
				$REPORT[$KEY]->gst_amount 			= _FormatNumberV2($GST_AMT);
				$REPORT[$KEY]->net_amount 			= _FormatNumberV2($NET_AMT);
				$REPORT[$KEY]->tcs_amount 			= _FormatNumberV2($TCS_AMT);
				$REPORT[$KEY]->final_invoice_amt 	= _FormatNumberV2($NET_EXC_TCS);
				$TOTAL_NET_EXC_TCS 	+= $NET_EXC_TCS;
				$TOTAL_NET 			+= $NET_AMT;
				$TOTAL_GROSS 		+= $GROSS_AMT;
				$TOTAL_GST 			+= $GST_AMT;
				$TOTAL_TCS 			+= $TCS_AMT;
			}
		}
		$RES 						= array();
		$RES['result'] 				= $REPORT;
		$RES['TOTAL_GROSS'] 		= _FormatNumberV2($TOTAL_GROSS);
		$RES['TOTAL_TCS'] 			= _FormatNumberV2($TOTAL_TCS);
		$RES['TOTAL_NET'] 			= _FormatNumberV2($TOTAL_NET);
		$RES['TOTAL_NET_EXC_TCS'] 	= _FormatNumberV2($TOTAL_NET_EXC_TCS);
		$RES['TOTAL_GST'] 			= _FormatNumberV2($TOTAL_GST);
		$RES['TOTAL_CGST'] 			= _FormatNumberV2($TOTAL_CGST);
		$RES['TOTAL_SGST'] 			= _FormatNumberV2($TOTAL_SGST);
		$RES['TOTAL_IGST'] 			= _FormatNumberV2($TOTAL_IGST);
		return $RES;
	}


	/*
	Use 	: B2B Report for Account
	Author 	: Axay Shah
	Date 	: 25 May 2022
	*/
	public static function B2BAccountReportV2($req){

		$START_DATE = (isset($req['startDate']) && !empty($req['startDate'])) ? date("Y-m-d",strtotime($req['startDate']))." ".GLOBAL_START_TIME : "01-".date("m-d")." ".GLOBAL_START_TIME;
		$END_DATE 	= (isset($req['endDate']) && !empty($req['endDate'])) ? date("Y-m-d",strtotime($req['endDate']))." ".GLOBAL_END_TIME : date("Y-m-t")." ".GLOBAL_END_TIME;
		$MRF_ID 	= (isset($req['mrf_id']) && !empty($req['mrf_id'])) ? $req['mrf_id'] : '';
		$WITH_TAX 	= (isset($req['with_tax']) && !empty($req['with_tax'])) ? $req['with_tax'] : 1;
		if(is_array($MRF_ID)){
			$MRF_ID = implode(",",$MRF_ID);
		}
		$REPORT 			= DB::select("CALL PR_B2B_REPORT_V2('".$START_DATE."','".$END_DATE."','".$MRF_ID."',".$WITH_TAX.")");
		$ARRAY_CHALLAN 		= array();
		$TOTAL_GROSS 		= 0;
		$TOTAL_CGST 		= 0;
		$TOTAL_SGST 		= 0;
		$TOTAL_IGST 		= 0;
		$TOTAL_GST 			= 0;
		$TOTAL_NET 			= 0;
		$TOTAL_TCS 			= 0;
		$TOTAL_NET_EXC_TCS 	= 0;
		$TOTAL_CHARGES_AMT 	= 0;
		if(!empty($REPORT)){
			foreach($REPORT as $KEY => $VALUE){
				$GROSS_AMT 			= 0;
				$GST_AMT 			= 0;
				$NET_AMT 			= 0;
				$TCS_AMT 			= 0;
				$NET_EXC_TCS 		= 0;
				$CHARGES_GROSS 		= 0;
				$CHARGES_GST 		= 0;
				$CHARGES_NET 		= 0;


				$REPORT[$KEY]->cgst_amount = ($VALUE->cgst_amount > 0) ? _FormatNumberV2($VALUE->cgst_amount) : 0;
				$REPORT[$KEY]->sgst_amount = ($VALUE->sgst_amount > 0) ?_FormatNumberV2($VALUE->sgst_amount) : 0;
				$REPORT[$KEY]->igst_amount = ($VALUE->igst_amount > 0) ? _FormatNumberV2($VALUE->igst_amount) : 0;
				$TOTAL_CGST 		+= ($VALUE->cgst_amount > 0) ? _FormatNumberV2($VALUE->cgst_amount) : 0;
				$TOTAL_SGST 		+= ($VALUE->sgst_amount > 0) ?_FormatNumberV2($VALUE->sgst_amount) : 0;
				$TOTAL_IGST 		+= ($VALUE->igst_amount > 0) ? _FormatNumberV2($VALUE->igst_amount) : 0;
				if (!in_array($VALUE->challan_no, $ARRAY_CHALLAN))
				{
				  	array_push($ARRAY_CHALLAN,$VALUE->challan_no);
				  	if($VALUE->TRN_TYPE == 'DISPATCH'){
				  		$CHARGE_DATA = \DB::table("wm_invoice_additional_charges")
				  		->select(
							\DB::raw("SUM(gross_amount) as charge_gross_amount"),
							\DB::raw("SUM(gst_amount) as charge_gst_amount"),
							\DB::raw("SUM(net_amount) as charge_net_amount")
						)->where("dispatch_id",$VALUE->dispatch_id)->groupBy("dispatch_id")->first();
						if(!empty($CHARGE_DATA)){
							$CHARGES_GROSS 			= _FormatNumberV2($CHARGE_DATA->charge_gross_amount);
						  	$CHARGES_GST 			= _FormatNumberV2($CHARGE_DATA->charge_gst_amount);
						  	$CHARGES_NET 			= _FormatNumberV2($CHARGE_DATA->charge_net_amount);
						}
				  	}
				  	
				  	$TCS_AMT 						= _FormatNumberV2($VALUE->tcs_amount);
				  	$GROSS_AMT 						= _FormatNumberV2($VALUE->gross_amount + $VALUE->rent_amt + $CHARGES_GROSS);
				  	$GST_AMT 						= _FormatNumberV2($VALUE->gst_amount + $VALUE->rent_gst_amt + $CHARGES_GST);
				  	$NET_AMT 						= _FormatNumberV2($VALUE->net_amount + $VALUE->total_rent_amt + $TCS_AMT + $CHARGES_NET);

				  	if($VALUE->TRN_TYPE == "TRANSFER"){
						$GROSS_AMT 	= _FormatNumberV2($VALUE->gross_amount);
						$GST_AMT 	= ($VALUE->gst_amount > 0) ? _FormatNumberV2($VALUE->gst_amount) : _FormatNumberV2(($GROSS_AMT * $VALUE->gst_percent) / 100);
						$NET_AMT 	= ($VALUE->net_amount > 0) ? _FormatNumberV2($VALUE->net_amount) : _FormatNumberV2($GROSS_AMT + $GST_AMT);
					}

				}else{
					$TCS_AMT						= 0;
					$GROSS_AMT 						= _FormatNumberV2($VALUE->gross_amount);
					$GST_AMT 						= _FormatNumberV2($VALUE->gst_amount);
					$NET_AMT 						= _FormatNumberV2($VALUE->net_amount);
					if($VALUE->TRN_TYPE == "TRANSFER"){
						$GROSS_AMT 	= _FormatNumberV2($VALUE->gross_amount);
						$GST_AMT 	= ($VALUE->gst_amount > 0) ? _FormatNumberV2($VALUE->gst_amount) : _FormatNumberV2(($GROSS_AMT * $VALUE->gst_percent) / 100);
						$NET_AMT 	= ($VALUE->net_amount > 0) ? _FormatNumberV2($VALUE->net_amount) : _FormatNumberV2($GROSS_AMT + $GST_AMT);
					}
				}
				$NET_EXC_TCS 						= _FormatNumberV2($NET_AMT - $TCS_AMT);
				$REPORT[$KEY]->gross_amount 		= _FormatNumberV2($GROSS_AMT);
				$REPORT[$KEY]->gst_amount 			= _FormatNumberV2($GST_AMT);
				$REPORT[$KEY]->net_amount 			= _FormatNumberV2($NET_AMT);
				$REPORT[$KEY]->tcs_amount 			= _FormatNumberV2($TCS_AMT);
				$REPORT[$KEY]->final_invoice_amt 	= _FormatNumberV2($NET_EXC_TCS);
				$TOTAL_NET_EXC_TCS 	+= $NET_EXC_TCS;
				$TOTAL_NET 			+= $NET_AMT;
				$TOTAL_GROSS 		+= $GROSS_AMT;
				$TOTAL_GST 			+= $GST_AMT;
				$TOTAL_TCS 			+= $TCS_AMT;
			}
		}
		$RES 						= array();
		$RES['result'] 				= $REPORT;
		$RES['TOTAL_GROSS'] 		= _FormatNumberV2($TOTAL_GROSS);
		$RES['TOTAL_TCS'] 			= _FormatNumberV2($TOTAL_TCS);
		$RES['TOTAL_NET'] 			= _FormatNumberV2($TOTAL_NET);
		$RES['TOTAL_NET_EXC_TCS'] 	= _FormatNumberV2($TOTAL_NET_EXC_TCS);
		$RES['TOTAL_GST'] 			= _FormatNumberV2($TOTAL_GST);
		$RES['TOTAL_CGST'] 			= _FormatNumberV2($TOTAL_CGST);
		$RES['TOTAL_SGST'] 			= _FormatNumberV2($TOTAL_SGST);
		$RES['TOTAL_IGST'] 			= _FormatNumberV2($TOTAL_IGST);
		return $RES;
	}


	/*
	Use 	: B2B CN DN REPORT
	Author 	: Axay Shah
	Date 	: 07 JUNE 2022
	*/
	public static function B2BCnDnReport($req){
		
		$START_DATE = (isset($req['startDate']) && !empty($req['startDate'])) ? date("Y-m-d",strtotime($req['startDate']))." ".GLOBAL_START_TIME : "01-".date("m-d")." ".GLOBAL_START_TIME;
		$END_DATE 	= (isset($req['endDate']) && !empty($req['endDate'])) ? date("Y-m-d",strtotime($req['endDate']))." ".GLOBAL_END_TIME : date("Y-m-t")." ".GLOBAL_END_TIME;
		$MRF_ID 	= (isset($req['mrf_id']) && !empty($req['mrf_id'])) ? $req['mrf_id'] : '11';

		if(is_array($MRF_ID)){
			$MRF_ID = implode(",",$MRF_ID);
		}
		$REPORT 			= DB::select("CALL PR_B2B_CNDN_REPORT('".$START_DATE."','".$END_DATE."','".$MRF_ID."')");
		$ARRAY_CHALLAN 		= array();
		$TOTAL_GROSS 		= 0;
		$TOTAL_GST 			= 0;
		$TOTAL_NET 			= 0;
		$TOTAL_TCS 			= 0;
		$TOTAL_NET_EXC_TCS 	= 0;
		$TOTAL_CHARGES_AMT 	= 0;
		if(!empty($REPORT)){
			foreach($REPORT as $KEY => $VALUE){
				$TOTAL_CHARGES_AMT 	= 0;
				$GROSS_AMT 			= 0;
				$GST_AMT 			= 0;
				$NET_AMT 			= 0;
				$TCS_AMT 			= 0;
				$NET_EXC_TCS 		= 0;
				$GROSS_AMT 			= _FormatNumberV2($VALUE->gross_amount);
				$GST_AMT 			= _FormatNumberV2($VALUE->gst_amount);
				$NET_AMT 			= _FormatNumberV2($VALUE->net_amount);
				if($VALUE->TRN_TYPE == 'DISPATCH'){
					if (!in_array($VALUE->cn_dn_id, $ARRAY_CHALLAN))
					{
						array_push($ARRAY_CHALLAN,$VALUE->cn_dn_id);
						$RENT_DATA = \DB::table("wm_invoices_credit_debit_notes_frieght_details")->select(
							\DB::raw("SUM(gross_amount) as rent_amt"),
							\DB::raw("SUM(gst_amount) as rent_gst_amt"),
							\DB::raw("SUM(net_amount) as total_rent_amt")
						)->where("cd_notes_id",$VALUE->cn_dn_id)->groupBy("cd_notes_id")->first();

						if(!empty($RENT_DATA)){
							$TOTAL_CHARGES_AMT += _FormatNumberV2($RENT_DATA->total_rent_amt);
							$GROSS_AMT 	+= _FormatNumberV2($RENT_DATA->rent_amt);
						  	$GST_AMT 	+= _FormatNumberV2($RENT_DATA->rent_gst_amt);
						  	$NET_AMT 	+= _FormatNumberV2($RENT_DATA->total_rent_amt);
						}
						$CHARGE_DATA 	= WmInvoicesCreditDebitNotesChargesDetails::where("cd_notes_id",$VALUE->cn_dn_id)->get();
						if(!empty($CHARGE_DATA)){
							foreach($CHARGE_DATA AS $CK => $CV){
								$TOTAL_CHARGES_AMT += _FormatNumberV2($CV->revised_net_amount);
								$GROSS_AMT 	+= _FormatNumberV2($CV->revised_gross_amount);
							  	$GST_AMT 	+= _FormatNumberV2($CV->revised_gst_amount);
							  	$NET_AMT 	+= _FormatNumberV2($CV->revised_net_amount);
							}
						}
					}
				}
				$NET_EXC_TCS 						= _FormatNumberV2($NET_AMT - $TOTAL_CHARGES_AMT);
				$REPORT[$KEY]->gross_amount 		= _FormatNumberV2($GROSS_AMT);
				$REPORT[$KEY]->gst_amount 			= _FormatNumberV2($GST_AMT);
				$REPORT[$KEY]->net_amount 			= _FormatNumberV2($NET_AMT);
				$REPORT[$KEY]->tcs_amount 			= _FormatNumberV2($TCS_AMT);
				$REPORT[$KEY]->final_invoice_amt 	= _FormatNumberV2($NET_EXC_TCS);
				$TOTAL_NET_EXC_TCS 	+= $NET_EXC_TCS;
				$TOTAL_NET 			+= $NET_AMT;
				$TOTAL_GROSS 		+= $GROSS_AMT;
				$TOTAL_GST 			+= $GST_AMT;
				$TOTAL_TCS 			+= $TCS_AMT;
			}
		}
		$RES 						= array();
		$RES['result'] 				= $REPORT;
		$RES['TOTAL_GROSS'] 		= _FormatNumberV2($TOTAL_GROSS);
		$RES['TOTAL_TCS'] 			= _FormatNumberV2($TOTAL_TCS);
		$RES['TOTAL_NET'] 			= _FormatNumberV2($TOTAL_NET);
		$RES['TOTAL_NET_EXC_TCS'] 	= _FormatNumberV2($TOTAL_NET_EXC_TCS);
		$RES['TOTAL_GST'] 			= _FormatNumberV2($TOTAL_GST);
		return $RES;
	}
}