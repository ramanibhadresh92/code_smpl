<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CompanyProductQualityParameter;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use DB;
class WmSalesToPurchaseSequence extends Model implements Auditable
{
    protected 	$table 		=	'wm_sales_to_purchase_sequence';
	protected 	$primaryKey =	"id"; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	/*
		Use 	: Sales to purchase Mapping of product
		Author 	: Axay Shah
		Date 	: 29 June,2020
	*/
	public static function AddSalesToPurchaseProductSequence($request)
	{
		try{
			$purchaseProduct 	=  (isset($request->purchase_product) && !empty($request->purchase_product)) ? $request->purchase_product : "";
			$salesIds 			=  (isset($request->sales_product_id) && !empty($request->sales_product_id)) ? $request->sales_product_id : "";
			if(!empty($salesIds) && !empty($purchaseProduct))
			{

				/** ADD LOG TO KEEP PREVIOUS DATA */
				$INSERT_SQL = "INSERT INTO wm_sales_to_purchase_sequence_log select * from wm_sales_to_purchase_sequence where sales_product_id IN (".$salesIds.")";
				DB::statement($INSERT_SQL);
				/** ADD LOG TO KEEP PREVIOUS DATA */

				self::where("sales_product_id",$salesIds)->delete();
				foreach($purchaseProduct as $pro)
				{
					self::insert([	"sales_product_id" => $salesIds,
									"purchase_product_id"=>$pro['purchase_product_id'],
									"sequence_no"=>$pro['sequence_no'],
									"created_at"=>date("Y-m-d H:i:s"),
									"created_by"=>Auth()->user()->adminuserid,
									"updated_at"=>date("Y-m-d H:i:s"),
									"updated_by"=>Auth()->user()->adminuserid]);
				}
				return true;
			}
			return false;
		}catch(\Exception $e){

			prd($e->getMessage());
			return false;
		}
	}

	/*
		Use 	: Get Sales Product Mapping
		Author 	: Axay Shah
		Date 	: 29 June,2020
	*/
	public static function GetById($id = 0){
		$result = array();
		$data = self::where("sales_product_id",$id)->get();
		return $data;
	}

	/*
	Use 	: Get Sales Product Mapping
	Author 	: Axay Shah
	Date 	: 29 June,2020
	*/
	public static function SalesProductByPurchaseProductID($id = 0){
		$self 			= (new static)->getTable();
		$salesProduct 	= new WmProductMaster();
		$data 			= self::select("$self.*","SAL.title")
							->join($salesProduct->getTable()." as SAL","$self.sales_product_id","=","SAL.id")
							->where("purchase_product_id",$id)
							->where("SAL.sales",1)
							->where("SAL.status",1)
							->get();
		return $data;
	}

	/*
	Use 	: Get Sales Product Mapping
	Author 	: Axay Shah
	Date 	: 29 June,2020
	*/
	public static function getSaleProductByPurchaseProduct($purchase_product_id)
	{
		$MappingArray	= array();
		$self 			= (new static)->getTable();
		$salesProduct 	= new WmProductMaster();
		$data 			= self::select("SAL.*","$self.*")
							->join($salesProduct->getTable()." as SAL","$self.sales_product_id","=","SAL.id")
							->whereIn("purchase_product_id",$purchase_product_id)
							->where("SAL.status","1")
							->orderBy("purchase_product_id","ASC")
							->orderBy('SAL.title', 'ASC')
							->get();
		if (!empty($data))
		{
			foreach ($data as $row) {
				if (isset($MappingArray[$row->purchase_product_id])) {
					$MappingArray[$row->purchase_product_id][] 	= $row;
				} else {
					$MappingArray[$row->purchase_product_id]	= array();
					$MappingArray[$row->purchase_product_id][] 	= $row;
				}
			}
		}
		return $MappingArray;
	}
	/*
	Use 	: Get Sales Product Mapping from purchase for mobile
	Author 	: Axay Shah
	Date 	: 29 June,2020
	*/
	public static function getSaleProductByPurchaseProductFromMobile($purchase_product_id)
	{
		$data			= array();
		$self 			= (new static)->getTable();
		$salesProduct 	= new WmProductMaster();
		if(!empty($purchase_product_id)){
			if(!is_array($purchase_product_id)){
				$purchase_product_id = explode(",",$purchase_product_id);
			}
			$data 			= self::select("purchase_product_id as product_id")->whereIn("purchase_product_id",$purchase_product_id)->groupBy("purchase_product_id")->get()->toArray();
			if (!empty($data))
			{
				if(!is_array($purchase_product_id)){
					$purchase_product_id = explode(",",$purchase_product_id);
				}
				foreach ($data as $key => $value) {

					$MappingArray		= array();
					$self 				= (new static)->getTable();
					$salesProduct 		= new WmProductMaster();
					$SalesProductData 	= self::select("SAL.id","SAL.title","SAL.status","SAL.sales")
										->join($salesProduct->getTable()." as SAL","$self.sales_product_id","=","SAL.id")
										->where("purchase_product_id",$value['product_id'])
										->where("SAL.status","1")
										->orderBy("purchase_product_id","ASC")
										->orderBy('SAL.title', 'ASC')
										->get();
					$data[$key]['sales_data'] = $SalesProductData;
				}
			}
		}
		return $data;
	}
}