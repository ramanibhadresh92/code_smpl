<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminUser;
use DB;
class GenerateDailyDispatchPlan extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateDailyDispatchPlan';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command use to Generate Daily Dispatch Plan';

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
		$AutoEntry 	= true;
		if ($AutoEntry) {
			$STOCK_DATE = date("Y-m-d");
			$AdminUser 	= AdminUser::select("adminuserid")->where("systemuser",1)->first();
			$SELECT_SQL = "	SELECT wm_product_saleable_tagging.mrf_id, wm_product_saleable_tagging.product_id, stock_ladger.opening_stock
							FROM wm_product_saleable_tagging
							LEFT JOIN stock_ladger ON stock_ladger.mrf_id = wm_product_saleable_tagging.mrf_id AND stock_ladger.product_id = wm_product_saleable_tagging.product_id
							WHERE stock_ladger.stock_date = '".$STOCK_DATE."'
							AND stock_ladger.opening_stock > 0
							ORDER BY wm_product_saleable_tagging.mrf_id ASC, wm_product_saleable_tagging.product_id ASC";
			$SelectRes  = DB::connection()->select($SELECT_SQL);
			if (!empty($SelectRes))
			{
				$CreatedBy = isset($AdminUser->adminuserid)?$AdminUser->adminuserid:0;
				$UpdatedBy = isset($AdminUser->adminuserid)?$AdminUser->adminuserid:0;
				foreach($SelectRes as $ReportRow)
				{
					$INSERT_SQL = "	INSERT INTO wm_daily_projection_plan SET
									mrf_id 			= ".$ReportRow->mrf_id.",
									projection_date = '".$STOCK_DATE."',
									product_id 		= '".$ReportRow->product_id."',
									projection_qty 	= '".$ReportRow->opening_stock."',
									no_of_days 		= 1,
									status 			= 0,
									created_at 		= '".date("Y-m-d H:i:s")."',
									created_by 		= $CreatedBy,
									updated_at 		= '".date("Y-m-d H:i:s")."',
									updated_by 		= $UpdatedBy";
					DB::connection()->statement($INSERT_SQL);
				}
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}
