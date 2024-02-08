<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Support\Facades\Http;
class WmDispatchSalesProductAvgPrice extends Model implements Auditable
{
    protected 	$table 		=	'wm_dispatch_sales_product_avg_price';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;

	// public function AddDispatchAvgPrice($DISPATCH_ID,$SALES_PRODUCT_ID,$AVG_PRICE=0,$PURCHASE_PRODUCT_ID=0,$PURCHASE_PRICE=0,$COLLECTION_ID=0,$COLLECTION_DET_ID=0){
	// 	self::insert([
	// 		"dispatch_id" 			=> $DISPATCH_ID,
	// 		"collection_id" 		=> $COLLECTION_ID,
	// 		"collection_detail_id" 	=> $COLLECTION_DET_ID,
	// 		"purchase_product_id" 	=> $PURCHASE_PRODUCT_ID,
	// 		"sales_product_id" 		=> $SALES_PRODUCT_ID,
	// 		"price" 				=> $PURCHASE_PRICE,
	// 		"avg_price" 			=> $AVG_PRICE,
	// 		"created_at" 			=> date("Y-m-d"),
	// 		"updated_at" 			=> date("Y-m-d"),

	// 	]);
	// }
}
