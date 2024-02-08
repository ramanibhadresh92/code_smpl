<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmServiceMaster;
use App\Models\WmServiceProductMaster;
use App\Models\Parameter;
use App\Http\Requests\AddService;
use App\Http\Requests\AddServiceByEPR;
use Validator;
use Log;
use App\Models\WmDispatch;
class ServiceController extends LRBaseController
{
	/*
	Use 	: Save Service Details
	Author 	: Upasana
	Date 	: 04 March 2021
	*/
	public function SaveServiceDetails(AddService $request){
		$data 		= WmServiceMaster::SaveService($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Get Service Details List
	Author 	: Upasana
	Date 	: 04 March 2021
	*/
	public function ServiceDetailsList(Request $request){
		$data = WmServiceMaster::GetServiceDetailsList($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Get Service Details List
	Author 	: Upasana
	Date 	: 04 March 2021
	*/
	// public function ApproveServiceRequest(Request $request){
	// 	$data = WmServiceMaster::ApproveServiceRequest($request);
	// 	$msg  = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
	// 	return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	// }

	public function ApproveServiceRequest(Request $request){
		$code 		= (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$data = WmServiceMaster::ApproveServiceRequest($request);
		if(isset($data["res_from_auto_einvoice"])){
			if($data["res_from_auto_einvoice"] == 1){
				$data 	= $data['res_result'];
				$code   =  INTERNAL_SERVER_ERROR;
	            if(!empty($data["ErrorDetails"])){
	                $i = 0;
	                foreach($data["ErrorDetails"] as $value){
	                    $msg = "Service Invoice Generated. <h4 class='text-danger'>Error in Einvoice - ".$value['ErrorMessage'].'</h4>';
	                    $i++;
	                }
	            }
	            if(empty($msg)){
	            	$msg    =  trans("message.RECORD_UPDATED");
	            	$data   = "";
	            }
	        }else{
				$code 	= (isset($data['res_result']) && $data['res_result'] == true) ?  SUCCESS : INTERNAL_SERVER_ERROR;
				$msg 	= (isset($data['res_result']) &&  $data['res_result'] == true) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
			}
		}
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use 	: Get Service Details List
	Author 	: Upasana
	Date 	: 04 March 2021
	*/
	public function ServiceReport(Request $request){
		$data = WmServiceMaster::ServiceReport($request);
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update e invoice no
	Author  : Axay Shah
	Date 	: 31 March,2021
	*/
	public function UpdateEinvoiceNo(Request $request){
		$dispatch_id 			= (isset($request->id) && !empty($request->id)) ? $request->id : "";
		$e_invoice_no 			= (isset($request->irn) && !empty($request->irn)) ? $request->irn : "";
		$acknowledgement_date 	= (isset($request->ack_date) && !empty($request->ack_date)) ? $request->ack_date : "";
		$acknowledgement_no 	= (isset($request->ack_no) && !empty($request->ack_no)) ? $request->ack_no : "";
		$data 					= WmServiceMaster::UpdateEinvoiceNo($dispatch_id,$e_invoice_no,$acknowledgement_no,$acknowledgement_date);
		$msg 					= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		$code 					= ($data) ? SUCCESS : SUCCESS;
		if($data == true){
			// LR_Modules_Log_CompanyUserActionLog($request,$id);
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Get By ID
	Author  : Axay Shah
	Date 	: 26 April,2021
	*/
	public function GetByID(Request $request){
		$id 	= (isset($request->id) && !empty($request->id)) ? $request->id : "";
		$data 	= WmServiceMaster::GetById($id);
		$msg 	= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code 	= ($data) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Get Service Details List
	Author 	: Upasana
	Date 	: 18 May 2021
	*/
	public function ServiceProductList(Request $request){
		$data = WmServiceProductMaster::ServiceProductList($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Get Service Details List
	Author 	: Upasana
	Date 	: 18 May 2021
	*/
	public function GetServiceType(Request $request){
		$data = Parameter::parentDropDown(PARA_SERVICE_TYPE)->get();
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: AddServiceInoviceByEPR
	Author 	: Kalpak Prajapati
	Date 	: 24 November 2021
	*/
	public function AddServiceInoviceByEPR(AddServiceByEPR $request)
	{
		$WmServiceMaster 	= new WmServiceMaster;
		$data 				= $WmServiceMaster->AddServiceInoviceByEPR($request);
		$msg 				= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use 	: Upload Service Signature invoice
	Author 	: Hasmukhi Patel
	Date 	: 24 Jan 2022
	*/
	public function uploadServiceInvoice(Request $request){
		$service_id = (isset($request->service_id) && (!empty($request->service_id)) ? $request->service_id : 0);
		$data 		= WmServiceMaster::uploadInvoice($request,$service_id);
		$msg 		= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Generate Service Invoice
	Author 	: Axay Shah
	Date 	: 28 September 2022
	*/
	public function GenerateServiceInvoice(Request $request)
	{
		$id         		= (isset($request->id) && !empty($request->id)) ? passdecrypt($request->id) : 0;
		$regenerated_flag   = (isset($request->regenerated_flag) && !empty($request->regenerated_flag)) ? passdecrypt($request->regenerated_flag) : 0;
		$name 	= "service_invoice_".$id;
		$data 	= \App\Models\WmServiceMaster::GetById($id);
		if(DIGITAL_SIGNATURE_FLAG == 1)
		{
			
			$partialPath 	= PATH_SERVICE."/".$id;
			$fullPath 		= public_path(PATH_IMAGE.'/').$partialPath;
			$url 			= url('/')."/".PATH_IMAGE.'/'.$partialPath."/".$name.".pdf";
			// if(!file_exists(public_path("/")."/".PATH_IMAGE.'/'.$partialPath."/".$name.".pdf") || $regenerated_flag == 1) {
			if(!file_exists(public_path("/")."/".PATH_IMAGE.'/'.$partialPath."/".$name.".pdf")) {
				$array 	= array("data"=> $data);
				if(isset($data->is_tradex) && $data->is_tradex == 1){
					$pdf 	= \PDF::loadView('service.tradex_service_invoice', $array);	
				}else{
					$pdf 	= \PDF::loadView('service.invoice', $array);	
				}
				$pdf->setPaper("A4", "potrait");
				$output = $pdf->output();
				if(!is_dir($fullPath)) {
					mkdir($fullPath,0777,true);
	            }
				file_put_contents($fullPath."/".$name.".pdf",$output);
				WmDispatch::DigitalSignature($fullPath."/".$name.".pdf",$fullPath,$name.".pdf");
			}else {
				$data 	= \App\Models\WmServiceMaster::GetById($id);
				$array 	= array("data"=> $data);
				if(isset($data->is_tradex) && $data->is_tradex == 1){
					$pdf 	= \PDF::loadView('service.tradex_service_invoice', $array);	
				}else{
					$pdf 	= \PDF::loadView('service.invoice', $array);	
				}
				$pdf->setPaper("A4", "potrait");
				$output = $pdf->output();
				if(!is_dir($fullPath)) {
					mkdir($fullPath,0777,true);
	            }
				file_put_contents($fullPath."/".$name.".pdf",$output);
				WmDispatch::DigitalSignature($fullPath."/".$name.".pdf",$fullPath,$name.".pdf");
			}
			header("Location: $url");
			exit;
		} else {
			$data 	= \App\Models\WmServiceMaster::GetById($id);
			$array 	= array("data"=> $data);
			if(isset($data->is_tradex) && $data->is_tradex == 1){
				$pdf 	= \PDF::loadView('service.tradex_service_invoice', $array);	
			}else{
				$pdf 	= \PDF::loadView('service.invoice', $array);	
			}
			$pdf->setPaper("A4", "potrait");
			file_put_contents($fullPath."/".$name.".pdf",$output);
			WmDispatch::DigitalSignature($fullPath."/".$name.".pdf",$fullPath,$name.".pdf");
		}
		header("Location: $url");
			exit;
	}
	/*
	Use 	: Download Service invoice Without Digital Signature
	Author 	: Hardyesh Gupta
	Date 	: 12 Sep 2023
	*/
	public function DownloadServiceInvoiceWithoutDigitalSignature($id)
	{
		$name 			= "service_invoice".$id;
		$partialPath 	= PATH_SERVICE."/".$id;
		$fullPath 		= public_path(PATH_IMAGE.'/').$partialPath;
		$url 			= url('/')."/".PATH_IMAGE.'/'.$partialPath."/".$name.".pdf";
		$data 			= WmServiceMaster::GetById($id);
		$array 			= array("data"=> $data);
		$pdf 			= \PDF::loadView('service.invoice', $array);
		$pdf->setPaper("A4", "potrait");
		$output = $pdf->output();
		if(!is_dir($fullPath)) {
			mkdir($fullPath,0777,true);
        }
        file_put_contents($fullPath."/".$name.".pdf",$output);
        $url 	= (file_exists(public_path("/")."/".PATH_IMAGE.'/'.$partialPath."/".$name.".pdf")) ? $url : "";
        $msg  	= (!empty($url)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND"); 
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$url]);
	}
	/*
	Use 	: View And Save Specific Service Details
	Author 	: Hardyesh Gupta
	Date 	: 15 Sep 2023
	*/
	public function ViewSaveServiceDetails(Request $request){	
		$id         = (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$result 	= WmServiceMaster::ViewSaveServiceDetails($request);
		$name 	= "service_invoice_".$id;
		if(DIGITAL_SIGNATURE_FLAG == 1 && !empty($result))
		{
			$partialPath 	= PATH_SERVICE."/".$id;
			$fullPath 		= public_path(PATH_IMAGE.'/').$partialPath;
			$url 			= url('/')."/".PATH_IMAGE.'/'.$partialPath."/".$name.".pdf";
			$data 	= \App\Models\WmServiceMaster::GetById($id);
			$array 	= array("data"=> $data);
			$pdf 	= \PDF::loadView('service.invoice', $array);
			$pdf->setPaper("A4", "potrait");
			$output = $pdf->output();
			if(!is_dir($fullPath)) {
				mkdir($fullPath,0777,true);
            }
			file_put_contents($fullPath."/".$name.".pdf",$output);
			WmDispatch::DigitalSignature($fullPath."/".$name.".pdf",$fullPath,$name.".pdf");
		} 
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$result]);
	}

	/*
	Use 	: Tradex Service Invoice API
	Author 	: Hardyesh Gupta
	Date 	: 03 Oct 2023
	*/
	public function TradexServiceInvoiceAPI(Request $request)
	{
		try{
			$data 	= WmServiceMaster::TradexServiceInvoiceAPI($request);
			$msg  	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
			$code  	= (!empty($data)) ? SUCCESS : VALIDATION_ERROR ;  
			return response()->json(['code' =>$code , "msg"=>$msg,"data"=>$data]);
		}catch(\Exception $e){
			\Log::info("*************** TRADEX SERVICE INVOICE API RESPONSE***************".$e->getMessage()." ".$e->getLine()." ".$e->getFile());
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=>$e]);
		}	
	}

	/*
	Use 	: Tradex Service Invoice API
	Author 	: Hardyesh Gupta
	Date 	: 06 Oct 2023
	*/
	public function TradexServiceInvoiceGenerateAPI(Request $request)
	{
		try{
			$data 		= "";
			$msg 		=  trans("message.RECORD_NOT_FOUND");
			$code 		= VALIDATION_ERROR;
			$resultdata = WmServiceMaster::TradexServiceEInvoiceGenerateAPI($request);
			if(isset($resultdata)){
				$msg 		= $resultdata['message'];
				$code 		= $resultdata['code'];
				$data 		= $resultdata['data'];
				if(!empty($resultdata['data'])){
					$msg 	= (!empty($resultdata['message']))? $resultdata['message'] : trans("message.RECORD_FOUND");
					$code   = SUCCESS;
				}		
			}
		}catch(\Exception $e){
			\Log::info("*** TRADEX SERVICE INVOICE GENERATE API RESPONSE *****".$e->getMessage()." ".$e->getLine()." ".$e->getFile());
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=>$e]);
 			
		}	
	}
}
