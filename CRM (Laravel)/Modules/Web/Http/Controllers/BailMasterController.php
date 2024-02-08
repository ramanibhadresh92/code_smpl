<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\BailInwardLedger;
use App\Models\BailStockLedger;

class BailMasterController extends LRBaseController
{
    

   /*
    Use     : List Stock 
    Author  : Axay Shah
    Date    : 09 Jan,2020
   */
    public static function ListBailInwardList(Request $request){
        $data = BailInwardLedger::ListBailData($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
    }

     /*
    Use     : List Stock 
    Author  : Axay Shah
    Date    : 09 Jan,2020
   */
    public static function AddBailInward(Request $request){
        $data = BailInwardLedger::AddBailInward($request->all());
        ($data > 0 ) ? $msg = trans('message.RECORD_INSERTED') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
    }

    /*
    Use     : List Bail Stock Ledger
    Author  : Axay Shah
    Date    : 10 Jan,2020
   */
    public static function ListBailStock(Request $request){
        $data = BailStockLedger::BailStockLedger($request);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
    }


    /*
    Use     :  Get BY Id Bail Inward
    Author  : Axay Shah
    Date    : 10 Jan,2020
   */
    public static function BailGetById(Request $request){
        $id   = (isset($request['id'])                && !empty($request['id']))  ? $request['id']:0;
        $data = BailInwardLedger::BailGetById($id);
        ($data) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
    }

    /*
    Use     :  Get BY Id Bail Inward
    Author  : Axay Shah
    Date    : 10 Jan,2020
   */
    public static function EditBailInward(Request $request){

        $data = BailInwardLedger::EditBailInward($request->all());
        ($data) ? $msg = trans('message.RECORD_UPDATED') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]); 
    }
}
