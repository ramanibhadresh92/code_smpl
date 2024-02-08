<?php

namespace Modules\MasterAdmin\Http\Controllers;
use Modules\MasterAdmin\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\LocationMaster;
use App\Models\Parameter;
class MasterSettingsController extends LRBaseController
{
    /*
    Use     : Add and edit location master
    Author  : Axay shah
    Date 	: 22 April,2019  
    */
    public function AddOrUpdateLocation(Request $request){
    	try{
	    	$status =	SUCCESS;
	    	$msg 	=	trans("message.RECORD_INSERTED");
	    	$data 	= 	LocationMaster::ModifyLocation($request);
    		if($data){
    			return response()->json(array("code" => SUCCESS,"msg"=>$msg,"data"=> $data));
    		}
    	}catch(\Exception $e){
            dd($e);
            \Log::error("ERROR :".$e->getMessage()." LINE : ".$e->getLine()." LINE : ".$e->getTraceAsString());
            return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
        }
    }

    /*
    Use     : Get Location master List
    Author  : Axay shah
    Date 	: 22 April,2019  
    */
    public function ListLocationMaster(Request $request){
    	try{
    		$status 	=	SUCCESS;
	    	$msg 		=	trans("message.RECORD_FOUND");
	    	$data 		= 	LocationMaster::ListLocations($request);
    		if(empty($data)){
    			$msg 	=	trans("message.RECORD_NOT_FOUND");
    		}
    		return response()->json(array("code" => SUCCESS,"msg"=> $msg,"data"=> $data));	
    	}catch(\Exception $e){
           return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
        }
    }

    /*
    Use     : Get By Id
    Author  : Axay shah
    Date 	: 22 April,2019  
    */
    public function getById(Request $request){
    	try{
    		$data 		= 	array();
	    	$status 	=	SUCCESS;
	    	$msg 		=	trans("message.RECORD_FOUND");
	    	if(isset($request->location_id) && !empty($request->location_id)){
	    		$data 		= 	LocationMaster::getById($request);
	    	}
	    	if(empty($data)){
    			$msg 	=	trans("message.RECORD_NOT_FOUND");
    		}
    		return response()->json(array("code" => SUCCESS,"msg"=> $msg,"data"=> $data));	
    	}catch(\Exception $e){
           return response()->json(array("code" => INTERNAL_SERVER_ERROR,"msg"=>trans("message.SOMETHING_WENT_WRONG"),"data"=> ""));
        }
    }

    /*
    Use     : List parameter Type list
    Author  : Axay shah
    Date    : 01 Oct,2018
    */
    public function getParameterType(Request $request){
        $data = Parameter::getParameterType($request);
        (count($data)>0) ?  $msg = trans('message.RECORD_FOUND') :  $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : List parameter list
    Author  : Axay shah
    Date    : 01 Oct,2018
    */
    public function list(Request $request)
    {
        $data = Parameter::parameterList($request);
        (count($data)>0) ?  $msg = trans('message.RECORD_FOUND') :  $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     :  create parameter 
    Author  : Axay shah
    Date    : 01 Oct,2018
    */
    public function create(Request $request)
    {
        $data = Parameter::add($request);
        ($data) ?  $msg     = trans('message.RECORD_INSERTED') :  $msg = trans('message.SOMETHING_WENT_WRONG');
        ($data) ?  $code    = SUCCESS :  $code = INTERNAL_SERVER_ERROR;
        return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     :  Get By Id parameter 
    Author  : Axay shah
    Date    : 01 Oct,2018
    */
    public function getByIdParameter(Request $request)
    {
        $data = Parameter::getById($request);
        ($data) ?  $msg = trans('message.RECORD_FOUND') :  $msg = trans('message.RECORD_NOT_FOUND');
        ($data) ?  $code    = SUCCESS :  $code = INTERNAL_SERVER_ERROR;
        return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : Update parameter 
    Author  : Axay shah
    Date    : 01 Oct,2018
    */   
    public function update(Request $request)
    {
        $data = Parameter::updateRecord($request);
        ($data) ?  $msg = trans('message.RECORD_UPDATED') :  $msg = trans('message.SOMETHING_WENT_WRONG');
        ($data) ?  $code    = SUCCESS :  $code = INTERNAL_SERVER_ERROR;
        return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : Add Peremeter Type
    Author  : Axay shah
    Date    : 01 Oct,2018
    */
    public function addParameterType(Request $request)
    {
        $data = Parameter::addParameterType($request);
        ($data) ?  $msg = trans('message.RECORD_INSERTED') :  $msg = trans('message.SOMETHING_WENT_WRONG');
        ($data) ?  $code    = SUCCESS :  $code = INTERNAL_SERVER_ERROR;
        return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : Update Peremeter Type
    Author  : Axay shah
    Date    : 01 Oct,2018
    */   
    public function updateParameterType(Request $request)
    {
        $data = Parameter::updateParameterType($request);
        ($data) ?  $msg = trans('message.RECORD_UPDATED') :  $msg = trans('message.SOMETHING_WENT_WRONG');
        ($data) ?  $code    = SUCCESS :  $code = INTERNAL_SERVER_ERROR;
        return response()->json(['code' => $code , "msg"=>$msg,"data"=>$data]);
    }
    /**
    * Use       :   Change company parameter status
    * Author    :   Axay Shah
    * Date      :   29 Sep,2018
    */
    public function changeStatus(Request $request){
        $msg            = trans('message.RECORD_NOT_FOUND');
        $changeStatus   = "";
        if(isset($request->id) && isset($request->status)){
            $changeStatus   = Parameter::changeStatus($request->id,$request->status); 
            if(!empty($changeStatus)){
                $msg        = trans('message.STATUS_CHANGED');
            }
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $changeStatus]);
    }
}
