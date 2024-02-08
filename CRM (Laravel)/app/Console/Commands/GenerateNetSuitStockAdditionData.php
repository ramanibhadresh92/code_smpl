<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NetSuitStockAdditionMaster;
use App\Models\NetSuitStockAddtionTransaction;
use DB;

class GenerateNetSuitStockAdditionData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateNetSuitStockAdditionData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate Netsuit Stock Addition API Data for the day.';

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
		// $StartDate 	= "2022-01-01";
		$StartDate 	= date("Y-m-d",strtotime("yesterday"));
		$Yesterday 	= date("Y-m-d",strtotime("yesterday"));
		while(strtotime($StartDate) <= strtotime($Yesterday)) {

			$StartDate 	= date("Y-m-d",strtotime($StartDate))." 00:00:00";
			$EndDate 	= date("Y-m-d",strtotime($StartDate))." 23:59:59";

			/** DELETE SAME DAY DATA */
			$MasterID 	= NetSuitStockAdditionMaster::select("id")->where("stock_date",date("Y-m-d",strtotime($StartDate)))->first();
			if (!empty($MasterID)) {
				$DeleteSQL 	= "DELETE FROM netsuit_stock_addition_transaction WHERE netsuit_stock_addition_transaction.ref_id = ".$MasterID->id;
				DB::connection()->statement($DeleteSQL);
				$DeleteSQL 	= "DELETE FROM netsuit_stock_addition_master WHERE netsuit_stock_addition_master.id = ".$MasterID->id;
				DB::connection()->statement($DeleteSQL);
			}
			/** DELETE SAME DAY DATA */

			$NetSuitStockAdditionMaster 				= new NetSuitStockAdditionMaster;
			$NetSuitStockAdditionMaster->stock_date 	= date("Y-m-d",strtotime($StartDate));
			$NetSuitStockAdditionMaster->created_at 	= date("Y-m-d H:i:s");
			$NetSuitStockAdditionMaster->updated_at 	= date("Y-m-d H:i:s");
			$NetSuitStockAdditionMaster->save();

			$MasterID 	= $NetSuitStockAdditionMaster->id;
			$SelectSql 	= "	SELECT inward_ledger.mrf_id AS mrf_id, sum(inward_ledger.avg_price * inward_ledger.quantity) as Total_Amount
							FROM inward_ledger
							INNER JOIN wm_department ON inward_ledger.mrf_id = wm_department.id
							WHERE inward_ledger.inward_date BETWEEN '".$StartDate."' AND '".$EndDate."'
							AND inward_ledger.type IN ('".TYPE_INWARD."','".TYPE_PURCHASE."','".TYPE_TRANSFER."')
							AND inward_ledger.product_type = ".PRODUCT_PURCHASE."
							GROUP BY wm_department.id";
			echo "\r\n".$SelectSql."\r\n";
			$SelectRes  = DB::connection()->select($SelectSql);
			if (!empty($SelectRes))
			{
				foreach($SelectRes as $ReportRow)
				{
					$NetSuitStockAddtionTransaction 				= new NetSuitStockAddtionTransaction;
					$NetSuitStockAddtionTransaction->ref_id 		= $MasterID;
					$NetSuitStockAddtionTransaction->mrf_id 		= $ReportRow->mrf_id;
					$NetSuitStockAddtionTransaction->amount 		= $ReportRow->Total_Amount;
					$NetSuitStockAddtionTransaction->created_at 	= date("Y-m-d H:i:s");
					$NetSuitStockAddtionTransaction->updated_at 	= date("Y-m-d H:i:s");
					$NetSuitStockAddtionTransaction->save();
				}
			}
			$StartDate = date('Y-m-d', strtotime('+1 day', strtotime($StartDate)));
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}