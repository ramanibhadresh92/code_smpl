<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\VehicleMaster;
use App\Models\AdminUserReading;
use App\Http\Requests\VehicleAdd;
use App\Http\Requests\VehicleUpdate;
use App\Http\Requests\AddAdminUserReading;
use App\Models\LastAdminGeoCode;
use App\Models\Parameter;
use App\Models\RtoStateCodes;
class VehicleManagementController extends LRBaseController
{
    /**
     * Use      : Get vehicle assets from parameter
     * Author   : Axay Shah
     * Date     : 24 Oct,2018
     */
    public function vehicleAssets(Request $request){
        $data = Parameter::parentDropDown(PARA_VEHICLE_ASSETS)->get();
        (count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }
    /**
     * Use      : Get vehicle Document type from parameter
     * Author   : Axay Shah
     * Date     : 25 Oct,2018
     */
    public function vehicleDocType(Request $request){
        $data = Parameter::parentDropDown(PARA_VEHICLE_DOC_TYPE)->get();
        (count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }
    /**
     * Use      : Add vehicle
     * Author   : Axay Shah
     * Date     : 24 Oct,2018
     */
    public function addVehicle(VehicleAdd $request){
        try{
            $data = VehicleMaster::addVehicle($request);
            ($data > 0) ? $msg = trans('message.RECORD_INSERTED') : $msg = trans('message.SOMETHING_WENT_WRONG');
            ($data) ? $code = SUCCESS : $msg = INTERNAL_SERVER_ERROR;
        }catch(\Exeption $e){
            $code = INTERNAL_SERVER_ERROR;
            $msg = $e->getMessage()." ".$e->getFile()." ".$e->getLine();
        }

        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    }
    /**
     * Use      : Update vehicle
     * Author   : Axay Shah
     * Date     : 24 Oct,2018
     */
    public function updateVehicle(VehicleUpdate $request){
        $data = VehicleMaster::updateVehicle($request);
        ($data) ? $msg = trans('message.RECORD_UPDATED') : $msg = trans('message.SOMETHING_WENT_WRONG');
        ($data) ? $code = SUCCESS : $msg = INTERNAL_SERVER_ERROR;
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
    }
    /**
     * Use      : get By Id
     * Author   : Axay Shah
     * Date     : 25 Oct,2018
     */
    public function getById(Request $request){
        $data = VehicleMaster::getById($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /**
    * Use      : get By Id
    * Author   : Axay Shah
    * Date     : 25 Oct,2018
    */
    public function list(Request $request){
        $data = VehicleMaster::list($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }


    /**
    * Use      : change vehicle status
    * Author   : Axay Shah
    * Date     : 21 Dec,2018
    */
    public function vehicleStatus(Request $request){
        $data = VehicleMaster::changeStatus($request);
        ($data) ? $msg = trans('message.RECORD_UPDATED') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }


    /**
    * Use      : gps tracking api
    * Author   : Axay Shah
    * Date     : 04 Mar,2019
    */
    public function gpsTrack(Request $request){
        $data = LastAdminGeoCode::getGeoTracking();
        ($data) ? $msg = trans('message.RECORD_UPDATED') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }


    /*
    use     : vehicle list for dropdown
    Author  : Axay Shah
    Date    : 24 May,2019
    */
    public function vehicleList(Request $request){
        $report  = (isset($request->from_report) && !empty($request->from_report)) ? $request->report : 0;
        $vehicle = VehicleMaster::listVehicleNo($report,$request);
        return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $vehicle]);
    }

    /*
    use     : vehicle owner list from dropdown
    Author  : Axay Shah
    Date    : 29 Nov,2019
    */
    public function listVehicleOwner(Request $request){

        $vehicle = VehicleMaster::listVehicleOwner();
        return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $vehicle]);
    }

    /*
    use     : Get Vehicle RTO Code
    Author  : Axay Shah
    Date    : 01 Feb,2020
    */
    public function GetRtoStateCodeData(Request $request){

        $vehicle = RtoStateCodes::GetRtoStateCodeData();
        return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $vehicle]);
    }
}
