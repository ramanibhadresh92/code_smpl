<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;
use Carbon\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\WmSalesToPurchaseMap;
use App\Models\CompanyProductMaster;
use App\Models\NetSuitStockLedger;
class ProductInwardLadger extends Model implements Auditable
{
	protected 	$table 		=	'inward_ledger';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;

	/*
	Use 	: Add Inward of product for stock
	Author 	: Axay Shah
	Date 	: 23 Aug,2019
	*/
	public static function AddInward($request){
		$id 						= 0;
		$Inward 					=  new self();
		$Inward->product_id   		=  (isset($request['product_id']) && !empty($request['product_id'])) ? $request['product_id'] : 0 ;
		$Inward->ref_id       		=  (isset($request['ref_id']) && !empty($request['ref_id'])) ? $request['ref_id'] : 0 ;
		$Inward->quantity 	  		=  (isset($request['quantity']) && !empty($request['quantity'])) ? $request['quantity'] : 0 ;
		$Inward->avg_price      	= (isset($request['avg_price']) && !empty($request['avg_price'])) ? $request['avg_price'] : 0 ;
		$Inward->type 		  		=  (isset($request['type']) 	&& !empty($request['type'])) ? $request['type'] : NULL ;
		$Inward->product_type 		=  (isset($request['product_type']) && !empty($request['product_type'])) ? $request['product_type'] : 1 ;
		$Inward->remarks        	= (isset($request['remarks'])    && !empty($request['remarks'])) ? $request['remarks'] : "" ;
		$Inward->mrf_id 	 		=  (isset($request['mrf_id']) 	&& !empty($request['mrf_id'])) ? $request['mrf_id'] : 0 ;

		$Inward->batch_id 	 		=  (isset($request['batch_id'])&& !empty($request['batch_id'])) ? $request['batch_id'] : 0 ;
		$Inward->direct_dispatch 	=  (isset($request['direct_dispatch'])&& !empty($request['direct_dispatch'])) ? $request['direct_dispatch'] : 0 ;
		$Inward->company_id  		=  Auth()->user()->company_id;
		$Inward->inward_date 		=  (isset($request['inward_date'])&& !empty($request['inward_date'])) ? $request['inward_date'] : date("Y-m-d");
		$Inward->created_by  		=  Auth()->user()->adminuserid;
		if($Inward->save()){
			$id = $Inward->id;
		}
		return $id;
	}

	/*
	Use     : Insert Inward
	Author  : Axay Shah
	Date    : 27 Aug,2019
	*/
	public static function CreateInWard($ProductId = 0,$quantity=0,$type="",$productType = 1,$mrfId=0,$date = "",$batch_id=0,$refId=0){
		$array = array(
				"product_id"        => $ProductId,
				"ref_id"            => $refId,
				"quantity"          => $quantity,
				"type"              => $type,
				"product_type" 		=> $productType,
				"mrf_id"            => $mrfId,
				"batch_id"          => $batch_id,
				"inward_date"       => $date,
				"created_by"        => Auth()->user()->adminuserid
			);
			self::AddInward($array);
	}

	/*
	Use     : Add Inward of product for stock
	Author  : Kalpak Prajapati
	Date    : 14 July,2020
	*/
	public static function AutoAddInward($request)
	{
		$id 							= 0;
		$Inward                         = new self();
		$Inward->purchase_product_id    = (isset($request['purchase_product_id']) && !empty($request['purchase_product_id'])) ? $request['purchase_product_id']:0;
		$Inward->product_id             = (isset($request['product_id']) && !empty($request['product_id'])) ? $request['product_id'] : 0 ;
		$Inward->production_report_id   = (isset($request['production_report_id'])  && !empty($request['production_report_id'])) ? $request['production_report_id'] : 0 ;
		$Inward->ref_id                 = (isset($request['ref_id'])    && !empty($request['ref_id'])) ? $request['ref_id'] : 0 ;
		$Inward->quantity               = (isset($request['quantity']) && !empty($request['quantity'])) ? $request['quantity'] : 0 ;
		$Inward->avg_price              = (isset($request['avg_price']) && !empty($request['avg_price'])) ? $request['avg_price'] : 0 ;
		$Inward->type                   = (isset($request['type'])  && !empty($request['type'])) ? $request['type'] : NULL ;
		$Inward->product_type           = (isset($request['product_type'])  && !empty($request['product_type'])) ? $request['product_type'] : NULL ;
		$Inward->mrf_id                 = (isset($request['mrf_id'])    && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0 ;
		$Inward->batch_id               = (isset($request['batch_id'])    && !empty($request['batch_id'])) ? $request['batch_id'] : 0 ;
		$Inward->remarks                = (isset($request['remarks'])    && !empty($request['remarks'])) ? $request['remarks'] : "" ;
		$Inward->company_id             = (isset($request['company_id'])    && !empty($request['company_id'])) ? $request['company_id'] :  Auth()->user()->company_id ;
		$Inward->inward_date           	= (isset($request['inward_date']) && !empty($request['inward_date'])) ? $request['inward_date'] : date("Y-m-d") ;
		$Inward->created_by             = (isset($request['created_by'])    && !empty($request['created_by'])) ? $request['created_by'] : 0 ;
		$Inward->updated_by             = (isset($request['updated_by'])    && !empty($request['updated_by'])) ? $request['updated_by'] : 0 ;
		$Inward->direct_dispatch 		=  (isset($request['direct_dispatch'])&& !empty($request['direct_dispatch'])) ? $request['direct_dispatch'] : 0 ;
		$Inward->new_cogs_price 		=  (isset($request['new_cogs_price'])&& !empty($request['new_cogs_price'])) ? $request['new_cogs_price'] : 0 ;
		if($Inward->save()){
			$id = $Inward->id;
		}
		return $id;
	}
}
