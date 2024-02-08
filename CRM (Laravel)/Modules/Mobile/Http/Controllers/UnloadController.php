<?php

namespace Modules\Mobile\Http\Controllers;
use Modules\Mobile\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\AppointmentCollection;
use App\Models\HelperAttendance;
use App\Models\HelperDriverMapping;
use App\Models\WmDepartment;
use App\Models\AppointmentCollectionDetail;
use App\Http\Requests\HelperAndWeightImageRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\WmBatchMaster;

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
			(!empty($data)) ? $msg = trans("message.COLLECTION_FOUND") : $msg = trans("message.COLLECTION_NOT_FOUND");
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
			$departmentId = (isset($request->department_id) && !empty($request->department_id)) ? $request->department_id : 0;
			if ($departmentId == 11 || empty($departmentId)) {
				//STOP UNLOAD IN PIRANA
				$code = VALIDATION_ERROR;
				$msg = trans("message.COLLECTION_NOT_FOUND");
			} else {
				$data = AppointmentCollection::createCollectionProductBatch($request);
				if(!empty($data)) {
					$code = SUCCESS;
					$msg = trans("message.BATCH_CREATE");
				} else {
					$code = VALIDATION_ERROR;
					$msg = trans("message.COLLECTION_NOT_FOUND");
				}
			}
			return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
		}catch (\Exception $e) {
		   return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
	}

	/*
	Use     : Get Department list with helper list and 
	Author  : Axay Shah
	Date    : 08 Mar,2019
	*/
	public function getDepartment(Request $request){
			$result = array();
		try {
			$Mappingdate        = date('Y-m-d');

			$collection_start_date      = date('Y-m-d')." ".GLOBAL_START_TIME;
			$collection_end_date        = date('Y-m-d')." ".GLOBAL_END_TIME;
			$checkCollection            = AppointmentCollection::checkTodayCollectionMobile(Auth::user()->adminuserid,$collection_start_date,$collection_end_date);
			if(isset($request->end_of_day) && $request->end_of_day != 1){
				if($checkCollection && count($checkCollection) == 0){
					$msg = trans('message.NO_ANY_COLLECTION_PENDING');
					return response()->json(['code' => ERROR , "msg"=>$msg,"data"=>'']);
				}
			}
			$data               = WmDepartment::getDepartment(false,"",true);
			$msg                = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
			$driverId           = (isset($request->driver_id) && !empty($request->driver_id)) ? $request->driver_id : Auth()->user()->adminuserid;
			$helperAttendance   = HelperDriverMapping::select("helper_driver_mapping.*",\DB::raw("CONCAT(helper_master.first_name,' ',helper_master.last_name) as `name`"))
									->join("helper_master","helper_master.code","=","helper_driver_mapping.code")
									->where('helper_driver_mapping.adminuserid',$driverId)
									->where('helper_driver_mapping.mapping_date',$Mappingdate)
									->get();
			$result['department']        = $data;
			$result['helper_list']       = $helperAttendance;
			$result['unload_mrf_range']  = UNLOAD_MRF_RANGE;
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$result]);
		}catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$result]);
		}
	}

	public static function uploadAttandanceAndWeight(HelperAndWeightImageRequest $request){
		$data           = array();
		try {
			$data =  AppointmentCollection::uploadAttandanceAndWeight($request);
			($data) ? $msg = trans("message.BATCH_CREATED") : $msg = trans("message.RECORD_NOT_FOUND");
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
		}catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
	}

	/**
	* @uses Vehicle Out From MRF
	* @param
	* @return
	* @author Kalpak Prajapati
	* @since 2019-03-13
	*/
	public function vehicleunloadfinish(Request $Request)
	{
		try {
			AppointmentCollection::MarkVehicleOutFromMRF($Request);
			return response()->json(['code' => SUCCESS,"msg"=>trans("message.RECORD_INSERTED")]);
		}catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>array()]);
		}
		return;
	}

	/*
	Use     : Batch List of Collection By who's tare weight is not entered
	Author  : Axay Shah
	Date    : 15 May,2019
	*/
	public function BatchListOfCollectionBy(Request $request){
		try {
			$data = WmBatchMaster::BatchListOfCollectionBy(Auth()->user()->adminuserid);
			if(!empty($data)){
				return response()->json(['code' => SUCCESS,"msg"=>trans("message.RECORD_FOUND"),"data"=>$data]);
			}else{
				return response()->json(['code' => SUCCESS,"msg"=>trans("message.RECORD_NOT_FOUND"),"data"=>$data]);
			}
			
		}catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>array()]);
		}
		return;
	}


	/*
	Use     : Update Tare weight of batch
	Author  : Axay Shah
	Date    : 15 May,2019
	*/
	public function UpdateTareWeightOfBatch(Request $request){
		try {

			$batchId 	= (isset($request->batch_id) && !empty($request->batch_id)) ? $request->batch_id : 0;
			$tareWeight = (isset($request->tare_weight) && !empty($request->tare_weight)) ? $request->tare_weight : 0;
			$data 		= false;
			

			$data 		= WmBatchMaster::UpdateTareWeightOfBatch($batchId,$tareWeight);
			
			if($data){
				return response()->json(['code' => SUCCESS,"msg"=>trans("message.RECORD_UPDATED"),"data"=>$data]);
			}else{
				return response()->json(['code' => SUCCESS,"msg"=>trans("message.RECORD_NOT_FOUND"),"data"=>$data]);
			}
			
		}catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>array()]);
		}
		return;
	}
	/*
	Use     : Create collection product batch For direct dispatch
	Author  : Axay Shah
	Date    : 06 June,2019
	*/
	public function DirectDispatchForMobile(Request $request){
		$data = array();
		try {
			$data = AppointmentCollection::DirectDispatchForMobile($request);
			if(!empty($data)) {
				$code = SUCCESS;
				$msg = trans("message.BATCH_CREATE");
			}else{
				$code = VALIDATION_ERROR;
				$msg = trans("message.COLLECTION_NOT_FOUND");
			}
			return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
		}catch (\Exception $e) {
		   return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
	}
}