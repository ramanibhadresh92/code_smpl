<?php

namespace App\Models;

use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\PurchaseInvoiceTdsDetailsMaster;
use App\Models\PurchaseInvoicePaymentPlanMaster;
use App\Models\CompanyBankAccountMaster;

class PurchaseInvoicePaymentPlanDetailMaster extends Model implements Auditable
{
	protected 	$table 		=	'purchase_invoice_payment_plan_detail_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;
	protected $casts = [
       
    ];

    /*
    Use 	: Payment Plan Get Last Process ID for CSV
    Author 	: Axay Shah
    Date 	: 04 Feburary 2022 
    */
    public static function GetLastProcessIDForCSV(){
    	return self::max("process_no");
    }
	/*
    Use 	: Payment plan details store
    Author 	: Axay Shah
    Date 	: 14 Sepetember 2021 
    */
    public static function StorePurchaseInvoiceDetail($req){
    	$appointment_id 	= (isset($req["appointment_id"]) && !empty($req["appointment_id"])) ? $req["appointment_id"] : 0;
    	$createdBy 			= Auth()->user()->adminuserid;
    	$updatedBy 			= Auth()->user()->adminuserid;
    	$add 				= new self();
    	$add->cluster_id 	= (isset($req["cluster_id"]) && !empty($req["cluster_id"])) ? $req["cluster_id"] : 0;
    	$add->customer_id 	= (isset($req["customer_id"]) && !empty($req["customer_id"])) ? $req["customer_id"] : 0;
    	$add->appointment_id 	= (isset($req["appointment_id"]) && !empty($req["appointment_id"])) ? $req["appointment_id"] : 0;
    	$add->invoice_no 	= (isset($req["invoice_no"]) && !empty($req["invoice_no"])) ? $req["invoice_no"]: 0;
		$add->gross_amt 	= (isset($req["gross_amt"]) && !empty($req["gross_amt"])) ? $req["gross_amt"]: 0;
		$add->gst_amt 		= (isset($req["gst_amt"]) && !empty($req["gst_amt"])) ? $req["gst_amt"]: 0;
		$add->net_amt 		= (isset($req["net_amt"]) && !empty($req["net_amt"])) ? $req["net_amt"]: 0;
		$add->deduction_amt = (isset($req["deduction_amt"]) && !empty($req["deduction_amt"])) ? $req["deduction_amt"]: 0;
		$add->final_amt 	= (isset($req["final_amt"]) && !empty($req["final_amt"])) ? $req["final_amt"]: 0;
		$add->priority 		= (isset($req["priority"]) && !empty($req["priority"])) ? $req["priority"]: 0;
		$add->gst_with_hold = (isset($req["gst_with_hold"]) && !empty($req["gst_with_hold"])) ? $req["gst_with_hold"]: 0;
		$add->company_id 	= (isset(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
		$add->created_by 	= $createdBy;
		$add->updated_by 	= $createdBy;
		$add->updated_by 	= $createdBy;
		/* DEBIT NOTE AMOUNT STORE */
		$DN_DATA 		= \DB::SELECT("CALL SP_GET_PURCHASE_CN_DN_AMOUNT(".$appointment_id.",1)");
		$CN_DATA 		= \DB::SELECT("CALL SP_GET_PURCHASE_CN_DN_AMOUNT(".$appointment_id.",0)");
		$DN_NET_AMT 	= (isset($DN_DATA) && !empty($DN_DATA)) ? _FormatNumberV2($DN_DATA[0]->NET_AMT) : 0;
		$CN_NET_AMT 	= (isset($CN_DATA) && !empty($CN_DATA)) ? _FormatNumberV2($CN_DATA[0]->NET_AMT) : 0;
		$add->dn_amt 	= $DN_NET_AMT;
		if($add->save()){
    		return $add->id;
    	}
    	return 0;
    }
	/*
    Use 	: Update Priority of Payment Plan
    Author 	: Axay Shah
    Date 	: 19 Sepetember 2021 
    */
    public static function UpdatePaymentPlanPriority($request){
	$adminuser 		= Auth()->user()->adminuserid;
	$invoice_data = (isset($request->invoice_data) && !empty($request->invoice_data)) ? $request->invoice_data : "";
	if(!empty($invoice_data)){
		$detailsData = json_decode($invoice_data,true);
			foreach($detailsData as $key => $value){
				PurchaseInvoicePaymentPlanDetailMaster::where("id",$value["id"])->where("appointment_id",$value["appointment_id"])
				->update([
					// "priority" 			=> $value["priority"],
					"priority_set_by" 	=> $adminuser,
					"status"			=> 1,
				]);
			}
			return true;
		}
		return false;
	}

	/*
    Use 	: Generate CSV of payment plan
    Author 	: Axay Shah
    Date 	: 25 Octomber 2021 
    */
    public static function GeneratePaymentPlanCSV($request){
    	
		$adminuser 				= Auth()->user()->adminuserid;
		$invoice_data 			= (isset($request->invoice_data) && !empty($request->invoice_data)) ? $request->invoice_data : "";
		$totalPlanAmt 			= (isset($request->total_plan_amt) && !empty($request->total_plan_amt)) ? $request->total_plan_amt : 0;
		$availableAmt 			= (isset($request->available_amt) && !empty($request->available_amt)) ? $request->available_amt : 0;
		$totalPaidAmt 			= (isset($request->total_paid_amt) && !empty($request->total_paid_amt)) ? $request->total_paid_amt : 0;
		$record_ids 			= (isset($request->record_ids) && !empty($request->record_ids)) ? $request->record_ids : array();
		$tds_amt 				= (isset($request->tds_amt) && !empty($request->tds_amt)) ? $request->tds_amt : 0;
		$bank_id 				= (isset($request->bank_id) && !empty($request->bank_id)) ? $request->bank_id : 0;
		$createdBy 				= Auth()->user()->adminuserid;
		$updatedBy 				= Auth()->user()->adminuserid;
		$id 					= 0;
		$availableAmt 			= (isset($request->available_amt) && !empty($request->available_amt)) ? $request->available_amt : 0;
		$add 					= new PurchaseInvoicePaymentPlanMaster();
		$add->generated_date 	= date("Y-m-d H:i:s");
		$add->bank_id 			= $bank_id;
		$add->total_plan_amt 	= $totalPlanAmt;
		$add->available_amt 	= $availableAmt;
		$add->total_paid_amt 	= $totalPaidAmt;
		$add->created_by 		= $createdBy;
		$MAX_NO 				= self::GetLastProcessIDForCSV();
		$PROCESS_NO 			= $MAX_NO + 1;
		if($add->save()){
			$id =  $add->id;
			$totalPlanAmount 	= 0; 
			$totalTDSAmount 	= 0;
			$totalReleseAmount 	= 0;
			if(!empty($invoice_data)){
				$recordsData 	= json_decode($invoice_data,true);
				foreach($recordsData as $value){
					self::where("id",$value["id"])
					->update([
						"cluster_id"			=> $id,
						"paid_amount" 			=> $value['amount_to_release'],
						"csv_generated" 		=> 1,
						"csv_generated_by" 		=> $adminuser,
						"csv_generated_date"	=> date("Y-m-d H:i:s"),
						"paid_date"				=> date("Y-m-d"),
						"process_no"			=> $PROCESS_NO,
					]);
					$tds_amt = (isset($value['tds_amt']) && !empty($value['tds_amt'])) ? _FormatNumberV2($value['tds_amt']) : 0;
					$totalTDSAmount 	+= $tds_amt;
					if($tds_amt > 0){
						$RequestArrayTDS 						= array();
						$RequestArrayTDS['trn_id'] 				= $value["id"];
						$RequestArrayTDS['appointment_id'] 		= $value["appointment_id"];
						$RequestArrayTDS['tds_amt'] 			= $tds_amt;
						$RequestArrayTDS['tds_deducted_flag'] 	= 1;
						PurchaseInvoiceTdsDetailsMaster::AddTDSAmount($RequestArrayTDS);
					}
					$amount_to_release = (isset($value['amount_to_release']) && !empty($value['amount_to_release'])) ? $value['amount_to_release'] : 0; 
					$totalPaidAmt 		+= $amount_to_release;
					$totalPlanAmt 		=  $amount_to_release + $tds_amt;
				}
			}
			PurchaseInvoicePaymentPlanMaster::where("id",$id)->update([
				"total_paid_amt" 	=> $totalPaidAmt,
				"available_amt" 	=> $availableAmt,
				'total_plan_amt' 	=> $totalPlanAmt,
				"total_tds_amt"     => $totalTDSAmount,
				"process_no"     	=> $PROCESS_NO,
			]);
		}
		if($PROCESS_NO > 0){
			return url('/')."/download-payment-plan-csv/".passencrypt($PROCESS_NO);
		}
		return "";
	}
	/*
    Use 	: Generate CSV of payment plan
    Author 	: Axay Shah
    Date 	: 25 Octomber 2021 
    */
	public static function GetDataByID($id=0){
		$createdBy 		= Auth()->user()->adminuserid;
    	$updatedBy 		= Auth()->user()->adminuserid;
    	$self 			= (new static)->getTable();
		$PIPDM 			= new PurchaseInvoicePaymentPlanDetailMaster();
		$PPDM 			= $PIPDM->getTable();
		$CUSTOMER 		= new CustomerMaster();
		$APP 			= new Appoinment();
		$PARA 			= new Parameter();
		$AdminUser 		= new AdminUser();
		$CustomerMasterTbl 	= new CustomerMaster();
		$Department		= new WmDepartment();
		$Admin 			= $AdminUser->getTable();
		$AdminUserID 	= Auth()->user()->adminuserid;
		$Today          = date('Y-m-d');
		$cityId        	= GetBaseLocationCity();
		$data 			= PurchaseInvoicePaymentPlanDetailMaster::select(
							"$PPDM.*",
			\DB::raw("CONCAT(CUS.first_name,' ',CUS.last_name) AS customer_name"),
			\DB::raw("CUS.bank_name AS cus_bank_name"),
			\DB::raw("CUS.account_no AS cus_acc_no"),
			\DB::raw("CUS.ifsc_code AS cus_ifsc_code"),
			\DB::raw("CUS.branch_name AS cus_branch_name"),
			\DB::raw("(CASE WHEN $PPDM.status = 0 THEN 'PENDING'
							WHEN $PPDM.status = 1 THEN 'PRIORITY APPROVED'
							WHEN $PPDM.status = 2 THEN 'CSV GENERATED'
							WHEN $PPDM.status = 3 THEN 'PAID'
					END) AS status_name"),
			\DB::raw("PARA.para_value as priority_name")
		)
		->join($CUSTOMER->getTable()." AS CUS",$PPDM.".customer_id","=","CUS.customer_id")
		->leftjoin($APP->getTable()." AS APP",$PPDM.".appointment_id","=","APP.appointment_id")
		->leftjoin($PARA->getTable()." AS PARA",$PPDM.".priority","=","PARA.para_id")
		->whereIn("id",$id)
		->get()->toArray();
		return $data;
	}
	/*
    Use 	: Generate CSV of payment plan
    Author 	: Axay Shah
    Date 	: 25 Octomber 2021 
    */
	public static function DownloadPaymentPlanCSV($id,$priority=0){

		$self 			= (new static)->getTable();
		$PIPDM 			= new PurchaseInvoicePaymentPlanDetailMaster();
		$PPDM 			= $PIPDM->getTable();
		$CUSTOMER 		= new CustomerMaster();
		$APP 			= new Appoinment();
		$PARA 			= new Parameter();
		$AdminUser 		= new AdminUser();
		$CustomerMasterTbl 	= new CustomerMaster();
		$Department		= new WmDepartment();
		$Admin 			= $AdminUser->getTable();
		$Today          = date('Y-m-d');
		// $cityId        	= GetBaseLocationCity();
		$data 			= PurchaseInvoicePaymentPlanDetailMaster::select(
							"$PPDM.*",
			\DB::raw("CONCAT(CUS.first_name,' ',CUS.last_name) AS customer_name"),
			\DB::raw("CUS.bank_name AS cus_bank_name"),
			\DB::raw("CUS.account_no AS cus_acc_no"),
			\DB::raw("CUS.ifsc_code AS cus_ifsc_code"),
			\DB::raw("CUS.branch_name AS cus_branch_name"),
			\DB::raw("	(CASE WHEN $PPDM.status = 0 THEN 'PENDING'
						 WHEN $PPDM.status = 1 THEN 'PRIORITY APPROVED'
						 WHEN $PPDM.status = 2 THEN 'CSV GENERATED'
						 WHEN $PPDM.status = 3 THEN 'PAID'
						END) AS status_name"),
			\DB::raw("PARA.para_value as priority_name")
		)
		->join($CUSTOMER->getTable()." AS CUS",$PPDM.".customer_id","=","CUS.customer_id")
		->leftjoin($APP->getTable()." AS APP",$PPDM.".appointment_id","=","APP.appointment_id")
		->leftjoin($PARA->getTable()." AS PARA",$PPDM.".priority","=","PARA.para_id");

		if($priority > 0){
			$data->where("$PPDM.priority",$priority);
		}
		$result = $data->where("process_no",$id)
				->orderBy("priority","DESC")
				->get()
				->toArray();
		if(!empty($result)){
			foreach($result as $key => $value){
				$BANK_DETAILS = CompanyBankAccountMaster::where("company_id",$value['company_id'])->where("default_bank",1)->first();
				$result[$key]['client_code'] 			= "NRMPCMS";
				$result[$key]['product_code'] 			= "RPAY";
				$result[$key]['payment_type'] 			= "	";
				$result[$key]['payment_ref_no'] 		= $value['appointment_id'];
				$result[$key]['payment_date'] 			= date("Y-m-d");
				$result[$key]['dr_ac_no'] 				= ($BANK_DETAILS) ? $BANK_DETAILS->account_no : "";
				$result[$key]['amount'] 				= _FormatNumberV2($value['final_amt']);
				$result[$key]['bank_code_indicator'] 	= "M";
				$result[$key]['beneficiary_name'] 		= $value['customer_name'];
				$result[$key]['beneficiary_bank'] 		= $value['cus_bank_name'];
				$result[$key]['beneficiary_ifsc_code'] 	= $value['cus_ifsc_code'];
				$result[$key]['beneficiary_acc_no'] 	= $value['cus_acc_no'];
			}
		}
		return $result;
	}

	/*
    Use 	: Generate CSV of payment plan
    Author 	: Axay Shah
    Date 	: 25 Octomber 2021 
    */
	public static function GetPaymentPlanReport($id){

		$self 			= (new static)->getTable();
		$PIPDM 			= new PurchaseInvoicePaymentPlanDetailMaster();
		$PIPPM 			= new PurchaseInvoicePaymentPlanMaster();
		$CBAM 			= new CompanyBankAccountMaster();
		$PPDM 			= $PIPDM->getTable();
		$PPMAS 			= $PIPPM->getTable();
		$CUSTOMER 		= new CustomerMaster();
		$APP 			= new Appoinment();
		$PARA 			= new Parameter();
		$AdminUser 		= new AdminUser();
		$Admin 			= $AdminUser->getTable();
		$Today          = date('Y-m-d');
		$startDate 	= (isset($request->startDate) && !empty($request->startDate)) ? date("Y-m-d",strtotime($request->startDate)) : "";
		$endDate 	= (isset($request->endDate) && !empty($request->endDate)) ? date("Y-m-d",strtotime($request->endDate)) : "";
		$processNo 	= (isset($request->process_no) && !empty($request->process_no)) ? $request->process_no : "";
		$bankId 	= (isset($request->bank_id) && !empty($request->bank_id)) ? $request->bank_id : 0;
		$data 		= PurchaseInvoicePaymentPlanMaster::select(
					\DB::raw("$PPMAS.*"),
					\DB::raw("BANK.bank_name"),
					\DB::raw("BANK.ifsc_code"),
					\DB::raw("BANK.account_no"),
					\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) AS payment_relese_by"),
					\DB::raw("BANK.account_no"))
					->leftjoin($CBAM->getTable()." as BANK","$PPMAS.bank_id","=","BANK.id")
					->leftjoin($AdminUser->getTable()." as U1","$PPMAS.created_by","=","U1.adminuserid");
		if(!empty($bank_id)){
			$data->where("bank_id",$bank_id);
		}	
		if(!empty($processNo)){
			$data->where("process_no",$processNo);
		}	
		if(!empty($startDate) && !empty($endDate)){
			$startDate 	= $startDate." ".GLOBAL_START_TIME;
			$endDate 	= $endDate." ".GLOBAL_END_TIME;
			$data->whereBetween("created_at",array($startDate,$endDate));
		}elseif(!empty($startDate)){
			$startDate 	= $startDate." ".GLOBAL_START_TIME;
			$endDate 	= $startDate." ".GLOBAL_END_TIME;
			$data->whereBetween("created_at",array($startDate,$endDate));
		}elseif(!empty($endDate)){
			$startDate 	= $endDate." ".GLOBAL_START_TIME;
			$endDate 	= $endDate." ".GLOBAL_END_TIME;
			$data->whereBetween("created_at",array($startDate,$endDate));
		}
		$result 			= $data->get()->toArray();
		$res 				= array();
		$TOTAL_PAID_AMOUNT 	= 0;
		$TOTAL_TDS_AMOUNT 	= 0;
		$TOTAL_PLAN_AMOUNT 	= 0;
		if(!empty($result)){
			foreach($result as $key => $value){
				$viewDetails = PurchaseInvoicePaymentPlanDetailMaster::select(
					"purchase_invoice_payment_plan_detail_master.*",
					"customer_master.net_suit_code",
					\DB::raw("CONCAT(customer_master.first_name,' ',customer_master.last_name) AS customer_name")
				)->join("customer_master","customer_master.customer_id","=","purchase_invoice_payment_plan_detail_master.customer_id")->where("cluster_id",$value['id'])->get(); 
				$result[$key]['process_details'] 		= $viewDetails;
				$result[$key]['total_appointment_cnt'] = PurchaseInvoicePaymentPlanDetailMaster::where("process_no",$value['process_no'])->count();
				$TOTAL_PAID_AMOUNT 	+= _FormatNumberV2($value['total_paid_amt']);
				$TOTAL_TDS_AMOUNT 	+= _FormatNumberV2($value['total_tds_amt']);
				$TOTAL_PLAN_AMOUNT 	+= _FormatNumberV2($value['total_plan_amt']);
			}
		}
		$res['result'] 				= $result;
		$res['TOTAL_PAID_AMOUNT'] 	= _FormatNumberV2($TOTAL_PAID_AMOUNT);
		$res['TOTAL_TDS_AMOUNT'] 	= _FormatNumberV2($TOTAL_TDS_AMOUNT);
		$res['TOTAL_PLAN_AMOUNT'] 	= _FormatNumberV2($TOTAL_PLAN_AMOUNT);
		
		return $res;
	}
}