<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedeemProductOrder extends Model
{
    protected 	$table 		=	'redeem_product_order';
    protected 	$guarded 	=	['order_id'];
    public 		$timestamps = 	false;
    public      $primaryKey = 'order_id';
    
    /**
    * Function Name : updateProductOrderStatus
    * @return
    * @author Sachin Patel
    * @date 23 April, 2019
    */
    public static function updateProductOrderStatus($request){
    	$data = self::where('order_id',$request->clsredeemproduct_order_id)->update([
		    		'status' 		=> ORDER_STATUS_REJECT,
		    		'reject_reason' => isset($request->clsredeemproduct_reject_reason) ? $request->clsredeemproduct_reject_reason : "",
		    		'rejected_by'	=> ORDER_REJECTED_BY_CUSTOMER,
		    	]);
    	
    	return true;
    }
}
