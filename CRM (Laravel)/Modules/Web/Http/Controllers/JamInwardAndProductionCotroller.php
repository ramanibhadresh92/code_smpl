<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\JamInwardMaster;
use App\Models\JamProductionMaster;
class JamInwardAndProductionCotroller extends LRBaseController
{
	
	/*
	Use     : Add Jamnagar Inward data
	Author  : Axay Shah
	Date    : 25 April 2022
	*/
	public function AddJamInwardData(Request $request){
		$result = JamInwardMaster::AddInwardData($request->all());
		if (!empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_INSERTED'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>array()]);
		}
	}
	/*
	Use     : Insert Jamnagar production data
	Author  : Axay Shah
	Date    : 25 April 2022
	*/
	public function AddJamProductionData(Request $request){
		$result =  JamProductionMaster::AddProductionData($request);
		if (!empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_INSERTED'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.SOMETHING_WENT_WRONG'),'data'=>array()]);
		}
	}

	/*
	Use     : List Inward 
	Author  : Axay Shah
	Date    : 25 April 2022
	*/
	public function JamInwardList(Request $request){
		$result =  JamInwardMaster::JamInwardList($request->all());
		if (!empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
		}
	}
	/*
	Use     : List Inward 
	Author  : Axay Shah
	Date    : 25 April 2022
	*/
	public function JamProductionList(Request $request){
		$result =  JamProductionMaster::JamProductionList($request->all());
		if (!empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
		}
	}
	/*
	Use     : List Inward 
	Author  : Axay Shah
	Date    : 25 April 2022
	*/
	public function JamProductList(Request $request){
		$result =  JamProductionMaster::JamProductList($request->all());
		if (!empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
		}
	}
}
