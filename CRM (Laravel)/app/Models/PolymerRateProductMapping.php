<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;
use App\Facades\LiveServices;
class PolymerRateProductMapping extends Model
{
	//
	protected 	$table 		=	'polymer_rate_product_mapping';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	false;


	/*
	Use 	: Get Department
	Author 	: Axay Shah
	Date 	: 03 May 2019
	*/
	public static function StoreProductData($record_id,$product_id){
		$self = new self();
		$self->purchase_product_id 	= $product_id;
		$self->polymer_id 			= $record_id;
		$self->created_at 			= date("Y-m-d H:i:s");
		$self->save();
	}
	
	/*
	Use 	:
	Author 	: Axay Shah
	Date 	: 23 May 2022
	*/
	public static function StoreProductLogData($log_id,$id){
		$ProductLog = PolymerRateProductMapping::where("polymer_id",$id)->pluck('purchase_product_id')->toArray();
		if(!empty($ProductLog)){
			foreach ($ProductLog as $value) {
				$data = array(
					'log_id' 				=> $log_id,
					'purchase_product_id' 	=> $value,
					'polymer_id' 			=> $id,
					'created_at' 			=> date("Y-m-d H:i:s")
				);
				\DB::table("polymer_rate_product_mapping_log")->insert($data);
			}
		}
	}

}
