<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use App\Models\Helper;
use Illuminate\Http\Request;

use Validator;
use Log;
use App\Models\HelperAttendance;
use App\Models\HelperAttendanceApproval;
class HelperController extends LRBaseController
{

    /**
     * Function Name : List Helper
     * @param $request
     * @return Json
     * @author Sachin Patel
     * @date 26 March, 2019
     */
    public function list(Request $request)
    {
        $data       = [];
        $msg        = trans('message.RECORD_FOUND');

        try {
            $data = Helper::getHelperlist($request);
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
        return Helper::createHelper($request);
    }

    /**
     * Function Name : Edit Helper
     * @param $request
     * @return Json
     * @author Sachin Patel
     * @date 26 March, 2019
     */
    public function edit(Request $request)
    {
        $msg     = trans('message.RECORD_FOUND');
        $helper  = Helper::with('profilePicture')->find($request->id);
        if($helper) {
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => $helper]);
        }else{
            $msg     = trans('message.RECORD_NOT_FOUND');
            return response()->json(["code" => SUCCESS, "msg" => $msg, "data" => '']);
        }
    }

    /**
     * Function Name : Update Helper
     * @param $request
     * @return Json
     * @author Sachin Patel
     * @date 26 March, 2019
     */
    public function update(Request $request){
        return Helper::updateHelper($request);
    }

    /**
    Use     : List Driver Attandace Month wise
    Author  : Axay Shah
    Date    : 31 July,2019
    */
    public function listDriverAttendance(Request $Request)
    {
        $Month  = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));
        $Year   = intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
        $UserId = intval((isset($Request->adminuserid) && !empty($Request->input('adminuserid')))? $Request->input('adminuserid') : 0);
        $Month  = empty($Month)?date("m"):$Month;
        $Year   = empty($Year)?date("Y"):$Year;
        $starttime  = $Year."-".$Month."-01 00:00:00";
        $endtime    = date("Y-m-t",strtotime($starttime))." 23:59:59";
        $result     =  HelperAttendance::ListDriverAttandance($UserId,$starttime,$endtime,$Month,$Year);
        if (empty($result)) {
            return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result]);
        } else {
            return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result]);
        }
    }

    /**
    Use     : Edit Driver Attendance
    Author  : Axay Shah
    Date    : 1 Aug,2019
    */
    public function EditAttendance(Request $Request)
    {
        $msg        = trans("message.NO_RECORD_FOUND");
        $InTime     = (isset($Request->in_time) && !empty($Request->input('in_time')))? $Request->input('in_time') : "";
        $OutTime    = (isset($Request->out_time) && !empty($Request->input('out_time')))? $Request->input('out_time') : "";
        $Type       = (isset($Request->attendance_type) && !empty($Request->input('attendance_type')))? $Request->input('attendance_type') : 0;
        $userType   = (isset($Request->user_type) && !empty($Request->input('user_type')))? $Request->input('user_type') : 0;
        $reason     = (isset($Request->reason) && !empty($Request->input('reason')))? $Request->input('reason') : "";
        $UserId     = intval((isset($Request->adminuserid) && !empty($Request->input('adminuserid')))? $Request->input('adminuserid') : 0);

        $data = HelperAttendanceApproval::InsertAttendanceApproval($UserId,$userType,$Type,$InTime,'',0,$reason);
        if($data){
            $msg = trans("message.ATTENDANCE_APPROVAL");
        }
        return  response()->json([
                                'code' => SUCCESS , 
                                 "msg"  =>$msg,
                                 "data" =>$data
                                ]);
    }

    /**
    Use     : List Driver Attandace Month wise
    Author  : Axay Shah
    Date    : 31 July,2019
    */
    public function listHelperAttendance(Request $Request)
    {
        $Month  = intval((isset($Request->month) && !empty($Request->input('month')))? $Request->input('month') : date("m"));
        $Year   = intval((isset($Request->year) && !empty($Request->input('year')))? $Request->input('year') : date("Y"));
        $UserId = intval((isset($Request->adminuserid) && !empty($Request->input('adminuserid')))? $Request->input('adminuserid') : 0);
        $Month  = empty($Month)?date("m"):$Month;
        $Year   = empty($Year)?date("Y"):$Year;
        $starttime    = $Year."-".$Month."-01 00:00:00";
        $endtime      = date("Y-m-t",strtotime($starttime))." 23:59:59";
        return HelperAttendance::ListHelperAttandance($UserId,$starttime,$endtime,$Month,$Year);
    }

     /**
    Use     : Edit Helper Attendance
    Author  : Axay Shah
    Date    : 1 Aug,2019
    */
    public function EditHelperAttendance(Request $Request)
    {
        $msg        = trans("message.NO_RECORD_FOUND");
        $InTime     = (isset($Request->in_time) && !empty($Request->input('in_time')))? $Request->input('in_time') : "";
        $OutTime    = (isset($Request->out_time) && !empty($Request->input('out_time')))? $Request->input('out_time') : "";
        $Type       = (isset($Request->attendance_type) && !empty($Request->input('attendance_type')))? $Request->input('attendance_type') : 0;
        $userType   = (isset($Request->user_type) && !empty($Request->input('user_type')))? $Request->input('user_type') : 0;
        $UserId     = intval((isset($Request->adminuserid) && !empty($Request->input('adminuserid')))? $Request->input('adminuserid') : 0);
        $reason     = (isset($Request->reason) && !empty($Request->input('reason')))? $Request->input('reason') : "";
        $data = HelperAttendanceApproval::InsertAttendanceApproval($UserId,$userType,$Type,$InTime,'',0,$reason);
        if($data){
            $msg = trans("message.ATTENDANCE_APPROVAL");
        }
        return  response()->json([
                                'code' => SUCCESS , 
                                 "msg"  =>$msg,
                                 "data" =>$data
                                ]);
    }

    /*
    Use     : List Helper Attendance Approval
    Author  : Axay Shah
    Date    : 23 March 2020
    */
    public function ListHelperAttendanceApproval(Request $request){    
        $result = HelperAttendanceApproval::ListHelperAttendanceApproval($request);
        $msg    = ($result) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$result]); 
    }

    /*
    Use     : Approve attendance request
    Author  : Axay shah
    Date    : 23 March 2020
    */
    public function approveAttendanceRequest(Request $request){    
        $id     = (isset($request->id) && !empty($request->id)) ? $request->id :0;
        $status = (isset($request->status) && !empty($request->status)) ? $request->status :0;
        $result = HelperAttendanceApproval::ApproveAttendanceRequest($status,$id);
        if($result > 0)
        {
            LR_Modules_Log_CompanyUserActionLog($request,$request->id);
        }
        $msg    = ($result > 0) ? trans("message.ATTENDANCE_UPDATED") : trans("message.RECORD_NOT_FOUND");
        return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$result]); 
    }
}