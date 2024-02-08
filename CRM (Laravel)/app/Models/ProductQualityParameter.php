<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductMaster;
class ProductQualityParameter extends Model
{
    //
    protected 	$table 		=	'product_quality_parameter';
    protected 	$guarded 	=	['product_quality_para_id'];
    protected 	$primaryKey =	'product_quality_para_id'; // or null
    public 		$timestamps = 	true;

	/*
		USE 	:  	Add product quality peremeter record
		Author 	:  	Axay Shah
		Date 	:	20-09-2018
    */
    public static function add($request,$productId){
    	$PQP 					= new ProductQualityParameter();
        $PQP->product_id       	= $productId;
        $PQP->parameter_name   	= $request->parameter_name;
        $PQP->created_by       	= Auth()->user()->adminuserid;
        $PQP->para_rate_in     	= (isset($request->para_rate_in) && !empty($request->para_rate_in)) ? $request->para_rate_in : PARA_RATE_IN_DEFAULT;
        $PQP->save();
        return $PQP;
    }

    public static function updateQualityByProduct($request,$productId){
    	$PQP =	self::where('product_id',$productId)->first();
    	if($PQP){
    		$PQP->product_id       	= 	$productId;
        	$PQP->parameter_name   	= 	$request->parameter_name;
        	$PQP->updated_by       	= 	Auth()->user()->adminuserid;
        	$PQP->para_rate_in     	= 	(isset($request->para_rate_in) && !empty($request->para_rate_in)) ? $request->para_rate_in : PARA_RATE_IN_DEFAULT;
        	$PQP->save();
        }
        return $PQP;
    }

}
