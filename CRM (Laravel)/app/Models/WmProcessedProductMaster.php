<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Facades\LiveServices;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmProductMaster;

class WmProcessedProductMaster extends Model implements Auditable
{
	use AuditableTrait;
	protected 	$table 		= 'wm_processed_product_master';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = false;
	protected 	$casts 		= [];

	/*
		Use 	: Add Production Report
		Author 	: Axay Shah
		Date 	: 03 July 2020
	*/
	public static function AddProcessedProduct($id,$purchaseProductId,$salesProductID=0,$Qty=0,$KG_AVG_PRICE=0,$ORIGINAL_QTY=0)
	{
		$Add 					= self::firstOrNew(['production_id' => $id,"sales_product_id"=>$salesProductID]); // your data
		$Add->sales_product_id	= $salesProductID;
		$Add->qty				+= $Qty;
		$Add->production_id 	= $id;
		$Add->kg_avg_price 		= $KG_AVG_PRICE;
		$Add->original_qty 		= $ORIGINAL_QTY;
		if($Add->save()) {
			$array = array(	'sales_product_id' 					=>	$Add->sales_product_id,
							'purchase_product_id' 				=>	$purchaseProductId,
							'wm_production_report_id' 			=>	$id,
							'wm_processed_product_master_id' 	=>	$Add->id,
							'qty' 								=>	$Qty,
							'kg_avg_price' 						=>	$KG_AVG_PRICE,
							'original_qty' 						=>	$ORIGINAL_QTY
						);
			WmAutoTransferProductionReportSalesProduct::AddAutoProcessForProductionReport($array);
		}
	}

	/*
	Use 	: Get By Process ID
	Author 	: Axay Shah
	Date 	: 06 July,2020
	*/
	public static function GetByProductionId($ID)
	{
		$RESULT 		= array();
		$self 			= (new static)->getTable();
 		$salesProduct 	= new WmProductMaster();
 		$Mapping 		= new WmSalesToPurchaseSequence();
 		$MAP 			= $Mapping->getTable();
 		$SEQUENCE 		= array();
 		$PRODUCTION 	= WmProductionReportMaster::find($ID);
 		if($PRODUCTION){
 			if($PRODUCTION->finalize == 1){
 				$SEQUENCE 	= self::select("SPM.id as sales_product_id","SPM.title","$self.qty")
	 			->join($salesProduct->getTable()." as SPM","$self.sales_product_id","=","SPM.id")
	 			->where("$self.production_id",$PRODUCTION->id)
	 			->get()
	 			->toArray();
 			}else{
 				$SEQUENCE 	= WmSalesToPurchaseSequence::select("SPM.id as sales_product_id","SPM.title")
	 			->join($salesProduct->getTable()." as SPM","$MAP.sales_product_id","=","SPM.id")
	 			->where("$MAP.purchase_product_id",$PRODUCTION->product_id)
	 			->get()
	 			->toArray();
	 			if(!empty($PRODUCTION)){
	 				foreach($SEQUENCE AS $KEY => $VALUE){
		 				$qty 					= self::where("production_id",$ID)->where("sales_product_id",$VALUE['sales_product_id'])->value('qty');
		 				$SEQUENCE[$KEY]['qty'] 	= $qty;
	 				}	
	 			}
 			}
 		}
		return $SEQUENCE;
	}

}