<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Crypt;

use GuzzleHttp\Client;
use App\Models\WmInvoices;
use App\Models\WmPaymentReceive;
use App\Models\WaybridgeSlipMaster;
use App\Models\InvoiceApprovalMaster;
use App\Models\WmSalesMaster;
use App\Models\WmDepartment;
use App\Models\WmDispatch;
use App\Models\GSTStateCodes;
use App\Models\WmInvoicePaymentLog;
use App\Http\Requests\AddWayBridge;
use PDF;
use Carbon\Carbon;
use SoapClient;
class InvoiceMasterController extends LRBaseController
{
	/*
	Use     : List Invoice Data
	Author  : Axay Shah
	Date    : 08 July,2019
	*/
	public function SearchInvoice(Request $request){
		$data       = WmInvoices::SearchInvoice($request);
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	}

	/*
	Use     : invoice by id
	Author  : Axay Shah
	Date    : 11 July,2019
	*/
	public function GetInvoiceById(Request $request){
		$id         = (isset($request->invoice_id) && !empty($request->invoice_id)) ? $request->invoice_id : 0;
		$from_CNDN  = (isset($request->from_CNDN) && !empty($request->from_CNDN)) ? $request->from_CNDN : 0;
		$data       = WmInvoices::GetById($id,$from_CNDN);
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	}

	/*
	Use     : Get Add payment Details
	Author  : Axay Shah
	Date    : 16 July,2019
	*/

	///////
	public function AddPaymentDetailData(Request $request){
		$InvoiceNo  = (isset($request->invoice_no)  && !empty($request->invoice_no))    ? $request->invoice_no  : 0;
		$SalesId    = (isset($request->sales_id)    && !empty($request->sales_id))      ? $request->sales_id    : 0;
		$invoiceId  = (isset($request->invoice_id)    && !empty($request->invoice_id))      ? $request->invoice_id    : 0; 
		$data       = WmPaymentReceive::AddPaymentDetailData($InvoiceNo,$SalesId,$invoiceId);
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	}

	public function hdfcencrypt($plainText,$key)
	{
		$key = $this->hdfchextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
		$encryptedText = bin2hex($openMode);
		return $encryptedText;
	}

	public function hdfcdecrypt($encryptedText,$key)
	{
		$key = $this->hdfchextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText = $this->hdfchextobin($encryptedText);
		$decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
		return $decryptedText;
	}
	//*********** Padding Function *********************

	public function pkcs5_pad ($plainText, $blockSize)
	{
	    $pad = $blockSize - (strlen($plainText) % $blockSize);
	    return $plainText . str_repeat(chr($pad), $pad);
	}

	//********** Hexadecimal to Binary function for php 4.0 version ********

	public function hdfchextobin($hexString) 
   	 { 
    	$length = strlen($hexString); 
    	$binString="";   
    	$count=0; 
    	while($count<$length) 
    	{       
    	    $subString =substr($hexString,$count,2);           
    	    $packedString = pack("H*",$subString); 
    	    if ($count==0)
	    {
			$binString=$packedString;
	    } 
    	    
	    else 
	    {
			$binString.=$packedString;
	    } 
    	    
	    $count+=2; 
    	} 
	        return $binString; 
	  } 

	///////
	public function generateOrderID($request) {
		if(array_key_exists('id', $request) && array_key_exists('invoice_no', $request)) {
			$invoice_id = $request['id'];
			$invoice_no = $request['invoice_no'];
			$now = strtotime(Carbon::now());
			return $invoice_id.'_'.$invoice_no.'_'.$now;
		}		
	}

	public function loadPaymentRequestParams(Request $request) {
		if($request->has('invoice_id')) {
			$isValidInvoiceId = WmInvoices::isValidInvoiceId($request->invoice_id);			
			if(!empty($isValidInvoiceId)) {
				$orderID = $this->generateOrderID($isValidInvoiceId);
				$NetAmount = WmPaymentReceive::GetNetAmountByInvoiceId($request->invoice_no);
				$NetAmount = 1; ///////
				$_params = array();
				$_params['tid'] = $orderID; 
				$_params['merchant_id'] = env('HDFC_MERCHANT', '3134968');
				$_params['order_id'] = $orderID;
				$_params['amount'] = $NetAmount;
				$_params['currency'] = "INR";
				$_params['redirect_url'] = env('HDFC_redirect_url', 'https://staging-v2.letsrecycle.co.in/web/v1/sales/invoice/getPaymentResponce');
				$_params['cancel_url'] = env('HDFC_cancel_url', 'https://staging-v2.letsrecycle.co.in/web/v1/sales/invoice/getPaymentResponce');
				$_params['redirect_url4mobile'] = env('HDFC_redirect_url', 'https://staging-v2.letsrecycle.co.in/mobile/v1/client/invoice/getPaymentResponce4mobile');
				$_params['cancel_url4mobile'] = env('HDFC_cancel_url', 'https://staging-v2.letsrecycle.co.in/mobile/v1/client/invoice/getPaymentResponce4mobile');
				$_params['language'] = "EN";
				$_params['invoice_id'] = $request->invoice_id;
				$_params['invoice_no'] = $request->invoice_no;
				return $_params;
			}
		}
	}

	public function initiatePayment(Request $request){
		$post = $this->loadPaymentRequestParams($request);					
		if (is_array($post) && count($post) > 0) {
	        $merchant_data='2';
	        foreach ($post as $key => $value){
	            $merchant_data.=$key.'='.$value.'&';
	        }
	        $encrypted_data=$this->hdfcencrypt($merchant_data,env('HDFC_WORKING_KEY', 'E065C110A0C130BD068C008B437B9EDC'));
	        $thirdPartyUrl = 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction';
			$form = "<form id='hdfcpaymentfrm' name='redirect' action='".$thirdPartyUrl."' method='POST'><input type='hidden' name='encRequest' value='".$encrypted_data."'><input type='hidden' name='access_code' value='".env('HDFC_ACCESS_CODE', 'AVKP44KL39CE91PKEC')."'><input type='submit'></form>";
			$data = WmInvoicePaymentLog::saveRequest($request, $post,$encrypted_data,$form);
			if($data) {
				$code = SUCCESS;
				return response()->json(['code'=>$code,'data'=>$form]); 
			}
		}
		$code = INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code]);   
	}

	public function initiatePayment4Mobile(Request $request){
		$post = $this->loadPaymentRequestParams($request);					
		if (is_array($post) && count($post) > 0) {
	        $merchant_data='2';
	        foreach ($post as $key => $value){
	            $merchant_data.=$key.'='.$value.'&';
	        }

	        $encrypted_data=$this->hdfcencrypt($merchant_data,env('HDFC_WORKING_KEY', 'E065C110A0C130BD068C008B437B9EDC'));

	        $thirdPartyUrl = 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction';

	        $callbackData = array();
	        $callbackData['enc_val'] = $encrypted_data; 
	        $callbackData['redirect_url'] = $post['redirect_url4mobile']; 
	        $callbackData['cancel_url'] = $post['cancel_url4mobile']; 
	        $callbackData['invoice_id'] = $post['invoice_id']; 
	        $callbackData['invoice_no'] = $post['invoice_no']; 
	        $callbackData['order_id'] = $post['order_id']; 
	        $callbackData['access_code'] = env('HDFC_ACCESS_CODE', 'AVKP44KL39CE91PKEC'); 
	        $callbackData['access_code'] = env('HDFC_ACCESS_CODE', 'AVKP44KL39CE91PKEC'); 
	        $callbackData['thirdPartyUrl'] = $thirdPartyUrl; 
	        $callbackData['last_call'] = 'sales/invoiceMaster/list'; /////// temporary hack for android side ansul 
				

	        $form = "<form id='hdfcpaymentfrm' name='redirect' action='".$thirdPartyUrl."' method='POST'><input type='hidden' name='encRequest' value='".$encrypted_data."'><input type='hidden' name='access_code' value='".env('HDFC_ACCESS_CODE', 'AVKP44KL39CE91PKEC')."'><input type='submit'></form>";

	        if(trim($thirdPartyUrl) != '') {
				$data = WmInvoicePaymentLog::saveRequest($request, $post,$encrypted_data,$form);
				if($data) {
					$code = SUCCESS;
					return response()->json(['code'=>$code,'data'=>$callbackData]); 
				}
			}

		}
		$code = INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code]);   
	}
 
	public function getResponce(Request $request){
		if($request->has('tid') && $request->has('order_id')) {
			$tid = $request->tid;
			$order_id = $request->order_id;
			$data = WmInvoicePaymentLog::getResponce($tid, $order_id);
			$msg        = ($data) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
			$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
		}
	}

	public function getPaymentResponce(Request $request){
		$encResponse=$request["encResp"];
		$rcvdString=$this->hdfcdecrypt($encResponse,env('HDFC_WORKING_KEY', 'E065C110A0C130BD068C008B437B9EDC'));
		$order_status="";
		$decryptValues=explode('&', $rcvdString);
		$createARRAY = array();
		foreach($decryptValues as $decryptValuesV) {
			$info=explode('=',$decryptValuesV);
			if(count($info) == 2) {
				$createARRAY[$info[0]] = $info[1];
			}
		}
		$data = WmInvoicePaymentLog::saveResponce($createARRAY);

		$id = base64_encode($createARRAY['order_id'].'@'.$createARRAY['tracking_id']);
		$id = urlencode($id);

		return redirect()->away('https://staging.letsrecycle.co.in/#/sales/invoiceMaster/list?id='.$id);
		//return redirect()->away('http://192.168.10.38:8000/#/sales/invoiceMaster/list?id='.$id);
		
		/*if($data) {
			$code = SUCCESS;
			return response()->json(['code'=>$code]);
		} else {
			$code = INTERNAL_SERVER_ERROR;
			return response()->json(['code'=>$code]);
		}*/
	}

	public function getPaymentResponce4mobile(Request $request){
		$encResponse=$request["encResp"];
		$rcvdString=$this->hdfcdecrypt($encResponse,env('HDFC_WORKING_KEY', 'E065C110A0C130BD068C008B437B9EDC'));
		$order_status="";
		$decryptValues=explode('&', $rcvdString);
		$createARRAY = array();
		foreach($decryptValues as $decryptValuesV) {
			$info=explode('=',$decryptValuesV);
			if(count($info) == 2) {
				$createARRAY[$info[0]] = $info[1];
			}
		}
		$data = WmInvoicePaymentLog::saveResponce($createARRAY);
		$code = SUCCESS;
		$msg = trans("message.RECORD_INSERTED");
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$createARRAY]);
	}

	public function getPaymentStatus(Request $request){
		if($request->has('id')) {
			$id = urldecode($request->id);
			$id = base64_decode($id);
			if (strpos($id, '@') !== false) {
				$ids = explode('@', $id);						
				$orderID = $ids[0];
				$tid = $ids[1];
				$paymentData = WmInvoicePaymentLog::getPaymentStatus($orderID, $tid);
				if(!empty($paymentData)) {
					$status = $paymentData['payment_responce_status'];
					$data = json_decode($paymentData['payment_responce'], true);					
					if(in_array($status, array('Success','Aborted','Failure'))) {
						if($status == 'Success') {
							$code = 200;
							return response()->json(['code'=>$code,'msg'=>'Payment Successful.','data' => $data]);
						} else if($status == 'Aborted') {
							$code = 403;
							return response()->json(['code'=>$code,'msg'=>'Payment Aborted.','data' => $data]);
						} else if($status == 'Failure') {
							$code = 500;
							return response()->json(['code'=>$code,'msg'=>'Payment Failed.']);
						}
					}
				}
			}
		}

		$code = INTERNAL_SERVER_ERROR;
		$msg = trans("message.SOMETHING_WENT_WRONG");
		return response()->json(['code'=>$code,'msg'=>$msg]);
	}


	/*
	Use     : Get Add payment Details
	Author  : Axay Shah
	Date    : 16 July,2019
	*/  
	public function AddPaymentReceive(Request $request){  
		$data       = WmPaymentReceive::AddPaymentReceive($request->all());
		$msg        = ($data) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Cancle Invoice
	Author  : Axay Shah
	Date    : 16 July,2019
	*/
	public function CancelInvoice(Request $request){
		$data = WmInvoices::CancelInvoice($request);
		$msg = ($data) ? trans("message.RECORD_UPDATED") : trans("message.NO_RECORD_FOUND");
		$code = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	}

	/*
	Use     : Payment History list
	Author  : Axay Shah
	Date    : 11 July,2019
	*/
	public function PaymentHistoryList(Request $request){
		$invoice_no = (isset($request->invoice_no) && !empty($request->invoice_no)) ? $request->invoice_no : 0;
		$invoice_id = (isset($request->invoice_id) && !empty($request->invoice_id)) ? $request->invoice_id : 0;
		$data       = WmPaymentReceive::PaymentHistoryList($invoice_no,$invoice_id);
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	}

	/*
	Use     :Search Way Bridge slip
	Author  : Axay Shah
	Date    : 24 July,2019
	*/
	public function SearchWayBridgeSlip(Request $request){
		$data       = WaybridgeSlipMaster::ListWayBridgeSlip($request);
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	}

	/*
	Use     : waybridge slip by id
	Author  : Axay Shah
	Date    : 24 July,2019
	*/
	public function GetWayBridgeById(Request $request){
		$id         = (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$data       = WaybridgeSlipMaster::GetById($id);
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	}

	/*
	Use     : List Invoice Data
	Author  : Axay Shah
	Date    : 08 July,2019
	*/
	public function createWayBridge(AddWayBridge $request){
		$data       = WaybridgeSlipMaster::createWayBridge($request);
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	}

	/*
	Use     : Generate Invoice PDF
	Author  : Axay Shah
	Date    : 29 Aug,2019
	*/
	public function GenerateWayBridgePDF(Request $request){
		$id         = (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$data       = WaybridgeSlipMaster::GenerateWayBridgePDF($id);
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	}

	/*
	Use     : Get Invoice PDF
	####THIS IS TEMPORORY COMMENTED BECAUSE WE ARE CHECKING FOR DIGITAL SIGNATURE###
	Author  : Axay Shah
	Date    : 19 Nov,2019
	*/
	public function GetInvoice(Request $request){
		$id         		= (isset($request->id) && !empty($request->id)) ? passdecrypt($request->id) : 0;
		$regenerated_flag   = (isset($request->regenerated_flag) && !empty($request->regenerated_flag)) ? passdecrypt($request->regenerated_flag) : 0;
		$data       = WmInvoices::GetById($id);
		$FROM_EPR 	= (isset($data['invoice_date']) && strtotime($data['invoice_date']) >= strtotime(EPR_DIGI_SIGNATURE_START_DATE)) ? 0 : 1;
		$pdf        = PDF::loadView('pdf.one',compact('data','FROM_EPR'));
		$pdf->setPaper("A4", "potrait");
		$timeStemp  = date("Y-m-d")."_".time().".pdf";
		$pdf->stream("one");
		if(DIGITAL_SIGNATURE_FLAG == 1 && $FROM_EPR == 0){
			########### NEW LOGIC TO PREVENT MULTIPLE HITS ON DIGITAL SIGNATURE - 25-11-2022 #############
			$DISPATCH = WmDispatch::find($data['dispatch_id']);
			if($DISPATCH){
				$TotalInvoiceAmount 		= WmSalesMaster::CalculateTotalInvoiceAmount($DISPATCH->id);
				$BILL_DISPLAY_GST_CODE 		= WmDepartment::where("id",$DISPATCH->bill_from_mrf_id)->leftjoin("view_city_state_contry_list","gst_state_id","=","gst_state_code_id")->value("display_state_code");
					$DEST_DISPLAY_GST_CODE 	= GSTStateCodes::where("id",$DISPATCH->destination_state_code)->value("display_state_code");
					############# BILL FROM AND DESTINATION GST STATE CODE CONDITION AS DISCUSS WITH ACCOUNT TEAM ##################
					if($TotalInvoiceAmount >= 0 && ($TotalInvoiceAmount > EWAY_BILL_MIN_AMOUNT || $DISPATCH->dispatch_type == NON_RECYCLEBLE_TYPE ) || (!empty($BILL_DISPLAY_GST_CODE) && !empty($DEST_DISPLAY_GST_CODE) && $BILL_DISPLAY_GST_CODE != $DEST_DISPLAY_GST_CODE)){
						$eway_flag = (!empty($DISPATCH->eway_bill_no) || $DISPATCH->approval_status != 1) ? false : true;
						if($eway_flag && empty($DISPATCH->eway_bill_no)){
							$fileName 		= "invoice_".$id.".pdf";
							$partialPath 	= PATH_DISPATCH;
							$fullPath 		= public_path(PATH_IMAGE.'/'.PATH_COMPANY."/".$data['company_id']."/").$partialPath;
							$url 			= url("/".PATH_IMAGE.'/'.PATH_COMPANY."/".$data['company_id'])."/".$partialPath."/".$fileName;
							if(!file_exists($fullPath."/".$fileName)){
								return $pdf->stream("invoice_".$id.".pdf",array("Attachment" => false));
							}
						}
					}
			}
			########### NEW LOGIC TO PREVENT MULTIPLE HITS ON DIGITAL SIGNATURE - 25-11-2022 #############
			$fileName 		= "invoice_".$id.".pdf";
			$partialPath 	= PATH_DISPATCH;
			$fullPath 		= public_path(PATH_IMAGE.'/'.PATH_COMPANY."/".$data['company_id']."/").$partialPath;
			$url 			= url("/".PATH_IMAGE.'/'.PATH_COMPANY."/".$data['company_id'])."/".$partialPath."/".$fileName;
			if(!file_exists($fullPath."/".$fileName) || $regenerated_flag == 1){
				$output 		= $pdf->output();
				if(!is_dir($fullPath)) {
					mkdir($fullPath,0777,true);
				}
				file_put_contents($fullPath."/".$fileName,$output);
				WmDispatch::DigitalSignature($fullPath."/".$fileName,$fullPath,$fileName);
			}
			header("Location: $url");
			exit;
		}else{
			return $pdf->stream("invoice_".$id.".pdf",array("Attachment" => false));
		}
		return $pdf->download("invoice_".$id.".pdf");
		$msg  = ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$url]);
	}

	/*
	Use     : Get BIll T Data
	Author  : Axay Shah
	Date    : 03 July,2020
	*/
	public function GetBillT(Request $request){
		$id         = (isset($request->id) && !empty($request->id)) ? passdecrypt($request->id) : 0;
		$data       = WmInvoices::GetById($id);
		$pdf        = PDF::loadView('pdf.billt',compact('data'));
		$pdf->setPaper("A4","landscape");
		$timeStemp  = date("Y-m-d")."_".time().".pdf";
		$pdf->stream("billt");
		return $pdf->download("billt_".$id.".pdf");
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$url]);
	}
	/*
	Use     : Add Reopen Invoice data
	Author  : Axay Shah
	Date    : 04 Dec,2019
	*/
	public function AddInvoiceApproval(Request $request){
		$code   = INTERNAL_SERVER_ERROR;
		$msg 	= "Reopen invoice functionality inactive from the software";
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>""]);
		$products   = (isset($request->product) && !empty($request->product)) ? $request->product : "" ;
		if(!empty($products)){
			$DispatchId 	= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : 0 ;
			$uniqeId     	= md5($DispatchId.uniqid());
			$challan_no   	= (isset($request->challan_no) && !empty($request->challan_no)) ? $request->challan_no : "" ;
			$productData 	= json_decode(json_encode($products),true);
			foreach($productData as $raw){
				$raw['unique_id'] 	= $uniqeId;
				$raw['dispatch_id'] = $DispatchId;
				$raw['challan_no'] 	= $challan_no;
				$raw['mrf_id']      = (isset($request->master_dept_id) && !empty($request->master_dept_id)) ? $request->master_dept_id : 0 ;
				$data               = InvoiceApprovalMaster::AddInvoiceApproval($raw);
			}
		}

		$data 		= 1;
		$msg        = ($data > 0) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : List Invoice Approval List
	Author  : Axay Shah
	Date    : 08 July,2019
	*/
	public function ListInvoiceApproval(Request $request){
		$data       = InvoiceApprovalMaster::ListInvoiceApproval($request);
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get Approval By id
	Author  : Axay Shah
	Date    : 08 July,2019
	*/
	public function GetById(Request $request){
		$unique_id   = (isset($request->unique_id) && !empty($request->unique_id)) ? $request->unique_id : "" ;
		$dispatch_id = (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : "" ;
		$data       = InvoiceApprovalMaster::GetById($unique_id,$dispatch_id);
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : First State Approval
	Author  : Axay Shah
	Date    : 03 Dec,2019
	*/
	public function FirstLevelApproval(Request $request){
		$unique_id  = (isset($request->unique_id) && !empty($request->unique_id)) ? $request->unique_id : "" ;
		$dispatch_id= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : "" ;
		$status   	= (isset($request->status) && !empty($request->status)) ? $request->status : 0 ;
		$data       = InvoiceApprovalMaster::FirstLevelApproval($unique_id,$dispatch_id,$status);
		$msg        = ($data) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Final Level Approval
	Author  : Axay Shah
	Date    : 03 Dec,2019
	*/
	public function FinalLevelApproval(Request $request){
		$unique_id  = (isset($request->unique_id) && !empty($request->unique_id)) ? $request->unique_id : "" ;
		$dispatch_id= (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : "" ;
		$status   	= (isset($request->status) && !empty($request->status)) ? $request->status : 0 ;
		$tallyDate 	= (isset($request->tally_date) && !empty($request->tally_date)) ? $request->tally_date : "" ;
		$tallyRefNo	= (isset($request->tally_ref_no) && !empty($request->tally_ref_no)) ? $request->tally_ref_no : "" ;
		$data       = InvoiceApprovalMaster::FinalLevelApproval($unique_id,$dispatch_id,$status,$tallyDate,$tallyRefNo);
		$msg        = ($data) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Cancle Invoice
	Author  : Axay Shah
	Date    : 16 July,2019
	*/
	public function EditInvoice(Request $request){
		$id 		= (isset($request->invoice_id) && !empty($request->invoice_id)) ? $request->invoice_id : 0;
		$rec 		= WmSalesMaster::EditInvoice($request->all());
		$data       = WmInvoices::GetById($id);
		$FROM_EPR 	= (isset($data['invoice_date']) && strtotime($data['invoice_date']) >= strtotime(EPR_DIGI_SIGNATURE_START_DATE)) ? 0 : 1;
		if(DIGITAL_SIGNATURE_FLAG == 1 && $FROM_EPR == 0)
		{
			$pdf        = PDF::loadView('pdf.one',compact('data','FROM_EPR'));
			$pdf->setPaper("A4", "potrait");
			$timeStemp  = date("Y-m-d")."_".time().".pdf";
			$pdf->stream("one");
			$fileName 		= "invoice_".$id.".pdf";
			$partialPath 	= PATH_DISPATCH;
			$fullPath 		= public_path(PATH_IMAGE.'/'.PATH_COMPANY."/".$data['company_id']."/").$partialPath;
			$url 			= url("/".PATH_IMAGE.'/'.PATH_COMPANY."/".$data['company_id'])."/".$partialPath."/".$fileName;
			$output 		= $pdf->output();
			if(!is_dir($fullPath)) {
				mkdir($fullPath,0777,true);
			}
			file_put_contents($fullPath."/".$fileName,$output);
			WmDispatch::DigitalSignature($fullPath."/".$fileName,$fullPath,$fileName);
		}
		$msg 	= ($rec) ? trans("message.RECORD_UPDATED") : trans("message.NO_RECORD_FOUND");
		$code 	= ($rec) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$rec]);
	}
	/*
	Use     : Get Invoice PDF
	Author  : Axay Shah
	Date    : 20 Jan,2021
	*/
	public function DownloadInvoice(Request $request){

		$Department = WmDepartment::where("status",1)->get();
		if(!empty($Department)){
			foreach($Department as $value){
				$Invoice = WmInvoices::where("invoice_status",0)->where("master_dept_id",$value->id)->whereBetween("invoice_date",array("2020-12-01","2020-12-31"))->get();
				if(!empty($Invoice)){
					foreach($Invoice as $raw){
						$data       = WmInvoices::GetById($raw->id);
						$pdf        = PDF::loadView('pdf.one',compact('data'));
						$pdf->setPaper("A4", "potrait");
						$timeStemp  = date("Y-m-d")."_".time().".pdf";
						$pdf->stream("one");
						$path = public_path("/")."MRF_VISE_INVOICE/".strtoupper(strtolower($value->department_name));
						if(!is_dir($path)) {
							mkdir($path,0777,true);
						}
						$fileName = str_replace('/', '_', $raw->invoice_no).".pdf";
						$pdf->save($path . '/' . $fileName);
					}
				}
			}
		}
	}
	
	/*
	Use     : Get Outstanding Ledger Report
	Author  : Axay Shah
	Date    : 03 Sep,2021
	*/
	public function OutStandingLedgerReport(Request $request){
		$data = WmInvoices::OutStandingLedgerReport($request);
		$msg = ($data) ? trans("message.RECORD_FOUND") : trans("message.NO_RECORD_FOUND");
		$code = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	}

	/*
	Use     : Get Analysis Report
	Author  : Axay Shah
	Date    : 06 Sep,2021
	*/
	public function GetAnalysisReport(Request $request){
		$report_type 	= (isset($request->report_type) && !empty($request->report_type)) ? $request->report_type : 0;
		if(!empty($report_type)){
			if ($report_type == 2) {
				$data =  WmInvoices::analysisreportByBaseLocation($request);
			} else {
				$data =  WmInvoices::analysisreportByMRF($request);
			}
			$msg 	= trans("message.RECORD_FOUND");
			$code 	= SUCCESS;
		}else{
			$msg 	= trans("message.NO_RECORD_FOUND");
			$code 	= INTERNAL_SERVER_ERROR;
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	}
	/*
	Use     : Generate Invoice PDF
	Author  : Axay Shah
	Date    : 29 Aug,2019
	*/
	public function PrintWayBridgePDF(Request $request){
		$id   = (isset($request->id) && !empty($request->id)) ? passdecrypt($request->id) : 0;
		$data = WaybridgeSlipMaster::GenerateWayBridgePDF($id);
		return $data;
	}
}
