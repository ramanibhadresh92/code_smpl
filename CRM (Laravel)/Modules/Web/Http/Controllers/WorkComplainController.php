<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;
use App\Models\Helper;
use App\Models\WorkComplain;
use App\Models\Parameter;
use App\Models\CustomerComplaint;
use Illuminate\Http\Request;

use Validator;
use Log;
class WorkComplainController extends LRBaseController
{

    /**
     * Function Name : List Helper
     * @param $request
     * @return Json
     * @author Sachin Patel
     * @date 29 March, 2019
     */
    public function list(Request $request)
    {
        $data       = [];
        $msg        = trans('message.RECORD_FOUND');
        try {
            $data = WorkComplain::listing($request);
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
     * Function Name : Create
     * @param $request
     * @return Json
     * @author Sachin Patel
     * @date 26 March, 2019
     */
    public function create(Request $request){
        return WorkComplain::createWorkComplain($request);
    }

    /**
     * Function Name : Edit Work Complain
     * @param $request
     * @return Json
     * @author Sachin Patel
     * @date 26 March, 2019
     */
    public function edit(Request $request)
    {
        $msg            = trans('message.RECORD_FOUND');
        $workComplain   = WorkComplain::getWorkComplain($request->work_complain_id);
        if($workComplain) {
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => $workComplain]);
        }else{
            $msg     = trans('message.RECORD_NOT_FOUND');
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => '']);
        }
    }

    /**
     * Function Name : Update
     * @param $request
     * @return Json
     * @author Sachin Patel
     * @date 26 March, 2019
     */
    public function update(Request $request){
        return WorkComplain::updateWorkComplain($request);
    }

    /*
    Use     : Customer Complaint type list
    Author  : Axay Shah
    Date    : 19 June,2019 
    */
    public function complaintType(Request $request){
      $data = Parameter::parentDropDown(PARA_COMPALINT_TYPE)->get();
      (count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
      return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);        
    }

    /*
    Use     : Customer Complaint Status list
    Author  : Axay Shah
    Date    : 19 June,2019 
    */
    public function complaintStatus(Request $request){
      $data = Parameter::parentDropDown(PARA_COMPLAINT_STATUS)->get();
        (count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
      return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);      
    }

    /*
    Use     : Customer Complaint Status list
    Author  : Axay Shah
    Date    : 20 June,2019 
    */
    public function AddCustomerCompalint(Request $request){
        $data = CustomerComplaint::AddCustomerCompalint($request->all());
        ($data > 0) ? $msg = trans('message.RECORD_INSERTED') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);      
    }


    /*
    Use     : Update Customer Complaint 
    Author  : Axay Shah
    Date    : 20 June,2019 
    */
    public function UpdateCustomerCompalint(Request $request){
        $data = CustomerComplaint::UpdateCustomerCompalint($request->all());
        (count($data) > 0) ? $msg = trans('message.RECORD_UPDATED') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);      
    }

    /*
    Use     : Customer Complaint By Id
    Author  : Axay Shah
    Date    : 20 June,2019 
    */
    public function GetById(Request $request){
        $id     = (isset($request->id) && !empty($request->id)) ? $request->id : 0 ;
        $data   = CustomerComplaint::GetById($id);
        (count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);      
    }

    /*
    Use     : List customer complaint
    Author  : Axay Shah
    Date    : 20 June,2019 
    */
    public function ListCustomerComplaint(Request $request){
        $data   = CustomerComplaint::ListCustomerComplaint($request);
        (count($data) > 0) ? $msg = trans('message.RECORD_FOUND') : $msg = trans('message.RECORD_NOT_FOUND');
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);      
    }

}