<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NetSuitCogsMaster;
use App\Models\NetSuitCogsTransaction;
use DB;

class GenerateNetSuitCogsData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GenerateNetSuitCogsData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate Netsuit Cogs Data for the day.';

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
			$MasterID 	= NetSuitCogsMaster::select("id")->where("cogs_date",date("Y-m-d",strtotime($StartDate)))->first();
			if (!empty($MasterID)) {
				$DeleteSQL 	= "DELETE FROM netsuit_cogs_transaction WHERE netsuit_cogs_transaction.ref_id = ".$MasterID->id;
				DB::connection()->statement($DeleteSQL);
				$DeleteSQL 	= "DELETE FROM netsuit_cogs_master WHERE netsuit_cogs_master.id = ".$MasterID->id;
				DB::connection()->statement($DeleteSQL);
			}
			/** DELETE SAME DAY DATA */

			$NetSuitCogsMaster 				= new NetSuitCogsMaster;
			$NetSuitCogsMaster->cogs_date 	= date("Y-m-d",strtotime($StartDate));
			$NetSuitCogsMaster->created_at 	= date("Y-m-d H:i:s");
			$NetSuitCogsMaster->updated_at 	= date("Y-m-d H:i:s");
			$NetSuitCogsMaster->save();

			$MasterID 	= $NetSuitCogsMaster->id;
			$SelectSql 	= "	SELECT wm_department.id AS mrf_id, 
							sum(IF(wm_dispatch_sales_product_avg_price.direct_dispatch = 1,wm_dispatch_sales_product_avg_price.price,wm_dispatch_sales_product_avg_price.avg_price) * wm_dispatch_product.quantity) as Total_Amount
							FROM wm_dispatch_product
							INNER JOIN wm_dispatch_sales_product_avg_price ON wm_dispatch_sales_product_avg_price.dispatch_product_id = wm_dispatch_product.id
							INNER JOIN wm_dispatch ON wm_dispatch_sales_product_avg_price.dispatch_id = wm_dispatch.id
							INNER JOIN wm_department ON wm_dispatch.bill_from_mrf_id = wm_department.id
							WHERE wm_dispatch.approval_status = 1
							AND wm_dispatch.dispatch_date BETWEEN '".$StartDate."' AND '".$EndDate."'
							GROUP BY wm_department.id";
			$SelectRes  = DB::connection()->select($SelectSql);
			if (!empty($SelectRes))
			{
				foreach($SelectRes as $ReportRow)
				{
					$NetSuitCogsTransaction 				= new NetSuitCogsTransaction;
					$NetSuitCogsTransaction->ref_id 		= $MasterID;
					$NetSuitCogsTransaction->mrf_id 		= $ReportRow->mrf_id;
					$NetSuitCogsTransaction->amount 		= $ReportRow->Total_Amount;
					$NetSuitCogsTransaction->created_at 	= date("Y-m-d H:i:s");
					$NetSuitCogsTransaction->updated_at 	= date("Y-m-d H:i:s");
					$NetSuitCogsTransaction->save();
				}
			}
			$StartDate = date('Y-m-d', strtotime('+1 day', strtotime($StartDate)));
		}

		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}