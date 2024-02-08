<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductPriceDetail;
use App\Models\Parameter;
class CompanyProductQualityParameter extends Model
{
	//
	protected 	$table 		=	'company_product_quality_parameter';
	protected 	$guarded 	=	['company_product_quality_id'];
	protected 	$primaryKey =	'company_product_quality_id'; // or null
	public 		$timestamps = 	true;

	public function qualityProduct(){
		return $this->belongsTo(CompanyProductMaster::class,'product_id');
	}


	/*
		USE 	:  	Add company product quality peremeter record
		Author 	:  	Axay Shah
		Date 	:	26-09-2018
	*/
	public static function add($request,$productId){
		$PQP 					= new self();
		$PQP->product_id       	= $productId;
		$PQP->parameter_name   	= $request->parameter_name;
		$PQP->created_by       	= Auth()->user()->adminuserid;
		$PQP->para_rate_in     	= (isset($request->para_rate_in) && !empty($request->para_rate_in)) ? $request->para_rate_in : PARA_RATE_IN_DEFAULT;
		$PQP->save();
		return $PQP;
	}
	/*
		USE 	:  	Update company product quality peremeter record
		Author 	:  	Axay Shah
		Date 	:	26-09-2018
	*/
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


	/*
	use     : get product Quality  By product or all product
	Author  : Axay Shah
	Date    : 20 Nov,2018
	*/
	public static function getProductQuality($productId = 0){
		
		$query =    CompanyProductMaster::with('productQuality')
								->where('company_id',Auth()->user()->company_id)
								->where('para_status_id',PRODUCT_STATUS_ACTIVE)
								->where('id',$productId)
								->get();
		
	 
		return $query;
	}
	/*
	use     : Get Product Quality by Id
	Author  : Axay Shah
	Date    : 28 Nov,2018
	*/
	public static function getById($qulId){
		$query = self::find($qulId);
		return $query;
	}

	/*
	Use     : retrieveProductParamListByFilter
	Author  : Axay Shah 
	Date    : 29 Sep,2018
	*/

	public static function retrieveProductParamListByFilter($arrFilter="",$orderby="company_product_quality_id",$order = "Asc"){
		$query = self::query();
		if(is_array($arrFilter)){
			$query->where($arrFilter);
		}
		$query->orderBy($orderby,$order);
		$results = $query->get();
		return $results;
	}

	/*
	Use     : Retrive Param from Dropdown of Collection Controller 
	Input   : @id : company_product_quality_id & collection_id & param = paramdtls
	Author  : Axay Shah 
	Date    : 29 Sep,2018
	*/
	public static function getParamDetail($request){
		$arrFilter  =  "";
		$data       = array();
		if((isset($request->id) && $request->id > 0)){
			$arrFilter = ["company_product_quality_id"=>$request->id];
			$productParam = self::retrieveProductParamListByFilter($arrFilter); 
			if(!$productParam->isEmpty()){
				foreach($productParam as $pro){
					$product 		= CompanyProductMaster::getById($pro->product_id);
					$Product_Price	= CompanyProductPriceDetail::retrieveProductPriceByCollectionID($request->collection_id,$pro->product_id);
					$Possible_Amount = 0;
					$isMinus 		 = false;
					if($Product_Price < 0 ) {
						$Product_Price 	= abs($Product_Price);
						$isMinus 		= true;
					}
					if ($Product_Price > 0) {
						if ($pro->para_rate_in == PARA_RATE_IN_PERCENTAGE) {
							$Possible_Amount = number_format(($Product_Price-(($Product_Price*$pro->para_rate)/ 100)),2);
						} else {
							$Possible_Amount = number_format($Product_Price-$pro->para_rate,2);
						}
					}
					if ($Possible_Amount == 0) $Possible_Amount = "0.00";
					if($isMinus) $Possible_Amount = -1 * $Possible_Amount;
					$UNIT_VALUE = Parameter::find($product->para_unit_id);
					$UNIT_NAME = "";
					if (isset($UNIT_VALUE->para_id)) {
						if ($Possible_Amount != 0) {
							$UNIT_NAME = $UNIT_VALUE->para_value;
						}
					}
					$Possible_Amount = (!empty($Possible_Amount)) ? str_replace(",","",$Possible_Amount) : 0;
					$data = array("rate"=>$Possible_Amount,"unit"=>$UNIT_NAME);
				}
		   }
		}
		return $data;
	}

	/*
	Use     : Get All product Qulaity list
	Author  : Axay Shah 
	Date    : 03 Dec,2018
	*/
	public static function getAllProductQuality(){
		$productMaster  = new CompanyProductMaster();
		$product        = $productMaster->getTable();
		$quality        = (new static)->getTable();
		$query          = self::join($product,"$quality.product_id","=","$product.id")
						->where("$product.company_id",Auth()->user()->company_id)
						->where("$product.para_status_id",PRODUCT_STATUS_ACTIVE)
						->select("$quality.*")
						->get();
		return $query;
	}

	/**
	 * Use      : validateCustomerProduct
	 * Author   : Sachin Patel
	 * Date     : 12 Feb, 2019
	*/
	public static function validateCustomerProduct($productArray){

		foreach ($productArray as $map_id) {
			$map_ids 	                = explode("#",$map_id);
			$company_product_quality_id	= isset($map_ids[2])?$map_ids[2]:0;
			$product_id					= isset($map_ids[1])?$map_ids[1]:0;
			$category_id				= isset($map_ids[0])?$map_ids[0]:0;
			if (count($map_ids) == 3) {
				$getProduct = ViewCompanyProductMaster::where('company_product_quality_id',$company_product_quality_id)->where('id',$product_id)->where('category_id',$category_id)->first();
				if(empty($getProduct)){
					return false;
				}
			}else{
				return false;
			}
		}
		return true;
	}

}