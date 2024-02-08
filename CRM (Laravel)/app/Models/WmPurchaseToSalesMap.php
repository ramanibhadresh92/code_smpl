<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WmProductMaster;
class WmPurchaseToSalesMap extends Model
{
    protected 	$table 		=	'wm_purchase_to_sales_map';
	protected 	$primaryKey =	NULL; // or null
	protected 	$fillable 	=	['sales_product_id','purchase_product_id','purchase_product_quality_id','is_default'];
	public 		$timestamps = 	false;
	protected $casts = [
		"sales_product_id" => "integer",
		"purchase_product_id" => "integer",
		"purchase_product_quality_id" => "integer",
	];
	public function InverseSalesProduct(){
		return $this->belongsTo(WmProductMaster::class,'sales_product_id','id');
    }

	/*
	Use 	: Get Sale product list which is mapped with purchase product list
	Author 	: Axay Shah
	Date 	: 30 May,2019
	*/
	public static function GetSaleProductByPurchaseProduct($purchaseProduct = 0){
		if(!is_array($purchaseProduct)){
			$purchaseProduct = explode(",",$purchaseProduct);
		}

		$ProductMaster 	= new WmProductMaster();
		$Product 		= $ProductMaster->getTable();
		$mapping 		= (new static)->getTable();
		$result 		= array();
		$data 			= self::whereIn("purchase_product_id",$purchaseProduct)->get();
		$productArray 	= array();
		foreach($data as $value){
			$result[$value->purchase_product_id][] = $value->InverseSalesProduct;
		}
		return $result;
	}

	/*
	Use 	: Purchase to Sales Mapping of product
	Author 	: Axay Shah
	Date 	: 12 Aug,2019
	*/
	public static function InsertPurchaseToSalesProduct($request){
		$purchaseId 	=  (isset($request->purchase_product_id) && !empty($request->purchase_product_id)) ? $request->purchase_product_id : 0;
		$salesIds 		=  (isset($request->sales_product_id) && !empty($request->sales_product_id)) ? $request->sales_product_id : "";
		$defaultSalesId =  (isset($request->default_sales_id) && !empty($request->default_sales_id)) ? $request->default_sales_id : 0;
		$delete = self::where("purchase_product_id",$purchaseId)->delete();
		if(!empty($purchaseId) && !empty($salesIds) && !empty($defaultSalesId)){
			// self::where("purchase_product_id",$purchaseId)->delete();
			foreach($salesIds as $sales){
				$is_default 	= 0;
				if($sales == $defaultSalesId){
					$is_default = 1;
				}
				self::insert(["purchase_product_id" => $purchaseId,"sales_product_id"=>$sales,"is_default"=>$is_default]);
				LR_Modules_Log_CompanyUserActionLog($request,$purchaseId);
			}
			return true;
		}
		return false;
	}

	/*
	Use 	: Purchase to Sales Mapping of product by id
	Author 	: Axay Shah
	Date 	: 14 Aug,2019
	*/
	public static function PurchaseToSalesMappingById($id = 0){
		$result = array(); 
		$data 	= self::where("purchase_product_id",$id)->pluck("sales_product_id");
		$result['purchase_product_id']	 = $id;
		$result['sales_product'] 		 = $data;
		$result['default_sales_product'] = self::where("purchase_product_id",$id)->where("is_default","1")->value("sales_product_id") ;

		return $result;
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
