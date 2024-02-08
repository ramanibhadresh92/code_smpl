<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\CompanyParameter;
use App\Models\BaseLocationMaster;
use App\Http\Requests\CompanyParameterAdd;
use App\Http\Requests\CompanyParameterUpdate;
use App\Http\Requests\CompanyParameterTypeUpdate;
use App\Http\Requests\CompanyParameterTypeAdd;
use App\Http\Requests\AddBaseLocation;
use App\Http\Requests\EditBaseLocation;
use App\Models\Parameter;
// //use Roketin\Auditing\Log;
class CompanyParameterController extends LRBaseController
{
    public function index()
    {
        // $data =CompanyParameter::with('parameter')->get();
        // $data =  $user->with('parameter');
        // $team = CompanyParameter::find(1025); // Get team
        // $team->logs; // Get all logs
        // dd($team->toArray());
        // $data = $team->logs->toArray();
        return response()->json(['code' => SUCCESS , "msg"=>"","data"=>$data]);
    
        
        // // dd( $team->logs->find(2)->toArray());
        // $team->logs->first(); // Get first log
        // $team->logs->last();  // Get last log
        // $team->logs->find(2); // Selects log
        
    }

    /*
    Use     : List parameter Type list
    Author  : Axay shah
    Date    : 01 Oct,2018
    */
    public function getParameterType(Request $request){
        $data = CompanyParameter::getParameterType($request);
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
        $data = CompanyParameter::parameterList($request);
        (count($data)>0) ?  $msg = trans('message.RECORD_FOUND') :  $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     :  create parameter 
    Author  : Axay shah
    Date    : 01 Oct,2018
    */
   	public function create(CompanyParameterAdd $request)
    {
        $data = CompanyParameter::add($request);
        ($data) ?  $msg = trans('message.RECORD_INSERTED') :  $msg = trans('message.SOMETHING_WENT_WRONG');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     :  Get By Id parameter 
    Author  : Axay shah
    Date    : 01 Oct,2018
    */
    public function getById(Request $request)
    {
        $data = CompanyParameter::getById($request);
        ($data) ?  $msg = trans('message.RECORD_FOUND') :  $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : Update parameter 
    Author  : Axay shah
    Date    : 01 Oct,2018
    */   
    public function update(CompanyParameterUpdate $request)
    {
        $data = CompanyParameter::updateRecord($request);
        ($data) ?  $msg = trans('message.RECORD_UPDATED') :  $msg = trans('message.SOMETHING_WENT_WRONG');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : Add Peremeter Type
    Author  : Axay shah
    Date    : 01 Oct,2018
    */
    public function addParameterType(CompanyParameterTypeAdd $request)
    {
        $data = CompanyParameter::addParameterType($request);
        ($data) ?  $msg = trans('message.RECORD_INSERTED') :  $msg = trans('message.SOMETHING_WENT_WRONG');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }
    /*
    Use     : Update Peremeter Type
    Author  : Axay shah
    Date    : 01 Oct,2018
    */   
    public function updateParameterType(CompanyParameterTypeUpdate $request)
    {
        $data = CompanyParameter::updateParameterType($request);
        ($data) ?  $msg = trans('message.RECORD_UPDATED') :  $msg = trans('message.SOMETHING_WENT_WRONG');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
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
            $changeStatus   = CompanyParameter::changeStatus($request->id,$request->status); 
            if(!empty($changeStatus)){
                LR_Modules_Log_CompanyUserActionLog($request,$request->id);
                $msg        = trans('message.STATUS_CHANGED');
            }
        }
        return response()->json(["code" =>SUCCESS,"msg" =>$msg,"data" => $changeStatus]);
    }

    /*
    Use     : Add Base Location Data
    Author  : Axay shah
    Date    : 23 April,2019
    */
    public function AddBaseLocation(AddBaseLocation $request)
    {
        $data = BaseLocationMaster::AddBaseLocation($request);
        ($data) ?  $msg = trans('message.RECORD_INSERTED') :  $msg = trans('message.SOMETHING_WENT_WRONG');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    /*
    Use     : Update Base Location Data
    Author  : Axay shah
    Date    : 23 April,2019
    */
    public function EditBaseLocation(Request $request)
    {
        $data = BaseLocationMaster::EditBaseLocation($request);
        ($data) ?  $msg = trans('message.RECORD_UPDATED') :  $msg = trans('message.SOMETHING_WENT_WRONG');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    /*
    Use     : Get By Id parameter 
    Author  : Axay shah
    Date    : 01 Oct,2018
    */
    public function BaseLocationById(Request $request)
    {   $data = false;
        if(isset($request->id) && !empty($request->id)){
            $data = BaseLocationMaster::getById($request->id);
        }
        ($data) ?  $msg = trans('message.RECORD_FOUND') :  $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    /*
    Use     : List Base Location with city
    Author  : Axay shah
    Date    : 01 Oct,2018
    */
    public function BaseLocationList(Request $request)
    {
        $data = BaseLocationMaster::ListBaseLocation($request);
        ($data) ?  $msg = trans('message.RECORD_FOUND') :  $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    }

    /**
    * Use       : List Product 2d 3d type
    * Author    : Axay Shah
    * Date      : 24 April 2020
    */
    public function TypeOfProductTagging(Request $request)
    {
        $data = Parameter::parentDropDown(TYPE_OF_PRODUCT_TAGGING)->get();
        (!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    } 
    /**
    * Use       : LIST NET SUIT CLASS
    * Author    : Axay Shah
    * Date      : 24 April 2020
    */
    public function NetSuitClassList(Request $request)
    {
        $data = Parameter::parentDropDown(PARA_NET_SUIT_CLASS)->get();
        (!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    } 

    /**
    * Use       : LIST NET SUIT DEPARTMENT
    * Author    : Axay Shah
    * Date      : 24 April 2020
    */
    public function NetSuitDepartmentList(Request $request)
    {
        $data = Parameter::parentDropDown(PARA_NET_SUIT_DEPARTMENT)->get();
        (!empty($data)) ? $msg = trans("message.RECORD_FOUND") : $msg = trans("message.RECORD_NOT_FOUND");
        return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
    } 
}
