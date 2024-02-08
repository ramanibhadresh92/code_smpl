<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\JobWorkClientMaster;
use App\Models\JobWorkerMaster;
use App\Models\JobWorkMaster;
use App\Models\Parameter;
use App\Http\Requests\JobWorkClientAddressValidation;
use App\Http\Requests\JobWorkMasterValidation;
use App\Http\Requests\AddJobWorkDetails;
use App\Exports\JobworkReport;
use Excel;
class JobWorkMasterController extends LRBaseController
{
	/*
	Use     : Add JobWork Details
	Author  : Upasana
	Date    : 6/2/2020
	*/

	public function InsertJobworkDetails(AddJobWorkDetails $request)
	{
		$data   = JobWorkMaster::AddJobWorkDetails($request);
		$msg 	= (!empty($data)) ? trans("message.JOBWORK_ADDED_SUCCESS") : trans("message.FAILED");
		return response()->json(["data" => $data , "msg" => $msg, "code" => STATUS_CODE_SUCCESS]);
	}

	/*
	Use     : Show JobWork Details
	Author  : Upasana
	Date    : 6/2/2020
	*/
	
	public function ShowJobworkDetails(Request $request)
	{
		$data   = JobWorkMaster::DisplayJobworkdetails($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(["data" => $data , "msg" => $msg, "code" => STATUS_CODE_SUCCESS]);
	}
	/*
	Use     : Approve JobWork Details
	Author  : Upasana
	Date    : 6/2/2020
	*/
	
	public function UpdateDetails(Request $request)
	{
		$data   = JobWorkMaster::ApproveJobworkDetails($request);
		$msg 	= (!empty($data)) ? trans("message.JOBWORK_APPROVED_SUCCESS") : trans("message.FAILED");
		return response()->json(["data" => $data , "msg" => $msg, "code" => STATUS_CODE_SUCCESS]);
	}
	/*
	Use     : Get by Id JobWork Details
	Author  : Upasana
	Date    : 13/2/2020
	*/
	public function JobworkgetById(Request $request)
	{
		$id   = (isset($request->id) && !empty($request->id) ? $request->id : 0);
		$data = JobWorkMaster::getById($id);
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND"):trans("message.RECORD_NOT_FOUND");
		return response()->json(["data" => $data,"msg" =>$msg,'code' => STATUS_CODE_SUCCESS]);
	}
	/*
	Use     : Generate JobWork Dispatch
	Author  : Upasana
	Date    : 27/2/2020
	*/
	public function generateDispatch(Request $request)
	{
		$data = JobWorkMaster::GenerateDirectDispatch($request);
		$msg  = (!empty($data)) ? trans("message.JOBWORK_DISPATCH_GENERATED"):trans("message.FAILED");
		return response()->json(["data" => $data,"msg" =>$msg,'code' => STATUS_CODE_SUCCESS]);
	}
	/*
	Use     : Jobworker Dropdown
	Author  : Upasana
	Date    : 13/2/2020
	*/
	public function JobworkerList(Request $request)
	{
		$data = JobWorkerMaster::JobworkerDropDown($request);
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND"):trans("message.RECORD_NOT_FOUND");
		return response()->json(["data" => $data,"msg" =>$msg,'code' => STATUS_CODE_SUCCESS]);
	}
	/*
	Use     : Generate Jobwork Challan
	Author  : Upasana
	Date    : 28/2/2020
	*/
	public function generateChallan(Request $request)
	{
		$data = JobWorkMaster::GenerateJobworkChallan($request);
		$msg  =(!empty($data)) ? trans("message.JOBWORK_CHALLAN_GENERATED"):trans("message.FAILED");	
		return response()->json(["data" => $data,"msg" =>$msg,'code' => STATUS_CODE_SUCCESS]);
	}
	/*
	Use     : Generate Jobwork Challan
	Author  : Upasana
	Date    : 28/2/2020
	*/
	public function JobworkTypeList(Request $request){
		$data = Parameter::parentDropDown(PARA_JOBWORK_TYPE)->get();
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND"):trans("message.RECORD_NOT_FOUND");
		return response()->json(["data" => $data,"msg" =>$msg,'code' => STATUS_CODE_SUCCESS]);
	}

	/*
	Use     : Generate Jobwork Challan
	Author  : Upasana
	Date    : 28/2/2020
	*/
	public function CreateJobworkParty(Request $request){
		$data = JobWorkerMaster::CreateJobworkParty($request);
		$msg  = (!empty($data)) ? trans("message.RECORD_INSERTED"):trans("message.RECORD_NOT_FOUND");
		return response()->json(["data" => $data,"msg" =>$msg,'code' => STATUS_CODE_SUCCESS]);
	}
	/*
	Use     : Generate Jobwork Challan
	Author  : Upasana
	Date    : 28/2/2020
	*/
	public function UpdateJobworkParty(Request $request){
		$data = JobWorkerMaster::UpdateJobworkParty($request);
		$msg  = (!empty($data)) ? trans("message.RECORD_UPDATED"):trans("message.RECORD_NOT_FOUND");
		return response()->json(["data" => $data,"msg" =>$msg,'code' => STATUS_CODE_SUCCESS]);
	}
	/*
	Use     : Generate Jobwork Challan
	Author  : Upasana
	Date    : 28/2/2020
	*/
	public function ListJobworker(Request $request){
		$data = JobWorkerMaster::ListJobworkerParty($request);
		$msg  = (!empty($data)) ? trans("message.RECORD_FOUND"):trans("message.RECORD_NOT_FOUND");
		return response()->json(["data" => $data,"msg" =>$msg,'code' => STATUS_CODE_SUCCESS]);
	}
	/*
	Use     : Generate Jobwork Challan
	Author  : Upasana
	Date    : 28/2/2020
	*/
	public function GetPartyById(Request $request){
		$ID 	= (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$data 	= JobWorkerMaster::GetPartyById($ID);
		$msg  	= (!empty($data)) ? trans("message.RECORD_FOUND"):trans("message.RECORD_NOT_FOUND");
		return response()->json(["data" => $data,"msg" =>$msg,'code' => STATUS_CODE_SUCCESS]);
	}
	/*
	Use     : Jobwork Report
	Author  : Axay Shah
	Date    : 03 November 2020
	*/
	public function JobworkReport(Request $request){
		$data 	= JobWorkMaster::JobworkReport($request);
		$msg  	= (!empty($data)) ? trans("message.RECORD_FOUND"):trans("message.RECORD_NOT_FOUND");
		return response()->json(["data" => $data,"msg" =>$msg,'code' => STATUS_CODE_SUCCESS]);
	}
	/*
	Use     : Jobwork Report
	Author  : Axay Shah
	Date    : 08 December 2020
	*/
	public function JobworkInwardReportExcel(Request $request){
		$data 		= JobWorkMaster::JobworkReport($request);
		// prd($data);
		$FileName 	= "jobwork.xlsx";
		if(!empty($data) && !empty($data)){
			return Excel::download(new JobworkReport($data,$data),$FileName);
		}
	}
}
