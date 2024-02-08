<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\CompanyPriceGroupMaster;
use App\Http\Requests\CompanyPriceGroupAdd;
use App\Http\Requests\CompanyPriceGroupUpdate;

class CompanyPriceGroupMasterController extends LRBaseController
{
   /*
	Use     : List all master price group
	Author  : Axay Shah 
	Date    : 27 Sep,2018
	*/
	public function list(Request $request){
		$msg        = trans('message.RECORD_FOUND');
		try {
			$data = CompanyPriceGroupMaster::listPriceGroup($request);
			if(!$data){
				$msg = trans('message.RECORD_NOT_FOUND');
			}
		}
		catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage().$e,"data"=>""]);
		}
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/*
	Use     : List all master price group
	Author  : Axay Shah 
	Date    : 27 Sep,2018
	*/
	public function priceGroupByCustomer(Request $request){
		$msg        	= trans('message.RECORD_FOUND');
		try {
			$cityId 	= (isset($request->city) && !empty($request->city)) ? $request->city : 0;
			$data 		= CompanyPriceGroupMaster::priceGroupByCustomer($cityId);
			if(!$data){
				$msg = trans('message.RECORD_NOT_FOUND');
			}
		}
		catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage().$e,"data"=>""]);
		}
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}


	/*
	Use     : Create master price group
	Author  : Axay Shah 
	Date    : 27 Sep,2018
	*/
	public function create(CompanyPriceGroupAdd $request){
		$data = CompanyPriceGroupMaster::add($request);
		($data) ? $msg = trans('message.RECORD_INSERTED') : $msg = trans('message.SOMETHING_WENT_WRONT');
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}
	/*
	Use     : update master price group
	Author  : Axay Shah 
	Date    : 27 Sep,2018
	*/
	public function update(CompanyPriceGroupUpdate $request){
	   $data = CompanyPriceGroupMaster::updateRecord($request);
	   ($data) ? $msg = trans('message.RECORD_UPDATED') : $msg = trans('message.SOMETHING_WENT_WRONT');
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>""]);
	}
	/*
	Use     : Get Price Group by its id
	Author  : Axay Shah 
	Date    : 27 Sep,2018
	*/
	public function getById(Request $request){
		$data =  CompanyPriceGroupMaster::find($request->price_group_id);
		($data) ? $msg =  trans('message.RECORD_FOUND') : $msg =  trans('message.RECORD_NOT_FOUND');
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}
	/*
	Use     : GET DEFAULT PRICE GROUP OF CITY
	Author  : Axay Shah 
	Date    : 27 Sep,2018
	*/
	public function priceGroupByCompany(Request $request){
		$data 	= array();
		$cityId = (isset($request->city) && !empty($request->city)) ?  $request->city :0; 
		if(isset($request->from_report) && !empty($request->from_report)){
			$data = CompanyPriceGroupMaster::priceGroupByCompany();
		}else{
			$data = CompanyPriceGroupMaster::priceGroupByCompany($cityId);
		}
		(count($data)>0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/**
	* Use       :   Change price group status
	* Author    :   Axay Shah
	* Date      :   29 Sep,2018
	*/
	public function changeStatus(Request $request){
		$msg            = trans('message.RECORD_NOT_FOUND');
		$changeStatus   = "";
		if(isset($request->id) && isset($request->status)){
			$changeStatus   = CompanyPriceGroupMaster::changeStatus($request->id,$request->status); 
			if(!empty($changeStatus)){
				LR_Modules_Log_CompanyUserActionLog($request,$request->id);
				$msg        = trans('message.STATUS_CHANGED');
			}
		}
		return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $changeStatus]);
	}
}
