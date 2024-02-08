<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmProductProcess extends Model
{
    protected 	$table 		=	'wm_product_process';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public 		$timestamps = 	false;

    /*
	Use 	: Insert Product Process
	Author 	: Axay Shah
	Date 	: 19 Aug,2019
	*/
	public static function AddProductProcess($productId,$processId){
		return self::insert(["product_id"=>$productId,"process_type_id"=>$processId]);
	}
}
