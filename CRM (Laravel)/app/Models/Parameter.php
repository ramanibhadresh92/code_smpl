<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\AdminUserRights;
class Parameter extends Model
{
    //
    protected 	$table 		=	'parameter';
    protected 	$guarded 	=	['para_id'];
    protected 	$primaryKey =	'para_id'; // or null
    public 		$timestamps = 	true;

    /**
     * get all drop down of paramter table 
     * Author 	: Axay Shah
     * Date 	: 19 Sep,2018
     */
    public function scopeGetParameterType($query,$type)
    {
    	return $query->whereIn('para_id',$type)->where('status','A')->orderBy("para_id","ASC");
    }
    public function scopeParentDropDown($query,$type)
    {
    	return $query->where('para_parent_id',$type)->where('status','A')->orderBy("para_sort_order","ASC");
    }
    public function children() {
        return $this->hasMany(Parameter::class,'para_parent_id');
    }
    public function parent() {
        return $this->belongsTo(Parameter::class,'para_parent_id');
    }

    /*
    Use     :   List parameter type 
    Author  :   Axay Shah
    Date    :   01-10-2018
    */
    public static function getParameterType($request){

        return self::where('para_parent_id',0)
                    ->get();
    }

    /**
     * Use      : getCustomerGroup
     * Author   : Sachin Patel
     * Date     : 14 Feb,2019
     */

    public static function getCustomerType(){
        return self::select('para_id as id','para_value as name')->where('para_parent_id',PARA_CUSTOMER_TYPE)->orderBy('para_value','ASC')->get();
    }

    /**
     * Use      : Add Master parameter 
     * Author   : Sachin Patel
     * Date     : 14 Feb,2019
     */


    public static function add($request){
        
        DB::beginTransaction();
        $max = 1;
        try{
            $userId     = Auth()->user()->adminuserid;
            $priceGroup = new self();
            if(isset($request->para_parent_id) && !empty($request->para_parent_id)) {
                
                $max        = self::where('para_parent_id',$request->para_parent_id)->max('para_id');
                if($max > 0){
                    $maxId  = $max+1;
                }else{
                    $maxId  = ($request->para_parent_id * 1000)+1;
                }
            }
            $priceGroup->para_id                = $maxId;
            $priceGroup->para_level             = $request->para_level;
            $priceGroup->para_value             = $request->para_value;
            $priceGroup->para_desc              = $request->para_desc;
            $priceGroup->para_tech_desc         = $request->para_tech_desc;
            $priceGroup->created_by             = $userId;
            $priceGroup->para_parent_id         = (isset($request->para_parent_id) && !empty($request->para_parent_id))? $request->para_parent_id : 0;
            $priceGroup->para_sort_order        = (isset($request->para_sort_order) && !empty($request->para_sort_order))? $request->para_sort_order : 1;
            $priceGroup->show_in_scheduler      = (isset($request->show_in_scheduler) && !empty($request->show_in_scheduler))? $request->show_in_scheduler  : 0;
            $priceGroup->scheduler_time         = (isset($request->scheduler_time) && !empty($request->scheduler_time))? $request->scheduler_time   : 0;
            $priceGroup->longitude              = (isset($request->longitude) && !empty($request->longitude))? $request->longitude  : 0;
            $priceGroup->latitude               = (isset($request->latitude) && !empty($request->latitude))? $request->latitude     : 0;
            $priceGroup->map_customer_id        = (isset($request->map_customer_id) && !empty($request->map_customer_id))   ? $request->map_customer_id : 0;
            $priceGroup->status                 = (isset($request->status)  && !empty($request->status))    ? $request->status  : "A";
            $priceGroup->cust_identify_group    = (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : "";
            $priceGroup->save();
            LR_Modules_Log_CompanyUserActionLog($request,$maxId);
            DB::commit();
            return $priceGroup;
        }catch(\Exception $e) {
            DB::rollback();
            return false;
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }

    /*
    use     : update company parameter
    Author  : Axay Shah
    Date    : 01 Oct,2018
    */
    public static function updateRecord($request){
        DB::beginTransaction();
        try{
            $userId     = Auth()->user()->adminuserid;
            $priceGroup = self::findOrFail($request->para_id);
            $priceGroup->para_level             = (isset($request->para_level) && !empty($request->para_level))? $request->para_level   : $priceGroup->para_level;
            $priceGroup->para_parent_id         = (isset($request->para_parent_id) && !empty($request->para_parent_id))? $request->para_parent_id  : $priceGroup->para_parent_id;
            $priceGroup->para_value             = (isset($request->para_value) && !empty($request->para_value))? $request->para_value   : $priceGroup->para_value;
            $priceGroup->para_desc              = (isset($request->para_desc) && !empty($request->para_desc))? $request->para_desc      : $priceGroup->para_desc;
            $priceGroup->para_tech_desc         = (isset($request->para_tech_desc) && !empty($request->para_tech_desc))? $request->para_tech_desc : $priceGroup->para_tech_desc;
            $priceGroup->para_sort_order        = (isset($request->para_sort_order) && !empty($request->para_sort_order))? $request->para_sort_order : $priceGroup->para_sort_order;
            $priceGroup->show_in_scheduler      = (isset($request->show_in_scheduler) && !empty($request->show_in_scheduler))? $request->show_in_scheduler  : $priceGroup->show_in_scheduler;
            $priceGroup->scheduler_time         = (isset($request->scheduler_time) && !empty($request->scheduler_time))? $request->scheduler_time   : $priceGroup->scheduler_time  ;
            $priceGroup->longitude              = (isset($request->longitude) && !empty($request->longitude))? $request->longitude  : $priceGroup->longitude  ;
            $priceGroup->latitude               = (isset($request->latitude) && !empty($request->latitude))? $request->latitude     : $priceGroup->latitude  ;
            $priceGroup->map_customer_id        = (isset($request->map_customer_id) && !empty($request->map_customer_id))   ? $request->map_customer_id : $priceGroup->map_customer_id;
            $priceGroup->status                 = (isset($request->status)  && !empty($request->status))    ? $request->status  : $priceGroup->status;
            $priceGroup->cust_identify_group    = (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : $priceGroup->cust_identify_group;
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
    use     : Get by id detail
    Author  : Axay Shah
    Date    : 01 Oct,2018
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
    use     : Add Parent Parameter Type
    Author  : Axay Shah
    Date    : 01 Oct,2018
    */
    public static function addParameterType($request){
        DB::beginTransaction();
        try{
            $userId     = Auth()->user()->adminuserid;
            $max        = self::where('para_parent_id',0)->max('para_id');
            $maxId      = ($max <= 0 ) ? 1 : $max+1;
            $priceGroup = new self();
            $priceGroup->para_parent_id         = (isset($request->para_parent_id) && !empty($request->para_parent_id))? $request->para_parent_id : 0;
            $priceGroup->para_id                = $maxId;
            $priceGroup->para_level             = (isset($request->para_level) && !empty($request->para_level))? $request->para_level : 0;
            $priceGroup->para_value             = (isset($request->para_value) && !empty($request->para_value))? $request->para_value : "";
            $priceGroup->para_desc              = (isset($request->para_desc) && !empty($request->para_desc))? $request->para_desc : "";
            $priceGroup->para_tech_desc         = (isset($request->para_tech_desc) && !empty($request->para_tech_desc))? $request->para_tech_desc : "";
            $priceGroup->para_sort_order        = (isset($request->para_sort_order) && !empty($request->para_sort_order))? $request->para_sort_order : 1;
            $priceGroup->show_in_scheduler      = (isset($request->show_in_scheduler) && !empty($request->show_in_scheduler))? $request->show_in_scheduler  : 0;
            $priceGroup->scheduler_time         = (isset($request->scheduler_time) && !empty($request->scheduler_time))? $request->scheduler_time   : 0;
            $priceGroup->longitude              = (isset($request->longitude) && !empty($request->longitude))? $request->longitude  : 0;
            $priceGroup->latitude               = (isset($request->latitude) && !empty($request->latitude))? $request->latitude     : 0;
            $priceGroup->map_customer_id        = (isset($request->map_customer_id) && !empty($request->map_customer_id))   ? $request->map_customer_id : 0;
            $priceGroup->status                 = (isset($request->status)  && !empty($request->status))    ? $request->status  : "A";
            $priceGroup->cust_identify_group    = (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : "";
            $priceGroup->created_by             = $userId;
            $priceGroup->save();
            LR_Modules_Log_CompanyUserActionLog($request,$maxId);
            DB::commit();
            return $priceGroup;
        }catch(\Exception $e) {
            DB::rollback();
            return response()->json(['code' => INTERNAL_SERVER_ERROR , "msg"=>$e->getMessage(),"data"=>""]);
        }
    }
    /*
    use     : update company Parameter Type
    Author  : Axay Shah
    Date    : 01 Oct,2018
    */
    public static function updateParameterType($request){
        DB::beginTransaction();
        try{
            $userId     = Auth()->user()->adminuserid;
            $priceGroup = self::findOrFail($request->para_id);
            $priceGroup->para_parent_id         = (isset($request->para_parent_id) && !empty($request->para_parent_id))? $request->para_parent_id : $priceGroup->para_parent_id;
            $priceGroup->para_level             = (isset($request->para_level) && !empty($request->para_level))? $request->para_level   : $priceGroup->para_level;
            $priceGroup->para_value             = (isset($request->para_value) && !empty($request->para_value))? $request->para_value   : $priceGroup->para_value;
            $priceGroup->para_desc              = (isset($request->para_desc) && !empty($request->para_desc))? $request->para_desc      : $priceGroup->para_desc;
            $priceGroup->para_tech_desc         = (isset($request->para_tech_desc) && !empty($request->para_tech_desc))? $request->para_tech_desc : $priceGroup->para_tech_desc;
            $priceGroup->para_sort_order        = (isset($request->para_sort_order) && !empty($request->para_sort_order))? $request->para_sort_order : $priceGroup->para_sort_order;
            $priceGroup->show_in_scheduler      = (isset($request->show_in_scheduler) && !empty($request->show_in_scheduler))? $request->show_in_scheduler  : $priceGroup->show_in_scheduler;
            $priceGroup->scheduler_time         = (isset($request->scheduler_time) && !empty($request->scheduler_time))? $request->scheduler_time   : $priceGroup->scheduler_time  ;
            $priceGroup->longitude              = (isset($request->longitude) && !empty($request->longitude))? $request->longitude  : $priceGroup->longitude  ;
            $priceGroup->latitude               = (isset($request->latitude) && !empty($request->latitude))? $request->latitude     : $priceGroup->latitude  ;
            $priceGroup->map_customer_id        = (isset($request->map_customer_id) && !empty($request->map_customer_id))   ? $request->map_customer_id : $priceGroup->map_customer_id;
            $priceGroup->status                 = (isset($request->status)  && !empty($request->status))    ? $request->status  : $priceGroup->status;
            $priceGroup->cust_identify_group    = (isset($request->cust_identify_group) && !empty($request->cust_identify_group)) ? $request->cust_identify_group : $priceGroup->cust_identify_group;
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
    Use     :   List parameter of company 
    Author  :   Axay Shah
    Date    :   01-10-2018
    */
    public static function parameterList($request){
        $table  =  (new static)->getTable();
        $table1 =  (new static)->getTable();
        $sortBy         = ($request->has('sortBy')      && !empty($request->input('sortBy')))    ? $request->input('sortBy')    : "parameter.para_id";
        $sortOrder      = ($request->has('sortOrder')   && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
        $cityId         = ($request->has('params.city_id')      && !empty($request->input('params.city_id'))) ? $request->input('params.city_id') : 0;
        $recordPerPage  = !empty($request->input('size'))       ?   $request->input('size')         : 10;
        $pageNumber     = !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
        $list           =   self::select("$table.*",DB::raw("P.para_value as para_parent_name"))
            ->leftjoin("$table1 as P","$table.para_parent_id","=","P.para_id");
        if($request->has('params.para_parent_id') && !empty($request->input('params.para_parent_id')))
        {
            $list->where("$table.para_parent_id", $request->input('params.para_parent_id'));
        }
        if($request->has('params.para_sort_order') && !empty($request->input('params.para_sort_order')))
        {
            $list->where("$table.para_sort_order",$request->input('params.para_sort_order'));
        }
        if($request->has('params.flag_show') && !empty($request->input('params.flag_show')))
        {
            $list->where("$table.flag_show",$request->input('params.flag_show'));
        }
        if($request->has("params.para_value") && !empty($request->input('params.para_value')))
        {
            $list->where("$table.para_value","like", "%".$request->input('params.para_value')."%");
        }
        if($request->has('params.para_desc') && !empty($request->input('params.para_desc')))
        {
            $list->where("$table.para_desc",'like', '%'.$request->input('params.para_desc').'%');
        }
        if($request->has('params.para_tech_desc') && !empty($request->input('params.para_tech_desc')))
        {
            $list->where("$table.para_tech_desc",'like', '%'.$request->input('params.para_tech_desc').'%');
        }
        if($request->has('params.status') && !empty($request->input('params.status')))
        {
            $list->where("$table.status",$request->input('params.status'));
        }
        // LiveServices::toSqlWithBinding($list);
        return $list->orderBy($sortBy, $sortOrder)->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
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
        return self::select('para_id as id','para_value as name')->where('ref_para_id',PARA_CUSTOMER_GROUP)
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
     /**
     * Use      : GET PARAMETER
     * Author   : Axay Shah
     * Date     : 11 Dec,2019
     */
    public static function getParameter($ParentId,$paraValue = 'para_value'){
        return self::where('para_parent_id',$ParentId)
        ->where('status','A')
        ->orderBy($paraValue,'Asc')
        ->get();
    }

    /*
    Use     : Get Sales type by user rights
    Author  : Axay Shah
    Date    : 16 June 2020
    */

    public static function ListTypeOfTransaction($adminuserId = 0){
        $array  = array();
        $data   = array();
        $Rights = AdminUserRights::where("adminuserid",$adminuserId)
        ->whereIn("trnid",[TRN_CORPORATE_SALES,TRN_RETAIL_SALES,TRN_RDF])
        ->pluck("trnid");
        if(!empty($Rights)){
            foreach($Rights AS $RAW){

                switch ($RAW) {
                    case TRN_CORPORATE_SALES:
                        array_push($array,PARA_CORPORATE_SALES);
                        break;
                    case TRN_RETAIL_SALES:
                        array_push($array,PARA_RETAIL_SALES);
                        break;
                    case TRN_RDF:
                        array_push($array,PARA_RDF);
                        break;
                    default:
                        $array;
                        break;
                }
            }
            
            if(!empty($array)){
                $data = Parameter::whereIn("para_id",$array)->get();
            }
        }
        return $data;
    }
}
