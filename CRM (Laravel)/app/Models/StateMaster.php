<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LocationMaster;
use App\Models\GSTStateCodes;
use App\Facades\LiveServices;
class StateMaster extends Model
{
    protected 	$table 		=	'state_master';
    protected 	$guarded 	=	['state_id'];
    protected 	$primaryKey =	'state_id'; // or null

    public function country(){
        return $this->hasOne(CountryMaster::class,'country_id');
     }
    public static function getAllState(){
    	return self::select('state_id','state_name as state','country_id')->get();
    }

    public static function GetStateList(){
        return self::select('state_id','state_name')->orderBy('state_name','ASC')->get();
    }

    public static function GetStateName($stateId){
        return self::where('state_id',$stateId)->value('state_name');
    }

    /*
    Use     : GET GST CODE FOR STATE MASTER
    Author  : Axay Shah
    Date    : 05 July,2019
    */
    public static function GetGSTCodeByCustomerCity($cityId){
        $state      = (new static)->getTable();
        $Location   = new LocationMaster();
        $GST        = new GSTStateCodes();
        $data =  LocationMaster::join("$state","$state.state_id","=",$Location->getTable().".state_id")
        ->join($GST->getTable(),"$state.gst_state_code_id","=",$GST->getTable().".id")
        ->where($Location->getTable().".location_id",$cityId)
        ->first([$GST->getTable().".state_code"]);
        return $data;
    }

    /*
    Use     : State Data
    Author  : Hardyesh Gupta
    Date    : 23 october, 2023
    */
    public static function getAllStateData($request){
        $keyword        =   (isset($request->keyword)) ? $request->keyword : "";
        $data           =   self::select("*");
                            if(!empty($keyword)){
                                $data->where("state_name",'like',$keyword.'%');    
                            }
                            $data->orderBy("state_name",'ASC');
        $data           =  json_decode(json_encode($data->get()->toArray()),true);                                          
        return $data;
    }
    
    /**
     * Use      : Get All State
     * Author   : Hardyesh Gupta
     * Date     : 11 January 2024
     */
    public static function StateList($request){
        
        $data   =  self::select(
                    'state_id as id',
                    'country_id as country_id',
                    'state_name as state_name',
                    'status as status',
                    'gst_state_code_id as gst_state_code_id');
        if(!empty($state_id)){
            $data->where("state_id",$state_id) ;
        } 
        $data->where("status","A")
        ->orderBy('city', 'ASC');
        $data = $data->get();
        //  $qry =  LiveServices::toSqlWithBinding($data,true);
        // prd($qry);
        return $data;
    }
}
