<?php

namespace App\Models;

use App\Facades\LiveServices;
use Illuminate\Database\Eloquent\Model;
use Validator;
use DB;
use Log;
use App\Models\WmClientMaster;
use App\Models\LocationMaster;
use App\Models\WmProductMaster;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class WmProductClientPriceMaster extends Model implements Auditable
{
	use AuditableTrait;
	protected 	$table 		=	'wm_product_client_price_master';
	protected 	$guarded 	=	['id'];
	protected 	$primaryKey =	'id'; // or null
	public 		$timestamps = 	true;

	/*
	Use 	: List Department
	Author 	: Axay Shah
	Date 	: 03 June,2019
	*/
	public static function ListProductClientPrice($request,$isPainate = true)
	{
		try {
				$self 			= (new static)->getTable();
				$Location 		= new LocationMaster();
				$Client 		= new WmClientMaster();
				$Product 		= new WmProductMaster();
				$Today      	= date('Y-m-d');
				$cityId     	= GetBaseLocationCity();
				$sortBy     	= ($request->has('sortBy') && !empty($request->input('sortBy'))) ? $request->input('sortBy') : "id";
				$sortOrder  	= ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
				$recordPerPage 	= !empty($request->input('size')) ? $request->input('size') : DEFAULT_SIZE;
				$pageNumber 	= !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
				$createdAt 		= ($request->has('params.created_from') && $request->input('params.created_from')) ? date("Y-m-d",strtotime($request->input("params.created_from"))) : "";
				$createdTo 		= ($request->has('params.created_to') && $request->input('params.created_to')) ? date("Y-m-d",strtotime($request->input("params.created_to"))) : "";
				$data 			= self::select("$self.*","C.client_name","L.city","P.title","P.net_suit_code")
									->join($Client->getTable()."  AS C","$self.client_id","=","C.id")
									->join($Product->getTable()."  AS P","$self.product_id","=","P.id")
									->leftjoin($Location->getTable()."  AS L","C.city_id","=","L.location_id")
									->where("$self.company_id",Auth()->user()->company_id);
				if($request->has('params.city_id') && !empty($request->input('params.city_id'))) {
					$data->whereIn("C.city_id", explode(",",$request->input('params.city_id')));
				} else {
					$data->whereIn("C.city_id",$cityId);
				}
				if($request->has('params.client_id') && !empty($request->input('params.client_id'))) {
					$data->where("$self.client_id", $request->input('params.client_id'));
				}
				if($request->has('params.product_id') && !empty($request->input('params.product_id'))) {
					$data->where("$self.product_id", $request->input('params.product_id'));
				}
				if(!empty($createdAt) && !empty($createdTo)) {
					$data->whereBetween("$self.rate_date",[$createdAt,$createdTo]);
				} elseif(!empty($createdAt)) {
					$data->whereBetween("$self.rate_date",[$createdAt,$createdAt]);
				} elseif(!empty($createdTo)) {
					$data->whereBetween("$self.rate_date",[$createdTo,$createdTo]);
				}
				if($isPainate == true) {
					$result =  $data->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
				} else {
					$result = $data->get();
				}
				return $result;
		} catch(\Exception $e) {

		}
	}

	/*
	Use 	: Add Product Price with client
	Author 	: Axay Shah
	Date 	: 19 June,2019
	*/
	public static function AddClientProductPrice($request)
	{
		$id 			= 0;
		$ProductList 	= (isset($request->product_list) && !empty($request->product_list)) ? $request->product_list : "";
		$clientID 		= (isset($request->client_id) && !empty($request->client_id)) ? $request->client_id : " ";
		$RateDate 		= (isset($request->rate_date) && !empty($request->rate_date)) ? date("Y-m-d",strtotime($request->rate_date)) : date("Y-m-d");
		$CityId 		= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
		if(!empty($ProductList)){
			foreach($ProductList as $raw){
				$Add 				= new self();
				$Add->product_id 	= (isset($raw['product_id']) && !empty($raw['product_id'])) ? $raw['product_id'] : 0;
				$Add->client_id 	= $clientID;
				$Add->rate 			= (isset($raw['rate']) && !empty($raw['rate'])) ? $raw['rate'] : 0;
				$Add->rate_date 	= $RateDate;
				$Add->city_id		= $CityId;
				$Add->created_by 	= Auth()->user()->adminuserid;
				$Add->company_id 	= Auth()->user()->company_id;
				if($Add->save()) {
					$id = $Add->id;
					LR_Modules_Log_CompanyUserActionLog($request,$id);
				}
			}
		}
	}

	/*
	Use 	: Update Product Price with client
	Author 	: Axay Shah
	Date 	: 19 June,2019
	*/
	public static function UpdateClientProductPrice($request)
	{
		$id = (isset($request->id) && !empty($request->id)) ? $request->id : 0;
		$Add 				= self::find($id);
		$Add->product_id 	= (isset($request->product_id) && !empty($request->product_id)) ? $request->product_id : 0;
		$Add->client_id		= (isset($request->client_id) && !empty($request->client_id)) ? $request->client_id : " ";
		$Add->rate			= (isset($request->rate) && !empty($request->rate)) ? $request->rate : 0;
		$Add->rate_date		= (isset($request->rate_date) && !empty($request->rate_date)) ? date("Y-m-d",strtotime($request->rate_date)) : date("Y-m-d");
		$Add->city_id		= (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
		$Add->created_by 	= Auth()->user()->adminuserid;
		$Add->company_id 	= Auth()->user()->company_id;
		$Add->save();
		LR_Modules_Log_CompanyUserActionLog($request,$id);
		return $id;
	}

	/*
	Use 	: Get By Id
	Author 	: Axay Shah
	Date 	: 19 June,2019
	*/
	public static function GetClientPriceById($id = 0)
	{
		$data 	= self::find($id);
		return $data;
	}



	/*
	Use 	: getMaxProductPrice
	Author 	: Kalpak Prajapati
	Date 	: 08 July,2020
	*/
	public function getMaxProductPrice($product_id=0,$company_id=0)
	{
		$MAX_PRICE	= 0;
		$self 		= (new static)->getTable();
		$Location 	= new LocationMaster();
		$Client 	= new WmClientMaster();
		$Product 	= new WmProductMaster();
		$Last6Month	= date("Y-m-d",strtotime("-6 Months"))." 00:00:00";
		$SelectSql 	= "	(
							SELECT wm_dispatch_product.price as max_rate
							FROM wm_dispatch_product
							INNER JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
							INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
							INNER JOIN location_master ON location_master.location_id = wm_client_master.city_id
							WHERE wm_dispatch_product.product_id = ".$product_id."
							AND wm_dispatch.company_id = ".$company_id."
							AND wm_dispatch.approval_status = 1
							AND wm_dispatch_product.price > 0
							AND wm_dispatch.dispatch_date >= '".$Last6Month."'
							ORDER BY wm_dispatch_product.price DESC
							LIMIT 1
						)
						UNION ALL
						(
							SELECT wm_product_client_price_master.rate as max_rate
							FROM wm_product_client_price_master
							INNER JOIN wm_client_master ON wm_client_master.id = wm_product_client_price_master.client_id
							INNER JOIN location_master ON location_master.location_id = wm_client_master.city_id
							WHERE wm_product_client_price_master.product_id = ".$product_id."
							AND wm_product_client_price_master.company_id = ".$company_id."
							AND wm_product_client_price_master.rate > 0
							AND wm_product_client_price_master.rate_date >= '".$Last6Month."'
							ORDER BY wm_product_client_price_master.rate DESC
							LIMIT 3
						)
						ORDER BY max_rate DESC
						LIMIT 1";
		$arrResult = DB::select($SelectSql);
		if (!empty($arrResult)) {
			foreach ($arrResult as $ResultRow) {
				$MAX_PRICE = _FormatNumberV2($ResultRow->max_rate);
			}
		}
		return $MAX_PRICE;
	}

	/*
	Use 	: getMaxProductPriceTrend
	Author 	: Kalpak Prajapati
	Date 	: 08 July,2020
	*/
	public function getMaxProductPriceTrend($product_id=0,$company_id=0)
	{
		$arrResult	= array();
		$self 		= (new static)->getTable();
		$Location 	= new LocationMaster();
		$Client 	= new WmClientMaster();
		$Product 	= new WmProductMaster();
		$Last6Month	= date("Y-m-d",strtotime("-6 Months"))." 00:00:00";
		$SelectSql 	= "	(
							SELECT wm_client_master.client_name as client_name,
							location_master.city as city,
							wm_dispatch_product.price as rate,
							wm_dispatch_product.price as rate_ori,
							DATE_FORMAT(wm_dispatch.dispatch_date,'%Y-%m-%d') as rate_date
							FROM wm_dispatch_product
							INNER JOIN wm_dispatch ON wm_dispatch.id = wm_dispatch_product.dispatch_id
							INNER JOIN wm_client_master ON wm_client_master.id = wm_dispatch.client_master_id
							INNER JOIN location_master ON location_master.location_id = wm_client_master.city_id
							WHERE wm_dispatch_product.product_id = ".$product_id."
							AND wm_dispatch.company_id = ".$company_id."
							AND wm_dispatch.approval_status = 1
							AND wm_dispatch.dispatch_date >= '".$Last6Month."'
							ORDER BY wm_dispatch_product.price DESC
							LIMIT 3
						)
						UNION ALL
						(
							SELECT wm_client_master.client_name as client_name,
							location_master.city as city,
							CONCAT(wm_product_client_price_master.rate,' (Q)') as rate,
							wm_product_client_price_master.rate as rate_ori,
							DATE_FORMAT(wm_product_client_price_master.rate_date,'%Y-%m-%d') as rate_date
							FROM wm_product_client_price_master
							INNER JOIN wm_client_master ON wm_client_master.id = wm_product_client_price_master.client_id
							INNER JOIN location_master ON location_master.location_id = wm_client_master.city_id
							WHERE wm_product_client_price_master.product_id = ".$product_id."
							AND wm_product_client_price_master.company_id = ".$company_id."
							AND wm_product_client_price_master.rate_date >= '".$Last6Month."'
							ORDER BY wm_product_client_price_master.rate DESC
							LIMIT 3
						)
						ORDER BY rate_ori DESC, rate_date DESC
						LIMIT 3";
		$arrResult = DB::select($SelectSql);
		if (!empty($arrResult)) {
			foreach ($arrResult as $ResultRow) {
				$ResultRow->rate = _FormatNumberV2($ResultRow->rate);
			}
		}
		return $arrResult;
	}

	/*
	Use 	: Save Product Price For client From Dispatch
	Author 	: Kalpak Prajapati
	Date 	: 07 July,2020
	*/
	public static function UpdateNewProductPriceTrend($arrFields=array())
	{
		if (empty($arrFields)) return true;
		$Add 						= new self();
		$Add->product_id 			= (isset($arrFields['product_id']) && !empty($arrFields['product_id'])) ? $arrFields['product_id'] : 0;
		$Add->client_id 			= (isset($arrFields['client_id']) && !empty($arrFields['client_id'])) ? $arrFields['client_id'] : 0;
		$Add->rate 					= (isset($arrFields['rate']) && !empty($arrFields['rate'])) ? $arrFields['rate'] : 0;
		$Add->rate_date 			= (isset($arrFields['rate_date']) && !empty($arrFields['rate_date'])) ? $arrFields['rate_date'] : 0;
		$Add->city_id 				= (isset($arrFields['city_id']) && !empty($arrFields['city_id'])) ? $arrFields['city_id'] : 0;
		$Add->company_id 			= (isset($arrFields['company_id']) && !empty($arrFields['company_id'])) ? $arrFields['company_id'] : 0;
		$Add->from_dispatch 		= (isset($arrFields['from_dispatch']) && !empty($arrFields['from_dispatch'])) ? $arrFields['from_dispatch'] : 0;
		$Add->dispatch_plan_id 		= (isset($arrFields['dispatch_plan_id']) && !empty($arrFields['dispatch_plan_id'])) ? $arrFields['dispatch_plan_id'] : 0;
		$Add->rate_change_remark 	= (isset($arrFields['rate_change_remark']) && !empty($arrFields['rate_change_remark'])) ? $arrFields['rate_change_remark'] : "";
		$Add->created_by 			= (isset($arrFields['created_by']) && !empty($arrFields['created_by'])) ? $arrFields['created_by'] : 0;
		$Add->updated_by 			= (isset($arrFields['updated_by']) && !empty($arrFields['updated_by'])) ? $arrFields['updated_by'] : 0;
		$Add->remark_id 			= (isset($arrFields['remark_id']) && !empty($arrFields['remark_id'])) ? $arrFields['remark_id'] : 0;
		if($Add->save()) {
			return true;
		} else {
			return false;
		}
	}
}