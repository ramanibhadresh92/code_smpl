<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use App\Models\SlabMaster;
use Illuminate\Http\Request;

use Validator;
use Log;
class SlabMasterController extends LRBaseController
{

    /**
     * Function Name : List SlabMaster
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 30 March, 2023
     */
    public function getSlabList(Request $request)
    {
        $data       = [];
        $msg        = trans('message.RECORD_FOUND');
        try {
            $data = SlabMaster::getSlabMasterList($request);
            
            if($data->isEmpty()){
                $msg = trans('message.RECORD_NOT_FOUND');
            }
        }
        catch (\Exception $e) {
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
        }
            return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /**
     * Function Name : getSlabById
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 30 March, 2023
     */
    public function getSlabById(Request $request){
        $data = SlabMaster::getById($request->id);
        ($data) ? $msg = trans("message.RECORD_FOUND") : $msg =  trans("message.RECORD_NOT_FOUND");
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
    }

    /**
     * Function Name : Edit Slab
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 30 March, 2023
     */
    public function edit(Request $request)
    {
        $msg     = trans('message.RECORD_FOUND');
        $data    = SlabMaster::find($request->id);
        if($data) {
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => $data]);
        }else{
            $msg     = trans('message.RECORD_NOT_FOUND');
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => '']);
        }
    }

    /**
     * Function Name : Update Slab
     * @param $request
     * @return Json
     * @author Hardyesh Gupta
     * @date 30 March, 2023
     */
    public function updateSlab(Request $request){
        return SlabMaster::UpdateSlab($request);
        /*
        try{
            $data   = SlabMaster::UpdateSlab($request);
            $msg    = ($data == true) ?  trans('message.RECORD_UPDATED') : trans('message.SOMETHING_WENT_WRONG');
            $code   =  ($data == true) ?  SUCCESS : INTERNAL_SERVER_ERROR;
        }catch(\Exception $e){
           \Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
            $data   = $e->getMessage()." ".$e->getLine().$e->getFile();
            $msg    = trans('message.SOMETHING_WENT_WRONG');
            $code   =  INTERNAL_SERVER_ERROR;
        }
        return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]);
        */
    }

    
}