<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmProductionReportMaster;
use App\Models\WmProductMaster;
use App\Models\WmDepartment;
use DB;
class GenerateSalesProductInwardOutwardTrend extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateSalesProductInwardOutwardTrend';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command use to Generate Sales Product Inward Outward Trend';

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
		$MIN_DATE = WmProductionReportMaster::where("finalize",1)->orderBy("production_date","ASC")->limit(1)->value("production_date");
		$MIN_DATE = "2022-04-24";
		$MAX_DATE = date("Y-m-d",strtotime("yesterday"));
		while (strtotime($MIN_DATE) <= strtotime($MAX_DATE))
		{	
			$WmProductMaster = WmProductMaster::select("id","title")->where("sales",1)->where("status",1)->orderBy("id","ASC")->get();
			foreach ($WmProductMaster as $WmProduct)
			{
				$WmDepartments = WmDepartment::select("id","department_name")->where("status",1)->where("is_virtual",0)->orderBy("id","ASC")->get();
				foreach ($WmDepartments as $WmDepartment)
				{
					$INWARD_OUTWARD_DATA = "SELECT
											CASE WHEN 1=1 THEN 
											(
												SELECT SUM(quantity)
												FROM inward_ledger
												WHERE inward_ledger.product_type = ".PRODUCT_SALES."
												AND inward_ledger.product_id = ".$WmProduct->id."
												AND inward_ledger.inward_date = '".$MIN_DATE."'
												AND inward_ledger.direct_dispatch = 0
												AND inward_ledger.mrf_id = ".$WmDepartment->id."
											) END AS INWARD_QTY,
											CASE WHEN 1=1 THEN 
											(
												SELECT SUM(quantity)
												FROM outward_ledger
												WHERE outward_ledger.product_id = 0
												AND outward_ledger.sales_product_id = ".$WmProduct->id."
												AND outward_ledger.outward_date = '".$MIN_DATE."'
												AND outward_ledger.direct_dispatch = 0
												AND outward_ledger.type = '".TYPE_DISPATCH."'
												AND outward_ledger.mrf_id = ".$WmDepartment->id."
											) END AS OUTWARD_QTY";
					$INWARD_OUTWARD_RES = DB::select($INWARD_OUTWARD_DATA);
					$INWARD_QTY 		= isset($INWARD_OUTWARD_RES[0]->INWARD_QTY)?$INWARD_OUTWARD_RES[0]->INWARD_QTY:0;
					$OUTWARD_QTY 		= isset($INWARD_OUTWARD_RES[0]->OUTWARD_QTY)?$INWARD_OUTWARD_RES[0]->OUTWARD_QTY:0;
					IF ($INWARD_QTY > 0 || $OUTWARD_QTY > 0)
					{
						$INSERTROW 			= "	INSERT INTO sales_product_trends SET
												trend_date 		= '".$MIN_DATE."',
												mrf_id 			= ".$WmDepartment->id.",
												mrf_name 		= '".$WmDepartment->department_name."',
												product_id 		= ".$WmProduct->id.",
												product_name 	= '".$WmProduct->title."',
												inward_qty 		= ".$INWARD_QTY.",
												outward_qty 	= ".$OUTWARD_QTY;
						$SelectRes  = DB::connection('META_DATA_CONNECTION')->statement($INSERTROW);
					}
				}
			}
			$MIN_DATE = date('Y-m-d',strtotime($MIN_DATE.' +1 day'));
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}
