<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Classes\Einvoice;
use App\Models\WmDispatch;
use App\Models\WmServiceMaster;
use App\Models\WmAssetMaster;
use App\Models\WmInvoicesCreditDebitNotes;
use App\Models\JobWorkMaster;
use App\Models\WmTransferMaster;
use App\Models\WmServiceInvoicesCreditDebitNotes;
use App\Models\EinvoiceApiLogger;
use App\Models\WmInvoices;
use App\Http\Requests\EwayBillRequest;
use PDF;
class EinvoiceController extends LRBaseController
{
	/*
	Use     : Generate Eway Bill from Dispatch id
	Author  : Axay Shah
	Date    : 10 December,2020
	*/
	public function GenerateEinvoice(Request $request){
		$ID                 =  (isset($request->dispatch_id) && !empty($request->dispatch_id)) ? $request->dispatch_id : 0;
		$generate_eway_bill = (isset($request->generate_eway_bill) && !empty($request->generate_eway_bill)) ? $request->generate_eway_bill : false;
		if ($generate_eway_bill && isset(Auth()->user()->adminuserid)) {
			$Generated = $this->GenerateEwayBill($ID);
		}
		$code               = SUCCESS;
		$data               = WmDispatch::GenerateEInvoice($ID);
		$msg                = array();
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				$invoice_id = WmInvoices::where("dispatch_id",$ID)->value("id");
				if($invoice_id > 0){
					$data       = WmInvoices::GetById($invoice_id);
					$pdf        = PDF::loadView('pdf.one',compact('data'));
					$pdf->setPaper("A4", "potrait");
					$timeStemp  = date("Y-m-d")."_".time().".pdf";
					$pdf->stream("one");
					if(DIGITAL_SIGNATURE_FLAG == 1){
						$fileName       = "invoice_".$invoice_id.".pdf";
						$partialPath    = PATH_DISPATCH;
						$fullPath       = public_path(PATH_IMAGE.'/'.PATH_COMPANY."/".$data['company_id']."/").$partialPath;
						$url            = url("/".PATH_IMAGE.'/'.PATH_COMPANY."/".$data['company_id'])."/".$partialPath."/".$fileName;
						$output         = $pdf->output();
						if(!file_exists($fullPath."/".$fileName)){
							// mkdir($fullPath,0777,true);
						}
						file_put_contents($fullPath."/".$fileName,$output);
						WmDispatch::DigitalSignature($fullPath."/".$fileName,$fullPath,$fileName);

					}
				}
				$msg    = trans("message.EINVOICE_GENERATE_SUCCESFULLY");
			}
		}
	   	return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Generate Eway Bill from Dispatch id
	Author  : Kalpak Prajapati
	Date    : 25 November,2022
	*/
	public function GenerateEwayBill($ID=0)
	{
		$Result     = WmDispatch::GenerateEwayBillFromDispatch($ID);
		if($Result['code'] == SUCCESS) {
			$arrResult['code']  = SUCCESS;
			$arrResult['msg']   = "";
		} else {
			$arrResult['code']  = INTERNAL_SERVER_ERROR;
			$msg                = array();
			if(isset($Result["ErrorDetails"]) && !empty($Result["ErrorDetails"])) {
				$i = 0;
				foreach($Result["ErrorDetails"] as $ErrorDetails) {
					$msg[$i] = $ErrorDetails['ErrorMessage'];
					$i++;
				}
			} else {
				$msg[0] = trans("message.SOMETHING_WENT_WRONG");
			}
			$arrResult['msg'] = $msg;
		}
		return $arrResult;
	}
	/*
	Use     : Cancel E invoice From Dispatch
	Author  : Axay Shah
	Date    : 10 December,2020
	*/
	public function CancelEInvoice(Request $request){
		$code       = SUCCESS;
		$data       = WmDispatch::CancelEinvoice($request->all());
		$msg        = "";
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				 $msg    = trans("message.EINVOICE_CANCEL_SUCCESFULLY");
			}
		}
		// $msg        = (!empty($data)) ?
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Generate Eway Bill from Dispatch id
	Author  : Axay Shah
	Date    : 11 December,2020
	*/
	public function CancelEinvoiceReasons(Request $request){
		$data       = WmDispatch::CancelEinvoiceReasons();
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;

		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Generate e invoice for service
	Author  : Axay Shah
	Date    : 10 December,2020
	*/
	public function GenerateServiceEinvoice(Request $request){
		$ID         =  (isset($request->service_id) && !empty($request->service_id)) ? $request->service_id : 0;
		$code       = SUCCESS;
		$data       = WmServiceMaster::GenerateServiceEinvoice($ID);
		$msg        = array();
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				$name   = "service_invoice_".$ID;
				$data   = \App\Models\WmServiceMaster::GetById($ID);
				$array  = array("data"=> $data);
				$pdf    = \PDF::loadView('service.invoice', $array);
				$pdf->setPaper("A4", "potrait");
				if(DIGITAL_SIGNATURE_FLAG == 1){
					$partialPath    = PATH_SERVICE."/".$ID;
					$fullPath       = public_path(PATH_IMAGE.'/').$partialPath;
					$url            = url('/')."/".PATH_IMAGE.'/'.$partialPath."/".$name.".pdf";
					$output         = $pdf->output();
					if(!is_dir($fullPath)) {
						mkdir($fullPath,0777,true);
					}
					file_put_contents($fullPath."/".$name.".pdf",$output);
					WmDispatch::DigitalSignature($fullPath."/".$name.".pdf",$fullPath,$name.".pdf");
					########## STORE LOG OF DISPATCH TO SEND EMAIL TO CLIENT IN BACKGROUD ###########
					WmServiceMaster::StoreServiceInvoiceEmailSentLog($ID);
					########## STORE LOG OF DISPATCH TO SEND EMAIL TO CLIENT IN BACKGROUD ###########
				}
				$msg    = trans("message.EINVOICE_GENERATE_SUCCESFULLY");
			}
		}
		// $msg        = (!empty($data)) ?
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	 /*
	Use     : cancel serivce e invoice
	Author  : Axay Shah
	Date    : 20 April,2021
	*/
	public function CancelServiceEInvoice(Request $request){
		$code       = SUCCESS;
		$data       = WmServiceMaster::CancelServiceEInvoice($request->all());
		$msg        = "";
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				 $msg    = trans("message.EINVOICE_CANCEL_SUCCESFULLY");
			}
		}
		// $msg        = (!empty($data)) ?
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Generate Asset e invoice
	Author  : Axay Shah
	Date    : 20 April,2021
	*/
	public function GenerateAssetEinvoice(Request $request){
		$ID         =  (isset($request->asset_id) && !empty($request->asset_id)) ? $request->asset_id : 0;
		$code       = SUCCESS;
		$data       = WmAssetMaster::GenerateAssetEinvoice($ID);
		$msg        = array();
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				$msg    = trans("message.EINVOICE_GENERATE_SUCCESFULLY");


			}
		}
		// $msg        = (!empty($data)) ?
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	 /*
	Use     : Cancel e invoice for asset
	Author  : Axay Shah
	Date    : 20 April,2021
	*/
	public function CancelAssetEInvoice(Request $request){
		$code       = SUCCESS;
		$data       = WmAssetMaster::CancelAssetEInvoice($request->all());
		$msg        = "";
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				 $msg    = trans("message.EINVOICE_CANCEL_SUCCESFULLY");
			}
		}
		// $msg        = (!empty($data)) ?
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Generate e invoice for service
	Author  : Axay Shah
	Date    : 10 December,2020
	*/
	public function GenerateCreditDebitEinvoice(Request $request){
		$ID         =  (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$INVOICEID  =  (isset($request->invoice_id) && !empty($request->invoice_id)) ? $request->invoice_id : 0;
		$code       = SUCCESS;
		$data       = WmInvoicesCreditDebitNotes::GenerateCreditDebitEinvoice($ID,$INVOICEID);
		$msg        = array();
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				$msg    = trans("message.EINVOICE_GENERATE_SUCCESFULLY");


			}
		}
		// $msg        = (!empty($data)) ?
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	 /*
	Use     : cancel serivce e invoice
	Author  : Axay Shah
	Date    : 20 April,2021
	*/
	public function CancelCreditDebitEInvoice(Request $request){
		$code       = SUCCESS;
		$data       = WmInvoicesCreditDebitNotes::CancelEInvoice($request->all());
		$msg        = "";
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				 $msg    = trans("message.EINVOICE_CANCEL_SUCCESFULLY");
			}
		}
		// $msg        = (!empty($data)) ?
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	 /*
	Use     : Generate e invoice for Jobwork
	Author  : Axay Shah
	Date    : 12 July,2021
	*/
	public function GenerateJobworkEinvoice(Request $request){
		$ID         =  (isset($request->jobwork_id) && !empty($request->jobwork_id)) ? $request->jobwork_id : 0;
		$code       = SUCCESS;
		$data       = JobWorkMaster::GenerateJobworkEinvoice($ID);
		$msg        = array();
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				LR_Modules_Log_CompanyUserActionLog($request,$request->jobwork_id);
				$msg    = trans("message.EINVOICE_GENERATE_SUCCESFULLY");


			}
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	 /*
	Use     : cancel Jobwork  e invoice
	Author  : Axay Shah
	Date    : 12 July,2021
	*/
	public function CancelJobworkEInvoice(Request $request){
		$code       = SUCCESS;
		$data       = JobWorkMaster::CancelEInvoice($request->all());
		$msg        = "";
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				 $msg    = trans("message.EINVOICE_CANCEL_SUCCESFULLY");
			}
		}
		// $msg        = (!empty($data)) ?
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Generate e invoice for Jobwork
	Author  : Axay Shah
	Date    : 12 July,2021
	*/
	public function GenerateTransferEinvoice(Request $request){
		$ID         =  (isset($request->transfer_id) && !empty($request->transfer_id)) ? $request->transfer_id : 0;
		$code       = SUCCESS;
		$data       = WmTransferMaster::GenerateTransferEinvoice($ID);
		$msg        = array();
		try{
			if(!empty($data)){
				if($data["Status"] == 0){
					$code   =  INTERNAL_SERVER_ERROR;
					if(!empty($data["ErrorDetails"])){
						$i = 0;
						foreach($data["ErrorDetails"] as $value){
							$msg[$i] = $value['ErrorMessage'];
							$i++;
						}
					}
					if(empty($msg))
					$msg    =  trans("message.SOMETHING_WENT_WRONG");
					$data   = "";
				}else{
					$msg    = trans("message.EINVOICE_GENERATE_SUCCESFULLY");
				}
			}
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
		}catch(\Exception $e){
			\Log::info("#############".$e->getMessage()." ".$e->getFile()." ".$e->getLine());
		}
		
	}

	 /*
	Use     : cancel Jobwork  e invoice
	Author  : Axay Shah
	Date    : 12 July,2021
	*/
	public function CancelTransferEInvoice(Request $request){
		$code       = SUCCESS;
		$data       = WmTransferMaster::CancelTransferEinvoice($request->all());
		$msg        = "";
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				 $msg    = trans("message.EINVOICE_CANCEL_SUCCESFULLY");
			}
		}
		// $msg        = (!empty($data)) ?
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}/*
	Use     : Generate e invoice for Jobwork
	Author  : Axay Shah
	Date    : 12 July,2021
	*/
	public function GenerateCreditDebitNotesEinvoice(Request $request){
		$ID         =  (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$code       = SUCCESS;
		$data       = WmServiceInvoicesCreditDebitNotes::GenerateServiceCreditDebitEinvoice($ID);
		$msg        = array();
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				$msg    = trans("message.EINVOICE_GENERATE_SUCCESFULLY");


			}
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	 /*
	Use     : cancel Jobwork  e invoice
	Author  : Axay Shah
	Date    : 12 July,2021
	*/
	public function CancelCreditDebitNotesEinvoice(Request $request){
		$code       = SUCCESS;
		$data       = WmServiceInvoicesCreditDebitNotes::CancelServiceCreditDebitEInvoice($request->all());
		$msg        = "";
		if(!empty($data)){
			if($data["Status"] == 0){
				$code   =  INTERNAL_SERVER_ERROR;
				if(!empty($data["ErrorDetails"])){
					$i = 0;
					foreach($data["ErrorDetails"] as $value){
						$msg[$i] = $value['ErrorMessage'];
						$i++;
					}
				}
				if(empty($msg))
				$msg    =  trans("message.SOMETHING_WENT_WRONG");
				$data   = "";
			}else{
				 $msg    = trans("message.EINVOICE_CANCEL_SUCCESFULLY");
			}
		}
		// $msg        = (!empty($data)) ?
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
}
