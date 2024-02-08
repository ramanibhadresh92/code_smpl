<?php

namespace Modules\Mobile\Http\Controllers;
use Modules\Mobile\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\GtsNameMaster;
use App\Models\Parameter;
use App\Models\InwardRemarkList;
use App\Models\WmDepartment;
use App\Models\InwardVehicleMaster;
use App\Models\InwardPlantDetails;
class InwardDetailController extends LRBaseController
{
    /*
    Use     : List GST name 
    Author  : Axay Shah
    Date    : 11 Dec,2019
    */
    public function GtsNameMaster()
    {
        $data   = GtsNameMaster::GtsNameList();
        $msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);  
    }

    /*
    Use     : List Product Details Remark
    Author  : Axay Shah
    Date    : 11 Dec,2019
    */
    public function InwardRemarkList()
    {
        $data   = Parameter::getParameter(INWARD_DETAIL_REMARK);
        $msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);  
    }
    

    /*
    Use     : List Vehicle Master
    Author  : Axay Shah
    Date    : 13 Jan,2020
    */
    public function ListInwardVehicle(Request $request)
    {
        $data   = InwardVehicleMaster::ListInwardVehicle();
        $msg    = (!empty($data)) ? trans('message.RECORD_FOUND') :trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);  
    }




    /*
    Use     : Add Inward Details
    Author  : Axay Shah
    Date    : 13 Dec,2019
    */
    public function InwardPlantDetailsStore(Request $request)
    {
        $data   = InwardPlantDetails::StoreInwardDetail($request);
        $msg    = (!empty($data)) ? trans('message.RECORD_INSERTED') :trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);  
    }


    /*
    Use     : Get Department LIst
    Author  : Axay Shah
    Date    : 13 Jan,2020
    */
    public function getDepartment(Request $request){
            $result = array();
        try {
            $Mappingdate        = date('Y-m-d');
            $data               = WmDepartment::getDepartmentForMobile();
            $msg                = (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
            $driverId           = (isset($request->driver_id) && !empty($request->driver_id)) ? $request->driver_id : Auth()->user()->adminuserid;
            $result['department']        = $data;
            $result['helper_list']       = array();
            $result['unload_mrf_range']  = UNLOAD_MRF_RANGE;
            return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$result]);
        }catch (\Exception $e) {
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$result]);
        }
    }
}
