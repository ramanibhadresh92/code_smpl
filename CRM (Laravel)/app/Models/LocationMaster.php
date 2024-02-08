<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Models\StateMaster;
use App\Models\BaseLocationCityMapping;
use App\Models\CompanyCityMpg;
use App\Models\CompanyParameter;
class LocationMaster extends Model
{
    protected 	$table 		=	'location_master';
    protected 	$guarded 	=	['location_id'];
    protected 	$primaryKey =	'location_id'; // or null
    public 		  $timestamps = 	false;

    public function getstate(){
       return $this->belongsTo(StateMaster::class,'state_id');
    }
    public function Department(){
       return $this->hasOne(WmDepartment::class,'location_id',"location_id");
    }

    // public function country()
    // {
    //     return $this->hasManyThrough(CountryMaster::class, StateMaster::class);
    // }
    /*
     * @param 	:
     * use 		: Get all record of location_master table
     * created 	: 13 Aug,2018
       Author 	: Axay Shah
     */
    public static function getAll(){
		return self::all();
    }
    /*
     * @param 	: pass state name of location master table
     * use 		: Get all city by its state name
     * created 	: 13 Aug,2018
       Author 	: Axay Shah
     */
    public static function getCityByState($state = null){
		return 	self::whereIn('state', array($state))
        		->orderBy('city', 'ASC')
        		->get();
    }
    /*
     * use 		: Get all state by its state name
     * Created 	: 13 Aug,2018
       Author 	: Axay Shah
     */
    public static function getAllState(){
		return  self::select('state_id','state')
				->orderBy('state', 'ASC')
				->groupBy('state')
				->get();
	}

	/**
	 * Use      : Get All City
     * Author   : Sachin Patel
     * Date     : 14 Feb, 2019
     */

    public static function GetCityList(){
        $cityId     = GetBaseLocationCity();
        return  self::select('location_id as id','city as name')
                // ->leftJoin('user_city_mpg','user_city_mpg.cityid','=','location_master.location_id')
                ->whereIn("location_id",$cityId)
                // ->where('adminuserid',Auth::user()->adminuserid)
                ->orderBy('city', 'ASC')->get();
    }
    /*
     * Use      : List All Citys from location master
     * Author   : Axay Shah
     * Date     : 22 April,2019
    */
    public static function ListLocations($request){

        $Today          =   date('Y-m-d');
        $sortBy         =   ($request->has('sortBy')    && !empty($request->input('sortBy'))) ? $request->input('sortBy') : "appointment_id";
        $sortOrder      =   ($request->has('sortOrder') && !empty($request->input('sortOrder'))) ? $request->input('sortOrder') : "ASC";
        $recordPerPage  =   !empty($request->input('size'))       ?   $request->input('size')         : DEFAULT_SIZE;
        $pageNumber     =   !empty($request->input('pageNumber')) ?   $request->input('pageNumber')   : '';
        $LocationTbl    =   (new static)->getTable();
        $Location       =   self::SELECT("$LocationTbl.*","state_master.state_name","country_master.country_name")
					        ->LEFTJOIN("state_master","$LocationTbl.state_id","=","state_master.state_id")
					        ->LEFTJOIN("country_master","state_master.country_id","=","country_master.country_id");

        if($request->has('params.city') && !empty($request->input('params.city')))
        {
            $Location->where("$LocationTbl.city",'like', '%'.$request->input('params.city').'%');
        }
        if($request->has('params.status') && !empty($request->input('params.status')))
        {
            $Location->where("$LocationTbl.status",$request->input('params.status'));
        }
        if($request->has('params.location_id') && !empty($request->input('params.location_id')))
        {
        	$LocationIds = array();
        	if(!is_array($request->input('params.location_id'))){
        		$LocationIds = explode(",",$request->input('params.location_id'));
        	}else{
        		$LocationIds = $request->input('params.location_id');
        	}
            $Location->whereIn("$LocationTbl.location_id",$LocationIds);
        }
        if($request->has('params.state_name') && !empty($request->input('params.state_name')))
        {
            $Location->where("state_master.state_id",$request->input('params.state_id'));
        }
        $data  = $Location->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
        return $data;
    }

    /*
     * Use      : Add & Edit Location Master citys
     * Author   : Axay Shah
     * Date     : 22 April,2019
    */
    // public static function ModifyLocation($request){
    //     if(isset($request->location_id) && !empty($request->location_id)){
    //         $Location           =   self::find($request->location_id);
    //     }else{
    //         $Location           =   new self();
    //     }
    //     $Location->city         =   (isset($request->city)      && !empty($request->city))      ? $request->city        : "";
    //     $Location->state_id     =   (isset($request->state_id)  && !empty($request->state_id))  ? $request->state_id    : 0;
    //     $Location->state     	=   StateMaster::GetStateName($request->state_id);
    //     $Location->color_code   =   (isset($request->color_code)&& !empty($request->color_code))? $request->color_code  : "";
    //     $Location->status       =   (isset($request->status)    && !empty($request->status))    ? $request->status      : "";
    //     if($Location->save()){
    //         return true;
    //     }else{
    //         return false;
    //     }
    // }
    public static function ModifyLocation($request){
        $cityName           = (isset($request->city) && !empty($request->city) ? $request->city : strtolower($request->city));
        $locationData       = LocationMaster::where('state_id',$request->state_id)->where('city',$cityName)->first();
        $location_id        = (!empty($locationData) ? CompanyCityMpg::where('city_id',$locationData->location_id)->value('city_id') : "");
        $company_id         = auth()->user()->company_id;
        $companyParameter   = array(PARA_CUSTOMER_GROUP,PARA_CUSTOMER_CONTACT_ROLE,PARA_CUSTOMER_REFFERED_BY,PARA_TYPE_OF_COLLECTION,PARA_COLLECTION_SITE);
        if(isset($request->location_id) && !empty($request->location_id)){
            $Location           = self::find($request->location_id);
        }else{
            $Location           =   new self();
        }
        $Location->city         =   (isset($request->city)      && !empty($request->city))      ? $request->city        : "";
        $Location->state_id     =   (isset($request->state_id)  && !empty($request->state_id))  ? $request->state_id    : 0;
        $Location->state        =   StateMaster::GetStateName($request->state_id);
        $Location->color_code   =   (isset($request->color_code)&& !empty($request->color_code))? $request->color_code  : "";
        $Location->status       =   (isset($request->status)    && !empty($request->status))    ? $request->status      : "";
        if($Location->save()){
            foreach($companyParameter as $value){
                $companyParameterCount = CompanyParameter::where('ref_para_id',$value)->where('city_id',$Location->location_id)->where('company_id',$company_id)->count();
                if($companyParameterCount <= 0){
                    DB::statement("CALL SP_INSERT_COMPANY_DEFALT_PARA_TYPE(".$value.", ".$company_id.", ".$Location->location_id.")");
                }
            }
            if(empty($location_id)){
                CompanyCityMpg::addCompanyCity($company_id,$Location->location_id);
            }
            return true;
        }else{
            return false;
        }
    }
    /*
     * Use      : Get By Id
     * Author   : Axay Shah
     * Date     : 22 April,2019
    */
    public static function getById($id){
        return self::find($id);
    }

    /*
     * Use      : Get Login Base Location City DropDown
     * Author   : Axay Shah
     * Date     : 29 April,2019
    */
    public static function BaseLocationCityDropDown($id){
        $BaseLocation       =   new BaseLocationCityMapping();
        $BaseLocationTbl    =   $BaseLocation->getTable();
        $LocationTbl        =   (new static)->getTable();
        return self::select("$LocationTbl.*")
        ->JOIN($BaseLocationTbl,"$LocationTbl.location_id","=","$BaseLocationTbl.city_id")
        ->WHERE("$BaseLocationTbl.base_location_id",$id)
        ->WHERE("$LocationTbl.status","A")
        ->get();
    }

    /**
     * Use      : Get All City State Wise (Client App)
     * Author   : Hardyesh Gupta
     * Date     : 23 Oct, 2020
     */
    public static function GetCityListStateWise($request){
        $state_id       = (isset($request->state_id)) ? $request->state_id : 0;
        $keyword        = (isset($request->keyword)) ? $request->keyword : "";
        $LocationTbl    =   (new static)->getTable();
        $data           =   self::SELECT("$LocationTbl.*")
                            ->where("$LocationTbl.status","A");
                            if(!empty($state_id)){
                                $data->where("$LocationTbl.state_id",$state_id);    
                            }
                            if(!empty($keyword)){
                                $data->where("$LocationTbl.city",'like',$keyword.'%');    
                            }
                            $data->orderBy("$LocationTbl.city",'ASC');
        $data           =  json_decode(json_encode($data->get()->toArray()),true);                            
        return $data;
    }

     /**
     * Use      : Get All City
     * Author   : Hardyesh Gupta
     * Date     : 11 January 2024
     */
    public static function CityList($request){
        $state_id   = (isset($request->state_id) && !empty($request->state_id)) ? $request->state_id : 0;
        $CityData   =  self::select(
                    'location_id as id',
                    'city as city',
                    'state as state',
                    'state_id as state_id',
                    'status as status');
        if(!empty($state_id)){
            $CityData->where("state_id",$state_id) ;
        } 
        // $CityData->where("status","A")
        $CityData->orderBy('city', 'ASC');
        $CityData = $CityData->get();
        //  $qry =  LiveServices::toSqlWithBinding($CityData,true);
        return $CityData;
    }
}


