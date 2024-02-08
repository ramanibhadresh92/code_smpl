<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\VehicleMaster;
use App\Models\AdminUserReading;
use App\Http\Requests\AddAdminUserReading;
use App\Http\Requests\UpdateAdminUserReading;
class AdminUserReadingController extends LRBaseController
{
    /*
    Use     :  Add vehicle Reading 
    Author  :  Axay Shah
    Date    :  26 Oct,2018 
    */

    public function addReading(AddAdminUserReading $request){
        $data = AdminUserReading::addUserKMReading($request);
        return response()->json($data); 
    }
    /*
    Use     :  Add vehicle Reading 
    Author  :  Axay Shah
    Date    :  26 Oct,2018 
    */

    public function updateReading(UpdateAdminUserReading $request){
        $data = AdminUserReading::updateUserKMReading($request);
        return response()->json($data); 
    }
    /*
    Use     :  Retrive KM Reading
    Author  :  Axay Shah
    Date    :  26 Oct,2018 
    */

    public function retrieveKMReading(Request $request){
        $data = AdminUserReading::retrieveKMReading($request);
        (count($data) > 0) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
    }

    /*
    Use     :  Retrive KM Reading
    Author  :  Axay Shah
    Date    :  29 Oct,2018 
    */

    public function getMaxReading(Request $request){
        $data = AdminUserReading::getMaxReading($request);
        (count($data) > 0) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
    }


    /**
    * Use      : Vehicle Reading Report
    * Author   : Axay Shah
    * Date     : 05 Nov,2018 
    */
    public function vehicleReadingReport(Request $request){
        $data = AdminUserReading::vehicleReadingReport($request);
        (count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
    }
}
