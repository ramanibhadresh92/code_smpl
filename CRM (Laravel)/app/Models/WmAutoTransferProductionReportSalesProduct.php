<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmProductMaster;
use App\Models\WmProductionReportMaster;
use App\Models\InwardLadger;
use App\Models\ProductInwardLadger;
use App\Models\StockLadger;
use App\Models\OutWardLadger;
use App\Models\NetSuitStockLedger;
use App\Models\WmBatchProductDetail;
class WmAutoTransferProductionReportSalesProduct extends Model implements Auditable
{
	use AuditableTrait;
	protected 	$table 		= 'wm_auto_transfer_production_report_sales_product';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;
	protected 	$casts 		= [];

	/*
		Use 	: Add Production Report
		Author 	: Axay Shah
		Date 	: 17 July 2020
	*/
	public static function AddAutoProcessForProductionReport($request)
	{
		$userID 								= Auth()->user()->adminuserid;
		$Add 									= new self();
		$Add->sales_product_id					= (isset($request['sales_product_id']) && !empty($request['sales_product_id'])) ? $request['sales_product_id'] : 0;
		$Add->wm_production_report_id			= (isset($request['wm_production_report_id']) && !empty($request['wm_production_report_id'])) ? $request['wm_production_report_id'] : 0;
		$Add->wm_processed_product_master_id	= (isset($request['wm_processed_product_master_id']) && !empty($request['wm_processed_product_master_id'])) ? $request['wm_processed_product_master_id'] : 0;
		$Add->purchase_product_id				= (isset($request['purchase_product_id']) && !empty($request['purchase_product_id'])) ? $request['purchase_product_id'] : 0;
		$Add->qty								= (isset($request['qty']) && !empty($request['qty'])) ? $request['qty'] : 0;
		$Add->kg_avg_price						= (isset($request['kg_avg_price']) && !empty($request['kg_avg_price'])) ? $request['kg_avg_price'] : 0;
		$Add->created_by 						= (isset($request['created_by']) && !empty($request['created_by'])) ? $request['created_by'] : $userID;
		$Add->updated_by 						= (isset($request['updated_by']) && !empty($request['updated_by'])) ? $request['updated_by'] : $userID;
		$Add->save();
	}

	/*
	Use     : Move Sales Product to Inward Stock
	Author  : Axay Shah
	Date    : 20 July,2020
	*/
	public static function MoveSalesProductToInwardStockOld($productReportID=0,$purchase_product_id=0,$sales_product_id=0,$qty=0)
	{
		$TodayDate 	= date("Y-m-d");
		$production = WmProductionReportMaster::find($productReportID);
		if($production){
			if($production->finalize == 1)
			{
				$processData 	= (isset($production->processProduct) && !empty($production->processProduct)) ? $production->processProduct :  "";
				if(!empty($processData)) {
					foreach($processData as $value)
					{
						$MrfID 					= $production->mrf_id;
						$purchase_product_id 	= $production->product_id;
						$CompanyID 				= Auth()->user()->company_id;
						$ProductionDate 		= $TodayDate;
						$sales_product_id 		= $value->sales_product_id;
						$PRODUCTION_QTY 		= $value->qty;
						$kg_avg_price 			= $value->kg_avg_price;
						$CompanyProductMaster 	= CompanyProductMaster::find($purchase_product_id);
						$PURCHASE_AVG_PRICE 	= StockLadger::where("product_id",$purchase_product_id)
													->where("mrf_id",$MrfID)
													->where("product_type",PRODUCT_PURCHASE)
													->where("stock_date",$TodayDate)
													->value('avg_price');
						if ($CompanyProductMaster->para_unit_id == PRODUCT_TYPE_UNIT && $CompanyProductMaster->weight_in_kg > 0) {
							$KG_AVG_PRICE 	= ($CompanyProductMaster->weight_in_kg > 0) ? ($PURCHASE_AVG_PRICE / $CompanyProductMaster->weight_in_kg) : $PURCHASE_AVG_PRICE;
							
						} else {
							$KG_AVG_PRICE =  $PURCHASE_AVG_PRICE;
						}
						$InwardProductDetails['purchase_product_id'] 	= $purchase_product_id;
						$InwardProductDetails['product_id'] 			= $sales_product_id;
						$InwardProductDetails['production_report_id'] 	= $productReportID;
						$InwardProductDetails['ref_id'] 				= 0;
						$InwardProductDetails['quantity'] 				= $PRODUCTION_QTY;
						$InwardProductDetails['type'] 					= TYPE_SALES;
						$InwardProductDetails['product_type'] 			= PRODUCT_SALES;
						$InwardProductDetails['batch_id'] 				= 0;
						$InwardProductDetails['mrf_id'] 				= $MrfID;
						$InwardProductDetails['company_id'] 			= $CompanyID;
						$InwardProductDetails['inward_date'] 			= $TodayDate;
						$InwardProductDetails['avg_price'] 				= _FormatNumberV2($KG_AVG_PRICE);
						$InwardProductDetails['created_by'] 			= Auth()->user()->adminuserid;
						$InwardProductDetails['updated_by'] 			= Auth()->user()->adminuserid;

						############ NEW PRODUCT AVG PRICE CALCULATION LIVE - 20 JAN 2022 ##########
						$insertedID 		= ProductInwardLadger::AutoAddInward($InwardProductDetails);
						$final_avg_price 	= WmBatchProductDetail::GetSalesProductAvgPriceN1($MrfID,$purchase_product_id,$sales_product_id,$insertedID,$ProductionDate);
						StockLadger::UpdateProductStockAvgPrice($sales_product_id,PRODUCT_SALES,$MrfID,$ProductionDate,$final_avg_price);
						############ NEW PRODUCT AVG PRICE CALCULATION LIVE - 20 JAN 2022 ##########

						############# NetSuitStockLedger ##########
						NetSuitStockLedger::addStockForNetSuit($sales_product_id,0,PRODUCT_SALES,$qty,$final_avg_price,$MrfID,$TodayDate);
						############# NetSuitStockLedger ##########
					}
				}
			}
		}
	}
	/*
	Use     : Move Sales Product to Inward Stock
	Author  : Axay Shah
	Date    : 20 July,2020
	*/
	public static function MoveSalesProductToInwardStock($productReportID=0,$purchase_product_id=0,$sales_product_id=0,$qty=0)
	{
		$TodayDate 	= date("Y-m-d");
		$production = WmProductionReportMaster::find($productReportID);
		if($production){
			if($production->finalize == 1)
			{
				$processing_qty 		= $production->processing_qty;
				$MrfID 					= $production->mrf_id;
				$purchase_product_id 	= $production->product_id;
				$CompanyID 				= Auth()->user()->company_id;
				$ProductionDate 		= $TodayDate;
				$processData 			= (isset($production->processProduct) && !empty($production->processProduct)) ? $production->processProduct :  "";
				###### LOGIC TO CALCULATE NEW COGS AVG PRICE FOR SALES PRODUCT- 06-06-2023 ##########
				$total_sales_qty 		= 0;
				$EXCLUDE_PRODUCT 		= WmProductMaster::where("is_afr",1)->orWhere("is_inert",1)->orWhere("is_rdf",1)->where("status",1)->pluck("id")->toArray();
				if(!empty($processData)) {
					foreach($processData as $value)
					{
						if(!in_array($value->sales_product_id, $EXCLUDE_PRODUCT)){
							$total_sales_qty = WmProcessedProductMaster::where("sales_product_id",$value->sales_product_id)->where("production_id",$productReportID)->sum("qty");
							$total_sales_qty += $value->qty;
						}
					}
				}
				$PURCHASE_AVG_PRICE 	= StockLadger::where("product_id",$purchase_product_id)
										->where("mrf_id",$MrfID)
										->where("product_type",PRODUCT_PURCHASE)
										->where("stock_date",$TodayDate)
										->value('avg_price');
				$total_amount_purchase 	= _FormatNumberV2($PURCHASE_AVG_PRICE * $processing_qty);
				$total_sales_qty 		= WmProcessedProductMaster::where("production_id",$productReportID)->whereNotIn("sales_product_id",$EXCLUDE_PRODUCT)->sum("qty");
				$NEW_COGS_PRICE 		= ($total_amount_purchase > 0 && $total_sales_qty > 0) ? _FormatNumberV2($total_amount_purchase / $total_sales_qty) : 0;
				########## UPDATE NEW COGS PRICE TO PRODUCTION REPORT #############
				WmProcessedProductMaster::where("production_id",$productReportID)->whereNotIn("sales_product_id",$EXCLUDE_PRODUCT)->update(array("new_cogs_price" => $NEW_COGS_PRICE));
				########## UPDATE NEW COGS PRICE TO PRODUCTION REPORT #############
				if(!empty($processData)) {
					foreach($processData as $value)
					{
						$sales_product_id 		= $value->sales_product_id;
						$PRODUCTION_QTY 		= $value->qty;
						$kg_avg_price 			= $value->kg_avg_price;
						$CompanyProductMaster 	= CompanyProductMaster::find($purchase_product_id);
						if ($CompanyProductMaster->para_unit_id == PRODUCT_TYPE_UNIT && $CompanyProductMaster->weight_in_kg > 0) {
							$KG_AVG_PRICE 	= ($CompanyProductMaster->weight_in_kg > 0) ? ($PURCHASE_AVG_PRICE / $CompanyProductMaster->weight_in_kg) : $PURCHASE_AVG_PRICE;
							
						} else {
							$KG_AVG_PRICE =  $PURCHASE_AVG_PRICE;
						}
						$InwardProductDetails['purchase_product_id'] 	= $purchase_product_id;
						$InwardProductDetails['product_id'] 			= $sales_product_id;
						$InwardProductDetails['production_report_id'] 	= $productReportID;
						$InwardProductDetails['ref_id'] 				= 0;
						$InwardProductDetails['quantity'] 				= $PRODUCTION_QTY;
						$InwardProductDetails['type'] 					= TYPE_SALES;
						$InwardProductDetails['product_type'] 			= PRODUCT_SALES;
						$InwardProductDetails['batch_id'] 				= 0;
						$InwardProductDetails['mrf_id'] 				= $MrfID;
						$InwardProductDetails['company_id'] 			= $CompanyID;
						$InwardProductDetails['inward_date'] 			= $TodayDate;
						$InwardProductDetails['avg_price'] 				= _FormatNumberV2($KG_AVG_PRICE);
						$InwardProductDetails['created_by'] 			= Auth()->user()->adminuserid;
						$InwardProductDetails['updated_by'] 			= Auth()->user()->adminuserid;
						$InwardProductDetails['new_cogs_price'] 		= (!in_array($sales_product_id,$EXCLUDE_PRODUCT)) ? $NEW_COGS_PRICE : 0;
						############ NEW PRODUCT AVG PRICE CALCULATION LIVE - 20 JAN 2022 ##########
						$insertedID 		= ProductInwardLadger::AutoAddInward($InwardProductDetails);
						$final_avg_price 	= WmBatchProductDetail::GetSalesProductAvgPriceN1($MrfID,$purchase_product_id,$sales_product_id,$insertedID,$ProductionDate);
						####### GET NEW COGS PRICE AS PER THE PRODUCTION REPORT ###########
						$final_cogs_avg_price 	= WmBatchProductDetail::GetSalesProductCogsAvgPrice($MrfID,$purchase_product_id,$sales_product_id,$insertedID,$ProductionDate);
						####### GET NEW COGS PRICE AS PER THE PRODUCTION REPORT ###########
						StockLadger::UpdateProductStockAvgPrice($sales_product_id,PRODUCT_SALES,$MrfID,$ProductionDate,$final_avg_price,$final_cogs_avg_price);
						############ NEW PRODUCT AVG PRICE CALCULATION LIVE - 20 JAN 2022 ##########
						############# NetSuitStockLedger ##########
						NetSuitStockLedger::addStockForNetSuit($sales_product_id,0,PRODUCT_SALES,$qty,$final_avg_price,$MrfID,$TodayDate);
						############# NetSuitStockLedger ##########
					}
				}
			}
		}
	}

	/*
	Use     : STOCK AVG CALCULATION
	Author  : Axay Shah
	Date    : 09 MARCH,2021
	*/
	public static function GetStockDataForAvgPrice($productID,$ProductType,$date,$mrfID,$productionReportID=0){
		$AVG_PRICE 			= 0;
		$TOTAL_AVG_PRICE 	= 0;
		$FINAL_AVG_PRICE 	= 0;
		$OPENING_AVG_PRICE 	= 0;
		$OPENING_STOCK 		= 0;
		$OPENING_STOCK_DATA = StockLadger::where("product_id",$productID)->where("product_type",$ProductType)->where("stock_date",$date)->where("mrf_id",$mrfID)->first();
		$OUTWARD_QTY 		= OutWardLadger::where("product_id",$productID)->where("outward_date",$date)->where("mrf_id",$mrfID)->sum("quantity");
		$INWARD_AVG_PRICE 	= ProductInwardLadger::where("product_id",$productID)->where("product_type",$ProductType)->where("inward_date",$date)->where("mrf_id",$mrfID)->sum("avg_price");
		$PROCESSING_QTY 	= WmProductionReportMaster::where("id",$productionReportID)->value("processing_qty");
		$OPENING_STOCK 		= ($OPENING_STOCK_DATA) ? $OPENING_STOCK_DATA->opening_stock : 0;
		$OUTWARD_QTY 		= ($OUTWARD_QTY > 0) ? $OUTWARD_QTY : 0;
		$CURRENT_STOCK 		= _FormatNumberV2($OPENING_STOCK - $OUTWARD_QTY);
		if($CURRENT_STOCK >= $PROCESSING_QTY){
			$AVG_PRICE = _FormatNumberV2($OPENING_STOCK_DATA->avg_price);
		}else{
			$OPENING_AVG_PRICE 	= ($OPENING_STOCK_DATA) ? _FormatNumberV2($OPENING_STOCK_DATA->avg_price) : 0;
			$TOTAL_AVG_PRICE 	= _FormatNumberV2($OPENING_AVG_PRICE + $INWARD_AVG_PRICE);
			$FINAL_AVG_PRICE 	= ($TOTAL_AVG_PRICE > 0) ? $TOTAL_AVG_PRICE / 2 : 0;
			$AVG_PRICE 			= _FormatNumberV2($FINAL_AVG_PRICE);
		}
		return $AVG_PRICE;
	}
}