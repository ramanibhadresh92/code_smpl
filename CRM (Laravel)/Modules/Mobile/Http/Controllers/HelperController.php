<?php

namespace Modules\Mobile\Http\Controllers;
use Modules\Mobile\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\HelperAttendance;
use App\Models\Helper;
use App\Models\HelperAttendanceApproval;
use Validator;
use Log;

class HelperController extends LRBaseController
{ 
	/*
	Use     : Search helper name 
	Author  : Upasana
	Date    : 11 Mar 2020
	*/
	public function SearchHelperName(Request $request)
	{   
		$result = Helper::SearchHelper($request);
		$msg    = ($result) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$result]); 
	}  
	/*
	Use     : Date wise Helper attendence list
	Author  : Upasana
	Date    : 11 Mar 2020
	*/
	public function HelperAttendenceDate(Request $request)
	{    
		$result = Helper::HelperAttendence($request);
		$msg    = ($result) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$result]); 
	}
	/*
	Use     : Update Helper Attendence 
	Author  : Upasana
	Date    : 12 Mar 2020
	*/
	public function UpdateAttendence(Request $request)
	{    
		$InTime     = (isset($Request->in_time) && !empty($Request->input('in_time')))? $Request->input('in_time') : "";
        $OutTime    = (isset($Request->out_time) && !empty($Request->input('out_time')))? $Request->input('out_time') : "";
        $Type       = (isset($Request->attendance_type) && !empty($Request->input('attendance_type')))? $Request->input('attendance_type') : 0;
        $UserId     = intval((isset($Request->adminuserid) && !empty($Request->input('adminuserid')))? $Request->input('adminuserid') : 0);
        $userType   = (isset($Request->user_type) && !empty($Request->input('user_type')))? $Request->input('user_type') : 0;
        $reason     = (isset($Request->reason) && !empty($Request->input('reason')))? $Request->input('reason') : "";
		$data = HelperAttendanceApproval::InsertAttendanceApproval($UserId,$userType,$Type,$InTime,'',0,$reason);
		$msg        = trans("message.NO_RECORD_FOUND");
		if($data){
			$msg = trans("message.ATTENDANCE_APPROVAL");
		}
		return  response()->json([
								'code' => SUCCESS , 
								 "msg"  =>$msg,
								 "data" =>$data
								]);
	}
}
