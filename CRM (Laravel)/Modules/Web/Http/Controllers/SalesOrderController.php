<?php
namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmDispatchPlan;
use App\Models\WmDispatch;
use App\Models\WmProductMaster;
use App\Http\Requests\AddSalesOrderPlan;
use App\Http\Requests\EditSalesOrderPlan;
class SalesOrderController extends LRBaseController
{
	/*
	Use     : Store Dispatch Plan
	Author  : Axay Shah
	Date 	: 14 September,2020
	*/
	public function ListDispatchPlan(Request $request){
		$data 		= WmDispatchPlan::ListDispatchPlan($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Store Dispatch Plan
	Author  : Axay Shah
	Date 	: 14 September,2020
	*/
	public function StoreDispatchPlan(AddSalesOrderPlan $request){
		$data 		= WmDispatchPlan::StoreDispatchPlan($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Dispatch Plan
	Author  : Axay Shah
	Date 	: 14 September,2020
	*/
	public function EditDispatchPlan(EditSalesOrderPlan $request){
		$data 		= WmDispatchPlan::EditDispatchPlan($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Dispatch Plan
	Author  : Axay Shah
	Date 	: 14 September,2020
	*/
	public function GetByIdDispatchPlan(Request $request){
		
		$plan_id 	= (isset($request->dispatch_plan_id) && !empty($request->dispatch_plan_id)) ? $request->dispatch_plan_id : 0;
		$data 		= WmDispatchPlan::GetByIdDispatchPlan($plan_id);
		$msg 		= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Change Approval Status
	Author  : Axay Shah
	Date 	: 15 September,2020
	*/
	public function ChangeApprovalStatus(Request $request){
		$data 		= WmDispatchPlan::ChangeApprovalStatus($request);
		$msg 		= ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	
	/*
	Use     : Get Product Client Rate
	Author  : Axay Shah
	Date 	: 04 November,2020
	*/
	public function GetSalesOrderClientRate(Request $request){
		$MRF_ID 		= (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id 		: 0;
		$ClientID		= (isset($request->client_id) && !empty($request->client_id)) ? $request->client_id : 0;
		$OriginID		= (isset($request->origin_id) && !empty($request->origin_id)) ? $request->origin_id : 0;
		$DispatchDate	= (isset($request->dispatch_date) && !empty($request->dispatch_date)) ? date("Y-m-d",strtotime($request->dispatch_date)) : "";
		$ProductID		= (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : 0;
		$data 			= WmProductMaster::GetSalesOrderClientRate($DispatchDate,$ProductID,$MRF_ID,$ClientID,$OriginID);
		$msg 			= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     	: Ready To Dispatch Report
	DevlopedBy  : Kalpak Prajapati 
	ConvertedBy : Axay Shah
	Date 		: 16 September,2021
	*/
	public function readyToDispach(Request $request){
		$data 		= array();
		$report_type = (isset($request->report_type) && !empty($request->report_type)) ? $request->report_type : 0;
		if ($report_type == 1) {
			$data 	= WmDispatch::readytodispachByMRF($request);
		}else{
			$data 	= WmDispatch::readytodispachByBaseLocation($request);
		}
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
}