<?php
namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Audits;
use App\Models\AutoprocessInfo;
use App\Models\AutoprocessInfoAuditLog;
use App\Models\CompanyUserActionLog;
use App\Http\Requests\AutoprocessAuditLogCreateRequest;
use DB;
class LogMasterController extends LRBaseController
{

	/*
	Use     : GET AUDIT LOG MASTER REPORT
	Author  : Axay Shah
	Date 	: 17 jan,2022
	*/
	public function AuditLogReport(Request $request){
		$data 		= Audits::AuditLogReport($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : GET AUTO PROCESS LIST DATA FROM autoprocess_info Table
	Author  : Hardyesh Gupta
	Date 	: 20 jan,2023
	*/
	public function AutoProcessList(Request $request)
	{
		$data       = [];
		$msg        = trans('message.RECORD_FOUND');
		try {
			$data = AutoprocessInfo::getAutoProcessList($request);
			return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
		}
		catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
	}

	/*
	Use     : Print Log File
	Author  : Axay Shah
	Date 	: 27 jan,2023
	*/
	public function PrintLogFile(Request $request)
	{
		try {
			$id =  (isset($request->id) && !empty($request->id)) ? $request->id : 0;
			if(!empty($id)){
				$data 		= AutoprocessInfo::find(passdecrypt($id));
				if($data){
					$path 		= storage_path("logs/".$data->logfile_name);
					$logfile 	= nl2br( file_get_contents($path));
					echo $logfile;
					exit;
				}
				return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
			}
		}
		catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
	}

	/*
	Use     : Create/Insert AutoprocessInfo AuditLog Record in autoprocess_info_audit_log table
	Author  : Hardyesh Gupta
	Date 	: 30 Jan 2023
	*/
	public function createAutoprocessAuditLog(Request $request)
	{
		$msg = trans('message.AUTOPROCESS_AUDIT_LOG_INSERT');
		try {
			$data = AutoprocessInfoAuditLog::InsertAutoprocessAuditLog($request);
			if($data == 0){
				 $msg = trans('message.AUTOPROCESS_AUDIT_LOG_RECORD_EXIST');
			}
		return response()->json(['code' => SUCCESS , "msg"=>$msg,"data"=>$data]);
		}
		catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>$data]);
		}
	}

	/*
	Use     : Get Log Report
	Author  : Axay Shah
	Date 	: 07 Feb,2023
	*/
	public function LogReport(Request $request){
		$data 		= CompanyUserActionLog::LogReport($request);
		$msg 		= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/**
	* Function Name : GetActionTitleList
	* @param $request
	* @return object
	* @author Kalpak Prajapati
	* @since 16 Feb 2023
	*/
	public function GetActionTitleList(Request $request)
	{
		try {
			$LogActionTitleList = DB::table("company_user_action_master")->select("action_id","action_title")->where("status",PARA_STATUS_ACTIVE)->orderBy("action_title","ASC")->get()->toArray();
			return response()->json(['code' => SUCCESS , "msg"=>trans("message.RECORD_FOUND"),"data"=>$LogActionTitleList]);
		}
		catch (\Exception $e) {
			return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>[]]);
		}
	}
}
