<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\AppointmentCollection;
use App\Models\HelperAttendance;
use App\Models\WmDepartment;
use App\Models\AppointmentCollectionDetail;
use App\Http\Requests\HelperAndWeightImageRequest;
use App\Http\Requests\DirectDispatch;
class UnloadController extends LRBaseController
{
	/*
	Use     : List unload vehicle 
	Author  : Axay Shah
	Date    : 07 Mar,2019
	*/
	public function unloadVehicleList(Request $request){
		$data = array();
		try {
			$data = AppointmentCollection::getCollectionByListByDate($request);
			(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
		}catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
	}
	/*
	Use     : Get Collection Product For Unload Vehicle
	Author  : Axay Shah
	Date    : 08 Mar,2019
	*/
	public function getCollectionProductForUnloadVehicle(Request $request){
		$data           = array();
		$collectionId   = (isset($request->collection_id) && !empty($request->collection_id)) ? $request->collection_id : ''; 
		try {
			$data = AppointmentCollectionDetail::getCollectionProductForUnloadVehicle($request->collection_by,$request->unload_date,$collectionId);
			(!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
		}catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
	}

	/*
	Use     : Create collection product batch
	Author  : Axay Shah
	Date    : 08 Mar,2019
	*/
	public function createCollectionProductBatch(Request $request){
		$data = array();
		try {
			$departmentId   = (isset($request->department_id) && !empty($request->department_id)) ? $request->department_id : 0;
			if(VIRTUAL_UNLOAD_ON != 1){
				$isVirtual      = WmDepartment::where("id",$departmentId)->value("is_virtual");
				if($isVirtual == 1){
					$code = INTERNAL_SERVER_ERROR;
					$msg = trans("message.UNLOAD_NOT_ALLOWED");
					return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
				}
			} else if ($departmentId == 11) {
				//Stop unload on PIRANA
				$code = INTERNAL_SERVER_ERROR;
				$msg = trans("message.UNLOAD_NOT_ALLOWED");
				return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
			}
			$data = AppointmentCollection::createCollectionProductBatch($request);
			(!empty($data)) ? $msg = trans("message.RECORD_INSERTED") : $msg = trans("message.RECORD_NOT_FOUND");
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
		}catch (\Exception $e) {
		   return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
	}

	/*
	Use     : Get Department list with helper list and 
	Author  : Axay Shah
	Date    : 08 Mar,2019
	*/
	public function getDepartment(Request $request)
	{
		$result = array();
		$data   = array();
		try {
			$report                         = (isset($request->from_report) && !empty($request->from_report)) ? true : false;
			$data                           = WmDepartment::getDepartment($report,$request);
			$msg                            = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
			$driverId                       = (isset($request->driver_id) && !empty($request->driver_id)) ? $request->driver_id : 0;
			$driverId                       = (empty($driverId) && isset(Auth()->user()->adminuserid)?Auth()->user()->adminuserid:0);
			$helperAttendance               = HelperAttendance::where('adminuserid',$driverId)->where('batch_id',0)->orderBy('created_at','DESC')->get();
			$result['department']           = $data;
			$result['default_mrf_id']       = Auth()->user()->mrf_user_id;
			$result['helper_list']          = $helperAttendance;
			$result['unload_mrf_range']     = UNLOAD_MRF_RANGE;
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$result]);
		} catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$result]);
		}
	}

	/*
	Use     : Get Virtual Department list 
	Author  : Axay Shah
	Date    : 08 Mar,2019
	*/
	public function getVirtualDepartment(Request $request){
			$result = array();
		try {
			$data   = WmDepartment::getVirtualDepartment();
			$msg    = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
			$driverId           = (isset($request->driver_id) && !empty($request->driver_id)) ? $request->driver_id : Auth()->user()->adminuserid;
			$helperAttendance   = HelperAttendance::where('adminuserid',$driverId)->where('batch_id',0)->orderBy('created_at','DESC')->get();
			$result['department']        = $data;
			$result['helper_list']       = $helperAttendance;
			$result['unload_mrf_range']  = UNLOAD_MRF_RANGE;
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$result]);
		}catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$result]);
		}
	}

	public static function uploadAttandanceAndWeight(Request $request){
		$data     = array();
		try {
			$data =  AppointmentCollection::uploadAttandanceAndWeight($request);
			($data) ? $msg = trans("message.RECORD_INSERTED") : $msg = trans("message.RECORD_NOT_FOUND");
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
		}catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
	}

	/*
	Use     : Call this function when appointment is Direct Dispatch
	Author  : Axay Shah
	Date    : 27 Mar,2019
	*/
	public static function UnloadAndDispatch(DirectDispatch $request){
		$data = array();
		try {
			if(DISPATCH_OFF){
				$msg = DISPATCH_OFF_MSG;
				return response()->json(['code' => VALIDATION_ERROR , "msg"=>$msg,"data"=>""]);
			}
			$data = AppointmentCollection::UnloadDirectDispatchAppointment($request);
			(!empty($data)) ? $msg = trans("message.RECORD_INSERTED") : $msg = trans("message.RECORD_NOT_FOUND");
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
		}catch (\Exception $e) {
		   return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
	}
}
