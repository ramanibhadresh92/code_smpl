<?php

namespace App\Models;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;
use App\Models\ViewCompanyParameter;
use DB;
use App\Models\CustomerMaster;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class CompanyParameter extends Model implements Auditable
{
    use AuditableTrait;
    // protected $auditEnabled  = true;
    // Disables the log record after 500 records.
    // protected $historyLimit = 12; 
    // Fields you do NOT want to register.
    // protected $dontKeepLogOf = ['created_at', 'updated_at'];
    // Tell what actions you want to audit.
    // protected $auditableTypes = ['created', 'saved', 'deleted'];
    /*End Log Traits Code*/
    protected 	$table 		    =	'company_parameter';
    protected 	$primaryKey     =	'para_id'; // or null
    public      $timestamps     =   true;
    
    /* NOTE :   From new development onword we are not using para_type column - Axay Shah 03 Oct,2018*/
    /* NOTE :   We insert default parameter type in company_parameter table as default city vise for
                now So no need to insert same parameter for every city of company for now - Axay Shah 03 Oct,2018*/
    
    public function parameter()
    {
        return $this->hasMany(self::class,'para_parent_id');
    }
    public function scopeCity($query){
        $cityId     = GetBaseLocationCity();
        return $query->whereIn('city_id',$cityId);
    }
    public function scopeCompany($query){
        return $query->where('company_id',Auth()->user()->company_id);
    }
    public function scopeStatus($query){
        return $query->where('status','A');
    }
    public function scopeCompanyMultipleParameter($query,$type){
    	return $query->whereIn('para_parent_id',array($type))->where('status','A');
    }
    
    /*
    Use     :   List parameter type 
    Author  :   Axay Shah
    Date    :   01-10-2018
    */
    public static function getParameterType($request){

        return self::where("city_id",$request->city_id)
			        ->company()
			        ->where('para_parent_id',0)
			        ->get();
    }
    /*
    Use     :   List parameter of company 
    Author  :   Axay Shah
    Date    :   01-10-2018
    */
    public static function parameterList($request){
        $sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy') 	: "para_id";
	    $sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
	    $cityId      	= ($request->has('params.city_id')   	&& !empty($request->input('params.city_id'))) ? $request->input('params.city_id') : 0;
	    $recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
	    $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
		$list           = 	ViewCompanyParameter::select("view_company_parameter.*","LOC.city as city_name")
							->leftjoin("location_master as LOC","view_company_parameter.city_id","=","LOC.location_id")
							->where('company_id',Auth()->user()->company_id);
		if(!empty($cityId)){
			$list->where('city_id',$cityId);
		}else{
			$cityId =	GetBaseLocationCity();
			$list->whereIn('city_id',$cityId);
		}
		if($request->has('params.para_parent_id') && !empty($request->input('params.para_parent_id')))
    	{
        	$list->where('para_parent_id', $request->input('params.para_parent_id'));
    	}
    	if($request->has('params.para_sort_order') && !empty($request->input('params.para_sort_order')))
    	{
        	$list->where('para_sort_order',$request->input('params.para_sort_order'));
    	}
    	if($request->has('params.flag_show') && !empty($request->input('params.flag_show')))
    	{
        	$list->where('flag_show',$request->input('params.flag_show'));
    	}
    	if($request->has('params.para_value') && !empty($request->input('params.para_value')))
    	{
        	$list->where('para_value','like', '%'.$request->input('params.para_value').'%');
    	}
    	if($request->has('params.para_desc') && !empty($request->input('params.para_desc')))
    	{
        	$list->where('para_desc','like', '%'.$request->input('params.para_desc').'%');
        }
        if($request->has('params.para_tech_desc') && !empty($request->input('params.para_tech_desc')))
    	{
        	$list->where('para_tech_desc','like', '%'.$request->input('params.para_tech_desc').'%');
        }
        if($request->has('params.status') && !empty($request->input('params.status')))
    	{
        	$list->where('status',$request->input('params.status'));
    	}
    	return $list->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
    }
    /*
	use  	: Add company parameter
	Author 	: Axay Shah
	Date 	: 01 Oct,2018
	*/
	public static function add($request){
        
        DB::beginTransaction();
        $max = 1;
        try{
            $userId 	= Auth()->user()->adminuserid;
            $priceGroup = new self();
            if(isset($request->para_parent_id) && !empty($request->para_parent_id)) {
                
                $max        = self::where('para_parent_id',$request->para_parent_id)->max('para_id');
                if($max > 0){
                    $maxId  = $max+1;
                }else{
                    $maxId  = ($request->para_parent_id * 1000)+1;
                }
            }
            $priceGroup->para_id 		        = $maxId;
            $priceGroup->para_level     		= $request->para_level;
            $priceGroup->para_value 			= $request->para_value;
			$priceGroup->para_desc 			    = $request->para_desc;
            $priceGroup->para_tech_desc 		= $request->para_tech_desc;
            $priceGroup->created_by 			= $userId;
            $priceGroup->para_parent_id         = (isset($request->para_parent_id) && !empty($request->para_parent_id))? $request->para_parent_id : 0;
            $priceGroup->para_sort_order    	= (isset($request->para_sort_order) && !empty($request->para_sort_order))? $request->para_sort_order : 1;
            $priceGroup->show_in_scheduler    	= (isset($request->show_in_scheduler) && !empty($request->show_in_scheduler))? $request->show_in_scheduler 	: 0;
            $priceGroup->scheduler_time    	    = (isset($request->scheduler_time) && !empty($request->scheduler_time))? $request->scheduler_time 	: 0;
            $priceGroup->longitude         	    = (isset($request->longitude) && !empty($request->longitude))? $request->longitude 	: 0;
            $priceGroup->latitude         	    = (isset($request->latitude) && !empty($request->latitude))? $request->latitude 	: 0;
            $priceGroup->map_customer_id		= (isset($request->map_customer_id) && !empty($request->map_customer_id)) 	? $request->map_customer_id : 0;
            $priceGroup->status 				= (isset($request->status) 	&& !empty($request->status)) 	? $request->status 	: "A";
            $priceGroup->cust_identify_group 	= (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : "";
            $priceGroup->company_id 	        = (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
            $priceGroup->city_id 	            = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
            $priceGroup->save();
            LR_Modules_Log_CompanyUserActionLog($request,$priceGroup->para_id);
			DB::commit();
			return $priceGroup;
		}catch(\Exception $e) {
            DB::rollback();
            return false;
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }
    /*
	use  	: Add Parent Parameter Type
	Author 	: Axay Shah
	Date 	: 01 Oct,2018
	*/
	public static function addParameterType($request){
        DB::beginTransaction();
        try{
            $userId 	= Auth()->user()->adminuserid;
            $max        = self::where('para_parent_id',0)->max('para_id');
            $maxId      = ($max <= 0 ) ? 1 : $max+1;
            $priceGroup = new self();
            $priceGroup->para_parent_id         = (isset($request->para_parent_id) && !empty($request->para_parent_id))? $request->para_parent_id : 0;
            $priceGroup->para_id 		        = $maxId;
            $priceGroup->para_level     		= (isset($request->para_level) && !empty($request->para_level))? $request->para_level : 0;
            $priceGroup->para_value 			= (isset($request->para_value) && !empty($request->para_value))? $request->para_value : "";
			$priceGroup->para_desc 			    = (isset($request->para_desc) && !empty($request->para_desc))? $request->para_desc : "";
			$priceGroup->para_tech_desc 		= (isset($request->para_tech_desc) && !empty($request->para_tech_desc))? $request->para_tech_desc : "";
			$priceGroup->para_sort_order    	= (isset($request->para_sort_order) && !empty($request->para_sort_order))? $request->para_sort_order : 1;
            $priceGroup->show_in_scheduler    	= (isset($request->show_in_scheduler) && !empty($request->show_in_scheduler))? $request->show_in_scheduler 	: 0;
            $priceGroup->scheduler_time    	    = (isset($request->scheduler_time) && !empty($request->scheduler_time))? $request->scheduler_time 	: 0;
            $priceGroup->longitude         	    = (isset($request->longitude) && !empty($request->longitude))? $request->longitude 	: 0;
            $priceGroup->latitude         	    = (isset($request->latitude) && !empty($request->latitude))? $request->latitude 	: 0;
            $priceGroup->map_customer_id		= (isset($request->map_customer_id) && !empty($request->map_customer_id)) 	? $request->map_customer_id : 0;
            $priceGroup->status 				= (isset($request->status) 	&& !empty($request->status)) 	? $request->status 	: "A";
            $priceGroup->cust_identify_group 	= (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : "";
            $priceGroup->company_id 	        = (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : 0;
            $priceGroup->city_id 	            = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : 0;
            $priceGroup->created_by 			= $userId;
            $priceGroup->save();
			DB::commit();
			return $priceGroup;
		}catch(\Exception $e) {
			DB::rollback();
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }
    /*
	use  	: update company Parameter Type
	Author 	: Axay Shah
	Date 	: 01 Oct,2018
	*/
	public static function updateParameterType($request){
		DB::beginTransaction();
		try{
			$userId 	= Auth()->user()->adminuserid;
            $priceGroup = self::findOrFail($request->para_id);
            $priceGroup->para_parent_id         = (isset($request->para_parent_id) && !empty($request->para_parent_id))? $request->para_parent_id : $priceGroup->para_parent_id;
            $priceGroup->para_level     		= (isset($request->para_level) && !empty($request->para_level))? $request->para_level   : $priceGroup->para_level;
            $priceGroup->para_value 			= (isset($request->para_value) && !empty($request->para_value))? $request->para_value   : $priceGroup->para_value;
			$priceGroup->para_desc 			    = (isset($request->para_desc) && !empty($request->para_desc))? $request->para_desc      : $priceGroup->para_desc;
			$priceGroup->para_tech_desc 		= (isset($request->para_tech_desc) && !empty($request->para_tech_desc))? $request->para_tech_desc : $priceGroup->para_tech_desc;
			$priceGroup->para_sort_order    	= (isset($request->para_sort_order) && !empty($request->para_sort_order))? $request->para_sort_order : $priceGroup->para_sort_order;
            $priceGroup->show_in_scheduler    	= (isset($request->show_in_scheduler) && !empty($request->show_in_scheduler))? $request->show_in_scheduler 	: $priceGroup->show_in_scheduler;
            $priceGroup->scheduler_time    	    = (isset($request->scheduler_time) && !empty($request->scheduler_time))? $request->scheduler_time 	: $priceGroup->scheduler_time  ;
            $priceGroup->longitude         	    = (isset($request->longitude) && !empty($request->longitude))? $request->longitude 	: $priceGroup->longitude  ;
            $priceGroup->latitude         	    = (isset($request->latitude) && !empty($request->latitude))? $request->latitude 	: $priceGroup->latitude  ;
            $priceGroup->map_customer_id		= (isset($request->map_customer_id) && !empty($request->map_customer_id)) 	? $request->map_customer_id : $priceGroup->map_customer_id;
            $priceGroup->status 				= (isset($request->status) 	&& !empty($request->status)) 	? $request->status 	: $priceGroup->status;
            $priceGroup->cust_identify_group 	= (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : $priceGroup->cust_identify_group;
            $priceGroup->company_id 	        = (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : $priceGroup->company_id;
            $priceGroup->city_id 	            = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : $priceGroup->city_id;
            $priceGroup->save();
			DB::commit();
			return $priceGroup;
		}catch(\Exception $e) {
			DB::rollback();
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }


	/*
	use  	: update company parameter
	Author 	: Axay Shah
	Date 	: 01 Oct,2018
	*/
	public static function updateRecord($request){
		DB::beginTransaction();
		try{
			$userId 	= Auth()->user()->adminuserid;
            $priceGroup = self::findOrFail($request->para_id);
            $priceGroup->para_level     		= (isset($request->para_level) && !empty($request->para_level))? $request->para_level   : $priceGroup->para_level;
            $priceGroup->para_parent_id 	    = (isset($request->para_parent_id) && !empty($request->para_parent_id))? $request->para_parent_id  : $priceGroup->para_parent_id;
			$priceGroup->para_value 			= (isset($request->para_value) && !empty($request->para_value))? $request->para_value   : $priceGroup->para_value;
			$priceGroup->para_desc 			    = (isset($request->para_desc) && !empty($request->para_desc))? $request->para_desc      : $priceGroup->para_desc;
			$priceGroup->para_tech_desc 		= (isset($request->para_tech_desc) && !empty($request->para_tech_desc))? $request->para_tech_desc : $priceGroup->para_tech_desc;
			$priceGroup->para_sort_order    	= (isset($request->para_sort_order) && !empty($request->para_sort_order))? $request->para_sort_order : $priceGroup->para_sort_order;
            $priceGroup->show_in_scheduler    	= (isset($request->show_in_scheduler) && !empty($request->show_in_scheduler))? $request->show_in_scheduler 	: $priceGroup->show_in_scheduler;
            $priceGroup->scheduler_time    	    = (isset($request->scheduler_time) && !empty($request->scheduler_time))? $request->scheduler_time 	: $priceGroup->scheduler_time  ;
            $priceGroup->longitude         	    = (isset($request->longitude) && !empty($request->longitude))? $request->longitude 	: $priceGroup->longitude  ;
            $priceGroup->latitude         	    = (isset($request->latitude) && !empty($request->latitude))? $request->latitude 	: $priceGroup->latitude  ;
            $priceGroup->map_customer_id		= (isset($request->map_customer_id) && !empty($request->map_customer_id)) 	? $request->map_customer_id : $priceGroup->map_customer_id;
            $priceGroup->status 				= (isset($request->status) 	&& !empty($request->status)) 	? $request->status 	: $priceGroup->status;
            $priceGroup->cust_identify_group 	= (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : $priceGroup->cust_identify_group;
            $priceGroup->company_id 	        = (isset(Auth()->user()->company_id) && !empty(Auth()->user()->company_id)) ? Auth()->user()->company_id : $priceGroup->company_id;
            $priceGroup->city_id 	            = (isset($request->city_id) && !empty($request->city_id)) ? $request->city_id : $priceGroup->city_id;
            $priceGroup->save();
            LR_Modules_Log_CompanyUserActionLog($request,$request->para_id);
			DB::commit();
			return $priceGroup;
		}catch(\Exception $e) {
			DB::rollback();
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }
    /*
	use  	: Get by id detail
	Author 	: Axay Shah
	Date 	: 01 Oct,2018
	*/
	public static function getById($request){
		try{
            $fullName   = "";
            $data       = self::find($request->para_id);
            if($data){
                if($data->map_customer_id > 0){
                    $Record        = CustomerMaster::select(\DB::raw("concat(first_name,' ',last_name) as full_name"))->where("customer_id",$data->map_customer_id)->first();
                    if($Record){
                        $fullName      = $Record->full_name;
                    }
                }
                $data->full_name = $fullName;
            }
            return $data;
		}catch(\Exception $e) {
		    return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }
    /*
    *   Use     :   Change status of Company parameter
    *   Author  :   Axay Shah
    *   Date    :   29 Dec,2018
    */
    public static function changeStatus($pricegroupId,$status){
        return self::where('para_id',$pricegroupId)->update(['status'=> $status]);
    }

    /**
     * Use      : getCustomerGroup
     * Author   : Sachin Patel
     * Date     : 14 Feb,2019
     */

    public static function getCustomerGroup(){
        $cityId = GetBaseLocationCity();
        return self::select('para_id as id','para_value as name')->where('para_parent_id',PARA_CUSTOMER_GROUP)
                    ->whereIn('city_id',$cityId)
                    ->where('company_id',Auth::user()->company_id)
                    ->orderBy('para_value','ASC')
                    ->get();
    }

    public static function GetCompanyParentParaIdByRefID($ref_para_id,$city_id,$company_id)
    {
        $city_id    = GetBaseLocationCity();
        return self::select('para_id')
                ->where('ref_para_id',$ref_para_id)
                ->whereIn('city_id',$city_id)
                ->where('company_id',$company_id)
                ->first();
    }
    /**
     * Use      : getCompanyCustomerGroup
     * Author   : Sachin Patel
     * Date     : 14 Feb,2019
     */

    public static function getCompanyCustomerGroup() {
        $city_id    = GetBaseLocationCity();
        $ParaParent = self::GetCompanyParentParaIdByRefID(PARA_CUSTOMER_GROUP,Auth::user()->city,Auth::user()->company_id);
        return self::select('para_id as id','para_value as name')
                ->where('para_parent_id',$ParaParent->para_id)
                ->whereIn('city_id',$city_id)
                ->where('company_id',Auth::user()->company_id)
                ->orderBy('para_value','ASC')
                ->get();
    }
}