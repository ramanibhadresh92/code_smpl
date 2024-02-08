<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmPaymentCollectionTargetMaster;
use App\Models\WmPaymentTargetCollectionDetails;
use App\Models\Parameter;
use App\Http\Requests\AddPaymentCollection;
use PDF;
class WmPaymentCollectionTargetMasterController extends LRBaseController
{
	/*
	Use     : List MRF for Sales Target
	Author  : Axay Shah
	Date    : 05 July,2021
	*/
	public function ListPaymentTarget(Request $request){
		$data       = WmPaymentCollectionTargetMaster::ListPaymentTarget($request);
		$msg        = ($data) ? trans("message.RECORD_FOUND") : trans("message.SOMETHING_WENT_WRONG");
		$code       = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Save Sales Target
	Author  : Axay Shah
	Date    : 05 July,2021
	*/
	public function SavePaymentTarget(Request $request){
		$data  = WmPaymentCollectionTargetMaster::SavePaymentTarget($request->all());
		$msg   = ($data) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code  = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Save Sales Target
	Author  : Axay Shah
	Date    : 05 July,2021
	*/
	public function AddPaymentCollectionDetails(AddPaymentCollection $request){
		$data  = WmPaymentTargetCollectionDetails::AddPaymentCollectionDetails($request->all());
		$msg   = ($data) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code  = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : vendor type list
	Author  : Axay Shah
	Date    : 05 July,2021
	*/
	public function PaymentVendorTypeList(Request $request){
		$data  = Parameter::parentDropDown(PARA_PAYMENT_VENDOR_TYPE)->get();
		$msg   = ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code  = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : WIDGET MASTER DATA
	Author  : Axay Shah
	Date    : 23 FEB,2022
	*/
	public function GetPaymentTargetWidget(Request $request){
		$data  = WmPaymentCollectionTargetMaster::GetPaymentTargetWidget($request);
		$msg   = ($data) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		$code  = ($data) ? SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}
}
