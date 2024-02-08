<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmAssetMaster;
use App\Models\WmDispatch;
use App\Http\Requests\AddAsset;
use Validator;
use Log;
class AssetController extends LRBaseController
{
	/*
	Use 	: Save Service Details
	Author 	: Upasana
	Date 	: 04 March 2021
	*/
	public function SaveAsset(AddAsset $request){
		$data 		= WmAssetMaster::SaveAsset($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Get Service Details List
	Author 	: Upasana
	Date 	: 04 March 2021
	*/
	public function AssetList(Request $request){
		$data 		= WmAssetMaster::AssetList($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Get Service Details List
	Author 	: Upasana
	Date 	: 04 March 2021
	*/
	public function AssetReport(Request $request){
		$data = WmAssetMaster::AssetReport($request);
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Get Service Details List
	Author 	: Upasana
	Date 	: 04 March 2021
	*/
	public function ApproveAssetRequest(Request $request){
		$data = WmAssetMaster::ApproveAssetRequest($request);
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
		$data 					= WmAssetMaster::UpdateEinvoiceNo($dispatch_id,$e_invoice_no,$acknowledgement_no,$acknowledgement_date);
		$msg 					= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		$code 					= ($data) ? SUCCESS : SUCCESS;
		if($data == true){
			LR_Modules_Log_CompanyUserActionLog($request,$request->id);
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
		$data 	= WmAssetMaster::GetById($id);
		$msg 	= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code 	= ($data) ? SUCCESS : SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/**
	* @uses Generate Asset Invoice
	* @param
	* @return 
	* @author Hardyesh Gupta
    * @since 16 March 2023
	*/
	public function PrintAssetInvoice($id)
	{
		$id 	= passdecrypt($id);
		//$name 	= "asset_invoice_".$id;
		$name 	= ASSET_FILE_PREFIX.$id;
		$data 	= WmAssetMaster::GetById($id);
		if(DIGITAL_SIGNATURE_FLAG == 1)
		{

			if($data->approval_status == '1'){

				$created_date 	= date_create($data->created_at);
		        $CheckAssetDate = date_create(ASSET_DATE_CHECK);
		        $AssetDateDiff 	= date_diff($CheckAssetDate,$created_date);

		        if($AssetDateDiff->format("%R%a") > 0){
		        	
		        	$partialPath 	= PATH_ASSET."/".$id;
					$fullPath 		= public_path(PATH_IMAGE.'/').$partialPath;
					$url 			= url('/')."/".PATH_IMAGE.'/'.$partialPath."/".$name.".pdf";
					// if(!file_exists(public_path("/")."/".PATH_IMAGE.'/'.$partialPath."/".$name.".pdf")) {
						
						$array 	= array("data"=> $data);
						$pdf 	= \PDF::loadView('service.asset_invoice', $array);
						$pdf->setPaper("A4", "potrait");
						$output = $pdf->output();
						if(!is_dir($fullPath)) {
							mkdir($fullPath,0777,true);
			            }
						file_put_contents($fullPath."/".$name.".pdf",$output);
						WmDispatch::DigitalSignature($fullPath."/".$name.".pdf",$fullPath,$name.".pdf");
					// }
					header("Location: $url");
					exit;	
		        }else {
					$array 	= array("data"=> $data);
					$pdf 	= \PDF::loadView('service.asset_invoice', $array);
					$pdf->setPaper("A4", "potrait");
					return $pdf->stream($name.".pdf",array("Attachment" => false));
				}
			}
		} else {
			$array 	= array("data"=> $data);
			$pdf 	= \PDF::loadView('service.asset_invoice', $array);
			$pdf->setPaper("A4", "potrait");
			return $pdf->stream($name.".pdf",array("Attachment" => false));
		}
	}
}
