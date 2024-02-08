<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmClientMaster;
use App\Models\LocationMaster;
use App\Models\CompanyMaster;
use App\Models\WmSalesMaster;
use App\Models\WmDispatch;
use App\Models\WmProductMaster;
use App\Models\AdminUser;
use App\Models\WmInvoices;
use App\Models\WmClientONSPayment;
use App\Models\WmPaymentReceiveONSLog;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\Parameter;
use DB;
class WmPaymentReceive extends Model implements Auditable
{
	protected 	$table 		=	'wm_payment_receive';
	protected 	$primaryKey =	'pid'; // or null
	protected 	$guarded 	=	['pid'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	
	/*
	Use 	: Payment History
	Author 	: Axay Shah
	Date 	: 16 July 2019
	*/
	public static function PaymentHistoryList($InvoiceNo = 0,$invoice_id=0)
	{
		$table 			= (new static)->getTable();
		$Inv 			= new WmInvoices();
		$AdminMaster 	= new AdminUser();
		$Parameter 		= new Parameter();
		$Admin 			= $AdminMaster->getTable();
		$para 			= $Parameter->getTable();
		$net_amount 	= 0;
		$net_amount 	= WmSalesMaster::where("invoice_no",$invoice_id)->sum("net_amount");
		$invoice_no 	= WmInvoices::where("id",$invoice_id)->value("invoice_no");
		$received_amount= 0;

		$data 				= self::select(	"$table.*",
											DB::raw("CONCAT($Admin.firstname,' ',$Admin.lastname) as collect_by_name"),
											DB::raw("$para.para_value as payment_type_name"))
							->leftjoin("$Admin","$table.collect_by","=","$Admin.adminuserid")
							->leftjoin("$para","$table.payment_type","=","$para.para_id")
							->where("$table.invoice_id",$invoice_id)
							->orderBy("id",'DESC')
							->get();
		if(!empty($data))
		{
			foreach($data as $key => $value) {
				$received_amount   				+= _FormatNumberV2($value['received_amount']);
				$data[$key]['remain_amount'] 	= _FormatNumberV2($net_amount - $value['received_amount']);
			}	
		}
		$res["net_amount"] 				=  _FormatNumberV2($net_amount);
		$res["received_amount"] 		=  _FormatNumberV2($received_amount);
		$res['invoice_no'] 				=  $invoice_no;
		$res['remain_amount'] 			= _FormatNumberV2($net_amount - $received_amount);
		$res["result"] 					= $data;
		return $res;
	}


	/*
	Use 	: Add Invoice Data
	Author 	: Axay Shah
	Date 	: 04 July 2019
	*/
	public static function AddPaymentReceive($request)
	{	
		if(isset(Auth()->user()->adminuserid) && !empty(Auth()->user()->adminuserid)) {
			$adminuserid = Auth()->user()->adminuserid;
			$company_id = Auth()->user()->company_id;
		} elseif(isset(Auth()->user()->id) && !empty(Auth()->user()->id)) {
			$adminuserid = Auth()->user()->id;
			$company_id = Auth()->user()->company_id;
		} else {
			$adminuserid = $request['created_by'];
			$company_id = $request['company_id'];
		}

		$invoice_no 				= (isset($request['invoice_no']) && !empty($request['invoice_no'])) ? $request['invoice_no'] :0;
		$InvoiceId  				= 0;
		$InvoiceId 					= WmInvoices::where("invoice_no",$invoice_no)->value("id");
		$Invoice 					=  new self();
		$Invoice->invoice_id 		= $InvoiceId;
		$Invoice->collect_by 		= (isset($request['collect_by']) && !empty($request['collect_by'])) ? $request['collect_by'] : $adminuserid;
		$Invoice->remarks 			= (isset($request['remarks']) && !empty($request['remarks'])) ? $request['remarks'] :0;
		$Invoice->received_amount 	= (isset($request['received_amount']) && !empty($request['received_amount'])) ? $request['received_amount'] :0;
		$Invoice->payment_type 		= (isset($request['payment_type']) && !empty($request['payment_type'])) ? $request['payment_type'] :0;
		$Invoice->payment_date 		= (isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] :0;
		$Invoice->created_by 		= $adminuserid;
		$Invoice->payment_date 		= (isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] :date('Y-m-d H:i:s');
		if($Invoice->save())
		{
			$id 				= $Invoice->id;
			$ReceivedAmount 	= self::ReceivedAmount($Invoice->invoice_id);
			$ReceivedAmount 	= _FormatNumberV2($ReceivedAmount);
			if($ReceivedAmount == $request['invoice_amount']){
				$GetData = self::where("invoice_id",$Invoice->invoice_id)->get();
				if(!empty($GetData)) {
					$i = 0;
					WmInvoices::where("id",$Invoice->invoice_id)->update(["collect_payment_status"=>1]);
				}
			}
			if(isset($request['payment_media']))
			{
				$path = PATH_COMPANY."/".$company_id."/".DIRECT_DISPATCH_IMG."/".PAYMENT_SLIP;
				/* IMAGE UPLOAD */
				if(!is_dir(public_path(PATH_IMAGE.'/').$path)) {
					mkdir(public_path(PATH_IMAGE.'/').$path,0777,true);
				}
				$image = $request["payment_media"];
				$input['imagename'] = "payment_media".$id."_".time().'.'.$image->getClientOriginalExtension();
				$destinationPath = public_path(PATH_IMAGE."/".$path);
				$image->move($destinationPath, $input['imagename']);
				$MediaMaster 	= MediaMaster::AddMedia($input['imagename'],$input['imagename'],$path,$company_id);
				if($MediaMaster > 0){
					self::where("id",$id)->update(["payment_media"=>$MediaMaster]);
				}
				/* END IMAGE UPLOAD */
			}
			return true;
		}
		return false;
	}

	/*
	Use 	: Received Order Amount
	Author 	: Axay Shah
	Date 	: 15 July 2019
	*/
	public static function ReceivedOrderAmount($InvoiceNo = 0)
	{
		return self::select(\DB::raw("SUM(received_amount) as received_amount"),
							\DB::raw("COUNT(received_amount) as received_amount_count"))
					->where("invoice_no",$InvoiceNo)
					->get();
	}
	/*
	Use 	: Add Payment Details Get By ID
	Author 	: Axay Shah
	Date 	: 15 July 2019
	*/
	public static function AddPaymentDetailData($invoiceNo =0,$salesId = 0,$invoiceID=0)
	{
		$ClientMaster 	= new WmClientMaster();
		$ReceivedAmount = 0;
		$NetAmount 		= 0;
		$RemainAmount 	= 0;
		$MediaUrl 		= "";
		$data 			= array();
		$ClientName 	= "";
		if(!empty($invoiceID))
		{
			$GetClientName 	= 	WmInvoices::select("WC.client_name","wm_invoices.invoice_no")
								->join($ClientMaster->getTable()." as WC","client_master_id","=","WC.id")
								->where("wm_invoices.id",$invoiceID)
								->first();
			$invoiceNo 		=   (isset($GetClientName->invoice_no)) ? $GetClientName->invoice_no : ""; 
			$NetAmount 		= 	self::GetNetAmountByInvoiceId($invoiceID);
			$ReceivedAmount = 	self::ReceivedAmount($invoiceID);
			$ClientName 	=   (isset($GetClientName->client_name)) ? $GetClientName->client_name : "";
		}
		$RemainAmount 				= $NetAmount - $ReceivedAmount;
		$data['net_amount'] 		= _FormatNumberV2($NetAmount);
		$data['received_amount'] 	= _FormatNumberV2($ReceivedAmount);
		$data['remain_amount'] 		= _FormatNumberV2($RemainAmount);
		$data['client_name'] 		= $ClientName;
		$data['invoice_id'] 		= $invoiceID;
		$data['invoice_no'] 		= $invoiceNo;
		$data['collect_by'] 		= "";
		$MediaUrl 					= self::GetAllPaymentSlip($invoiceID);
		$data['payment_slip']		= $MediaUrl;
		return $data;
	}

	/*
	Use 	:  	Get Net Amount Of sales id
	Author 	: 	Axay Shah
	Date 	:  	16 July 2019
	*/
	public static function GetNetAmountBySalesId($salesId = 0)
	{
		if(!is_array($salesId)) {
			$salesId = explode(",",$salesId);
		}
		return WmSalesMaster::whereIn("id",$salesId)->sum('net_amount');
	}

	/*
	Use 	:  	Get Net Amount Of sales id
	Author 	: 	Axay Shah
	Date 	:  	16 July 2019
	*/
	public static function GetNetAmountByInvoiceId($invoice_id = 0)
	{
		return WmSalesMaster::where("invoice_no",$invoice_id)->sum('net_amount');
	}

	/*
	Use 	: Get Received amount sum
	Author 	: Axay Shah
	Date 	: 16 July,2019
	*/
	public static function ReceivedAmount($invoice_id = 0)
	{
		return self::where("invoice_id",$invoice_id)->sum('received_amount');
	}

	/*
	Use 	: Get Details by Invoice NO
	Author 	: Axay Shah
	Date 	: 22 July,2019
	*/
	public static function GetAllPaymentSlip($invoice_id = 0)
	{
		$media 			= array();
		$payment_media 	= self::where("invoice_id",$invoice_id)->pluck('payment_media');
		if($payment_media) {
			foreach($payment_media as $payment) {
				$image = MediaMaster::where("id",$payment)->first();
				if($image) {
					array_push($media,$image->original_name);
				}
			}
		}
		return $media;
	}

	/*
	Use 	: Add Payment Details Get By ID
	Author 	: Axay Shah
	Date 	: 15 July 2019
	*/
	public static function AddPaymentReceiveByONS($Request)
	{
		$SystemUserID 		= 1;
		$PAYMENT_TYPE 		= 1030003;
		$Invoice_No 		= trim(isset($Request->invoice_no)?$Request->invoice_no:0);
		$PaidAmount 		= trim(isset($Request->payment_amount)?$Request->payment_amount:0);
		$PAYMENT_DATE 		= trim(isset($Request->payment_date)?$Request->payment_date:0);
		$REMARKS 			= trim(isset($Request->bank_reference_details)?$Request->bank_reference_details.". Payment details updated by auto process ONS on ".date("Y-m-d"):"Payment details updated by auto process ONS on ".date("Y-m-d"));
		$REMARKS 			= trim(isset($Request->pmt_ref) && !empty($Request->pmt_ref)?"Payment Ref. No: ".$Request->pmt_ref." ".$REMARKS:$REMARKS);
		if (!empty($Invoice_No))
		{
			WmPaymentReceiveONSLog::saveONSPaymentLog($Request);
			$DispatchDetail 	= WmDispatch::select("id","challan_no")->where("challan_no",$Invoice_No)->where("approval_status",1)->first();
			if (isset($DispatchDetail->id) && !empty($DispatchDetail->id))
			{
				$InvoiceAmountSql 	= "	SELECT
										CASE WHEN 1=1 THEN
										(
											SELECT SUM(net_amount) FROM wm_dispatch_product WHERE dispatch_id = ".$DispatchDetail->id."
										) END AS Invoice_Amount,
										CASE WHEN 1=1 THEN
										(
											SELECT SUM(received_amount)
											FROM wm_payment_receive
											INNER JOIN wm_invoices ON wm_invoices.id = wm_payment_receive.invoice_id
											INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices.dispatch_id
											WHERE wm_dispatch.approval_status = 1
											AND wm_dispatch.id = ".$DispatchDetail->id."
										) END AS Total_Received_Amount,
										CASE WHEN 1=1 THEN
										(
											SELECT SUM(wm_invoices_credit_debit_notes_details.revised_net_amount)
											FROM wm_invoices_credit_debit_notes_details
											INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
											INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
											WHERE wm_dispatch.approval_status = 1
											AND wm_invoices_credit_debit_notes.notes_type = 0
											AND wm_invoices_credit_debit_notes.status = 3
											AND wm_dispatch.id = ".$DispatchDetail->id."
										) END AS CN_Amount,
										CASE WHEN 1=1 THEN
										(
											SELECT SUM(wm_invoices_credit_debit_notes_details.revised_net_amount)
											FROM wm_invoices_credit_debit_notes_details
											INNER JOIN wm_invoices_credit_debit_notes ON wm_invoices_credit_debit_notes.id = wm_invoices_credit_debit_notes_details.cd_notes_id
											INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices_credit_debit_notes.dispatch_id
											WHERE wm_dispatch.approval_status = 1
											AND wm_invoices_credit_debit_notes.notes_type = 1
											AND wm_invoices_credit_debit_notes.status = 3
											AND wm_dispatch.id = ".$DispatchDetail->id."
										) END AS DN_Amount,
										CASE WHEN 1=1 THEN
										(
											SELECT wm_invoices.id
											FROM wm_invoices
											INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices.dispatch_id
											WHERE wm_dispatch.approval_status = 1
											AND wm_dispatch.id = ".$DispatchDetail->id."
										) END AS Invoice_ID,
										CASE WHEN 1=1 THEN
										(
											SELECT wm_invoices.invoice_no
											FROM wm_invoices
											INNER JOIN wm_dispatch ON wm_dispatch.id = wm_invoices.dispatch_id
											WHERE wm_dispatch.approval_status = 1
											AND wm_dispatch.id = ".$DispatchDetail->id."
										) END AS Invoice_No";
				$SELECTRES 			= DB::connection('master_database')->select($InvoiceAmountSql);
				if (!empty($SELECTRES) && isset($SELECTRES[0]->Invoice_Amount) && !empty($SELECTRES[0]->Invoice_ID))
				{
					$Invoice_Amount 		= !empty($SELECTRES[0]->Invoice_Amount)?$SELECTRES[0]->Invoice_Amount:0;
					$Total_Received_Amount 	= !empty($SELECTRES[0]->Total_Received_Amount)?$SELECTRES[0]->Total_Received_Amount:0;
					$CN_Amount 				= !empty($SELECTRES[0]->CN_Amount)?$SELECTRES[0]->CN_Amount:0;
					$DN_Amount 				= !empty($SELECTRES[0]->DN_Amount)?$SELECTRES[0]->DN_Amount:0;
					$Invoice_ID 			= !empty($SELECTRES[0]->Invoice_ID)?$SELECTRES[0]->Invoice_ID:0;
					$Total_Invoice_Amount	= round((($Invoice_Amount + $DN_Amount) - $CN_Amount),2);
					$OrgPaidAmount 			= $PaidAmount;
					if ($PaidAmount > 0 && $Total_Invoice_Amount > 0 && !empty($Invoice_ID)) {
						if ($PaidAmount >= $Total_Invoice_Amount) {
							$Invoice_Paid_Amount = $Total_Invoice_Amount;
						} else {
							$Invoice_Paid_Amount = $PaidAmount;
						}
						$Total_Paid_Amount = $PaidAmount + $Total_Received_Amount;
						/** INSERT DATA IN PAYMENT RECEIVED TABLE */
						$WmPaymentReceive 					= new self;
						$WmPaymentReceive->invoice_id 		= $Invoice_ID;
						$WmPaymentReceive->collect_by 		= $SystemUserID;
						$WmPaymentReceive->received_amount 	= $Invoice_Paid_Amount;
						$WmPaymentReceive->payment_type 	= $PAYMENT_TYPE;
						$WmPaymentReceive->payment_date 	= $PAYMENT_DATE;
						$WmPaymentReceive->remarks 			= $REMARKS;
						$WmPaymentReceive->created_at 		= date("Y-m-d H:i:s");
						$WmPaymentReceive->created_by 		= $SystemUserID;
						$WmPaymentReceive->updated_at 		= date("Y-m-d H:i:s");
						$WmPaymentReceive->updated_by 		= $SystemUserID;
						$WmPaymentReceive->save();
						/** INSERT DATA IN PAYMENT RECEIVED TABLE */
						
						/** Mark Invoice Status as PAID */
						if ($Total_Paid_Amount >= $Total_Invoice_Amount) {
							WmInvoices::where("id",$Invoice_ID)->update(["invoice_status"=>2,"collect_payment_status"=>1]);
						} else {
							WmInvoices::where("id",$Invoice_ID)->update(["invoice_status"=>3]);
						}
						/** Mark Invoice Status as PAID */
						return true;
					}
				}
			}
			return false;
		} else {
			WmClientONSPayment::saveONSPaymentLog($Request);
			return true;
		}
		return false;
	}

	/*
	Use 	: Client Wise Payment History
	Author 	: Hardyesh Gupta
	Date 	: 02 Nov, 2023
	*/
	public static function ClientPaymentHistoryList($request,$client_id = 0,$paginate = true)
	{
		$table 			= (new static)->getTable();
		$Inv 			= new WmInvoices();
		$AdminMaster 	= new AdminUser();
		$Parameter 		= new Parameter();
		$Admin 			= $AdminMaster->getTable();
		$para 			= $Parameter->getTable();
		$WmInvoice 		= $Inv->getTable();
		$SalesMaster 	= new WmSalesMaster();
		$WmSalesMaster	= $SalesMaster->getTable();
		$sortBy         = (isset($request->sortBy) && !empty($request->sortBy)) ? $request->sortBy : "id"; 
		$sortOrder      = (isset($request->sortOrder) && !empty($request->sortOrder)) ? $request->sortOrder : "ASC"; 
		$recordPerPage  = (isset($request->size) && !empty($request->size)) ? $request->size : DEFAULT_SIZE; 
		$pageNumber     = (isset($request->pageNumber) && !empty($request->pageNumber)) ? $request->pageNumber : 1; 
		$invoice_no   	= (isset($request->invoice_no) && !empty($request->invoice_no)) ? $request->invoice_no : 0; 
		$invoice_id   	= (isset($request->invoice_id) && !empty($request->invoice_id)) ? $request->invoice_id : 0; 
		// $payment_date   = (isset($request->payment_date) && !empty($request->payment_date)) ? date("Y-m-d",strtotime($request->payment_date)) : date('Y-m-d'); 
		$FromDate 		= ($request->startDate) && !empty($request->startDate) ? date("Y-m-d",strtotime($request->startDate)) :"" ;
		$EndDate 		= ($request->endDate) && !empty($request->endDate) ? date("Y-m-d",strtotime($request->endDate)) :"" ;	
		$data 			= self::select(	"$table.*",
										DB::raw("CONCAT($Admin.firstname,' ',$Admin.lastname) as collect_by_name"),
										DB::raw("$para.para_value as payment_type_name"),
										DB::raw("$WmInvoice.invoice_no as invoice_no"))
							->leftjoin("$Admin","$table.collect_by","=","$Admin.adminuserid")
							->leftjoin("$para","$table.payment_type","=","$para.para_id")
							->leftjoin("$WmInvoice","$table.invoice_id","=","$WmInvoice.id")
							->where("$WmInvoice.client_master_id",$client_id);
		if(!empty($invoice_no)){
			$data->where("$WmInvoice.invoice_no","like","%$invoice_no%");
		}
		if(!empty($invoice_id)){
			$data->where("$table.invoice_id","like","%$invoice_id%");
		}
		if(!empty($FromDate) && !empty($EndDate)){
			$data->whereBetween("$table.payment_date",array($FromDate,$EndDate));
		}elseif(!empty($FromDate)){
			$data->whereBetween("$table.payment_date",array($FromDate,$FromDate));
		}elseif(!empty($EndDate)){
			$data->whereBetween("$table.payment_date",array($EndDate,$EndDate));
		}	
		if($paginate){
			$data =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		}else{
			$data = $data->get();
		}							
		$res = $data;
		return $res;
	}

	/*
	Use 	: Client Wise Payment History Get By Id (For Mobile)
	Author 	: Hardyesh Gupta
	Date 	: 02 Nov, 2023
	*/
	public static function ClientPaymentHistoryGetById($id = 0)
	{
		$data = "";
		$table 			= (new static)->getTable();
		$Inv 			= new WmInvoices();
		$AdminMaster 	= new AdminUser();
		$Parameter 		= new Parameter();
		$Admin 			= $AdminMaster->getTable();
		$para 			= $Parameter->getTable();
		$WmInvoice 		= $Inv->getTable();
		$SalesMaster 	= new WmSalesMaster();
		$WmSalesMaster	= $SalesMaster->getTable();
		$net_amount 	= 0;
		$total_received_amount = 0;
		$AdditionalChargesAmount = 0;
		if(!empty($id)){
			$data 	= self::select(	"$table.*",
							DB::raw("CONCAT($Admin.firstname,' ',$Admin.lastname) as collect_by_name"),
							DB::raw("$WmInvoice.dispatch_id as dispatch_id"),
							DB::raw("$para.para_value as payment_type_name"),
							DB::raw("$WmInvoice.invoice_no as invoice_no"))
						->leftjoin("$Admin","$table.collect_by","=","$Admin.adminuserid")
						->leftjoin("$para","$table.payment_type","=","$para.para_id")
						->leftjoin("$WmInvoice","$table.invoice_id","=","$WmInvoice.id")
						->where("$table.id",$id)->get();
			if(!empty($data))
			{
				foreach($data as $key => $value) {
					$invoice_id 	= $value['invoice_id'];
					$invoice_no 	= $value['invoice_no'];
					$total_received_amount = self::where("invoice_id",$invoice_id)->sum("received_amount");
					$InvoiceAmount 		=  WmSalesMaster::where("invoice_no",$invoice_id)->sum('net_amount');
					$AdditionalCharges 	= InvoiceAdditionalCharges::GetInvoiceAdditionalCharges($value['dispatch_id'],$invoice_id);
					if (!empty($AdditionalCharges))
					{
						foreach($AdditionalCharges as $chargekey => $chargevalue){
						$AdditionalChargesAmount += $chargevalue['net_amount'];
						}
					}
					$DispatchData 		= WmDispatch::where("id",$value['dispatch_id'])->first();
					$RentAmount 		= (!empty($DispatchData) && $DispatchData->total_rent_amt) ? $DispatchData->total_rent_amt : 0;
					$TCS_AMT			= (!empty($DispatchData) && $DispatchData->tcs_amount) ? $DispatchData->tcs_amount : 0;
					$Discount_Amount	= (!empty($DispatchData) && $DispatchData->discount_amt) ? $DispatchData->discount_amt : 0;
					$net_amount 		= (!empty($InvoiceAmount)) ? ($InvoiceAmount + $AdditionalChargesAmount + $RentAmount + $TCS_AMT - $Discount_Amount): 0;
					$data[$key]['net_amount'] 				= _FormatNumberV2($net_amount);
					$data[$key]['total_received_amount'] 	= _FormatNumberV2($total_received_amount);
					$data[$key]['remain_amount'] 			= _FormatNumberV2($net_amount - $value['received_amount']);
				}	
			}
			return $data;	
		}
		return $data;
	}
}