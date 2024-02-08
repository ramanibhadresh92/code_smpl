<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\AutoWayBridgeDetails;
use App\Models\WaybridgeModuleMaster;
use App\Models\WaybridgeModuleVehicleInOut;
class WaybridgeController extends LRBaseController
{
	/**
	* Function Name : saveWaybridgeDetails
	* @param mixed $request
	* @return
	* @author Kalpak Prajapati
	* @since 2020-07-18
	*/
	public function saveWaybridgeDetails(Request $request)
	{
		$arrFields['row_id'] 			= (isset($request['row_id']) && !empty($request['row_id']))?$request['row_id']:"";
		$arrFields['wb_id'] 			= (isset($request['wb_id']) && !empty($request['wb_id']))?$request['wb_id']:"";
		$arrFields['ticket_no'] 		= (isset($request['ticket_no']) && !empty($request['ticket_no']))?$request['ticket_no']:0;
		$arrFields['vehicle_no'] 		= (isset($request['vehicle_no']) && !empty($request['vehicle_no']))?$request['vehicle_no']:0;
		$arrFields['tare_weight'] 		= (isset($request['tare_weight']) && !empty($request['tare_weight']))?$request['tare_weight']:0;
		$arrFields['gross_weight'] 		= (isset($request['gross_weight']) && !empty($request['gross_weight']))?$request['gross_weight']:0;
		$arrFields['net_weight']		= (isset($request['net_weight']) && !empty($request['net_weight']))?$request['net_weight']:0;
		$arrFields['gross_date'] 		= (isset($request['gross_date']) && !empty($request['gross_date']))?date("Y-m-d",strtotime($request['gross_date'])):"";
		$arrFields['tare_date'] 		= (isset($request['tare_date']) && !empty($request['tare_date']))?date("Y-m-d",strtotime($request['tare_date'])):"";
		$arrFields['gross_time'] 		= (isset($request['gross_time']) && !empty($request['gross_time']))?date("H:i:s",strtotime($request['gross_time'])):"";
		$arrFields['tare_time'] 		= (isset($request['tare_time']) && !empty($request['tare_time']))?date("H:i:s",strtotime($request['tare_time'])):"";
		$arrFields['wayslip_pdf'] 		= (isset($request['wayslip_pdf']) && !empty($request['wayslip_pdf']))?$request['wayslip_pdf']:"";
		$arrFields['wayslip_photo_1'] 	= (isset($request['wayslip_photo_1']) && !empty($request['wayslip_photo_1']))?$request['wayslip_photo_1']:"";
		$arrFields['wayslip_photo_2'] 	= (isset($request['wayslip_photo_2']) && !empty($request['wayslip_photo_2']))?$request['wayslip_photo_2']:"";
		$arrFields['wayslip_photo_3'] 	= (isset($request['wayslip_photo_3']) && !empty($request['wayslip_photo_3']))?$request['wayslip_photo_3']:"";
		$arrFields['wayslip_photo_4'] 	= (isset($request['wayslip_photo_4']) && !empty($request['wayslip_photo_4']))?$request['wayslip_photo_4']:"";
		AutoWayBridgeDetails::AddNewRecord($arrFields);
		return response()->json(array("code" => SUCCESS,"msg"=>trans("message.RECORD_INSERTED")));
	}

	/**
	use 	: list way bridge
	Author 	: Axay Shah
	Date    : 24 Feb 2021
	*/
	public function GetById(Request $request)
	{
		$DispatchId = (isset($request->id) && !empty($request->id)) ? $request->id : 0 ;
		$data 		= WaybridgeModuleMaster::GetById($id);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	use 	: list way bridge
	Author 	: Axay Shah
	Date    : 24 Feb 2021
	*/
	public function CreateWayBridge(Request $request)
	{
		$data 		= WaybridgeModuleMaster::CreateWayBridge($request->all());
		$msg 		= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		if(!empty($data) && isset($data->id)){
			WmDispatch::UpdateDocumentForEPR($request,$data->id);
		}
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	use 	: list way bridge
	Author 	: Axay Shah
	Date    : 24 Feb 2021
	*/
	public function UpdateWayBridge(Request $request)
	{
		$data 		= WaybridgeModuleMaster::UpdateWayBridge($request->all());
		$msg 		= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		if(!empty($data) && isset($data->id)){
			WmDispatch::UpdateDocumentForEPR($request,$data->id);
		}
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	use 	: list way bridge
	Author 	: Axay Shah
	Date    : 24 Feb 2021
	*/
	public function ListWayBridge(Request $request)
	{
		$data 		= WaybridgeModuleMaster::ListWayBridge($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	use 	: Way Bridge dropdown
	Author 	: Axay Shah
	Date    : 26 Feb 2021
	*/
	public function GetWayBridgeDropDown(Request $request)
	{
		$data 		= WaybridgeModuleMaster::GetWayBridgeDropDown($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/**
	use 	: Way Bridge dropdown
	Author 	: Axay Shah
	Date    : 26 Feb 2021
	*/
	public function ListWaybridgeVehicleInOut(Request $request)
	{

		$data 		= WaybridgeModuleVehicleInOut::ListWaybridgeVehicleInOut($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
	/**
	use 	: Add waybridge and vehicle details
	Author 	: Axay Shah
	Date    : 26 Feb 2021
	*/
	public function AddWaybridgeVehicleInOut(Request $request)
	{
		$data 	= WaybridgeModuleVehicleInOut::AddWaybridgeVehicleInOut($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	use 	: Add waybridge and vehicle details
	Author 	: Axay Shah
	Date    : 26 Feb 2021
	*/
	public function RefreshWaybridgeVehicleInOut(Request $request)
	{
		$vehicle_id = (isset($request->vehicle_id) && !empty($request->vehicle_id)) ? $request->vehicle_id : 0;
		$record_id 	= (isset($request->record_id) && !empty($request->record_id)) ? $request->record_id : 0;
		$wb_id 		= (isset($request->wb_id) && !empty($request->wb_id)) ? $request->wb_id : "";
		$data 		= WaybridgeModuleVehicleInOut::GetVehicleWeightDetails($record_id,$wb_id,$vehicle_id);
		$msg 		= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	use 	: Mark Row As Used
	Author 	: Kalpak Prajapati
	Date    : 26 Nov 2022
	*/
	public function MarkRowAsUsed(Request $request)
	{
		$waybridge_slip_id 	= (isset($request->waybridge_slip_id) && !empty($request->waybridge_slip_id)) ? $request->waybridge_slip_id : "";
		$adminuserid 		= isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0;
		$data 				= AutoWayBridgeDetails::MarkRowAsUsed($waybridge_slip_id,$adminuserid);
		$msg 				= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
}