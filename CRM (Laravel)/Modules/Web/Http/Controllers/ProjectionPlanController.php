<?php

namespace Modules\Web\Http\Controllers;
use Modules\Web\Http\Controllers\LRBaseController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\ProjectionPlan;
use App\Models\ProjectionPlanDetails;
use App\Http\Requests\AddProjectionPlan;
use App\Http\Requests\UpdateProjectionPlan;
use App\Http\Requests\AddProjectionPlanDetails;
use App\Http\Requests\UpdateProjectionPlanDetails;
use Validator;
class ProjectionPlanController extends LRBaseController
{
	/*
	Use     : Get ProjetionPlans
	Author  : Kalpak Prajapati
	Date    : 19 Nov,2021
	*/
	public function getprojectionplans(Request $request)
	{
		$data 	= ProjectionPlan::GetProjectionPlans($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Get ProjetionPlanDetails
	Author  : Kalpak Prajapati
	Date    : 19 Nov,2021
	*/
	public function getprojectionplandetails(Request $request)
	{
		$data 	= ProjectionPlan::GetProjectionPlans($request,true);
		$msg 	= (!empty($data)) ? trans("message.RECORD_FOUND") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Add ProjetionP lan
	Author  : Kalpak Prajapati
	Date    : 19 Nov,2021
	*/
	public function addProjectionPlan(AddProjectionPlan $request)
	{
		$data 	= ProjectionPlan::addProjectionPlan($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.VALIDATION_ERROR");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update ProjetionP lan
	Author  : Kalpak Prajapati
	Date    : 19 Nov,2021
	*/
	public function updateProjectionPlan(UpdateProjectionPlan $request)
	{
		$data 	= ProjectionPlan::updateProjectionPlan($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.VALIDATION_ERROR");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Add Projetion Plan Details
	Author  : Kalpak Prajapati
	Date    : 19 Nov,2021
	*/
	public function addProjectionPlanDetail(AddProjectionPlanDetails $request)
	{
		$data 	= ProjectionPlanDetails::addProjectionPlanDetail($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_INSERTED") : trans("message.VALIDATION_ERROR");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Update Projetion Plan Details
	Author  : Kalpak Prajapati
	Date    : 19 Nov,2021
	*/
	public function updateProjectionPlanDetail(UpdateProjectionPlanDetails $request)
	{
		$data 	= ProjectionPlanDetails::updateProjectionPlanDetail($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.VALIDATION_ERROR");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Projetion Plan Widget
	Author  : Kalpak Prajapati
	Date    : 19 Nov,2021
	*/
	public function getProjectionPlanWidget(Request $request)
	{
		$ProjectionPlan = new ProjectionPlan;
		$data 			= $ProjectionPlan->getProjectionPlanWidget($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Projetion Plan status Update 
	Author  : Axay Shah
	Date    : 07 Jan,2022
	*/
	public function ApproveProjectionPlan(Request $request)
	{
		$data 	= ProjectionPlan::ApproveProjectionPlan($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}

	/*
	Use     : Projetion Plan Detail status Update 
	Author  : Axay Shah
	Date    : 07 Jan,2022
	*/
	public function ApproveProjectionPlanDetail(Request $request)
	{
		$data 	= ProjectionPlanDetails::ApproveProjectionPlanDetail($request);
		$msg 	= (!empty($data)) ? trans("message.RECORD_UPDATED") : trans("message.RECORD_NOT_FOUND");
		return response()->json(['code'=>SUCCESS,'msg'=>$msg,'data'=>$data]);
	}
}
