<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmBatchAuditedProduct;
use App\Models\AppointmentCollectionDetail;
use App\Models\ProductInwardLadger;
use App\Models\OutWardLadger;
use App\Models\StockLadger;
use App\Models\WmBatchMaster;
use App\Models\WmBatchCollectionMap;
use App\Models\CompanyProductQualityParameter;
class WmBatchProductDetail extends Model
{
	protected 	$table 		= 'wm_batch_product_detail';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = false;

	public function BatchData() {
		return $this->belongsTo(WmBatchMaster::class,"batch_id","batch_id");
	}

	/*
	Use     : Insert purchese product details from batch product
	Author  : Axay Shah
	Date    : 14 Mar,2019
	*/
	public static function insertBatchProductDetail($request)
	{
		$productExits = self::where('product_id',$request->product_id)->where('batch_id',$request->batch_id)->first();
		if($productExits) {
			return '-1';
		}
		$wrong_product_id       				= (isset($request->wrong_product_id) && !empty($request->wrong_product_id)) ? $request->wrong_product_id : 0;
		$add 									= new self();
		$add->batch_id          				= (isset($request->batch_id) && !empty($request->batch_id)) ? $request->batch_id : 0;
		$add->category_id       				= (isset($request->category_id) && !empty($request->category_id)) ? $request->category_id : 0;
		$add->product_id        				= (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : 0;
		$add->collection_qty    				= (isset($request->collection_qty) && !empty($request->collection_qty)) ? $request->collection_qty : 0;
		$add->from_mrf          				= (isset($request->from_mrf) && !empty($request->from_mrf)) ? $request->from_mrf : 1;
		$add->product_quality_para_id    		= (isset($request->product_quality_para_id) && !empty($request->product_quality_para_id)) ? $request->product_quality_para_id : 0;
		$add->wrong_product_id                  = $wrong_product_id;
		$add->wrong_product_quality_para_id     = CompanyProductQualityParameter::where("product_id",$wrong_product_id)->value("company_product_quality_id");
		if($add->save()) {
			$request->id            = $add->id;
			$request->qty           = $add->collection_qty;
			$request->no_of_bag     = (isset($request->no_of_bag) && !empty($request->no_of_bag)) ? $request->no_of_bag : 0;
			WmBatchAuditedProduct::insertBatchAuditedProduct($request);
			LR_Modules_Log_CompanyUserActionLog($request,$add->id);
		}
		return $add;
	}

	/*
	Use     : get Collection of data
	Author  : Axay Shah
	Date    : 23 Aug,2019
	*/
	public static function getAuditDataForLadger($batchId = 0)
	{
		try {
			$array      = array();
			$BATCH      = new WmBatchMaster();
			$D          = (new static)->getTable();
			$Details    = self::select("$D.*","B.master_dept_id","B.collection_id")
							->leftjoin($BATCH->getTable()." as B","$D.batch_id","=","B.batch_id")
							->where("$D.batch_id",$batchId)
							->get();
			if(!empty($Details)){
				foreach($Details as $row){
					$collectionIDS  = (!empty($row->collection_id)) ? explode(",",$row->collection_id) : 0;
					$AvgPrice       = AppointmentCollectionDetail::where("product_id",$row->product_id)->whereIn("collection_id",$collectionIDS)->sum("product_customer_price");
					$array['product_id']    = $row->product_id;
					$array['mrf_id']        = $row->master_dept_id;
					$array['type']          = TYPE_PURCHASE;
					$array['product_type']  = PRODUCT_PURCHASE;
					$array['batch_id']      = $batchId;
					$array['quantity']      = WmBatchAuditedProduct::where("id",$row->id)->sum("qty");
					$array['avg_price']     = _FormatNumberV2($AvgPrice);
					ProductInwardLadger::AutoAddInward($array);
				}
			}
		} catch(\Exception $e) {
			\Log::error("ERROR ****".$e->getMessage()." LINE ".$e->getLine()." FILE ".$e->getFile());
		}
	}


	/*
	Use     : To Calculate the product avg price
	Author  : Axay Shah
	Date    : 28 May 2021
	*/
	public static function GetPurchaseProductAvgPrice($MRF_ID=0,$PRODUCT_ID=0,$INWARD_RECORD_ID=0)
	{
		########### NEW AVG PRICE CALCULATION ##############
		$OPENING_STOCK          =   0;
		$GET_CURRENT_STOCK      =   StockLadger::where("product_id",$PRODUCT_ID)
									->where("mrf_id",$MRF_ID)
									->where("product_type",PRODUCT_PURCHASE)
									->where("stock_date",date("Y-m-d"))
									->first();
		$OPENING_STOCK          =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
		$OPENING_STOCK_AVG      =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
		$TOTAL_OPENING_AMT      =   _FormatNumberV2($OPENING_STOCK * $OPENING_STOCK_AVG);
		if($INWARD_RECORD_ID > 0){
			$TOTAL_QTY  =   ProductInwardLadger::where("product_id",$PRODUCT_ID)
				->where("mrf_id",$MRF_ID)
				->where("product_type",PRODUCT_PURCHASE)
				->where("direct_dispatch",0)
				->where("inward_date",date("Y-m-d"))
				->where("id",$INWARD_RECORD_ID)
				->value("quantity");
			$TOTAL_PRICE_DATA       =   ProductInwardLadger::select(\DB::raw("SUM(quantity * avg_price) as total_amount"))
				->where("product_id",$PRODUCT_ID)
				->where("mrf_id",$MRF_ID)
				->where("direct_dispatch",0)
				->where("product_type",PRODUCT_PURCHASE)
				->where("inward_date",date("Y-m-d"))
				->where("id",$INWARD_RECORD_ID)
				->get()->toArray();
		}else{
			$TOTAL_QTY  =   ProductInwardLadger::where("product_id",$PRODUCT_ID)
				->where("mrf_id",$MRF_ID)
				->where("product_type",PRODUCT_PURCHASE)
				->where("direct_dispatch",0)
				->where("inward_date",date("Y-m-d"))
				->sum("quantity");
			$TOTAL_PRICE_DATA       =   ProductInwardLadger::select(\DB::raw("SUM(quantity * avg_price) as total_amount"))
				->where("product_id",$PRODUCT_ID)
				->where("mrf_id",$MRF_ID)
				->where("direct_dispatch",0)
				->where("product_type",PRODUCT_PURCHASE)
				->where("inward_date",date("Y-m-d"))
				->get()->toArray();
		}
		
		
		$TOTAL_PRICE = 0;
		$GRAND_TOTAL = 0;
		if(!empty($TOTAL_PRICE_DATA)){
			foreach ($TOTAL_PRICE_DATA as $key => $value) {
				if($value['total_amount'] > 0){
					$GRAND_TOTAL += _FormatNumberV2($value['total_amount']);
				}
				
			}
		}
		$GRAND_TOTAL      += $TOTAL_OPENING_AMT;
		$TOTAL_QTY        += $OPENING_STOCK;
		$AVG_PRICE         = (!empty($GRAND_TOTAL)) ? _FormatNumberV2($GRAND_TOTAL / $TOTAL_QTY) : 0;
		return $AVG_PRICE;
	}

	/*
	Use     : To Calculate the product avg price
	Author  : Axay Shah
	Date    : 28 May 2021
	*/
	public static function GetPurchaseProductAvgPriceByDate($MRF_ID=0,$PRODUCT_ID=0,$INWARD_RECORD_ID=0,$date="",$OPENING_STOCK=0,$OPENING_STOCK_AVG=0)
	{
		$date = (!empty($date)) ? date("Y-m-d",strtotime($date)) : date("Y-m-d");
		########### NEW AVG PRICE CALCULATION ##############
		// $OPENING_STOCK          =   0;
		// $GET_CURRENT_STOCK      =   StockLadger::where("product_id",$PRODUCT_ID)
		// 							->where("mrf_id",$MRF_ID)
		// 							->where("product_type",PRODUCT_PURCHASE)
		// 							->where("stock_date",$date)
		// 							->first();
		// $OPENING_STOCK          =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
		// $OPENING_STOCK_AVG      =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
		$TOTAL_OPENING_AMT      =   _FormatNumberV2($OPENING_STOCK * $OPENING_STOCK_AVG);
		if($INWARD_RECORD_ID > 0){
			$TOTAL_QTY  =   ProductInwardLadger::where("product_id",$PRODUCT_ID)
				->where("mrf_id",$MRF_ID)
				->where("product_type",PRODUCT_PURCHASE)
				->where("direct_dispatch",0)
				->where("inward_date",$date)
				->where("id",$INWARD_RECORD_ID)
				->sum("quantity");
			$TOTAL_PRICE_DATA       =   ProductInwardLadger::select(\DB::raw("SUM(quantity * avg_price) as total_amount"))
				->where("product_id",$PRODUCT_ID)
				->where("mrf_id",$MRF_ID)
				->where("direct_dispatch",0)
				->where("product_type",PRODUCT_PURCHASE)
				->where("inward_date",$date)
				->where("id",$INWARD_RECORD_ID)
				->get()->toArray();
		}else{
			$TOTAL_QTY  =   ProductInwardLadger::where("product_id",$PRODUCT_ID)
				->where("mrf_id",$MRF_ID)
				->where("product_type",PRODUCT_PURCHASE)
				->where("direct_dispatch",0)
				->where("inward_date",$date)
				->sum("quantity");
			$TOTAL_PRICE_DATA       =   ProductInwardLadger::select(\DB::raw("SUM(quantity * avg_price) as total_amount"))
				->where("product_id",$PRODUCT_ID)
				->where("mrf_id",$MRF_ID)
				->where("direct_dispatch",0)
				->where("product_type",PRODUCT_PURCHASE)
				->where("inward_date",$date)
				->get()->toArray();
		}
		
		
		$TOTAL_PRICE = 0;
		$GRAND_TOTAL = 0;
		if(!empty($TOTAL_PRICE_DATA)){
			foreach ($TOTAL_PRICE_DATA as $key => $value) {
				if($value['total_amount'] > 0){
					$GRAND_TOTAL += _FormatNumberV2($value['total_amount']);
				}
				
			}
		}
		$GRAND_TOTAL      += $TOTAL_OPENING_AMT;
		$TOTAL_QTY        += $OPENING_STOCK;
		$AVG_PRICE         = (!empty($GRAND_TOTAL)) ? _FormatNumberV2($GRAND_TOTAL / $TOTAL_QTY) : 0;
		return $AVG_PRICE;
	}


	/*
	Use     : To Calculate the Sales product avg price
	Author  : Axay Shah
	Date    : 28 May 2021
	*/
	public static function GetSalesProductAvgPrice($MRF_ID=0,$PURCHASE_PRODUCT_ID=0,$SALES_PRODUCT_ID=0,$PRODUCTION_ID=0,$PRODUCTION_DATE="")
	{
		########### NEW AVG PRICE CALCULATION ##############
		$OPENING_STOCK      = 0;
		$GET_CURRENT_STOCK  = StockLadger::where("product_id",$SALES_PRODUCT_ID)
							->where("mrf_id",$MRF_ID)
							->where("product_type",PRODUCT_SALES)
							->where("stock_date",$PRODUCTION_DATE)
							->first();
		$OPENING_STOCK      = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
		$OPENING_AVG        = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
		$TOTAL_OPENING_AMT  = _FormatNumberV2($OPENING_STOCK * $OPENING_AVG);
		$TOTAL_QTY          = ProductInwardLadger::where("product_id",$SALES_PRODUCT_ID)
							->where("mrf_id",$MRF_ID)
							->where("product_type",PRODUCT_SALES)
							->where("production_report_id",$PRODUCTION_ID)
							->where("direct_dispatch",0)
							->where("inward_date",$PRODUCTION_DATE)
							->sum("quantity");
		$TOTAL_PRICE_DATA  =  ProductInwardLadger::select(\DB::raw("SUM(quantity * avg_price) as total_amount"))
							->where("product_id",$SALES_PRODUCT_ID)
							->where("mrf_id",$MRF_ID)
							->where("product_type",PRODUCT_SALES)
							->where("inward_date",$PRODUCTION_DATE)
							->where("direct_dispatch",0)
							->where("inward_date",date("Y-m-d"))
							->get()
							->toArray();
		$TOTAL_PRICE = 0;
		$GRAND_TOTAL = 0;
		if(!empty($TOTAL_PRICE_DATA)){
			foreach ($TOTAL_PRICE_DATA as $key => $value) {
				$GRAND_TOTAL += _FormatNumberV2($value['total_amount']);
			}
		}
		$GRAND_TOTAL      += $TOTAL_OPENING_AMT;
		$TOTAL_QTY        += $OPENING_STOCK;
		$AVG_PRICE        = (!empty($GRAND_TOTAL)) ? _FormatNumberV2($GRAND_TOTAL / $TOTAL_QTY) : 0;
		return $AVG_PRICE;
	}

	/*
	Use     : Get Purchase collection Product
	Author  : Axay Shah
	Date    : 13-12-2021
	*/
	public static function GetCollectionPurchaseProductByBatch($batch_id=0){
		$data = array();
		$collection_ids = WmBatchCollectionMap::where("batch_id",$batch_id)->pluck("collection_id")->toArray();
		if(!empty($collection_ids)){
			$data = AppointmentCollectionDetail::select("CPM.id",\DB::raw("CONCAT(CPM.name,' ',CQ.parameter_name) as product_name"),"CQ.company_product_quality_id")
			->join("company_product_master as CPM","CPM.id","=","appointment_collection_details.product_id")
			->join("company_product_quality_parameter as CQ","CPM.id","=","CQ.product_id")
			->whereIn("appointment_collection_details.collection_id",$collection_ids)
			->groupBy("appointment_collection_details.product_id")
			->get()
			->toArray();
		}
		return $data;
	}

	/*
	Use     : To Calculate the product avg price
	Author  : Axay Shah
	Date    : 28 May 2021
	*/
	public static function GetPurchaseProductAvgPriceN1($MRF_ID=0,$PRODUCT_ID=0,$INWARD_RECORD_ID=0)
	{
		########### NEW AVG PRICE CALCULATION ##############
		$OPENING_STOCK      = 0;
		$GET_CURRENT_STOCK  = StockLadger::where("product_id",$PRODUCT_ID)
								->where("mrf_id",$MRF_ID)
								->where("product_type",PRODUCT_PURCHASE)
								->where("stock_date",date("Y-m-d"))
								->first();
		$OPENING_STOCK      = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
		$OPENING_AVG        = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
		$TOTAL_OPENING_AMT  = _FormatNumberV2($OPENING_STOCK * $OPENING_AVG);
		$TOTAL_IN_QTY       = ProductInwardLadger::where("product_id",$PRODUCT_ID)
								->where("mrf_id",$MRF_ID)
								->where("id","!=",$INWARD_RECORD_ID)
								->where("product_type",PRODUCT_PURCHASE)
								->where("direct_dispatch",0)
								->where("inward_date",date("Y-m-d"))
								->sum("quantity");
		$TOTAL_OUT_QTY      = OutwardLadger::where("product_id",$PRODUCT_ID)
								->where("sales_product_id",0)
								->where("mrf_id",$MRF_ID)
								->where("direct_dispatch",0)
								->where("outward_date",date("Y-m-d"))
								->sum("quantity");
		$CLOSING_QTY        = _FormatNumberV2(($OPENING_STOCK + $TOTAL_IN_QTY) - $TOTAL_OUT_QTY);
		$TOTAL_PRICE        = 0;
		$GRAND_TOTAL        = 0;
		$GRAND_TOTAL        = _FormatNumberV2(($CLOSING_QTY * $OPENING_AVG));

		$TOTAL_PRICE_DATA   = ProductInwardLadger::select(\DB::raw("SUM(quantity * avg_price) as total_amount"),"quantity")
								->where("product_id",$PRODUCT_ID)
								->where("mrf_id",$MRF_ID)
								->where("id","=",$INWARD_RECORD_ID)
								->where("product_type",PRODUCT_PURCHASE)
								->where("direct_dispatch",0)
								->where("inward_date",date("Y-m-d"))
								->get()
								->toArray();
		if(!empty($TOTAL_PRICE_DATA)){
			foreach ($TOTAL_PRICE_DATA as $key => $value) {
				$GRAND_TOTAL += _FormatNumberV2($value['total_amount']);
				$CLOSING_QTY +=  _FormatNumberV2($value['quantity']);
			}
		}
		$AVG_PRICE   = (!empty($GRAND_TOTAL)) ? _FormatNumberV2($GRAND_TOTAL / $CLOSING_QTY) : 0;

		return $AVG_PRICE;
	}
	/*
	Use     : To Calculate the Sales product avg price
	Author  : Axay Shah
	Date    : 28 May 2021
	*/
	public static function GetSalesProductAvgPriceN1($MRF_ID=0,$PURCHASE_PRODUCT_ID=0,$SALES_PRODUCT_ID=0,$INWARD_RECORD_ID=0,$STOCK_DATE="")
	{
		########### NEW AVG PRICE CALCULATION ##############
		$OPENING_STOCK      = 0;
		$GET_CURRENT_STOCK  = StockLadger::where("product_id",$SALES_PRODUCT_ID)->where("mrf_id",$MRF_ID)->where("product_type",PRODUCT_SALES)->where("stock_date",$STOCK_DATE)->first();
		$OPENING_STOCK      = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
		$OPENING_AVG        = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
		$TOTAL_OPENING_AMT  = _FormatNumberV2($OPENING_STOCK * $OPENING_AVG);
		$TOTAL_IN_QTY       = ProductInwardLadger::where("product_id",$SALES_PRODUCT_ID)
								->where("mrf_id",$MRF_ID)
								->where("product_type",PRODUCT_SALES)
								->where("id","!=",$INWARD_RECORD_ID)
								->where("direct_dispatch",0)
								->where("inward_date",date("Y-m-d"))
								->sum("quantity");
		$TOTAL_OUT_QTY      = OutwardLadger::where("sales_product_id",$SALES_PRODUCT_ID)->where("mrf_id",$MRF_ID)->where("outward_date",date("Y-m-d"))->where("direct_dispatch",0)
								->sum("quantity");
		$CLOSING_QTY        = _FormatNumberV2(($OPENING_STOCK + $TOTAL_IN_QTY) - $TOTAL_OUT_QTY);
		$TOTAL_PRICE        = 0;
		$GRAND_TOTAL        = 0;
		$GRAND_TOTAL        = _FormatNumberV2(($CLOSING_QTY * $OPENING_AVG));
		$TOTAL_PRICE_DATA   = ProductInwardLadger::select(\DB::raw("SUM(quantity * avg_price) as total_amount"),"quantity")
								->where("product_id",$SALES_PRODUCT_ID)
								->where("mrf_id",$MRF_ID)
								->where("id","=",$INWARD_RECORD_ID)
								->where("direct_dispatch",0)
								->where("product_type",PRODUCT_SALES)
								->where("inward_date",date("Y-m-d"))
								->get()
								->toArray();
		if(!empty($TOTAL_PRICE_DATA))
		{
			foreach ($TOTAL_PRICE_DATA as $key => $value) {
				$value['total_amount']  = (empty($value['total_amount'])) ? 0 : $value['total_amount'];
				$value['quantity']      = (empty($value['quantity'])) ? 0 : $value['quantity'];
				$GRAND_TOTAL 			+= _FormatNumberV2($value['total_amount']);
				$CLOSING_QTY 			+= _FormatNumberV2($value['quantity']);
			}
		}
		$AVG_PRICE = ($CLOSING_QTY > 0) ? _FormatNumberV2($GRAND_TOTAL / $CLOSING_QTY) : 0;
		return $AVG_PRICE;
	}

	/*
	UPDATE PURCHASE STOCK VALUE
	16 April 2022
	*/
	public static function UpdatePurchaseStockValue($DATE,$PRODUCT_ID,$MRF_ID)
	{
		$OPENING_TOTAL_VALUE    = 0;
		$OPENING_TOTAL_QTY      = 0;
		$AVG_PRICE              = 0;
		$CLOSING_STOCK          = 0;
		$OPENING_STOCK          = 0;
		$GET_CURRENT_STOCK      = StockLadger::where("product_id",$PRODUCT_ID)->where("mrf_id",$MRF_ID)->where("product_type",PRODUCT_PURCHASE)->where("stock_date",$DATE)->first();
		if($GET_CURRENT_STOCK)
		{
			$OPENING_TOTAL_QTY   	= _FormatNumberV2($GET_CURRENT_STOCK->opening_stock);
			$OPENING_TOTAL_VALUE 	= _FormatNumberV2($GET_CURRENT_STOCK->opening_stock * $GET_CURRENT_STOCK->avg_price);
			$AVG_PRICE           	= _FormatNumberV2($GET_CURRENT_STOCK->avg_price);
			$SQL 					= "	SELECT * FROM (
														SELECT product_id,
															mrf_id,
															type,
															avg_price,
															quantity,
															'1' as trn,
															(avg_price * quantity) as total_value,
															created_at
														FROM inward_ledger
														WHERE mrf_id = $MRF_ID AND product_id = $PRODUCT_ID
														AND product_type = '1' AND inward_date = '".$DATE."' AND direct_dispatch = 0
													UNION ALL
														SELECT product_id,
														mrf_id,
														type,
														avg_price,
														quantity,
														'2' as trn,
														(avg_price * quantity) as total_value,
														created_at
														FROM outward_ledger
														WHERE mrf_id = $MRF_ID AND product_id = $PRODUCT_ID AND outward_date = '".$DATE."' AND direct_dispatch = 0
													) as q ORDER BY created_at";
			$RECORD_DATA 			= \DB::select($SQL);
			if(!empty($RECORD_DATA)) {
				foreach($RECORD_DATA AS $RAW => $VALUE) {
					if($VALUE->trn == 1) {
						$OPENING_TOTAL_VALUE  = _FormatNumberV2($OPENING_TOTAL_QTY * $AVG_PRICE);
						$OPENING_TOTAL_VALUE  += _FormatNumberV2($VALUE->total_value);
						$OPENING_TOTAL_QTY    += $VALUE->quantity;
						$AVG_PRICE = ($OPENING_TOTAL_QTY > 0) ? _FormatNumberV2($OPENING_TOTAL_VALUE / $OPENING_TOTAL_QTY) : 0;
						$NEXT_DATE  = date('Y-m-d', strtotime('+1 day', strtotime($DATE)));
						StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,1,$MRF_ID,$DATE,$AVG_PRICE);
						StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,1,$MRF_ID,$NEXT_DATE,$AVG_PRICE);
					}
					if($VALUE->trn == 2){
						$AVG_PRICE  = StockLadger::where("product_id",$PRODUCT_ID)
										->where("mrf_id",$MRF_ID)
										->where("product_type",PRODUCT_PURCHASE)
										->where("stock_date",$DATE)
										->VALUE("avg_price");
						$OPENING_TOTAL_QTY    -= $VALUE->quantity;
					}
				}
			} else {
				$NEXT_DATE  = date('Y-m-d', strtotime('+1 day', strtotime($DATE)));
				StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,1,$MRF_ID,$DATE,$AVG_PRICE);
				StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,1,$MRF_ID,$NEXT_DATE,$AVG_PRICE);
			}
		}
	}
	/*
	Use     : To Calculate the Sales product avg price
	Author  : Axay Shah
	Date    : 09 June 2023
	*/
	public static function GetSalesProductCogsAvgPrice($MRF_ID=0,$PURCHASE_PRODUCT_ID=0,$SALES_PRODUCT_ID=0,$INWARD_RECORD_ID=0,$STOCK_DATE="")
	{
		########### NEW AVG PRICE CALCULATION ##############
		$OPENING_STOCK      = 0;
		$GET_CURRENT_STOCK  = StockLadger::where("product_id",$SALES_PRODUCT_ID)->where("mrf_id",$MRF_ID)->where("product_type",PRODUCT_SALES)->where("stock_date",$STOCK_DATE)->first();
		$OPENING_STOCK      = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
		$OPENING_AVG        = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->new_cogs_price : 0;
		$TOTAL_OPENING_AMT  = _FormatNumberV2($OPENING_STOCK * $OPENING_AVG);
		$TOTAL_IN_QTY       = ProductInwardLadger::where("product_id",$SALES_PRODUCT_ID)
							->where("mrf_id",$MRF_ID)
							->where("product_type",PRODUCT_SALES)
							->where("id","!=",$INWARD_RECORD_ID)
							->where("direct_dispatch",0)
							->where("inward_date",date("Y-m-d"))
							->sum("quantity");
		$TOTAL_OUT_QTY      = OutwardLadger::where("sales_product_id",$SALES_PRODUCT_ID)->where("mrf_id",$MRF_ID)->where("outward_date",date("Y-m-d"))->where("direct_dispatch",0)
								->sum("quantity");
		$CLOSING_QTY        = _FormatNumberV2(($OPENING_STOCK + $TOTAL_IN_QTY) - $TOTAL_OUT_QTY);
		$TOTAL_PRICE        = 0;
		$GRAND_TOTAL        = 0;
		$GRAND_TOTAL        = _FormatNumberV2(($CLOSING_QTY * $OPENING_AVG));
		$TOTAL_PRICE_DATA   = ProductInwardLadger::select(\DB::raw("SUM(quantity * new_cogs_price) as total_amount"),"quantity")
							->where("product_id",$SALES_PRODUCT_ID)
							->where("mrf_id",$MRF_ID)
							->where("id","=",$INWARD_RECORD_ID)
							->where("direct_dispatch",0)
							->where("product_type",PRODUCT_SALES)
							->where("inward_date",date("Y-m-d"))
							->get()
							->toArray();
		if(!empty($TOTAL_PRICE_DATA))
		{
			foreach ($TOTAL_PRICE_DATA as $key => $value) {
				$value['total_amount']  = (empty($value['total_amount'])) ? 0 : $value['total_amount'];
				$value['quantity']      = (empty($value['quantity'])) ? 0 : $value['quantity'];
				$GRAND_TOTAL 			+= _FormatNumberV2($value['total_amount']);
				$CLOSING_QTY 			+= _FormatNumberV2($value['quantity']);
			}
		}
		$AVG_PRICE = ($CLOSING_QTY > 0) ? _FormatNumberV2($GRAND_TOTAL / $CLOSING_QTY) : 0;
		return $AVG_PRICE;
	}
}
