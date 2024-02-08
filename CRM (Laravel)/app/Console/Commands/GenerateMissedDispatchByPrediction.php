<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use DB;
class GenerateMissedDispatchByPrediction extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateMissedDispatchByPrediction';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command use to Generate Sales Projection Prediction Missed Details';

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
		$LR_MASTER_DB 	= env("DB_DATABASE");
		$Due_Date 		= date("Y-m-d",strtotime("now"));
		$SUB_DAYS 		= 0;
		$SELECT_SQL 	= "	SELECT $LR_MASTER_DB.wm_department.id as MRF_ID,
							$LR_MASTER_DB.wm_product_master.id as PRODUCT_ID, 
							wm_sales_plan_prediction.current_stock AS CURRENT_STOCK,
							CASE WHEN 1=1 THEN
							(
								SELECT load_qty
								FROM $LR_MASTER_DB.wm_sales_product_min_load
								WHERE $LR_MASTER_DB.wm_sales_product_min_load.product_id = $LR_MASTER_DB.wm_product_master.id
								AND $LR_MASTER_DB.wm_sales_product_min_load.mrf_id = $LR_MASTER_DB.wm_department.id
								GROUP BY $LR_MASTER_DB.wm_sales_product_min_load.product_id
							) END AS MIN_DISPATCH_QTY,
							DATE_FORMAT(DATE_SUB(DATE_ADD(NOW(), INTERVAL wm_sales_plan_prediction.due_in_days DAY),INTERVAL $SUB_DAYS DAY),'%Y-%m-%d') as Predictive_Due_Date,
							wm_sales_plan_prediction.last_dispatch_date as Last_Dispatched_On,
							wm_sales_plan_prediction.last_dispatch_qty as Last_Dispatched_Qty
							FROM wm_sales_plan_prediction
							LEFT JOIN $LR_MASTER_DB.wm_department ON $LR_MASTER_DB.wm_department.id = wm_sales_plan_prediction.mrf_id
							LEFT JOIN $LR_MASTER_DB.wm_product_master ON $LR_MASTER_DB.wm_product_master.id = wm_sales_plan_prediction.product_id
							WHERE wm_sales_plan_prediction.due_in_days >= 0
							HAVING (CURRENT_STOCK > MIN_DISPATCH_QTY AND Predictive_Due_Date <= '".$Due_Date."')";
		$SELECT_RES = DB::connection('META_DATA_CONNECTION')->select($SELECT_SQL);
		foreach ($SELECT_RES as $SELECT_ROW)
		{
			$INSERTROW 		= "	INSERT INTO wm_sales_plan_prediction_vs_actual_log SET
								mrf_id 				= ".$SELECT_ROW->MRF_ID.",
								product_id 			= ".$SELECT_ROW->PRODUCT_ID.",
								current_stock 		= ".$SELECT_ROW->CURRENT_STOCK.",
								min_dispatch_qty 	= ".$SELECT_ROW->MIN_DISPATCH_QTY.",
								p_d_d 				= '".$SELECT_ROW->Predictive_Due_Date."',
								last_dispatched_on 	= '".$SELECT_ROW->Last_Dispatched_On."',
								last_dispatched_qty = '".$SELECT_ROW->Last_Dispatched_Qty."',
								created_at 			= '".date("Y-m-d H:i:s")."'";
			DB::connection('META_DATA_CONNECTION')->statement($INSERTROW);
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}
