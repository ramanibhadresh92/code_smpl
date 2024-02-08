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
class WmBatchProductDetailsProcessAvgPrice extends Model
{
    protected 	$table 		=	'wm_batch_product_details_process_avg_price';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public 		$timestamps = 	false;

    
    /*
    Use     : ADD PRODUCT DETAILS DATA WITH AVG PRICE
    Author  : AXAY SHAH
    Date    : 24 JAN,2022
    */
    public static function AddProductData($batch_id,$batch_product_detail_id,$product_id,$avg_price=0){

        $batch_data = self::where("product_id",$product_id)->where("batch_id",$batch_id)->first();
        if(!$batch_data){
            $add  = new self;
            $add->product_id                = $product_id;
            $add->batch_id                  = $batch_id;
            $add->batch_product_detail_id   = $batch_product_detail_id;
            $add->avg_price                 = $avg_price;
            $add->price                     = $avg_price;
            $add->created_at                = date("Y-m-d H:i:s");
            $add->company_id                = (isset(Auth()->user()->company_id)) ? Auth()->user()->company_id : 1;
            $add->save();
        }else{
            if($batch_data->process == 0){
                $batch_data->company_id     = (isset(Auth()->user()->company_id)) ? Auth()->user()->company_id : 1;
                $batch_data->avg_price      = $avg_price;
                $batch_data->save();
            }
        }
    }
}
