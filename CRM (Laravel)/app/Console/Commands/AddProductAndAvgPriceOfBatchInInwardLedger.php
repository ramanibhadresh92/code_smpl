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
use App\Models\CompanyProductMaster;
use App\Models\OutWardLadger;


class AddProductAndAvgPriceOfBatchInInwardLedger extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'AddProductAndAvgPriceOfBatchInInwardLedger';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'ADD AVG PRICE OF PRODUCT IN INWARD LEDGER TABLE OF BATCH';

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
		// return false;
		$DATA = WmBatchProductDetailsProcessAvgPrice::where("process",0)->get()->toArray();
		if(!empty($DATA)){
			foreach($DATA AS $RAW => $VALUE){
				$IS_AUDITED 		= WmBatchMaster::where("batch_id",$VALUE['batch_id'])->value('is_audited');
				if($IS_AUDITED > 0){
					$PRODUCT_ID 				= $VALUE['product_id'];
					$AVG_PRICE 					= $VALUE['avg_price'];
					$BATCH_ID 					= $VALUE['batch_id'];
					$COMPANY_ID 				= $VALUE['company_id'];
					$MRF_ID 					= WmBatchMaster::where("batch_id",$VALUE['batch_id'])->value('master_dept_id');
					$QTY 						= WmBatchAuditedProduct::where("id",$VALUE['batch_product_detail_id'])->sum("qty");
					 ######## MAPPED PRODUCT DEVELOPEMENT FOR ELCITA ###############
					$mapped_purchase_product 	= CompanyProductMaster::where("id",$PRODUCT_ID)->value("mapped_purchase_product");
					######## INWARD ORIGINAL PRODUCT AND OUTWARD ORIGINAL PRODUCT STOCK QTY #############
					$array['product_id']    	= $PRODUCT_ID;
	                $array['mrf_id']        	= $MRF_ID;
	                $array['type']          	= TYPE_PURCHASE;
	                $array['product_type']  	= PRODUCT_PURCHASE;
	                $array['batch_id']      	= $BATCH_ID;
	                $array['inward_date']   	= date("Y-m-d");
	                $array['quantity']      	= ($QTY > 0) ? $QTY : 0;
	                $array['company_id']      	= $COMPANY_ID;
	                $array['avg_price']     	= _FormatNumberV2($AVG_PRICE);

	                ############# AVG PRICE CALCULATION FOR PURCHASE PRODUCT 08 JAN 2021  ##############
	                $inward_record_id 		= ProductInwardLadger::AutoAddInward($array);
	                $STOCK_AVG_PRICE 		= WmBatchProductDetail::GetPurchaseProductAvgPrice($MRF_ID,$PRODUCT_ID,$inward_record_id);
	                StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,date("Y-m-d"),$STOCK_AVG_PRICE);
	                if($mapped_purchase_product > 0){
	                	######## MAPPED PRODUCT DEVELOPEMENT FOR ELCITA ###############
	                	$mapProductInward 					= array();
						$mapProductInward['product_id']    	= $mapped_purchase_product;
		                $mapProductInward['mrf_id']        	= $MRF_ID;
		                $mapProductInward['type']          	= TYPE_PURCHASE;
		                $mapProductInward['product_type']  	= PRODUCT_PURCHASE;
		                $mapProductInward['batch_id']      	= $BATCH_ID;
		                $mapProductInward['inward_date']   	= date("Y-m-d");
		                $mapProductInward['quantity']      	= ($QTY > 0) ? $QTY : 0;
		                $mapProductInward['company_id']     = $COMPANY_ID;
		                $mapProductInward['avg_price']     	= _FormatNumberV2($AVG_PRICE);
		                $mapProductInward['remarks']    	= "Quantity from Mapped Product";
		                $mapProductInward_record_id 		= ProductInwardLadger::AutoAddInward($mapProductInward);
		                $STOCK_AVG_PRICE 					= WmBatchProductDetail::GetPurchaseProductAvgPrice($MRF_ID,$mapped_purchase_product,$mapProductInward_record_id);
	                	StockLadger::UpdateProductStockAvgPrice($mapped_purchase_product,PRODUCT_PURCHASE,$MRF_ID,date("Y-m-d"),$STOCK_AVG_PRICE);
	                	
		                ############# AVG PRICE CALCULATION FOR PURCHASE PRODUCT 08 JAN 2021  ##############
						$OUTWORDDATA 						= array();
		                $OUTWORDDATA['batch_id'] 			= $BATCH_ID;
						$OUTWORDDATA['sales_product_id'] 	= 0;
						$OUTWORDDATA['product_id'] 			= $PRODUCT_ID;
						$OUTWORDDATA['production_report_id']= 0;
						$OUTWORDDATA['direct_dispatch']		= 0;
						$OUTWORDDATA['ref_id']				= 0;
						$OUTWORDDATA['quantity']			= $QTY;
						$OUTWORDDATA['type']				= TYPE_PURCHASE;
						$OUTWORDDATA['product_type']		= PRODUCT_PURCHASE;
						$OUTWORDDATA['mrf_id']				= $MRF_ID;
						$OUTWORDDATA['company_id']			= $COMPANY_ID;
						$OUTWORDDATA['outward_date']		= date("Y-m-d");
						$OUTWORDDATA['created_by']			= 1;
						$OUTWORDDATA['updated_by']			= 1;
						OutWardLadger::AutoAddOutward($OUTWORDDATA);
					}
					WmBatchProductDetailsProcessAvgPrice::where("id",$VALUE['id'])->update(["process"=> 1]);
	            }
	        }
		}
		echo "\r\n--EndTime::".date("Y-m-d H:i:s")."--\r\n";
	}
}