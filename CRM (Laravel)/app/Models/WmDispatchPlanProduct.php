<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmDispatchPlan;
class WmDispatchPlanProduct extends Model
{
	protected 	$table 		= 'wm_dispatch_plan_product';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = false;

	/*
	Use 	: Store Dispatch plan products
	Author 	: Axay Shah
	Date 	: 11 September 2020
	*/
	public static function AddDispatchPlanProduct($plan_id,$sales_product_id,$rate=0,$description="",$qty=0)
	{
		$NewRecord 	= WmDispatchPlanProduct::updateOrCreate(
						[	'sales_product_id' 	=> $sales_product_id,
							'dispatch_plan_id' 	=> $plan_id,
							'rate' 				=> $rate],
						[	'dispatch_plan_id' 	=> $plan_id,
							'sales_product_id' 	=> $sales_product_id,
							'rate' 				=> $rate,
							'description' 		=> $description,
							'created_at' 		=> date("Y-m-d H:i:s"),
							'qty'				=> $qty]);
	}

	/*
	Use 	: Dispatch Plan Approved
	Author 	: Kalpak Prajapati
	Date 	: 05 Aug 2022
	*/
	public static function DispatchPlanApproved($master_dept_id=0,$client_master_id=0,$sales_product_id=0,$rate=0,$date="")
	{
		$WmDispatchPlan = new WmDispatchPlan;
		$self 			= (new static)->getTable();
		$ApprovedBy 	= self::select("DP.id","DP.approved_by")
							->leftjoin($WmDispatchPlan->getTable()." AS DP","$self.dispatch_plan_id","=","DP.id")
							->where("DP.master_dept_id",$master_dept_id)
							->where("DP.client_master_id",$client_master_id)
							->where("$self.sales_product_id",$sales_product_id)
							->where("$self.rate",$rate)
							->where("DP.valid_last_date",">=",date("Y-m-d",strtotime($date)))
							->where("DP.approval_status",REQUEST_APPROVED)
							->first();
		if (!empty($ApprovedBy) && isset($ApprovedBy->approved_by)) {
			return $ApprovedBy;
		} else {
			return 0;
		}
	}
}