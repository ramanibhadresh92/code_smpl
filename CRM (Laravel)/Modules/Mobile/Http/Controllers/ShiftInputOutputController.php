<?php

namespace Modules\Mobile\Http\Controllers;
use Modules\Mobile\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Parameter;
use App\Models\ShiftTimingMaster;
use App\Models\WmProductMaster;
use App\Models\ShiftProductEntryMaster;
use App\Models\ShiftTimingApprovalMaster;
use App\Http\Requests\ShiftAdd;

class ShiftInputOutputController extends LRBaseController
{
	/**
	* Use       : Shift List Drop Down
	* Author    : Axay Shah
	* Date      : 19 May 2020
	*/
	public function ShiftList(Request $request)
	{
		$data = Parameter::parentDropDown(PARA_SHIFT_TYPE)->get();
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/**
	* Use       : Add Shift Timing
	* Author    : Axay Shah
	* Date      : 19 May 2020
	*/
	public function CreateShiftTiming(ShiftAdd $request)
	{

		$ID = (isset($request->id) && !empty($request->id)) ?  $request->id : 0;
		if(!empty($ID)){
			$data = ShiftTimingMaster::UpdateShiftTiming($request);
			$msg  = (!empty($data)) ?  trans("message.RECORD_UPDATED") : trans("message.SOMETHING_WENT_WRONG"); 
		}else{
			$data = ShiftTimingMaster::AddShiftTiming($request);
			$msg  = (!empty($data)) ?  trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		}
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}    

	/**
	* Use       : Shift Product List
	* Author    : Axay Shah
	* Date      : 19 May 2020
	*/
	public function ShiftProductList(Request $request)
	{
		$data = WmProductMaster::ShiftProductList($request);
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/**
	* Use       : Shift Data Listing
	* Author    : Axay Shah
	* Date      : 19 May 2020
	*/
	public function ListShiftTiming(Request $request)
	{
		$data = ShiftTimingMaster::ListShiftTiming($request,true);
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/**
	* Use       : Add Shift Product
	* Author    : Axay Shah
	* Date      : 19 May 2020
	*/
	public function AddShiftProduct(Request $request)
	{
		$data = ShiftProductEntryMaster::AddShiftProduct($request);
		($data) ? $msg = trans("message.RECORD_INSERTED") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}


	/**
	* Use       : Shift Input Output report
	* Author    : Axay Shah
	* Date      : 07 April 2020
	*/
	public function ShiftInputOutputReport(Request $request)
	{
		$data = ShiftTimingMaster::ShiftInputOutputReport($request);
		($data) ? $msg = trans("message.RECORD_INSERTED") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/**
	* Use       : Shift Product Qty by Shift timing id
	* Author    : Axay Shah
	* Date      : 19 May 2020
	*/
	public function ShiftProductTotalQty(Request $request)
	{
		$id 	= (isset($request->shift_timing_id) && !empty($request->shift_timing_id)) ? $request->shift_timing_id : 0 ;
		$data 	= ShiftProductEntryMaster::ShiftProductTotalQty($id);
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/**
	* Use       : Shift Approval listing
	* Author    : Axay Shah
	* Date      : 19 May 2020
	*/
	public function ListShiftTimingApproval(Request $request)
	{
		$data = ShiftTimingApprovalMaster::ListShiftTiming($request);
		(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}
	
}
