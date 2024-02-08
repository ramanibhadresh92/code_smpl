<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Parameter;
use App\Models\CustomerMaster;
use App\Models\AdminUser;
use App\Models\WmProductMaster;
use App\Models\ProductInwardLadger;
class ShiftProductEntryMaster extends Model
{
    protected 	$table              = 'shift_product_entry_master';
    protected 	$guarded            = ['id'];
    protected 	$primaryKey         = 'id'; // or null
 
 
    /*
	Use  	: Add Product entry in Shift Product 
	Date 	: 03 April 2020
	Author 	: Axay Shah
    */

	public static function AddShiftProduct($request){
		$shift_id 			= (isset($request->shift_id) && !empty($request->shift_id)) ?  $request->shift_id : 0;
		$mrf_id 			= (isset($request->mrf_id) && !empty($request->mrf_id)) ?  $request->mrf_id : 0;
		$productData 		= (isset($request->product) && !empty($request->product)) ? json_decode($request->product,true) : "";
		$shift_timing_id	= (isset($request->shift_timing_id) && !empty($request->shift_timing_id)) ? $request->shift_timing_id : 0;
		if(!empty($productData)){
			foreach($productData as $raw){
				$ADD 					= self::firstOrNew(array(
					'shift_timing_id' => $shift_timing_id,
					"product_id"=>$raw['product_id']
				));
				$QTY 					= (!empty($raw['qty'])) ? $raw['qty'] : 0;
				$ADD->qty 				+= $QTY;
				$ADD->shift_id 			= $shift_id;
				$ADD->mrf_id 			= $mrf_id;
				$ADD->created_by 		= Auth()->user()->adminuserid;
				$ADD->updated_by 		= Auth()->user()->adminuserid;
				$ADD->company_id 		= Auth()->user()->company_id;
				$ADD->save();
				$SHIFT_START_DATE = ''; /////// not available added blank
				if($QTY > 0){
					ProductInwardLadger::CreateInWard($raw['product_id'],$QTY,TYPE_MRF_SHIFT,PRODUCT_SALES,$mrf_id,$SHIFT_START_DATE,0,$shift_timing_id);
				}
			}
			return  true;
		}
		return false;
	}
 	
	 /*
	Use  	: Shift Product Qty
	Date 	: 13 April,2020
	Author 	: Axay Shah
    */
	public static function ShiftProductTotalQty($id=0) {
		$Sales 	 = new WmProductMaster();
		$self 	 = (new static)->getTable();
		$result  = 	array();
		$data 	 = 	self::select(\DB::raw("P.title"),
	    				\DB::raw("P.id"),
	    				\DB::raw("P.hsn_code"),
	    				\DB::raw("P.description"),
	    				\DB::raw("SUM($self.qty) as total_qty")
    				)
					->join($Sales->getTable()." as P","$self.product_id","=","P.id")
    				->where("$self.shift_timing_id",$id)->groupBy("$self.product_id")->orderBy("P.title","ASC")->get();
    	return $data;
	}
}
