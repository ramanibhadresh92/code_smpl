<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmBatchMaster;
use App\Models\WmBatchProductDetail;
use App\Models\WmSalesToPurchaseMap;
use App\Models\WmAutoStockPurchaseToSales;
use App\Models\AppointmentCollectionDetail;
use App\Models\AppointmentCollection;
use App\Models\WmBatchCollectionMap;
use App\Models\CompanyProductPriceDetail;
use App\Models\StockLadger;
use App\Models\WmBatchProductDetailsProcessAvgPrice;
use App\Models\Appoinment;
use DB;
use DatePeriod,DateTime,DateInterval;
class WmBatchAuditedProduct extends Model
{
	protected 	$table 		= 'wm_batch_audited_product';
	protected 	$primaryKey = 'aid'; // or null
	protected 	$guarded 	= ['aid'];
	public 		$timestamps = false;

	/*
	Use     : Insert batch audited Product
	Author  : Axay Shah
	Date    : 13 Mar,2019
	*/
	public static function insertBatchAuditedProductOld($request)
	{
		$add                = new self();
		$AvgPrice 			= 0;
		$add->id            = (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$add->qty           = (isset($request->qty) && !empty($request->qty)) ? $request->qty : 0;
		$add->no_of_bag     = (isset($request->no_of_bag) && !empty($request->no_of_bag)) ? $request->no_of_bag : 0;
		$add->created_by    = Auth()->user()->adminuserid;
		$add->created_date  = date('Y-m-d H:i:s');
		if ($add->save()) {
			/* IF PRODUCT IS ONE TO ONE MAPPING ADD STOCK INTO SALES */
			$QTY 	= $add->qty;
			if ($add->qty > 0)
			{
				############# AVG PRICE CALCULATION FOR PURCHASE PRODUCT ##############
				$Details 					= WmBatchProductDetail::find($request->id);
				if($Details){
					$totalProductPrice 		= 0;
					$totalProductQty 		= 0;
					$MRF_ID     			=  	(isset($Details->BatchData->master_dept_id)) ? $Details->BatchData->master_dept_id : 0;
					$PRODUCT_ID 			=  	(isset($Details->product_id)) ? $Details->product_id : 0;
					$collection_id 			= 	WmBatchCollectionMap::where("batch_id",$Details->batch_id)->pluck("collection_id");
					$appointment_id 		= AppointmentCollection::whereIn("collection_id",$collection_id)->pluck("appointment_id");
					########## AVG PRICE CALCULATION FOR COLLECTION ITEM ################
					$productPriceData      	= AppointmentCollectionDetail::select(
													\DB::raw("(product_customer_price * actual_coll_quantity) as total_value"),
													"actual_coll_quantity"
												)
												->where("product_id",$PRODUCT_ID)
												->whereIn("collection_id",$collection_id)
												->get()
												->toArray();
					if(!empty($productPriceData)){
						foreach($productPriceData as $data){
							$PRODUCT_QTY 		= WmBatchAuditedProduct::where("id",$Details->id)->value('qty');
							$PRODUCT_QTY 		= ($PRODUCT_QTY > 0) ? _FormatNumberV2($PRODUCT_QTY) : 0 ; 
							$totalProductQty 	+= _FormatNumberV2($PRODUCT_QTY);
							$totalProductPrice 	+= _FormatNumberV2($data["total_value"]);
						}
					}
					$CollectionAvgPrice 	= (!empty($totalProductPrice) && !empty($totalProductQty)) ? _FormatNumberV2($totalProductPrice / $totalProductQty) : 0;
					if(!empty($CollectionAvgPrice)){
						$AvgPrice 			= (!empty($CollectionAvgPrice) && !empty($totalProductQty)) ? _FormatNumberV2(($CollectionAvgPrice * $QTY ) / $QTY) : 0;
					}
					########## AVG PRICE CALCULATION FOR COLLECTION ITEM ################
					$totalPrice = 0;
					$totalQty 	= 0;
					if($Details->from_mrf == 1){
						$appointment_id = AppointmentCollection::whereIn("collection_id",$collection_id)->pluck("appointment_id");
						if(!empty($appointment_id)){
							$customerIds = Appoinment::whereIn("appointment_id",$appointment_id)->pluck("customer_id");
							if($customerIds){
								$priceGroups = CustomerMaster::whereIn("customer_id",$customerIds)
								->where("price_group",">",0)
								->pluck("price_group");

								if(!empty($priceGroups)){
									$price  = CompanyProductPriceDetail::whereIn("para_waste_type_id",$priceGroups)
									->where("product_id",$PRODUCT_ID)
									->where("price",">","0.01")
									->groupBy("price")
									->get()
									->pluck("price");
									if(!empty($price)){
										foreach($price as $rs){
											$totalPrice += _FormatNumberV2($QTY * $rs);
											$totalQty 	+= _FormatNumberV2($QTY);
										}
										$AvgPrice = ($totalQty > 0 && $totalPrice > 0) ? _FormatNumberV2($totalPrice / $totalQty) : 0;
									}
								}
							}
						}
					}
					############ DIRECT DISPATCH AVG PRICE AND QTY. NOTE CALCULATE AS DISCUSS WITH SAMIR SIR - 29 JUNE 2021###################
					$DIRECT_DISPATCH_FLAG = false;
					$DIRECT_DISPATCH_DATA = Appoinment::whereIn("appointment_id",$appointment_id)->pluck("direct_dispatch");
					if(!empty($DIRECT_DISPATCH_DATA)){
						foreach($DIRECT_DISPATCH_DATA as $DIRECT_DISPATCH){
							$DIRECT_DISPATCH_FLAG = ($DIRECT_DISPATCH == "1") ? true : false;
						}
					}
					############ DIRECT DISPATCH AVG PRICE AND QTY. NOTE CALCULATE AS DISCUSS WITH SAMIR SIR - 29 JUNE 2021###################
					$array['product_id']    = $PRODUCT_ID;
                    $array['mrf_id']        = $MRF_ID;
                    $array['type']          = TYPE_PURCHASE;
                    $array['product_type']  = PRODUCT_PURCHASE;
                    $array['batch_id']      = $Details->batch_id;
                    $array['inward_date']   = date("Y-m-d");
                    $array['quantity']      = $add->qty;
                    $array['avg_price']     	= ($DIRECT_DISPATCH_FLAG) ? 0 :_FormatNumberV2($AvgPrice);
                    $array['direct_dispatch']   = ($DIRECT_DISPATCH_FLAG == 1) ? 1 : 0;
                    $inward_record_id 		= ProductInwardLadger::AutoAddInward($array);
                    if(!$DIRECT_DISPATCH_FLAG){
						$STOCK_AVG_PRICE 	= WmBatchProductDetail::GetPurchaseProductAvgPriceN1($MRF_ID,$PRODUCT_ID,$inward_record_id);
                    	StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,date("Y-m-d"),$STOCK_AVG_PRICE);
                    }
                    ############# AVG PRICE CALCULATION FOR PURCHASE PRODUCT ##############
					$IsOne2OneMap 	= WmSalesToPurchaseMap::VerifyOne2OneMapping($Details->product_id);
					if ($IsOne2OneMap)
					{
						$SaveFields 								= array();
						$SaveFields['wm_batch_audited_product_id'] 	= $add->aid;
						$SaveFields['wm_batch_product_detail_id'] 	= $request->id;
						$SaveFields['created_by'] 					= Auth()->user()->adminuserid;
						$SaveFields['updated_by'] 					= Auth()->user()->adminuserid;
						WmAutoStockPurchaseToSales::AddProductToProcess($SaveFields);
					}
				}

			}
			/* IF PRODUCT IS ONE TO ONE MAPPING ADD STOCK INTO SALES */
		}
		return $add;
	}

	/*
	Use     : Insert batch audited Product
	Author  : Axay Shah
	Date    : 13 Mar,2019
	*/
	public static function insertBatchAuditedProduct($request)
	{
		$add                = new self();
		$AvgPrice 			= 0;
		$add->id            = (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$add->qty           = (isset($request->qty) && !empty($request->qty)) ? $request->qty : 0;
		$add->no_of_bag     = (isset($request->no_of_bag) && !empty($request->no_of_bag)) ? $request->no_of_bag : 0;
		$add->created_by    = Auth()->user()->adminuserid;
		$add->created_date  = date('Y-m-d H:i:s');
		if ($add->save()) {
			/* IF PRODUCT IS ONE TO ONE MAPPING ADD STOCK INTO SALES */
			$QTY 	= $add->qty;
			if ($add->qty > 0)
			{
				############# AVG PRICE CALCULATION FOR PURCHASE PRODUCT ##############
				$Details 					= WmBatchProductDetail::find($request->id);
				if($Details){
					$totalProductPrice 		= 0;
					$totalProductQty 		= 0;
					$AvgPrice 				= 0;
					$MRF_ID     			= (isset($Details->BatchData->master_dept_id)) ? $Details->BatchData->master_dept_id : 0;
					$PRODUCT_ID 			= (isset($Details->product_id)) ? $Details->product_id : 0;
					$collection_id 			= WmBatchCollectionMap::where("batch_id",$Details->batch_id)->pluck("collection_id");
					$appointment_id 		= AppointmentCollection::whereIn("collection_id",$collection_id)->pluck("appointment_id");
					########## AVG PRICE CALCULATION FOR COLLECTION ITEM ################
					
					$DIRECT_DISPATCH_FLAG = false;

					$DIRECT_DISPATCH_DATA = Appoinment::whereIn("appointment_id",$appointment_id)->pluck("direct_dispatch");
					if(!empty($DIRECT_DISPATCH_DATA)){
						foreach($DIRECT_DISPATCH_DATA as $DIRECT_DISPATCH){
							$DIRECT_DISPATCH_FLAG = ($DIRECT_DISPATCH == "1") ? true : false;
						}
					}
					############ DIRECT DISPATCH AVG PRICE AND QTY. NOTE CALCULATE AS DISCUSS WITH SAMIR SIR - 29 JUNE 2021###################
					if($DIRECT_DISPATCH_FLAG){
						$array['product_id']    	= $PRODUCT_ID;
	                    $array['mrf_id']        	= $MRF_ID;
	                    $array['type']          	= TYPE_PURCHASE;
	                    $array['product_type']  	= PRODUCT_PURCHASE;
	                    $array['batch_id']      	= $Details->batch_id;
	                    $array['inward_date']   	= date("Y-m-d");
	                    $array['quantity']      	= $add->qty;
	                    $array['avg_price']     	= ($DIRECT_DISPATCH_FLAG) ? 0 :_FormatNumberV2($AvgPrice);
	                    $array['direct_dispatch']   = ($DIRECT_DISPATCH_FLAG == 1) ? 1 : 0;
	                    ############# AVG PRICE CALCULATION FOR PURCHASE PRODUCT 08 JAN 2021  ##############
	                    $inward_record_id 			= ProductInwardLadger::AutoAddInward($array);
	                    if(!$DIRECT_DISPATCH_FLAG){
							$STOCK_AVG_PRICE 		= WmBatchProductDetail::GetPurchaseProductAvgPrice($MRF_ID,$PRODUCT_ID,$inward_record_id);
	                    	StockLadger::UpdateProductStockAvgPrice($PRODUCT_ID,PRODUCT_PURCHASE,$MRF_ID,date("Y-m-d"),$STOCK_AVG_PRICE);
	                    }
	                }
                    ############# AVG PRICE CALCULATION FOR PURCHASE PRODUCT 08 JAN 2021  ##############
					$IsOne2OneMap 	= WmSalesToPurchaseMap::VerifyOne2OneMapping($Details->product_id);
					if ($IsOne2OneMap)
					{
						$SaveFields 								= array();
						$SaveFields['wm_batch_audited_product_id'] 	= $add->aid;
						$SaveFields['wm_batch_product_detail_id'] 	= $request->id;
						$SaveFields['created_by'] 					= Auth()->user()->adminuserid;
						$SaveFields['updated_by'] 					= Auth()->user()->adminuserid;
						WmAutoStockPurchaseToSales::AddProductToProcess($SaveFields);
					}
				}
			}
			/* IF PRODUCT IS ONE TO ONE MAPPING ADD STOCK INTO SALES */
			LR_Modules_Log_CompanyUserActionLog($request,$add->aid);
		}
		return $add;
	}
	/*
	FOR backup table

	*/
	public static function updateInwardLastestAvgPrice(){
		$Details 	=  	\DB::table("inward_ledger")->where("batch_id",">",0)->where("product_type",1)
					// ->where("batch_id",46345)
					->whereIn("mrf_id",[3,11])
					->where("inward_date",">=",'2021-06-01')
					->orderBy("inward_date")
					->get();

		if($Details){
			foreach($Details as $batch){
				$QTY 				= $batch->quantity;
				$AvgPrice 			= 0;
				$totalProductPrice 	= 0;
				$totalProductQty 	= 0;
				$INWARD_ID 			= $batch->id;
				$MRF_ID     		= (isset($batch->mrf_id)) ? $batch->mrf_id : 0;

				$PRODUCT_ID 		= (isset($batch->product_id)) ? $batch->product_id : 0;
				$collection_id 		= WmBatchCollectionMap::where("batch_id",$batch->batch_id)->pluck("collection_id");
				$productPriceData   = AppointmentCollectionDetail::select(
										\DB::raw("(product_customer_price * actual_coll_quantity) as total_value"),
										"actual_coll_quantity"
									)
				->where("product_id",$PRODUCT_ID)
				->whereIn("collection_id",$collection_id)
				->get()
				->toArray();

				if($QTY > 0){
					if(!empty($productPriceData)){
						foreach($productPriceData as $data){
							$totalProductQty 	+= _FormatNumberV2($data["actual_coll_quantity"]);
							$totalProductPrice 	+= _FormatNumberV2($data["total_value"]);
						}
						$CollectionAvgPrice = (!empty($totalProductPrice) && !empty($totalProductQty)) ? _FormatNumberV2($totalProductPrice / $totalProductQty) : 0;
						if(!empty($CollectionAvgPrice)){
							$AvgPrice 	= (!empty($CollectionAvgPrice) && !empty($totalProductQty)) ? _FormatNumberV2(($CollectionAvgPrice * $QTY ) / $QTY) : 0;
						}
					}else{
						$totalPrice = 0;
						$totalQty 	= 0;

						$appointment_id = AppointmentCollection::whereIn("collection_id",$collection_id)->pluck("appointment_id");
						if(!empty($appointment_id)){
							$customerIds = Appoinment::whereIn("appointment_id",$appointment_id)->pluck("customer_id");
							if($customerIds){
								$priceGroups = CustomerMaster::whereIn("customer_id",$customerIds)
								->where("price_group",">",0)
								->pluck("price_group");

								if(!empty($priceGroups)){
									$price  = CompanyProductPriceDetail::whereIn("para_waste_type_id",$priceGroups)
									->where("product_id",$PRODUCT_ID)
									->where("price",">","0.01")
									->groupBy("price")
									->get()
									->pluck("price");
									if(!empty($price)){
										foreach($price as $rs){
											$totalPrice += _FormatNumberV2($QTY * $rs);
											$totalQty 	+= _FormatNumberV2($QTY);
										}
										$AvgPrice = ($totalQty > 0 && $totalPrice > 0) ? _FormatNumberV2($totalPrice / $totalPrice) : 0;
									}
								}
							}
						}
					}
				}
				$AvgPrice = _FormatNumberV2($AvgPrice);
				$UPDATE = DB::table("inward_ledger")->where("id",$INWARD_ID)->update(["avg_price"=>$AvgPrice]);
			}
		}
		echo "DONE";
	}

	public static function updateInwardSalesProductAvgPriceFormProductionReport(){
		return false;
		$Details 	=  	\DB::table("inward_ledger_latest")
						->where("production_report_id",">",0)
						->where("purchase_product_id",">",0)
						->whereIn("mrf_id",[3,11])
						->where("inward_date",">=",'2021-06-27')
						->orderBy("inward_date")
						->groupBy("production_report_id","inward_date")
						->get();
		if($Details){

			foreach($Details as $batch){
				$production_report_id 	= $batch->production_report_id;
				$purchase_product_id 	= $batch->purchase_product_id;
				$inward_date 			= $batch->inward_date;
				$mrf_id 				= $batch->mrf_id;
				$productPriceData   	= DB::table("inward_ledger_latest")->select(
										\DB::raw("(avg_price * quantity) as total_value"),
										"quantity","avg_price"
									)
				->where("product_id",$purchase_product_id)
				->where("product_type",1)
				->where("mrf_id",$mrf_id)
				->where("inward_date",$inward_date)
				->get()
				->toArray();
				$totalProductQty = 0;
				$totalProductPrice = 0;
				if(!empty($productPriceData)){
					foreach($productPriceData as $data){

						$totalProductQty 	+= _FormatNumberV2($data->quantity);
						$totalProductPrice 	+= _FormatNumberV2($data->total_value);
					}
				}

				$AvgPrice 	= (!empty($totalProductPrice) && !empty($totalProductQty)) ? _FormatNumberV2($totalProductPrice / $totalProductQty) : 0;
				$AvgPrice 	= _FormatNumberV2($AvgPrice);
				$UPDATE 	= DB::table("inward_ledger_latest")->where("production_report_id",$production_report_id)->where("product_type",2)->update(["avg_price"=>$AvgPrice]);
			}
		}
		echo "DONE";
	}
	// For Live Table
	public static function updateInwardLastestAvgPriceV2(){
		$Details 	=  	\DB::table("inward_ledger")->where("batch_id",">",0)
					->where("product_type",1)
					->whereIn("mrf_id",[3,11,22,23,26,27,48,59])
					// ->where("batch_id",53482)
					->where("inward_date",">=","2021-06-01")
					// ->where("inward_date","<=","2021-06-23")
					->orderBy("inward_date")
					->get();
		// prd($Details);
		if($Details){
			foreach($Details as $batch){
				$QTY 				= $batch->quantity;
				$AvgPrice 			= 0;
				$totalProductPrice 	= 0;
				$totalProductQty 	= 0;
				$INWARD_ID 			= $batch->id;
				$MRF_ID     		= (isset($batch->mrf_id)) ? $batch->mrf_id : 0;

				$PRODUCT_ID 		= (isset($batch->product_id)) ? $batch->product_id : 0;
				$collection_id 		= WmBatchCollectionMap::where("batch_id",$batch->batch_id)->pluck("collection_id");
				$productPriceData   = AppointmentCollectionDetail::select(
										\DB::raw("(product_customer_price * actual_coll_quantity) as total_value"),
										"actual_coll_quantity"
									)
				->where("product_id",$PRODUCT_ID)
				->whereIn("collection_id",$collection_id)
				->get()
				->toArray();
				if($QTY > 0){
					if(!empty($productPriceData)){
						foreach($productPriceData as $data){
							$totalProductQty 	+= _FormatNumberV2($data["actual_coll_quantity"]);
							$totalProductPrice 	+= _FormatNumberV2($data["total_value"]);
						}
						$CollectionAvgPrice = (!empty($totalProductPrice) && !empty($totalProductQty)) ? _FormatNumberV2($totalProductPrice / $totalProductQty) : 0;
						if(!empty($CollectionAvgPrice)){
							$AvgPrice 	= (!empty($CollectionAvgPrice) && !empty($totalProductQty)) ? _FormatNumberV2(($CollectionAvgPrice * $QTY ) / $QTY) : 0;
						}
					}else{
						$totalPrice = 0;
						$totalQty 	= 0;

						$appointment_id = AppointmentCollection::whereIn("collection_id",$collection_id)->pluck("appointment_id");
						if(!empty($appointment_id)){
							$customerIds = Appoinment::whereIn("appointment_id",$appointment_id)->pluck("customer_id");
							if($customerIds){
								$priceGroups = CustomerMaster::whereIn("customer_id",$customerIds)
								->where("price_group",">",0)
								->pluck("price_group");

								if(!empty($priceGroups)){
									$price  = CompanyProductPriceDetail::whereIn("para_waste_type_id",$priceGroups)
									->where("product_id",$PRODUCT_ID)
									->where("price",">","0.01")
									->groupBy("price")
									->get()
									->pluck("price");
									if(!empty($price)){
										foreach($price as $rs){
											$totalPrice += _FormatNumberV2($QTY * $rs);
											$totalQty 	+= _FormatNumberV2($QTY);
										}
										$AvgPrice = ($totalQty > 0 && $totalPrice > 0) ? _FormatNumberV2($totalPrice / $totalPrice) : 0;
									}
								}
							}
						}
					}
				}

				$AvgPrice = ($AvgPrice > 0) ? _FormatNumberV2($AvgPrice) : 0;
				$UPDATE = DB::table("inward_ledger")->where("id",$INWARD_ID)->update(["avg_price"=>$AvgPrice]);
			}
		}
		echo "DONE";
	}

	public static function updateInwardSalesProductAvgPriceFormProductionReportV2(){
		$Details 	=  	\DB::table("inward_ledger")
						->where("production_report_id",">",0)
						->where("purchase_product_id",">",0)
						->where("inward_date",">=","2021-06-01")
						->whereIn("mrf_id",[3,11,22,23,26,27,48,59])
						->orderBy("inward_date")
						->groupBy("production_report_id","inward_date")
						->get();

		if($Details){

			foreach($Details as $batch){
				$production_report_id 	= $batch->production_report_id;
				$purchase_product_id 	= $batch->purchase_product_id;
				$inward_date 			= $batch->inward_date;
				$mrf_id 				= $batch->mrf_id;
				$productPriceData   	= DB::table("inward_ledger")->select(
										\DB::raw("(avg_price * quantity) as total_value"),
										"quantity","avg_price"
									)
				->where("product_id",$purchase_product_id)
				->where("product_type",1)
				->where("mrf_id",$mrf_id)
				->where("inward_date",$inward_date)
				->get()
				->toArray();
				$totalProductQty = 0;
				$totalProductPrice = 0;
				if(!empty($productPriceData)){
					foreach($productPriceData as $data){

						$totalProductQty 	+= _FormatNumberV2($data->quantity);
						$totalProductPrice 	+= _FormatNumberV2($data->total_value);
					}
				}

				$AvgPrice 	= (!empty($totalProductPrice) && !empty($totalProductQty)) ? _FormatNumberV2($totalProductPrice / $totalProductQty) : 0;
				$AvgPrice 	= _FormatNumberV2($AvgPrice);
				$UPDATE 	= DB::table("inward_ledger")->where("production_report_id",$production_report_id)->where("product_type",2)->update(["avg_price"=>$AvgPrice]);
			}
		}
		echo "DONE";
	}

	/*
    Use     : UPDATE PURCHASE PRODUCT STOCK AVG PRICE 2
    Author  : Axay Shah
    Date    : 28 May 2021
    */
    public static function UpdatePurchaseProductStockAvgPriceV2(){
    	// 3,11,22,23,26,27,48,59echo "ASdf";exit;
        ########### NEW AVG PRICE CALCULATION ##############
        $MRF_ID =  3 ;
    	$PRODUCT_DATA = \DB::table("company_product_master")
    	->where("para_status_id",6001)
    	->pluck("id");
    	if(!empty($PRODUCT_DATA)){
    		// 3,11,22,26,48,49;

    		foreach($PRODUCT_DATA AS $PRODUCT_ID){
    			$period = new DatePeriod(
				     new DateTime('2021-04-01'),
				     new DateInterval('P1D'),
				     new DateTime('2021-07-08')
				);
				foreach ($period as $key => $value) {
				   	$DATE = $value->format('Y-m-d');
				   	########
				   	$OPENING_STOCK          =   0;
			        $GET_CURRENT_STOCK      =   StockLadger::where("product_id",$PRODUCT_ID)
			                                    ->where("mrf_id",$MRF_ID)
			                                    ->where("product_type",PRODUCT_PURCHASE)
			                                    ->where("stock_date",$DATE)
			                                    ->first();
			        $OPENING_STOCK          =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
			        $OPENING_STOCK_AVG      =   ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
			        $TOTAL_OPENING_AMT      =   _FormatNumberV2($OPENING_STOCK * $OPENING_STOCK_AVG);

			        $TOTAL_QTY              =   ProductInwardLadger::where("product_id",$PRODUCT_ID)
			                                    ->where("mrf_id",$MRF_ID)
			                                    ->where("direct_dispatch",0)
			                                    ->where("product_type",PRODUCT_PURCHASE)
			                                    ->where("inward_date",$DATE)
			                                    ->sum("quantity");
			        $TOTAL_PRICE_DATA       =   ProductInwardLadger::select(\DB::raw("SUM(quantity * avg_price) as total_amount"))
			                                    ->where("product_id",$PRODUCT_ID)
			                                    ->where("direct_dispatch",0)
			                                    ->where("mrf_id",$MRF_ID)
			                                    ->where("product_type",PRODUCT_PURCHASE)
			                                    ->where("inward_date",$DATE)
			                                    ->get()->toArray();
			        $TOTAL_PRICE            = 0;
			        $GRAND_TOTAL 			= 0;
			        if(!empty($TOTAL_PRICE_DATA)){
			            foreach ($TOTAL_PRICE_DATA as $key => $value) {
			                $GRAND_TOTAL += _FormatNumberV2($value['total_amount']);
			            }
			        }
			        $GRAND_TOTAL    += $TOTAL_OPENING_AMT;
			        $TOTAL_QTY      += $OPENING_STOCK;
			        $AVG_PRICE      = (!empty($GRAND_TOTAL)) ? _FormatNumberV2($GRAND_TOTAL / $TOTAL_QTY) : 0;
			        ####### UPDATE STOCK IN CURRENT DATE ########
			        $UPDATE 		= StockLadger::updateOrCreate([
				         	"product_id" 	=> $PRODUCT_ID,
				         	"mrf_id"		=> $MRF_ID,
				         	"stock_date" 	=> $DATE,
				         	"product_type" 	=> PRODUCT_PURCHASE
				        ],
			         	[
			           		"avg_price" 	=> $AVG_PRICE,
			        		"company_id" 	=> 1
						]);
			        ######### UPDATE STOCK IN NEXT DATE #########
			        	$NEXT_DATE 			= date('Y-m-d', strtotime($DATE .' +1 day'));
			        	$UPDATE 			= StockLadger::updateOrCreate([
				         	"product_id" 	=> $PRODUCT_ID,
				         	"mrf_id"		=> $MRF_ID,
				         	"stock_date" 	=> $NEXT_DATE,
				         	"product_type" 	=> PRODUCT_PURCHASE
				        ],
			         	[
			           		"avg_price" 	=> $AVG_PRICE,
			        		"company_id" 	=> 1
						]);
				   	########

				}
    		}
    	}
    	echo "DONE FOR $MRF_ID";
    	exit;
    }
    /*
    Use     : UPDATE SALES PRODUCT STOCK AVG PRICE V2
    Author  : Axay Shah
    Date    : 28 May 2021
    */
    public static function UpdateSalesProductStockAvgPriceV2(){
		echo date("Y-m-d H:i:s")."<br>";
        ########### NEW AVG PRICE CALCULATION ##############
        $DEPARTMENT = WmDepartment::where("status",1)->where("is_virtual",0)->pluck("id");
        // $DEPARTMENT = array(48);
        if(!empty($DEPARTMENT)){
        	foreach($DEPARTMENT AS $MRF_ID){
				$PRODUCT_DATA = \DB::table("wm_product_master")
		    	->where("status",1)
		    	// ->where("id",76)
		    	->pluck("id");
		    	if(!empty($PRODUCT_DATA)){
		    		foreach($PRODUCT_DATA AS $PRODUCT_ID){
		    			$NEXT_DATE 	= date('Y-m-d');
		    			$DATE 		= date('Y-m-d', strtotime($NEXT_DATE .' -1 day'));
		    			$PRI_DATE 	= date('Y-m-d', strtotime($DATE .' -1 day'));
		    			############ USE FOR SPECIFIC DATE RECORD UPDATE ############
		    			// $NEXT_DATE 	= date('Y-m-d');
		    			// $DATE 		= '2021-12-30';
		    			############ USE FOR SPECIFIC DATE RECORD UPDATE ############

		    			$period 	= new DatePeriod(
						     new DateTime($DATE),
						     new DateInterval('P1D'),
						     new DateTime($NEXT_DATE)
						);
						foreach ($period as $key => $value) {
						   	$DATE 				= $value->format('Y-m-d');
						   	$PRI_DATE 			= date('Y-m-d', strtotime($DATE .' -1 day'));
						   	$PRI_AVG_PRICE  	= StockLadger::where("product_id",$PRODUCT_ID)
					                            ->where("mrf_id",$MRF_ID)
					                            ->where("product_type",PRODUCT_SALES)
					                            ->where("stock_date",$PRI_DATE)
					                            ->value("avg_price");
						   	$GET_CURRENT_STOCK  = StockLadger::where("product_id",$PRODUCT_ID)
					                            ->where("mrf_id",$MRF_ID)
					                            ->where("product_type",PRODUCT_SALES)
					                            ->where("stock_date",$DATE)
					                            ->first();
					        $OPENING_STOCK      = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
					        $OPENING_AVG        = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
							
							$GET_CURRENT_STOCK  = StockLadger::where("product_id",$PRODUCT_ID)
					                            ->where("mrf_id",$MRF_ID)
					                            ->where("product_type",PRODUCT_SALES)
					                            ->where("stock_date",$DATE)
					                            ->first();
					        $OPENING_STOCK      = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->opening_stock : 0;
					        $OPENING_AVG        = ($GET_CURRENT_STOCK) ? $GET_CURRENT_STOCK->avg_price : 0;
						    
					        $TOTAL_OPENING_AMT  = _FormatNumberV2($OPENING_STOCK * $OPENING_AVG);
					        $TOTAL_QTY          = ProductInwardLadger::where("product_id",$PRODUCT_ID)
					                            ->where("mrf_id",$MRF_ID)
					                            ->where("direct_dispatch",0)
					                            ->where("product_type",PRODUCT_SALES)
					                            ->where("inward_date",$DATE)
					                            ->sum("quantity");
					        $TOTAL_PRICE_DATA  =  ProductInwardLadger::select(\DB::raw("SUM(quantity * avg_price) as total_amount"))
					                            ->where("product_id",$PRODUCT_ID)
					                            ->where("mrf_id",$MRF_ID)
					                            ->where("direct_dispatch",0)
					                            ->where("product_type",PRODUCT_SALES)
					                            ->where("inward_date",$DATE)
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
					        $AVG_PRICE 		  = ($AVG_PRICE == 0) ? $PRI_AVG_PRICE : $AVG_PRICE;
					       
							####### UPDATE STOCK IN CURRENT DATE ########
					        $UPDATE 		= StockLadger::updateOrCreate([
						         	"product_id" 	=> $PRODUCT_ID,
						         	"mrf_id"		=> $MRF_ID,
						         	"stock_date" 	=> $DATE,
						         	"product_type" 	=> PRODUCT_SALES
						        ],
					         	[
					           		"avg_price" 	=> $AVG_PRICE,
					        		"company_id" 	=> 1
								]);
				        	######### UPDATE STOCK IN NEXT DATE #########
				        	$NEXT_DATE 			= date('Y-m-d', strtotime($DATE .' +1 day'));
				        	$UPDATE 			= StockLadger::updateOrCreate([
					         	"product_id" 	=> $PRODUCT_ID,
					         	"mrf_id"		=> $MRF_ID,
					         	"stock_date" 	=> $NEXT_DATE,
					         	"product_type" 	=> PRODUCT_SALES
					        ],
				         	[
				           		"avg_price" 	=> $AVG_PRICE,
				        		"company_id" 	=> 1
							]);
						}
		    		}
		    	}
		    	echo date("Y-m-d H:i:s")."<br>";
		    	echo "DONE FOR $MRF_ID";
		    	exit;
	    	}
	    }
    }
    public static function updateInwardSalesLatestAvgPriceV2(){
    	echo "<pre>";
		$Details 	=  	\DB::table("inward_ledger")->where("production_report_id",">",0)
						->where("product_type",2)
						->where("mrf_id",23)
						->where("inward_date",">=","2021-04-01")
						->orderBy("inward_date")
						->get();
						print_r($Details);
		if($Details){
			foreach($Details as $data){
				$PRODUCT_ID 			= $data->product_id;
				$PURCHASE_PRODUCT_ID 	= $data->purchase_product_id;
				$INWARD_DATE 			= $data->inward_date;
				$MRF_ID 				= $data->mrf_id;
				$ID 					= $data->id;

				$AVG_PRICE = \DB::table("stock_ladger")
				->where("product_id",$PURCHASE_PRODUCT_ID)
				->where("product_type",1)
				->where("stock_date",$INWARD_DATE)
				->where("mrf_id",$MRF_ID)
				->value("avg_price");
				$AVG_PRICE 	= _FormatNumberV2($AVG_PRICE);
				// prd($ID);
				$UPDATE 	= DB::table("inward_ledger")->where("id",$ID)->update(["avg_price"=>$AVG_PRICE]);

			}
		}
		echo "DONE";
	}

	
    /*
	Use     : GET BATCH PRODUCT DETAILS DATA
	Author  : Axay Shah
	Date    : 24 JAN,2022
	*/
	public static function InsertProductProcessDataForAvgPriceAxay($batch_id)
	{
		$AvgPrice 					= 0;
		$Details 					= WmBatchProductDetail::where("batch_id",$batch_id)->where("wrong_product_id","=",0)->get();
		if(!empty($Details)){
			foreach($Details as $key => $value){
				$totalProductPrice 	= 0;
				$totalProductQty 	= 0;
				$MRF_ID  			= (isset($value->BatchData->master_dept_id)) ? $value->BatchData->master_dept_id : 0;
				$PRODUCT_ID 		= (isset($value->product_id)) ? $value->product_id : 0;
				$collection_id 		= WmBatchCollectionMap::where("batch_id",$batch_id)->pluck("collection_id");
				$appointment_id 	= AppointmentCollection::whereIn("collection_id",$collection_id)->pluck("appointment_id");
				$productPriceData   = AppointmentCollectionDetail::select(
									\DB::raw("(product_customer_price * actual_coll_quantity) as total_value"),
									"actual_coll_quantity","product_customer_price AS price")
									->where("product_id",$PRODUCT_ID)
									->whereIn("collection_id",$collection_id)
									->get()
									->toArray();
				if(!empty($productPriceData)){
					foreach($productPriceData as $data){
						$totalProductPrice 	+= _FormatNumberV2($data["total_value"]);
					}
				}
				$PRODUCT_QTY 		= WmBatchAuditedProduct::where("id",$value->id)->sum('qty');
				$PRODUCT_QTY 		= ($PRODUCT_QTY > 0) ? _FormatNumberV2($PRODUCT_QTY) : 0 ; 
				$totalProductQty 	+= _FormatNumberV2($PRODUCT_QTY);
				$WRONG_PRODUCT_RECORD_ID  	= array(); 
				$WRONG_PRODUCT_DATA 		= WmBatchProductDetail::where("wrong_product_id",$value->product_id)->where("batch_id",$batch_id)->get();
				if(!empty($WRONG_PRODUCT_DATA)){
					foreach($WRONG_PRODUCT_DATA as $data){
						$AUDITED_QTY = WmBatchAuditedProduct::where("id",$data->id)->sum('qty');
						if($AUDITED_QTY > 0){
							$totalProductQty 	+= _FormatNumberV2($AUDITED_QTY);
							array_push($WRONG_PRODUCT_RECORD_ID,$data->id);
						}
					}
				}
				
				
				$AvgPrice 	= (!empty($totalProductPrice) && !empty($totalProductQty)) ? _FormatNumberV2($totalProductPrice / $totalProductQty) : 0;
				WmBatchProductDetailsProcessAvgPrice::AddProductData($batch_id,$value->id,$PRODUCT_ID,$AvgPrice);
				if(!empty($WRONG_PRODUCT_RECORD_ID)){
					$GET_WRONG_PRODUCT_DATA = WmBatchProductDetail::whereIn("id",$WRONG_PRODUCT_RECORD_ID)->get();
					if(!empty($GET_WRONG_PRODUCT_DATA)){
						foreach($GET_WRONG_PRODUCT_DATA AS $WRONG_DATA_VALUE){
							WmBatchProductDetailsProcessAvgPrice::AddProductData($batch_id,$WRONG_DATA_VALUE->id,$WRONG_DATA_VALUE->product_id,$AvgPrice);
						}
					}
				}	
			}
		}
	}

	/*
	Use     : GET BATCH PRODUCT DETAILS DATA
	Author  : Axay Shah
	Date    : 24 JAN,2022
	*/
	public static function InsertProductProcessDataForAvgPrice($batch_id)
	{
		$AvgPrice 			= 0;
		$Details  			= WmBatchProductDetail::where("batch_id",$batch_id)->where("wrong_product_id","=",0)->get();
		$collection_id 		= WmBatchCollectionMap::where("batch_id",$batch_id)->pluck("collection_id")->toArray();
		$appointment_id 	= AppointmentCollection::whereIn("collection_id",$collection_id)->pluck("appointment_id")->toArray();

		$TOTAL_COL_PRO		= "	SELECT COUNT(DISTINCT appointment_collection_details.product_id) AS TOTAL_NO_OF_PRODUCT_IN_COLLECTION,
								SUM(appointment_collection_details.price) AS TOTAL_PAID_AMOUNT
								FROM appointment_collection_details 
								WHERE appointment_collection_details.collection_id IN (".implode(",",$collection_id).")";
		$RESULT_COL_PRO  	= DB::select($TOTAL_COL_PRO);
		$TOTAL_COL_CNT 		= isset($RESULT_COL_PRO[0]->TOTAL_NO_OF_PRODUCT_IN_COLLECTION)?($RESULT_COL_PRO[0]->TOTAL_NO_OF_PRODUCT_IN_COLLECTION):0;
		$TOTAL_COL_AMT 		= isset($RESULT_COL_PRO[0]->TOTAL_PAID_AMOUNT)?($RESULT_COL_PRO[0]->TOTAL_PAID_AMOUNT):0;
		$TOTAL_AUD_PRO		= "	SELECT COUNT(DISTINCT wm_batch_product_detail.product_id) AS TOTAL_NO_OF_PRODUCT_IN_AUDIT
								FROM wm_batch_audited_product
								INNER JOIN wm_batch_product_detail ON wm_batch_audited_product.id = wm_batch_product_detail.id
								INNER JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_product_detail.batch_id
								WHERE wm_batch_master.batch_id IN (".$batch_id.")";
		$RESULT_AUD_PRO  	= DB::select($TOTAL_AUD_PRO);
		$TOTAL_AUD_CNT 		= isset($RESULT_AUD_PRO[0]->TOTAL_NO_OF_PRODUCT_IN_AUDIT)?($RESULT_AUD_PRO[0]->TOTAL_NO_OF_PRODUCT_IN_AUDIT):0;
		
		$TOTAL_AUD_QTY		= "	SELECT SUM(wm_batch_audited_product.qty) AS TOTAL_AUDIT_QTY
								FROM wm_batch_audited_product
								INNER JOIN wm_batch_product_detail ON wm_batch_audited_product.id = wm_batch_product_detail.id
								INNER JOIN wm_batch_master ON wm_batch_master.batch_id = wm_batch_product_detail.batch_id
								WHERE wm_batch_master.batch_id IN (".$batch_id.")";
		$RESULT_AUD_QTY  	= DB::select($TOTAL_AUD_QTY);
		$TOTAL_AUD_QT 		= isset($RESULT_AUD_QTY[0]->TOTAL_AUDIT_QTY)?($RESULT_AUD_QTY[0]->TOTAL_AUDIT_QTY):0;
		$AVERAGE_PRICE 		= 0;
		$AUDIT_COUNT_DIFF	= false;
		if ($TOTAL_AUD_CNT < $TOTAL_COL_CNT && $TOTAL_AUD_QT > 0 && $TOTAL_COL_AMT > 0) {
			$AVERAGE_PRICE 		= round(($TOTAL_COL_AMT / $TOTAL_AUD_QT),4);
			$AUDIT_COUNT_DIFF	= true;
		}

		if(!empty($Details))
		{
			foreach($Details as $key => $value)
			{
				$totalProductPrice 	= 0;
				$totalProductQty 	= 0;
				$MRF_ID  			= (isset($value->BatchData->master_dept_id)) ? $value->BatchData->master_dept_id : 0;
				$PRODUCT_ID 		= (isset($value->product_id)) ? $value->product_id : 0;
				$productPriceData   = AppointmentCollectionDetail::select(	
					\DB::raw("sum(product_customer_price * actual_coll_quantity) as total_value"),
					\DB::raw("sum(actual_coll_quantity) as actual_coll_quantity"))
				->where("product_id",$PRODUCT_ID)
				->whereIn("collection_id",$collection_id)
				->groupBy("product_id")
				->get()
				->toArray();
				if(!empty($productPriceData)) {
					foreach($productPriceData as $data)
					{
						$PRODUCT_QTY 		= WmBatchAuditedProduct::where("id",$value->id)->sum('qty');
						$PRODUCT_QTY 		= ($PRODUCT_QTY > 0) ? _FormatNumberV2($PRODUCT_QTY) : 0 ; 
						$totalProductQty 	+= _FormatNumberV2($PRODUCT_QTY);
						$totalProductPrice 	+= _FormatNumberV2($data["total_value"]);
					}
				}
				$WRONG_PRODUCT_RECORD_ID  	= array(); 
				$WRONG_PRODUCT_DATA 		= WmBatchProductDetail::where("wrong_product_id",$value->product_id)->where("batch_id",$batch_id)->get();
				if(!empty($WRONG_PRODUCT_DATA)) {
					foreach($WRONG_PRODUCT_DATA as $data) {
						$AUDITED_QTY = WmBatchAuditedProduct::where("id",$data->id)->sum('qty');
						if($AUDITED_QTY > 0) {
							$totalProductQty += _FormatNumberV2($AUDITED_QTY);
							array_push($WRONG_PRODUCT_RECORD_ID,$data->id);
						}
					}
				}
				$AvgPrice = (!empty($totalProductPrice) && !empty($totalProductQty)) ? $totalProductPrice / $totalProductQty : 0;
				$AvgPrice = ($AUDIT_COUNT_DIFF)?$AVERAGE_PRICE:$AvgPrice;
				WmBatchProductDetailsProcessAvgPrice::AddProductData($batch_id,$value->id,$PRODUCT_ID,$AvgPrice);
				if(!empty($WRONG_PRODUCT_RECORD_ID)) {
					$GET_WRONG_PRODUCT_DATA = WmBatchProductDetail::whereIn("id",$WRONG_PRODUCT_RECORD_ID)->get();
					if(!empty($GET_WRONG_PRODUCT_DATA)) {
						foreach($GET_WRONG_PRODUCT_DATA AS $WRONG_DATA_VALUE) {
							WmBatchProductDetailsProcessAvgPrice::AddProductData($batch_id,$WRONG_DATA_VALUE->id,$WRONG_DATA_VALUE->product_id,$AvgPrice);
						}
					}
				}
			}
		}
	}
	/*
	Use     : GET BATCH PRODUCT DETAILS DATA
	Author  : Axay Shah
	Date    : 24 JAN,2022
	*/
	public static function updateAuditQty($product_details_id=0,$qty=0)
	{
		$res 			= false;
		$bag 			= 0;
		$created_by 	= 0;
		$data 			= self::where("id",$product_details_id)->first();
		if($data){
			$created_by = $data->created_by;
			$bag 		= $data->no_of_bag;
			$v 			= self::where("id",$product_details_id)->delete();
			$res 		= self::insert(array(
								"qty" 			=> $qty,
								"id" 			=> $product_details_id,
								"created_date"	=> $created_by,
								"no_of_bag" 	=> $bag
							));
		}
		return $res;
	}
}
