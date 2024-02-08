<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseLocationCityMapping extends Model
{
    protected 	$table 		    =	'base_location_city_mapping';
    protected 	$guarded 	    =	['id'];
    protected 	$primaryKey     =	'id'; // or null
    public      $timestamps     = 	true;



    

    /*
	Use 	: Insert mapping of base location with city
	Author 	: Axay Shah
	Date 	: 23 April,2019	
	*/
    public static function InsertBaseLocationMapping($companyId,$baseLocationId,$cityId){
    	$Mapping = self::create([
    		"company_id"		=>$companyId,
    		"base_location_id"	=>$baseLocationId,
    		"city_id"			=>$cityId,
    		"created_by" 		=> Auth()->user()->adminuserid
    	]);
    	return $Mapping;
    }

    /*
	Use 	: remove base location mapping data
	Author 	: Axay Shah
	Date 	: 23 April,2019	
	*/
    public static function RemoveBaseLocationMapping($companyId,$baseLocationId){
    	return self::where(["company_id"=>$companyId,"base_location_id"=>$baseLocationId])->delete();
    }


    /*
    Use     : get city from base location id
    Author  : Axay Shah
    Date    : 26 April,2019
    */
    public static function getCityByBaseLocation(){
        return self::where("company_id",Auth()->user()->company_id)->where("base_location_id",Auth()->user()->base_location)->pluck('city_id');
    }

}
