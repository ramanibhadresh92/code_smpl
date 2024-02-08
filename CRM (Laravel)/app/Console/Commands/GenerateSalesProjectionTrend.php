<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyMaster;
use App\Models\WmDepartment;
use DB;
class GenerateSalesProjectionTrend extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateSalesProjectionTrend';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command use to Generate Sales Projection Trend';

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
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', '-1');
		set_time_limit(0);
		echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";
		$Days_Sales 	= 60;
		$Dispatch_Date 	= date("Y-m-d",strtotime("- $Days_Sales Days"))." 00:00:00";
		$Today 			= date("Y-m-d");
		$CompanyMaster  = new CompanyMaster;
		$WmDepartment  	= new WmDepartment;
		$arrCompany     = $CompanyMaster->select('company_id')->where('status','Active')->get();
		if (!empty($arrCompany))
		{
			foreach($arrCompany as $Company)
			{
				$arrMRF     = $WmDepartment->select('id','department_name','location_id')
											->where('company_id',$Company->company_id)
											->where('status',1)
											->where("display_in_unload",1)
											->get();
				if (!empty($arrMRF))
				{
					foreach($arrMRF as $MRF)
					{
						$MRF_ID 	= $MRF->id;
						$SELECT_SQL = "	SELECT wm_dispatch_product.product_id, 
										SUM(wm_dispatch_product.quantity) AS TOTAL_SALES_QTY,
										COUNT(DISTINCT wm_dispatch.id) AS TOTAL_SALES_COUNT,
										getSalesProductCurrentStock(wm_dispatch_product.product_id,'".$Today."',".$MRF_ID.",0) AS Current_Stock,
										CASE WHEN 1=1 THEN
										(
											SELECT SUM(wm_processed_product_master.qty)
											FROM wm_processed_product_master
											LEFT JOIN wm_production_report_master ON wm_processed_product_master.production_id = wm_production_report_master.id
											WHERE wm_production_report_master.production_date >= '".$Dispatch_Date."'
											AND wm_production_report_master.finalize = 1
											AND wm_production_report_master.mrf_id = ".$MRF_ID."
											AND wm_processed_product_master.sales_product_id = wm_dispatch_product.product_id
										) END AS TOTAL_PRO_QTY,
										CASE WHEN 1=1 THEN
										(
											SELECT load_qty
											FROM wm_sales_product_min_load
											WHERE wm_sales_product_min_load.product_id = wm_dispatch_product.product_id
											AND wm_sales_product_min_load.mrf_id = ".$MRF_ID."
											GROUP BY wm_sales_product_min_load.product_id
										) END AS MIN_LOAD_QTY,
										CASE WHEN 1=1 THEN
										(
											SELECT CONCAT(WD.dispatch_date,'|',WDP.quantity,'|',WD.id)
											FROM wm_dispatch_product AS WDP
											LEFT JOIN wm_dispatch WD ON WDP.dispatch_id = WD.id
											WHERE WDP.product_id = wm_dispatch_product.product_id
											AND WD.bill_from_mrf_id = ".$MRF_ID."
											AND WD.master_dept_id = ".$MRF_ID."
											AND WD.appointment_id = 0
											AND WD.dispatch_date >= '".$Dispatch_Date."'
											ORDER BY WD.dispatch_date DESC
											LIMIT 1
										) END AS last_dispatch_details
										FROM wm_dispatch_product
										LEFT JOIN wm_dispatch ON wm_dispatch_product.dispatch_id = wm_dispatch.id
										LEFT JOIN wm_product_master ON wm_product_master.id = wm_dispatch_product.product_id
										WHERE wm_dispatch.approval_status = ".DISPATCH_APPROVED."
										AND wm_dispatch.dispatch_date >= '".$Dispatch_Date."'
										AND wm_dispatch.bill_from_mrf_id = ".$MRF_ID."
										AND wm_dispatch.master_dept_id = ".$MRF_ID."
										AND wm_dispatch.appointment_id = 0
										AND wm_product_master.id IS NOT NULL
										GROUP BY wm_dispatch_product.product_id";
						$SELECT_RES = DB::select($SELECT_SQL);
						foreach ($SELECT_RES as $SELECT_ROW)
						{
							$PRODUCTION_PER_DAY = (!empty($SELECT_ROW->TOTAL_PRO_QTY)?round($SELECT_ROW->TOTAL_PRO_QTY/$Days_Sales):0);
							$SALES_PER_DAY 		= (!empty($SELECT_ROW->TOTAL_SALES_QTY)?round($SELECT_ROW->TOTAL_SALES_QTY/$SELECT_ROW->TOTAL_SALES_COUNT):0);
							$CURRENT_STOCK 		= $SELECT_ROW->Current_Stock;
							$Due_In_Days 		= 0;
							if (!empty($SELECT_ROW->MIN_LOAD_QTY)) {
								$REMAIN_QTY = $SELECT_ROW->MIN_LOAD_QTY - $CURRENT_STOCK;
								if ($REMAIN_QTY > 0 && $PRODUCTION_PER_DAY > 0) {
									$Due_In_Days = ceil($REMAIN_QTY/$PRODUCTION_PER_DAY);
								}
							}
							$LAST_DISPATCH_DETAILS 	= $SELECT_ROW->last_dispatch_details;
							$LAST_DISPATCH_DATE 	= "";
							$LAST_DISPATCH_QTY 		= "";
							$DISPATCH_ID 			= "";
							if (!empty($LAST_DISPATCH_DETAILS))
							{
								$LAST_DISPATCH_DATA = explode("|",$LAST_DISPATCH_DETAILS);
								$LAST_DISPATCH_DATE = $LAST_DISPATCH_DATA[0];
								$LAST_DISPATCH_QTY 	= $LAST_DISPATCH_DATA[1];
								$DISPATCH_ID 		= $LAST_DISPATCH_DATA[2];
							}
							$RecordID 		= 0;
							$ExistingSql 	= "	SELECT id FROM wm_sales_plan_prediction 
												WHERE mrf_id = $MRF_ID
												AND product_id = ".$SELECT_ROW->product_id;
							$ExistingRes 	= DB::connection('META_DATA_CONNECTION')->select($ExistingSql);
							if (!empty($ExistingRes)) {
								foreach($ExistingRes as $ExistingRow) {
									$RecordID = $ExistingRow->id;
								}
							}
							if (empty($RecordID)) {
								$INSERTROW 		= "	INSERT INTO wm_sales_plan_prediction SET
													mrf_id 				= ".$MRF_ID.",
													product_id 			= ".$SELECT_ROW->product_id.",
													current_stock 		= ".$CURRENT_STOCK.",
													production_per_day 	= ".$PRODUCTION_PER_DAY.",
													sales_per_day 		= ".$SALES_PER_DAY.",
													due_in_days 		= ".$Due_In_Days.",
													last_dispatch_date 	= '".$LAST_DISPATCH_DATE."',
													last_dispatch_qty 	= '".$LAST_DISPATCH_QTY."',
													dispatch_id 		= ".$DISPATCH_ID.",
													created_at 			= '".date("Y-m-d H:i:s")."',
													updated_at 			= '".date("Y-m-d H:i:s")."'";
								DB::connection('META_DATA_CONNECTION')->statement($INSERTROW);
							} else {
								$UPDATEROW 		= "	UPDATE wm_sales_plan_prediction SET
													current_stock 		= ".$CURRENT_STOCK.",
													production_per_day 	= ".$PRODUCTION_PER_DAY.",
													sales_per_day 		= ".$SALES_PER_DAY.",
													due_in_days 		= ".$Due_In_Days.",
													last_dispatch_date 	= '".$LAST_DISPATCH_DATE."',
													last_dispatch_qty 	= '".$LAST_DISPATCH_QTY."',
													dispatch_id 		= ".$DISPATCH_ID.",
													updated_at 			= '".date("Y-m-d H:i:s")."'
													WHERE id = ".$RecordID;
								DB::connection('META_DATA_CONNECTION')->statement($UPDATEROW);
							}
						}
					}
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}
