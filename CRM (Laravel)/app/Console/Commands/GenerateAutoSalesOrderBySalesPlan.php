<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyProjectionPlanLog;
use App\Models\DailyProjectionPlanDetails;
use App\Models\DailyProjectionPlan;
use App\Models\WmProductMaster;
use App\Models\WmClientMaster;
use App\Models\WmDepartment;
use App\Models\AdminUser;
use App\Models\WmDispatchPlanProduct;
use App\Models\WmDispatchPlan;
use App\Models\WmDispatch;
use DB;

class GenerateAutoSalesOrderBySalesPlan extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateAutoSalesOrderBySalesPlan';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate Auto Sales Order By Sales Plan.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";

		$DailyProjectionPlan 		= (new DailyProjectionPlan)->getTable();
		$DailyProjectionPlanDetails = (new DailyProjectionPlanDetails)->getTable();
		$WmProductMaster 			= (new WmProductMaster)->getTable();
		$WmClientMaster 			= (new WmClientMaster)->getTable();
		$WmDepartment 				= (new WmDepartment)->getTable();
		$AdminUser 					= (new AdminUser)->getTable();
		$LogRecords 				= DailyProjectionPlanLog::whereIn("processed",[0,2])->orderBy("created_at","ASC")->get();

		if (!empty($LogRecords)) {
			foreach ($LogRecords as $LogRecord) {
				/** UPDATE STATUS TO IN PROCESS */
				$LogRecord->processed = 2;
				$LogRecord->save();
				/** UPDATE STATUS TO IN PROCESS */

				/** GENERATE SALES ORDER PLAN */
				$SelectSql 	= "	SELECT $DailyProjectionPlan.mrf_id AS mrf_id,
								$DailyProjectionPlan.projection_date AS plan_date,
								$DailyProjectionPlan.approved_by AS approved_by,
								CONCAT(AB.firstname,' ',AB.lastname) as ApprovedBy,
								$DailyProjectionPlan.approved_date AS approved_date,
								$DailyProjectionPlan.product_id AS sales_product_id,
								$DailyProjectionPlanDetails.plan_to as wm_client_master_id,
								$DailyProjectionPlanDetails.qty as dispatch_qty,
								$DailyProjectionPlanDetails.rate as dispatch_rate,
								$DailyProjectionPlanDetails.created_by as relationship_manager_id,
								CONCAT(CB.firstname,' ',CB.lastname) as CreatedBy,
								$WmProductMaster.recyclable as recyclable,
								$WmClientMaster.days as collection_cycle_term,
								$WmDepartment.company_id as company_id
								FROM $DailyProjectionPlanDetails
								INNER JOIN $DailyProjectionPlan ON $DailyProjectionPlanDetails.wm_daily_projection_plan_id = $DailyProjectionPlan.id
								INNER JOIN $WmProductMaster ON $WmProductMaster.id = $DailyProjectionPlan.product_id
								LEFT JOIN $WmClientMaster ON $WmClientMaster.id = $DailyProjectionPlanDetails.plan_to
								LEFT JOIN $WmDepartment ON $WmDepartment.id = $DailyProjectionPlan.mrf_id
								LEFT JOIN $AdminUser as AB ON AB.adminuserid = $DailyProjectionPlan.approved_by
								LEFT JOIN $AdminUser as CB ON CB.adminuserid = $DailyProjectionPlanDetails.created_by
								WHERE $DailyProjectionPlanDetails.plan_type = 1
								AND $DailyProjectionPlan.id = ".$LogRecord->wm_daily_projection_plan_id."
								AND $WmClientMaster.id IS NOT NULL";
				$SelectRes  = DB::connection()->select($SelectSql);
				if (!empty($SelectRes))
				{
					foreach($SelectRes as $SelectRow)
					{
						$dispatch_type 				= ($SelectRow->recyclable == 0)?NON_RECYCLEBLE_TYPE:RECYCLEBLE_TYPE;
						$client_master_id 			= $SelectRow->wm_client_master_id;
						$origin 					= 0; //Not Direct Dispatch
						$dispatch_plan_date 		= $SelectRow->plan_date;
						$valid_last_date 			= $SelectRow->plan_date;
						$master_dept_id 			= $SelectRow->mrf_id;
						$direct_dispatch 			= 0; //Not Direct Dispatch
						$relationship_manager_id 	= $SelectRow->relationship_manager_id;
						$collection_cycle_term 		= $SelectRow->collection_cycle_term;
						$approval_status 			= 1;
						$approved_by 				= $SelectRow->approved_by;
						$approval_date 				= $SelectRow->approved_date;
						$trip 						= 1;
						$transporter_po_no 			= "";
						$transporter_po_media_id 	= 0;
						$company_id 				= $SelectRow->company_id;
						$is_dispatch 				= 1;
						$created_by 				= $SelectRow->relationship_manager_id;
						$updated_by 				= $SelectRow->relationship_manager_id;
						$sales_product_id 			= $SelectRow->sales_product_id;
						$qty 						= $SelectRow->dispatch_qty;
						$rate 						= $SelectRow->dispatch_rate;
						$ApprovedBy 				= $SelectRow->ApprovedBy;
						$CreatedBy 					= $SelectRow->CreatedBy;

						$IsRecordExists = "	SELECT wm_dispatch_plan_product.id,
											wm_dispatch_plan_product.dispatch_plan_id,
											wm_dispatch_plan_product.remarks,
											wm_dispatch_plan.approval_status,
											wm_dispatch_plan_product.rate
											FROM wm_dispatch_plan_product
											LEFT JOIN wm_dispatch_plan ON wm_dispatch_plan.id = wm_dispatch_plan_product.dispatch_plan_id
											WHERE wm_dispatch_plan_product.sales_product_id = ".$sales_product_id."
											AND wm_dispatch_plan.client_master_id = ".$client_master_id."
											AND wm_dispatch_plan.master_dept_id = ".$master_dept_id."
											AND '".$dispatch_plan_date."' BETWEEN wm_dispatch_plan.dispatch_plan_date AND wm_dispatch_plan.valid_last_date";
						$IsRecordRes  	= DB::connection()->select($IsRecordExists);
						if (!empty($IsRecordRes)) {
							foreach ($IsRecordRes as $IsRecordRow) {
								$Remarks = "";
								if (!empty($IsRecordRow->remarks)) {
									$Remarks = $IsRecordRow->remarks."\r\n";
								}
								$Remarks .= "Rate has been updated on ".date("Y-m-d H:i:s")." from old rate (".$IsRecordRow->rate.") to new rate (".$rate.") based on daily dispatch plan created by (".$CreatedBy.") and approved by (".$ApprovedBy.")";
								$WmDispatchPlanProduct 			= WmDispatchPlanProduct::find($IsRecordRow->id);
								$WmDispatchPlanProduct->rate 	= $rate;
								$WmDispatchPlanProduct->remarks = $Remarks;
								$WmDispatchPlanProduct->save();
								if ($IsRecordRow->approval_status != 0) {
									$WmDispatchPlan 					= WmDispatchPlan::find($IsRecordRow->dispatch_plan_id);
									$WmDispatchPlan->approval_status	= 1;
									$WmDispatchPlan->approved_by		= $approved_by;
									$WmDispatchPlan->approval_date		= $approval_date;
									$WmDispatchPlan->save();
								}
							}
						} else {
							$WmDispatchPlan 							= new WmDispatchPlan;
							$WmDispatchPlan->dispatch_type 				= $dispatch_type;
							$WmDispatchPlan->client_master_id 			= $client_master_id;
							$WmDispatchPlan->origin 					= $origin;
							$WmDispatchPlan->dispatch_plan_date 		= $dispatch_plan_date;
							$WmDispatchPlan->valid_last_date 			= $dispatch_plan_date;
							$WmDispatchPlan->master_dept_id 			= $master_dept_id;
							$WmDispatchPlan->direct_dispatch 			= $direct_dispatch;
							$WmDispatchPlan->relationship_manager_id 	= $relationship_manager_id;
							$WmDispatchPlan->collection_cycle_term 		= $collection_cycle_term;
							$WmDispatchPlan->approval_status 			= $approval_status;
							$WmDispatchPlan->approved_by 				= $approved_by;
							$WmDispatchPlan->approval_date 				= $approval_date;
							$WmDispatchPlan->cancel_reason 				= "";
							$WmDispatchPlan->trip 						= $trip;
							$WmDispatchPlan->transporter_po_no 			= $transporter_po_no;
							$WmDispatchPlan->transporter_po_media_id 	= $transporter_po_media_id;
							$WmDispatchPlan->transporter_po_media_id 	= $transporter_po_media_id;
							$WmDispatchPlan->company_id 				= $company_id;
							$WmDispatchPlan->is_dispatch 				= $is_dispatch;
							$WmDispatchPlan->created_by 				= $created_by;
							$WmDispatchPlan->updated_by 				= $updated_by;
							if ($WmDispatchPlan->save()) {
								$Remarks = "Rate has been approved on ".date("Y-m-d H:i:s")." based on daily dispatch plan created by (".$CreatedBy.") and approved by (".$ApprovedBy.")";
								$WmDispatchPlanProduct 						= new WmDispatchPlanProduct;
								$WmDispatchPlanProduct->dispatch_plan_id 	= $WmDispatchPlan->id;
								$WmDispatchPlanProduct->sales_product_id 	= $sales_product_id;
								$WmDispatchPlanProduct->description 		= "";
								$WmDispatchPlanProduct->qty 				= $qty;
								$WmDispatchPlanProduct->rate 				= $rate;
								$WmDispatchPlanProduct->remarks 			= $Remarks;
								$WmDispatchPlanProduct->save();
							}
						}
					}
				}
				/** GENERATE SALES ORDER PLAN */

				/** UPDATE STATUS TO PROCESSED */
				$LogRecord->processed = 1;
				$LogRecord->save();
				/** UPDATE STATUS TO IN PROCESSED */
			}
		}

		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}