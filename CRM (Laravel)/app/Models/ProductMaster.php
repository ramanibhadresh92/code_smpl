<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\ProductQualityParameter;
use App\Models\ViewProductMaster;
use Validator;
use App\Traits\storeImage;
class ProductMaster extends Model
{
    //
    use storeImage;
    protected 	$table 		=	'product_master';
    protected 	$guarded 	=	['product_id'];
    protected 	$primaryKey =	'product_id'; // or null
    public 		$timestamps = 	true;
    /*
	use  	: List master Products 
	Author 	: Axay Shah
	Date 	: 19 Sep,2018
    */
	public static function listProduct($request){
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "product_id";
	    $sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
	    $recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
	    $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$list = ViewProductMaster::select("*");
        if($request->has('params.product_id') && !empty($request->input('params.product_id')))
    	{
            $product_id = explode(',',$request->input('params.product_id'));
        	$list->whereIn('product_id', $product_id);
    	}
    	if($request->has('params.status') && !empty($request->input('params.status')))
    	{
        	$list->where('para_status_id',$request->input('params.status'));
    	}
    	if($request->has('params.name') && !empty($request->input('params.name')))
    	{
        	$list->where('name','like', '%'.$request->input('params.name').'%');
    	}
        if($request->has('params.category_name') && !empty($request->input('params.category_name')))
        {
            $list->where('category_name','like', '%'.$request->input('params.category_name').'%');
        }
         if(!empty($request->input('params.startDate')) && !empty($request->input('params.endDate')))
        {
            $list->whereBetween('created_date',array(date("Y-m-d", strtotime($request->input('params.startDate'))),date("Y-m-d", strtotime($request->input('params.endDate')))));
        }else if(!empty($request->input('params.startDate'))){
           $list->whereBetween('created_date',array(date("Y-m-d", strtotime($request->input('params.startDate'))),$Today));
        }else if(!empty($request->input('params.endDate'))){
            $list->whereBetween('created_date',array(date("Y-m-d", strtotime($request->input('params.endDate'))),$Today));
        }
    	return $list->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	}
    /*
    use     : add master Products 
    Author  : Axay Shah
    Date    : 20 Sep,2018
    */
    public static function add($request){
        DB::beginTransaction();
        $created_by = Auth()->user()->adminuserid;
        try{
            $masterId               = 0;
            $ADD                    =  new ProductMaster();
            $ADD->name              =  $request->name;
            $ADD->category_id       =  $request->category_id;
            $ADD->para_status_id    =  $request->para_status_id;
            $ADD->para_unit_id      =  $request->para_unit_id;
            $ADD->co2_saved         =  $request->co2_saved;
            $ADD->processing_cost   =  $request->processing_cost;
            $ADD->enurt             =  $request->enurt;
            $ADD->created_by        =  $created_by;
            $ADD->para_group_id     =  $request->para_group_id;
            $ADD->product_volume    =  (isset($request->product_volume) && !empty($request->product_volume)) ? $request->product_volume : 0 ;     
            if($request->hasfile('normal_img')) {
                $normal_pic = $ADD->verifyAndStoreImage($request,'normal_img',PATH_COMPANY,$masterId,PATH_COMPANY_PRODUCT,$masterId);
            }
            if($request->hasfile('select_img')) {
                $select_pic = $ADD->verifyAndStoreImage($request,'select_img',PATH_COMPANY,$masterId,PATH_COMPANY_PRODUCT,$masterId);
            }
            $ADD->normal_img            = (isset($normal_pic) && !empty($normal_pic)) ? $normal_pic->id   : "" ;
            $ADD->select_img            = (isset($select_pic) && !empty($select_pic)) ? $select_pic->id   : "";
            if($ADD->save()){
                $PQP = ProductQualityParameter::add($request,$ADD->product_id);
            }
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollback();
            return response()->json(["code" =>INTERNAL_SERVER_ERROR,"msg" =>$e->getMessage().$e->getLine(),"data" =>""]);
        }
        
    }
    /*
    use     : Update master Product by id 
    Author  : Axay Shah
    Date    : 20 Sep,2018
    */
    public static function updateProduct($request){
        DB::beginTransaction();
        try{
            $masterId                   = 0;
            $updated_by                 = Auth()->user()->adminuserid;
            $UPDATE                     = self::findOrFail($request->product_id);
            $UPDATE->name               =    (isset($request->name) && !empty($request->name)) 
                                            ? $request->name : $UPDATE->name;
            $UPDATE->category_id        =    (isset($request->category_id) && !empty($request->category_id)) 
                                            ? $request->category_id : $UPDATE->category_id;
            $UPDATE->para_status_id     =    (isset($request->para_status_id) && !empty($request->para_status_id)) 
                                            ? $request->para_status_id : $UPDATE->para_status_id;
            $UPDATE->para_unit_id       =    (isset($request->para_unit_id) && !empty($request->para_unit_id)) 
                                            ? $request->para_unit_id : $UPDATE->para_unit_id;
            $UPDATE->co2_saved          =    (isset($request->co2_saved) && !empty($request->co2_saved)) 
                                            ? $request->co2_saved : $UPDATE->co2_saved;
            $UPDATE->processing_cost    =    (isset($request->processing_cost) && !empty($request->processing_cost)) 
                                            ? $request->processing_cost : $UPDATE->processing_cost;
            $UPDATE->enurt              =    (isset($request->enurt ) && !empty($request->enurt )) 
                                            ? $request->enurt : $UPDATE->enurt;
            $UPDATE->para_group_id      =    (isset($request->para_group_id) && !empty($request->para_group_id)) 
                                            ? $request->para_group_id : $UPDATE->para_group_id;
            $UPDATE->product_volume     =    (isset($request->product_volume) && !empty($request->product_volume)) 
                                            ? $request->product_volume : $UPDATE->product_volume ;   
            $UPDATE->updated_by         =    $updated_by;
            if($request->hasfile('normal_img')) {
                $normal_pic = $UPDATE->verifyAndStoreImage($request,'normal_img',PATH_COMPANY,$masterId,PATH_COMPANY_PRODUCT,$masterId);
            }
            if($request->hasfile('select_img')) {
                $select_pic = $UPDATE->verifyAndStoreImage($request,'select_img',PATH_COMPANY,$masterId,PATH_COMPANY_PRODUCT,$masterId);
            }
            $UPDATE->normal_img            = (isset($normal_pic) && !empty($normal_pic)) ? $normal_pic->id   : $UPDATE->normal_img ;
            $UPDATE->select_img            = (isset($select_pic) && !empty($select_pic)) ? $select_pic->id   : $UPDATE->select_img;
            if($UPDATE->save()){
                $PQP = ProductQualityParameter::updateQualityByProduct($request,$UPDATE->product_id);
            }
            DB::commit();
            return true;
        }catch(\Exception $e) {
            DB::rollback();
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }
    
    /*
    use     : Get By Id
    Author  : Axay Shah
    Date    : 21 Nov,2018
    */
    public static function getById($productId){
        $product =  self::select("product_master.*",'m1.original_name as normal_original_name','m1.server_name as normal_server_name','m1.original_name as select_original_name','m2.server_name as select_server_name','m1.image_path as select_image_path','m1.image_path as normal_image_path','pq.*')
        ->leftjoin('media_master as m1','product_master.normal_img','=','m1.id')->leftjoin('media_master as m2','product_master.select_img','=','m2.id')
        ->leftjoin('product_quality_parameter as pq','product_master.product_id','=','pq.product_id')
        ->where('product_master.product_id',$productId)->first();
         
 
         if($product){
             ($product->normal_img!='') ? $product->normal_img   = url('/').URL_HTTP_IMAGES.$product->normal_image_path."/".$product->normal_server_name : "";
             ($product->select_img!='') ? $product->select_img   = url('/').URL_HTTP_IMAGES.$product->select_image_path."/".$product->select_server_name : "";
         }
         return $product;
         
     }
      /*
    *   Use     :   Change status of master Product
    *   Author  :   Axay Shah
    *   Date    :   11 Jan,2019
    */
    public static function changeStatus($productId,$status){
       
        return self::where('product_id',$productId)->update(['para_status_id'=> $status]);
    }
}
