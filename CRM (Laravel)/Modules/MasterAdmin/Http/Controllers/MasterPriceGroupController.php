<?php

namespace Modules\MasterAdmin\Http\Controllers;
use Modules\MasterAdmin\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\PriceGroupMaster;
use App\Http\Requests\MasterPriceGroupRequest;
class MasterPriceGroupController extends LRBaseController
{
    /*
    Use     : List all master price group
    Author  : Axay Shah 
    Date    : 17 Sep,2018
    */
    public function list(Request $request){
        $msg        = trans('message.RECORD_FOUND');
        try {
            $data = PriceGroupMaster::listPriceGroup($request);
            if(!$data){
                $msg = trans('message.RECORD_NOT_FOUND');
            }
        }
        catch (\Exception $e) {
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage().$e,"data"=>""]);
        }
            return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    /*
    Use     : Create master price group
    Author  : Axay Shah 
    Date    : 18 Sep,2018
    */
    public function create(MasterPriceGroupRequest $request){
        $data = PriceGroupMaster::add($request);
        return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_INSERTED'),"data"=>$data]);
    }
    /*
    Use     : update master price group
    Author  : Axay Shah 
    Date    : 18 Sep,2018
    */
    public function update(MasterPriceGroupRequest $request){
       $data = PriceGroupMaster::updateRecord($request);
        return response()->json(['code' => SUCCESS , "msg"=>trans('message.RECORD_UPDATED'),"data"=>""]);
    }
    /*
    Use     : Get Price Group by its id
    Author  : Axay Shah 
    Date    : 18 Sep,2018
    */
    public function getById(Request $request){
        $data =  PriceGroupMaster::find($request->price_group_id);
        ($data) ? $msg =  trans('message.RECORD_FOUND') : $msg =  trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    /**
    * Use       :   Change category status
    * Author    :   Axay Shah
    * Date      :   11 Jan,20119
    */
    public function changeStatus(Request $request){
        $msg            = trans('message.RECORD_NOT_FOUND');
        $changeStatus   = "";
        if(isset($request->pricegroup_id) && isset($request->status)){
            $changeStatus   = PriceGroupMaster::changeStatus($request->pricegroup_id,$request->status); 
            if(!empty($changeStatus)){
                $msg        = trans('message.STATUS_CHANGED');
            }
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $changeStatus]);
    }

}
