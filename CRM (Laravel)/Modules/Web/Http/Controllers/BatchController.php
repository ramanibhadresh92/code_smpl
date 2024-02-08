<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmBatchMaster;
use App\Models\WmBatchAuditedProduct;
use App\Models\WmBatchProductDetail;
use App\Models\WmDispatchMediaMaster;
use App\Models\VehicleDocument;
use App\Models\WmBatchMediaMaster;
use App\Http\Requests\BatchAudit;
class BatchController extends LRBaseController
{
	/*
	Use     : Get batch list with filter & search
	Author  : Axay Shah
	Date    : 13 Mar 2019
	*/
	public function getBatchList(Request $request){
		return WmBatchMaster::getBatchList($request);
	}
	/*
	Use     : Get Audit collection data
	Author  : Axay Shah
	Date    : 13 Mar 2019
	*/
	public function getAuditCollectionData(Request $request){
		$result = WmBatchMaster::getAuditCollectionData($request);
		if (!empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
		}
	}
	/*
	Use     : Insert Batch audited product
	Author  : Axay Shah
	Date    : 13 Mar 2019
	*/
	public function insertBatchAuditedProduct(BatchAudit $request){
		$result =  WmBatchAuditedProduct::insertBatchAuditedProduct($request);
		if (!empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_INSERTED'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>array()]);
		}
	}
	/*
	Use     : Get batch collection
	Author  : Axay Shah
	Date    : 13 Mar 2019
	*/
	public function getBatchCollectionData(Request $request){
		$result =  WmBatchMaster::getBatchCollectionData($request);
		if (!empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_INSERTED'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>array()]);
		}
	}
	/*
	Use     : Insert purchese product details from batch product
	Author  : Axay Shah
	Date    : 14 Mar,2019
	*/
	public function insertBatchProductDetail(Request $request){
		$result =  WmBatchProductDetail::insertBatchProductDetail($request);
		if(!empty($result) && $result == '-1') {
			$msgarr = array("product_id" =>array(trans('message.PRODUCT_EXITS')) );
			return response()->json(['code'=>VALIDATION_ERROR,'msg'=>$msgarr,'data'=>$result]);
		}elseif(!empty($result)){
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.PRODUCT_ADDED'),'data'=>$result]);
		}else{
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>array()]);
		}
	}

	 /**
	* Function Name : GetCollectionDetails
	* @param Object $request
	* @return string
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Batch Report Details
	*/
	public function getBatchReportData(Request $request){
		$result =  WmBatchMaster::getBatchReport($request);
		if (!empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
		}
	}

	/*
	Use     : Batch Approval listing
	Author  : Axay Shah
	Date    : 01 April,2019
	*/
	public function batchApprovalList(Request $request){
		return $result =  WmBatchMaster::getBatchList($request);
	}
	/*
	Use     : Batch Approval listing (with DETAILS)
	Author  : Axay Shah
	Date    : 06 April,2019
	*/
	public function batchApprovalSingleList(Request $request){
		return $result =  WmBatchMaster::getBatchSingleList($request);
	}



	/*
	Use     : Update batch approval status
	Author  : Axay Shah
	Date    : 01 April,2019
	*/
	public function UpdateBatchStatus(Request $request){

		$result = false;
		if(isset($request->batch_id) && !empty($request->batch_id)  && isset($request->status)){
			$comment    = (isset($request->comment)) ? $request->comment : "";
			$result =  WmBatchMaster::UpdateBatchStatus($request->batch_id,$request->status,$comment);
			if ($result) {
				LR_Modules_Log_CompanyUserActionLog($request,$request->batch_id);
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_UPDATED'),'data'=>$result]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
			}
		}else{
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
		}
	}

	/*
	Use     : Batch Details
	Author  : Axay Shah
	Date    : 01 April,2019
	*/
	public function batchDetailsById(Request $request){
		$result = "";
		if(isset($request->batch_id) && !empty($request->batch_id)){
			$result =  WmBatchMaster::batchDetailsById($request->batch_id);
			if ($result) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
			}
		}else{
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
		}
	}

	/*
	Use     : Update Batch audit status
	Author  : Axay Shah
	Date    : 12 April,2019
	*/
	public function updateBatchAuditStatus(Request $request){
		/*NOW DRIVER RATING  ALSO UPDATE IN BATCH AUDIT - 18 FEB 2020*/
		if(isset($request->batch_id) && !empty($request->batch_id)){
			$rating 		= (isset($request->rating)) ? $request->rating : '';
			$ratingRemark 	= (isset($request->rating_remark)) ? $request->rating_remark : '';
			$result 		= WmBatchMaster::UpdateBatchAuditStatus($request->batch_id,'1',false,$rating,$ratingRemark);
			if ($result) {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_UPDATED'),'data'=>$result]);
			} else {
				return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
			}
		}else{
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>""]);
		}
	}

	/*
	Use     : GetBatchRealizationDetails
	Author  : Kalpak Prajapati
	Date    : 13 May,2019
	*/
	public function GetBatchRealizationDetails(Request $request){
		$result = WmBatchMaster::GetBatchRealizationDetails($request);
		if ($result) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
		}
	}

	/*
	Use     : CheckGrossWeightSleepUploaded
	Author  : Axay Shah
	Date    : 14 May,2019
	*/
	public function CheckGrossWeightSlipUploaded(Request $request){
		$batchId 	=	(isset($request->batch_id) && !empty($request->batch_id)) ? $request->batch_id : 0 ;
		$result 	=	array();
		if(!empty($batchId)){
			$result 	= 	WmBatchMaster::CheckGrossWeightSlipUploaded($batchId);
		}
		if (!empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
		}
	}

	/*
	Use     : Update Gross Weight Slip Status
	Author  : Axay Shah
	Date    : 14 May,2019
	*/
	public function MarkGrossWeightSlipStatus(Request $request){
		$batchId 	=	(isset($request->batch_id) && !empty($request->batch_id)) ? $request->batch_id : 0 ;
		$status 	=	(isset($request->gross_weight_slip_status) && !empty($request->gross_weight_slip_status)) ? $request->gross_weight_slip_status : 0 ;
		$comment 	=	(isset($request->gross_slip_comment) && !empty($request->gross_slip_comment)) ? $request->gross_slip_comment : "" ;
		$result 	=	array();
		if(!empty($batchId)){
			$result 	= 	WmBatchMaster::MarkGrossWeightSlipStatus($batchId,$status,$comment);
		}
		if($result) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_UPDATED'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
		}
	}

	/*
	Use     : Get Image List By its Module ID
	Author  : Axay Shah
	Date    : 03 Dec,2020
	*/
	public function GetImageListByID(Request $request){
		$type 		=	(isset($request->type) && !empty($request->type)) ? strtoupper(strtolower($request->type)) : "" ;
		$id 		=	(isset($request->id) && !empty($request->id)) ? $request->id : 0 ;
		$result 	=	array();

		if(!empty($type) && !empty($id)){
			switch ($type) {
				case 'DISPATCH': ## FOR DISPATCH
					$result = WmDispatchMediaMaster::GetAllImageByDispatchID($id);
					break;
				case 'VEHICLE': ## VEHICLE
					$result = VehicleDocument::GetVehicleDocumentImages($id);
					break;
				case 'BATCH': ## BATCH
					$result = WmBatchMediaMaster::GetBatchAllMedia($id);
					break;
				default:
					$result = array();
					break;
			}
		}
		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
	}
	/*
	Use     : GetCollectionPurchaseProductByBatch
	Author  : Axay Shah
	Date    : 13 Dec,2021
	*/
	public function GetCollectionPurchaseProductByBatch(Request $request){
		$batch_id 	=  (isset($request->batch_id) && !empty($request->batch_id)) ? $request->batch_id : 0;
		$result 	= WmBatchProductDetail::GetCollectionPurchaseProductByBatch($batch_id);
		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
	}
	/*
	Use     : Update Audit Qty
	Author  : Axay Shah
	Date    : 03 Auguest 2023
	*/
	public function updateAuditQty(Request $request){
		$product_details_id 	=  (isset($request->product_details_id) && !empty($request->product_details_id)) ? $request->product_details_id : 0;
		$qty 		=  (isset($request->qty) && !empty($request->qty)) ? $request->qty : 0;
		$result 	= WmBatchAuditedProduct::updateAuditQty($product_details_id,$qty);
		return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_UPDATED'),'data'=>$result]);
	}
}
