<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\StockLadger;
use App\Facades\LiveServices;
class ViewCompanyProductMaster extends Model
{
	//
	 //
	protected 	$table 		=	'view_company_product_master';
	protected 	$guarded 	=	['id'];
	protected 	$primaryKey =	'id'; // or null
	public 		$timestamps = 	true;
	protected $casts = [
	'product_id'    => 'integer',
	'id'            => 'integer',
];
	/*
	use     : category wise product
	Author  : Axay Shah
	Date    : 17 Dec,2018
	*/
	public static function categoryWiseProduct(){
		return CompanyCategoryMaster::with(['productList' =>function($e){
			$e->where('para_status_id',PRODUCT_STATUS_ACTIVE);
		}])
		->where('company_id',Auth()->user()->company_id)
		->where('status','Active')
		->get();
	}

	/*
	use     : category wise product
	Author  : Axay Shah
	Date    : 17 Dec,2018
	*/
	public static function customerProduct(){
		return CompanyCategoryMaster::with(['productList' =>function($e){
			$e->where('para_status_id',PRODUCT_STATUS_ACTIVE);
		}])
		->where('company_id',Auth()->user()->company_id)
		->where('status','Active')
		->get();
	}

	/*
	use     : List company Product
	Author  : Axay Shah
	Date    : 13 Dec,2018
	*/
	public static function companyProduct($column = "*"){

		return self::select("*")
		->where('company_id',Auth()->user()->company_id)
		->where('para_status_id',PRODUCT_STATUS_ACTIVE)
		->get();
	}
	public static function getCompanyProductOnCustomer($request){
		$sortable       =  (isset($request->sortable) && !empty($request->sortable)) ? 1 : 0;
		$MRF_ID         =  (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$date           =  (isset($request->production_report_date) && !empty($request->production_report_date)) ? date("Y-m-d",strtotime($request->production_report_date)) : "";
		$ProductMaster  =  new CompanyProductMaster();
		$self           =  $ProductMaster->getTable();
		$ProductQuality =  new CompanyProductQualityParameter();
		$CategoryMaster =  new CompanyCategoryMaster();
		$Parameter      =  new Parameter();
		$data           =   CompanyProductMaster::select("$self.id as product_id",
							"$self.company_id",
							"$self.city_id",
							DB::raw("CONCAT($self.name,' ',QAL.parameter_name) AS product_name"),
							DB::raw("enurt as product_inert"),
							"$self.price",
							"$self.igst",
							"$self.cgst",
							"$self.sgst",
							"$self.factory_price",
							"$self.para_status_id",
							"$self.para_unit_id",
							"$self.category_id",
							"CAT.category_name",
							"CAT.color_code",
							"$self.para_group_id",
							'QAL.company_product_quality_id',
							'PARA.para_value as product_group',
							"$self.sortable",
							"$self.created_at",
							"$self.updated_at")
		->join($CategoryMaster->getTable()." as CAT","$self.category_id","=","CAT.id")
		->join($ProductQuality->getTable()." as QAL","$self.id","=","QAL.product_id")
		->leftjoin($Parameter->getTable()." as PARA","$self.para_group_id","=","PARA.para_id")
		->where("$self.company_id",Auth()->user()->company_id)
		->where("$self.para_status_id",PRODUCT_STATUS_ACTIVE);
		if($sortable > 0) {
			$data->where("$self.sortable",$sortable);
		}
		$data->orderBy("product_name")->orderBy('CAT.category_name');

		$result = $data->get()->toArray();
		if(!empty($result)) {
			$TodayDate                   = date("Y-m-d");
			foreach($result as $key => $value) {
				$TOTAL_CURRENT_STOCK        = 0;
				$PRODUCT_ID             = $value['product_id'];
				if(!empty($date)){
					$TOTAL_CURRENT_STOCK    = StockLadger::GetPurchaseProductCurrentStock($TodayDate,$value['product_id'],$MRF_ID);
				}
				$AVG_PRICE = StockLadger::where("mrf_id",$MRF_ID)->where("stock_date",date("Y-m-d"))->where("product_type",PRODUCT_PURCHASE)->where("product_id",$PRODUCT_ID)->value("avg_price");
				$result[$key]['avg_price']      = (!empty($AVG_PRICE)) ? _FormatNumberV2($AVG_PRICE) : 0;
				$result[$key]['current_stock']  = number_format($TOTAL_CURRENT_STOCK,2); //CHANGED BY KP
			}
		}
		return $result;
	}

	public static function AutoCompleteProduct($request){
		$sortable       =  (isset($request->sortable) && !empty($request->sortable)) ? 1 : 0;
		$MRF_ID         =  (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$date           =  (isset($request->production_report_date) && !empty($request->production_report_date)) ? date("Y-m-d",strtotime($request->production_report_date)) : "";
		$keyword        =  (isset($request->keyword) && !empty($request->keyword)) ? $request->keyword : "";
		$ProductMaster  =  new CompanyProductMaster();
		$self           =  $ProductMaster->getTable();
		$ProductQuality =  new CompanyProductQualityParameter();
		$CategoryMaster =  new CompanyCategoryMaster();
		$Parameter      =  new Parameter();
		if(!empty($keyword)){
			$data   =   CompanyProductMaster::select("$self.id as product_id",
							"$self.company_id",
							"$self.city_id",
							DB::raw("CONCAT($self.name,' ',QAL.parameter_name) AS product_name"),
							DB::raw("enurt as product_inert"),
							"$self.price",
							"$self.factory_price",
							"$self.para_status_id",
							"$self.para_unit_id",
							"$self.category_id",
							"CAT.category_name",
							"CAT.color_code",
							"$self.para_group_id",
							'QAL.company_product_quality_id',
							'PARA.para_value as product_group',
							"$self.sortable",
							"$self.created_at",
							"$self.updated_at")
			->join($CategoryMaster->getTable()." as CAT","$self.category_id","=","CAT.id")
			->join($ProductQuality->getTable()." as QAL","$self.id","=","QAL.product_id")
			->leftjoin($Parameter->getTable()." as PARA","$self.para_group_id","=","PARA.para_id")
			->where("$self.name","like","%".$keyword."%")
			->orWhere("QAL.parameter_name","like","%".$keyword."%")
			->where("$self.company_id",Auth()->user()->company_id)
			->where("$self.para_status_id",PRODUCT_STATUS_ACTIVE);
			if($sortable > 0) {
				$data->where("$self.sortable",$sortable);
			}
			$data->orderBy("product_name")->orderBy('CAT.category_name');
			$result = $data->get()->toArray();
			if(!empty($result)) {
				$TodayDate                   = date("Y-m-d");
				foreach($result as $key => $value) {
					$TOTAL_CURRENT_STOCK        = 0;
					if(!empty($date)){
						if($date == $TodayDate){
							$TOTAL_CURRENT_STOCK    = StockLadger::GetPurchaseProductCurrentStock($date,$value['product_id'],$MRF_ID);
						}else{
							$TOTAL_CURRENT_STOCK    = StockLadger::where("stock_date",$date)->where("product_type",PRODUCT_PURCHASE)->Where("product_id",$value['product_id'])->where("mrf_id",$MRF_ID)->value('closing_stock');
						}
					}
					$result[$key]['current_stock']  = $TOTAL_CURRENT_STOCK; //CHANGED BY KP
				}
			}
		}
		return $result;
	}

	/*
	Use     : Autocomplate Production Purchase product List
	Author  : Hasmukhi Patel
	Date    : 28 June,2021
	*/
	public static function AutoCompleteProductionPurchaseProduct($request){
		$sortable       =  (isset($request->sortable) && !empty($request->sortable)) ? 1 : 0;
		$MRF_ID         =  (isset($request->mrf_id) && !empty($request->mrf_id)) ? $request->mrf_id : 0;
		$date           =  (isset($request->production_report_date) && !empty($request->production_report_date)) ? date("Y-m-d",strtotime($request->production_report_date)) : "";
		$keyword        =  (isset($request->keyword) && !empty($request->keyword)) ? $request->keyword : "";
		$ProductMaster  =  new CompanyProductMaster();
		$self           =  $ProductMaster->getTable();
		$ProductQuality =  new CompanyProductQualityParameter();
		$CategoryMaster =  new CompanyCategoryMaster();
		$Parameter      =  new Parameter();
		$ProductionMaster= new WmProductionReportMaster();
		if(!empty($keyword)){
			$data   =   CompanyProductMaster::select("$self.id as product_id",
							"$self.company_id",
							"$self.city_id",
							DB::raw("CONCAT($self.name,' ',QAL.parameter_name) AS product_name"),
							DB::raw("enurt as product_inert"),
							"$self.price",
							"$self.factory_price",
							"$self.para_status_id",
							"$self.para_unit_id",
							"$self.category_id",
							"CAT.category_name",
							"CAT.color_code",
							"$self.para_group_id",
							'QAL.company_product_quality_id',
							'PARA.para_value as product_group',
							"$self.sortable",
							"$self.created_at",
							"$self.updated_at")
			->join($CategoryMaster->getTable()." as CAT","$self.category_id","=","CAT.id")
			->join($ProductQuality->getTable()." as QAL","$self.id","=","QAL.product_id")
			->join($ProductionMaster->getTable()." as WPRM","$self.id","=","WPRM.product_id")
			->leftjoin($Parameter->getTable()." as PARA","$self.para_group_id","=","PARA.para_id")
			->where("$self.name","like","%".$keyword."%")
			->orWhere("QAL.parameter_name","like","%".$keyword."%")
			->where("$self.company_id",Auth()->user()->company_id)
			->where("$self.para_status_id",PRODUCT_STATUS_ACTIVE);
			if($sortable > 0) {
				$data->where("$self.sortable",$sortable);
			}
			$data->orderBy("product_name")->orderBy('CAT.category_name')->groupBy('WPRM.product_id');
			$result = $data->get()->toArray();
			if(!empty($result)) {
				$TodayDate                   = date("Y-m-d");
				foreach($result as $key => $value) {
					$TOTAL_CURRENT_STOCK        = 0;
					if(!empty($date)){
						if($date == $TodayDate){
							$TOTAL_CURRENT_STOCK    = StockLadger::GetPurchaseProductCurrentStock($date,$value['product_id'],$MRF_ID);
						}else{
							$TOTAL_CURRENT_STOCK    = StockLadger::where("stock_date",$date)->where("product_type",PRODUCT_PURCHASE)->Where("product_id",$value['product_id'])->where("mrf_id",$MRF_ID)->value('closing_stock');
						}
					}
					$result[$key]['current_stock']  = number_format($TOTAL_CURRENT_STOCK,2); //CHANGED BY KP
				}
			}
		}
		return $result;
	}
}