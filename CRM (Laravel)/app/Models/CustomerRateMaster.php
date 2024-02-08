<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Facades\LiveServices;
use DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Support\Facades\Http;
use App\Models\CustomerRateMasterDetails;
class CustomerRateMaster extends Model implements Auditable
{
	protected 	$table 		=	'customer_rate_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public 		$timestamps = 	true;
	use AuditableTrait;
	protected $casts = [
		
	];

	public static function GetCustomerProductRateById($customer_id){
		$CRD 	= new CustomerRateMasterDetails();
		$self 	= (new static)->getTable();  
		$data 	= self::select("CRD.*")
		->join($CRD->getTable()." as CRD","$self.id","=","CRD.rate_master_id")
		->where("$self.customer_id",$customer_id)
		->where("$self.status",1)
		->orderBy("$self.id","DESC")
		->get()->toArray();
		return $data;
	}


	/*
	Use 	: Customer rate master store 
	Author 	: Axay Shah
	Date 	: 16 November,2021
	*/
	public static function StoreCustomerRate($request){
		try{
			$datetime 		=  date("Y-m-d H:i:s");
			$userID 		=  Auth()->user()->adminuserid;
			$customer_id 	=  $request['customer_id'];
			$purchase_product = (isset($request['purchase_product']) && !empty($request['purchase_product'])) ? $request['purchase_product'] : array();
			if(!empty($purchase_product)){
				$product = json_decode($purchase_product,true);
				if(isset($product[0]['product_id']) && !empty($product[0]['product_id'])){
					$data = self::where("customer_id",$customer_id)
						->where("status",1)
						->orderBy("id","DESC")
						->update([  "updated_by"=>$userID,
									"updated_at" => $datetime,
									"status" => 0]);
					$id = 	self::insertGetId(array(
							"customer_id" 	=> $customer_id,
							"created_at" 	=> $datetime,
							"updated_at" 	=> $datetime,
							"created_by"	=> $userID,
							"updated_by" 	=> $userID));
					###### DECODING PRODUCT ########
					$product = json_decode($purchase_product,true);
					foreach($product as $value){
						###### STORING PRODUCT DETAILS ########
						$ProductData = CompanyProductMaster::where("id",$value['product_id'])->first(["cgst","sgst","igst"]);
						CustomerRateMasterDetails::insertGetId(
						array(
							"customer_id" 		=> $customer_id,
							"rate_master_id" 	=> $id,
							"rate" 				=> $value['rate'],
							"product_id" 		=> $value['product_id'],
							"cgst" 				=> ($ProductData) ?  $ProductData->cgst : 0,
							"sgst" 				=> ($ProductData) ?  $ProductData->cgst : 0,
							"igst" 				=> ($ProductData) ?  $ProductData->cgst : 0,
							"updated_at" 		=> $datetime,
							"created_at" 		=> $datetime
						));
					}
				}
			}
		}catch(\Exception $e){
			dd($e);
		}
		
	}
}

