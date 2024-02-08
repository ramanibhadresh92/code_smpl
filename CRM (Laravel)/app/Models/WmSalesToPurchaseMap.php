<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CompanyProductQualityParameter;
class WmSalesToPurchaseMap extends Model
{
    protected 	$table 		=	'wm_sales_to_purchase_map';
	protected 	$primaryKey =	NULL; // or null
	protected 	$fillable 	=	['sales_product_id','purchase_product_id','purchase_product_quality_id','is_default'];
	public 		$timestamps = 	false;
	protected $casts 		= [
		"purchase_product_id" 			=> "integer",
		"purchase_product_quality_id" 	=> "integer",
		"sales_product_id" 				=> "integer"
	];
	/*
	Use 	: Sales to purchase Mapping of product
	Author 	: Axay Shah
	Date 	: 13 Aug,2019
	*/
	public static function InsertSalesToPurchaseProduct($request){
		try{
			$purchaseId 			=  (isset($request->purchase_product_id) && !empty($request->purchase_product_id)) ? $request->purchase_product_id : 0;
			$salesIds 				=  (isset($request->sales_product_id) && !empty($request->sales_product_id)) ? $request->sales_product_id : "";
			$defaultPurchaseId 		=  (isset($request->default_purchase_id) && !empty($request->default_purchase_id)) ? $request->default_purchase_id : 0;
			if(!empty($salesIds) && !empty($defaultPurchaseId)){
				self::where("sales_product_id",$salesIds)->delete();
				$qualityId = CompanyProductQualityParameter::where("product_id",$defaultPurchaseId)->value("company_product_quality_id");

				($qualityId > 0) ? $qualityId : $qualityId = 0;
				$is_default 	= 1;
				$productId 		= $defaultPurchaseId ;
				self::insert(["purchase_product_id" => $productId,"purchase_product_quality_id"=>$qualityId,"sales_product_id"=>$salesIds,"is_default"=>$is_default]);
				LR_Modules_Log_CompanyUserActionLog($request,$salesIds);
				}
				return true;
		}catch(\Exception $e){
			dd($e);
			return false;
		}
	}

	/*
	Use 	: Sales to purchase Mapping of product by id
	Author 	: Axay Shah
	Date 	: 14 Aug,2019
	*/
	public static function SalesToPurchaseMappingById($id = 0){
		$result = array(); 
		$data = self::where("sales_product_id",$id)->first();
		return $data;
	}
	/*
	Use 	: Verify One 2 One Mapping of Purchase to Sales Mapping
	Author 	: Kalpak Prajapati
	Date 	: 14 July,2020
	*/
	public static function VerifyOne2OneMapping($purchase_product_id=0)
	{
		if (empty($purchase_product_id)) return false;
		$COUNT = self::select('id')->where("purchase_product_id",intval($purchase_product_id))->count();
		if ($COUNT > 0) {
			return false;
		} else {
			return true;
		}
	}

	/*
	Use 	: GetSingleMappedSalesProductID
	Author 	: Kalpak Prajapati
	Date 	: 14 July,2020
	*/
	public static function GetSingleMappedSalesProductID($purchase_product_id=0)
	{
		if (empty($purchase_product_id)) return false;
		$SalesProductID = self::where("purchase_product_id",intval($purchase_product_id))->value("sales_product_id");
		return $SalesProductID;
	}
}
