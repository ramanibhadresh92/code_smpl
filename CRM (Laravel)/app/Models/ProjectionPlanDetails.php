<?php

namespace App\Models;
use App\Facades\LiveServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;
use App\Models\WmDepartment;
use App\Models\WmClientMaster;
class ProjectionPlanDetails extends Model
{
	protected 	$table 		= 'wm_projection_plan_details';
	protected 	$guarded 	= ['id'];
	protected 	$primaryKey = 'id'; // or null
	public 		$timestamps = true;

	/*
	Use 	: Get Projection Plans Details By ID
	Author 	: Kalpak Prajapati
	Date 	: 19 Nov 2021
	*/
	public static function GetProjectionPlan($wm_projection_plan_id=0,$widget=false)
	{
		$arrResult 		= array();
		$self 			= (new static)->getTable();
		$AdminUser 		= new AdminUser();
		$Department 	= new WmDepartment();
		$CM 			= new WmClientMaster();
		$SelectSql 		= self::select(	\DB::raw("$self.*"),
										\DB::raw("IF($self.plan_type = 1,'Dispatch','Transfer') as Plan_Type"),
										\DB::raw("IF($self.plan_type = 1,CM.client_name,CMS.department_name) as C_Name"),
										\DB::raw("CONCAT(U1.firstname,' ',U1.lastname) as created_by_name"),
										\DB::raw("CONCAT(U2.firstname,' ',U2.lastname) as updated_by_name"));
		$SelectSql->leftjoin($Department->getTable()." AS CMS","$self.plan_to","=","CMS.id");
		$SelectSql->leftjoin($CM->getTable()." AS CM","$self.plan_to","=","CM.id");
		$SelectSql->leftjoin($AdminUser->getTable()." as U1","$self.created_by","=","U1.adminuserid");
		$SelectSql->leftjoin($AdminUser->getTable()." as U2","$self.updated_by","=","U2.adminuserid");
		$SelectSql->where("$self.wm_projection_plan_id",$wm_projection_plan_id);
		$arrResult 	= $SelectSql->get()->toArray();
		if (!$widget) {
			return $arrResult;
		} else {
			$returnResult = array();
			foreach ($arrResult as $ResultRow) {
				$Suffix 		= ($ResultRow['plan_type'] == 1)?"":"&nbsp;(T)";
				$returnResult[] = array("Name"=>$ResultRow['C_Name'].$Suffix,
										"Qty"=>_FormatNumberV2(trim($ResultRow['qty']),0,true),
										"Rate"=>_FormatNumberV2(trim($ResultRow['rate']),2,true),
										"Total_Amount"=>_FormatNumberV2(round((trim($ResultRow['qty']) * trim($ResultRow['rate'])),2),2,true),
										"Remark"=>$ResultRow['remarks']);
			}
			return $returnResult;
		}
	}

	/*
	Use 	: Add Projection Plan Details
	Author 	: Kalpak Prajapati
	Date 	: 19 Nov 2021
	*/
	public static function addProjectionPlanDetail($request)
	{
		$id 					= 0;
		$wm_projection_plan_id 	= (isset($request->wm_projection_plan_id) && !empty($request->wm_projection_plan_id))?$request->wm_projection_plan_id:0;
		if (!empty($request->projection_plan_list)) {
			$projection_plan_lists = json_decode($request->projection_plan_list);
			if (!empty($projection_plan_lists)) {
				foreach ($projection_plan_lists as $projection_plan_list) {
					$plan_type 				= (isset($projection_plan_list->plan_type) && !empty($projection_plan_list->plan_type))?$projection_plan_list->plan_type:0;
					$plan_to 				= (isset($projection_plan_list->plan_to) && !empty($projection_plan_list->plan_to))?$projection_plan_list->plan_to:0;
					$qty 					= (isset($projection_plan_list->qty) && !empty($projection_plan_list->qty))?$projection_plan_list->qty:0;
					$rate 					= (isset($projection_plan_list->rate) && !empty($projection_plan_list->rate))?$projection_plan_list->rate:0;
					$remarks 				= (isset($projection_plan_list->remarks) && !empty($projection_plan_list->remarks))?$projection_plan_list->remarks:"";
					$status 				= (isset($projection_plan_list->status) && !empty($projection_plan_list->status))?$projection_plan_list->status:0;

					$Add 						= new self();
					$Add->wm_projection_plan_id = $wm_projection_plan_id;
					$Add->plan_type 			= $plan_type;
					$Add->plan_to 				= $plan_to;
					$Add->qty 					= $qty;
					$Add->rate 					= $rate;
					$Add->remarks 				= $remarks;
					$Add->status 				= $status;
					$Add->created_by 			= Auth()->user()->adminuserid;
					$Add->updated_by 			= Auth()->user()->adminuserid;
					if($Add->save()) {
						$id = $Add->id;
						LR_Modules_Log_CompanyUserActionLog($request,$id);
					}
				}
			}
		}
		return $id;
	}

	/*
	Use 	: Update Projection Plan Details
	Author 	: Kalpak Prajapati
	Date 	: 19 Nov 2021
	*/
	public static function updateProjectionPlanDetail($request)
	{
		$id 					= (isset($request['id'])&&!empty($request['id']))?$request['id']:0;
		$ProjectionPlanDetails 	= self::find($id);
		if($ProjectionPlanDetails) {
			$wm_projection_plan_id 	= (isset($request->wm_projection_plan_id) && !empty($request->wm_projection_plan_id))?$request->wm_projection_plan_id:0;
			$plan_type 				= (isset($request->plan_type) && !empty($request->plan_type))?$request->plan_type:0;
			$plan_to 				= (isset($request->plan_to) && !empty($request->plan_to))?$request->plan_to:0;
			$qty 					= (isset($request->qty) && !empty($request->qty))?$request->qty:0;
			$rate 					= (isset($request->rate) && !empty($request->rate))?$request->rate:0;
			$remarks 				= (isset($request->remarks) && !empty($request->remarks))?$request->remarks:"";
			$status 				= (isset($request->status) && !empty($request->status))?$request->status:0;
			$ProjectionPlanDetails->wm_projection_plan_id 	= $wm_projection_plan_id;
			$ProjectionPlanDetails->plan_type 				= $plan_type;
			$ProjectionPlanDetails->plan_to 				= $plan_to;
			$ProjectionPlanDetails->qty 					= $qty;
			$ProjectionPlanDetails->rate					= $rate;
			$ProjectionPlanDetails->remarks					= $remarks;
			$ProjectionPlanDetails->status 					= $status;
			$ProjectionPlanDetails->updated_by 				= Auth()->user()->adminuserid;
			$ProjectionPlanDetails->save();
			LR_Modules_Log_CompanyUserActionLog($request,$id);
			return true;
		}
		return false;
	}
	/*
	Use 	: Projection Plan Detail Approval 
	Author 	: Axay Shah
	Date 	: 07 Jan 2021
	*/
	public static  function ApproveProjectionPlanDetail($request)
	{
		$ID 	= (isset($request->id) && !empty($request->id)) ? $request->id : array();
		$STATUS = (isset($request->status) && !empty($request->status)) ? $request->status : 0;
		if(!empty($ID)){
			foreach($ID as $value){
				$data 	= ProjectionPlan::where("id",$value)->update(["status"=>$STATUS,"approved_date"=>date("Y-m-d H:i:s"),"approved_by" => Auth()->user()->adminuserid]);
				LR_Modules_Log_CompanyUserActionLog($request,$value);
			}
		}
	
		return $data;
	}
}