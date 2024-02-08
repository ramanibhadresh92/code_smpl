<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\DailyProjectionPlan;
use App\Models\DailyProjectionPlanDetails;
use App\Http\Requests\AddDailyProjectionPlan;
use App\Http\Requests\UpdateDailyProjectionPlan;
use App\Http\Requests\AddDailyProjectionPlanDetails;
use App\Http\Requests\UpdateDailyProjectionPlanDetails;
use Validator;
class DailyProjectionPlanController extends LRBaseController
{
	/*
	Use     : Get ProjetionPlans
	Author  : Kalpak Prajapati
	Date    : 01 Aug 2022
	*/
	public function getprojectionplans(Request $request)
	{
		$data 	= DailyProjectionPlan::GetProjectionPlans($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get ProjetionPlanDetails
	Author  : Kalpak Prajapati
	Date    : 01 Aug 2022
	*/
	public function getprojectionplandetails(Request $request)
	{
		$data 	= DailyProjectionPlan::GetProjectionPlans($request,true);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Add ProjetionP lan
	Author  : Kalpak Prajapati
	Date    : 01 Aug 2022
	*/
	public function addProjectionPlan(AddDailyProjectionPlan $request)
	{
		$data 	= DailyProjectionPlan::addProjectionPlan($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.VALIDATION_ERROR");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update ProjetionP lan
	Author  : Kalpak Prajapati
	Date    : 01 Aug 2022
	*/
	public function updateProjectionPlan(UpdateDailyProjectionPlan $request)
	{
		$data 	= DailyProjectionPlan::updateProjectionPlan($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.VALIDATION_ERROR");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Add Projetion Plan Details
	Author  : Kalpak Prajapati
	Date    : 01 Aug 2022
	*/
	public function addProjectionPlanDetail(AddDailyProjectionPlanDetails $request)
	{
		$data 	= DailyProjectionPlanDetails::addProjectionPlanDetail($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.VALIDATION_ERROR");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Projetion Plan Details
	Author  : Kalpak Prajapati
	Date    : 01 Aug 2022
	*/
	public function updateProjectionPlanDetail(UpdateDailyProjectionPlanDetails $request)
	{
		$data 	= DailyProjectionPlanDetails::updateProjectionPlanDetail($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.VALIDATION_ERROR");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Projetion Plan Widget
	Author  : Kalpak Prajapati
	Date    : 01 Aug 2022
	*/
	public function getProjectionPlanWidget(Request $request)
	{
		$ProjectionPlan = new DailyProjectionPlan;
		$data 			= $ProjectionPlan->getProjectionPlanWidget($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Projetion Plan status Update 
	Author  : Kalpak Prajapati
	Date    : 01 Aug 2022
	*/
	public function ApproveProjectionPlan(Request $request)
	{
		$data 	= DailyProjectionPlan::ApproveProjectionPlan($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Projetion Plan Detail status Update 
	Author  : Kalpak Prajapati
	Date    : 01 Aug 2022
	*/
	public function ApproveProjectionPlanDetail(Request $request)
	{
		$data 	= DailyProjectionPlanDetails::ApproveProjectionPlanDetail($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
}
