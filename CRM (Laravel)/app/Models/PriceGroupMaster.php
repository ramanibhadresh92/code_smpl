<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ViewPriceGroupMaster;
use DB;
class PriceGroupMaster extends Model
{
    protected 	$table 		=	'price_group_master';
    protected 	$guarded 	=	['id'];
    protected 	$primaryKey =	'id'; // or null
    public 		$timestamps = 	true;
	

/*
	use  	: List master price group
	Author 	: Axay Shah
	Date 	: 17 Sep,2018
*/
	public static function listPriceGroup($request){
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
	    $sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
	    $recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
	    $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$list = 	ViewPriceGroupMaster::select("*");
		if($request->has('params.group_type') && !empty($request->input('params.group_type')))
    	{
        	$list->where('group_type', $request->input('params.group_type'));
    	}
    	if($request->has('params.group_value') && !empty($request->input('params.group_value')))
    	{
        	$list->where('group_value','like','%'.$request->input('params.group_value').'%');
    	}
    	if($request->has('params.group_desc') && !empty($request->input('params.group_desc')))
    	{
        	$list->where('group_desc','like', '%'.$request->input('params.group_desc').'%');
    	}
    	if($request->has('params.sort_order') && !empty($request->input('params.sort_order')))
    	{
        	$list->where('sort_order', $request->input('params.sort_order'));
    	}
    	if($request->has('params.flag_show') && !empty($request->input('params.flag_show')))
    	{
        	$list->where('flag_show', $request->input('params.flag_show'));
    	}
    	if($request->has('params.group_tech_desc') && !empty($request->input('params.group_tech_desc')))
    	{
    		$list->where('group_tech_desc','like', '%'.$request->input('params.group_tech_desc').'%');
    	}
    	return $list->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	}
	/*
	use  	: Add master price group
	Author 	: Axay Shah
	Date 	: 18 Sep,2018
	*/
	public static function add($request){
		DB::beginTransaction();
		try{
			$userId 	= Auth()->user()->adminuserid;
			$priceGroup = new PriceGroupMaster();
			$priceGroup->group_value 			= $request->group_value;
			$priceGroup->group_desc 			= $request->group_desc;
			$priceGroup->group_tech_desc 		= $request->group_tech_desc;
			$priceGroup->group_type 			= $request->group_type;
			$priceGroup->created_by 			= $userId;
			$priceGroup->sort_order 			= (isset($request->sort_order) 	&& !empty($request->sort_order))? $request->sort_order 	: "";
			$priceGroup->status 				= (isset($request->status) 		&& !empty($request->status)) 	? $request->status 		: "";
			$priceGroup->cust_identify_group 	= (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : "";
			$priceGroup->save();
			DB::commit();
			return $priceGroup;
		}catch(\Exception $e) {
			DB::rollback();
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
	}
	/*
	use  	: update master price group
	Author 	: Axay Shah
	Date 	: 18 Sep,2018
	*/
	public static function updateRecord($request){
		DB::beginTransaction();
		try{
			$userId 	= Auth()->user()->adminuserid;
			$priceGroup = self::findOrFail($request->id);
			$priceGroup->group_value 	= $request->group_value;
			$priceGroup->group_desc 	= $request->group_desc;
			$priceGroup->group_tech_desc= $request->group_tech_desc;
			$priceGroup->group_type 	= $request->group_type;
			$priceGroup->sort_order 	= (isset($request->sort_order) && !empty($request->sort_order))? $request->sort_order : $priceGroup->sort_order;
			$priceGroup->status 		= (isset($request->status) && !empty($request->status)) ? $request->status : $priceGroup->status;
			$priceGroup->cust_identify_group = (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : $priceGroup->cust_identify_group;
			$priceGroup->updated_by 	= $userId;
			$priceGroup->save();
			DB::commit();
			return $priceGroup;
		}catch(\Exception $e) {
			DB::rollback();
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
	}

	/*
    *   Use     :   Change status of Company Category
    *   Author  :   Axay Shah
    *   Date    :   11 Jan,2019
    */
    public static function changeStatus($categoryId,$status){
        
        return self::where('id',$categoryId)->update(['status'=> $status]);
    }
}
