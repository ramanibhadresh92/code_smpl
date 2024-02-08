<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockLadger;
use App\Models\ProductInwardLadger;
use App\Models\WmProductMaster;
use App\Models\OutWardLadger;
use App\Models\WmDepartment;
use DateTime;
use DateInterval;
use DatePeriod;

class UpdateAvgPriceSalesForYear2022And2023 extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'UpdateAvgPriceSalesForYear2022And2023';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "ADD AVG PRICE OF SALES PRODUCT IN STOCK LEDGER TABLE";

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
		return false;
		echo "\r\n--StartTime::".date("Y-m-d H:i:s")."--\r\n";
		try{
			$COMPANY_ID 	= 	1;
		 	$START_DATE 	= 	"2022-10-28";
			$END_DATE 		=  	date("Y-m-d");
			$PRODUCT_TYPE 	=   PRODUCT_SALES;
			$MRF_IDS 		= WmDepartment::where("status",1)->where("is_virtual",0)->where("is_service_mrf",0)->pluck("id")->toArray();
			$MRF_IDS 		= array(11);
			if(!empty($MRF_IDS)){
				
				foreach($MRF_IDS AS $MRF_ID){
					// $PRODUCT_DATA = WmProductMaster::where("status",1)->pluck('id')->toArray();
					$PRODUCT_DATA = array(132);

					if(!empty($PRODUCT_DATA)){

						foreach($PRODUCT_DATA AS $PRODUCT_ID){
							print_r($PRODUCT_ID);
							$begin 			= 	new DateTime($START_DATE);
							$end 			= 	new DateTime($END_DATE);
							$interval 		= 	DateInterval::createFromDateString('1 day');
							$period 		= 	new DatePeriod($begin, $interval, $end);

							foreach ($period as $dt) {
								####### START CALCULATION ON DATE WISE #########
								$STOCK_DATE 			= $dt->format("Y-m-d");
								$PRIVIOUS_DATE 			= date('Y-m-d', strtotime($STOCK_DATE .' -1 day'));
								$NEXT_DATE 				= date('Y-m-d', strtotime($STOCK_DATE .' +1 day'));

								$PREV_AVG_PRICE_DATA 	= StockLadger::where("mrf_id",$MRF_ID)
														->where("company_id",$COMPANY_ID)
												        ->where("product_id",$PRODUCT_ID)
														->where("product_type",$PRODUCT_TYPE)
														->where("stock_date",$PRIVIOUS_DATE)
														->first();

								
								$CLOSING_STOCK 		= (isset($PREV_AVG_PRICE_DATA->closing_stock)) ? $PREV_AVG_PRICE_DATA->closing_stock : 0;
								$PREV_AVG_PRICE 	= (isset($PREV_AVG_PRICE_DATA->avg_price)) ? $PREV_AVG_PRICE_DATA->avg_price : 0;
								echo "</br>";
								echo $PREV_AVG_PRICE_DATA->stock_date."  ".$PREV_AVG_PRICE."  closing ".$CLOSING_STOCK;
								echo "</br>";
								$PREV_STOCK_VALUE 	= _FormatNumberV2($PREV_AVG_PRICE * $CLOSING_STOCK);
								$SQL 		= 	"SELECT * FROM (
														SELECT 	mrf_id,
															inward_date as trn_date,
															quantity,
															product_id,
															created_at,
															'1' as type,
															'INWARD' as trn_type,
															avg_price,
															product_type
														FROM inward_ledger
														WHERE inward_date = '$STOCK_DATE' and mrf_id=$MRF_ID and product_id=$PRODUCT_ID and product_type = $PRODUCT_TYPE and direct_dispatch = 0
													UNION ALL
														SELECT 	mrf_id,
															outward_date as trn_date,
															quantity,
															product_id,
															created_at,
															'0' as type,
															'OUTWARD' as trn_type,
															avg_price,
															$PRODUCT_TYPE AS product_type
															
														FROM outward_ledger
														WHERE outward_date = '$STOCK_DATE' and mrf_id= $MRF_ID and sales_product_id=$PRODUCT_ID  and direct_dispatch = 0 ) AS Q ORDER BY created_at";
								ECHO $SQL."<br/>";
								$DATA 			=  \DB::SELECT($SQL);
								$NEW_AVG_PRICE 	= $PREV_AVG_PRICE;
								
								if(!empty($DATA)){
									foreach($DATA as $RAW){
										$QTY 		 		= (isset($RAW->quantity) && !empty($RAW->quantity)) ? $RAW->quantity : 0;
										$RATE 		 		= (isset($RAW->avg_price) && !empty($RAW->avg_price)) ? $RAW->avg_price : 0;
										$STOCK_VALUE 		= _FormatNumberV2($QTY * $RATE);
										$PREV_STOCK_VALUE 	= _FormatNumberV2($PREV_AVG_PRICE * $CLOSING_STOCK);
										######### IF TRANSACTION IS INWARD THEN 1 ELSE OUTWARD 0 ############
										if($RAW->type == 1){
											$CLOSING_STOCK 		= _FormatNumberV2($CLOSING_STOCK + $QTY);
											$TOTAL_STOCK_VALUE 	= _FormatNumberV2($PREV_STOCK_VALUE + $STOCK_VALUE);
											$PREV_AVG_PRICE 	= ($CLOSING_STOCK > 0) ? _FormatNumberV2($TOTAL_STOCK_VALUE / $CLOSING_STOCK) : 0;
										}else{
											$CLOSING_STOCK 		= _FormatNumberV2($CLOSING_STOCK - $QTY);
											$TOTAL_STOCK_VALUE 	= _FormatNumberV2($PREV_STOCK_VALUE + $STOCK_VALUE);
										}
										$CURRENT_DATE 		= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
															->where("product_type",$PRODUCT_TYPE)
															->where("company_id",$COMPANY_ID)
															->where("mrf_id",$MRF_ID)
															->where("stock_date",$STOCK_DATE)
															->update(array("avg_price"=> $PREV_AVG_PRICE));
										$NEXT_DATE_DATA 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
															->where("product_type",$PRODUCT_TYPE)
															->where("company_id",$COMPANY_ID)
															->where("mrf_id",$MRF_ID)
															->where("stock_date",$NEXT_DATE)
															->update(array("avg_price"=> $PREV_AVG_PRICE));
										echo "CLOSING_STOCK ".$CLOSING_STOCK." PREV_AVG_PRICE ".$PREV_AVG_PRICE."<br/>";
										######### IF TRANSACTION IS INWARD THEN 1 ELSE OUTWARD 0 ############
									}
								}else{
									$CURRENT_DATE 	= \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
										->where("product_type",$PRODUCT_TYPE)
										->where("company_id",$COMPANY_ID)
										->where("mrf_id",$MRF_ID)
										->where("stock_date",$STOCK_DATE)
										->update(array("avg_price"=> $PREV_AVG_PRICE));
									$NEXT_DATE_DATA = \App\Models\StockLadger::where("product_id",$PRODUCT_ID)
										->where("product_type",$PRODUCT_TYPE)
										->where("company_id",$COMPANY_ID)
										->where("mrf_id",$MRF_ID)
										->where("stock_date",$NEXT_DATE)
										->update(array("avg_price"=> $PREV_AVG_PRICE));
									
								}
							}
						}
					}
				}
			}
		}catch(\Exception $e){
			\Log::info("ERROR CRON UpdateAvgPriceSalesForYear2022And2023 ".$e->getMessage());
		}
	}
}