<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\WmDepartment;
use App\Models\MrfShiftTimingMaster;
use App\Http\Requests\DepartmentAdd;
use App\Http\Requests\DepartmentUpdate;
use App\Models\WmReadyForDispatchMaster;
use App\Models\WmPlantProcessingCost;
class DepartmentController extends LRBaseController
{


	/*
	Use     : List Client for sales module & serach filter
	Author  : Axay Shah
	Date    :
	*/
	public function ListDepartment(Request $request){
		$data   = WmDepartment::ListDepartment($request);
		$msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}


	/*
	Use     : Add Department
	Author  : Axay Shah
	Date    : 03 July,2019
	*/
	public function AddDepartment(DepartmentAdd $request){
		$data       = WmDepartment::AddDepartment($request->all());
		$msg        = (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.SOMETHING_WENT_WRONG");
		$code       = (!empty($data)) ?  SUCCESS : INTERNAL_SERVER_ERROR;
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Department
	Author  : Axay Shah
	Date    : 03 July,2019
	*/
	public function UpdateDepartment(DepartmentUpdate $request){
		$data       = WmDepartment::UpdateDepartment($request->all());
		$msg        = ($data) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get Department By Id
	Author  : Axay Shah
	Date    : 26 June,2019
	*/
	public function GetDepartmentById(Request $request){
		$id         = (isset($request->id) && !empty($request->id)) ? $request->id : 0 ;
		$data       = WmDepartment::GetDepartmentById($id);
		$msg        = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	 /**
	* Use       : Add Shift Timing
	* Author    : Axay Shah
	* Date      : 21 May 2020
	*/
	public function CreateMRFShift(Request $request)
	{
		$data = MrfShiftTimingMaster::AddMrfShift($request);
		$msg  = (!empty($data)) ?  trans("message.RECORD_UPDATED") : trans("message.SOMETHING_WENT_WRONG");
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
	}

	/*
	Use     : List Shift for MRF
	Author  : Axay Shah
	Date    : 21 May,2020
	*/
	public function ListMRFShift(Request $request){
		$data   = MrfShiftTimingMaster::ListMRFShift($request);
		$msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	 /*
	Use     : List Ready For Dispatch
	Author  : Axay Shah
	Date    : 26 June 2020
	*/
	public function ListReadyForSales(Request $request){
		$data   = WmReadyForDispatchMaster::ListReadyForSales($request);
		$msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Add Ready For Dispatch
	Author  : Axay Shah
	Date    : 26 June,2020
	*/
	public function AddReadyForDispatch(Request $request){
		$data   = WmReadyForDispatchMaster::CreateReadyForDispatch($request);
		$msg    = (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	 /*
	Use     :
	Author  : Axay Shah
	Date    : 26 June,2020
	*/
	public function GetDeparmentByScreenID(Request $request){
		$data   = WmDepartment::GetDeparmentByScreenID($request);
		
		$msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	* Function Name : GetDepartmentCostHistory
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-09-30
	*/
	public function GetDepartmentCostHistory(Request $request) {
		$MRFID 	= isset($request->mrf_id)?$request->mrf_id:0;
		$data   = WmPlantProcessingCost::GetDepartmentCostHistory($MRFID);
		$msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	* Function Name : SavesDepartmentCostHistory
	* @param object $request
	* @author Kalpak Prajapati
	* @since 2022-09-30
	*/
	public function SavesDepartmentCostHistory(Request $request) {
		$data   = WmPlantProcessingCost::SavesDepartmentCostHistory($request);
		$msg    = (!empty($data) && strtolower($data) == "new")?trans("message.RECORD_INSERTED") : trans("message.RECORD_UPDATED");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use     : Get Department By BaseLocation
	Author  : Hardyesh Gupta
	Date    : 05-09-2023
	*/
	public function GetDepartmentByBaseLocation(Request $request){
		$data   = WmDepartment::GetDepartmentByBaseLocation($request);
		$msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
}