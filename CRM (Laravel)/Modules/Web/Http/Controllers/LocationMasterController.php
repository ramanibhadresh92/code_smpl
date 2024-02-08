<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\LocationMaster;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddLocation;
use Validator;
use Log;

class LocationMasterController extends LRBaseController
{
	/*
	Use     : Insert City
	Date    : 29 May 2021
	Author  : Upasana
	*/
	public function AddCity(AddLocation $request) {
		$cityName		= (isset($request->city) && !empty($request->city) ? $request->city : strtolower($request->city));
		$locationData	= LocationMaster::where('state_id',$request->state_id)->where('city',$cityName)->first();
		$data 			= LocationMaster::ModifyLocation($request);
		
		return response()->json(['code'=>SUCCESS,'msg'=>"Record Found"]);
	}
	/*
	Use     : Change Location Status
	Date    : 29 May 2021
	Author  : Upasana
	*/
	public function ChangeLocationStatus(Request $request){
		$id             = (isset($request->location_id) && !empty($request->location_id) ? $request->location_id : 0);
		$status         = (isset($request->status) && !empty($request->status) ? $request->status : "");
		$location       = LocationMaster::find($id);
		if (!empty($location)) {
			if ($location->status == "I") {
				if($status == "A"){
					$location->status = "A";
					if($location->save()){
						return response()->json(['code'=>SUCCESS,'msg'=>"City activated successfully"]);
					}
				}
			} 
			else {
				if($location->status == "A"){
					if($status == "I"){
						$location->status = "I";
						if($location->save()){
							return response()->json(['code'=>SUCCESS,'msg'=>"City de-activated successfully"]);
						}
					}
				}   
			}
		}
		return response()->json(['code'=>SUCCESS,'msg'=>"Something went wrong here."]);
	}
	 /*
	Use     : Autocomplete City Dropdown
	Date    : 29 May 2021
	Author  : Upasana
	*/
	public function AutocompleteCityDropdown(Request $request)
	{   
		$data = LocationMaster::AutocompleteCityList($request);
		if(!empty($data)){
		return response()->json($data);                 
		}else{
		  $data = [];
		return response()->json($data);
		}
	}
	public function ListCity(Request $request){
		$data = LocationMaster::ListLocations($request);
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
}
