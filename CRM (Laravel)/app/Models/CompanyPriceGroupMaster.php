<?php

namespace App\Models;

use App\Facades\LiveServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use App\Models\ViewCompanyPriceGroupMaster;
use Illuminate\Support\Facades\Auth;
use App\Models\UserBaseLocationMapping;
use App\Models\LocationMaster;
class CompanyPriceGroupMaster extends Model
{
    protected 	$table 		    =	'company_price_group_master';
    protected 	$guarded 	    =	['id'];
    protected 	$primaryKey     =	'id'; // or null
    public      $timestamps     = true;


    public function PriceGroupCity(){
    	return $this->hasOne(LocationMaster::class,'location_id');
    }


    
/*
	use  	: List master price group
	Author 	: Axay Shah
	Date 	: 17 Sep,2018
*/
	public static function listPriceGroup($request){
		$cityId 		= GetBaseLocationCity();
		$sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "id";
	    $sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
	    $recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
	    $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$list 			= ViewCompanyPriceGroupMaster::select("view_company_price_group_master.*","L.city as city_name")
						  ->leftjoin("location_master as L","city_id","=","L.location_id")
						  ->where('company_id',Auth()->user()->company_id)
						  ->whereIn('city_id',$cityId);
		if($request->has('params.city_id') && !empty($request->input('params.city_id')))
    	{
        	$list->where('city_id', $request->input('params.city_id'));
    	}
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
		$list->where('is_default','Y');
    	return $list->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
	}
	/*
	use  	: Add master price group
	Author 	: Axay Shah
	Date 	: 27 Sep,2018
	*/
	public static function add($request){
		DB::beginTransaction();
		try{
			$id 		= 0;
			$userId 	= Auth()->user()->adminuserid;
			$priceGroup = new self();
			$priceGroup->group_value 			= $request->group_value;
			$priceGroup->group_desc 			= $request->group_desc;
			$priceGroup->group_tech_desc 		= $request->group_tech_desc;
			$priceGroup->flag_show 				= (isset($request->flag_show) && !empty($request->flag_show))? $request->flag_show 		: 0;
			$priceGroup->is_default 			= (isset($request->is_default) && !empty($request->is_default))? $request->is_default 	: "N";
			$priceGroup->created_by 			= $userId;
			$priceGroup->customer_id 			= (isset($request->customer_id) && !empty($request->customer_id))? $request->customer_id 	: 0;
			$priceGroup->sort_order 			= (isset($request->sort_order) 	&& !empty($request->sort_order))? $request->sort_order 	: "";
			$priceGroup->status 				= (isset($request->status) 		&& !empty($request->status)) 	? $request->status 		: "";
            $priceGroup->cust_identify_group 	= (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : "";
            $priceGroup->ref_price_group_id    	= (isset($request->ref_price_group_id) && !empty($request->ref_price_group_id)) ? $request->ref_price_group_id : 0;
            $priceGroup->company_id 	        = (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
            $priceGroup->city_id 	            = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
            if($priceGroup->save()){
            	$id = $priceGroup->id;
            	LR_Modules_Log_CompanyUserActionLog($request,$priceGroup->id);
            }
			DB::commit();
			return $id;
		}catch(\Exception $e) {
			DB::rollback();
			return false;
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
	}
	/*
	use  	: update master price group
	Author 	: Axay Shah
	Date 	: 27 Sep,2018
	*/
	public static function updateRecord($request){
		DB::beginTransaction();
		try{
			$userId 	= Auth()->user()->adminuserid;
			$priceGroup = self::findOrFail($request->id);
			$priceGroup->group_value 	        = $request->group_value;
			$priceGroup->group_desc 	        = $request->group_desc;
			$priceGroup->group_tech_desc        = $request->group_tech_desc;
			$priceGroup->group_type 	        = $request->group_type;
			$priceGroup->is_default 			= (isset($request->is_default) && !empty($request->is_default))? $request->is_default 	: $priceGroup->is_default;
			$priceGroup->flag_show 				= (isset($request->flag_show) && !empty($request->flag_show))? $request->flag_show 		: $priceGroup->is_default;
			$priceGroup->customer_id 			= (isset($request->customer_id) && !empty($request->customer_id))? $request->customer_id 	: $priceGroup->customer_id;
			$priceGroup->sort_order 	        = (isset($request->sort_order)  && !empty($request->sort_order))? $request->sort_order : $priceGroup->sort_order;
			$priceGroup->status 		        = (isset($request->status) && !empty($request->status)) ? $request->status : $priceGroup->status;
			$priceGroup->cust_identify_group    = (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : $priceGroup->cust_identify_group;
			$priceGroup->updated_by 	        = $userId;
            $priceGroup->ref_price_group_id    	= (isset($request->ref_price_group_id) && !empty($request->ref_price_group_id)) ? $request->ref_price_group_id : $priceGroup->ref_price_group_id ;
            $priceGroup->company_id 	        = (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : $priceGroup->company_id ;
            $priceGroup->city_id 	            = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
	        if($priceGroup->save()){
	        	LR_Modules_Log_CompanyUserActionLog($request,$priceGroup->id);
	        	DB::commit();
	        	return $priceGroup;
	        }else{
	        	return false;	
	        }
		}catch(\Exception $e) {
			DB::rollback();
			return false;
            // return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
	}
	/*
	use  	: Get List of Price Group By Company Id
	Author 	: Axay Shah
	Date 	: 09 Oct,2018
	NOTE 	: CHANGE API BECAUSE OF BASE LOCATION INTRODUCE - 29 April,2019
	*/
	public static function priceGroupByCompany($cityId = 0){
		$locationMaster = new LocationMaster();
		$locationTbl 	= $locationMaster->getTable();
		$priceGroup 	= (new static)->getTable();
		$data 			=  self::select("$priceGroup.*",\DB::raw("$locationTbl.city as city_name"))->where('company_id',Auth()->user()->company_id)
		->join("$locationTbl","$priceGroup.city_id","=","$locationTbl.location_id")
		->where("$priceGroup.status",'A')
		->where("$priceGroup.is_default",'Y');
		

		if($cityId == 0){
			$cityId = array();
	        $cityId = UserBaseLocationMapping::GetBaseLocationCityListByUser(Auth()->user()->adminuserid);
	     	$data->whereIn('city_id',$cityId);   
	    }else{
			$data->where("$priceGroup.city_id",$cityId);
		}
		return $data->get();
	}
	/*
	use  	: Price group by customer with customer name
	Author 	: Axay Shah
	Date 	: 09 Oct,2018
	*/
	public static function priceGroupByCustomer($cityId = 0){
		$data =  CustomerMaster::select('c.*',\DB::raw("CONCAT(customer_master.first_name,
		' ',
		customer_master.last_name) AS customer_name"))
		->JOIN('company_price_group_master as c','customer_master.price_group','=','c.id')
		->where('c.company_id',Auth()->user()->company_id)
		->where('c.city_id',$cityId)
		->where('c.status','A')
		->where('c.is_default','N')
		->groupBy('customer_master.price_group')
		->get();
		return $data;
	}




	/*
    *   Use     :   Change status of Company price group
    *   Author  :   Axay Shah
    *   Date    :   29 Dec,2018
    */
    public static function changeStatus($pricegroupId,$status){
        return self::where('id',$pricegroupId)->update(['status'=> $status]);
    }


    /**
     * Use      : getCustomerGroup
     * Author   : Sachin Patel
     * Date     : 14 Feb,2019
     */

    public static function GetGeneralPriceGroups(){
        return self::select('id','group_value as name')
            ->where('is_default','Y')
            ->where('city_id', Auth::user()->city)
            ->where('company_id',Auth::user()->company_id)
            ->where('status',SHORT_ACTIVE_STATUS)
            ->orderBy('group_value','ASC')
            ->get();
    }
}
