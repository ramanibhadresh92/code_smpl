<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmSaleableProductTagging extends Model
{
	protected 	$table 		= 'wm_product_saleable_tagging';
	protected 	$primaryKey = 'id';
	protected 	$guarded 	= ['id'];
	public 		$timestamps = false;

	public static function UpdateProductTagging($mrf_id,$arrProducts,$USERID)
	{
		if (!empty($arrProducts)) {
			$arrProducts = explode(",",$arrProducts);
			self::where("mrf_id",$mrf_id)->delete();
			foreach($arrProducts as $PRODUCT_ID) {
				$NewRecord 				= new self;
				$NewRecord->mrf_id 		= $mrf_id;
				$NewRecord->product_id 	= $PRODUCT_ID;
				$NewRecord->created_by 	= $USERID;
				$NewRecord->updated_by 	= $USERID;
				$NewRecord->save();
			}
		}
	}

	public static function GetMRFSaleableProducts($mrf_id)
	{
		$arrReturn 	= self::where("mrf_id",$mrf_id)->pluck("product_id")->implode(',');
		return $arrReturn;
	}
}