<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\GtsNameMaster;
use App\Models\Parameter;
use App\Models\InwardPlantDetails;
use App\Models\InwardVehicleMaster;
use App\Models\InwardSegregationMaster;
use App\Http\Requests\AddSegregationRequest;
use App\Http\Requests\AddEditInwardPlant;
class InwardPlantAreaController extends LRBaseController
{
	/*
	Use 	: List GST name
	Author 	: Axay Shah
	Date 	: 11 Dec,2019
	*/
	public function GtsNameList()
	{
		$data   = GtsNameMaster::GtsNameList();
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: List Product Details Remark
	Author 	: Axay Shah
	Date 	: 11 Dec,2019
	*/
	public function InwardRemarkList()
	{
		$data   = Parameter::getParameter(INWARD_DETAIL_REMARK);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Add Inward Details
	Author 	: Axay Shah
	Date 	: 13 Dec,2019
	*/
	public function InwardPlantDetailsStore(AddEditInwardPlant $request)
	{
		$data   = InwardPlantDetails::StoreInwardDetail($request->all());
		$msg    = (!empty($data)) ? trans('message.RECORD_INSERTED') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}


	/*
	Use 	: Update Inward Details
	Author 	: Axay Shah
	Date 	: 17 Dec,2019
	*/
	public function InwardPlantDetailsUpdate(AddEditInwardPlant $request)
	{
		$data   = InwardPlantDetails::UpdateInwardDetail($request->all());
		$msg    = (!empty($data)) ? trans('message.RECORD_UPDATED') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Get By Id
	Author 	: Axay Shah
	Date 	: 17 Dec,2019
	*/
	public function InwardPlantDetailsById(Request $request)
	{
		$id 	= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$data   = InwardPlantDetails::GetById($id);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use 	: List Inward Plant Details
	Author 	: Axay Shah
	Date 	: 13 Dec,2019
	*/
	public function ListInwardPlantDetails(Request $request)
	{
		$data   = InwardPlantDetails::ListInwardPlantAreaDetils($request);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Approved or reject inward status
	Author 	: Axay Shah
	Date 	: 18 Dec,2019
	*/
	public function ApproveOrRejectPlantData(Request $request)
	{
		$id 	= (isset($request->id) && !empty($request->id)) ?  $request->id : 0;
		$status	= (isset($request->status) && !empty($request->status)) ?  $request->status : 0;
		$data   = InwardPlantDetails::ApproveOrRejectPlantData($id,$status);
		$msg    = (!empty($data)) ? trans('message.RECORD_UPDATED') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: List Vehicle Master
	Author 	: Axay Shah
	Date 	: 18 Dec,2019
	*/
	public function ListInwardVehicle(Request $request)
	{
		$data   = InwardVehicleMaster::ListInwardVehicle();
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}


	/*
	Use 	: Add Segeragation Data
	Author 	: Axay Shah
	Date 	: 20 Dec,2019
	*/
	public function AddSegregation(AddSegregationRequest $request)
	{
		$data 			= array();
		$msg    		= "Inward Segregation stop due to technical reasons. you can enter your inward in List Inward Details menu.";
		$product 		= (isset($request->product) && !empty(isset($request->product))) ? json_decode($request->product,true) : '';
		$ShiftId 		= (isset($request->shift_id) && !empty(isset($request->shift_id))) ? $request->shift_id : 0;
		$inward_date 	= (isset($request->inward_date) && !empty(isset($request->inward_date))) ? date("Y-m-d",strtotime($request->inward_date)) : date("Y-m-d");
		$mrf_id 		= (isset($request->mrf_id) && !empty(isset($request->mrf_id))) ? $request->mrf_id : 0;
		$from_product_sorting	= (isset($request->from_product_sorting) && !empty(isset($request->from_product_sorting))) ? $request->from_product_sorting : 0;
		if(!empty($product)){
			foreach($product as $raw){
				$productId 	= (isset($raw['product_id']) && !empty($raw['product_id'])) ? $raw['product_id'] : 0;
				$qty 		= (isset($raw['qty']) 		&& !empty($raw['qty'])) ? $raw['qty'] : 0;
				$data   	= InwardSegregationMaster::AddSegregation($productId,$ShiftId,$qty,$mrf_id,$inward_date,$from_product_sorting);
				LR_Modules_Log_CompanyUserActionLog($request,$data);
			}
		}
		$msg    = (!empty($data)) ? trans('message.RECORD_INSERTED') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}


	/*
	Use 	: Edit Segeragation Data
	Author 	: Axay Shah
	Date 	: 02 Jan,2020
	*/
	public function EditSegregation(Request $request)
	{
		$ID 			= (isset($request->id) 			&& !empty(isset($request->id))) ? $request->id : 0;
		$productId 		= (isset($request->product_id) 	&& !empty(isset($request->product_id))) ? $request->product_id : 0;
		$inward_date 	= (isset($request->inward_date) && !empty(isset($request->inward_date))) ? date("Y-m-d",strtotime($request->inward_date)) : "";
		$mrf_id 		= (isset($request->mrf_id) 		&& !empty(isset($request->mrf_id))) ? $request->mrf_id : 0;
		$from_product_sorting	= (isset($request->from_product_sorting) && !empty(isset($request->from_product_sorting))) ? $request->from_product_sorting : 0;
		$qty 			= (isset($request->qty) 		&& !empty(isset($request->qty))) ? $request->qty : 0;
		$data   		= InwardSegregationMaster::EditSegregation($ID,$productId,$qty,$mrf_id,$inward_date,$from_product_sorting);
		$msg    		= (!empty($data)) ? trans('message.RECORD_UPDATED') :trans('message.RECORD_NOT_FOUND');
		if(!empty($data))
		{
			LR_Modules_Log_CompanyUserActionLog($request,$request->id);
		}
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}


	/*
	Use 	: List inward
	Author 	: Axay Shah
	Date 	: 23 Dec,2019
	*/
	public function ListInwardSegregation(Request $request)
	{
		$data   = InwardSegregationMaster::ListInwardSegregation($request);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use 	: Get Details List
	Author 	: Axay Shah
	Date 	: 02 Jan,2019
	*/
	public function GetDetailsList(Request $request)
	{
		$data   = InwardSegregationMaster::ListInwardSegregation($request,false);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Add Inward Ledger from Segeregation
	Author 	: Axay Shah
	Date 	: 02 Jan,2020
	*/
	public function updateSegeregationStock(Request $request)
	{
		$data   = InwardSegregationMaster::updateSegeregationStock($request,false);
		$msg    = (!empty($data)) ? trans('message.RECORD_UPDATED') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Add Inward Ledger from Segeregation
	Author 	: Axay Shah
	Date 	: 02 Jan,2020
	*/
	public function InwardTotalNumberOfTripReport(Request $request)
	{
		$data   = InwardPlantDetails::InwardTotalNumberOfTripReport($request);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Inward Details Report
	Author 	: Axay Shah
	Date 	: 24 March 2020
	*/
	public function InwardDetailReport(Request $request)
	{
		$data   = InwardPlantDetails::ListInwardPlantAreaDetils($request,true);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}


	/*
	Use 	: Inward Sagregation input output report
	Author 	: Axay Shah
	Date 	: 26 March 2020
	*/
	public function InwardInputOutputReport(Request $request)
	{
		$data   = InwardSegregationMaster::InwardInputOutputReport($request,true);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: List Product Sorting Sagregation
	Author 	: Axay Shah
	Date 	: 31 March,2020
	*/
	public function ListProductSortingSegregation(Request $request)
	{
		$data   = InwardSegregationMaster::ListProductSortingSegregation($request);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Get Details List
	Author 	: Axay Shah
	Date 	: 02 Jan,2020
	*/
	public function GetProductSortingDetailsList(Request $request)
	{
		$data   = InwardSegregationMaster::ListProductSortingSegregation($request,false);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use 	: Save Vehicle Inward In Out Detail
	Author 	: Hasmukhi patel
	Date 	: 02 July,2021
	*/
	public function SaveVehicleInwardInOutDetail(Request $request)
	{
		$data   = InwardVehicleMaster::SaveVehicleInwardInOutDetail($request);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Save Vehicle Inward In Out Detail
	Author 	: Hasmukhi Patel
	Date 	: 02 July,2021
	*/
	public function VehicleInwardInOutList(Request $request)
	{
		$data   = InwardVehicleMaster::VehicleInwardInOutList($request);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use 	: Update Vehicle Inward Out Detail
	Author 	: Hasmukhi
	Date 	: 02 July,2021
	*/
	public function UpdateVehicleInwardOutTime(Request $request){
		$data   = InwardVehicleMaster::updateVehicleInwardOutTime($request);
		$msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/*
	Use 	: Update Inward Out Detail
	Author 	: Hasmukhi
	Date 	: 02 July,2021
	*/
	public function UpdateInwardDetail(Request $request){
		$data   = InwardPlantDetails::UpdateStoreInwardDetail($request->all());
		$msg    = (!empty($data)) ? trans('message.RECORD_UPDATED') :trans('message.RECORD_NOT_FOUND');
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
}
