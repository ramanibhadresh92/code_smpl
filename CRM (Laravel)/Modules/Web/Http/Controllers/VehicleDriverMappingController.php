<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\VehicleDriverMappings;
use App\Models\VehicleMaster;
use Validator;
use Log;
class VehicleDriverMappingController extends LRBaseController
{
	public function saveVehicleMapping(Request $request){
	   try{
		$code   = INTERNAL_SERVER_ERROR;
		$msg    = "Vehicle Mapped fail.";
		$data = VehicleDriverMappings::saveVehicleMapping($request);
		if($data){
			$code   = SUCCESS;
			$msg    = "Vehicle Mapped successfully.";
		}
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	   }catch(\Exception $e){
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
	   }
		
	}

	public function getVehicleUnMappedUserList(Request $request){
		try{
		 $code   = SUCCESS;
		 $msg    = trans('message.RECORD_NOT_FOUND');
		 $data = VehicleDriverMappings::getVehicleUnMappedUserList($request);
		 if($data){
			 $code   = SUCCESS;
			 $msg    = trans('message.RECORD_FOUND');;
		 }
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
		}catch(\Exception $e){
			return response()->json(['code'=>$code,'msg'=>$msg,'data'=>""]); 
		}
		 
	 }

	 public function getAllVehicle(Request $request){
		try{
			$code  = SUCCESS;
			$data  = VehicleMaster::getAllVehicle();
			($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND'); 
			return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
		}catch(\Exception $e){
			return response()->json(['code'=>INTERNAL_SERVER_ERROR,'msg'=>json_encode($e),'data'=>""]); 
		}
		 
	 }
}
