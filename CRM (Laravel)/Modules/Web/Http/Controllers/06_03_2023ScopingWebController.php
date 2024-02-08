<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\MediaMaster;
use App\Models\ScopingCustomerMaster;

class ScopingWebController extends LRBaseController
{
    /**
     * use      : Add scoping customer data
     * Date     : 10 Jan,2019
     * Author   : Axay Shah
    */
    public function create(Request $request)
    {
        try{
            $data   = ScopingCustomerMaster::addScopingCustomer($request);
            $msg    = trans('message.RECORD_INSERTED') ;
            $code   = SUCCESS;
        }catch(\Exception $e){
            //dd($e);
            // \Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
            $data   = ""; 
            $msg    = trans('message.SOMETHING_WENT_WRONT');
            $code   =  INTERNAL_SERVER_ERROR;
        }
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
    }
    /**
     * use      : get by id
     * Date     : 10 Jan,2019
     * Author   : Axay Shah
    */
    public function getById(Request $request)
    {
        try{
            $data   = ScopingCustomerMaster::getById($request);
            $msg    = trans('message.RECORD_FOUND') ;
            $code   = SUCCESS;
        }catch(\Exception $e){
            dd($e);
            \Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
            $data   = ""; 
            $msg    = trans('message.SOMETHING_WENT_WRONT');
            $code   =  INTERNAL_SERVER_ERROR;
        }
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
    }
    /**
     * use      : update scoping customer data
     * Date     : 10 Jan,2019
     * Author   : Axay Shah
    */    
    public function update(Request $request)
    {
        try{
            $data   = ScopingCustomerMaster::updateScopingCustomer($request);
            $msg    = trans('message.RECORD_UPDATED') ;
            $code   = SUCCESS;
        }catch(\Exception $e){
            \Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
            $data   = ""; 
            $msg    = trans('message.SOMETHING_WENT_WRONT');
            $code   =  INTERNAL_SERVER_ERROR;
        }
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
    }
    /**
     * use      : List all record
     * Date     : 10 Jan,2019
     * Author   : Axay Shah
    */
    public function list(Request $request)
    {
        try{
            $data   = ScopingCustomerMaster::list($request);
            $msg    = trans('message.RECORD_FOUND') ;
            $code   = SUCCESS;
        }catch(\Exception $e){
            \Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
            $data   = ""; 
            $msg    = trans('message.SOMETHING_WENT_WRONT');
            $code   =  INTERNAL_SERVER_ERROR;
        }
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
    }
    /**
     * use      : List all record
     * Date     : 10 Jan,2019
     * Author   : Axay Shah
    */
    public function scopingImageUpload(Request $request)
    {
        try{
            $data   = ScopingCustomerMaster::scopingImageUpload($request);
            $msg    = trans('message.RECORD_FOUND') ;
            $code   = SUCCESS;
        }catch(\Exception $e){
            \Log::error($e->getMessage()." ".$e->getLine().$e->getTraceAsString());
            $data   = ""; 
            $msg    = trans('message.SOMETHING_WENT_WRONT');
            $code   =  INTERNAL_SERVER_ERROR;
        }
		return response()->json(['code'=>$code,'msg'=>$msg,'data'=>$data]); 
    }
}
