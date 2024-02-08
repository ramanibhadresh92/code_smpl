<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\ProductInwardLadger;
use App\Models\OutWardLadger;
use App\Models\CompanyProductMaster;
use App\Models\WmDepartment;
use App\Models\WmProductMaster;
use App\Facades\LiveServices;
use view;
use PDF;
class StockAdjustmentProcessMaster extends Model implements Auditable
{
	protected 	$table 		=	'stock_adjustment_process_master';
	protected 	$primaryKey =	'id'; // or null
	protected 	$guarded 	=	['id'];
	public      $timestamps =   true;
	use AuditableTrait;

	/*
	Use 	: Update Daily Stock of every product on daily basis
	Author 	: Axay Shah
	Date 	: 17 Aug,2020
	*/
	public static function AddStockAdjustmentProcess($request){
		$Add 						= new self();
		$Add->quantity 				= (isset($request['quantity']) && !empty($request['quantity'])) ? $request['quantity'] : 0;
		$Add->product_id 			= (isset($request['product_id']) && !empty($request['product_id'])) ? $request['product_id'] : 0;
		$Add->product_type			= (isset($request['product_type']) && !empty($request['product_type'])) ? $request['product_type'] : 0;
		$Add->stock_qty 			= (isset($request['stock_qty']) && !empty($request['stock_qty'])) ? $request['stock_qty'] : 0;
		$Add->mrf_id 				= (isset($request['mrf_id']) && !empty($request['mrf_id'])) ? $request['mrf_id'] : 0;
		$Add->company_id			= (isset($request['company_id']) && !empty($request['company_id'])) ? $request['company_id'] : Auth()->user()->company_id;
		$Add->stock_process_from_date = date("Y-m-d",strtotime($DATE));
		$Add->stock_process_to_date	= date("Y-m-d",strtotime($DATE));
		$Add->created_at			= Date("Y-m-d H:i:s");
		$Add->updated_at			= Date("Y-m-d H:i:s");
		if($Add->save()){
			return $request->id;
		}
		return 0;
	}
}

