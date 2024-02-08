<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmAutoStockPurchaseToSales;
use App\Models\WmBatchProductDetailsProcessAvgPrice;
use App\Models\StockLadger;
use App\Models\ProductInwardLadger;
use App\Models\WmBatchMaster;
use App\Models\WmBatchAuditedProduct;
use App\Models\WmBatchProductDetail;
use App\Facades\LiveServices;

class BatchAvgPriceNewCalculation extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'BatchAvgPriceNewCalculation';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'BATCH AVG PRICE NEW CALCULATION FOR BATCH DATA UPDATE';

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
		$BATCH_IDS = WmBatchMaster::where("audited_date",">=","2022-02-01 00:00:00")
		->where("is_audited",'1')
		->pluck("batch_id")->toArray();
		// LiveServices::toSqlWithBinding();
		// prd($BATCH_IDS);
		if(!empty($BATCH_IDS)){
			foreach($BATCH_IDS AS $BATCH_ID){
				WmBatchAuditedProduct::InsertProductProcessDataForAvgPrice($BATCH_ID);
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}