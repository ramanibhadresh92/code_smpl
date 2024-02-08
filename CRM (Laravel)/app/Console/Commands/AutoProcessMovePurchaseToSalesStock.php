<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WmAutoStockPurchaseToSales;

class AutoProcessMovePurchaseToSalesStock extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'AutoProcessMovePurchaseToSalesStock';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Auto Process Move Purchase To Sales Stock';

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
		$AutoProcessTable 		= new WmAutoStockPurchaseToSales;
		$arrRecordsToProcess 	= $AutoProcessTable->where('processed',0)->orderBy(["id","ASC"])->limit(10)->get();
		if (!empty($arrRecordsToProcess))
		{
			foreach($arrRecordsToProcess as $arrRecord)
			{
				WmAutoStockPurchaseToSales::where("id",$arrRecord->id)->update(["processed" => 1]);
				WmAutoStockPurchaseToSales::MovePurchaseStockToSales($arrRecord->wm_batch_audited_product_id,$arrRecord->created_by,$arrRecord->updated_by);
				WmAutoStockPurchaseToSales::where("id",$arrRecord->id)->update(["processed" => 2]);
			}
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}