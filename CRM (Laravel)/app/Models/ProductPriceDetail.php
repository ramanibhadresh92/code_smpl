<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PriceGroupMaster;
use App\Models\ProductVeriablePriceDetail;
use DB;
use Illuminate\Pagination\Paginate;
class ProductPriceDetail extends Model
{
    //
    //
    protected 	$table 		=	'product_price_details';
    protected 	$guarded 	=	['details_id'];
    protected 	$primaryKey =	'details_id'; // or null
    public 		$timestamps = 	true;

    /*
    Use     : List price group of perticular product
    Author  : Axay Shah 
    Date    : 21 Sep,2018
    */
	public static function productPriceDetailList($request){
        $sortBy         =   ($request->has('sortBy')      && !empty($request->input('sortBy'))) 
                            ? $request->input('sortBy')    : "id";
        $sortOrder      =   ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) 
                            ? $request->input('sortOrder') : "ASC";
        $recordPerPage  =   !empty($request->input('size'))       ?   $request->input('size')         : 15;
        $pageNumber     =   !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : 1;
        $start_from     =   ($pageNumber-1) * $recordPerPage;  
        $where          = array();
        $one   =    DB::table('view_price_group_with_detail')->whereNull('product_id')->get();
        $two   =    DB::table('view_price_group_with_detail')
                    ->where('product_id',$request->product_id)
                    ->pluck('details_id')
                    ->toArray();
                    
        $query = "  select * from view_price_group_with_detail as v 
                    where product_id IS NULL";
        if(!empty($two)){
            $values = implode(",",$two);
            
            $query  =   "select * from (
                            SELECT *  FROM `view_price_group_with_detail` WHERE 
                            `details_id` IN (".$values.")
                        union 
                            select * from view_price_group_with_detail 
                            where product_id IS NULL
                        ) as v";
        }
        if($request->has('params.group_value') && !empty($request->input('params.group_value')))
        {
            $where[] = 'v.group_value like "%'.$request->input('params.group_value').'%"';
        }
        if($request->has('params.product_inert') && !empty($request->input('params.product_inert')))
        {
            $where[] = 'v.product_inert like "%'.$request->input('params.product_inert').'%"';
        }
        if($request->has('params.factory_price') && !empty($request->input('params.factory_price')))
        {
            $where[] = 'v.factory_price like "%'.$request->input('params.factory_price').'%"';
        }
        if($request->has('params.price') && !empty($request->input('params.price')))
        {
            $where[] = 'v.price like "%'.$request->input('params.price').'%"';
        }
        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }
        $totalElements          = DB::select($query);
        $query .= ' ORDER BY '.$sortBy.' '.$sortOrder.' LIMIT  ' .$start_from.','.$recordPerPage ;
        $totalPages             = ceil(count($totalElements) / $recordPerPage);
        $data['result']         = $totalElements;
        $data['totalElements']  = count($totalElements);
        $data['totalPages']     = $totalPages;
        $data['size']           = $recordPerPage;
        $data['pageNumber']     = $pageNumber;
        return $data;
    }
	/*
    Use     : Add product price group with its price detail 
    Author  : Axay Shah 
    Date    : 21 Sep,2018
    */
	public static function add($request){
        try{
            DB::beginTransaction();
            $priceDetail = new self();
			$priceDetail->product_id 	     = (isset($request->product_id) && !empty($request->product_id)) 
                                                ? $request->product_id          : 0;
			$priceDetail->para_waste_type_id = (isset($request->para_waste_type_id) && !empty($request->para_waste_type_id)) 
                                                ? $request->para_waste_type_id  : 0;
			$priceDetail->product_inert      = (isset($request->product_inert) && !empty($request->product_inert)) 
                                                ? $request->product_inert       : 0;
			$priceDetail->factory_price      = (isset($request->factory_price) && !empty($request->factory_price)) 
                                                ? $request->factory_price : 0;
			$priceDetail->price              = (isset($request->price) && !empty($request->price)) ? $request->price : 0;
				if($priceDetail->save()){
					if(isset($request->variable_data) && !empty($request->variable_data)){
							$veriable_price_detail = json_decode(json_encode($request->variable_data),true);
                        foreach($veriable_price_detail as $key=>$value){
							/*INSERT VERIABLE PRICE GROUP DATA*/
                            DB::select('call SP_INSERT_VERIABLE_PRICE_DETAIL('.$priceDetail->details_id.','.$veriable_price_detail[$key]['min'].','.$veriable_price_detail[$key]['max'].','.$veriable_price_detail[$key]['price'].','.Auth()->user()->adminuserid.')');
		      		    }
                    }
			    }
			DB::commit();
			return $priceDetail;
		}catch(\Exception $e) {
			DB::rollback();
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
	}

    /*
    Use     : update product price group with its price detail 
    Author  : Axay Shah 
    Date    : 21 Sep,2018
    */
    public static function updateRecord($request,$id){
        try{
            DB::beginTransaction();
            $update = array();
            $update['product_id']           =   $request->product_id;
            $update['para_waste_type_id']   =   $request->para_waste_type_id;
            $update['product_inert']        =   (isset($request->product_inert) && !empty($request->product_inert)) 
                                                ? $request->product_inert : $id->product_inert;
            $update['factory_price']        =   (isset($request->factory_price) && !empty($request->factory_price)) 
                                                ? $request->factory_price : $id->factory_price;
            $update['price']                =   (isset($request->price) && !empty($request->price)) ? $request->price : $id->price;
            $priceDetail = self::where('details_id',$id->details_id)->update($update);
            if($priceDetail){
                    if(isset($request->variable_data) && !empty($request->variable_data)){
                        $veriable_price_detail = json_decode(json_encode($request->variable_data),true);
                        ProductVeriablePriceDetail::deleteByProductId($id->details_id);
                        foreach($veriable_price_detail as $key=>$value){
                        /*INSERT VERIABLE PRICE GROUP DATA*/
                            DB::select('call SP_INSERT_VERIABLE_PRICE_DETAIL('.$id->details_id.','.$veriable_price_detail[$key]['min'].','.$veriable_price_detail[$key]['max'].','.$veriable_price_detail[$key]['price'].','.Auth()->user()->adminuserid.')');
                        }
                    }
                }
            DB::commit();
            return $priceDetail;
        }catch(\Exception $e) {
            DB::rollback();
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }
    
}
