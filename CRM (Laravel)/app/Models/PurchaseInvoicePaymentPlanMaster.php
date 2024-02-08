<?php

namespace App\Models;

use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use PDF;  
use Excel;
use App\Exports\PaymentPlanCSV;
class PurchaseInvoicePaymentPlanMaster extends Model implements Auditable
{
	protected 	$table 		=	'purchase_invoice_payment_plan_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;
	protected $casts = [
       
    ];
    /*
    Use 	: Generate payment invoice for appointment
    Author 	: Axay Shah
    Date 	: 14 Sepetember 2021 
    */
    public static function AddPurchaseInvoicePaymentPlan($request){
    	$createdBy 		= Auth()->user()->adminuserid;
    	$updatedBy 		= Auth()->user()->adminuserid;
    	$totalPlanAmt 	= (isset($request->total_plan_amt) && !empty($request->total_plan_amt)) ? $request->total_plan_amt : 0;
    	$availableAmt 	= (isset($request->available_amt) && !empty($request->available_amt)) ? $request->available_amt : 0;
    	$totalPaidAmt 	= (isset($request->total_paid_amt) && !empty($request->total_paid_amt)) ? $request->total_paid_amt : 0;
    	$invoiceDetails = (isset($request->invoice_details) && !empty($request->invoice_details)) ? $request->invoice_details : "";
		if(!empty($invoiceDetails)){
			$detailsData = json_decode($invoiceDetails,true);
			foreach($detailsData as $key => $value){
				$GROSS_AMT 				= 0;
				$DEDUCTION_AMT 			= 0;
				$GST_WITH_HOLD 			= 0;
				$FINAL_AMT 				= 0;
				$REMAIN_AMT 			= 0;
				$NET_AMT 				= 0;
				PurchaseInvoicePaymentPlanDetailMaster::where(["appointment_id" => $value['appointment_id'],"status" => 0,"csv_generated" => 0])->delete();
				$insert = PurchaseInvoicePaymentPlanDetailMaster::StorePurchaseInvoiceDetail($value);
			}
    	}
    }
	/*
    Use 	: Generate payment invoice for appointment
    Author 	: Axay Shah
    Date 	: 19 Sepetember 2021 
    */
    public static function ListPaymentPlan($request){

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
		$Location 		= new LocationMaster();
		$Admin 			= $AdminUser->getTable();
		$AdminUserID 	= Auth()->user()->adminuserid;
		$Today          = date('Y-m-d');
		$cityId        	= GetBaseLocationCity();
		$sortBy         = (isset($request->sortBy) && !empty($request->sortBy)) ? $request->sortBy 	: "id";
		$sortOrder      = (isset($request->sortOrder) && !empty($request->sortOrder)) ? $request->sortOrder : "ASC";
		$recordPerPage  = (isset($request->size) && !empty($request->size)) ? $request->size : DEFAULT_SIZE;
		$pageNumber     = (isset($request->pageNumber) && !empty($request->pageNumber)) ? $request->pageNumber :'';
		$status     	= (isset($request->status) && !empty($request->status)) ? $request->status :"0";
		$appointmentID 	= (isset($request->appointment_id) && !empty($request->appointment_id)) ? $request->appointment_id   :"";
		$csvGenerated 	= (isset($request->csv_generated) && !empty($request->csv_generated)) ? $request->csv_generated   : '0';
		$priority 	= (isset($request->priority) && !empty($request->priority)) ? $request->priority  : 0;
		$startDate 	= (isset($request->startDate) && !empty($request->startDate)) ? date("Y-m-d",strtotime($request->startDate)) :'';
		$endDate 	= (isset($request->endDate) && !empty($request->endDate)) ? date("Y-m-d",strtotime($request->endDate))  : '';
		$data 	= PurchaseInvoicePaymentPlanDetailMaster::select(
				"$PPDM.*",
				\DB::raw("CONCAT(CUS.first_name,' ',CUS.last_name) AS customer_name"),
				\DB::raw("CUS.bank_name AS cus_bank_name"),
				\DB::raw("CUS.account_no AS cus_acc_no"),
				\DB::raw("CUS.ifsc_code AS cus_ifsc_code"),
				\DB::raw("CUS.net_suit_code AS customer_ns_code"),
				\DB::raw("CUS.branch_name AS cus_branch_name"),
				\DB::raw("Location.city AS cus_city"),
				\DB::raw("(CASE WHEN $PPDM.status = 0 THEN 'PENDING'
							 WHEN $PPDM.status = 1 THEN 'PRIORITY APPROVED'
							 WHEN $PPDM.status = 2 THEN 'CSV GENERATED'
							 WHEN $PPDM.status = 3 THEN 'PAID'
						END) AS status_name"),
				\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) AS created_by_name"),
				\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) AS updated_by_name"),
				\DB::raw("CONCAT(U3.firstname,' ',U3.lastname) AS priority_set_by_name"),
				\DB::raw("CUS.branch_name AS cus_branch_name"),
				\DB::raw("PARA.para_value as priority_name"))
		->join($CUSTOMER->getTable()." AS CUS",$PPDM.".customer_id","=","CUS.customer_id")
		->leftjoin($Location->getTable()." AS Location","CUS.city","=","Location.location_id")
		->leftjoin($APP->getTable()." AS APP",$PPDM.".appointment_id","=","APP.appointment_id")
		->leftjoin($PARA->getTable()." AS PARA",$PPDM.".priority","=","PARA.para_id")
		->leftjoin($AdminUser->getTable()." AS U1",$PPDM.".created_by","=","U1.adminuserid")
		->leftjoin($AdminUser->getTable()." AS U2",$PPDM.".updated_by","=","U2.adminuserid")
		->leftjoin($AdminUser->getTable()." AS U3",$PPDM.".priority_set_by","=","U3.adminuserid");
		
		if($csvGenerated == "0"){
			$data->where("$PPDM.csv_generated",$csvGenerated);
		}elseif($csvGenerated == 1){
			$data->where("$PPDM.csv_generated",$csvGenerated);
		}
		if(!empty($startDate) && !empty($endDate)){
			$startDate 	= $startDate." ".GLOBAL_START_TIME;
			$endDate 	= $endDate." ".GLOBAL_END_TIME;
		}elseif(!empty($startDate)){
			$startDate 	= $startDate." ".GLOBAL_START_TIME;
			$endDate 	= $startDate." ".GLOBAL_END_TIME;
		}elseif(!empty($endDate)){
			$startDate 	= $endDate." ".GLOBAL_START_TIME;
			$endDate 	= $endDate." ".GLOBAL_END_TIME;
		}
		if(!empty($startDate) && !empty($endDate)){
			$data->whereBetween("$PPDM.created_at",array($startDate,$endDate));
		}
		if($appointmentID){
			$appointmentID =  explode(",",$appointmentID);
			$data->whereIn("$PPDM.appointment_id",$appointmentID);
		}
		if($priority){
			$data->where("$PPDM.priority",$priority);
		}
		$data->where("$PPDM.status",$status);
		// LiveServices::toSqlWithBinding($data);
		$result = $data->get()->toArray();
		if(!empty($result)){
			foreach($result as $key => $value){
				$collection_id = AppointmentCollection::where("appointment_id",$value['appointment_id'])->pluck("collection_id")->toArray();
			    $departmentName = WmBatchMaster::join("wm_department","wm_department.id","=","wm_batch_master.master_dept_id")->whereIn("collection_id",$collection_id)->value("department_name");
			    $result[$key]['department_name'] =  $departmentName;
				$TDS_EXITS = PurchaseInvoiceTdsDetailsMaster::where("appointment_id",$value['appointment_id'])->count();
				$result[$key]['display_tds_textbox'] = ($TDS_EXITS > 0) ? 0 : 1;
			}
		}
		return $result;
	}

	/*
    Use 	: Generate Payment Plan Invoice
    Author 	: Axay Shah
    Date 	: 03 fabruary 2021 
    */
    public static function GeneratePaymentPlanCSV($request){
    	// $data 		= JobWorkMaster::JobworkReport($request);
		// prd($data);
		$FileName 	= "jobwork.xlsx";
		if(!empty($data) && !empty($data)){
			return Excel::download(new PaymentPlanCSV($data,$data),$FileName);
		}
	}
	
}