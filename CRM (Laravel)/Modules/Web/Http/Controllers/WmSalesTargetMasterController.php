<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmSalesTargetMaster;
use App\Http\Requests\AddSalesTarget;
use PDF;
class WmSalesTargetMasterController extends LRBaseController
{
	/*
	Use     : List MRF for Sales Target
	Author  : Axay Shah
	Date    : 05 July,2021
	*/
	public function ListMRFForSalesTarget(Request $request)
	{
		$from_widget = (isset($request->custom) && !empty($request->custom)) ? $request->custom : 0;
		if($from_widget) {
			if(Auth()->user()->adminuserid == 1) {
				$data = WmSalesTargetMaster::getMRFWiseTargetV5($request->all());
			} else {
				$data = WmSalesTargetMaster::getMRFWiseTargetV5($request->all());
			}
		} else {
			$data = WmSalesTargetMaster::ListMRFForSalesTarget($request->all());
		}
		$msg  	= ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code 	= ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Save Sales Target
	Author  : Axay Shah
	Date    : 05 July,2021
	*/
	public function SaveSalesTarget(AddSalesTarget $request){
		$data 	= WmSalesTargetMaster::SaveSalesTarget($request->all());
		$msg  	= ($data) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code 	= ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Save Sales Target
	Author  : Axay Shah
	Date    : 05 July,2021
	*/
	public function ListSalesTarget(Request $request){
		$data 	= WmSalesTargetMaster::ListSalesTarget($request);
		$msg  	= ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code 	= SUCCESS;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
}