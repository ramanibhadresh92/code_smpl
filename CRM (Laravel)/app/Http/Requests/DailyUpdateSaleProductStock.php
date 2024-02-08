<?php

namespace App\Console\Commands;

use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Models\StockLadger;
use Illuminate\Console\Command;
use DateTime;
use DateInterval;
use DatePeriod;

class DailyUpdateSaleProductStock extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'DailyUpdateSaleProductStock';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'command update daily stock in stock ledger table at night';

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
		$DEPARTMENT_DATA = WmDepartment::where("status",1)->where("is_virtual",0)->pluck("id")->toArray();
		if(!empty($DEPARTMENT_DATA)){
			foreach($DEPARTMENT_DATA AS $MRF_ID){
				$date    	= date("Y-m-d",strtotime('-1 days'));
				$begin   	= new DateTime($date);
				$end     	= new DateTime();
				$interval	= DateInterval::createFromDateString('1 day');
				$period  	= new DatePeriod($begin, $interval, $end);
				foreach ($period as $dt) {
					$STOCK_DATE   = $dt->format("Y-m-d");
					$NEXT_DAY 	  = date('Y-m-d', strtotime('+1 day', strtotime($STOCK_DATE)));
					$PRODUCT_DATA = WmProductMaster::where("status",1)
									->where("company_id",1)
									->where("sales",1)
									->pluck('id')
									->toArray();
					if(!empty($PRODUCT_DATA)){
						foreach($PRODUCT_DATA as $value){
							$data = \DB::table("stock_ladger")
							->where("product_type",PRODUCT_SALES)
							->where("stock_date",$STOCK_DATE)
							->where("mrf_id",$MRF_ID)
							->where("product_id",$value)
							->first();
							if($data){
								$inward = \DB::table("inward_ledger")
								->where("product_type",PRODUCT_SALES)
								->where("inward_date",$STOCK_DATE)
								->where("mrf_id",$MRF_ID)
								->where("product_id",$value)
								->sum('quantity');
								$outward = \DB::table("outward_ledger")
								->where("sales_product_id",$value)
								->where("outward_date",$STOCK_DATE)
								->where("mrf_id",$MRF_ID)
								->sum('quantity');
								
								\DB::table("stock_ladger")
								->where("product_type",PRODUCT_SALES)
								->where("stock_date",$STOCK_DATE)
								->where("mrf_id",$MRF_ID)
								->where("product_id",$value)
								->update(["inward" => $inward,"outward"=> $outward]);
								$opening 	= _FormatNumberV2($data->opening_stock);
								$closing 	= _FormatNumberV2(($opening + $inward) - $outward);
								
								\DB::table("stock_ladger")
								->where("product_type",PRODUCT_SALES)
								->where("stock_date",$STOCK_DATE)
								->where("mrf_id",$MRF_ID)
								->where("product_id",$value)
								->update(["closing_stock" => $closing]);

								\DB::table("stock_ladger")
								->where("product_type",PRODUCT_SALES)
								->where("stock_date",$NEXT_DAY)
								->where("mrf_id",$MRF_ID)
								->where("product_id",$value)
								->update(["opening_stock" => $closing]);
							}
						}
					}
				}
			}
		}
	}
}
